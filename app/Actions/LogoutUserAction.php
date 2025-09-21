<?php

declare(strict_types=1);

namespace App\Actions;

use App\Application\UseCases\LogoutUserUseCase;

final readonly class LogoutUserAction
{
    public function __construct(
        private LogoutUserUseCase $logoutUserUseCase
    ) {
    }

    public function execute(): void
    {
        $this->logoutUserUseCase->execute();
    }
}