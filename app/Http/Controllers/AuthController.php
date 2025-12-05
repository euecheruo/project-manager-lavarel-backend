<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthResponseResource;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {
    }

    /**
     * Public: Exchange email/password for Access + Refresh tokens.
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !password_verify($request->password, $user->password_hash)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['error' => 'Account is inactive'], 403);
        }

        $tokens = $this->authService->generateTokens($user);

        return new AuthResponseResource(array_merge(['user' => $user], $tokens));
    }

    /**
     * Public: Complete account setup (Set password for invited user).
     */
    public function register(RegisterRequest $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password_hash' => bcrypt($request->password),
        ]);

        // Auto-login after setup
        $tokens = $this->authService->generateTokens($user);

        return new AuthResponseResource(array_merge(['user' => $user], $tokens));
    }

    /**
     * Protected: Rotate the Refresh Token.
     */
    public function refresh(Request $request)
    {
        $refreshToken = $request->input('refresh_token');

        try {
            $tokens = $this->authService->rotateToken($refreshToken);
            return response()->json($tokens);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid or expired token'], 401);
        }
    }

    /**
     * Protected: Logout (Revoke all tokens).
     */
    public function logout(Request $request)
    {
        $this->authService->logoutAllDevices($request->user());
        return response()->json(['message' => 'Logged out successfully']);
    }
}
