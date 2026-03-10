<?php

namespace App\Models;

use App\Traits\HasActivities;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherSearch extends Model
{
    use HasActivities;
    use HasFactory;

    public const SOURCE_CACHE = 'cache';
    public const SOURCE_EXTERNAL = 'external_api';

     protected $fillable = [
        'city',
        'source',
    ];
}
