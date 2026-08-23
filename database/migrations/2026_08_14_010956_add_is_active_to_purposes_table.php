<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purposes', function (Blueprint $table) {
            if (!Schema::hasColumn('purposes', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purposes', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};