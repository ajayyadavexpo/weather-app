<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\WeatherSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_activities_paginated(): void
    {
        $search = WeatherSearch::query()->create([
            'city' => 'London',
            'source' => WeatherSearch::SOURCE_EXTERNAL,
        ]);

        Activity::query()->create([
            'description' => 'Weather fetched for London',
            'subject_type' => $search->getMorphClass(),
            'subject_id' => $search->getKey(),
        ]);

        $response = $this->getJson('/api/activities');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Activities fetched successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_shows_single_activity(): void
    {
        $search = WeatherSearch::query()->create([
            'city' => 'London',
            'source' => WeatherSearch::SOURCE_EXTERNAL,
        ]);

        $activity = Activity::query()->create([
            'description' => 'Weather fetched for London',
            'subject_type' => $search->getMorphClass(),
            'subject_id' => $search->getKey(),
        ]);

        $response = $this->getJson("/api/activities/{$activity->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Activity fetched successfully',
                'data' => [
                    'id' => $activity->id,
                    'description' => 'Weather fetched for London',
                    'subject_type' => 'WeatherSearch',
                    'subject_id' => $search->id,
                ],
            ]);
    }

    public function test_shows_404_for_missing_activity(): void
    {
        $response = $this->getJson('/api/activities/999999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Activity not found',
                'data' => null,
            ]);
    }

    public function test_lists_weather_searches(): void
    {
        WeatherSearch::query()->create([
            'city' => 'Paris',
            'source' => WeatherSearch::SOURCE_CACHE,
        ]);

        $response = $this->getJson('/api/weather-searches');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Weather searches fetched successfully',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_lists_activities_for_a_weather_search(): void
    {
        $search = WeatherSearch::query()->create([
            'city' => 'London',
            'source' => WeatherSearch::SOURCE_EXTERNAL,
        ]);

        Activity::query()->create([
            'description' => 'Weather fetched for London',
            'subject_type' => $search->getMorphClass(),
            'subject_id' => $search->getKey(),
        ]);

        $response = $this->getJson("/api/weather-searches/{$search->id}/activities");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Activities fetched successfully',
            ])
            ->assertJsonCount(1, 'data');
    }
}

