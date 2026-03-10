# Weather API Service

A production-quality Laravel REST API that fetches weather information from OpenWeatherMap and returns structured JSON. Built to demonstrate clean architecture, layered design, and senior-level Laravel practices.

## Architecture

```
app/
├── Http/
│   ├── Controllers/
│   │     WeatherController.php    # Thin controller: receive → delegate → respond
│   │     ActivityController.php   # Read-only activity endpoints
│   │     WeatherSearchController.php # Read-only weather-search endpoints
│   ├── Requests/
│   │     WeatherRequest.php      # FormRequest validation
│   ├── Resources/
│   │     WeatherResource.php     # API response transformation
│   │     ActivityResource.php    # Activity serialization
│   │     WeatherSearchResource.php # WeatherSearch serialization
│   ├── Services/
│   │     WeatherService.php      # Business logic, caching, DTO mapping
│   │     ActivityService.php     # Writes activity logs
│   │     ActivityQueryService.php # Reads activity logs
│   ├── Repositories/
│   │     WeatherRepository.php   # External API communication (HTTP client)
│   ├── DTO/
│   │     WeatherDTO.php          # Data transfer object for weather data
│   ├── Exceptions/
│   │     ExternalApiException.php # Custom exception for API failures
│   └── Helpers/
│         ApiResponse.php         # Standardized success/error responses
├── Models/
│     Activity.php                # Polymorphic activity log model
│     WeatherSearch.php           # Represents a weather query (activity subject)
├── Traits/
│     HasActivities.php           # Reusable polymorphic relationship
```

- **Controller**: Type-hints `WeatherRequest`, calls `WeatherService`, returns `ApiResponse`.
- **Service**: Caching (10 min), calls repository, maps response to `WeatherDTO`.
- **Repository**: Laravel HTTP client, timeout 5s, handles errors and invalid city.
- **DTO**: Immutable representation of weather data.
- **Resource**: Transforms DTO to API JSON shape.

## Requirements

- PHP 8.2+
- Composer
- [OpenWeatherMap API key](https://openweathermap.org/api) (free tier is sufficient)

## Installation

```bash
# Clone or navigate to the project
cd weather-api

# Install dependencies (already done if created via composer create-project)
composer install

# Copy environment file and add your API key
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set:

```env
WEATHER_API_KEY=your_openweathermap_api_key
WEATHER_BASE_URL=https://api.openweathermap.org/data/2.5
```

Get an API key at [OpenWeatherMap](https://openweathermap.org/api).

## Running the API

```bash
php artisan serve
```

Default URL: `http://127.0.0.1:8000`

## API Endpoint

### GET /api/weather

Returns current weather for a city.

**Query parameters**

| Parameter | Required | Rules        | Description   |
|-----------|----------|---------------|---------------|
| `city`    | Yes      | string, max 100 | City name (e.g. London, Paris) |

**Example request (cURL)**

```bash
curl -X GET "http://127.0.0.1:8000/api/weather?city=London"
```

**Example response (200)**

```json
{
  "success": true,
  "message": "Weather fetched successfully",
  "data": {
    "city": "London",
    "temperature": 25,
    "humidity": 70,
    "description": "clear sky"
  }
}
```

**Validation error (422 – missing city)**

```json
{
  "success": false,
  "message": "The city parameter is required.",
  "data": null
}
```

**Not found / invalid city (404)**

```json
{
  "success": false,
  "message": "city not found",
  "data": null
}
```

**External API failure (502)**

```json
{
  "success": false,
  "message": "Weather service is temporarily unavailable.",
  "data": null
}
```

## Activity Log (Polymorphic)

This project includes a **polymorphic Activity Log system** to demonstrate scalable audit/event tracking.

- **Activity**: `morphTo subject` (e.g. `WeatherSearch`, or any other model in the future)
- **WeatherSearch**: `morphMany activities`
- **HasActivities trait**: reusable relationship to attach activity logging to any model

### Activity endpoints

#### GET /api/activities

Returns a paginated list of activities (latest first).

**Example request**

```bash
curl -X GET "http://127.0.0.1:8000/api/activities"
```

**Example response (200)**

```json
{
  "success": true,
  "message": "Activities fetched successfully",
  "data": [
    {
      "id": 1,
      "description": "Weather fetched for London",
      "subject_type": "WeatherSearch",
      "subject_id": 5,
      "created_at": "2026-03-10T10:30:00Z"
    }
  ]
}
```

#### GET /api/activities/{id}

```bash
curl -X GET "http://127.0.0.1:8000/api/activities/1"
```

#### GET /api/weather-searches

Returns all weather searches (latest first).

```bash
curl -X GET "http://127.0.0.1:8000/api/weather-searches"
```

Example item:

```json
{
  "id": 5,
  "city": "London",
  "source": "external",
  "created_at": "2026-03-10T10:30:00Z"
}
```

#### GET /api/weather-searches/{id}/activities

```bash
curl -X GET "http://127.0.0.1:8000/api/weather-searches/5/activities"
```

## Configuration

- **config/services.php**: `weather.key` and `weather.base_url` from env.
- **Caching**: Results cached per city for 10 minutes via the cache store.
- **HTTP**: 5-second timeout; failures and timeouts throw `ExternalApiException` and are logged.

## Running Tests

```bash
# All tests
php artisan test

# Only Weather API feature tests
php artisan test tests/Feature/WeatherApiTest.php

# Activity API feature tests
php artisan test tests/Feature/ActivityApiTest.php
```

**Test cases**

- Valid city returns 200 and correct JSON.
- Missing `city` returns 422 and validation errors.
- City over 100 characters returns 422.
- Invalid/unknown city returns 404 and error JSON.
- External API failure returns 502 and error JSON.
- Responses are cached (second request does not call HTTP).
- Activity endpoints return 200/404 with standardized JSON.

Tests use `Http::fake()` so no real API key is needed when running the suite.

## Code Quality

- **PSR-12** and Laravel conventions.
- **Dependency injection**: Controller → Service → Repository; repository bound in `AppServiceProvider`.
- **Docblocks** on classes and key methods.
- **Thin controller**: no business or HTTP logic in the controller.
- **Single responsibility**: Repository (API), Service (logic + cache), Controller (HTTP only).

## Namespaces

| Layer       | Namespace              |
|------------|-------------------------|
| Controller | `App\Http\Controllers`  |
| Request    | `App\Http\Requests`    |
| Resource   | `App\Http\Resources`   |
| Service    | `App\Services`          |
| Repository | `App\Repositories`      |
| DTO        | `App\DTO`               |
| Exception  | `App\Exceptions`       |
| Helper     | `App\Helpers`           |

## License

MIT.
