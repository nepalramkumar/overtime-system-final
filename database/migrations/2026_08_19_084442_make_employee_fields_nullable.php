<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // यी कोलमहरूमा NULL डेटा राख्न पाइने बनाउने
            $table->string('department')->nullable()->change();
            $table->string('designation')->nullable()->change();
            $table->foreignId('position_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // रोलब्याक (rollback) गर्दा साविककै अवस्थामा फर्काउने
            $table->string('department')->nullable(false)->change();
            $table->string('designation')->nullable(false)->change();
            $table->foreignId('position_id')->nullable(false)->change();
        });
    }
};