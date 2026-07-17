<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_create_company(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'company_name' => 'Acme Corporation',
            'name' => 'John Doe',
            'email' => 'john@acme.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('companies', [
            'name' => 'Acme Corporation',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@acme.com',
            'role' => 'admin',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $company = Company::create([
            'name' => 'Acme Corporation',
            'slug' => 'acme-corporation',
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'John Doe',
            'email' => 'john@acme.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'john@acme.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user',
                ],
            ]);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'nonexistent@acme.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials',
            ]);
    }
}
