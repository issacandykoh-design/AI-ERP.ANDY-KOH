<?php

namespace Open\RestAPI;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    /**
     * Create a standardized API response.
     *
     * @param mixed $data
     * @param array $meta
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public static function make($data = null, array $meta = [], int $status = 200, array $headers = []): JsonResponse
    {
        $response = [
            'status' => $status >= 200 && $status < 300 ? 'success' : 'error',
            'data' => $data,
        ];

        // Handle pagination
        if ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->items();
            $response['pagination'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'has_more_pages' => $data->hasMorePages(),
            ];
        }

        // Add meta information
        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        // Add timestamp
        $response['timestamp'] = now()->toISOString();

        return response()->json($response, $status, $headers);
    }

    /**
     * Create a success response.
     *
     * @param mixed $data
     * @param array $meta
     * @param int $status
     * @return JsonResponse
     */
    public static function success($data = null, array $meta = [], int $status = 200): JsonResponse
    {
        return self::make($data, $meta, $status);
    }

    /**
     * Create an error response.
     *
     * @param string $message
     * @param array $errors
     * @param int $status
     * @return JsonResponse
     */
    public static function error(string $message, array $errors = [], int $status = 400): JsonResponse
    {
        $meta = [
            'message' => $message,
        ];

        if (!empty($errors)) {
            $meta['errors'] = $errors;
        }

        return self::make(null, $meta, $status);
    }

    /**
     * Create a validation error response.
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::error($message, $errors, 422);
    }

    /**
     * Create an unauthorized response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, [], 401);
    }

    /**
     * Create a forbidden response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, [], 403);
    }

    /**
     * Create a not found response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, [], 404);
    }

    /**
     * Create an internal server error response.
     *
     * @param string $message
     * @return JsonResponse
     */
    public static function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return self::error($message, [], 500);
    }
}
