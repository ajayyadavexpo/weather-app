<?php

namespace App\Http\Resources;

use App\DTO\WeatherDTO;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WeatherResource extends JsonResource
{
    public function __construct(WeatherDTO $resource)
    {
        parent::__construct($resource);
    }

     public function toArray(Request $request): array
    {
        $dto = $this->resource;
        
        return [
            'city' => $dto->city,
            'temperature' => $dto->temperature,
            'humidity' => $dto->humidity,
            'description' => $dto->description,
        ];
    }
}
