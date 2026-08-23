<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Position;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
         'email',
        'designation',
        'department',
        'ot_rate',
        'is_active',
        'employee_code',
        'external_staff_id',
        'position_id',
        'last_synced_at',
        'vehicle_no',
        'petrol_quantity_limit',
        'repair_expense_limit',
    ];

    public function repairExpenses()
    {
        return $this->hasMany(RepairExpense::class, 'employee_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function overtimeRecords()
    {
        return $this->hasMany(OvertimeRecord::class, 'employee_id');
    }
}