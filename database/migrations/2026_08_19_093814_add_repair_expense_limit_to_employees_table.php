<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // यदि कोलम छैन भने थप्ने
            if (!Schema::hasColumn('employees', 'repair_expense_limit')) {
                $table->integer('repair_expense_limit')->default(8000)->after('id');
            } else {
                // यदि कोलम पहिले नै छ भने Modify गर्ने
                $table->integer('repair_expense_limit')->default(8000)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'repair_expense_limit')) {
                $table->dropColumn('repair_expense_limit');
            }
        });
    }
};