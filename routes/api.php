<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\WeatherSearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/weather', [WeatherController::class, 'show']);

Route::get('/activities', [ActivityController::class, 'index']);
Route::get('/activities/{id}', [ActivityController::class, 'show'])
    ->whereNumber('id');

Route::get('/weather-searches', [WeatherSearchController::class, 'index']);
Route::get('/weather-searches/{id}/activities', [WeatherSearchController::class, 'activities'])
    ->whereNumber('id');
