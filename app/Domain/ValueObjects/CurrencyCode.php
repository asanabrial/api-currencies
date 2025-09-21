<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class CurrencyCode
{
    public function __construct(
        private string $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException('Currency code cannot be empty.');
        }

        if (strlen($this->value) !== 3) {
            throw new InvalidArgumentException('Currency code must be exactly 3 characters.');
        }

        if (!ctype_alpha($this->value)) {
            throw new InvalidArgumentException('Currency code must contain only alphabetic characters.');
        }
    }

    public function value(): string
    {
        return strtoupper($this->value);
    }

    public function equals(CurrencyCode $other): bool
    {
        return $this->value() === $other->value();
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
