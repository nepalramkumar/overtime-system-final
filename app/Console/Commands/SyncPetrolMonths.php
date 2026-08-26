<?php

namespace App\Console\Commands;

use App\Http\Controllers\PetrolMonthController;
use App\Models\PetrolMonth;
use Illuminate\Console\Command;

class SyncPetrolMonths extends Command
{
    protected $signature = 'petrol:sync-months';
    protected $description = 'हालको BS महिनाको Petrol Month record auto-create गर्ने (हरेक १ गते खुल्नुपर्ने, manual "थप्नुहोस्" नगरिकनै)';

    public function handle(): int
    {
        $bs = adToBs(now()->format('Y-m-d'));
        [$bsYear, $bsMonthNum] = array_map('intval', explode('-', $bs));

        $monthName = PetrolMonthController::BS_MONTHS[$bsMonthNum - 1] ?? null;
        if (!$monthName) {
            $this->error('अहिलेको BS महिना पत्ता लाग्न सकेन।');
            return self::FAILURE;
        }

        $existing = PetrolMonth::where('month', $monthName)->where('year', $bsYear)->exists();

        $month = PetrolMonth::ensureExists($monthName, $bsYear);

        if ($existing) {
            $this->info("{$monthName} {$bsYear} पहिले नै existed थियो, केही परिवर्तन गरिएन।");
        } else {
            $this->info("{$monthName} {$bsYear} auto-create गरियो (Start: {$month->start_date->format('Y-m-d')}, End: {$month->end_date->format('Y-m-d')})।");
        }

        return self::SUCCESS;
    }
}
