<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\WeatherRequest;
use App\Http\Resources\WeatherResource;
use App\Services\WeatherService;

class WeatherController extends Controller
{
    public function __construct(
        private WeatherService $weatherService,
    ) {}


    public function show(WeatherRequest $request)
    {
        $city = $request->validated('city');
        $weather = $this->weatherService->getWeatherByCity($city);
        
        return ApiResponse::success(
            new WeatherResource($weather),
            'Weather fetched successfully',
        );
    }
}
