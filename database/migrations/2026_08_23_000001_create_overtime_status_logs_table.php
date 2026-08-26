<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // कुनै पनि OT record (Individual वा Event-based दुबै) मा भएको हरेक status परिवर्तनको
    // पूरा इतिहास (who, when, from -> to, किन) राख्नको लागि — Reject गरेर फेरि Recommend गर्दा
    // पुरानो history नहराओस् भनेर छुट्टै table मा राखिएको (record को field हरू मात्र भरपर्दो होइनन्,
    // किनकि ती त पछिको action ले overwrite भइहाल्छन्)।
    public function up(): void
    {
        Schema::create('overtime_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overtime_record_id')->constrained('overtime_records')->cascadeOnDelete();
            $table->string('action');       // Submitted, Recommended, Verified, Rejected, Unverified आदि
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason')->nullable(); // Reject को कारण भएमा
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->index(['overtime_record_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_status_logs');
    }
};
