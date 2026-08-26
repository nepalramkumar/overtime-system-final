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

    // Event को "सिफारिस गर्ने" (Recommender) dropdown मा देखिने — Position Level ६ भन्दा माथिका मात्र
    public function scopeEligibleRecommenders($query)
    {
        return $query->where('is_active', true)
            ->whereHas('position', function ($q) {
                $q->where('level', '>', 6);
            });
    }

    // Event को "स्वीकृति गर्ने" (Approver) dropdown मा देखिने — Position Level १० भन्दा माथिका मात्र
    public function scopeEligibleApprovers($query)
    {
        return $query->where('is_active', true)
            ->whereHas('position', function ($q) {
                $q->where('level', '>', 10);
            });
    }
}