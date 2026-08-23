<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petrol_months', function (Blueprint $table) {
            if (!Schema::hasColumn('petrol_months', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('petrol_months', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};