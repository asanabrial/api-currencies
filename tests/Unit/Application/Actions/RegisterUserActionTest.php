<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Actions;

use App\Application\Actions\RegisterUserAction;
use App\Application\DTOs\RegisterUserRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class RegisterUserActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoke_registers_user_successfully(): void
    {
        // Arrange
        $action = new RegisterUserAction();
        $request = new RegisterUserRequest('John Doe', 'john@example.com', 'password123');

        // Act
        $result = ($action)($request);

        // Assert
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertNotEmpty($result->token);
        
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_invoke_throws_exception_for_existing_email(): void
    {
        // Arrange
        $action = new RegisterUserAction();
        
        User::factory()->create(['email' => 'existing@example.com']);
        $request = new RegisterUserRequest('Jane Doe', 'existing@example.com', 'password123');

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Email already exists');
        
        ($action)($request);
    }
}