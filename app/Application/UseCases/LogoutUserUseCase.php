<?php

declare(strict_types=1);

namespace App\Application\UseCases;

class LogoutUserUseCase
{
    public function execute(): void
    {
        $user = auth()->user();
        
        if ($user) {
            $user->currentAccessToken()->delete();
        }
    }
}