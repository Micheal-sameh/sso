<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait HandlesApiResponses
{
    protected function ok(mixed $data, ?string $message = null): JsonResponse
    {
        return response()->json(['data' => $data, 'message' => $message], 200);
    }

    protected function created(mixed $data, ?string $message = null): JsonResponse
    {
        return response()->json(['data' => $data, 'message' => $message], 201);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    protected function error(string $message, int $status = 400): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }

    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return response()->json(['message' => $message], 401);
    }

    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return response()->json(['message' => $message], 403);
    }

    protected function notFound(string $message = 'Not found'): JsonResponse
    {
        return response()->json(['message' => $message], 404);
    }

    protected function validationError(array $errors): JsonResponse
    {
        return response()->json(['errors' => $errors], 422);
    }
}
