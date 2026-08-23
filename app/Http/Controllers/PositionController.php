<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index()
    {
        $positions = Position::orderBy('name')->get();
        return view('settings.positions', compact('positions'));
    }

  public function store(Request $request)
{
    $request->validate([
        'name'    => 'required|string|max:255|unique:positions,name',
        'ot_rate' => 'nullable|numeric|min:0',
        'level'   => 'nullable|integer|min:0',
    ]);

    Position::create($request->only(['name', 'ot_rate', 'level']));

    return back()->with('success', 'नयाँ Position सफलतापूर्वक थपियो!');
}

  public function updateRate(Request $request, $id)
{
    $request->validate([
        'ot_rate' => 'required|numeric|min:0',
        'level'   => 'nullable|integer|min:0',
    ]);

    $position = Position::findOrFail($id);
    $position->update([
        'ot_rate' => $request->ot_rate,
        'level'   => $request->level ?? 0,
    ]);

    return back()->with('success', 'Position सफलतापूर्वक अपडेट भयो!');
}

    public function destroy($id)
    {
        $position = Position::findOrFail($id);

        // Yo position link bhako employee cha bhane delete narok garne
        if ($position->employees()->exists()) {
            return back()->with('error', 'यो Position मा employee assign भइरहेकोले हटाउन मिल्दैन।');
        }

        $position->delete();
        return back()->with('success', 'Position हटाइयो!');
    }
}