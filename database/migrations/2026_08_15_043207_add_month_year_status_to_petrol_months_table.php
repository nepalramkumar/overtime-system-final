<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petrol_months', function (Blueprint $table) {
            if (!Schema::hasColumn('petrol_months', 'month')) {
                $table->string('month')->after('id');
            }
            if (!Schema::hasColumn('petrol_months', 'year')) {
                $table->string('year')->after('month');
            }
            if (!Schema::hasColumn('petrol_months', 'status')) {
                $table->boolean('status')->default(1)->after('year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('petrol_months', function (Blueprint $table) {
            $table->dropColumn(['month', 'year', 'status']);
        });
    }
};