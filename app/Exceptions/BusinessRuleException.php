<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BusinessRuleException extends Exception
{
    /**
     * Report the exception.
     * Return false to stop Laravel from logging strictly logic errors (optional).
     */
    public function report(): bool
    {
        return true;
    }

    /**
     * Render the exception into an HTTP response.
     * Laravel 12 automatically calls this method if the exception is thrown.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'Business Logic Error',
            'message' => $this->getMessage(),
            'code' => 422
        ], 422);
    }
}
