<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // कर्मचारीहरूको सूची देखाउने
    public function index()
    {
        $employees = Employee::with('user', 'position')->get();
        return view('employees.list', compact('employees'));
    }

    // नयाँ कर्मचारी थप्ने फारम
    public function create()
    {
        $users = User::doesntHave('employee')->get();
        $positions = Position::where('is_active', true)->get();
        return view('employees.create', compact('users', 'positions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'employee_code' => 'required|string|unique:employees,employee_code',
            'department'    => 'required',
            'position_id'   => 'required|exists:positions,id',
        ]);

        Employee::create($request->only([
            'name', 'employee_code', 'department', 'position_id', 'user_id',
        ]));

        return redirect()->route('employees.index')->with('success', 'कर्मचारी सफलतापूर्वक दर्ता भयो!');
    }

    // कर्मचारीको विवरण Edit गर्ने फारम (Vehicle No / Petrol Quantity Limit यहीँबाट अपडेट हुन्छ)
    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        $positions = Position::where('is_active', true)->get();
        return view('employees.edit', compact('employee', 'positions'));
    }

   public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        // नाम, विभाग, पद अब External API बाट Sync हुने भएकोले यहाँबाट सम्पादन गर्न मिल्दैन
      $request->validate([
            'vehicle_no'            => 'nullable|string|max:50',
            'petrol_quantity_limit' => 'nullable|integer|min:0',
            'repair_expense_limit'  => 'nullable|integer|min:0',
            'hierarchy'             => 'nullable|integer|min:1',
        ]);

        $employee->update($request->only([
            'vehicle_no', 'petrol_quantity_limit', 'repair_expense_limit', 'hierarchy',
        ]));
        return redirect()->route('employees.index')->with('success', 'कर्मचारीको विवरण अपडेट भयो।');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'कर्मचारी Delete भयो।');
    }

    // Logged-in employee ले आफ्नो मात्र Vehicle No अपडेट गर्ने (Profile पेजबाट)
    public function updateOwnVehicle(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
            return redirect()->route('profile.edit')->with('error', 'तपाईंसँग Employee प्रोफाइल जोडिएको छैन।');
        }

        $request->validate([
            'vehicle_no' => 'nullable|string|max:50',
        ]);

        $employee->update(['vehicle_no' => $request->vehicle_no]);

        return redirect()->route('profile.edit')->with('status', 'vehicle-updated');
    }
}