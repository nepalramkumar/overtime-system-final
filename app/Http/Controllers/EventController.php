<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Employee;
use App\Models\OvertimeRecord;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Services\OvertimeCalculator;

class EventController extends Controller
{
    private function isAdmin(): bool
    {
        return auth()->user()->role === 'admin';
    }

    // Event create गर्ने (creator) वा admin ले मात्र Submit गर्न पाउने
    // (created_by नभएका पुराना/legacy event हरूको लागि, events.manage भएको जो सुकैलाई अनुमति)
    private function canSubmit(Event $event): bool
    {
        if ($this->isAdmin()) return true;
        if (!$event->created_by) return true;
        return (int) $event->created_by === (int) auth()->id();
    }

    // Event मा तोकिएको recommender_employee_id सँग auth user को employee मिल्यो भने मात्र सिफारिस/reject(Submitted stage) गर्न पाउने
    private function isRecommenderOf(Event $event): bool
    {
        if ($this->isAdmin()) return true;
        $employeeId = auth()->user()->employee_id ?? null;
        return $employeeId && $event->recommender_employee_id && (int) $employeeId === (int) $event->recommender_employee_id;
    }

    // Event मा तोकिएको approver_employee_id सँग auth user को employee मिल्यो भने मात्र स्वीकृति/reject(Recommended stage) गर्न पाउने
    private function isApproverOf(Event $event): bool
    {
        if ($this->isAdmin()) return true;
        $employeeId = auth()->user()->employee_id ?? null;
        return $employeeId && $event->approver_employee_id && (int) $employeeId === (int) $event->approver_employee_id;
    }

    // Event को detail (सबै OT record) — creator, recommender, approver वा admin ले मात्र हेर्न पाउने
    private function canViewDetails(Event $event): bool
    {
        if ($this->isAdmin()) return true;
        if ($this->canSubmit($event)) return true; // creator
        if ($this->isRecommenderOf($event)) return true;
        if ($this->isApproverOf($event)) return true;
        return false;
    }

    // Event name click गर्दा popup मा देखाउने OT detail (JSON) — OT, खाजा, समय, मिति
    public function otDetails($id)
    {
        $event = Event::with('approver', 'recommender', 'creator')->findOrFail($id);

        if (!$this->canViewDetails($event)) {
            return response()->json(['error' => 'तपाईंलाई यो कार्यक्रमको विवरण हेर्ने अधिकार छैन।'], 403);
        }

        // Submit नहुँदासम्म Event creator वा admin ले यहीं बाटै अरूको OT पनि Edit/Delete गर्न पाउने
        $canManageOt = $event->isEditable() && ($this->isAdmin() || $this->canSubmit($event));

        $records = OvertimeRecord::with('employee.position')
            ->where('event_id', $event->id)
            ->orderBy('ot_date')
            ->get()
            ->map(function ($r) use ($canManageOt) {
                return [
                    'id'            => $r->id,
                    'employee_name' => $r->employee->name ?? 'N/A',
                    'employee_code' => $r->employee->employee_code ?? '-',
                    'position'      => $r->employee->position->name ?? '-',
                    'ot_date'       => adToBs($r->ot_date),
                    'from_time'     => $r->from_time,
                    'to_time'       => $r->to_time,
                    'total_hours'   => number_format($r->total_hours, 2),
                    'tiffin_amount' => number_format($r->tiffin_amount, 2),
                    'status'        => $r->status,
                    'remarks'       => $r->remarks,
                    'can_manage'    => $canManageOt,
                    'edit_url'      => $canManageOt ? route('overtime.edit', $r->id) : null,
                    'delete_url'    => $canManageOt ? route('overtime.destroy', $r->id) : null,
                ];
            });

        return response()->json([
            'event' => [
                'name'          => $event->event_name,
                'workflow_status' => $event->workflow_status ?? 'Draft',
                'creator'       => $event->creator->name ?? '-',
                'recommender'   => $event->recommender->name ?? '-',
                'approver'      => $event->approver->name ?? '-',
            ],
            'can_manage_ot' => $canManageOt,
            'records' => $records,
            'total_hours'  => number_format($records->sum(fn($r) => (float) str_replace(',', '', $r['total_hours'])), 2),
            'total_tiffin' => number_format($records->sum(fn($r) => (float) str_replace(',', '', $r['tiffin_amount'])), 2),
        ]);
    }

    // इभेन्ट लिस्ट हेर्नको लागि
    public function index()
    {
        $events = Event::orderBy('id', 'desc')->get();

        $statusBreakdown = OvertimeRecord::select('event_id', 'status', DB::raw('count(DISTINCT employee_id) as total'))
            ->whereNotNull('event_id')
            ->groupBy('event_id', 'status')
            ->get()
            ->groupBy('event_id');

        foreach ($events as $event) {
            $event->status_summary = $statusBreakdown->get($event->id, collect())
                ->pluck('total', 'status');

            // Workflow action button हरू देखाउने/लुकाउने निर्णयको लागि (view बाट private method access नहुने भएकोले)
            $event->can_submit    = $event->isEditable() && $this->canSubmit($event);
            $event->can_recommend = $event->workflow_status === Event::WF_SUBMITTED && $this->isRecommenderOf($event);
            $event->can_approve   = $event->workflow_status === Event::WF_RECOMMENDED && $this->isApproverOf($event);
            $event->can_reject    = in_array($event->workflow_status, [Event::WF_SUBMITTED, Event::WF_RECOMMENDED])
                                        && ($event->can_recommend || $event->can_approve || $this->isAdmin());
            $event->can_view_details = $this->canViewDetails($event);
        }

        return view('events.index', compact('events'));
    }

    public function toggleActive($id)
    {
        $event = Event::findOrFail($id);
        $event->is_active = !$event->is_active;
        $event->save();

        return redirect()->back()->with('success', $event->is_active ? 'Event Enable गरियो।' : 'Event Disable गरियो।');
    }

    // इभेन्ट दर्ता गर्ने फर्म देखाउनको लागि
    public function create()
    {
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        return view('events.create', compact('employees', 'departments'));
    }

    // डेटा सेभ गर्नको लागि
    public function store(Request $request)
    {
        $request->validate([
            'event_name' => 'required',
            'approver_employee_id' => 'nullable|exists:employees,id',
            'recommender_employee_id' => 'nullable|exists:employees,id',
        ]);
        
        $data = $request->all();
        $data['is_tiffin_eligible'] = $request->has('is_tiffin_eligible') ? true : false;

        $event = Event::create($data);
        // Submit गर्ने अधिकार यही बनाउने ले पाउने भएकोले creator track गर्ने (workflow_status default Draft नै रहन्छ)
        $event->created_by = auth()->id();
        $event->save();

        return redirect()->route('events.list')->with('success', 'कार्यक्रम दर्ता भयो!');
    }

    public function edit($id)
    {
        $event = Event::findOrFail($id);
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        return view('events.edit', compact('event', 'employees', 'departments'));
    }

   public function update(Request $request, $id, OvertimeCalculator $calculator)
{
    $event = Event::findOrFail($id);

    // Submit भइसकेको (Submitted/Recommended/Approved) Event लाई admin बाहेक अरूले edit गर्न नपाउने
    if (!$event->isEditable() && !$this->isAdmin()) {
        return redirect()->back()->with('error', 'यो कार्यक्रम Submit भइसकेकोले अहिले Edit गर्न मिल्दैन। Reject नभएसम्म पर्खनुहोस्।');
    }

    $request->validate([
        'event_name' => 'required',
        'approver_employee_id' => 'nullable|exists:employees,id',
        'recommender_employee_id' => 'nullable|exists:employees,id',
    ]);

    $data = $request->all();
    $data['is_tiffin_eligible'] = $request->has('is_tiffin_eligible') ? true : false;

    // १. इभेन्ट डेटा अपडेट गर्ने
    $event->update($data);

    $updatedCount = 0;

    // २. यदि admin ले verified रेकर्डहरू पनि अपडेट गर्ने भनेर कन्फर्म पठाएको छ भने
    if ($request->has('update_verified') && $request->input('update_verified') == 1) {
        // सबै (Verified सहित वा नभएका सबै) रेकर्डहरूको tiffin recalculate गर्ने
        $updatedCount = $calculator->recalculateTiffinForEvent($event->id, includeVerified: true);
    } else {
        // सामान्य अवस्थामा Verified बाहेक अरू सबैको tiffin recalculate गर्ने
        $updatedCount = $calculator->recalculateTiffinForEvent($event->id, includeVerified: false);

        // ३. Verified भएका records हरू छन् कि छैनन् चेक गर्ने
        $verifiedRecords = OvertimeRecord::where('event_id', $event->id)
            ->where('status', 'Verified')
            ->with('employee')
            ->get();

        // यदि Verified records छन् र admin ले अहिलेसम्म कन्फर्म गरेको छैन भने वार्निंग देखाउने
        if ($verifiedRecords->count() > 0 && !$request->has('checked_verified')) {
            return redirect()->back()->with([
                'warning_verified_records' => $verifiedRecords,
                'event_id' => $event->id,
                'event_data' => $request->all(),
                'success' => "कार्यक्रम अपडेट भयो र {$updatedCount} वटा OT record को खाजा रकम पुनः गणना गरियो।"
            ]);
        }
    }

    return redirect()->route('events.list')->with('success', "कार्यक्रम अपडेट भयो र {$updatedCount} वटा OT record को खाजा रकम पुनः गणना गरियो।");
}

    // Step 3: Event creator ले सबैको OT भरिसकेपछि Submit गर्ने — यसपछि यो Event अन्तर्गत नयाँ OT थप्न वा भएका edit गर्न सबैलाई रोकिन्छ
    // Employee ID बाट सम्बन्धित User (login account) पत्ता लगाउने — email/dashboard notification पठाउनको लागि
    private function userForEmployee($employeeId)
    {
        if (!$employeeId) return null;
        return \App\Models\User::where('employee_id', $employeeId)->first();
    }

    public function submit($id)
    {
        $event = Event::findOrFail($id);

        if (!$this->canSubmit($event)) {
            return redirect()->back()->with('error', 'यो कार्यक्रम Submit गर्ने अधिकार तपाईंलाई छैन। यो अधिकार कार्यक्रम बनाउने ब्यक्तिलाई मात्र छ।');
        }
        if (!$event->isEditable()) {
            return redirect()->back()->with('error', 'यो कार्यक्रम पहिले नै Submit भइसकेको छ।');
        }
        if (!$event->recommender_employee_id || !$event->approver_employee_id) {
            return redirect()->back()->with('error', 'Submit गर्नुअघि सिफारिस गर्ने र स्वीकृति गर्ने दुबै तोक्नुपर्छ।');
        }
        if (!OvertimeRecord::where('event_id', $event->id)->exists()) {
            return redirect()->back()->with('error', 'यो कार्यक्रममा अहिलेसम्म कुनै OT रेकर्ड भरिएको छैन।');
        }

        $event->workflow_status = Event::WF_SUBMITTED;
        $event->submitted_by = auth()->id();
        $event->submitted_at = now();
        $event->save();

        OvertimeRecord::where('event_id', $event->id)
            ->whereIn('status', [OvertimeRecord::ST_PENDING, OvertimeRecord::ST_REJECTED])
            ->update(['status' => OvertimeRecord::ST_SUBMITTED]);

        // सिफारिस गर्नेलाई email + dashboard message
        if ($recommenderUser = $this->userForEmployee($event->recommender_employee_id)) {
            $recommenderUser->notify(new \App\Notifications\EventSubmittedNotification($event));
        }

        return redirect()->back()->with('success', 'कार्यक्रम Submit गरियो, अब सिफारिस गर्नेको लागि पठाइयो। यो कार्यक्रममा थप OT entry/edit रोकियो।');
    }

    // Step 4: सिफारिस गर्नेले सिफारिस गर्ने
    public function recommend($id)
    {
        $event = Event::findOrFail($id);

        if (!$this->isRecommenderOf($event)) {
            return redirect()->back()->with('error', 'यो कार्यक्रम सिफारिस गर्ने अधिकार तपाईंलाई छैन।');
        }
        if ($event->workflow_status !== Event::WF_SUBMITTED) {
            return redirect()->back()->with('error', 'यो कार्यक्रम अहिले सिफारिसको लागि तयार अवस्थामा छैन।');
        }

        $event->workflow_status = Event::WF_RECOMMENDED;
        $event->recommended_by = auth()->id();
        $event->recommended_at = now();
        $event->save();

        OvertimeRecord::where('event_id', $event->id)
            ->where('status', OvertimeRecord::ST_SUBMITTED)
            ->update([
                'status'         => OvertimeRecord::ST_RECOMMENDED,
                'recommended_by' => auth()->id(),
                'recommended_at' => now(),
            ]);

        // स्वीकृति गर्नेलाई email + dashboard message
        if ($approverUser = $this->userForEmployee($event->approver_employee_id)) {
            $approverUser->notify(new \App\Notifications\EventRecommendedNotification($event));
        }

        return redirect()->back()->with('success', 'कार्यक्रम सिफारिस गरियो, अब स्वीकृतिको लागि पठाइयो।');
    }

    // Step 6: स्वीकृति गर्नेले स्वीकृत गर्ने — स्वीकृत भएपछि report मा reflect हुन्छ (Verified)
    public function approve($id)
    {
        $event = Event::findOrFail($id);

        if (!$this->isApproverOf($event)) {
            return redirect()->back()->with('error', 'यो कार्यक्रम स्वीकृत गर्ने अधिकार तपाईंलाई छैन।');
        }
        if ($event->workflow_status !== Event::WF_RECOMMENDED) {
            return redirect()->back()->with('error', 'यो कार्यक्रम अहिले स्वीकृतिको लागि तयार अवस्थामा छैन।');
        }

        $event->workflow_status = Event::WF_APPROVED;
        $event->approved_by = auth()->id();
        $event->approved_at = now();
        $event->save();

        OvertimeRecord::where('event_id', $event->id)
            ->where('status', OvertimeRecord::ST_RECOMMENDED)
            ->update([
                'status'      => OvertimeRecord::ST_VERIFIED,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

        return redirect()->back()->with('success', 'कार्यक्रम स्वीकृत गरियो। अब यसका OT रेकर्डहरू Report मा देखिनेछन्।');
    }

    // सिफारिस वा स्वीकृति — जुन stage मा भए पनि Reject गर्दा सिधा Draft (editable) मा फर्किन्छ (batch — सबै OT record सँगै)
    public function reject(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $stage = $event->workflow_status;
        $stageLabel = $stage === Event::WF_SUBMITTED ? 'सिफारिस' : 'स्वीकृति';

        if ($stage === Event::WF_SUBMITTED) {
            if (!$this->isRecommenderOf($event)) {
                return redirect()->back()->with('error', 'यो कार्यक्रम Reject गर्ने अधिकार तपाईंलाई छैन।');
            }
        } elseif ($stage === Event::WF_RECOMMENDED) {
            if (!$this->isApproverOf($event)) {
                return redirect()->back()->with('error', 'यो कार्यक्रम Reject गर्ने अधिकार तपाईंलाई छैन।');
            }
        } else {
            return redirect()->back()->with('error', 'यो कार्यक्रम अहिले Reject गर्न मिल्ने अवस्थामा छैन।');
        }

        // Notification पठाउनको लागि submit गर्ने User लाई field null हुनुअघि नै समात्ने
        $submitterUserId = $event->submitted_by ?: $event->created_by;

        $event->workflow_status = Event::WF_DRAFT;
        $event->submitted_by = null;
        $event->submitted_at = null;
        $event->recommended_by = null;
        $event->recommended_at = null;
        $event->rejected_by = auth()->id();
        $event->rejected_at = now();
        $event->rejection_reason = $request->reason;
        $event->save();

        // यो कार्यक्रम अन्तर्गतका सबै OT record फेरि editable बनाउने (batch)
        OvertimeRecord::where('event_id', $event->id)
            ->whereIn('status', [OvertimeRecord::ST_SUBMITTED, OvertimeRecord::ST_RECOMMENDED])
            ->update([
                'status'            => OvertimeRecord::ST_REJECTED,
                'rejection_reason'  => $request->reason,
                'rejected_by'       => auth()->id(),
                'rejected_at'       => now(),
                'recommended_by'    => null,
                'recommended_at'    => null,
            ]);

        // Submit गर्ने (creator) लाई email + dashboard message
        if ($submitterUser = \App\Models\User::find($submitterUserId)) {
            $submitterUser->notify(new \App\Notifications\EventRejectedNotification($event, $request->reason, $stageLabel));
        }

        return redirect()->back()->with('success', 'कार्यक्रम Reject गरियो, फेरि Edit गर्न मिल्ने भयो।');
    }
}