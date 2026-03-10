<?php

namespace App\Http\Resources;

use App\Models\WeatherSearch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WeatherSearch
 */
class WeatherSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var WeatherSearch $search */
        $search = $this->resource;

        return [
            'id' => $search->id,
            'city' => $search->city,
            'source' => $this->normalizeSource($search->source),
            'created_at' => optional($search->created_at)?->toISOString(),
        ];
    }

    private function normalizeSource(?string $source): ?string
    {
        return match ($source) {
            WeatherSearch::SOURCE_EXTERNAL => 'external',
            WeatherSearch::SOURCE_CACHE => 'cache',
            default => $source,
        };
    }
}

