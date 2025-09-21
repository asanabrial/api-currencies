<?php

declare(strict_types=1);

namespace App\Application\DTOs;

readonly class RegisterUserRequest
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['email'],
            $data['password']
        );
    }
}