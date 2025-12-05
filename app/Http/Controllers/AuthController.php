<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthResponseResource;
use App\Models\User;

/**
 * @OA\Tag(
 * name="Authentication",
 * description="Endpoints for user login, registration, and token management."
 * )
 */
class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * operationId="login",
     * tags={"Authentication"},
     * summary="Login and retrieve JWT",
     * description="Exchanges email and password for an access token (1 hr) and refresh token (7 days).",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email", "password"},
     * @OA\Property(property="email", type="string", format="email", example="admin@company.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful Login",
     * @OA\JsonContent(
     * @OA\Property(property="user", ref="#/components/schemas/UserResource"),
     * @OA\Property(
     * property="authorization",
     * type="object",
     * @OA\Property(property="token", type="string", example="eyJ0eX..."),
     * @OA\Property(property="refresh_token", type="string", example="def502..."),
     * @OA\Property(property="type", type="string", example="Bearer"),
     * @OA\Property(property="expires_in", type="integer", example=3600)
     * )
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Invalid Credentials"
     * ),
     * @OA\Response(
     * response=403,
     * description="Account is inactive"
     * )
     * )
     */
    public function login(LoginRequest $request)
    {
        // 1. Authenticate (Password check happens here or in Service)
        $user = User::where('email', $request->email)->first();

        if (!$user || !password_verify($request->password, $user->password_hash)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['error' => 'Account is inactive'], 403);
        }

        // 2. Generate Tokens
        $tokens = $this->authService->generateTokens($user);

        // 3. Return Formatted Response
        return new AuthResponseResource(array_merge(['user' => $user], $tokens));
    }

    /**
     * @OA\Post(
     * path="/api/register",
     * operationId="register",
     * tags={"Authentication"},
     * summary="Complete User Setup",
     * description="Used by employees to set their password after receiving an invite email.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email", "first_name", "last_name", "password", "password_confirmation"},
     * @OA\Property(property="email", type="string", format="email", example="new.hire@company.com"),
     * @OA\Property(property="first_name", type="string", example="John"),
     * @OA\Property(property="last_name", type="string", example="Doe"),
     * @OA\Property(property="password", type="string", format="password", minLength=8, example="Secret123!"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="Secret123!")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Account setup successful & Auto-Logged In",
     * @OA\JsonContent(
     * @OA\Property(property="user", ref="#/components/schemas/UserResource"),
     * @OA\Property(
     * property="authorization",
     * type="object",
     * @OA\Property(property="token", type="string"),
     * @OA\Property(property="refresh_token", type="string"),
     * @OA\Property(property="type", type="string", example="Bearer"),
     * @OA\Property(property="expires_in", type="integer")
     * )
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error (e.g. Passwords do not match or User not found)"
     * )
     * )
     */
    public function register(RegisterRequest $request)
    {
        // User exists (created by Admin), but needs password set.
        $user = User::where('email', $request->email)->firstOrFail();

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password_hash' => bcrypt($request->password), // or Hash::make()
        ]);

        // Auto-login after setup
        $tokens = $this->authService->generateTokens($user);

        return new AuthResponseResource(array_merge(['user' => $user], $tokens));
    }

    /**
     * @OA\Post(
     * path="/api/refresh-token",
     * operationId="refreshToken",
     * tags={"Authentication"},
     * summary="Rotate Refresh Token",
     * description="Exchange a valid refresh token for a new pair of access/refresh tokens.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"refresh_token"},
     * @OA\Property(property="refresh_token", type="string", example="def5020085...")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="New Tokens Issued",
     * @OA\JsonContent(
     * @OA\Property(property="access_token", type="string"),
     * @OA\Property(property="refresh_token", type="string"),
     * @OA\Property(property="token_type", type="string", example="Bearer"),
     * @OA\Property(property="expires_in", type="integer", example=3600)
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Invalid or expired token"
     * )
     * )
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
     * @OA\Post(
     * path="/api/logout",
     * operationId="logout",
     * tags={"Authentication"},
     * summary="Logout User",
     * description="Revokes all refresh tokens for the current user (Logout from all devices).",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Logged out successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Logged out successfully")
     * )
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function logout(Request $request)
    {
        $this->authService->logoutAllDevices($request->user());
        return response()->json(['message' => 'Logged out successfully']);
    }
}
