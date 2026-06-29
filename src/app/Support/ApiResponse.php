<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(mixed $data = null, array $meta = [], int $status = 200): JsonResponse 
    {
        return response()->json([
            'data' => $data,
            'meta' => $meta,
            'errors' => [],
        ], $status);
    }

    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse 
    {
        return response()->json([
            'data' => null,
            'meta' => [],
            'errors' => [
                [
                    'message' => $message,
                    'details' => $errors,
                ]
            ],
        ], $status);
    }
}