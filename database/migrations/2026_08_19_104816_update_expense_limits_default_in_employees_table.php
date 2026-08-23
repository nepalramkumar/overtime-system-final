<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // पेट्रोल र मर्मत खर्चको डिफल्ट भ्यालु यहाँ एउटैमा मिलाउन सक्नुहुन्छ
            $table->integer('petrol_quantity_limit')->default(25)->change(); // जस्तै: २५ लिटर
            $table->integer('repair_expense_limit')->default(8000)->change(); // जस्तै: ८००० रुपैयाँ
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->integer('petrol_quantity_limit')->default(0)->change();
            $table->integer('repair_expense_limit')->default(0)->change();
        });
    }
};