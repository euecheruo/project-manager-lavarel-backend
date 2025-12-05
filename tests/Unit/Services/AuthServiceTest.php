<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_tokens_creates_db_record()
    {
        $user = User::factory()->create();
        $service = new AuthService();

        // Run Logic
        $result = $service->generateTokens($user);

        // Assertions
        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('refresh_token', $result);

        // Check DB for Refresh Token Hash
        $this->assertDatabaseHas('refresh_tokens', [
            'user_id' => $user->user_id,
            'is_revoked' => false
        ]);
    }
}
