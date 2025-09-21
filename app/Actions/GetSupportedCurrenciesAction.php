<?php

declare(strict_types=1);

namespace App\Actions;

use App\Application\UseCases\GetSupportedCurrenciesUseCase;

final readonly class GetSupportedCurrenciesAction
{
    public function __construct(
        private GetSupportedCurrenciesUseCase $getSupportedCurrenciesUseCase
    ) {
    }

    public function execute(): array
    {
        return $this->getSupportedCurrenciesUseCase->execute();
    }
}
