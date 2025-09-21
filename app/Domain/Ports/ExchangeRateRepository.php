<?php

declare(strict_types=1);

namespace App\Domain\Ports;

use App\Domain\ValueObjects\CurrencyCode;
use App\Domain\ValueObjects\ExchangeRate;

interface ExchangeRateRepository
{
    /**
     * Retrieve the current exchange rate between two currencies
     *
     * @throws \Exception when exchange rate cannot be retrieved
     */
    public function getExchangeRate(CurrencyCode $from, CurrencyCode $to): ExchangeRate;

    /**
     * Get list of supported currency codes
     *
     * @return array<string>
     */
    public function getSupportedCurrencies(): array;
}
