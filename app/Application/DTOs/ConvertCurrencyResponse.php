<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class ConvertCurrencyResponse
{
    public function __construct(
        public string $from,
        public string $to,
        public float $amount,
        public float $converted,
        public float $rate
    ) {
    }

    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'amount' => $this->amount,
            'converted' => $this->converted,
            'rate' => $this->rate,
        ];
    }
}
