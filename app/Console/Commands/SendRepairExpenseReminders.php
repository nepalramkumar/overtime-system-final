<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\RepairExpense;
use App\Notifications\RepairExpenseReminderNotification;
use Illuminate\Console\Command;

class SendRepairExpenseReminders extends Command
{
    protected $signature = 'repair:remind-claim {--force : आजको मिति Ashad १ गते नभए पनि जबरजस्ती चलाउने (testing को लागि)} {--employee= : यही Employee ID लाई मात्र पठाउने (testing को लागि, अरूलाई छुट्टै)}';
    protected $description = 'हरेक वर्ष Ashad १ गते, चालु FY मा Repair Expense limit पूरा claim नगरेका (vehicle भएका) कर्मचारीलाई बाँकी रकमको Email/Notification पठाउने';

    // BS_MONTHS सूचीमा Ashad = index 2 (0-based) => महिना नम्बर 3
    protected const ASHAD_MONTH_NUM = 3;

    public function handle(): int
    {
        $bs = adToBs(now()->format('Y-m-d'));
        [$bsYear, $bsMonthNum, $bsDay] = array_map('intval', explode('-', $bs));

        if (($bsMonthNum !== self::ASHAD_MONTH_NUM || $bsDay !== 1) && !$this->option('force')) {
            $this->info('आज Ashad १ गते होइन, केही पठाइएन। (--force ले टेस्ट गर्न सकिन्छ)');
            return self::SUCCESS;
        }

        // Ashad यही bsYear को भएकोले, FY = (bsYear-1)/(bsYear) — RepairExpenseController::fyOptions() सँग मिल्दो
        // --force ले अरू महिनामा टेस्ट गर्दा पनि Ashad वर्ष अनुमान गर्न: bsMonthNum<4 भए यही वर्ष, नत्र यही वर्ष नै (जुनसुकै महिना भए पनि हालको Ashad-adjacent FY)
        $ashadYear = $bsMonthNum >= 4 ? $bsYear + 1 : $bsYear;
        $fyYear = ($ashadYear - 1) . '/' . $ashadYear;

        $query = $this->option('employee')
            ? Employee::where('id', $this->option('employee'))->with('user')
            : Employee::where('is_active', 1)
                ->whereNotNull('vehicle_no')
                ->where('vehicle_no', '!=', '')
                ->where('repair_expense_limit', '>', 0)
                ->with('user');

        $employees = $query->get();

        $sent = 0;
        foreach ($employees as $employee) {
            if (!$employee->user) {
                $this->warn("Employee #{$employee->id} ({$employee->name}) सँग कुनै User account link भएको छैन, skip गरियो।");
                continue;
            }

            $claimed = RepairExpense::where('employee_id', $employee->id)
                ->where('fy_year', $fyYear)
                ->sum('total_amount');

            $remaining = (float) $employee->repair_expense_limit - (float) $claimed;

            // "limit सम्म same FY मा claim गरेको छैन भने मात्र पठाउने" — तर --employee टेस्टको लागि भए यो शर्त पनि बाइपास (जबरजस्ती पठाउन)
            if ($remaining <= 0 && !$this->option('employee')) {
                continue;
            }

            $employee->user->notify(new RepairExpenseReminderNotification(
                $fyYear,
                (float) $employee->repair_expense_limit,
                (float) $claimed,
                $remaining
            ));
            $sent++;
            $this->info("→ पठाइयो: {$employee->name} ({$employee->user->email})");
        }

        $this->info("FY {$fyYear} मा Repair Expense बाँकी भएका {$sent} जना कर्मचारीलाई Notification पठाइयो।");
        return self::SUCCESS;
    }
}