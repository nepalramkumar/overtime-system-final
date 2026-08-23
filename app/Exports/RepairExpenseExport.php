<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RepairExpenseExport implements FromArray, WithHeadings
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

        // 'expense_id' अनुसार group गर्ने (एउटै entry का सबै filtered date-rows सँगै रहने)
        $grouped = collect($this->rows)->groupBy('expense_id');

        foreach ($grouped as $expenseRows) {
            $expenseRows = collect($expenseRows)->values();
            $first = $expenseRows->first();
            $employee = $first['employee'];
            $totalAmount = $expenseRows->sum('amount');

            foreach ($expenseRows as $i => $row) {
                if ($i === 0) {
                                        $out[] = [
                        $sn++,
                        $employee->employee_code ?? '-',
                        $row['fy_year'],
                        $employee->vehicle_no ?? '-',
                        $employee->position->level ?? '-',
                        $employee->hierarchy ?? '-',
                        $employee->name ?? 'N/A',
                        $row['bs_date'],
                        $row['description'],
                        $row['amount'],
                        $totalAmount,
                    ];
                } else {
                                       $out[] = [
                        '', '', '', '', '', '', '',
                        $row['bs_date'],
                        $row['description'],
                        $row['amount'],
                        '',
                    ];
                }
            }
        }

        if (empty($out)) {
            $out[] = ['डेटा उपलब्ध छैन', '', '', '', '', '', '', '', '', ''];
        }

        return $out;
    }

    public function headings(): array
    {
                return ["S.N", "Employee Code", "FY Year", "Vehicle No", "Level", "Hierarchy", "Name", "Bill Date", "Description", "Amount", "Total Amount"];
    }
}