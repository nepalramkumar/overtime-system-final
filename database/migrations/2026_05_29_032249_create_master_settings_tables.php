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
        // office_shifts table
        Schema::create('office_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('day_name')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });

        // snack_allowances table
        Schema::create('snack_allowances', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_hours', 5, 2);
            $table->decimal('max_hours', 5, 2);
            $table->decimal('amount', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snack_allowances');
        Schema::dropIfExists('office_shifts');
    }
};