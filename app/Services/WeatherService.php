<?php

namespace App\Services;

use App\DTO\WeatherDTO;
use App\Exceptions\ExternalApiException;
use App\Models\WeatherSearch;
use App\Repositories\WeatherRepository;
use Illuminate\Support\Facades\Cache;

/**
 * Business logic for weather data.
 *
 * Handles caching, coordinates repository calls, and maps API responses to DTOs.
 */
class WeatherService
{
    /**
     * Cache TTL in seconds (10 minutes).
     */
    private const CACHE_TTL_SECONDS = 600;

    public function __construct(
        private ActivityService $activityService,
        private WeatherRepository $weatherRepository,
    ) {}

    /**
     * Get weather for a city. Results are cached for 10 minutes.
     *
     * @throws ExternalApiException When the external API fails or city is invalid
     */
    public function getWeatherByCity(string $city): WeatherDTO
    {
        $cacheKey = $this->getCacheKey($city);

        /** @var array{data: array<string,mixed>, city: string}|null $cached */
        $cached = Cache::get($cacheKey);
        if (is_array($cached) && isset($cached['data']) && is_array($cached['data'])) {
            $search = WeatherSearch::query()->create([
                'city' => $city,
                'source' => WeatherSearch::SOURCE_CACHE,
            ]);

            $this->activityService->record("User searched weather for {$city}", $search);
            $this->activityService->record("Weather data served from cache for {$city}", $search);

            return WeatherDTO::fromArray($cached['data']);
        }

        $search = WeatherSearch::query()->create([
            'city' => $city,
            'source' => WeatherSearch::SOURCE_EXTERNAL,
        ]);

        $this->activityService->record("User searched weather for {$city}", $search);

        $raw = $this->weatherRepository->getWeatherByCity($city);
        $dto = $this->mapResponseToDTO($raw);

        $this->activityService->record("Weather fetched for {$city}", $search);

        Cache::put($cacheKey, ['data' => $dto->toArray()], self::CACHE_TTL_SECONDS);

        return $dto;
    }

    /**
     * Build cache key for a city (normalized for case and spacing).
     */
    private function getCacheKey(string $city): string
    {
        $normalized = strtolower(trim($city));
        return 'weather:'.$normalized;
    }

    /**
     * Map external API response array to WeatherDTO.
     */
    private function mapResponseToDTO(array $data): WeatherDTO
    {
        $city = $data['name'] ?? '';
        $temperature = (int) round($data['main']['temp'] ?? 0);
        $humidity = (int) ($data['main']['humidity'] ?? 0);
        $description = $data['weather'][0]['description'] ?? '';

        return new WeatherDTO(
            city: $city,
            temperature: $temperature,
            humidity: $humidity,
            description: $description,
        );
    }
}
