<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Exception thrown when external API communication fails.
 *
 * Used for timeouts, API errors, invalid responses, or when the requested
 * resource (e.g. city) is not found.
 */
class ExternalApiException extends Exception
{
    /**
     * HTTP status code to return in the API response.
     */
    protected int $statusCode = 502;

    public function __construct(
        string $message = 'External weather service is temporarily unavailable.',
        int $code = 0,
        ?\Throwable $previous = null,
        int $statusCode = 502,
    ) {
        parent::__construct($message, $code, $previous);
        $this->statusCode = $statusCode;
    }

    /**
     * Get the HTTP status code for the exception.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
            'data' => null,
        ], $this->statusCode);
    }

    /**
     * Report the exception to the Laravel logger.
     */
    public function report(): bool
    {
        \Illuminate\Support\Facades\Log::warning('External API exception', [
            'message' => $this->getMessage(),
            'status_code' => $this->statusCode,
        ]);

        return true;
    }
}
