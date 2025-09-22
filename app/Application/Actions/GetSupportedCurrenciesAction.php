<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Domain\Services\CurrencyConverterService;

class GetSupportedCurrenciesAction
{
    public function __construct(
        private readonly CurrencyConverterService $currencyConverterService
    ) {
    }

    public function __invoke(): array
    {
        return $this->currencyConverterService->getSupportedCurrencies();
    }
}