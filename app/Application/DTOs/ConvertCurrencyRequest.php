<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class ConvertCurrencyRequest
{
    public function __construct(
        public string $from,
        public string $to,
        public float $amount
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['from'],
            $data['to'],
            (float) $data['amount']
        );
    }
}
