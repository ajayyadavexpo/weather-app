<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityService
{
    /**
     * Record an activity for a subject model.
     *
     * @throws \InvalidArgumentException
     */
    public function record(string $description, Model $subject): Activity
    {
        if (trim($description) === '') {
            throw new \InvalidArgumentException('Activity description must not be empty.');
        }

        $activity = Activity::query()->create([
            'description' => $description,
            'subject_id' => $subject->getKey(),
            'subject_type' => $subject->getMorphClass(),
        ]);

        Log::info('Activity recorded', [
            'description' => $description,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'activity_id' => $activity->getKey(),
        ]);

        return $activity;
    }
}

