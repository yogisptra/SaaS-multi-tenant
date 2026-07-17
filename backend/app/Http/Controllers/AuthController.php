<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private AuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return $this->successResponse('User registered successfully', $user, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login($request->validated());

        if (!$token) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        return $this->successResponse('Login successful', [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => auth('api')->user()
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return $this->successResponse('Successfully logged out');
    }

    public function refresh(): JsonResponse
    {
        $token = $this->authService->refresh();

        return $this->successResponse('Token refreshed', [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }

    public function profile(): JsonResponse
    {
        $user = $this->authService->getProfile();

        return $this->successResponse('Profile retrieved successfully', $user);
    }
}
