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
    Schema::table('users', function (Blueprint $table) {
        // डिफल्ट 'employee' रोल राख्नु राम्रो हुन्छ
        $table->string('role')->default('employee')->after('email'); 
    });
}

    /**
     * Reverse the migrations.
     */
 public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}
};
