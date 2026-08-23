<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRecord;
use App\Models\PetrolBill;
use App\Models\RepairExpense;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';
        $employeeId = $user->employee_id;

        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();
        $startOfYear = now()->startOfYear()->toDateString();

        // ---- मेरो (Personal) OT आँकडा ----
        $myOt = null;
        if ($employeeId) {
            $myOtQuery = OvertimeRecord::where('employee_id', $employeeId);
            $myOt = [
                'total_hours_month' => (clone $myOtQuery)->where('status', 'Verified')->whereBetween('ot_date', [$startOfMonth, $endOfMonth])->sum('total_hours'),
                'total_hours_year'  => (clone $myOtQuery)->where('status', 'Verified')->whereBetween('ot_date', [$startOfYear, $endOfMonth])->sum('total_hours'),
                'pending'    => (clone $myOtQuery)->whereIn('status', ['Pending', 'Submitted', 'Recommended'])->count(),
                'verified'   => (clone $myOtQuery)->where('status', 'Verified')->count(),
                'rejected'   => (clone $myOtQuery)->where('status', 'Rejected')->count(),
            ];
        }

        // ---- मेरो Petrol/Repair (यदि आफ्नै entry गर्न पाउने भए मात्र देखाउने) ----
        $myPetrolTotal = $employeeId ? PetrolBill::where('employee_id', $employeeId)->whereBetween('created_at', [$startOfYear, $endOfMonth . ' 23:59:59'])->get()->sum('total_amount') : 0;
        $myRepairTotal = $employeeId ? RepairExpense::where('employee_id', $employeeId)->whereBetween('created_at', [$startOfYear, $endOfMonth . ' 23:59:59'])->get()->sum('total_amount') : 0;

        // ---- Repair: यो FY मा कति claim भयो, कति बाँकी (Limit भन्दा) ----
        $myRepairFy = null;
        if ($employeeId) {
            $currentFy = \App\Http\Controllers\RepairExpenseController::fyOptions()[0] ?? null;
            if ($currentFy) {
                $claimed = RepairExpense::where('employee_id', $employeeId)->where('fy_year', $currentFy)->sum('total_amount');
                $limit = $user->employee->repair_expense_limit ?? 0;
                $myRepairFy = [
                    'fy_year'   => $currentFy,
                    'claimed'   => $claimed,
                    'limit'     => $limit,
                    'remaining' => max($limit - $claimed, 0),
                ];
            }
        }

        // ---- Petrol: हालको Active महिनाको claim भयो कि भएन ----
        $myPetrolPending = null;
        if ($employeeId) {
            $activeMonth = \App\Models\PetrolMonth::active()->orderByDesc('id')->first();
            if ($activeMonth) {
                $alreadyClaimed = PetrolBill::where('employee_id', $employeeId)
                    ->where('petrol_month_id', $activeMonth->id)
                    ->exists();
                $myPetrolPending = [
                    'month'   => $activeMonth->month,
                    'year'    => $activeMonth->year,
                    'claimed' => $alreadyClaimed,
                ];
            }
        }

        // ---- Overall (Admin मात्र) ----
        $overall = null;
        if ($isAdmin) {
            $overall = [
                'ot_pending_recommend' => OvertimeRecord::whereNull('event_id')->where('status', 'Submitted')->count()
                                          + \App\Models\Event::where('workflow_status', 'Submitted')->count(),
                'ot_pending_approve'   => OvertimeRecord::whereNull('event_id')->where('status', 'Recommended')->count()
                                          + \App\Models\Event::where('workflow_status', 'Recommended')->count(),
                'ot_verified_month'    => OvertimeRecord::where('status', 'Verified')->whereBetween('ot_date', [$startOfMonth, $endOfMonth])->count(),
                'ot_hours_month'       => OvertimeRecord::where('status', 'Verified')->whereBetween('ot_date', [$startOfMonth, $endOfMonth])->sum('total_hours'),
                'events_active'        => \App\Models\Event::where('is_active', true)->count(),
                'petrol_month_total'   => PetrolBill::whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])->get()->sum('total_amount'),
                'petrol_count_month'   => PetrolBill::whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])->count(),
                'repair_month_total'   => RepairExpense::whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])->get()->sum('total_amount'),
                'repair_count_month'   => RepairExpense::whereBetween('created_at', [$startOfMonth . ' 00:00:00', $endOfMonth . ' 23:59:59'])->count(),
            ];
        }

        return view('dashboard', compact('myOt', 'myPetrolTotal', 'myRepairTotal', 'myRepairFy', 'myPetrolPending', 'overall', 'isAdmin'));
    }
}
