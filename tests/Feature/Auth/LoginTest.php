<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials()
    {
        // 1. Create User
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password_hash' => Hash::make('password123'),
        ]);

        // 2. Attempt Login
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // 3. Assert Response
        // FIX: Removed 'data' wrapper from assertion to match AppServiceProvider logic
        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'email',
                    'full_name',
                    'roles' // Included in UserResource
                ],
                'authorization' => [
                    'token',
                    'refresh_token',
                    'type',
                    'expires_in'
                ]
            ]);
    }

    public function test_inactive_user_cannot_login()
    {
        $user = User::factory()->inactive()->create([
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }
}
