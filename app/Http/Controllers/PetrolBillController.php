<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PetrolBill;
use App\Models\PetrolMonth;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PetrolBillController extends Controller
{
   public function index(Request $request)
    {
        $query = PetrolBill::with(['employee.position', 'month']);

        if ($request->filled('petrol_month_id')) {
            $query->where('petrol_month_id', $request->petrol_month_id);
        }

        if ($request->has('export') && $request->export === 'excel') {
            $allBills = $query->orderBy('created_at', 'desc')->get();
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PetrolBillExport($allBills), 'Petrol_Bills_' . date('Ymd') . '.xlsx');
        }

        $bills = $query->orderBy('created_at', 'desc')->paginate(20);
        $months = PetrolMonth::orderBy('id', 'desc')->get();

        return view('petrol.bills.index', compact('bills', 'months'));
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

        // Disable गरिएका Month नयाँ Bill entry मा नदेखियोस्
        $months = PetrolMonth::active()->orderBy('id', 'desc')->get();

        return view('petrol.bills.form', [
            'employees'      => $employees,
            'months'         => $months,
            'bill'           => null,
            'canSelectAny'   => $canSelectAny,
            'lockedEmployee' => $lockedEmployee,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id'    => 'required|exists:employees,id',
            'petrol_month_id'=> 'required|exists:petrol_months,id',
            'date'           => 'required|array',
            'quantity'       => 'required|array',
            'rate'           => 'required|array',
            'amount'         => 'required|array',
            'remarks'        => 'nullable|string',
        ]);

        if (!$this->canSelectAny() && (int) $validated['employee_id'] !== (int) auth()->user()->employee_id) {
            return redirect()->back()->with('error', 'तपाईं आफ्नो बाहेक अरूको Petrol Bill दर्ता गर्न पाउनुहुन्न।');
        }

        $employee = Employee::findOrFail($validated['employee_id']);

      // Vehicle No नभएको employee को Petrol Bill दर्ता गर्न नमिल्ने
        if (empty($employee->vehicle_no)) {
            return redirect()->back()->withInput()->with('error', 'यस कर्मचारी (' . $employee->name . ') को Vehicle No अद्यावधिक गरिएको छैन। Petrol Bill दर्ता गर्नुअघि Vehicle No थप्नुहोस्।')
                ->with('vehicle_missing_employee_id', $employee->id)
                ->with('vehicle_missing_employee_name', $employee->name)
                ->with('is_self_entry', !$this->canSelectAny());
        }

        $exists = PetrolBill::where('employee_id', $validated['employee_id'])
            ->where('petrol_month_id', $validated['petrol_month_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'यो कर्मचारीको यो Month को Bill पहिले नै दर्ता भइसकेको छ।');
        }

       if ($employee->petrol_quantity_limit <= 0) {
            return redirect()->back()->withInput()->with('error', 'यस कर्मचारीको Petrol Quantity Limit अझै Set गरिएको छैन। Admin लाई सम्पर्क गर्नुहोस्।');
        }

        $totalQuantity = collect($validated['quantity'])->map(fn($q) => (float) $q)->sum();

        if ($totalQuantity > $employee->petrol_quantity_limit) {
            return redirect()->back()->withInput()->with('error', 'Quantity Limit (' . $employee->petrol_quantity_limit . ' लिटर) भन्दा बढी भयो।');
        }

        $totalAmount = collect($validated['amount'])->map(fn($a) => (float) $a)->sum();

        PetrolBill::create([
            'employee_id'     => $validated['employee_id'],
            'petrol_month_id' => $validated['petrol_month_id'],
            'date'            => $validated['date'],
            'quantity'        => $validated['quantity'],
            'rate'            => $validated['rate'],
            'amount'          => $validated['amount'],
            'total_quantity'  => $totalQuantity,
            'total_amount'    => $totalAmount,
            'remarks'         => $validated['remarks'] ?? null,
            'isEdit'          => false,
        ]);

        return redirect()->route('petrol.bills.index')->with('success', 'Petrol Bill सफलतापूर्वक दर्ता भयो।');
    }

    protected function canSelectAny()
    {
        if (auth()->user()->role === 'admin') {
            return true;
        }

        return \App\Models\RolePermission::where('role', auth()->user()->role)
            ->where('permission', 'petrol.bills.manage')
            ->exists();
    }

   public function edit($id)
    {
        $bill = PetrolBill::with(['employee', 'month'])->findOrFail($id);

        if (!$this->canEdit($bill)) {
            return redirect()->route('petrol.bills.index')->with('error', 'यो Bill Edit गर्न अनुमति छैन। Admin/Manager लाई सम्पर्क गर्नुहोस्।');
        }

        $employees = Employee::orderBy('name')->get();
        $months = PetrolMonth::orderBy('id', 'desc')->get();
        $canSelectAny = $this->canSelectAny();
        $lockedEmployee = null;

        return view('petrol.bills.form', compact('bill', 'employees', 'months', 'canSelectAny', 'lockedEmployee'));
    }

    public function update(Request $request, $id)
    {
        $bill = PetrolBill::findOrFail($id);

        if (!$this->canEdit($bill)) {
            return redirect()->route('petrol.bills.index')->with('error', 'यो Bill Edit गर्न अनुमति छैन।');
        }

        $validated = $request->validate([
            'date'     => 'required|array',
            'quantity' => 'required|array',
            'rate'     => 'required|array',
            'amount'   => 'required|array',
            'remarks'  => 'nullable|string',
        ]);

     $employee = $bill->employee;

        if ($employee->petrol_quantity_limit <= 0) {
            return redirect()->back()->withInput()->with('error', 'यस कर्मचारीको Petrol Quantity Limit अझै Set गरिएको छैन। Admin लाई सम्पर्क गर्नुहोस्।');
        }

        $totalQuantity = collect($validated['quantity'])->map(fn($q) => (float) $q)->sum();

        if ($totalQuantity > $employee->petrol_quantity_limit) {
            return redirect()->back()->withInput()->with('error', 'Quantity Limit (' . $employee->petrol_quantity_limit . ' लिटर) भन्दा बढी भयो।');
        }

        $totalAmount = collect($validated['amount'])->map(fn($a) => (float) $a)->sum();

        $bill->update([
            'date'           => $validated['date'],
            'quantity'       => $validated['quantity'],
            'rate'           => $validated['rate'],
            'amount'         => $validated['amount'],
            'total_quantity' => $totalQuantity,
            'total_amount'   => $totalAmount,
            'remarks'        => $validated['remarks'] ?? null,
        ]);

        return redirect()->route('petrol.bills.index')->with('success', 'Petrol Bill अपडेट भयो।');
    }

    public function destroy($id)
    {
        $bill = PetrolBill::findOrFail($id);
        $bill->delete();
        return redirect()->back()->with('success', 'Petrol Bill Delete भयो।');
    }

    // Admin/Manager ले हरेक entry को Edit permission toggle गर्ने (isEdit flag)
    public function toggleEditPermission($id)
    {
        $bill = PetrolBill::findOrFail($id);
        $bill->isEdit = !$bill->isEdit;
        $bill->save();

        return redirect()->back()->with('success', 'Edit अनुमति ' . ($bill->isEdit ? 'खुला' : 'बन्द') . ' गरियो।');
    }

    protected function canEdit(PetrolBill $bill)
    {
        // Admin/Manager (petrol.bills.manage भएको) लाई सधैं अनुमति; अरूलाई isEdit flag अनुसार मात्र
        if (auth()->user()->role === 'admin') {
            return true;
        }

        $hasManagePermission = \App\Models\RolePermission::where('role', auth()->user()->role)
            ->where('permission', 'petrol.bills.manage')
            ->exists();

        if ($hasManagePermission) {
            return true;
        }

        return (bool) $bill->isEdit;
    }

    public function printBill($id)
    {
        $bill = PetrolBill::with(['employee.position', 'month'])->findOrFail($id);

        $pdf = Pdf::loadView('petrol.bills.pdf', ['bill' => $bill])->setPaper('a4');
        return $pdf->download('Petrol_Bill_' . str_replace(' ', '_', $bill->employee->name) . '_' . $bill->month->month . '_' . $bill->month->year . '.pdf');
    }
}