<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SnackAllowance extends Model
{
  protected $fillable = ['min_hours', 'max_hours', 'amount'];
}
