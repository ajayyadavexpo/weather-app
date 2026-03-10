<?php

namespace App\Http\Resources;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Activity
 */
class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Activity $activity */
        $activity = $this->resource;

        return [
            'id' => $activity->id,
            'description' => $activity->description,
            'subject_type' => class_basename($activity->subject_type),
            'subject_id' => $activity->subject_id,
            'created_at' => optional($activity->created_at)?->toISOString(),
        ];
    }
}

