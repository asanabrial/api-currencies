<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\ConvertCurrencyRequest;
use App\Application\DTOs\ConvertCurrencyResponse;
use App\Domain\Services\CurrencyConverterService;
use App\Domain\ValueObjects\Amount;
use App\Domain\ValueObjects\CurrencyCode;

class ConvertCurrencyUseCase
{
    public function __construct(
        private readonly CurrencyConverterService $currencyConverterService
    ) {
    }

    public function execute(ConvertCurrencyRequest $request): ConvertCurrencyResponse
    {
        $from = new CurrencyCode($request->from);
        $to = new CurrencyCode($request->to);
        $amount = new Amount($request->amount);

        $conversion = $this->currencyConverterService->convert($from, $to, $amount);

        return new ConvertCurrencyResponse(
            from: $conversion->fromCurrency()->value(),
            to: $conversion->toCurrency()->value(),
            amount: $conversion->originalAmount()->value(),
            converted: round($conversion->convertedAmount()->value(), 2),
            rate: $conversion->exchangeRate()->value()
        );
    }
}
