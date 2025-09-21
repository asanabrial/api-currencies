<?php

declare(strict_types=1);

namespace App\Application\UseCases;

use App\Application\DTOs\AuthenticatedUserResponse;
use App\Application\DTOs\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class RegisterUserUseCase
{
    public function execute(RegisterUserRequest $request): AuthenticatedUserResponse
    {
        if (User::where('email', $request->email)->exists()) {
            throw new InvalidArgumentException('Email already exists');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return new AuthenticatedUserResponse(
            $user->id,
            $user->name,
            $user->email,
            $token
        );
    }
}