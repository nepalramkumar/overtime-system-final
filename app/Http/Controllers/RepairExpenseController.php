<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\RepairExpense;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RepairExpenseController extends Controller
{
    /**
     * FY dropdown options: हालको Nepali FY (Shrawan देखि Ashad सम्म)।
     * Shrawan महिना (BS month 4) मा भने अघिल्लो FY पनि देखिन्छ (grace period —
     * FY सकिएको १ महिनासम्म late entry गर्न मिल्ने, तर मिति भने त्यही FY भित्रकै हुनुपर्छ)।
     */
    public static function fyOptions(): array
    {
        $bs = adToBs(now()->format('Y-m-d'));
        [$bsYear, $bsMonth] = array_map('intval', explode('-', $bs));

        // Shrawan = BS महिना ४ बाट FY सुरु हुन्छ
        $currentFyStart = $bsMonth >= 4 ? $bsYear : $bsYear - 1;

        $options = [$currentFyStart . '/' . ($currentFyStart + 1)];

        if ($bsMonth == 4) {
            $prevFyStart = $currentFyStart - 1;
            $options[] = $prevFyStart . '/' . $currentFyStart;
        }

        return $options;
    }

    /**
     * दिइएको fy_year (जस्तै "2083/2084") को वास्तविक मिति सीमा (Shrawan 1 देखि Ashad अन्त्यसम्म) फर्काउने।
     */
    protected function fyDateRange(string $fyYear): array
    {
        $startYear = (int) explode('/', $fyYear)[0];
        $endYear   = $startYear + 1;

        return [
            sprintf('%04d-04-01', $startYear), // Shrawan 1
            sprintf('%04d-03-32', $endYear),    // Ashad अन्त्य (safe upper bound)
        ];
    }

    protected function canSelectAny()
    {
        if (auth()->user()->role === 'admin') {
            return true;
        }

        return RolePermission::where('role', auth()->user()->role)
            ->where('permission', 'repair.expenses.manage')
            ->exists();
    }

  protected const BS_MONTHS = ['Baishakh','Jestha','Ashad','Shrawan','Bhadra','Ashwin','Kartik','Mangsir','Poush','Magh','Falgun','Chaitra'];

    /**
     * हरेक RepairExpense entry को date/description/amount array लाई
     * per-date row मा भत्काएर (flatten), हरेक row मा BS month थपेर दिने।
     */
    protected function flattenedRows($fyFilter = null, $monthFilter = null, $employeeFilter = null)
    {
        $query = RepairExpense::with(['employee.position']);
        if ($fyFilter) {
            $query->where('fy_year', $fyFilter);
        }
        if ($employeeFilter) {
            $query->where('employee_id', $employeeFilter);
        }

        $expenses = $query->orderBy('created_at', 'desc')->get();
        $rows = collect();

        foreach ($expenses as $expense) {
            foreach ($expense->date as $i => $d) {
                $bsDate = adToBs($d);
                $bsMonthNum = (int) explode('-', $bsDate)[1];
                $bsMonthName = self::BS_MONTHS[$bsMonthNum - 1] ?? '';

                if ($monthFilter && $bsMonthName !== $monthFilter) {
                    continue;
                }

              $rows->push([
                    'expense_id'  => $expense->id,
                    'employee'    => $expense->employee,
                    'fy_year'     => $expense->fy_year,
                    'date'        => $d,
                    'bs_date'     => $bsDate,
                    'bs_month'    => $bsMonthName,
                    'description' => $expense->description[$i] ?? '',
                    'amount'      => (float) ($expense->amount[$i] ?? 0),
                    'isEdit'      => $expense->isEdit,
                ]);
            }
        }

        return $rows->sortByDesc('bs_date')->values();
    }
    public function printExpense($id)
{
    $expense = RepairExpense::with(['employee'])->findOrFail($id);
    $employee = $expense->employee;

    // यो record भन्दा अघि (created_at अनुसार) यही FY मा बनेका अरू entries को जम्मा
    $priorRecords = RepairExpense::where('employee_id', $expense->employee_id)
            ->where('fy_year', $expense->fy_year)
            ->where('created_at', '<', $expense->created_at)
            ->get();

        $priorClaimed = $priorRecords->sum('total_amount');
        $openingBalance = $employee->repair_expense_limit - $priorClaimed;

        // Opening date: अघिल्लो record भएको अन्तिम (सबैभन्दा पछिल्लो) मिति;
        // पहिलो पटक हो भने FY को सुरुको मिति (श्रावण १)
        $lastPriorDate = $priorRecords
            ->flatMap(fn($rec) => $rec->date)
            ->sort()
            ->last();

        if ($lastPriorDate) {
            $openingDateBs = adToBs($lastPriorDate);
        } else {
            $fyStartYear = (int) explode('/', $expense->fy_year)[0];
            $openingDateBs = $fyStartYear . '-04-01';
        }

    $ledgerRows = [];
    $runningBalance = $openingBalance;

    foreach ($expense->date as $i => $d) {
        $amount = (float) ($expense->amount[$i] ?? 0);
        $runningBalance -= $amount;

        $ledgerRows[] = [
            'date'        => adToBs($d),
            'particulars' => $expense->description[$i] ?? '',
            'bill_amount' => $amount,
            'balance'     => $runningBalance,
        ];
    }

    return \Barryvdh\DomPDF\Facade\Pdf::loadView('repair.expenses.pdf', [
        'expense'        => $expense,
        'employee'       => $employee,
        'openingDateBs'  => $openingDateBs,
        'openingBalance' => $openingBalance,
        'ledgerRows'     => $ledgerRows,
    ])->setPaper('a4')->download('Repair_Expense_' . str_replace(' ', '_', $employee->name) . '_' . date('Ymd') . '.pdf');
}

    public function index(Request $request)
    {
        $fyFilter = $request->filled('fy_year') ? $request->fy_year : null;
        $monthFilter = $request->filled('bs_month') ? $request->bs_month : null;
        $employeeFilter = $request->filled('employee_id') ? $request->employee_id : null;

        $rows = $this->flattenedRows($fyFilter, $monthFilter, $employeeFilter);

        if ($request->has('export') && $request->export === 'excel') {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\RepairExpenseExport($rows), 'Repair_Expenses_' . date('Ymd') . '.xlsx');
        }

        $fyList = RepairExpense::select('fy_year')->distinct()->orderBy('fy_year', 'desc')->pluck('fy_year');
        $monthList = self::BS_MONTHS;

        // सामान्य pagination नगरी, सिधै collection नै view मा (धेरै ठूलो डेटा भए Pagination अलग व्यवस्था गर्न सकिन्छ)
        return view('repair.expenses.index', compact('rows', 'fyList', 'monthList'));
    }

    public function create()
    {
        $canSelectAny = $this->canSelectAny();

        if ($canSelectAny) {
            $employees = Employee::orderBy('name')->get();
            $lockedEmployee = null;
        } else {
            $lockedEmployee = Employee::where('id', auth()->user()->employee_id)->first();
            $employees = $lockedEmployee ? collect([$lockedEmployee]) : collect([]);

            if (!$lockedEmployee) {
                return redirect()->back()->with('error', 'तपाईंको User account कुनै Employee सँग link भएको छैन। कृपया Admin लाई सम्पर्क गर्नुहोस्।');
            }
        }

        $fyOptions = self::fyOptions();

        return view('repair.expenses.form', [
            'employees'      => $employees,
            'fyOptions'      => $fyOptions,
            'expense'        => null,
            'canSelectAny'   => $canSelectAny,
            'lockedEmployee' => $lockedEmployee,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'fy_year'     => ['required', 'string', Rule::in(self::fyOptions())],
            'date'        => 'required|array',
            'description' => 'required|array',
            'amount'      => 'required|array',
            'remarks'     => 'nullable|string',
        ], [
            'fy_year.in' => 'कृपया सूचीबाट मात्र FY Year छान्नुहोस्।',
        ]);

        if (!$this->canSelectAny() && (int) $validated['employee_id'] !== (int) auth()->user()->employee_id) {
            return redirect()->back()->with('error', 'तपाईं आफ्नो बाहेक अरूको Repair Expense दर्ता गर्न पाउनुहुन्न।');
        }

        $employee = Employee::findOrFail($validated['employee_id']);

        // Vehicle No नभएको employee को Repair Expense दर्ता गर्न नमिल्ने

      // Vehicle No नभएको employee को Repair Expense दर्ता गर्न नमिल्ने
        if (empty($employee->vehicle_no)) {
            return redirect()->back()->withInput()->with('error', 'यस कर्मचारी (' . $employee->name . ') को Vehicle No अद्यावधिक गरिएको छैन। Repair Expense दर्ता गर्नुअघि Vehicle No थप्नुहोस्।')
                ->with('vehicle_missing_employee_id', $employee->id)
                ->with('is_self_entry', !$this->canSelectAny());
        }

        
        // हरेक मिति छानिएको FY भित्रकै हो कि जाँच्ने (BS मा बदलेर)
        [$rangeStart, $rangeEnd] = $this->fyDateRange($validated['fy_year']);
        foreach ($validated['date'] as $d) {
            $bsDate = adToBs($d);
            if ($bsDate < $rangeStart || $bsDate > $rangeEnd) {
                return redirect()->back()->withInput()->with('error', 'मिति (' . $d . ') छानिएको FY Year (' . $validated['fy_year'] . ') भित्र पर्दैन।');
            }
        }

      if ($employee->repair_expense_limit <= 0) {
            return redirect()->back()->withInput()->with('error', 'यस कर्मचारीको Repair Expense Limit अझै Set गरिएको छैन। Admin लाई सम्पर्क गर्नुहोस्।');
        }

        $totalAmount = collect($validated['amount'])->map(fn($a) => (float) $a)->sum();

        $alreadyClaimed = RepairExpense::where('employee_id', $validated['employee_id'])
            ->where('fy_year', $validated['fy_year'])
            ->sum('total_amount');

        $remaining = $employee->repair_expense_limit - $alreadyClaimed;

        if ($totalAmount > $remaining) {
            return redirect()->back()->withInput()->with('error',
                'Limit भन्दा बढी भयो। यो FY Year (' . $validated['fy_year'] . ') मा तपाईंको कुल Limit रु. ' . number_format($employee->repair_expense_limit) .
                ' मध्ये रु. ' . number_format($alreadyClaimed) . ' पहिले नै claim भइसकेको छ। बाँकी रु. ' . number_format($remaining) . ' मात्र claim गर्न मिल्छ।'
            );
        }
        RepairExpense::create([
            'employee_id'  => $validated['employee_id'],
            'fy_year'      => $validated['fy_year'],
            'date'         => $validated['date'],
            'description'  => $validated['description'],
            'amount'       => $validated['amount'],
            'total_amount' => $totalAmount,
            'remarks'      => $validated['remarks'] ?? null,
            'isEdit'       => false,
        ]);

        return redirect()->route('repair.expenses.index')->with('success', 'Repair Expense सफलतापूर्वक दर्ता भयो।');
    }

    public function edit($id)
    {
        $expense = RepairExpense::with(['employee'])->findOrFail($id);

        if (!$this->canEdit($expense)) {
            return redirect()->route('repair.expenses.index')->with('error', 'यो Repair Expense Edit गर्न अनुमति छैन। Admin/Manager लाई सम्पर्क गर्नुहोस्।');
        }

        return view('repair.expenses.form', [
            'expense'        => $expense,
            'employees'      => Employee::orderBy('name')->get(),
            'fyOptions'      => self::fyOptions(),
            'canSelectAny'   => $this->canSelectAny(),
            'lockedEmployee' => null,
        ]);
    }

    public function update(Request $request, $id)
    {
        $expense = RepairExpense::findOrFail($id);

        if (!$this->canEdit($expense)) {
            return redirect()->route('repair.expenses.index')->with('error', 'यो Repair Expense Edit गर्न अनुमति छैन।');
        }

        $validated = $request->validate([
            'date'        => 'required|array',
            'description' => 'required|array',
            'amount'      => 'required|array',
            'remarks'     => 'nullable|string',
        ]);

        $employee = $expense->employee;

      [$rangeStart, $rangeEnd] = $this->fyDateRange($expense->fy_year);
        foreach ($validated['date'] as $d) {
            $bsDate = adToBs($d);
            if ($bsDate < $rangeStart || $bsDate > $rangeEnd) {
                return redirect()->back()->withInput()->with('error', 'मिति (' . $d . ') यो FY Year (' . $expense->fy_year . ') भित्र पर्दैन।');
            }
        }

        if ($employee->repair_expense_limit <= 0) {
            return redirect()->back()->withInput()->with('error', 'यस कर्मचारीको Repair Expense Limit अझै Set गरिएको छैन। Admin लाई सम्पर्क गर्नुहोस्।');
        }

        $totalAmount = collect($validated['amount'])->map(fn($a) => (float) $a)->sum();

        // यो हालको entry लाई बाहेक राखेर, बाँकी सबै entries को जम्मा
        $alreadyClaimed = RepairExpense::where('employee_id', $expense->employee_id)
            ->where('fy_year', $expense->fy_year)
            ->where('id', '!=', $expense->id)
            ->sum('total_amount');

        $remaining = $employee->repair_expense_limit - $alreadyClaimed;

        if ($totalAmount > $remaining) {
            return redirect()->back()->withInput()->with('error',
                'Limit भन्दा बढी भयो। बाँकी रु. ' . number_format($remaining) . ' मात्र claim गर्न मिल्छ।'
            );
        }

   if ($employee->repair_expense_limit <= 0) {
            return redirect()->back()->withInput()->with('error', 'यस कर्मचारीको Repair Expense Limit अझै Set गरिएको छैन। Admin लाई सम्पर्क गर्नुहोस्।');
        }

        $totalAmount = collect($validated['amount'])->map(fn($a) => (float) $a)->sum();

        if ($totalAmount > $employee->repair_expense_limit) {
            return redirect()->back()->withInput()->with('error', 'Repair Expense Limit (रु. ' . number_format($employee->repair_expense_limit) . ') भन्दा बढी भयो।');
        }

        $expense->update([
            'date'         => $validated['date'],
            'description'  => $validated['description'],
            'amount'       => $validated['amount'],
            'total_amount' => $totalAmount,
            'remarks'      => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('repair.expenses.index')->with('success', 'Repair Expense अपडेट भयो।');
    }

    public function destroy($id)
    {
        $expense = RepairExpense::findOrFail($id);
        $expense->delete();
        return redirect()->back()->with('success', 'Repair Expense Delete भयो।');
    }

    public function toggleEditPermission($id)
    {
        $expense = RepairExpense::findOrFail($id);
        $expense->isEdit = !$expense->isEdit;
        $expense->save();

        return redirect()->back()->with('success', 'Edit अनुमति ' . ($expense->isEdit ? 'खुला' : 'बन्द') . ' गरियो।');
    }

    protected function canEdit(RepairExpense $expense)
    {
        if (auth()->user()->role === 'admin') {
            return true;
        }

        $hasManagePermission = RolePermission::where('role', auth()->user()->role)
            ->where('permission', 'repair.expenses.manage')
            ->exists();

        if ($hasManagePermission) {
            return true;
        }

        return (bool) $expense->isEdit;
    }
}
