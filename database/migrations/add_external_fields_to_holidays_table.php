<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            if (!Schema::hasColumn('holidays', 'external_holiday_id')) {
                // HR API को holidayId - एउटै multi-day holiday भित्रका सबै date row ले यही ID share गर्छन्
                $table->unsignedBigInteger('external_holiday_id')->nullable()->index()->after('bs_year');
            }
            if (!Schema::hasColumn('holidays', 'source')) {
                // 'manual' (यो form बाट थपेको) वा 'hr_sync' (HR API बाट आयातित)
                $table->string('source')->default('manual')->after('external_holiday_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            if (Schema::hasColumn('holidays', 'external_holiday_id')) {
                $table->dropColumn('external_holiday_id');
            }
            if (Schema::hasColumn('holidays', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};
