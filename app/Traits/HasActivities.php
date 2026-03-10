<?php

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Add polymorphic activities to a model.
 *
 * Usage:
 * - use HasActivities;
 * - $model->activities()->create(['description' => '...']);
 */
trait HasActivities
{
    /**
     * Get all activities for this model.
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}

