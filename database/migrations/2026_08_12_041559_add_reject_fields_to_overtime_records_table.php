<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            if (!Schema::hasColumn('overtime_records', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('remarks');
            }
            if (!Schema::hasColumn('overtime_records', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('verified_at')
                      ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('overtime_records', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['rejection_reason', 'rejected_by', 'rejected_at']);
        });
    }
};