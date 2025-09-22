<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_integration_flow(): void
    {
        // Act
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Assert - Based on the actual response structure from middleware
        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'token',
                ],
                'timestamp',
            ]);

        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        $this->assertEquals('John Doe', $responseData['data']['user']['name']);
        $this->assertEquals('john@example.com', $responseData['data']['user']['email']);
        $this->assertNotEmpty($responseData['data']['token']);

        // Verify user was created in database
        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_login_integration_flow(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert - Based on the actual response structure from middleware
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'token',
                ],
                'timestamp',
            ]);

        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        $this->assertEquals($user->id, $responseData['data']['user']['id']);
        $this->assertEquals($user->email, $responseData['data']['user']['email']);
        $this->assertNotEmpty($responseData['data']['token']);
    }

    public function test_user_logout_integration_flow(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token->plainTextToken,
        ])->postJson('/api/auth/logout');

        // Assert - Based on the actual response structure from middleware
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'message',
                ],
                'timestamp',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'message' => 'Logged out successfully',
                ],
            ]);

        // Verify token was deleted
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    public function test_registration_validation_errors(): void
    {
        // Act - Missing required fields
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_with_invalid_credentials(): void
    {
        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJsonStructure([
                'success',
                'message',
            ])
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_logout_without_authentication(): void
    {
        // Act
        $response = $this->postJson('/api/auth/logout');

        // Assert
        $response->assertStatus(401);
    }
}