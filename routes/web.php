<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\MasterSettingsController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PurposeController;
use App\Http\Controllers\PetrolBillController;
use App\Http\Controllers\PetrolMonthController;
use App\Http\Controllers\RepairExpenseController;
use App\Http\Controllers\HrSyncController;

// स्वागत पेज (Public)
Route::get('/', function () {
    return view('welcome');
});

// ड्यासबोर्ड
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// ⚠️ अस्थायी SMTP test route — email debug गरिसकेपछि यो हटाइदिनुहोस्
Route::get('/test-mail-debug', function () {
    try {
        \Illuminate\Support\Facades\Mail::raw('यो OT and Bills system बाट पठाइएको test email हो।', function ($message) {
            $message->to('icanramkumar@gmail.com')->subject('SMTP Test - OT and Bills');
        });
        return 'Email पठाउने प्रयास सफल भयो (कुनै PHP error आएन)। अब inbox/spam दुबै जाँच्नुहोस्। SMTP server ले silently reject गरेको भए पनि यहाँ error नदेखिन सक्छ।';
    } catch (\Throwable $e) {
        return 'ERROR भेटियो — यही message copy गरेर पठाउनुहोस्:<br><br><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    }
})->middleware(['auth']);

// प्रोफाइलका रुटहरू (Auth आवश्यक)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// auth.php समावेश गर्नुहोस्
require __DIR__.'/auth.php';

// ==========================================
// लगइन (Auth) आवश्यक पर्ने मुख्य रुटहरू
// ==========================================
Route::middleware(['auth'])->group(function () {

    // ------------------------------------------
    // Notifications (Dashboard message)
    // ------------------------------------------
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    // ------------------------------------------
    // Permission Routes (Admin Only - controller भित्रै check गरिएको)
    // ------------------------------------------
    Route::get('/settings/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/settings/permissions', [PermissionController::class, 'update'])->name('permissions.update');

    // ------------------------------------------
    // Purpose Routes
    // ------------------------------------------
    Route::get('/settings/purposes', [PurposeController::class, 'index'])->middleware('role:events.manage')->name('purposes.index');
    Route::post('/settings/purposes', [PurposeController::class, 'store'])->middleware('role:events.manage')->name('purposes.store');
    Route::delete('/settings/purposes/{id}', [PurposeController::class, 'destroy'])->middleware('role:events.manage')->name('purposes.destroy');
    Route::post('/settings/purposes/{id}/toggle', [PurposeController::class, 'toggleActive'])->middleware('role:events.manage')->name('purposes.toggle');
    Route::get('/settings/purposes/{id}/print', [OvertimeController::class, 'printPurposeSlip'])->middleware('role:overtime.entry')->name('purposes.print');

    // ------------------------------------------
    // Overtime Routes
    // ------------------------------------------
    Route::get('/overtime/create', [OvertimeController::class, 'create'])->name('overtime.create');
    Route::post('/overtime/store', [OvertimeController::class, 'store'])->name('overtime.store');
    Route::get('/overtime', [OvertimeController::class, 'index'])->middleware('role:overtime.entry')->name('overtime.list');
    Route::get('/overtime/my', [OvertimeController::class, 'myRecords'])->middleware('role:overtime.entry')->name('overtime.my');
    Route::get('/overtime/{id}/edit', [OvertimeController::class, 'edit'])->middleware('role:overtime.entry')->name('overtime.edit');
    Route::put('/overtime/{id}', [OvertimeController::class, 'update'])->middleware('role:overtime.entry')->name('overtime.update');
    Route::delete('/overtime/{id}', [OvertimeController::class, 'destroy'])->middleware('role:overtime.entry')->name('overtime.destroy');
    Route::get('/overtime/{id}/print', [OvertimeController::class, 'printSlip'])->middleware('role:overtime.entry')->name('overtime.print');

    Route::get('/overtime/pending', [OvertimeController::class, 'pendingList'])->middleware('role:overtime.verify')->name('overtime.pending');
    Route::get('/overtime/recommended', [OvertimeController::class, 'recommendedList'])->middleware('role:overtime.verify')->name('overtime.recommended');
    // सिफारिस/स्वीकृति गर्ने कोही पनि हुनसक्ने (role permission भन्दा event/record मा तोकिएको recommender/approver_employee_id ले नै अधिकार तोक्छ), त्यसैले यहाँ role middleware छैन — controller भित्रै जाँचिन्छ
    Route::post('/overtime/{id}/recommend', [OvertimeController::class, 'recommend'])->name('overtime.recommend');
    Route::post('/overtime/{id}/verify', [OvertimeController::class, 'verify'])->name('overtime.verify');
    Route::post('/overtime/{id}/reject', [OvertimeController::class, 'reject'])->name('overtime.reject');

    Route::get('/overtime/verified', [OvertimeController::class, 'verifiedList'])->middleware('role:overtime.unverify')->name('overtime.verified');
    Route::post('/overtime/{id}/unverify', [OvertimeController::class, 'unverify'])->middleware('role:overtime.unverify')->name('overtime.unverify');

    // ------------------------------------------
    // Employees Resource Route
    // ------------------------------------------
    Route::resource('employees', EmployeeController::class)->middleware('role:employees.manage');

    // ------------------------------------------
    // Settings & Allowance Routes
    // ------------------------------------------
    Route::get('/settings', [MasterSettingsController::class, 'index'])->middleware('role:settings.manage')->name('settings.index');
    Route::put('/settings/allowance/{id}', [MasterSettingsController::class, 'updateAllowance'])->middleware('role:settings.manage')->name('settings.updateAllowance');
    Route::post('/settings/allowance/store', [MasterSettingsController::class, 'storeAllowance'])->middleware('role:settings.manage')->name('settings.storeAllowance');
    Route::delete('/settings/allowance/delete/{id}', [MasterSettingsController::class, 'destroyAllowance'])->middleware('role:settings.manage')->name('settings.destroyAllowance');
    Route::get('/settings/snack', [MasterSettingsController::class, 'snackIndex'])->middleware('role:settings.manage')->name('settings.snack');

    // ------------------------------------------
    // Shift Settings Routes
    // ------------------------------------------
    Route::get('/settings/shifts', [MasterSettingsController::class, 'shiftIndex'])->middleware('role:settings.manage')->name('shifts.index');
    Route::post('/settings/shifts/store', [MasterSettingsController::class, 'shiftStore'])->middleware('role:settings.manage')->name('shifts.store');
    Route::put('/settings/shifts/update/{id}', [MasterSettingsController::class, 'shiftUpdate'])->middleware('role:settings.manage')->name('shifts.update');
    Route::delete('/settings/shifts/delete/{id}', [MasterSettingsController::class, 'shiftDestroy'])->middleware('role:settings.manage')->name('shifts.destroy');

    Route::get('/settings/holidays', [MasterSettingsController::class, 'holidayIndex'])->middleware('role:settings.manage')->name('holidays.index');
    Route::post('/settings/holidays/store', [MasterSettingsController::class, 'holidayStore'])->middleware('role:settings.manage')->name('holidays.store');
    Route::put('/settings/holidays/update/{id}', [MasterSettingsController::class, 'holidayUpdate'])->middleware('role:settings.manage')->name('holidays.update');
    Route::delete('/settings/holidays/delete/{id}', [MasterSettingsController::class, 'holidayDestroy'])->middleware('role:settings.manage')->name('holidays.destroy');

    // ------------------------------------------
    // Event Routes
    // ------------------------------------------
    Route::get('/events/list', [EventController::class, 'index'])->name('events.list');
    Route::get('/events/{id}/ot-details', [EventController::class, 'otDetails'])->name('events.ot-details');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events/store', [EventController::class, 'store'])->name('events.store');
    Route::post('/events/{id}/toggle', [EventController::class, 'toggleActive'])->middleware('role:events.manage')->name('events.toggle');
    Route::get('/events/{id}/print', [OvertimeController::class, 'printEventSlip'])->middleware('role:overtime.entry')->name('events.print');
    Route::get('/events/{id}/edit', [EventController::class, 'edit'])->middleware('role:events.manage')->name('events.edit');
    Route::put('/events/{id}', [EventController::class, 'update'])->middleware('role:events.manage')->name('events.update');

    // Event Approval Workflow: Submit (creator) -> Recommend (recommender) -> Approve (approver), Reject जुनसुकै stage मा -> Draft
    // यहाँ पनि role middleware छैन — Event मा तोकिएको creator/recommender/approver employee अनुसार अधिकार controller भित्रै जाँचिन्छ
    Route::post('/events/{id}/submit', [EventController::class, 'submit'])->name('events.submit');
    Route::post('/events/{id}/recommend', [EventController::class, 'recommend'])->name('events.recommend');
    Route::post('/events/{id}/approve', [EventController::class, 'approve'])->name('events.approve');
    Route::post('/events/{id}/reject', [EventController::class, 'reject'])->name('events.reject');
    // छुट्टै Event-level Approval गेट (OT भर्नुअघि नै सिफारिस गर्नेले Event Approve गर्ने)
    Route::post('/events/{id}/approve-creation', [EventController::class, 'approveEventCreation'])->name('events.approveCreation');
    Route::post('/events/{id}/reject-creation', [EventController::class, 'rejectEventCreation'])->name('events.rejectCreation');
    Route::post('/events/{id}/resubmit-approval', [EventController::class, 'resubmitEventApproval'])->name('events.resubmitApproval');

    // ------------------------------------------
    // User Management Routes
    // ------------------------------------------
    Route::get('/users/create', [UserController::class, 'create'])->middleware('role:users.manage')->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware('role:users.manage')->name('users.store');
    Route::get('/users', [UserController::class, 'index'])->middleware('role:users.manage')->name('users.index');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('role:users.manage')->name('users.destroy');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->middleware('role:users.manage')->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('role:users.manage')->name('users.update');

    // ------------------------------------------
    // Report Routes
    // ------------------------------------------
    Route::get('/reports', [OvertimeController::class, 'generateReport'])->middleware('role:reports.view')->name('reports.index');
    Route::get('/reports/export-excel', [OvertimeController::class, 'exportExcel'])->middleware('role:reports.view')->name('reports.excel');
    Route::get('/reports/export-summary', [OvertimeController::class, 'exportSummaryExcel'])->middleware('role:reports.view')->name('reports.exportSummaryExcel');
    Route::get('/reports/export-pivot', [OvertimeController::class, 'exportPivotExcel'])->middleware('role:reports.view')->name('reports.exportPivot');
    Route::get('/reports/summary', [OvertimeController::class, 'summaryreport'])->middleware('role:reports.view')->name('reports.summary');
    Route::get('/reports/finance/export', [OvertimeController::class, 'exportFinanceExcel'])->middleware('role:reports.view')->name('reports.exportFinanceExcel');
    Route::get('/reports/finance', [OvertimeController::class, 'financeReport'])->middleware('role:reports.view')->name('reports.finance');
    Route::post('/reports/finance/update', [OvertimeController::class, 'updateFinanceData'])->middleware('role:reports.view')->name('reports.updateFinanceData');

    // ------------------------------------------
    // Position Settings Routes
    // ------------------------------------------
    Route::get('/settings/positions', [PositionController::class, 'index'])->middleware('role:positions.manage')->name('positions.index');
    Route::post('/settings/positions', [PositionController::class, 'store'])->middleware('role:positions.manage')->name('positions.store');
    Route::put('/settings/positions/{id}/rate', [PositionController::class, 'updateRate'])->middleware('role:positions.manage')->name('positions.updateRate');
    Route::delete('/settings/positions/{id}', [PositionController::class, 'destroy'])->middleware('role:positions.manage')->name('positions.destroy');

    // ------------------------------------------
    // Petrol Month Routes
    // ------------------------------------------
    Route::get('/petrol/months', [PetrolMonthController::class, 'index'])->middleware('role:petrol.months.manage')->name('petrol.months.index');
    Route::post('/petrol/months', [PetrolMonthController::class, 'store'])->middleware('role:petrol.months.manage')->name('petrol.months.store');
    Route::delete('/petrol/months/{id}', [PetrolMonthController::class, 'destroy'])->middleware('role:petrol.months.manage')->name('petrol.months.destroy');
    Route::post('/petrol/months/{id}/toggle-status', [PetrolMonthController::class, 'toggleStatus'])->middleware('role:petrol.months.manage')->name('petrol.months.toggleStatus');
    Route::post('/petrol/months/{id}/extend', [PetrolMonthController::class, 'extendDeadline'])->middleware('role:petrol.months.manage')->name('petrol.months.extend');
    Route::post('/petrol/months/{id}/clear-extension', [PetrolMonthController::class, 'clearExtension'])->middleware('role:petrol.months.manage')->name('petrol.months.clearExtension');

    // ------------------------------------------
    // Petrol Bill Routes
    // ------------------------------------------
    Route::get('/petrol/bills', [PetrolBillController::class, 'index'])->middleware('role:petrol.bills.view')->name('petrol.bills.index');
    Route::get('/petrol/bills/create', [PetrolBillController::class, 'create'])->name('petrol.bills.create');
    Route::post('/petrol/bills', [PetrolBillController::class, 'store'])->name('petrol.bills.store');
    Route::get('/petrol/bills/{id}/edit', [PetrolBillController::class, 'edit'])->middleware('role:petrol.bills.view')->name('petrol.bills.edit');
    Route::put('/petrol/bills/{id}', [PetrolBillController::class, 'update'])->middleware('role:petrol.bills.view')->name('petrol.bills.update');
    Route::delete('/petrol/bills/{id}', [PetrolBillController::class, 'destroy'])->middleware('role:petrol.bills.manage')->name('petrol.bills.destroy');
    Route::post('/petrol/bills/{id}/toggle-edit', [PetrolBillController::class, 'toggleEditPermission'])->middleware('role:petrol.bills.manage')->name('petrol.bills.toggleEdit');
    Route::get('/petrol/bills/{id}/print', [PetrolBillController::class, 'printBill'])->middleware('role:petrol.bills.view')->name('petrol.bills.print');

    // ------------------------------------------
    // Repair Expense Routes
    // ------------------------------------------
    Route::get('/repair/expenses', [RepairExpenseController::class, 'index'])->middleware('role:repair.expenses.view')->name('repair.expenses.index');
    Route::get('/repair/expenses/create', [RepairExpenseController::class, 'create'])->name('repair.expenses.create');
    Route::post('/repair/expenses', [RepairExpenseController::class, 'store'])->name('repair.expenses.store');
    Route::get('/repair/expenses/{id}/edit', [RepairExpenseController::class, 'edit'])->middleware('role:repair.expenses.view')->name('repair.expenses.edit');
    Route::put('/repair/expenses/{id}', [RepairExpenseController::class, 'update'])->middleware('role:repair.expenses.view')->name('repair.expenses.update');
    Route::delete('/repair/expenses/{id}', [RepairExpenseController::class, 'destroy'])->middleware('role:repair.expenses.manage')->name('repair.expenses.destroy');
    Route::post('/repair/expenses/{id}/toggle-edit', [RepairExpenseController::class, 'toggleEditPermission'])->middleware('role:repair.expenses.manage')->name('repair.expenses.toggleEdit');
    Route::get('/repair/expenses/{id}/print', [RepairExpenseController::class, 'printExpense'])->middleware('role:repair.expenses.view')->name('repair.expenses.print');

    // ------------------------------------------
    // आफ्नो Vehicle No अपडेट गर्ने (Profile पेजबाट)
    // ------------------------------------------
    Route::patch('/profile/vehicle', [EmployeeController::class, 'updateOwnVehicle'])->name('profile.vehicle.update');
    Route::get('/hr-sync', [HrSyncController::class, 'index'])->middleware('role:hr.sync')->name('hr-sync.index');
    Route::post('/hr-sync/run', [HrSyncController::class, 'run'])->middleware('role:hr.sync')->name('hr-sync.run');
    Route::post('/hr-sync/run-departments', [HrSyncController::class, 'runDepartments'])->middleware('role:hr.sync')->name('hr-sync.run-departments');
    Route::post('/hr-sync/run-positions', [HrSyncController::class, 'runPositions'])->middleware('role:hr.sync')->name('hr-sync.run-positions');
    Route::post('/hr-sync/run-employees', [HrSyncController::class, 'runEmployees'])->middleware('role:hr.sync')->name('hr-sync.run-employees');
Route::middleware(['role:petrol.bills.entry,petrol.bills.manage'])->group(function () {
    // petrol routes...

    });
    Route::post('/hr-sync/run-holidays', [HrSyncController::class, 'runHolidays'])->name('hr-sync.run-holidays');
    

Route::get('/debug-hr/leave', function () {
    $data = HrController::getLeave(4, '2083-01-01', '2083-04-30'); // empId, fromDate, toDate - आफ्नो valid empId/date राख्नुहोस्
    dd($data);
});

Route::get('/debug-hr/attendance', function () {
    $data = HrController::getAttendance('2083-01-01', '2083-04-30'); // empId नदिए सबैको आउँछ
    dd($data);
});

Route::get('/debug-hr/holiday', function () {
    $data = HrController::getHoliday(null, null, '2083'); // fiscalYear दिएर
    dd($data);
});
});


