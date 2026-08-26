<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PetrolBillExport implements FromArray, WithHeadings, WithEvents, WithTitle
{
    protected $bills;

    // Ranges like "A2:A5" that need to be merged after the sheet is built.
    // Populated inside array() because we only know how many date-rows
    // each bill needs once we loop through them.
    protected $mergeRanges = [];

    public function __construct($bills)
    {
        $this->bills = $bills;
    }

    public function array(): array
    {
        $rows = [];
        $sn = 1;

        // Row 1 is the heading row (WithHeadings), so data starts at row 2.
        $currentRow = 2;

        foreach ($this->bills as $bill) {
            $employee = $bill->employee;
            $monthLabel = ($bill->month->month ?? '') . ' ' . ($bill->month->year ?? '');

            $dateCount = count($bill->date);
            $startRow  = $currentRow;
            $endRow    = $currentRow + max($dateCount, 1) - 1;

            foreach ($bill->date as $i => $d) {
                // FIX: bill date was being written as the raw AD date entered
                // in the form. Converted to BS (Nepali) here, same as the
                // OT / Repair reports do with adToBs().
                $bsDate = $d ? adToBs($d) : $d;

                if ($i === 0) {
                    // पहिलो row: सबै जानकारी + total
                    $rows[] = [
                        $sn++,
                        $employee->employee_code ?? '-',
                        $monthLabel,
                        $employee->vehicle_no ?: '-',
                        $employee->position->level ?? '-',
                        $employee->hierarchy ?: '-',
                        $employee->name ?? 'N/A',
                        $bsDate,
                        $bill->quantity[$i] ?? '',
                        $bill->rate[$i] ?? '',
                        $bill->amount[$i] ?? '',
                        $bill->total_quantity,
                        $bill->total_amount,
                    ];
                } else {
                    // बाँकी row: date/quantity/rate/amount मात्र
                    $rows[] = [
                        '', '', '', '', '', '', '',
                        $bsDate,
                        $bill->quantity[$i] ?? '',
                        $bill->rate[$i] ?? '',
                        $bill->amount[$i] ?? '',
                        '', '',
                    ];
                }
            }

            // FIX: "merge bhayena" — previously the employee-info / total
            // columns were just left blank on rows 2+. Now we actually
            // merge those cells vertically across the bill's date-rows,
            // matching the look of the old report.
            if ($dateCount > 1) {
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'L', 'M'] as $col) {
                    $this->mergeRanges[] = "{$col}{$startRow}:{$col}{$endRow}";
                }
            }

            $currentRow = $endRow + 1;
        }

        if (empty($rows)) {
            $rows[] = ['डेटा उपलब्ध छैन', '', '', '', '', '', '', '', '', '', '', '', ''];
        }

        return $rows;
    }

    public function headings(): array
    {
        return ["S.N", "Employee Code", "Month", "Vehicle No", "Level", "Hierarchy", "Name", "Bill Date", "Quantity", "Rate", "Amount", "Total Quantity", "Total Amount"];
    }

    public function title(): string
    {
        return 'Petrol Bill';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach ($this->mergeRanges as $range) {
                    $sheet->mergeCells($range);
                    $sheet->getStyle($range)->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            },
        ];
    }
}
