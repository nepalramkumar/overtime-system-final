<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE employees MODIFY designation VARCHAR(255) NULL');
        DB::statement('ALTER TABLE employees MODIFY ot_rate DECIMAL(10,2) NULL');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE employees MODIFY designation VARCHAR(255) NOT NULL DEFAULT ''");
        DB::statement('ALTER TABLE employees MODIFY ot_rate DECIMAL(10,2) NOT NULL DEFAULT 0');
    }
};