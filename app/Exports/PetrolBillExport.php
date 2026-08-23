<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PetrolBillExport implements FromArray, WithHeadings
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

            foreach ($bill->date as $i => $d) {
                if ($i === 0) {
                    // पहिलो row: सबै जानकारी + total
                        $rows[] = [
                        $sn++,
                        $employee->employee_code ?? '-',
                        $monthLabel,
                        $employee->vehicle_no ?? '-',
                        $employee->position->level ?? '-',
                        $employee->hierarchy ?? '-',
                        $employee->name ?? 'N/A',
                        $d,
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
                        $d,
                        $bill->quantity[$i] ?? '',
                        $bill->rate[$i] ?? '',
                        $bill->amount[$i] ?? '',
                        '', '',
                    ];
                }
            }
        }

        if (empty($rows)) {
            $rows[] = ['डेटा उपलब्ध छैन', '', '', '', '', '', '', '', '', '', '', ''];
        }

        return $rows;
    }

    public function headings(): array
    {
                return ["S.N", "Employee Code", "Month", "Vehicle No", "Level", "Hierarchy", "Name", "Bill Date", "Quantity", "Rate", "Amount", "Total Quantity", "Total Amount"];
    }
}