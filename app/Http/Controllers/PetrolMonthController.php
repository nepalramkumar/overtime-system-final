<?php

namespace App\Http\Controllers;

use App\Models\PetrolMonth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PetrolMonthController extends Controller
{
    // BS महिनाहरू (dropdown मा प्रयोग हुने, क्रम अनुसार)
    public const BS_MONTHS = [
        'Baishakh', 'Jestha', 'Ashad', 'Shrawan', 'Bhadra', 'Ashwin',
        'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra',
    ];

    /**
     * हालको BS वर्ष अनुसार Year dropdown को लागि वर्षहरूको सूची (अगाडि ५ + पछाडि २ वर्ष)।
     */
    public static function yearOptions(): array
    {
        $currentBsYear = (int) explode('-', adToBs(now()->format('Y-m-d')))[0];

        $start = $currentBsYear - 1;
        $end   = $currentBsYear + 7;

        return range($start, $end);
    }

    public function index()
    {
        $months = PetrolMonth::orderBy('year', 'desc')
            ->orderByRaw("FIELD(month, '" . implode("','", self::BS_MONTHS) . "') desc")
            ->get();

        $bsMonths   = self::BS_MONTHS;
        $yearOptions = self::yearOptions();

        return view('petrol.months', compact('months', 'bsMonths', 'yearOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'string', Rule::in(self::BS_MONTHS)],
            'year'  => ['required', 'integer', Rule::in(self::yearOptions())],
        ], [
            'month.in' => 'कृपया सूचीबाट मात्र महिना छान्नुहोस्।',
            'year.in'  => 'कृपया सूचीबाट मात्र वर्ष छान्नुहोस्।',
        ]);

        // Duplicate month+year check (soft-deleted भएका बाहेक)
        $exists = PetrolMonth::where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'यो Month (' . $validated['month'] . ' - ' . $validated['year'] . ') पहिले नै थपिएको छ। एउटै Month+Year दोहोर्याउन मिल्दैन।');
        }

        // अब सामान्यतया Month auto (हरेक १ गते) बनिहाल्छ — यो form मात्र missed/पुरानो महिना
        // manually थप्नुपर्दा प्रयोग हुन्छ। start_date/end_date पनि उसैगरी auto-compute हुन्छ।
        PetrolMonth::ensureExists($validated['month'], (int) $validated['year']);

        return redirect()->back()->with('success', 'Month सफलतापूर्वक थपियो।');
    }

    /**
     * Admin ले कुनै Month को Bill-entry deadline थप गते (default ५ गते भन्दा पर) सम्म बढाउने।
     * जस्तै: Shrawan लाई डिफल्ट Bhadra ५ को साटो Bhadra ७ सम्म देखाउनुपर्दा, extra_days = 2।
     */
    public function extendDeadline(Request $request, $id)
    {
        $month = PetrolMonth::findOrFail($id);

        $validated = $request->validate([
            'extra_days' => 'required|integer|min:1|max:60',
        ]);

        if (!$month->end_date) {
            return redirect()->back()->with('error', 'यो Month को डिफल्ट Deadline गणना भएको छैन, Admin लाई सम्पर्क गर्नुहोस्।');
        }

        $month->extended_end_date = $month->end_date->copy()->addDays($validated['extra_days']);
        $month->save();

        return redirect()->back()->with('success', $month->month . ' ' . $month->year . ' को Deadline ' . $month->extended_end_date->format('Y-m-d') . ' (AD) सम्म थपियो।');
    }

    /**
     * Extension हटाएर फेरि डिफल्ट Deadline (अर्को महिनाको ५ गते) मा फर्काउने।
     */
    public function clearExtension($id)
    {
        $month = PetrolMonth::findOrFail($id);
        $month->extended_end_date = null;
        $month->save();

        return redirect()->back()->with('success', 'Deadline फेरि डिफल्ट (Default) मा फर्काइयो।');
    }

    /**
     * Enable/Disable toggle — Month लाई Petrol Bill entry गर्न खुला/बन्द गर्ने।
     */
    public function toggleStatus($id)
    {
        $month = PetrolMonth::findOrFail($id);
        $month->status = !$month->status;
        $month->save();

        return redirect()->back()->with('success', 'Month ' . ($month->status ? 'Enable' : 'Disable') . ' गरियो।');
    }

    public function destroy($id)
    {
        $month = PetrolMonth::findOrFail($id);

        if ($month->bills()->exists()) {
            return redirect()->back()->with('error', 'यो Month मा Bill रेकर्ड भएकोले Delete गर्न मिल्दैन।');
        }

        $month->delete();
        return redirect()->back()->with('success', 'Month Delete भयो।');
    }
}