<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeatherApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test successful weather fetch for a valid city.
     */
    public function test_returns_weather_for_valid_city(): void
    {
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response([
                'name' => 'London',
                'main' => [
                    'temp' => 25.4,
                    'humidity' => 70,
                ],
                'weather' => [
                    ['description' => 'clear sky'],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/weather?city=London');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Weather fetched successfully',
                'data' => [
                    'city' => 'London',
                    'temperature' => 25,
                    'humidity' => 70,
                    'description' => 'clear sky',
                ],
            ]);
    }

    /**
     * Test validation error when city is missing.
     */
    public function test_validates_missing_city(): void
    {
        $response = $this->getJson('/api/weather');

        $response->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'The city parameter is required.',
                'data' => null,
            ]);
    }

    /**
     * Test validation error when city exceeds max length.
     */
    public function test_validates_city_max_length(): void
    {
        $response = $this->getJson('/api/weather?city='.str_repeat('a', 101));

        $response->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'The city must not exceed 100 characters.',
                'data' => null,
            ]);
    }

    /**
     * Test error response for invalid or unknown city.
     */
    public function test_returns_error_for_invalid_city(): void
    {
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response([
                'cod' => '404',
                'message' => 'city not found',
            ], 404),
        ]);

        $response = $this->getJson('/api/weather?city=InvalidCityXYZ');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'city not found',
                'data' => null,
            ]);
    }

    /**
     * Test error response when external API fails.
     */
    public function test_returns_error_when_external_api_fails(): void
    {
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response(null, 500),
        ]);

        $response = $this->getJson('/api/weather?city=London');

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'data' => null,
            ]);
    }

    /**
     * Test that results are cached (second request does not hit HTTP).
     */
    public function test_caches_weather_results(): void
    {
        Http::fake([
            'api.openweathermap.org/data/2.5/weather*' => Http::response([
                'name' => 'Paris',
                'main' => ['temp' => 20, 'humidity' => 65],
                'weather' => [['description' => 'cloudy']],
            ], 200),
        ]);

        $this->getJson('/api/weather?city=Paris');
        $this->getJson('/api/weather?city=Paris');

        Http::assertSentCount(1);
    }
}
