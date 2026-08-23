<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            if (Schema::hasColumn('overtime_records', 'purpose')) {
                $table->dropColumn('purpose');
            }
            if (!Schema::hasColumn('overtime_records', 'purpose_id')) {
                $table->foreignId('purpose_id')->nullable()->after('event_id')
                      ->constrained('purposes')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            $table->dropForeign(['purpose_id']);
            $table->dropColumn('purpose_id');
            $table->string('purpose')->nullable();
        });
    }
};