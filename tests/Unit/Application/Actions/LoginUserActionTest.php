<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Actions;

use App\Application\Actions\LoginUserAction;
use App\Application\DTOs\LoginUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\TestCase;

class LoginUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_login_user_successfully(): void
    {
        // Arrange
        $action = new LoginUserAction();
        
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
        
        $request = new LoginUserRequest('test@example.com', 'password123');

        // Act
        $result = ($action)($request);

        // Assert
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals($user->name, $result->name);
        $this->assertEquals($user->email, $result->email);
        $this->assertNotEmpty($result->token);
    }

    public function test_invoke_throws_exception_for_invalid_credentials(): void
    {
        // Arrange
        $action = new LoginUserAction();
        $request = new LoginUserRequest('nonexistent@example.com', 'wrongpassword');

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid credentials');
        
        ($action)($request);
    }
}