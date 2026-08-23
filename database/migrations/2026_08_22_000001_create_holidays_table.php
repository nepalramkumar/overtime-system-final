<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('holidays')) {
            return;
        }

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();      // AD मिति (bs-date-input बाट आउँछ, DB मा सधैं AD)
            $table->string('name');               // बिदाको नाम (जस्तै "नयाँ वर्ष", "गणतन्त्र दिवस")
            $table->unsignedSmallInteger('bs_year')->nullable(); // BS वर्ष (फिल्टर/group को लागि, जस्तै 2083
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
