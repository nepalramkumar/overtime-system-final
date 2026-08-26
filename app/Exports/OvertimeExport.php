<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OvertimeExport implements FromCollection, WithHeadings
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

        $flattened = [];
        $sn = 1;

        foreach ($this->data as $records) {
            if (!is_iterable($records)) continue;

            foreach ($records as $rec) {
                $eventDateRange = $rec->event
                    ? adToBs($rec->event->start_date) . ' - ' . adToBs($rec->event->end_date)
                    : '-';

                $flattened[] = [
                    'sn'            => $sn++,
                    // FIX: was raw $rec->ot_date (AD). "reports" excel export was
                    // showing the English date instead of Nepali date — converted below.
                    'date'          => $rec->ot_date ? adToBs($rec->ot_date) : 'N/A',
                    'employee_code' => $rec->employee->employee_code ?? '-',
                    'name'          => $rec->employee->name ?? 'N/A',
                    'position'      => $rec->employee->position->name ?? 'N/A',
                    'event'         => $rec->event->event_name ?? ($rec->remarks ?: 'सामान्य (General)'),
                    'event_dates'   => $eventDateRange,
                    'time'          => ($rec->from_time ?? '0') . ' - ' . ($rec->to_time ?? '0'),
                    'hours_hm'      => hoursToHm($rec->total_hours ?? 0),
                    'hours_decimal' => $rec->total_hours ?? 0,
                    'tiffin'        => $rec->tiffin_amount ?? 0,
                ];
            }
        }

        return collect($flattened);
    }

    public function headings(): array
    {
        return ["सि.नं.", "मिति", "कर्मचारी कोड", "कर्मचारी", "पद", "कार्यक्रम / कारण", "कार्यक्रम मिति", "समय", "घण्टा (HH:MM)", "घण्टा (Decimal)", "खाजा"];
    }
}
