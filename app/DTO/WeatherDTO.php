<?php

namespace App\DTO;

/**
 * Data Transfer Object for weather data.
 *
 * Represents a structured, immutable representation of weather information
 * returned from the external weather API.
 */
readonly class WeatherDTO
{
    public function __construct(
        public string $city,
        public int $temperature,
        public int $humidity,
        public string $description,
    ) {}

    /**
     * Create a WeatherDTO from an array (e.g. API response).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            city: $data['city'] ?? '',
            temperature: (int) ($data['temperature'] ?? 0),
            humidity: (int) ($data['humidity'] ?? 0),
            description: $data['description'] ?? '',
        );
    }

    /**
     * Convert DTO to array for caching and serialization.
     */
    public function toArray(): array
    {
        return [
            'city' => $this->city,
            'temperature' => $this->temperature,
            'humidity' => $this->humidity,
            'description' => $this->description,
        ];
    }
}
