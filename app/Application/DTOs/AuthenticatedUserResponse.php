<?php

declare(strict_types=1);

namespace App\Application\DTOs;

readonly class AuthenticatedUserResponse
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $token
    ) {
    }

    public function toArray(): array
    {
        return [
            'user' => [
                'id' => $this->id,
                'name' => $this->name,
                'email' => $this->email,
            ],
            'token' => $this->token,
        ];
    }
}