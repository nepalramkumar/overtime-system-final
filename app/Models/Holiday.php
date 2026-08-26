<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'bs_year',
        'external_holiday_id',
        'source',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}