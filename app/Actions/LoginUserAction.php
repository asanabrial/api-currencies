<?php

declare(strict_types=1);

namespace App\Actions;

use App\Application\DTOs\AuthenticatedUserResponse;
use App\Application\DTOs\LoginUserRequest;
use App\Application\UseCases\LoginUserUseCase;

final readonly class LoginUserAction
{
    public function __construct(
        private LoginUserUseCase $loginUserUseCase
    ) {
    }

    public function execute(array $requestData): AuthenticatedUserResponse
    {
        $request = LoginUserRequest::fromArray($requestData);

        return $this->loginUserUseCase->execute($request);
    }
}