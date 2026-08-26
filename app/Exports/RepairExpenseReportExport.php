<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Use this in the controller instead of RepairExpenseExport directly, e.g.:
 *   return Excel::download(new RepairExpenseReportExport($rows), 'repair_expense.xlsx');
 */
class RepairExpenseReportExport implements WithMultipleSheets
{
    protected $rows;

    public function __construct($rows)
    {
        $this->rows = $rows;
    }

    public function sheets(): array
    {
        return [
            new RepairExpenseExport($this->rows),
            new RepairExpenseSummaryExport($this->rows),
        ];
    }
}
