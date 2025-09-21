<?php

declare(strict_types=1);

namespace Tests\Unit\Http;

use App\Domain\Ports\ExchangeRateRepository;
use App\Domain\ValueObjects\CurrencyCode;
use App\Domain\ValueObjects\ExchangeRate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Tests\TestCase;

class CurrencyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_convert_currency_requires_authentication(): void
    {
        $response = $this->postJson('/api/currency/convert', [
            'from' => 'USD',
            'to' => 'EUR',
            'amount' => 100,
        ]);

        $response->assertStatus(401);
    }

    public function test_successful_currency_conversion(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $mockRepository = Mockery::mock(ExchangeRateRepository::class);
        $mockRepository->shouldReceive('getExchangeRate')
            ->with(
                Mockery::type(CurrencyCode::class),
                Mockery::type(CurrencyCode::class)
            )
            ->once()
            ->andReturn(new ExchangeRate(0.9325));

        $this->app->instance(ExchangeRateRepository::class, $mockRepository);

        $response = $this->postJson('/api/currency/convert', [
            'from' => 'USD',
            'to' => 'EUR',
            'amount' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'from' => 'USD',
                    'to' => 'EUR',
                    'amount' => 100,
                    'converted' => 93.25,
                    'rate' => 0.9325,
                ]
            ]);
    }

    public function test_validation_errors(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/currency/convert', [
            'from' => 'INVALID',
            'to' => 'EUR',
            'amount' => -100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['from', 'amount']);
    }

    public function test_missing_required_fields(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/currency/convert', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['from', 'to', 'amount']);
    }

    public function test_handles_external_api_errors(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $mockRepository = Mockery::mock(ExchangeRateRepository::class);
        $mockRepository->shouldReceive('getExchangeRate')
            ->once()
            ->andThrow(new \Exception('Unable to fetch current exchange rates. Please try again later.'));

        $this->app->instance(ExchangeRateRepository::class, $mockRepository);

        $response = $this->postJson('/api/currency/convert', [
            'from' => 'USD',
            'to' => 'EUR',
            'amount' => 100,
        ]);

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'Service Unavailable'
            ]);
    }

    public function test_get_supported_currencies_requires_authentication(): void
    {
        $response = $this->getJson('/api/currency');

        $response->assertStatus(401);
    }

    public function test_get_supported_currencies_success(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/currency');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'currencies' => [],
                ]
            ]);
    }
}
