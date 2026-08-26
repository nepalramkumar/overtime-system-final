<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PetrolMonth;
use App\Http\Controllers\PetrolMonthController;

return new class extends Migration
{
    // Petrol Month अब auto-compute हुने भएकोले (हरेक BS महिनाको १ गते खुल्ने, अर्को महिनाको ५ गते
    // सम्म रहने) यी date column हरू थप्ने:
    // - start_date : महिना खुल्ने मिति (AD) — BS महिनाको १ गते
    // - end_date    : डिफल्ट बन्द हुने मिति (AD) — अर्को BS महिनाको ५ गते
    // - extended_end_date : Admin ले थप दिन दिएमा (default भन्दा पछाडिको मिति)
    public function up(): void
    {
        Schema::table('petrol_months', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('year');
            $table->date('end_date')->nullable()->after('start_date');
            $table->date('extended_end_date')->nullable()->after('end_date');
        });

        // पुराना (यो migration अघिका) record हरूको लागि पनि start_date/end_date backfill गर्ने,
        // नत्र तिनीहरू date-based scopeActive() मा कहिल्यै "active" देखिँदैनन्।
        PetrolMonth::withTrashed()->whereNull('start_date')->each(function (PetrolMonth $pm) {
            $monthIndex = array_search($pm->month, PetrolMonthController::BS_MONTHS); // 0-based
            if ($monthIndex === false) {
                return;
            }

            $bsMonth = $monthIndex + 1;
            $bsYear  = (int) $pm->year;

            $nextBsMonth = $bsMonth + 1;
            $nextBsYear  = $bsYear;
            if ($nextBsMonth > 12) {
                $nextBsMonth = 1;
                $nextBsYear++;
            }

            $startBs = sprintf('%04d-%02d-01', $bsYear, $bsMonth);
            $endBs   = sprintf('%04d-%02d-05', $nextBsYear, $nextBsMonth);

            $pm->start_date = bsToAd($startBs) ?: null;
            $pm->end_date   = bsToAd($endBs) ?: null;
            $pm->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('petrol_months', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'extended_end_date']);
        });
    }
};
