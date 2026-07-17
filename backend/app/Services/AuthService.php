<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Handle user registration and company creation
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $company = Company::create([
                'uuid' => Str::uuid(),
                'name' => $data['company_name'],
                'slug' => Str::slug($data['company_name']) . '-' . Str::random(5),
                'status' => 'active',
            ]);

            $user = User::create([
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'admin',
                'status' => 'active',
            ]);

            return $user;
        });
    }

    /**
     * Handle user login
     */
    public function login(array $credentials): ?string
    {
        if (!$token = auth('api')->attempt($credentials)) {
            return null;
        }

        // Update last login
        $user = auth('api')->user();
        $user->update(['last_login' => now()]);

        return $token;
    }

    /**
     * Handle user logout
     */
    public function logout(): void
    {
        auth('api')->logout();
    }

    /**
     * Handle token refresh
     */
    public function refresh(): string
    {
        return auth('api')->refresh();
    }

    /**
     * Get authenticated user profile
     */
    public function getProfile(): ?User
    {
        return auth('api')->user();
    }
}
