<?php

declare(strict_types=1);

namespace App\Application\Actions;

class LogoutUserAction
{
    public function __invoke(): void
    {
        $user = auth()->user();
        
        if ($user) {
            $user->currentAccessToken()->delete();
        }
    }
}