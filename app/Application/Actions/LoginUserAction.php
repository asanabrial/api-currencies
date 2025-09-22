<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\DTOs\AuthenticatedUserResponse;
use App\Application\DTOs\LoginUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class LoginUserAction
{
    public function __invoke(LoginUserRequest $request): AuthenticatedUserResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw new InvalidArgumentException('Invalid credentials');
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return new AuthenticatedUserResponse(
            $user->id,
            $user->name,
            $user->email,
            $token
        );
    }
}