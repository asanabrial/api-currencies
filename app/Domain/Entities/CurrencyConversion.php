<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Amount;
use App\Domain\ValueObjects\CurrencyCode;
use App\Domain\ValueObjects\ExchangeRate;

final readonly class CurrencyConversion
{
    public function __construct(
        private CurrencyCode $from,
        private CurrencyCode $to,
        private Amount $originalAmount,
        private Amount $convertedAmount,
        private ExchangeRate $exchangeRate
    ) {
    }

    public static function create(
        CurrencyCode $from,
        CurrencyCode $to,
        Amount $amount,
        ExchangeRate $rate
    ): self {
        $convertedAmount = $amount->multiply($rate);

        return new self(
            $from,
            $to,
            $amount,
            $convertedAmount,
            $rate
        );
    }

    public function fromCurrency(): CurrencyCode
    {
        return $this->from;
    }

    public function toCurrency(): CurrencyCode
    {
        return $this->to;
    }

    public function originalAmount(): Amount
    {
        return $this->originalAmount;
    }

    public function convertedAmount(): Amount
    {
        return $this->convertedAmount;
    }

    public function exchangeRate(): ExchangeRate
    {
        return $this->exchangeRate;
    }

    public function isSameCurrency(): bool
    {
        return $this->from->equals($this->to);
    }

    public function toArray(): array
    {
        return [
            'from' => $this->from->value(),
            'to' => $this->to->value(),
            'amount' => $this->originalAmount->value(),
            'converted' => round($this->convertedAmount->value(), 2),
            'rate' => $this->exchangeRate->value(),
        ];
    }
}
