<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Actions\LoginUserAction;
use App\Application\Actions\LogoutUserAction;
use App\Application\Actions\RegisterUserAction;
use App\Application\DTOs\LoginUserRequest;
use App\Application\DTOs\RegisterUserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class AuthController extends Controller
{
    public function __construct(
        private RegisterUserAction $registerUserAction,
        private LoginUserAction $loginUserAction,
        private LogoutUserAction $logoutUserAction
    ) {
    }
    /**
     * @OA\Post(
     *     path="/api/auth/register",
     *     summary="Register a new user",
     *     description="Create a new user account and return an API token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com")
     *             ),
     *             @OA\Property(property="token", type="string", example="1|abc123def456...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $registerUserRequest = RegisterUserRequest::fromArray($validator->validated());
            $result = ($this->registerUserAction)($registerUserRequest);

            return response()->json($result->toArray(), 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/login",
     *     summary="Login user",
     *     description="Authenticate user and return an API token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com")
     *             ),
     *             @OA\Property(property="token", type="string", example="2|xyz789abc123...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $loginUserRequest = LoginUserRequest::fromArray($validator->validated());
            $result = ($this->loginUserAction)($loginUserRequest);

            return response()->json($result->toArray());
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/auth/logout",
     *     summary="Logout user",
     *     description="Revoke the current user's API token",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        ($this->logoutUserAction)();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
