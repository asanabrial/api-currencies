<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Domain\Entities\CurrencyConversion;
use App\Domain\Ports\ExchangeRateRepository;
use App\Domain\ValueObjects\Amount;
use App\Domain\ValueObjects\CurrencyCode;
use App\Domain\ValueObjects\ExchangeRate;

class CurrencyConverterService
{
    public function __construct(
        private readonly ExchangeRateRepository $exchangeRateRepository
    ) {
    }

    public function convert(CurrencyCode $from, CurrencyCode $to, Amount $amount): CurrencyConversion
    {
        if ($from->equals($to)) {
            return CurrencyConversion::create(
                $from,
                $to,
                $amount,
                new ExchangeRate(1.0)
            );
        }

        $exchangeRate = $this->exchangeRateRepository->getExchangeRate($from, $to);

        return CurrencyConversion::create($from, $to, $amount, $exchangeRate);
    }

    public function getSupportedCurrencies(): array
    {
        return $this->exchangeRateRepository->getSupportedCurrencies();
    }
}
