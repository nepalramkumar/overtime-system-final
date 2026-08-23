<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            if (!Schema::hasColumn('overtime_records', 'purpose')) {
                $table->string('purpose')->nullable()->after('remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            $table->dropColumn('purpose');
        });
    }
};