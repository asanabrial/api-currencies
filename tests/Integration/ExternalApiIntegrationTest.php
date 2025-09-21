<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Domain\ValueObjects\CurrencyCode;
use App\Infrastructure\Adapters\ExternalApiExchangeRateRepository;
use GuzzleHttp\Client;
use Tests\TestCase;

class ExternalApiIntegrationTest extends TestCase
{
    public function test_external_api_repository_integration(): void
    {
        $repository = new ExternalApiExchangeRateRepository(new Client());

        $fromCurrency = new CurrencyCode('USD');
        $toCurrency = new CurrencyCode('EUR');

        $exchangeRate = $repository->getExchangeRate($fromCurrency, $toCurrency);

        $this->assertIsFloat($exchangeRate->value());
        $this->assertGreaterThan(0, $exchangeRate->value());
        $this->assertLessThan(100, $exchangeRate->value());
    }

    public function test_get_supported_currencies_from_real_api(): void
    {
        $repository = new ExternalApiExchangeRateRepository(new Client());

        $currencies = $repository->getSupportedCurrencies();

        $this->assertIsArray($currencies);
        $this->assertNotEmpty($currencies);
        
        $this->assertContains('USD', $currencies);
        $this->assertContains('EUR', $currencies);
        $this->assertContains('GBP', $currencies);
        $this->assertContains('JPY', $currencies);
        
        foreach ($currencies as $currency) {
            $this->assertMatchesRegularExpression('/^[A-Z]{3}$/', $currency);
        }
    }
}