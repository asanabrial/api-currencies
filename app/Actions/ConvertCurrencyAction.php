<?php

declare(strict_types=1);

namespace App\Actions;

use App\Application\DTOs\ConvertCurrencyRequest;
use App\Application\DTOs\ConvertCurrencyResponse;
use App\Application\UseCases\ConvertCurrencyUseCase;

final readonly class ConvertCurrencyAction
{
    public function __construct(
        private ConvertCurrencyUseCase $convertCurrencyUseCase
    ) {
    }

    public function execute(array $requestData): ConvertCurrencyResponse
    {
        $request = ConvertCurrencyRequest::fromArray($requestData);

        return $this->convertCurrencyUseCase->execute($request);
    }
}
