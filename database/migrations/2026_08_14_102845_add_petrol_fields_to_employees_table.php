<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'vehicle_no')) {
                $table->string('vehicle_no')->nullable()->after('department');
            }
            if (!Schema::hasColumn('employees', 'petrol_quantity_limit')) {
                $table->integer('petrol_quantity_limit')->default(20)->after('vehicle_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['vehicle_no', 'petrol_quantity_limit']);
        });
    }
};