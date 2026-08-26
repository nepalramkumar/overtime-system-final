<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    // Approval workflow का स्थिति (workflow_status column) — यी controller action बाटै मात्र बदलिन्छ,
    // form बाट mass-assign हुन नदिन जानाजान $fillable बाहिर राखिएको
    // (यो OT भरिसकेपछि चल्ने Submit→सिफारिस→स्वीकृति workflow को लागि हो)
    const WF_DRAFT       = 'Draft';
    const WF_SUBMITTED   = 'Submitted';
    const WF_RECOMMENDED = 'Recommended';
    const WF_APPROVED    = 'Approved';

    // Event-level Approval गेट — माथिको workflow_status भन्दा *छुट्टै/independent*।
    // Event बन्नासाथ सिफारिस गर्नेलाई यही गेटको लागि पठाइन्छ; यो Approve नभएसम्म कसैले पनि OT entry गर्न पाउँदैन।
    const EA_PENDING  = 'Pending';
    const EA_APPROVED = 'Approved';
    const EA_REJECTED = 'Rejected';

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
        'submitted_at'    => 'datetime',
        'recommended_at'  => 'datetime',
        'approved_at'     => 'datetime',
        'rejected_at'     => 'datetime',
        'event_approved_at' => 'datetime',
        'event_rejected_at' => 'datetime',
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

    // Submit नभएसम्म (Draft) मात्र Event को आफ्नै विवरण (नाम/मिति/विभाग आदि) Edit गर्न मिल्ने — Submitted/Recommended/Approved भएपछि locked
    // (यो OT भरिसकेपछि चल्ने workflow सँग सम्बन्धित — पुरानै व्यवहार, अपरिवर्तित)
    public function isEditable(): bool
    {
        return $this->workflow_status === self::WF_DRAFT || empty($this->workflow_status);
    }

    // OT Entry गर्न मिल्ने/नमिल्ने — Event बन्नासाथ सिफारिस गर्नेलाई पठाइने *छुट्टै* Approval गेट
    // (माथिको isEditable()/workflow_status सँग कुनै सम्बन्ध छैन — यो त्यसभन्दा पहिल्यै हुने gate हो)
    public function canEnterOt(): bool
    {
        return $this->event_approval_status === self::EA_APPROVED;
    }
}