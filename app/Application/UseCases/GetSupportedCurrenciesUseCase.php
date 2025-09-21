<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Domain\Services\CurrencyConverterService;

class GetSupportedCurrenciesUseCase
{
    public function __construct(
        private readonly CurrencyConverterService $currencyConverterService
    ) {
    }

    public function execute(): array
    {
        return $this->currencyConverterService->getSupportedCurrencies();
    }
}
