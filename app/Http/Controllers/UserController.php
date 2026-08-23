<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function create()
    {
        $employees = Employee::all();
        return view('users.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|min:6',
            'role'        => 'required|string',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password), // यहाँ म्यानुअल ह्यास गर्ने
            'role'        => $request->role,
            'employee_id' => $request->employee_id,
        ]);

        return redirect()->route('users.index')->with('success', 'प्रयोगकर्ता सफलतापूर्वक सिर्जना गरियो!');
    }
    

    public function index()
    {
        $users = User::with('employee')->get();
        return view('users.index', compact('users'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        if (auth()->id() === $user->id) {
            return back()->with('error', 'तपाईं आफ्नो चालु अकाउन्ट मेटाउन सक्नुहुन्न!');
        }

        $user->delete();
        return back()->with('success', 'युजर हटाइयो!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $employees = Employee::all(); 
        return view('users.edit', compact('user', 'employees'));
    }

    public function update(Request $request, $id)
{
    $user = User::findOrFail($id);
    
    $request->validate([
        'name'        => 'required|string|max:255',
        'email'       => 'required|email|unique:users,email,' . $user->id,
        'role'        => 'required|string',
        'employee_id' => 'nullable|exists:employees,id',
        'password'    => 'nullable|min:6', // खाली छोड्दा भ्यालिडेशन पास हुन्छ
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->role = $request->role;
    $user->employee_id = $request->employee_id;

    // यदि एडमिनले नयाँ पासवर्ड लेखेको छ भने मात्र पासवर्ड परिवर्तन गर्ने
    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return redirect()->route('users.index')->with('success', 'प्रयोगकर्ताको विवरण सफलतापूर्वक अपडेट भयो!');
}

    }
