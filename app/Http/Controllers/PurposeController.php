<?php

namespace App\Http\Controllers;

use App\Models\Purpose;
use Illuminate\Http\Request;

class PurposeController extends Controller
{
  public function index()
{
    $purposes = Purpose::orderBy('id', 'desc')->get();
    return view('settings.purposes', compact('purposes'));
}

public function toggleActive($id)
{
    $purpose = Purpose::findOrFail($id);
    $purpose->is_active = !$purpose->is_active;
    $purpose->save();

    return redirect()->back()->with('success', $purpose->is_active ? 'Purpose Enable गरियो।' : 'Purpose Disable गरियो।');
}

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:purposes,name',
        ]);

        Purpose::create(['name' => $request->name]);

        return back()->with('success', 'नयाँ Purpose सफलतापूर्वक थपियो!');
    }

    public function destroy($id)
    {
        $purpose = Purpose::findOrFail($id);

        if ($purpose->overtimeRecords()->exists()) {
            return back()->with('error', 'यो Purpose मा OT record भइरहेकोले हटाउन मिल्दैन।');
        }

        $purpose->delete();
        return back()->with('success', 'Purpose हटाइयो!');
    }
}