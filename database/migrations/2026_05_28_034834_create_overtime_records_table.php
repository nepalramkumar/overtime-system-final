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
        Schema::create('overtime_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            // यदि विशेष कार्यक्रम भए ID बस्छ, सामान्य (General OT) भए null हुन्छ (सेक्सन ३)
            $table->unsignedBigInteger('event_id')->nullable(); 
            
            $table->date('ot_date');            // अतिरिक्त समय काम गरेको मिति (सेक्सन ४.२)
            $table->time('from_time');          // काम सुरु भएको समय (सेक्सन ४.२)
            $table->time('to_time');            // काम सकिएको समय (सेक्सन ४.२)
            $table->decimal('total_hours', 5, 2); // गणना गरिएको कुल घण्टा (सेक्सन ४.२)
            
            // ऐतिहासिक डाटा सुरक्षा: ओभरटाइम दर्ता हुँदाको बखतको पद र दर सुरक्षित राखिन्छ (सेक्सन २.१)
            $table->string('designation_snapshot'); 
            $table->decimal('ot_rate_snapshot', 10, 2); 
            
            $table->decimal('tiffin_amount', 10, 2)->default(0.00); // खाजा खर्च रकम (सेक्सन २.३)
            
            // विदाको दिन हो कि होइन (सेक्सन ४.२)
            $table->boolean('is_holiday')->default(false); 
            
            // विभाजन प्रकार: 'Before Office', 'After Office', वा 'Holiday' (सेक्सन ४.२)
            $table->string('type'); 
            
            // स्वीकृतिको स्थिति: Pending, Recommended, Approved (सेक्सन १.१)
            $table->string('status')->default('Pending'); 
            
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_records');
    }
};
