<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');       // कार्यक्रमको नाम
            $table->string('department');       // आयोजक विभाग
            $table->date('start_date');         // सुरु हुने मिति (एक वा धेरै दिनको लागि)
            $table->date('end_date');           // सकिने मिति
            $table->time('start_time')->nullable(); // समय
            $table->time('end_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};