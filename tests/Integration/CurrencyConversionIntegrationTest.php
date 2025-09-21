<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CurrencyConversionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_full_currency_conversion_flow_with_real_dependencies(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/currency/convert', [
            'from' => 'USD',
            'to' => 'EUR',
            'amount' => 100,
        ]);

        $response->assertStatus(200);

        $responseData = $response->json();
        
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertTrue($responseData['success']);
        
        $data = $responseData['data'];
        $this->assertArrayHasKey('from', $data);
        $this->assertArrayHasKey('to', $data);
        $this->assertArrayHasKey('amount', $data);
        $this->assertArrayHasKey('converted', $data);
        $this->assertArrayHasKey('rate', $data);

        $this->assertEquals('USD', $data['from']);
        $this->assertEquals('EUR', $data['to']);
        $this->assertEquals(100, $data['amount']);
        $this->assertIsFloat($data['converted']);
        $this->assertIsFloat($data['rate']);
        
        $this->assertEquals($data['amount'] * $data['rate'], $data['converted']);
    }

    public function test_authentication_integration_with_real_sanctum(): void
    {
        $response = $this->postJson('/api/currency/convert', [
            'from' => 'USD',
            'to' => 'EUR',
            'amount' => 100,
        ]);

        $response->assertStatus(401);
    }

    public function test_validation_integration_with_real_form_request(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/currency/convert', [
            'from' => 'INVALID_CURRENCY',
            'to' => 'EUR',
            'amount' => -50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['from', 'amount']);
    }

    public function test_supported_currencies_integration(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/currency');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'currencies',
                ],
                'timestamp',
            ]);

        $currencies = $response->json('data.currencies');
        $this->assertIsArray($currencies);
        
        $this->assertContains('USD', $currencies);
        $this->assertContains('EUR', $currencies);
    }
}