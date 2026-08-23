<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeShift extends Model
{
protected $fillable = ['day_name', 'start_time', 'end_time'];
}
