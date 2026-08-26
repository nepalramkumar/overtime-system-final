<?php

namespace App\Services;

use App\Http\Controllers\HrController;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Position;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class HrSyncService
{
    protected UserProvisioningService $provisioningService;

    protected array $summary = [
        'departments_synced' => 0,
        'positions_synced'   => 0,
        'employees_created'  => 0,
        'employees_updated'  => 0,
        'users_created'      => 0,
        'holidays_synced'    => 0,
        'errors'             => [],
    ];

    public function __construct(UserProvisioningService $provisioningService)
    {
        $this->provisioningService = $provisioningService;
    }

    public function runFullSync(): array
    {
        $this->syncDepartments();
        $this->syncPositions();
        $this->syncEmployees();

        return $this->summary;
    }

    // छुट्टाछुट्टै चलाउन सकिने public wrapper — कुनै एउटा मात्र sync गर्नुपर्दा
    public function runDepartmentsOnly(): array
    {
        $this->syncDepartments();
        return $this->summary;
    }

    public function runPositionsOnly(): array
    {
        $this->syncPositions();
        return $this->summary;
    }

    public function runEmployeesOnly(): array
    {
        $this->syncEmployees();
        return $this->summary;
    }

    // fiscalYear (BS, जस्तै "2083") दिनुपर्छ, नदिए null - HrController::getHoliday ले fromDate/toDate चाहिन्छ भन्ने अर्थ हुन्छ
    public function runHolidaysOnly(?string $fiscalYear = null): array
    {
        $this->syncHolidays($fiscalYear);
        return $this->summary;
    }

    // हाइरार्की कन्भर्ट गर्ने नियम (Logic)
    protected function mapHierarchy($hierarchy): int
    {
        $hierarchy = (int) $hierarchy;

        $map = [
            1  => 12,
            2  => 11,
            3  => 10,
            4  => 9,
            5  => 8,
            6  => 7,
            7  => 6,
            8  => 5,
            9  => 4,
            10 => 3,
        ];

        return $map[$hierarchy] ?? 1; // अन्य (Other) भएमा 1
    }

    protected function syncDepartments(): void
    {
        try {
            $list = HrController::getDepartmentList();
            foreach ($list->data ?? [] as $dept) {
                $name = $dept->departmentName ?? null;
                if (!$name) continue;

                Department::firstOrCreate(['name' => trim($name)]);
                $this->summary['departments_synced']++;
            }
        } catch (\Exception $e) {
            $this->logError('Department sync error: ' . $e->getMessage());
        }
    }

    protected function syncPositions(): void
    {
        try {
            $list = HrController::getDesignationList();
           
            foreach ($list->data ?? [] as $designation) {
                $name = $designation->name ?? null;
                if (!$name) continue;

                // API बाट आएको hierarchy लाई म्याप गर्ने
                $rawHierarchy = $designation->hierarchy ?? 0;
                $mappedHierarchy = $this->mapHierarchy($rawHierarchy);

                // पद सिर्जना गर्ने
                $position = Position::firstOrCreate(
                    ['name' => trim($name)],
                    [
                        'ot_rate'   => 0,
                        'is_active' => true
                    ]
                );

                // म्याप गरिएको level अपडेट गर्ने
                $position->update([
                    'level' => $mappedHierarchy
                ]);

                $this->summary['positions_synced']++;
            }
        } catch (\Exception $e) {
            $this->logError('Position sync error: ' . $e->getMessage());
        }
    }

    protected function syncEmployees(): void
    {
        try {
            $list = HrController::getEmployeeList();
           
            foreach ($list->data ?? [] as $emp) {
                try {
                    $this->syncOneEmployee($emp);
                } catch (\Exception $e) {
                    $empCode = $emp->employeeCode ?? 'unknown';
                    $this->logError("Employee ({$empCode}) error: " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            $this->logError('Employee list fetch error: ' . $e->getMessage());
        }
    }

    protected function syncHolidays(?string $fiscalYear = null): void
    {
        try {
            $list = HrController::getHoliday(null, null, $fiscalYear);

            foreach ($list->data ?? [] as $holiday) {
                $holidayId = $holiday->holidayId ?? null;
                if (!$holidayId) continue;

                $name      = $holiday->holidayName ?? 'N/A';
                $startDate = $this->msToDate($holiday->holidayStartDateAD ?? null);
                $endDate   = $this->msToDate($holiday->holidayEndDateAD ?? null) ?: $startDate;

                if (!$startDate) continue;

                // Multi-day holiday भए range भित्रको हरेक दिनको लागि छुट्टै row (existing table single-date per row हो)
                $cursor = \Carbon\Carbon::parse($startDate);
                $end    = \Carbon\Carbon::parse($endDate);

                while ($cursor->lte($end)) {
                    $adDateStr = $cursor->toDateString();
                    $bsDateStr = function_exists('adToBs') ? adToBs($adDateStr) : null;
                    $bsYear    = $bsDateStr ? (int) substr($bsDateStr, 0, 4) : null;

                    Holiday::updateOrCreate(
                        ['date' => $adDateStr],
                        [
                            'name'                 => $name,
                            'bs_year'              => $bsYear,
                            'external_holiday_id'  => $holidayId,
                            'source'               => 'hr_sync',
                        ]
                    );

                    $cursor->addDay();
                }

                $this->summary['holidays_synced']++;
            }
        } catch (\Exception $e) {
            $this->logError('Holiday sync error: ' . $e->getMessage());
        }
    }

    // HR API ले AD date लाई epoch milliseconds मा दिन्छ, त्यसलाई Y-m-d मा बदल्ने
    protected function msToDate($ms): ?string
    {
        if (empty($ms)) {
            return null;
        }
        return \Carbon\Carbon::createFromTimestamp(intdiv((int) $ms, 1000))->toDateString();
    }

    protected function syncOneEmployee(object|array $emp): void
    {
        $emp = (object) $emp;

        if (!($emp->active ?? false)) {
            return;
        }

        $employeeCode = trim($emp->employeeCode ?? '');
        if (empty($employeeCode)) {
            return;
        }

        // Sanitization
        $rawEmail    = trim($emp->email ?? '');
        $rawDeptName = trim($emp->department ?? '');

        $name        = trim($emp->name ?? '') ?: 'N/A';
        $mobile      = $emp->mobile ?? null;
        $designation = $emp->designation ?? null;
        
        // कर्मचारीको हकमा पनि hierarchy म्याप गर्ने
        $rawHierarchy = $emp->hierarchy ?? 1;
        $hierarchy    = $this->mapHierarchy($rawHierarchy);
        
        $externalId  = $emp->id ?? null;

        $email = !empty($rawEmail) ? $rawEmail : null;
        $deptName = !empty($rawDeptName) ? $rawDeptName : 'Unassigned';

        // Department Sync
        Department::firstOrCreate(['name' => $deptName]);
        
        // Position & Level Sync
        $positionId = null;
        if ($designation) {
            $position = Position::firstOrCreate(
                ['name' => trim($designation)],
                [
                    'ot_rate'   => 0,
                    'is_active' => true
                ]
            );

            $position->update([
                'level' => $hierarchy
            ]);

            $positionId = $position->id;
        }

        $employeeData = [
            'external_staff_id'    => $externalId,
            'email'                => $email,
            'mobile'               => $mobile,
            'designation'          => $designation,
            'department'           => $deptName,
            'position_id'          => $positionId,
            'hierarchy'            => $hierarchy, // म्याप गरिएको सही हाइरार्की
            'is_active'            => true,
            'last_synced_at'       => now(),
            'repair_expense_limit' => 8000, 
            'petrol_quantity_limit'=> 25,  
        ];

        $employee = Employee::where('employee_code', $employeeCode)
            ->when($email, function ($query) use ($email) {
                $query->orWhere('email', $email);
            })
            ->first();

        if ($employee) {
            $employee->update(array_merge($employeeData, ['employee_code' => $employeeCode]));
            $this->summary['employees_updated']++;
        } else {
            $employee = Employee::create(array_merge($employeeData, [
                'employee_code' => $employeeCode,
                'name'          => $name,
            ]));
            $this->summary['employees_created']++;
        }

        try {
            $user = $this->provisioningService->provisionFor($employee);
            if ($user && $user->wasRecentlyCreated) {
                $this->summary['users_created']++;
            }

            if ($user) {
                if (Schema::hasColumn('employees', 'user_id') && $employee->user_id !== $user->id) {
                    $employee->user_id = $user->id;
                    $employee->save();
                }

                if (Schema::hasColumn('users', 'employee_id') && $user->employee_id !== $employee->id) {
                    $user->employee_id = $employee->id;
                    $user->save();
                }
            }

        } catch (\Exception $e) {
            $this->logError("User provisioning/mail failed for ({$employeeCode}): " . $e->getMessage());
        }
    }

    protected function logError(string $message): void
    {
        $this->summary['errors'][] = $message;
        Log::error("HR Sync - {$message}");
    }
}