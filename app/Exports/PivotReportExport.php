<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class PivotReportExport implements FromArray
{
    protected $pivotColumns;
    protected $pivotHours;
    protected $pivotLunch;
    protected $employees; // [employee_id => Employee model] insertion order sorted

    public function __construct($pivotColumns, $pivotHours, $pivotLunch, $employees)
    {
        $this->pivotColumns = $pivotColumns;
        $this->pivotHours   = $pivotHours;
        $this->pivotLunch   = $pivotLunch;
        $this->employees    = $employees;
    }

    public function array(): array
    {
        $rows = [];

        // ===== Hours Table =====
        $rows[] = ['कार्यक्रम अनुसार घण्टा (Programme-wise Hours)'];
        $headerRow = ['सि.नं.', 'कर्मचारी कोड', 'कर्मचारी', 'पद'];
        foreach ($this->pivotColumns as $col) {
            $headerRow[] = $col;
        }
        $rows[] = $headerRow;

        $sn = 1;
        foreach ($this->employees as $empId => $empGroup) {
            $row = [
                $sn++,
                $empGroup['employee']->employee_code ?? '-',
                $empGroup['employee']->name ?? 'N/A',
                $empGroup['employee']->position->name ?? 'N/A',
            ];
            foreach ($this->pivotColumns as $col) {
                $row[] = isset($this->pivotHours[$empId][$col])
    ? round($this->pivotHours[$empId][$col], 2)
    : '';
            }
            $rows[] = $row;
        }

        // खाली दुई पंक्ति (Hours र Lunch table बीच छुट्याउन)
        $rows[] = [];
        $rows[] = [];

        // ===== Lunch Table =====
        $rows[] = ['कार्यक्रम अनुसार खाजा रकम (Programme-wise Lunch Amount)'];
        $headerRow2 = ['सि.नं.', 'कर्मचारी कोड', 'कर्मचारी', 'पद'];
        foreach ($this->pivotColumns as $col) {
            $headerRow2[] = $col;
        }
        $rows[] = $headerRow2;

        $sn = 1;
        foreach ($this->employees as $empId => $empGroup) {
            $row = [
                $sn++,
                $empGroup['employee']->employee_code ?? '-',
                $empGroup['employee']->name ?? 'N/A',
                $empGroup['employee']->position->name ?? 'N/A',
            ];
            foreach ($this->pivotColumns as $col) {
                $row[] = isset($this->pivotLunch[$empId][$col]) ? number_format($this->pivotLunch[$empId][$col], 2) : '';
            }
            $rows[] = $row;
        }

        return $rows;
    }
}