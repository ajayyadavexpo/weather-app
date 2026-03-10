<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\WeatherSearchResource;
use App\Models\WeatherSearch;
use App\Services\ActivityQueryService;
use Illuminate\Http\JsonResponse;


class WeatherSearchController extends Controller
{
    public function __construct(
        private readonly ActivityQueryService $activityQueryService,
    ) {}

    public function index(): JsonResponse
    {
        $searches = $this->activityQueryService->getWeatherSearches();

        return ApiResponse::success(
            WeatherSearchResource::collection($searches),
            'Weather searches fetched successfully',
        );
    }

    public function activities(int $id): JsonResponse
    {
        $search = WeatherSearch::query()->find($id);
        if ($search === null) {
            return ApiResponse::error('Weather search not found', 404);
        }

        $activities = $this->activityQueryService->getActivitiesForWeatherSearch($search);

        return ApiResponse::success(
            ActivityResource::collection($activities),
            'Activities fetched successfully',
        );
    }
}

