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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('designation');      // पद (जस्तै: D1, D2)
            $table->string('department');       // विभाग
            $table->decimal('ot_rate', 10, 2);  // व्यक्तिगत OT Rate
            $table->boolean('is_active')->default(true);
            $table->softDeletes();              // कर्मचारी हटाउँदा पुराना रेकرد जोगाउन Soft Delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
