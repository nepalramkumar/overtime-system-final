<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->string('fy_year'); // जस्तै "2083/2084"
            $table->json('date');
            $table->json('description');
            $table->json('amount');
            $table->decimal('total_amount', 10, 2);
            $table->text('remarks')->nullable();
            $table->boolean('isEdit')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_expenses');
    }
};
