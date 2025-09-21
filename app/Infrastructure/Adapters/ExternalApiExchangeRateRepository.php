<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters;

use App\Domain\Ports\ExchangeRateRepository;
use App\Domain\ValueObjects\CurrencyCode;
use App\Domain\ValueObjects\ExchangeRate;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final readonly class ExternalApiExchangeRateRepository implements ExchangeRateRepository
{
    private const CACHE_TTL = 3600;
    private const BASE_URL = 'https://api.exchangerate-api.com/v4/latest/';

    public function __construct(
        private Client $httpClient
    ) {
    }

    public function getExchangeRate(CurrencyCode $from, CurrencyCode $to): ExchangeRate
    {
        $cacheKey = "exchange_rate_{$from->value()}_{$to->value()}";

        $cachedRate = Cache::get($cacheKey);
        
        if ($cachedRate !== null) {
            // If cached value is already an ExchangeRate object, return it
            if ($cachedRate instanceof ExchangeRate) {
                return $cachedRate;
            }
            // If cached value is a numeric value, create ExchangeRate object
            if (is_numeric($cachedRate)) {
                return new ExchangeRate((float) $cachedRate);
            }
        }

        try {
            $response = $this->httpClient->get(self::BASE_URL . $from->value(), [
                'timeout' => 10,
                'verify' => false,
                'headers' => [
                    'User-Agent' => 'Currency-Converter-API/1.0',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (!isset($data['rates'][$to->value()])) {
                throw new Exception("Exchange rate not found for {$from->value()} to {$to->value()}");
            }

            $rate = (float) $data['rates'][$to->value()];
            $exchangeRate = new ExchangeRate($rate);
            
            // Cache the numeric value instead of the object to avoid serialization issues
            Cache::put($cacheKey, $rate, self::CACHE_TTL);
            
            return $exchangeRate;

        } catch (GuzzleException $e) {
            Log::error('Failed to fetch exchange rate', [
                'from' => $from->value(),
                'to' => $to->value(),
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Unable to fetch current exchange rates. Please try again later.');
        }
    }

    public function getSupportedCurrencies(): array
    {
        $cacheKey = 'supported_currencies';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            try {
                $response = $this->httpClient->get(self::BASE_URL . 'USD', [
                    'timeout' => 10,
                    'verify' => false,
                    'headers' => [
                        'User-Agent' => 'Currency-Converter-API/1.0',
                    ],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if (!isset($data['rates'])) {
                    throw new Exception('Unable to fetch supported currencies');
                }

                $currencies = array_keys($data['rates']);
                $currencies[] = 'USD';
                sort($currencies);

                return $currencies;

            } catch (GuzzleException $e) {
                Log::error('Failed to fetch supported currencies', [
                    'error' => $e->getMessage(),
                ]);

                return ['USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY'];
            }
        });
    }
}
