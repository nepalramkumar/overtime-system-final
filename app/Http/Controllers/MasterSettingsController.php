<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OfficeShift;
use App\Models\SnackAllowance;

class MasterSettingsController extends Controller
{
    public function index() {
        $shifts = OfficeShift::all();
        $allowances = SnackAllowance::all();
        return view('settings.index', compact('shifts', 'allowances'));
    }

    public function updateAllowance(Request $request, $id) {
        $allowance = SnackAllowance::findOrFail($id);
        $allowance->update(['amount' => $request->amount]);
        return back()->with('success', 'खाजा खर्चको दर अपडेट भयो!');
    }
    public function storeAllowance(Request $request) {
    $request->validate([
        'min_hours' => 'required',
        'max_hours' => 'required',
        'amount' => 'required',
    ]);

    SnackAllowance::create($request->all());
    return back()->with('success', 'नयाँ दर सफलतापूर्वक थपियो!');
}
public function destroyAllowance($id) {
    $allowance = SnackAllowance::findOrFail($id);
    $allowance->delete();
    return back()->with('success', 'दर सफलतापूर्वक हटाइयो!');
}
public function snackIndex() 
{
    $allowances = SnackAllowance::all();
    return view('settings.snack', compact('allowances'));
}
public function shiftIndex() {
    // दिन अनुसार क्रमबद्ध गरेर देखाउने (Sunday देखि Saturday)
    $shifts = \App\Models\OfficeShift::orderByRaw(
        "FIELD(day_name, 'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday')"
    )->get();
    // 'shift' को साटो 'settings.shift' लेख्नुहोस्
    return view('settings.shift', compact('shifts')); 

}

public function shiftStore(Request $request) {
    $validated = $request->validate([
        'day_name'   => 'required|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday|unique:office_shifts,day_name',
        'start_time' => 'required',
        'end_time'   => 'required',
    ], [
        'day_name.unique' => 'यो दिनको लागि सिफ्ट पहिले नै थपिइसकेको छ। सट्टामा Edit गर्नुहोस्।',
    ]);

    \App\Models\OfficeShift::create($validated);
    return back()->with('success', 'सिफ्ट थपियो!');
}

public function shiftUpdate(Request $request, $id) {
    $shift = \App\Models\OfficeShift::findOrFail($id);

    $validated = $request->validate([
        'day_name'   => 'required|string|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday|unique:office_shifts,day_name,' . $shift->id,
        'start_time' => 'required',
        'end_time'   => 'required',
    ], [
        'day_name.unique' => 'यो दिनको लागि अर्को सिफ्ट पहिले नै अवस्थित छ।',
    ]);

    $shift->update($validated);
    return back()->with('success', 'सिफ्ट अपडेट भयो!');
}

public function shiftDestroy($id) {
    \App\Models\OfficeShift::findOrFail($id)->delete();
    return back()->with('success', 'सिफ्ट हटाइयो!');
}

// ------------------------------------------
// Holiday (बिदा) व्यवस्थापन — OT गणना गर्दा auto-detect गर्नको लागि
// ------------------------------------------
public function holidayIndex(Request $request) {
    $year = $request->get('bs_year');

    $query = \App\Models\Holiday::query();
    if ($year) {
        $query->where('bs_year', $year);
    }

    $holidays = $query->orderBy('date')->get();
    $years = \App\Models\Holiday::whereNotNull('bs_year')->distinct()->orderByDesc('bs_year')->pluck('bs_year');

    return view('settings.holidays', compact('holidays', 'years', 'year'));
}

public function holidayStore(Request $request) {
    $validated = $request->validate([
        'date'    => 'required|date|unique:holidays,date',
        'name'    => 'required|string|max:255',
        'bs_year' => 'nullable|integer|min:2070|max:2110',
    ], [
        'date.unique' => 'यो मितिको लागि बिदा पहिले नै थपिइसकेको छ।',
    ]);

    \App\Models\Holiday::create($validated);
    return back()->with('success', 'बिदा थपियो!');
}

public function holidayUpdate(Request $request, $id) {
    $holiday = \App\Models\Holiday::findOrFail($id);

    $validated = $request->validate([
        'date'    => 'required|date|unique:holidays,date,' . $holiday->id,
        'name'    => 'required|string|max:255',
        'bs_year' => 'nullable|integer|min:2070|max:2110',
    ], [
        'date.unique' => 'यो मितिको लागि अर्को बिदा पहिले नै अवस्थित छ।',
    ]);

    $holiday->update($validated);
    return back()->with('success', 'बिदा अपडेट भयो!');
}

public function holidayDestroy($id) {
    \App\Models\Holiday::findOrFail($id)->delete();
    return back()->with('success', 'बिदा हटाइयो!');
}
}