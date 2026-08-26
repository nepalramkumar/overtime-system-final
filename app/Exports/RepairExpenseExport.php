<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RepairExpenseExport implements FromArray, WithHeadings, WithEvents, WithTitle
{
    protected $rows;
    protected $mergeRanges = [];

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        $out = [];
        $sn = 1;
        $currentRow = 2; // row 1 = heading

        // 'expense_id' अनुसार group गर्ने (एउटै entry का सबै filtered date-rows सँगै रहने)
        $grouped = collect($this->rows)->groupBy('expense_id');

        foreach ($grouped as $expenseRows) {
            $expenseRows = collect($expenseRows)->values();
            $first = $expenseRows->first();
            $employee = $first['employee'];
            $totalAmount = $expenseRows->sum('amount');

            // FIX: "ahileko report ma claim gareko month rakhne" — this column
            // was dropped from the array output. Confirmed from
            // RepairExpenseController::flattenedRows() — it already computes
            // 'bs_month' (Nepali month name) per row from the bill's own
            // bs_date, we just weren't using it here. Year comes from bs_date.
            $bsYear = $first['bs_date'] ? explode('-', $first['bs_date'])[0] : '';
            $claimedMonth = trim(($first['bs_month'] ?? '') . ' ' . $bsYear);

            $rowCount = $expenseRows->count();
            $startRow = $currentRow;
            $endRow   = $currentRow + max($rowCount, 1) - 1;

            foreach ($expenseRows as $i => $row) {
                if ($i === 0) {
                    $out[] = [
                        $sn++,
                        $employee->employee_code ?? '-',
                        $row['fy_year'],
                        $claimedMonth,
                        $employee->vehicle_no ?: '-',
                        $employee->position->level ?? '-',
                        $employee->hierarchy ?: '-',
                        $employee->name ?? 'N/A',
                        $row['bs_date'],
                        $row['description'],
                        $row['amount'],
                        $totalAmount,
                    ];
                } else {
                    $out[] = [
                        '', '', '', '', '', '', '', '',
                        $row['bs_date'],
                        $row['description'],
                        $row['amount'],
                        '',
                    ];
                }
            }

            // FIX: merge the employee-info / total columns vertically across
            // this expense's date-rows, same as the petrol bill report.
            if ($rowCount > 1) {
                foreach (['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'L'] as $col) {
                    $this->mergeRanges[] = "{$col}{$startRow}:{$col}{$endRow}";
                }
            }

            $currentRow = $endRow + 1;
        }

        if (empty($out)) {
            $out[] = ['डेटा उपलब्ध छैन', '', '', '', '', '', '', '', '', '', ''];
        }

        return $out;
    }

    public function headings(): array
    {
        return ["S.N", "Employee Code", "FY Year", "Claimed Month", "Vehicle No", "Level", "Hierarchy", "Name", "Bill Date", "Description", "Amount", "Total Amount"];
    }

    public function title(): string
    {
        return 'Repair Expense';
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
