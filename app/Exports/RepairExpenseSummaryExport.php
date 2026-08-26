<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Sheet 2 of the Repair Expense report — one row per claim, totals only.
 * ("sheet 2 ma new report thapne")
 */
class RepairExpenseSummaryExport implements FromArray, WithHeadings, WithTitle
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        $out = [];
        $sn = 1;

        $grouped = collect($this->rows)->groupBy('expense_id');

        foreach ($grouped as $expenseRows) {
            $expenseRows = collect($expenseRows)->values();
            $first = $expenseRows->first();
            $employee = $first['employee'];
            $totalAmount = $expenseRows->sum('amount');
            $bsYear = $first['bs_date'] ? explode('-', $first['bs_date'])[0] : '';
            $claimedMonth = trim(($first['bs_month'] ?? '') . ' ' . $bsYear);

            $out[] = [
                $sn++,
                $employee->employee_code ?? '-',
                $first['fy_year'],
                $claimedMonth,
                $employee->vehicle_no ?: '-',
                $employee->position->level ?? '-',
                $employee->hierarchy ?: '-',
                $employee->name ?? 'N/A',
                $totalAmount,
            ];
        }

        if (empty($out)) {
            $out[] = ['डेटा उपलब्ध छैन', '', '', '', '', '', '', '', ''];
        }

        return $out;
    }

    public function headings(): array
    {
        return ["S.N", "Employee Code", "FY Year", "Claimed Month", "Vehicle No", "Level", "Hierarchy", "Name", "Total Amount"];
    }

    public function title(): string
    {
        return 'Summary';
    }
}
