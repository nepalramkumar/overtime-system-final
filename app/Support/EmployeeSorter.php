<?php

namespace App\Support;

use Illuminate\Support\Collection;

class EmployeeSorter
{
    /**
     * Employee sorting:
     *
     * 1. Position level - DESC
     *    ठूलो level पहिले
     *
     * 2. Employee code - Natural ASC
     *    P-2 पहिले, P-10 पछि
     */
    public static function sort(Collection $records): Collection
    {
        return $records->sort(function ($a, $b) {

            $levelA = $a->employee->position->level ?? 0;
            $levelB = $b->employee->position->level ?? 0;

            // ठूलो level पहिले
            if ($levelA != $levelB) {
                return $levelB <=> $levelA;
            }

            // समान level भए employee code natural order
            return strnatcmp(
                $a->employee->employee_code ?? '',
                $b->employee->employee_code ?? ''
            );

        })->values();
    }
}