<?php

namespace App\Console\Commands;

use App\Http\Controllers\PetrolMonthController;
use App\Models\Employee;
use App\Models\PetrolBill;
use App\Models\PetrolMonth;
use App\Notifications\PetrolBillReminderNotification;
use Illuminate\Console\Command;

class SendPetrolBillReminders extends Command
{
    protected $signature = 'petrol:remind-bill-entry {--force : आजको मिति १ गते नभए पनि जबरजस्ती चलाउने (testing को लागि)} {--employee= : यही Employee ID लाई मात्र पठाउने (testing को लागि, अरूलाई छुट्टै)}';
    protected $description = 'हरेक BS महिनाको १ गते, अघिल्लो महिनाको Petrol Bill दर्ता नगरेका (vehicle भएका) कर्मचारीलाई Email/Notification पठाउने';

    public function handle(): int
    {
        $bs = adToBs(now()->format('Y-m-d'));
        [$bsYear, $bsMonthNum, $bsDay] = array_map('intval', explode('-', $bs));

        // ठ्याक्कै महिनाको १ गते मात्र चलाउने (daily schedule बाट रोजाना चले पनि महिनाको एक पटक मात्र पठाउन)
        // --force दिएमा यो जाँच छाडेर जुनसुकै दिन पनि टेस्टको लागि चलाउन मिल्छ
        if ($bsDay !== 1 && !$this->option('force')) {
            $this->info('आज महिनाको १ गते होइन, केही पठाइएन। (--force ले टेस्ट गर्न सकिन्छ)');
            return self::SUCCESS;
        }

        $prevBsMonth = $bsMonthNum - 1;
        $prevBsYear  = $bsYear;
        if ($prevBsMonth < 1) {
            $prevBsMonth = 12;
            $prevBsYear--;
        }

        $prevMonthName = PetrolMonthController::BS_MONTHS[$prevBsMonth - 1] ?? null;
        if (!$prevMonthName) {
            $this->error('अघिल्लो BS महिना पत्ता लाग्न सकेन।');
            return self::FAILURE;
        }

        $prevMonth = PetrolMonth::where('month', $prevMonthName)->where('year', $prevBsYear)->first();
        if (!$prevMonth) {
            $this->info("{$prevMonthName} {$prevBsYear} को Petrol Month record भेटिएन, केही पठाइएन।");
            return self::SUCCESS;
        }

        $deadline = $prevMonth->effective_end_date ?? $prevMonth->end_date;
        if (!$deadline) {
            $this->info('Deadline मिति गणना भएको छैन, केही पठाइएन।');
            return self::SUCCESS;
        }

        // Vehicle No भएका सबै active employee, जसले अघिल्लो महिनाको Bill दर्ता गरिसकेका छैनन्
        $employeesWithBill = PetrolBill::where('petrol_month_id', $prevMonth->id)->pluck('employee_id');

        if ($this->option('employee')) {
            // --employee=ID दिएमा त्यही एक जना लाई मात्र, र टेस्टको लागि उसले Bill दर्ता गरिसके/नगरे जे भए पनि पठाउने
            $query = Employee::where('id', $this->option('employee'))->with('user');
        } else {
            $query = Employee::where('is_active', 1)
                ->whereNotNull('vehicle_no')
                ->where('vehicle_no', '!=', '')
                ->whereNotIn('id', $employeesWithBill)
                ->with('user');
        }

        $pendingEmployees = $query->get();

        $sent = 0;
        foreach ($pendingEmployees as $employee) {
            if (!$employee->user) {
                $this->warn("Employee #{$employee->id} ({$employee->name}) सँग कुनै User account link भएको छैन, skip गरियो।");
                continue;
            }
            $employee->user->notify(new PetrolBillReminderNotification($prevMonth, $deadline));
            $sent++;
            $this->info("→ पठाइयो: {$employee->name} ({$employee->user->email})");
        }

        $this->info("{$prevMonthName} {$prevBsYear} को Petrol Bill बाँकी भएका {$sent} जना कर्मचारीलाई Notification पठाइयो।");
        return self::SUCCESS;
    }
}