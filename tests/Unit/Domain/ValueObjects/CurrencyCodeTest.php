<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObjects;

use App\Domain\ValueObjects\CurrencyCode;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CurrencyCodeTest extends TestCase
{
    public function test_creates_valid_currency_code(): void
    {
        $code = new CurrencyCode('USD');

        $this->assertEquals('USD', $code->value());
    }

    public function test_normalizes_lowercase_input(): void
    {
        $code = new CurrencyCode('usd');

        $this->assertEquals('USD', $code->value());
    }

    public function test_throws_exception_for_whitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency code must be exactly 3 characters.');

        new CurrencyCode(' EUR ');
    }

    public function test_throws_exception_for_empty_code(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency code cannot be empty.');

        new CurrencyCode('');
    }

    public function test_throws_exception_for_invalid_length(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency code must be exactly 3 characters.');

        new CurrencyCode('US');
    }

    public function test_throws_exception_for_invalid_format(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Currency code must contain only alphabetic characters.');

        new CurrencyCode('U5D');
    }

    public function test_equality_comparison(): void
    {
        $usd1 = new CurrencyCode('USD');
        $usd2 = new CurrencyCode('USD');
        $eur = new CurrencyCode('EUR');

        $this->assertTrue($usd1->equals($usd2));
        $this->assertFalse($usd1->equals($eur));
    }
}
