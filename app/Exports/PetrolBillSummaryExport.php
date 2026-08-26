<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Sheet 2 of the Petrol Bill report — one row per bill (per employee/month),
 * showing only the totals. This is the "naya report" the user asked for
 * ("sheet 2 ma naya report thapne").
 */
class PetrolBillSummaryExport implements FromArray, WithHeadings, WithTitle
{
    protected $bills;

    public function __construct($bills)
    {
        $this->bills = $bills;
    }

    public function array(): array
    {
        $rows = [];
        $sn = 1;

        foreach ($this->bills as $bill) {
            $employee = $bill->employee;
            $monthLabel = ($bill->month->month ?? '') . ' ' . ($bill->month->year ?? '');

            $rows[] = [
                $sn++,
                $employee->employee_code ?? '-',
                $monthLabel,
                $employee->vehicle_no ?: '-',
                $employee->position->level ?? '-',
                $employee->hierarchy ?: '-',
                $employee->name ?? 'N/A',
                $bill->total_quantity,
                $bill->total_amount,
            ];
        }

        if (empty($rows)) {
            $rows[] = ['डेटा उपलब्ध छैन', '', '', '', '', '', '', '', ''];
        }

        return $rows;
    }

    public function headings(): array
    {
        return ["S.N", "Employee Code", "Month", "Vehicle No", "Level", "Hierarchy", "Name", "Total Quantity", "Total Amount"];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
