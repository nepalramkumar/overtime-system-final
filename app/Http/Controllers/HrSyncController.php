<?php

namespace App\Http\Controllers;

use App\Services\HrSyncService;
use Illuminate\Http\Request;

class HrSyncController extends Controller
{
    public function index()
    {
        return view('hr-sync.index');
    }

    public function run(HrSyncService $syncService)
    {
        $summary = $syncService->runFullSync();

        return redirect()->route('hr-sync.index')->with('summary', $summary)->with('ran', 'सबै (Full Sync)');
    }

    public function runDepartments(HrSyncService $syncService)
    {
        $summary = $syncService->runDepartmentsOnly();
        return redirect()->route('hr-sync.index')->with('summary', $summary)->with('ran', 'Departments मात्र');
    }

    public function runPositions(HrSyncService $syncService)
    {
        $summary = $syncService->runPositionsOnly();
        return redirect()->route('hr-sync.index')->with('summary', $summary)->with('ran', 'Positions मात्र');
    }

    public function runEmployees(HrSyncService $syncService)
    {
        $summary = $syncService->runEmployeesOnly();
        return redirect()->route('hr-sync.index')->with('summary', $summary)->with('ran', 'Employees मात्र');
    }

    public function runHolidays(Request $request, HrSyncService $syncService)
    {
        $fiscalYear = $request->input('fiscal_year'); // उदाहरण: "2083", खाली भए null जान्छ
        $summary = $syncService->runHolidaysOnly($fiscalYear ?: null);
        $label = $fiscalYear ? "Holidays मात्र (FY {$fiscalYear})" : 'Holidays मात्र';
        return redirect()->route('hr-sync.index')->with('summary', $summary)->with('ran', $label);
    }
}