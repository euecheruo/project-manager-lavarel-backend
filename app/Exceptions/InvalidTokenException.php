<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvalidTokenException extends Exception
{
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'Unauthorized',
            'message' => $this->getMessage() ?: 'Token is invalid or expired.',
        ], 401);
    }
}
