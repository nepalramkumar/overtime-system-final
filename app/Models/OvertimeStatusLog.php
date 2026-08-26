<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeStatusLog extends Model
{
    protected $fillable = [
        'overtime_record_id',
        'action',
        'from_status',
        'to_status',
        'performed_by',
        'reason',
        'performed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function overtimeRecord()
    {
        return $this->belongsTo(OvertimeRecord::class);
    }

    public function performedByUser()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // छिटो प्रयोगको लागि: कुनै पनि controller (Overtime वा Event) बाट यसरी एक लाइनमा log गर्न मिल्ने
    public static function record($overtimeRecordId, string $action, ?string $fromStatus, string $toStatus, ?string $reason = null, $performedBy = null): self
    {
        return self::create([
            'overtime_record_id' => $overtimeRecordId,
            'action'             => $action,
            'from_status'        => $fromStatus,
            'to_status'          => $toStatus,
            'performed_by'       => $performedBy ?? auth()->id(),
            'reason'             => $reason,
            'performed_at'       => now(),
        ]);
    }
}
