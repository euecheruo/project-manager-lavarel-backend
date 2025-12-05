<?php

namespace App\Services;

use App\Models\User;
use App\Models\RefreshToken;
use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AuthService
{
    /**
     * Generate a new pair of Access (JWT) and Refresh (Database) tokens.
     */
    public function generateTokens(User $user): array
    {
        $payload = [
            'iss' => config('app.url'),          
            'sub' => $user->user_id,             
            'role' => $user->roles->pluck('role_name'),
            'iat' => time(),                     
            'exp' => time() + (60 * 60)          
        ];

        $accessToken = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

        $rawRefreshToken = Str::random(64);
        
        RefreshToken::create([
            'user_id' => $user->user_id,
            'token_hash' => hash('sha256', $rawRefreshToken),
            'expires_at' => Carbon::now()->addDays(7),
            'is_revoked' => false
        ]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $rawRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600
        ];
    }

    /**
     * Rotate Tokens: Invalidate old refresh token, issue new pair.
     */
    public function rotateToken(string $rawRefreshToken): array
    {
        $hash = hash('sha256', $rawRefreshToken);

        $storedToken = RefreshToken::where('token_hash', $hash)->first();

        if (!$storedToken || !$storedToken->isValid()) {
            throw new \Exception('Invalid or expired refresh token.');
        }

        $storedToken->revoke();

        return $this->generateTokens($storedToken->user);
    }

    /**
     * Revoke all tokens for a user (Logout everywhere).
     */
    public function logoutAllDevices(User $user): void
    {
        $user->refreshTokens()->update(['is_revoked' => true]);
    }
}
