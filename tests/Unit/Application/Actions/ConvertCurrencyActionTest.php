<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Actions;

use App\Application\Actions\ConvertCurrencyAction;
use App\Application\DTOs\ConvertCurrencyRequest;
use App\Domain\Entities\CurrencyConversion;
use App\Domain\Services\CurrencyConverterService;
use App\Domain\ValueObjects\Amount;
use App\Domain\ValueObjects\CurrencyCode;
use App\Domain\ValueObjects\ExchangeRate;
use PHPUnit\Framework\TestCase;

class ConvertCurrencyActionTest extends TestCase
{
    public function test_invoke_converts_currency_successfully(): void
    {
        // Arrange
        $mockService = $this->createMock(CurrencyConverterService::class);
        $action = new ConvertCurrencyAction($mockService);
        
        $request = new ConvertCurrencyRequest('USD', 'EUR', 100.0);
        
        $conversion = new CurrencyConversion(
            new CurrencyCode('USD'),
            new CurrencyCode('EUR'),
            new Amount(100.0),
            new Amount(93.25),
            new ExchangeRate(0.9325)
        );
        
        $mockService->expects($this->once())
            ->method('convert')
            ->willReturn($conversion);

        // Act
        $result = ($action)($request);

        // Assert
        $this->assertEquals('USD', $result->from);
        $this->assertEquals('EUR', $result->to);
        $this->assertEquals(100.0, $result->amount);
        $this->assertEquals(93.25, $result->converted);
        $this->assertEquals(0.9325, $result->rate);
    }
}