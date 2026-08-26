<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Event;

return new class extends Migration
{
    // यो एउटा *छुट्टै* Approval गेट हो — Event बन्नासाथ (OT entry सुरु हुनुअघि नै) सिफारिस गर्नेले
    // Event लाई नै Approve गर्नुपर्ने। यो हालको workflow_status (Submit→सिफारिस→स्वीकृति, जुन OT भरिसकेपछि
    // चल्छ) भन्दा फरक/independent column हो — दुबै एकैसाथ अस्तित्वमा रहन्छन्।
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('event_approval_status')->nullable()->after('workflow_status'); // Pending | Approved | Rejected
            $table->unsignedBigInteger('event_approved_by')->nullable()->after('event_approval_status');
            $table->timestamp('event_approved_at')->nullable()->after('event_approved_by');
            $table->unsignedBigInteger('event_rejected_by')->nullable()->after('event_approved_at');
            $table->timestamp('event_rejected_at')->nullable()->after('event_rejected_by');
            $table->text('event_rejection_reason')->nullable()->after('event_rejected_at');
        });

        // यो feature भन्दा अघि (वा यसको छिटो-अघिल्लो गलत संस्करणमा) बनिसकेका Event हरूलाई
        // पूर्वव्यापी रूपमा नयाँ gate ले नरोकोस् भनेर — पहिले नै अस्तित्वमा भएका सबैलाई Approved मानिन्छ।
        // (नयाँ बन्ने Event हरू भने डिफल्ट रूपमा Pending बाट सुरु हुन्छन् — कोडमा नै सेट हुन्छ।)
        Event::query()->update([
            'event_approval_status' => 'Approved',
            'event_approved_at'     => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'event_approval_status',
                'event_approved_by',
                'event_approved_at',
                'event_rejected_by',
                'event_rejected_at',
                'event_rejection_reason',
            ]);
        });
    }
};
