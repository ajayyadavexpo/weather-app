<?php

namespace App\Repositories;

use App\Exceptions\ExternalApiException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Repository for weather data from external API.
 */
class WeatherRepository
{
    private const TIMEOUT_SECONDS = 5;

    public function __construct(
        private string $baseUrl,
        private string $apiKey,
    ) {}

    /**
     * Fetch raw weather data from the external API for the given city.
     *
     * @return array<string, mixed> Raw API response body
     *
     * @throws ExternalApiException When the request fails, times out, or city is invalid
     */
    public function getWeatherByCity(string $city): array
    {
        $url = rtrim($this->baseUrl, '/').'/weather';

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->get($url, [
                    'q' => $city,
                    'appid' => $this->apiKey,
                    'units' => 'metric',
                ]);

            if ($response->failed()) {
                $this->handleFailedResponse($response, $city);
            }

            $body = $response->json();
            if (! is_array($body)) {
                Log::warning('Weather API returned non-array response', ['city' => $city]);
                throw new ExternalApiException(
                    'Invalid response from weather service.',
                    502,
                );
            }

            if (isset($body['cod']) && (int) $body['cod'] >= 400) {
                $message = $body['message'] ?? 'City not found or invalid.';
                Log::info('Weather API returned error for city', [
                    'city' => $city,
                    'code' => $body['cod'],
                    'message' => $message,
                ]);
                throw new ExternalApiException(
                    $message,
                    0,
                    null,
                    (int) $body['cod'] === 404 ? 404 : 502,
                );
            }

            return $body;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Weather API connection failed', [
                'city' => $city,
                'message' => $e->getMessage(),
            ]);
            throw new ExternalApiException(
                'Unable to reach weather service. Please try again later.',
                0,
                $e,
                503,
            );
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Weather API request failed', [
                'city' => $city,
                'message' => $e->getMessage(),
            ]);
            throw new ExternalApiException(
                'Weather service request failed.',
                0,
                $e,
                502,
            );
        }
    }

    /**
     * Handle failed HTTP response (4xx/5xx).
     */
    private function handleFailedResponse(\Illuminate\Http\Client\Response $response, string $city): void
    {
        Log::warning('Weather API HTTP error', [
            'city' => $city,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        $status = $response->status();
        $body = $response->json();
        $message = $body['message'] ?? 'Weather service is temporarily unavailable.';

        if ($status === 401) {
            $message = 'Weather service configuration error.';
        } elseif ($status >= 500) {
            $message = 'Weather service is temporarily unavailable.';
        }

        throw new ExternalApiException($message, 0, null, $status >= 500 ? 502 : $status);
    }
}
