<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Return a successful JSON response.
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $status);
    }

    /**
     * Return an error JSON response.
     */
    public static function error(
        ?string $message = null,
        mixed $errors = null,
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Return a validation error JSON response.
     * @param array<string, mixed|null> $errors
     * @param string|null $message
     * @param int $status
     * @return JsonResponse
     */
    public static function validation(array $errors, ?string $message = 'Validation failed', int $status = 422): JsonResponse
    {
        return self::error($message, $errors, $status);
    }
}
