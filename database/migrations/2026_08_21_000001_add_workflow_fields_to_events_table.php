<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // यो Event कसले बनायो (Submit गर्न पाउने हकदार पहिचान गर्नको लागि)
            if (!Schema::hasColumn('events', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('recommender_employee_id')
                      ->constrained('users')->nullOnDelete();
            }

            // Approval workflow को स्थिति: Draft -> Submitted -> Recommended -> Approved (Reject भए फेरि Draft)
            if (!Schema::hasColumn('events', 'workflow_status')) {
                $table->string('workflow_status')->default('Draft')->after('created_by');
            }

            if (!Schema::hasColumn('events', 'submitted_by')) {
                $table->foreignId('submitted_by')->nullable()->after('workflow_status')
                      ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('events', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('submitted_by');
            }

            if (!Schema::hasColumn('events', 'recommended_by')) {
                $table->foreignId('recommended_by')->nullable()->after('submitted_at')
                      ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('events', 'recommended_at')) {
                $table->timestamp('recommended_at')->nullable()->after('recommended_by');
            }

            if (!Schema::hasColumn('events', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('recommended_at')
                      ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('events', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }

            if (!Schema::hasColumn('events', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('approved_at')
                      ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('events', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('events', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            foreach (['created_by', 'submitted_by', 'recommended_by', 'approved_by', 'rejected_by'] as $fk) {
                if (Schema::hasColumn('events', $fk)) {
                    $table->dropForeign([$fk]);
                }
            }
            $table->dropColumn([
                'created_by', 'workflow_status',
                'submitted_by', 'submitted_at',
                'recommended_by', 'recommended_at',
                'approved_by', 'approved_at',
                'rejected_by', 'rejected_at', 'rejection_reason',
            ]);
        });
    }
};
