<?php

declare(strict_types=1);

namespace App\Actions;

use App\Application\DTOs\AuthenticatedUserResponse;
use App\Application\DTOs\RegisterUserRequest;
use App\Application\UseCases\RegisterUserUseCase;

final readonly class RegisterUserAction
{
    public function __construct(
        private RegisterUserUseCase $registerUserUseCase
    ) {
    }

    public function execute(array $requestData): AuthenticatedUserResponse
    {
        $request = RegisterUserRequest::fromArray($requestData);

        return $this->registerUserUseCase->execute($request);
    }
}