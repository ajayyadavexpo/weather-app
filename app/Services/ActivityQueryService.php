<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\WeatherSearch;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Query service for fetching activity logs.
 *
 * Keeps controllers thin by encapsulating read/query concerns.
 */
class ActivityQueryService
{
    /**
     * Fetch paginated activities (latest first).
     */
    public function paginateActivities(int $perPage = 15): LengthAwarePaginator
    {
        return Activity::query()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Fetch a single activity by ID.
     */
    public function getActivityById(int $id): ?Activity
    {
        return Activity::query()->find($id);
    }

    /**
     * Fetch all weather searches (latest first).
     *
     * @return Collection<int, WeatherSearch>
     */
    public function getWeatherSearches(): Collection
    {
        return WeatherSearch::query()
            ->latest()
            ->get();
    }

    /**
     * Fetch activities for a specific weather search (latest first).
     *
     * @return Collection<int, Activity>
     */
    public function getActivitiesForWeatherSearch(WeatherSearch $weatherSearch): Collection
    {
        return $weatherSearch->activities()
            ->latest()
            ->get();
    }
}

