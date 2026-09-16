<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    /**
     * Return a standardized successful JSON API response.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function respondWithSuccess(
        mixed $data = null,
        string $message = 'Request completed successfully',
        int $statusCode = 200,
        array $meta = []
    ): JsonResponse {
        $payload = [
            'success' => true,
            'data' => $data ?? (object) [],
            'meta' => (object) $meta,
            'message' => $message,
        ];

        return response()->json($payload, $statusCode);
    }

    /**
     * Return a standardized 201 Created JSON API response.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function respondCreated(
        mixed $data = null,
        string $message = 'Resource created successfully',
        array $meta = []
    ): JsonResponse {
        return $this->respondWithSuccess($data, $message, 201, $meta);
    }

    /**
     * Return a standardized 204 No Content response.
     */
    protected function respondNoContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return a standardized error JSON API response.
     *
     * @param  array<string, list<string>>|null  $errors
     */
    protected function respondWithError(
        string $message,
        int $statusCode = 400,
        ?array $errors = null
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $statusCode);
    }

    /**
     * Return a standardized 401 Unauthenticated response.
     */
    protected function respondUnauthorized(string $message = 'Unauthenticated'): JsonResponse
    {
        return $this->respondWithError($message, 401);
    }

    /**
     * Return a standardized 403 Forbidden response.
     */
    protected function respondForbidden(string $message = 'This action is unauthorized.'): JsonResponse
    {
        return $this->respondWithError($message, 403);
    }

    /**
     * Return a standardized 404 Not Found response.
     */
    protected function respondNotFound(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->respondWithError($message, 404);
    }

    /**
     * Return a standardized 422 Validation Error response.
     *
     * @param  array<string, list<string>>  $errors
     */
    protected function respondValidationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->respondWithError($message, 422, $errors);
    }
}
