<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RepairExpense extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = "repair_expenses";

    protected $fillable = [
        'employee_id',
        'fy_year',
        'date',
        'description',
        'amount',
        'total_amount',
        'remarks',
        'isEdit',
    ];

    protected $casts = [
        'date'        => 'array',
        'description' => 'array',
        'amount'      => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
