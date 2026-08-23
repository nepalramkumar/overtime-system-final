<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            // Individual OT (event_id null) को लागि — Event मा जस्तै सिफारिस/स्वीकृति गर्ने तोक्ने
            if (!Schema::hasColumn('overtime_records', 'recommender_employee_id')) {
                $table->unsignedBigInteger('recommender_employee_id')->nullable()->after('purpose_id');
                $table->foreign('recommender_employee_id')->references('id')->on('employees')->nullOnDelete();
            }
            if (!Schema::hasColumn('overtime_records', 'approver_employee_id')) {
                $table->unsignedBigInteger('approver_employee_id')->nullable()->after('recommender_employee_id');
                $table->foreign('approver_employee_id')->references('id')->on('employees')->nullOnDelete();
            }

            // सिफारिस (Recommend) गरेको बेला को (कुन User) ले र कहिले भन्ने track (Approve को लागि verified_by/verified_at पहिल्यै छ)
            if (!Schema::hasColumn('overtime_records', 'recommended_by')) {
                $table->foreignId('recommended_by')->nullable()->after('purpose_id')
                      ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('overtime_records', 'recommended_at')) {
                $table->timestamp('recommended_at')->nullable()->after('recommended_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            if (Schema::hasColumn('overtime_records', 'recommender_employee_id')) {
                $table->dropForeign(['recommender_employee_id']);
            }
            if (Schema::hasColumn('overtime_records', 'approver_employee_id')) {
                $table->dropForeign(['approver_employee_id']);
            }
            if (Schema::hasColumn('overtime_records', 'recommended_by')) {
                $table->dropForeign(['recommended_by']);
            }
            $table->dropColumn([
                'recommender_employee_id', 'approver_employee_id',
                'recommended_by', 'recommended_at',
            ]);
        });
    }
};
