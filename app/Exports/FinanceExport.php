<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FinanceExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        if (empty($this->data)) {
            return collect([['Error' => 'डेटा उपलब्ध छैन']]);
        }

        $rows = [];
        $sn = 1;

        foreach ($this->data as $rec) {
            $hours = $rec->total_hours ?? 0;
            
            // यहाँ कर्मचारीको व्यक्तिगत ot_rate नभई सधैं Position (Level) मा भएको ot_rate मात्र लिने
            $rate  = $rec->employee->position->ot_rate ?? 0;

            $eventDateRange = $rec->event
                ? adToBs($rec->event->start_date) . ' - ' . adToBs($rec->event->end_date)
                : '-';

            $rows[] = [
                'sn'            => $sn++,
                'employee_code' => $rec->employee->employee_code ?? '-',
                'name'          => $rec->employee->name ?? 'N/A',
                'position'      => $rec->employee->position->name ?? 'N/A',
                'event'         => $rec->event->event_name ?? 'सामान्य (General)',
                'event_dates'   => $eventDateRange,
                'hours_hm'      => hoursToHm($hours),
                'hours_decimal' => $hours,
                'rate'          => $rate,
                'total_amount'  => round($hours * $rate, 2),
                'tiffin'        => $rec->total_lunch ?? 0,
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return ["सि.नं.", "कर्मचारी कोड", "कर्मचारी", "पद", "कार्यक्रम", "कार्यक्रम मिति", "घण्टा (HH:MM)", "घण्टा (Decimal)", "OT रेट", "जम्मा रकम", "खाजा"];
    }
}