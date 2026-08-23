<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    Schema::table('employees', function (Blueprint $table) {
        if (!Schema::hasColumn('employees', 'employee_code')) {
            $table->string('employee_code')->unique()->after('id');
        }
        if (!Schema::hasColumn('employees', 'external_staff_id')) {
            $table->string('external_staff_id')->nullable()->after('employee_code');
        }
        if (!Schema::hasColumn('employees', 'position_id')) {
            $table->foreignId('position_id')->nullable()->after('department')
                  ->constrained()->nullOnDelete();
        }
        if (!Schema::hasColumn('employees', 'last_synced_at')) {
            $table->timestamp('last_synced_at')->nullable();
        }
    });
}

public function down(): void
{
    Schema::table('employees', function (Blueprint $table) {
        $table->dropForeign(['position_id']);
        $table->dropColumn(['employee_code', 'external_staff_id', 'position_id', 'last_synced_at']);
    });
}
};