<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Adapters;

use App\Domain\ValueObjects\CurrencyCode;
use App\Infrastructure\Adapters\ExternalApiExchangeRateRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class ExternalApiExchangeRateRepositoryTest extends TestCase
{
    private ExternalApiExchangeRateRepository $repository;
    private $mockClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClient = Mockery::mock(Client::class);
        $this->repository = new ExternalApiExchangeRateRepository($this->mockClient);

        Cache::flush();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_gets_exchange_rate_from_api(): void
    {
        $this->mockClient->shouldReceive('get')
            ->with(
                'https://api.exchangerate-api.com/v4/latest/USD',
                Mockery::type('array')
            )
            ->once()
            ->andReturn(new Response(200, [], json_encode([
                'rates' => [
                    'EUR' => 0.9325,
                ],
            ])));

        $from = new CurrencyCode('USD');
        $to = new CurrencyCode('EUR');

        $rate = $this->repository->getExchangeRate($from, $to);

        $this->assertEquals(0.9325, $rate->value());
    }

    public function test_throws_exception_when_rate_not_found(): void
    {
        $this->mockClient->shouldReceive('get')
            ->once()
            ->andReturn(new Response(200, [], json_encode([
                'rates' => [],
            ])));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Exchange rate not found for USD to EUR');

        $from = new CurrencyCode('USD');
        $to = new CurrencyCode('EUR');

        $this->repository->getExchangeRate($from, $to);
    }

    public function test_throws_exception_on_http_error(): void
    {
        $this->mockClient->shouldReceive('get')
            ->once()
            ->andThrow(new RequestException('Network error', new Request('GET', 'test')));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to fetch current exchange rates. Please try again later.');

        $from = new CurrencyCode('USD');
        $to = new CurrencyCode('EUR');

        $this->repository->getExchangeRate($from, $to);
    }
}
