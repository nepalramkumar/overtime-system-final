<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // एउटै "logical" OT entry (एउटै form submission) ले कहिलेकाहीं Before Office + After Office गरी
    // २ वटा DB row बनाउँछ। पहिले Edit गर्दा तिनीहरूलाई सफा गर्न date+employee_id+event_id+purpose_id
    // ले पुरानो row(s) खोजिन्थ्यो — तर सोही मिति/employee मा भएका *अरू छुट्टाछुट्टै* entry हरू पनि
    // उही ४ field मिल्ने भएकोले गलतीले delete हुन्थे (data-loss bug)। यो column ले हरेक entry लाई
    // यसको आफ्नै unique group id दिन्छ, त्यसैले Edit गर्दा ठ्याक्कै त्यही entry का row(s) मात्र छोइन्छन्।
    public function up(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            $table->string('entry_group')->nullable()->after('id')->index();
        });

        // पुराना (migration अघिका) record हरूलाई सुरक्षित default दिने: हरेक लाई आ-आफ्नै छुट्टै group
        // (यसो नगरे भविष्यमा तिनीहरूको entry_group NULL रहन्थ्यो, जुन फेरि उस्तै grouping bug निम्त्याउन सक्थ्यो)
        DB::statement("UPDATE overtime_records SET entry_group = CONCAT('legacy-', id) WHERE entry_group IS NULL");
    }

    public function down(): void
    {
        Schema::table('overtime_records', function (Blueprint $table) {
            $table->dropColumn('entry_group');
        });
    }
};
