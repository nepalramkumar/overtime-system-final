<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['date', 'name', 'bs_year'];

    protected $casts = [
        'date' => 'date',
    ];
}
