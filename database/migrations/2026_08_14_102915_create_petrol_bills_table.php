<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petrol_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->foreignId('petrol_month_id')->constrained('petrol_months');
            $table->json('quantity');
            $table->json('rate');
            $table->json('amount');
            $table->json('date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('total_quantity', 10, 2);
            $table->text('remarks')->nullable();
            $table->boolean('isEdit')->default(1);
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petrol_bills');
    }
};