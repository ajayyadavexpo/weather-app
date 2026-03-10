<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Standardized API response helper.
 *
 * Ensures consistent JSON structure across all API endpoints:
 * - success: boolean
 * - message: string
 * - data: mixed (resource, array, or null)
 */
class ApiResponse
{
    /**
     * Return a successful JSON response.
     *
     * @param  JsonResource|array|null  $data
     */
    public static function success(
        JsonResource|array|null $data = null,
        string $message = 'Operation completed successfully.',
        int $status = 200,
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data instanceof JsonResource ? $data->toArray(request()) : $data,
        ];

        return response()->json($payload, $status);
    }

    /**
     * Return an error JSON response.
     */
    public static function error(
        string $message = 'An error occurred.',
        int $status = 400,
        ?array $errors = null,
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
            'data' => null,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
