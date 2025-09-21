<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class ExchangeRate
{
    public function __construct(
        private float $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->value <= 0) {
            throw new InvalidArgumentException('Exchange rate must be a positive number.');
        }

        if (!is_finite($this->value)) {
            throw new InvalidArgumentException('Exchange rate must be a valid finite number.');
        }
    }

    public function value(): float
    {
        return $this->value;
    }

    public function equals(ExchangeRate $other): bool
    {
        return abs($this->value - $other->value()) < 0.0001;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
