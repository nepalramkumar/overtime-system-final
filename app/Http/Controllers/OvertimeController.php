<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Event;
use App\Services\OvertimeCalculator;
use Exception;
use App\Models\OvertimeRecord;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OvertimeExport;
use App\Services\OvertimeWordService;
use App\Support\EmployeeSorter;

class OvertimeController extends Controller
{
    protected $calculator;

    public function __construct(OvertimeCalculator $calculator)
    {
        $this->calculator = $calculator;
    }
    private function canEnterForAnyone(): bool
{
    $role = auth()->user()->role;
    if ($role === 'admin') {
        return true;
    }
    return \App\Models\RolePermission::where('role', $role)
            ->where('permission', 'overtime.entry.all')
            ->exists();
}

// Report search box हरू (employee_search, event_search) query मा लागू गर्ने — पहिले यी field हरू कतै प्रयोग नै भएका थिएनन्
private function applyReportTextSearch($query, Request $request): void
{
    if ($request->filled('event_search')) {
        $term = trim($request->event_search);
        $query->whereHas('event', function ($q) use ($term) {
            $q->where('event_name', 'like', '%' . $term . '%');
        });
    }

    if ($request->filled('employee_search')) {
        $term = trim($request->employee_search);
        // Datalist बाट "नाम - पद (कोड: XXX)" जस्तो पूरै string आउन सक्छ, कोड निकालेर match गर्ने
        if (preg_match('/कोड:\s*([^\)]+)\)/u', $term, $m)) {
            $code = trim($m[1]);
            $query->whereHas('employee', function ($q) use ($code) {
                $q->where('employee_code', $code);
            });
        } else {
            $namePart = trim(explode(' - ', $term)[0]);
            $query->whereHas('employee', function ($q) use ($namePart) {
                $q->where('name', 'like', '%' . $namePart . '%')
                  ->orWhere('employee_code', 'like', '%' . $namePart . '%');
            });
        }
    }
}

// Event मा तोकिएको creator/recommender/approver सँग auth user को employee मिल्यो कि admin हो भने मात्र OT record edit/delete गर्न पाउने
// (आफ्नै record भए त सधैं मिल्छ नै; Event भित्रको अरूको record भने Event Submit नहुँदासम्म Event creator ले पनि सच्याउन पाउने)
private function canModifyRecord(OvertimeRecord $record): bool
{
    if ($this->canEnterForAnyone()) return true;
    if ((int) $record->employee_id === (int) (auth()->user()->employee_id ?? 0)) return true;

    if ($record->event_id && $record->event
        && $record->event->canEnterOt()
        && $record->event->created_by
        && (int) $record->event->created_by === (int) auth()->id()) {
        return true;
    }

    return false;
}
private function canVerify(): bool
{
    $role = auth()->user()->role;
    if ($role === 'admin') return true;
    return \App\Models\RolePermission::where('role', $role)->where('permission', 'overtime.verify')->exists();
}

private function canUnverify(): bool
{
    $role = auth()->user()->role;
    if ($role === 'admin') return true;
    return \App\Models\RolePermission::where('role', $role)->where('permission', 'overtime.unverify')->exists();
}

// Individual OT (event_id नभएको) मा तोकिएको recommender_employee_id सँग auth user को employee मिल्यो भने मात्र सिफारिस/reject गर्न पाउने
private function isRecommenderOf(OvertimeRecord $record): bool
{
    if (auth()->user()->role === 'admin') return true;
    $employeeId = auth()->user()->employee_id ?? null;
    return $employeeId && $record->recommender_employee_id && (int) $employeeId === (int) $record->recommender_employee_id;
}

// Individual OT मा तोकिएको approver_employee_id सँग auth user को employee मिल्यो भने मात्र स्वीकृति/reject गर्न पाउने
private function isApproverOf(OvertimeRecord $record): bool
{
    if (auth()->user()->role === 'admin') return true;
    $employeeId = auth()->user()->employee_id ?? null;
    return $employeeId && $record->approver_employee_id && (int) $employeeId === (int) $record->approver_employee_id;
}
public function create(Request $request)
{
    $canSelectAny = $this->canEnterForAnyone();

    if ($canSelectAny) {
        $employees = Employee::all();
        $lockedEmployee = null;
    } else {
        $lockedEmployee = Employee::where('id', auth()->user()->employee_id)->first();
        $employees = $lockedEmployee ? collect([$lockedEmployee]) : collect([]);

        if (!$lockedEmployee) {
            return redirect()->back()->with('error', 'तपाईंको User account कुनै Employee सँग link भएको छैन। कृपया Admin लाई सम्पर्क गर्नुहोस्।');
        }
    }

    $selectedEventId = $request->query('event_id');

    // Disabled event लाई URL बाट directly access गर्न रोक्ने
    if ($selectedEventId) {
        $selectedEvent = Event::find($selectedEventId);
        if (!$selectedEvent || !$selectedEvent->is_active) {
            return redirect()->route('events.list')->with('error', 'यो कार्यक्रम अहिले Disable भएको छ, नयाँ OT Entry गर्न मिल्दैन।');
        }
        if (!$selectedEvent->canEnterOt()) {
            return redirect()->route('events.list')->with('error', 'यो कार्यक्रम अझै Event Approval पर्खिरहेको छ, त्यसैले नयाँ OT Entry गर्न मिल्दैन।');
        }
        // Event Submit भइसकेपछि (workflow_status Draft मा नरहेपछि) नयाँ OT entry रोक्ने
        if (!$selectedEvent->isEditable()) {
            return redirect()->route('events.list')->with('error', 'यो कार्यक्रम पहिले नै Submit भइसकेकोले नयाँ OT Entry गर्न मिल्दैन।');
        }
    }

    $events = Event::orderBy('id', 'desc')->get();

    // सिफारिस/स्वीकृति गर्ने dropdown को लागि सधैं सबै employee चाहिन्छ ($employees canSelectAny=false हुँदा लक भएर एक जना मात्र हुनसक्छ)
    $allEmployees = Employee::where('is_active', true)->orderBy('name')->get();

    return view('overtime.create', compact('employees', 'events', 'selectedEventId', 'canSelectAny', 'lockedEmployee', 'allEmployees'));
}
    public function store(Request $request)
{
    $validated = $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'event_id'    => 'nullable|exists:events,id',
        'ot_date'     => 'required|date',
        'from_time'   => 'required',
        'to_time'     => 'required',
        // Individual OT (event_id नभएको) मा सिफारिस/स्वीकृति गर्ने दुबै अनिवार्य
        'recommender_employee_id' => 'required_without:event_id|nullable|exists:employees,id',
        'approver_employee_id'    => 'required_without:event_id|nullable|exists:employees,id',
    ]);

   // सुरक्षा जाँच: आफ्नै मात्र भर्न पाउने भए, अरूको employee_id manually पठाएर पनि रोक्ने
    if (!$this->canEnterForAnyone() && (int) $request->employee_id !== (int) auth()->user()->employee_id) {
        return redirect()->back()->with('error', 'तपाईं आफ्नो बाहेक अरूको OT भर्न पाउनुहुन्न।');
    }

    // Event disabled वा Submit भइसकेको (locked) भए, entry रोक्ने
    if ($request->filled('event_id')) {
        $event = Event::find($request->event_id);
        if (!$event || !$event->is_active) {
            return redirect()->back()->with('error', 'यो कार्यक्रम Disable भएको छ, OT Entry गर्न मिल्दैन।');
        }
        if (!$event->canEnterOt()) {
            return redirect()->back()->with('error', 'यो कार्यक्रम अझै Event Approval पर्खिरहेको छ, त्यसैले OT Entry गर्न मिल्दैन।');
        }
        // Event पहिले नै Submit भइसकेको (workflow Draft मा नरहेको) भए नयाँ OT entry रोक्ने
        if (!$event->isEditable()) {
            return redirect()->back()->with('error', 'यो कार्यक्रम पहिले नै Submit भइसकेकोले OT Entry गर्न मिल्दैन।');
        }
        // Event को date range भन्दा बाहिरको मितिमा OT भर्न नदिने
        if ($request->ot_date < $event->start_date || $request->ot_date > $event->end_date) {
            return redirect()->back()->with('error', 'यो कार्यक्रमको मिति ('.adToBs($event->start_date).' देखि '.adToBs($event->end_date).') भन्दा बाहिरको मितिमा OT भर्न मिल्दैन।');
        }
    }

    // Purpose disabled भए, entry रोक्ने
    if ($request->filled('purpose_id')) {
        $purpose = \App\Models\Purpose::find($request->purpose_id);
        if (!$purpose || !$purpose->is_active) {
            return redirect()->back()->with('error', 'यो Purpose Disable भएको छ, OT Entry गर्न मिल्दैन।');
        }
    }

    try {
        $employee = Employee::findOrFail($request->employee_id);

        $additionalData = [
            'event_id'   => $request->event_id,
            'ot_date'    => $request->ot_date,
            'from_time'  => $request->from_time,
            'to_time'    => $request->to_time,
            // Admin ले मात्र manual override पठाउन पाउने, अरूको लागि auto-detect नै अन्तिम हुन्छ
            'is_holiday_override' => (auth()->user()->role === 'admin' && $request->has('is_holiday_override')) ? $request->is_holiday_override : null,
            'remarks'    => $request->remarks,
            'purpose_id' => $request->purpose_id,
            'recommender_employee_id' => $request->recommender_employee_id,
            'approver_employee_id'    => $request->approver_employee_id,
        ];

      $newRecord = $this->calculator->calculateAndSave($additionalData, $employee);
return redirect()->route('overtime.my')
    ->with('success', 'ओभरटाइम विवरण सफलतापूर्वक दर्ता भयो।')
    ->with('highlight_id', $newRecord->id)
    ->with('last_event_id', $newRecord->event_id);
    } catch (Exception $e) {
        return redirect()->back()->withInput()->with('error', $e->getMessage());
    }

}
    public function eventList()
    {
        $events = Event::where('status', 'Active')->get();
        return view('overtime.events', compact('events'));
    }

   public function edit($id)
{
    $record = OvertimeRecord::findOrFail($id);

    $canSelectAny = $this->canEnterForAnyone();

    if (!$this->canModifyRecord($record)) {
        abort(403, 'तपाईं यो record edit गर्न पाउनुहुन्न।');
    }

    if ($record->status === 'Verified') {
        return redirect()->back()->with('error', 'यो record पहिले नै Verified छ। Edit गर्न पहिले Unverify गर्नुपर्छ।');
    }

    if (!$record->isEditable() || ($record->event_id && !$record->event->canEnterOt())) {
        return redirect()->back()->with('error', 'यो OT Record अहिले Edit गर्न मिल्दैन (Event Approve भइसकेको बेला मात्र OT Edit गर्न मिल्छ, वा Record आफैं Submit भइसकेको छ)।');
    }

    $employees = Employee::all();
    $allEmployees = Employee::where('is_active', true)->orderBy('name')->get();
    return view('overtime.edit', compact('record', 'employees', 'canSelectAny', 'allEmployees'));
}
    public function update(Request $request, $id)
{
    try {
        $oldRecord = OvertimeRecord::findOrFail($id);
        $eventId = $request->event_id;

        if (!$this->canModifyRecord($oldRecord)) {
            abort(403, 'तपाईं यो record update गर्न पाउनुहुन्न।');
        }

        if (!$oldRecord->isEditable() || ($oldRecord->event_id && !$oldRecord->event->canEnterOt())) {
            return redirect()->back()->with('error', 'यो OT Record अहिले Edit गर्न मिल्दैन (Event Approve भइसकेको बेला मात्र OT Edit गर्न मिल्छ, वा Record आफैं Submit भइसकेको छ)।');
        }

        // Event date range भन्दा बाहिरको मितिमा update हुन नदिने
        if ($request->filled('event_id')) {
            $ev = Event::find($request->event_id);
            if ($ev && ($request->ot_date < $ev->start_date || $request->ot_date > $ev->end_date)) {
                return redirect()->back()->with('error', 'यो कार्यक्रमको मिति ('.adToBs($ev->start_date).' देखि '.adToBs($ev->end_date).') भन्दा बाहिरको मितिमा OT राख्न मिल्दैन।');
            }
        }

        

        // १. पुरानो रेकर्ड डिलिट र नयाँ बनाउने
        // पहिले यहाँ date+employee_id+event_id+purpose_id ले पुरानो row(s) खोजिन्थ्यो — तर सोही
        // employee+date मा भएका *अरू छुट्टाछुट्टै* entry हरू पनि यी ४ field मिल्दा गलतीले delete
        // हुन्थे (data-loss bug)। entry_group ले ठ्याक्कै यही entry का row(s) मात्र लक्षित गर्छ।
        if ($oldRecord->entry_group) {
            OvertimeRecord::where('entry_group', $oldRecord->entry_group)->delete();
        } else {
            // Defensive fallback (सामान्यतया हुनुहुँदैन, migration ले सबैलाई entry_group दिइसकेको हुन्छ)
            $oldRecord->delete();
        }

        $employee = Employee::findOrFail($request->employee_id);
        $additionalData = [
            'event_id'   => $request->event_id,
            'ot_date'    => $request->ot_date,
            'from_time'  => $request->from_time,
            'to_time'    => $request->to_time,
            'is_holiday_override' => (auth()->user()->role === 'admin' && $request->has('is_holiday_override')) ? $request->is_holiday_override : null,
            'remarks'    => $request->remarks,
            'purpose_id' => $request->purpose_id,
            'recommender_employee_id' => $request->recommender_employee_id ?? $oldRecord->recommender_employee_id,
            'approver_employee_id'    => $request->approver_employee_id ?? $oldRecord->approver_employee_id,
        ];
        $newRecord = $this->calculator->calculateAndSave($additionalData, $employee);

        // २. अब Tiffin recalculate गर्ने — यही edit भएको entry (entry_group) का row(s) मात्र,
        // चाहे Individual होस् वा Event-based, अरू कुनै record होइन। (पहिले Event-based भएमा
        // recalculateTiffinForEvent() ले त्यो event का *सबै* record छोइदिन्थ्यो, जुन अनावश्यक थियो।)
        if ($eventId) {
            $event = Event::find($eventId);
            $isEligible = $event ? (bool) $event->is_tiffin_eligible : false;
        } else {
            $isEligible = true;
        }

        $updatedCount = $this->calculator->recalculateTiffinForEntryGroup($newRecord->entry_group, $isEligible, true);
        $verifiedCount = OvertimeRecord::where('entry_group', $newRecord->entry_group)
            ->where('status', 'Verified')
            ->count();

        $message = "{$updatedCount} वटा OT record को खाजा रकम पुनः गणना गरियो।";
        
        if ($verifiedCount > 0) {
            $message .= " (चेतावनी: {$verifiedCount} वटा Verified रेकर्डहरू अपडेट गरिएका छैनन्।)";
        }

        return redirect()->route('overtime.list')->with('success', $message);

    } catch (Exception $e) {
        return redirect()->back()->with('error', 'अपडेट गर्दा त्रुटि भयो: ' . $e->getMessage());
    }
}
   public function destroy($id)
{
    $record = OvertimeRecord::findOrFail($id);

    if (!$this->canModifyRecord($record)) {
        abort(403, 'तपाईं यो record हटाउन पाउनुहुन्न।');
    }

    if ($record->status === 'Verified') {
        return redirect()->back()->with('error', 'Verified record हटाउन मिल्दैन। पहिले Unverify गर्नुपर्छ।');
    }

    if (!$record->isEditable() || ($record->event_id && !$record->event->canEnterOt())) {
        return redirect()->back()->with('error', 'यो OT Record अहिले हटाउन मिल्दैन (Event Approve भइसकेको बेला मात्र OT हटाउन मिल्छ, वा Record आफैं Submit भइसकेको छ)।');
    }

    $record->delete();
    return redirect()->back()->with('success', 'रेकर्ड हटाइयो!');
}
public function printSlip($id)
{
    $record = OvertimeRecord::with('employee.position', 'event', 'purpose')->findOrFail($id);
    $wordService = new OvertimeWordService();
    $printedBy = auth()->user()->employee ?? null;

    // Case 1: Formal Event भएको — सधैं Group format
    if ($record->event_id) {
        $records = OvertimeRecord::with('employee.position', 'event')
                    ->where('event_id', $record->event_id)
                    ->get();
        return $wordService->generateGroup($records, $record->event->event_name, $record->event, $printedBy);
    }

    // Case 2: Purpose भएको — कति जना छन् त्यसमा भर पर्छ
    if ($record->purpose_id) {
        $records = OvertimeRecord::with('employee.position', 'purpose')
                    ->where('purpose_id', $record->purpose_id)
                    ->get();

        $distinctEmployees = $records->pluck('employee_id')->unique();

        if ($distinctEmployees->count() > 1) {
            // धेरै जना — Group format
            return $wordService->generateGroup($records, $record->purpose->name, null, $printedBy);
        } else {
            // एउटै जना, धेरै दिन भए पनि — Individual format
            return $wordService->generateIndividual($records, $record->employee, null, $printedBy);
        }
    }

    // Case 3: न Event, न Purpose — एउटै दिनको Individual OT
    $records = collect([$record]);
    return $wordService->generateIndividual($records, $record->employee, null, $printedBy);
}
public function printEventSlip($eventId)
{
    $event = Event::findOrFail($eventId);

    $records = OvertimeRecord::with('employee.position', 'event')
                ->where('event_id', $eventId)
                ->get();

    if ($records->isEmpty()) {
        return redirect()->back()->with('error', 'यो कार्यक्रममा अहिलेसम्म कुनै OT रेकर्ड दर्ता भएको छैन।');
    }

    $wordService = new OvertimeWordService();
    $printedBy = auth()->user()->employee ?? null;
    return $wordService->generateGroup($records, $event->event_name, $event, $printedBy);
}

public function printPurposeSlip($purposeId)
{
    $purpose = \App\Models\Purpose::findOrFail($purposeId);

    $records = OvertimeRecord::with('employee.position', 'purpose')
                ->where('purpose_id', $purposeId)
                ->get();

    if ($records->isEmpty()) {
        return redirect()->back()->with('error', 'यो Purpose मा अहिलेसम्म कुनै OT रेकर्ड दर्ता भएको छैन।');
    }

    $wordService = new OvertimeWordService();
    $printedBy = auth()->user()->employee ?? null;
    $distinctEmployees = $records->pluck('employee_id')->unique();

    if ($distinctEmployees->count() > 1) {
        return $wordService->generateGroup($records, $purpose->name, null, $printedBy);
    } else {
        $employee = $records->first()->employee;
        return $wordService->generateIndividual($records, $employee, null, $printedBy);
    }
}
// सिफारिस बाँकी रहेका Individual OT (event_id नभएका, Event-based भए Event page बाटै batch मा हुन्छ)
public function pendingList(Request $request)
{
    $query = OvertimeRecord::with('employee', 'event')
        ->whereNull('event_id')
        ->where('status', 'Submitted');

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('ot_date', [$request->from_date, $request->to_date]);
    }
    if ($request->filled('employee_id')) {
        $query->where('employee_id', $request->employee_id);
    }
    if ($request->filled('event_id')) {
        $query->where('event_id', $request->event_id);
    }

    $records = $query->orderBy('ot_date', 'desc')->get();

    return view('overtime.pending', compact('records'));
}

// स्वीकृति बाँकी रहेका Individual OT (सिफारिस भइसकेका)
public function recommendedList(Request $request)
{
    $query = OvertimeRecord::with('employee', 'event')
        ->whereNull('event_id')
        ->where('status', 'Recommended');

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('ot_date', [$request->from_date, $request->to_date]);
    }
    if ($request->filled('employee_id')) {
        $query->where('employee_id', $request->employee_id);
    }

    $records = $query->orderBy('ot_date', 'desc')->get();

    return view('overtime.recommended', compact('records'));
}

// Step 4 (Individual): सिफारिस गर्ने ले सिफारिस गर्ने
public function recommend($id)
{
    $record = OvertimeRecord::findOrFail($id);

    if (!$this->isRecommenderOf($record) && !$this->canVerify()) {
        return redirect()->back()->with('error', 'तपाईंलाई यो रेकर्ड सिफारिस गर्ने अधिकार छैन।');
    }
    if ($record->status !== 'Submitted') {
        return redirect()->back()->with('error', 'यो रेकर्ड अहिले सिफारिसको लागि तयार अवस्थामा छैन।');
    }

    $fromStatus = $record->status;

    // Event model मा जस्तै direct property assignment + save() — recommended_by/recommended_at
    // $fillable बाहिर राखिएकोले ->update([...]) (mass assignment) ले यी field silently drop गर्थ्यो
    $record->status = 'Recommended';
    $record->recommended_by = auth()->id();
    $record->recommended_at = now();
    $record->save();

    \App\Models\OvertimeStatusLog::record($record->id, 'Recommended', $fromStatus, $record->status);

    return redirect()->back()->with('success', 'रेकर्ड सिफारिस गरियो, अब स्वीकृतिको लागि पठाइयो।');
}

// Step 6 (Individual): स्वीकृति गर्नेले अन्तिम स्वीकृत गर्ने — Approved भएपछि Report मा reflect हुन्छ
public function verify($id)
{
    $record = OvertimeRecord::findOrFail($id);

    if (!$this->isApproverOf($record) && !$this->canVerify()) {
        return redirect()->back()->with('error', 'तपाईंलाई verify गर्ने अधिकार छैन।');
    }

    if ($record->status === 'Verified') {
        return redirect()->back()->with('error', 'यो रेकर्ड पहिले नै verify भइसकेको छ।');
    }
    // Event-based रेकर्ड यहाँबाट होइन, Event को Approve action (batch) बाटै हुनुपर्छ
    if ($record->event_id) {
        return redirect()->back()->with('error', 'यो रेकर्ड कुनै कार्यक्रम अन्तर्गत छ, Event को Approve बाटै स्वीकृत गर्नुपर्छ।');
    }
    if ($record->status !== 'Recommended') {
        return redirect()->back()->with('error', 'यो रेकर्ड पहिले सिफारिस हुनुपर्छ, त्यसपछि मात्र स्वीकृत गर्न मिल्छ।');
    }

    $fromStatus = $record->status;

    $record->update([
        'status'      => 'Verified',
        'verified_by' => auth()->id(),
        'verified_at' => now(),
    ]);

    \App\Models\OvertimeStatusLog::record($record->id, 'Verified', $fromStatus, $record->status);

    return redirect()->back()->with('success', 'रेकर्ड सफलतापूर्वक verify भयो!');
}
    public function generateReport(Request $request)
{
  if (!$request->hasAny(['from_date', 'to_date', 'employee_id', 'event_id', 'employee_search', 'event_search', 'group_by'])) {
        return view('reports.index', [
            'groupedData' => collect([]),
            'totalHoursDecimalSum' => 0,
            'totalAmountSum' => 0,
            'hasSearched' => false,
            'pivotColumns' => [],
            'pivotHours' => [],
            'pivotLunch' => [],
        ]);
    }

    $query = OvertimeRecord::query()->with(['employee.position', 'event', 'purpose'])->where('status', 'Verified');

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('ot_date', [$request->from_date, $request->to_date]);
    }
    if ($request->filled('employee_id')) {
        $query->where('employee_id', $request->employee_id);
    }
    if ($request->filled('event_id')) {
        $query->where('event_id', $request->event_id);
    }
    $this->applyReportTextSearch($query, $request);

    $reportData = $query->get();

    // Position-hierarchy sort: level jati thulo, tyati agadi. Utai level bhitra employee_code (natural order)
    $reportData = $reportData->sort(function ($a, $b) {
        $levelA = $a->employee->position->level ?? 0;
        $levelB = $b->employee->position->level ?? 0;

        if ($levelA !== $levelB) {
            return $levelB <=> $levelA; // ठूलो level अगाडि
        }

        $codeCompare = strnatcmp($a->employee->employee_code ?? '', $b->employee->employee_code ?? '');
        if ($codeCompare !== 0) {
            return $codeCompare; // natural order (P-2 pahile P-10 pachi)
        }

        return strcmp($a->ot_date, $b->ot_date);
    })->values();

    // Employee -> Event/General subgroup -> records बनाउने
    $employeeGroups = [];

    foreach ($reportData as $rec) {
        $empId = $rec->employee_id;

        if (!isset($employeeGroups[$empId])) {
            $employeeGroups[$empId] = [
                'employee'    => $rec->employee,
                'events'      => [],
                'total_hours' => 0,
                'total_lunch' => 0,
            ];
        }

        $eventKey = $rec->event_id ?? 'general';

        if (!isset($employeeGroups[$empId]['events'][$eventKey])) {
            $employeeGroups[$empId]['events'][$eventKey] = [
                'label'          => $rec->event->event_name ?? 'सामान्य (General)',
                'records'        => [],
                'subtotal_hours' => 0,
                'subtotal_lunch' => 0,
            ];
        }

        $employeeGroups[$empId]['events'][$eventKey]['records'][]  = $rec;
        $employeeGroups[$empId]['events'][$eventKey]['subtotal_hours'] += $rec->total_hours;
        $employeeGroups[$empId]['events'][$eventKey]['subtotal_lunch'] += $rec->tiffin_amount;

        $employeeGroups[$empId]['total_hours'] += $rec->total_hours;
        $employeeGroups[$empId]['total_lunch'] += $rec->tiffin_amount;
    }

    $groupedData = collect($employeeGroups); // insertion order = sorted order (PHP associative array preserves order)

    $totalHoursDecimalSum = $reportData->sum('total_hours');
    $totalAmountSum       = $reportData->sum('tiffin_amount');

    // ==========================================
    // Pivot View को लागि डेटा तयार गर्ने
    // ==========================================
    $pivotHours = [];
    $pivotLunch = [];
    $pivotColumns = [];

    foreach ($reportData as $rec) {
        $empId = $rec->employee_id;

        // Column label: Event > Purpose > सामान्य (General)
        if ($rec->event_id) {
            $label = $rec->event->event_name ?? 'सामान्य (General)';
        } elseif ($rec->purpose_id) {
            $label = $rec->purpose->name ?? 'सामान्य (General)';
        } else {
            $label = 'सामान्य (General)';
        }

        $pivotColumns[$label] = true; // unique collect

        if (!isset($pivotHours[$empId])) {
            $pivotHours[$empId] = [];
            $pivotLunch[$empId] = [];
        }

        $pivotHours[$empId][$label] = ($pivotHours[$empId][$label] ?? 0) + $rec->total_hours;
        $pivotLunch[$empId][$label] = ($pivotLunch[$empId][$label] ?? 0) + $rec->tiffin_amount;
    }

    // Column हरू alphabetically sort
    $pivotColumns = array_keys($pivotColumns);
    sort($pivotColumns, SORT_STRING);

    return view('reports.index', compact(
        'groupedData', 'totalHoursDecimalSum', 'totalAmountSum',
        'pivotColumns', 'pivotHours', 'pivotLunch'
    ));

}
public function exportPivotExcel(Request $request)
{
    $query = OvertimeRecord::query()->with(['employee.position', 'event', 'purpose'])->where('status', 'Verified');

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('ot_date', [$request->from_date, $request->to_date]);
    }
    if ($request->filled('employee_id')) {
        $query->where('employee_id', $request->employee_id);
    }
    if ($request->filled('event_id')) {
        $query->where('event_id', $request->event_id);
    }
    $this->applyReportTextSearch($query, $request);

    $reportData = $query->get();

    $reportData = $reportData->sort(function ($a, $b) {
        $levelA = $a->employee->position->level ?? 0;
        $levelB = $b->employee->position->level ?? 0;

        if ($levelA !== $levelB) {
            return $levelB <=> $levelA;
        }

        $codeCompare = strnatcmp($a->employee->employee_code ?? '', $b->employee->employee_code ?? '');
        if ($codeCompare !== 0) {
            return $codeCompare;
        }

        return strcmp($a->ot_date, $b->ot_date);
    })->values();

    $employees = [];
    $pivotHours = [];
    $pivotLunch = [];
    $pivotColumns = [];

    foreach ($reportData as $rec) {
        $empId = $rec->employee_id;

        if (!isset($employees[$empId])) {
            $employees[$empId] = ['employee' => $rec->employee];
        }

        if ($rec->event_id) {
            $label = $rec->event->event_name ?? 'सामान्य (General)';
        } elseif ($rec->purpose_id) {
            $label = $rec->purpose->name ?? 'सामान्य (General)';
        } else {
            $label = 'सामान्य (General)';
        }

        $pivotColumns[$label] = true;

        $pivotHours[$empId][$label] = ($pivotHours[$empId][$label] ?? 0) + $rec->total_hours;
        $pivotLunch[$empId][$label] = ($pivotLunch[$empId][$label] ?? 0) + $rec->tiffin_amount;
    }

    $pivotColumns = array_keys($pivotColumns);
    sort($pivotColumns, SORT_STRING);

    return \Maatwebsite\Excel\Facades\Excel::download(
        new \App\Exports\PivotReportExport($pivotColumns, $pivotHours, $pivotLunch, $employees),
        'Pivot_Report_' . date('Ymd') . '.xlsx'
    );
}
   public function exportExcel(Request $request)
{
    $query = \App\Models\OvertimeRecord::query()->with(['employee.position', 'event'])->where('status', 'Verified');

    if ($request->filled('employee_id')) { $query->where('employee_id', $request->employee_id); }
    if ($request->filled('event_id')) { $query->where('event_id', $request->event_id); }
    if ($request->filled('from_date')) { $query->where('ot_date', '>=', $request->from_date); }
    if ($request->filled('to_date')) { $query->where('ot_date', '<=', $request->to_date); }
    $this->applyReportTextSearch($query, $request);

    $reportData = $query->get()->sort(function ($a, $b) {
        $levelA = $a->employee->position->level ?? 0;
        $levelB = $b->employee->position->level ?? 0;
        if ($levelA !== $levelB) { return $levelB <=> $levelA; }
        return strnatcmp($a->employee->employee_code ?? '', $b->employee->employee_code ?? '');
    })->values();

    $groupBy = $request->get('group_by', 'employee');
    $groupColumn = ($groupBy == 'event') ? 'event_id' : 'employee_id';
    $data = $reportData->groupBy($groupColumn);

    if ($data->isEmpty()) {
        return back()->with('error', 'कुनै पनि ओभरटाइम रेकर्ड भेटिएन!');
    }

    return Excel::download(new OvertimeExport($data), 'OvertimeReport.xlsx');
}

public function myRecords(Request $request)
{
    $employeeId = auth()->user()->employee_id;

    if (!$employeeId) {
        return redirect()->back()->with('error', 'तपाईंको account कुनै Employee सँग link भएको छैन।');
    }

    $query = OvertimeRecord::with('event')->where('employee_id', $employeeId);

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('ot_date', [$request->from_date, $request->to_date]);
    }
    if ($request->filled('event_id')) {
        $query->where('event_id', $request->event_id);
    }

    $records = $query->orderBy('ot_date', 'desc')->get();

    return view('overtime.my', compact('records'));
}
public function index()
{
    if ($this->canEnterForAnyone()) {
        $records = OvertimeRecord::with('employee', 'event')->orderBy('ot_date', 'desc')->get();
    } else {
        $myEmployeeId = auth()->user()->employee_id;
        // आफ्नै record + आफूले बनाएको Event अन्तर्गतका सबैको record (Submit नहुँदासम्म edit/delete गर्न पाउनको लागि यहीं देखिनुपर्छ)
        $records = OvertimeRecord::with('employee', 'event')
                    ->where(function ($q) use ($myEmployeeId) {
                        $q->where('employee_id', $myEmployeeId)
                          ->orWhereHas('event', function ($eq) {
                              $eq->where('created_by', auth()->id());
                          });
                    })
                    ->orderBy('ot_date', 'desc')
                    ->get();
    }

    return view('overtime.index', compact('records'));
}
public function summaryReport(Request $request)
{
    $query = \App\Models\OvertimeRecord::query()->with(['employee.position', 'event'])->where('status', 'Verified');

    if ($request->filled('from_date')) { $query->where('ot_date', '>=', $request->from_date); }
    if ($request->filled('to_date')) { $query->where('ot_date', '<=', $request->to_date); }
    if ($request->filled('employee_id')) { $query->where('employee_id', $request->employee_id); }
    if ($request->filled('event_id')) { $query->where('event_id', $request->event_id); }
    $this->applyReportTextSearch($query, $request);

    $summaryData = $query->select(
            'employee_id', 'event_id',
            \DB::raw('SUM(total_hours) as total_hours'),
            \DB::raw('SUM(tiffin_amount) as total_lunch'),
            \DB::raw('MIN(ot_date) as date_from'),
            \DB::raw('MAX(ot_date) as date_to')
        )
        ->groupBy('employee_id', 'event_id')
        ->with(['employee.position', 'event'])
        ->get()
        ->sort(function ($a, $b) {
            $levelA = $a->employee->position->level ?? 0;
            $levelB = $b->employee->position->level ?? 0;
            if ($levelA !== $levelB) { return $levelB <=> $levelA; }
            return strnatcmp($a->employee->employee_code ?? '', $b->employee->employee_code ?? '');
        })->values();

    return view('reports.summary', compact('summaryData'));
}
public function financeReport(Request $request)
{
    $query = \App\Models\OvertimeRecord::query()->with(['employee.position', 'event'])->where('status', 'Verified');

    if ($request->filled('from_date')) { $query->where('ot_date', '>=', $request->from_date); }
    if ($request->filled('to_date')) { $query->where('ot_date', '<=', $request->to_date); }
    if ($request->filled('employee_id')) { $query->where('employee_id', $request->employee_id); }
    if ($request->filled('event_id')) { $query->where('event_id', $request->event_id); }
    $this->applyReportTextSearch($query, $request);

    $financeData = $query->select(
            'employee_id', 'event_id',
            \DB::raw('SUM(total_hours) as total_hours'),
            \DB::raw('SUM(tiffin_amount) as total_lunch'),
            \DB::raw('MIN(ot_date) as date_from'),
            \DB::raw('MAX(ot_date) as date_to')
        )
        ->groupBy('employee_id', 'event_id')
        ->with(['employee.position', 'event'])
        ->get()
        ->sort(function ($a, $b) {
            $levelA = $a->employee->position->level ?? 0;
            $levelB = $b->employee->position->level ?? 0;
            if ($levelA !== $levelB) { return $levelB <=> $levelA; }
            return strnatcmp($a->employee->employee_code ?? '', $b->employee->employee_code ?? '');
        })->values();

    return view('reports.finance', compact('financeData'));
}
public function updateFinanceData(Request $request)
{
    // यहाँबाट rates हरू अपडेट गर्ने लजिक
    foreach ($request->rates as $id => $rate) {
        $record = \App\Models\OvertimeRecord::find($id);
        if ($record) {
            $record->update(['ot_rate_snapshot' => $rate]);
        }
    }

    return back()->with('success', 'OT Rates सफलतापूर्वक अपडेट गरियो!');
}
// OvertimeController.php भित्र यो मेथड थप्नुहोस्
public function exportFinanceExcel(Request $request)
{
    $query = \App\Models\OvertimeRecord::query()->with(['employee.position', 'event'])->where('status', 'Verified');

    if ($request->filled('from_date')) { $query->where('ot_date', '>=', $request->from_date); }
    if ($request->filled('to_date')) { $query->where('ot_date', '<=', $request->to_date); }
    if ($request->filled('employee_id')) { $query->where('employee_id', $request->employee_id); }
    if ($request->filled('event_id')) { $query->where('event_id', $request->event_id); }
    $this->applyReportTextSearch($query, $request);

    $data = $query->get()->sort(function ($a, $b) {
        $levelA = $a->employee->position->level ?? 0;
        $levelB = $b->employee->position->level ?? 0;
        if ($levelA !== $levelB) { return $levelB <=> $levelA; }
        return strnatcmp($a->employee->employee_code ?? '', $b->employee->employee_code ?? '');
    })->values();

    if ($data->isEmpty()) {
        return back()->with('error', 'एक्सपोर्ट गर्नका लागि कुनै डेटा भेटिएन!');
    }

    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\FinanceExport($data), 'FinanceReport.xlsx');
}
public function exportSummaryExcel(Request $request)
{
    $query = \App\Models\OvertimeRecord::query()->with(['employee.position', 'event'])->where('status', 'Verified');

    if ($request->filled('from_date')) { $query->where('ot_date', '>=', $request->from_date); }
    if ($request->filled('to_date')) { $query->where('ot_date', '<=', $request->to_date); }
    if ($request->filled('employee_id')) { $query->where('employee_id', $request->employee_id); }
    if ($request->filled('event_id')) { $query->where('event_id', $request->event_id); }
    $this->applyReportTextSearch($query, $request);

    $data = $query->select(
            'employee_id', 'event_id',
            \DB::raw('SUM(total_hours) as total_hours'),
            \DB::raw('SUM(tiffin_amount) as total_lunch'),
            \DB::raw('MIN(ot_date) as date_from'),
            \DB::raw('MAX(ot_date) as date_to')
        )
        ->groupBy('employee_id', 'event_id')
        ->with(['employee.position', 'event'])
        ->get()
        ->sort(function ($a, $b) {
            $levelA = $a->employee->position->level ?? 0;
            $levelB = $b->employee->position->level ?? 0;
            if ($levelA !== $levelB) { return $levelB <=> $levelA; }
            return strnatcmp($a->employee->employee_code ?? '', $b->employee->employee_code ?? '');
        })->values();

    if ($data->isEmpty()) {
        return back()->with('error', 'एक्सपोर्ट गर्नका लागि कुनै डेटा भेटिएन!');
    }

    return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SummaryExport($data), 'SummaryReport.xlsx');
}
public function reject(Request $request, $id)
{
    $record = OvertimeRecord::findOrFail($id);

    if ($record->event_id) {
        return redirect()->back()->with('error', 'यो रेकर्ड कुनै कार्यक्रम अन्तर्गत छ, Event को Reject बाटै (batch) अस्वीकृत गर्नुपर्छ।');
    }

    $request->validate([
        'reason' => 'required|string|max:500',
    ]);

    // कुन stage मा छ त्यसै अनुसार अधिकार जाँच्ने (Submitted -> Recommender, Recommended -> Approver)
    if ($record->status === 'Submitted') {
        if (!$this->isRecommenderOf($record) && !$this->canVerify()) {
            return redirect()->back()->with('error', 'तपाईंलाई reject गर्ने अधिकार छैन।');
        }
    } elseif ($record->status === 'Recommended') {
        if (!$this->isApproverOf($record) && !$this->canVerify()) {
            return redirect()->back()->with('error', 'तपाईंलाई reject गर्ने अधिकार छैन।');
        }
    } else {
        return redirect()->back()->with('error', 'यो रेकर्ड अहिले Reject गर्न मिल्ने अवस्थामा छैन।');
    }

    $fromStatus = $record->status;

    // Reject भएपछि सिधा Draft/editable अवस्थामा फर्कने (recommend भइसकेको भए त्यो पनि हट्ने)
    // recommended_by/recommended_at $fillable बाहिर भएकोले (Event को जस्तै) direct property assignment
    $record->status = 'Rejected';
    $record->rejection_reason = $request->reason;
    $record->rejected_by = auth()->id();
    $record->rejected_at = now();
    $record->recommended_by = null;
    $record->recommended_at = null;
    $record->save();

    \App\Models\OvertimeStatusLog::record($record->id, 'Rejected', $fromStatus, $record->status, $request->reason);

    return redirect()->back()->with('success', 'रेकर्ड Reject गरियो, फेरि Edit गर्न मिल्ने भयो।');
}

public function unverify($id)
{
    if (!$this->canUnverify()) {
        return redirect()->back()->with('error', 'तपाईंलाई Unverify गर्ने अधिकार छैन।');
    }

    $record = OvertimeRecord::findOrFail($id);

    if ($record->status !== 'Verified') {
        return redirect()->back()->with('error', 'यो रेकर्ड Verified छैन।');
    }

    $fromStatus = $record->status;

    // Un-verify भएपछि सधैं Pending मा फर्किन्छ (design अनुसार नै) — सिफारिसकर्ताले फेरि सिफारिस गर्नुपर्छ,
    // त्यसैले recommended_by/recommended_at पनि यहीं clear गरिन्छ (fillable बाहिर भएकोले direct assignment)
    $record->status = 'Pending';
    $record->verified_by = null;
    $record->verified_at = null;
    $record->recommended_by = null;
    $record->recommended_at = null;
    $record->save();

    \App\Models\OvertimeStatusLog::record($record->id, 'Unverified', $fromStatus, $record->status);

    return redirect()->back()->with('success', 'रेकर्ड Unverify गरियो।');
}
public function verifiedList(Request $request)
{
    $query = OvertimeRecord::with('employee.position', 'event')->where('status', 'Verified');

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('ot_date', [$request->from_date, $request->to_date]);
    }
    if ($request->filled('employee_id')) {
        $query->where('employee_id', $request->employee_id);
    }
    if ($request->filled('event_id')) {
        $query->where('event_id', $request->event_id);
    }

    $records = $query->orderBy('ot_date', 'desc')->get();

    return view('overtime.verified', compact('records'));
}
}