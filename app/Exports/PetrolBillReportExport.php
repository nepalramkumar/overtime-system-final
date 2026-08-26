<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Use this in the controller instead of PetrolBillExport directly, e.g.:
 *   return Excel::download(new PetrolBillReportExport($bills), 'petrol_bill.xlsx');
 *
 * This produces a 2-sheet workbook:
 *   Sheet 1 "Petrol Bill" -> detailed report (PetrolBillExport)
 *   Sheet 2 "Summary"     -> new totals-only report (PetrolBillSummaryExport)
 */
class PetrolBillReportExport implements WithMultipleSheets
{
    protected $bills;

    public function __construct($bills)
    {
        $this->bills = $bills;
    }

    public function sheets(): array
    {
        return [
            new PetrolBillExport($this->bills),
            new PetrolBillSummaryExport($this->bills),
        ];
    }
}
