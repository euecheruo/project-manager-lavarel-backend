<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Bearer token missing'
            ], 401);
        }

        try {
            $secret = config('jwt.secret', env('JWT_SECRET'));
            $credentials = JWT::decode($token, new Key($secret, 'HS256'));

            $user = User::with(['roles.permissions'])
                ->where('user_id', $credentials->sub)
                ->first();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            if (!$user->is_active) {
                return response()->json([
                    'error' => 'Account Suspended',
                    'message' => 'Your account is inactive. Please contact an administrator.'
                ], 403);
            }

            $request->setUserResolver(fn() => $user);
            Auth::setUser($user);

        } catch (ExpiredException $e) {
            return response()->json([
                'error' => 'Token Expired',
                'message' => 'Please refresh your token.'
            ], 401);
        } catch (SignatureInvalidException $e) {
            return response()->json([
                'error' => 'Token Invalid',
                'message' => 'The token signature could not be verified.'
            ], 401);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Unauthorized token',
                'message' => 'The token is malformed or invalid.'
            ], 401);
        }

        return $next($request);
    }
}
