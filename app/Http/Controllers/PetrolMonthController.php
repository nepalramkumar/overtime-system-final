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

        PetrolMonth::create([
            'month'  => $validated['month'],
            'year'   => $validated['year'],
            'status' => 1,
        ]);

        return redirect()->back()->with('success', 'Month सफलतापूर्वक थपियो।');
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