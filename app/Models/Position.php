<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

protected $fillable = [
    'name',
    'ot_rate',
    'is_active',
    'level',
];
    protected function casts(): array
    {
        return [
            'ot_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}