<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Actions;

use App\Application\Actions\GetSupportedCurrenciesAction;
use App\Domain\Services\CurrencyConverterService;
use PHPUnit\Framework\TestCase;

class GetSupportedCurrenciesActionTest extends TestCase
{
    public function test_invoke_returns_supported_currencies(): void
    {
        // Arrange
        $mockService = $this->createMock(CurrencyConverterService::class);
        $action = new GetSupportedCurrenciesAction($mockService);
        
        $expectedCurrencies = ['USD', 'EUR', 'GBP', 'JPY'];
        
        $mockService->expects($this->once())
            ->method('getSupportedCurrencies')
            ->willReturn($expectedCurrencies);

        // Act
        $result = ($action)();

        // Assert
        $this->assertEquals($expectedCurrencies, $result);
    }
}