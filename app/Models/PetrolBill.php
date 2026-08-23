<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PetrolBill extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = "petrol_bills";

    protected $fillable = [
        'employee_id',
        'petrol_month_id',
        'date',
        'quantity',
        'rate',
        'amount',
        'total_amount',
        'total_quantity',
        'remarks',
        'isEdit',
    ];

    protected $casts = [
        'date'     => 'array',
        'quantity' => 'array',
        'rate'     => 'array',
        'amount'   => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function month()
    {
        return $this->belongsTo(PetrolMonth::class, 'petrol_month_id');
    }
}