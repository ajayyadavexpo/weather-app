<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\ActivityResource;
use App\Services\ActivityQueryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityQueryService $activityQueryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $activities = $this->activityQueryService->paginateActivities(15);

        return ApiResponse::success(
            ActivityResource::collection($activities),
            'Activities fetched successfully',
        );
    }

    public function show(int $id): JsonResponse
    {
        $activity = $this->activityQueryService->getActivityById($id);
        if ($activity === null) {
            return ApiResponse::error('Activity not found', 404);
        }

        return ApiResponse::success(
            new ActivityResource($activity),
            'Activity fetched successfully',
        );
    }
}

