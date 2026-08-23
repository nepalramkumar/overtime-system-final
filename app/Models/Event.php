<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    // Approval workflow का स्थिति (workflow_status column) — यी controller action बाटै मात्र बदलिन्छ,
    // form बाट mass-assign हुन नदिन जानाजान $fillable बाहिर राखिएको
    const WF_DRAFT       = 'Draft';
    const WF_SUBMITTED   = 'Submitted';
    const WF_RECOMMENDED = 'Recommended';
    const WF_APPROVED    = 'Approved';

    protected $fillable = [
        'event_name',
        'department',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_tiffin_eligible',
        'approver_employee_id',
        'recommender_employee_id',
    ];

    protected $casts = [
        'submitted_at'  => 'datetime',
        'recommended_at' => 'datetime',
        'approved_at'   => 'datetime',
        'rejected_at'   => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_employee_id');
    }
    public function recommender()
{
    return $this->belongsTo(\App\Models\Employee::class, 'recommender_employee_id');
}

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function submittedByUser()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
    public function recommendedByUser()
    {
        return $this->belongsTo(User::class, 'recommended_by');
    }
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function rejectedByUser()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Submit नभएसम्म (Draft) मात्र OT entry/edit गर्न मिल्ने — Submitted/Recommended/Approved भएपछि locked
    public function isEditable(): bool
    {
        return $this->workflow_status === self::WF_DRAFT || empty($this->workflow_status);
    }
}