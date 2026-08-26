<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeRecord extends Model
{
    use HasFactory, SoftDeletes;

    // Approval workflow स्थिति — Event-based भए Event को workflow_status बाट cascade हुन्छ,
    // Individual (event_id null) भए यहीं सिधा चल्छ
    const ST_PENDING     = 'Pending';     // Draft, Event submit नभएसम्म
    const ST_SUBMITTED   = 'Submitted';
    const ST_RECOMMENDED = 'Recommended';
    const ST_VERIFIED    = 'Verified';    // = Approved (report मा देखिने अन्तिम स्थिति)
    const ST_REJECTED    = 'Rejected';    // Reject भएपछि फेरि editable

    // Note: workflow-tracking fields (status, verified_by/at, rejected_by/at, rejection_reason,
    // recommended_by/at) are set by the controller only, via direct property assignment + save()
    // (same pattern as Event model) — recommended_by/recommended_at are intentionally left OUT of
    // $fillable so a mass ->update([...]) call can never silently mis-set them from a form.
    protected $fillable = [
    'entry_group',
    'employee_id',
    'event_id',
    'purpose_id',
    'ot_date',
    'from_time',
    'to_time',
    'total_hours',
    'designation_snapshot',
    'ot_rate_snapshot',
    'tiffin_amount',
    'is_holiday',
    'type',
    'status',
    'remarks',
    'verified_by',
    'verified_at',
    'rejection_reason',
    'rejected_by',
    'rejected_at',
    'recommender_employee_id',
    'approver_employee_id',
];

    protected $casts = [
        'recommended_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function event()
    {
        return $this->belongsTo(\App\Models\Event::class, 'event_id');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
    public function rejecter()
{
    return $this->belongsTo(User::class, 'rejected_by');
}
public function purpose()
{
    return $this->belongsTo(Purpose::class);
}

    public function recommenderEmployee()
    {
        return $this->belongsTo(Employee::class, 'recommender_employee_id');
    }
    public function approverEmployee()
    {
        return $this->belongsTo(Employee::class, 'approver_employee_id');
    }
    public function recommendedByUser()
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }

    // पूरा audit trail (कस-ले, कहिले, कुन stage मा के गर्‍यो) — history table मा
    public function statusLogs()
    {
        return $this->hasMany(OvertimeStatusLog::class)->orderBy('created_at', 'desc');
    }

    // Verified नभएसम्म editable — Submitted/Recommended भएको बेला (Event locked भएको बेला) भने locked
    public function isEditable(): bool
    {
        if (in_array($this->status, [self::ST_VERIFIED, self::ST_SUBMITTED, self::ST_RECOMMENDED])) {
            return false;
        }
        return true;
    }
}