<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\OvertimeRecord;
use App\Models\Event;
use App\Models\SnackAllowance;
use App\Models\OfficeShift;
use App\Models\Holiday;
use App\Models\OvertimeStatusLog;

class OvertimeCalculator
{
    public function calculateAndSave(array $data, $employee, $officeStart = '08:00:00', $officeEnd = '17:00:00')
    {
        $otDate = $data['ot_date'];
        $event = null;

        if (!empty($data['event_id'])) {
            $event = Event::find($data['event_id']);
        }

        $isEligible = $event ? (bool)$event->is_tiffin_eligible : true;
        //filter_var($data['is_tiffin_eligible'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $from = Carbon::parse($otDate . ' ' . $data['from_time']);
        $to = Carbon::parse($otDate . ' ' . $data['to_time']);

        if ($to->lt($from)) {
            $to->addDay();
        }

        $totalMinutes = ($to->timestamp - $from->timestamp) / 60;

        // Position नभएको employee को लागि पहिल्यै जाँच्ने (Holiday/Weekly-Off/Regular सबै case मा)
        if (!$employee->position) {
            throw new \Exception("यो कर्मचारीको लागि Position तोकिएको छैन। कृपया पहिले Position assign गर्नुहोस्।");
        }

        // Individual OT (event_id नभएको) मा creation नै submission मानिन्छ, त्यसैले सिधा Submitted बाट सुरु हुन्छ।
        // Event-based भए Event अझै Draft (Submit नभएको) हुँदासम्म Pending नै रहन्छ, Event Submit भएपछि cascade ले Submitted बनाउँछ।
        $isIndividual = empty($data['event_id']);

        // Holiday auto-detect: Holiday table मा यो मिति भेटियो भने बिदाको दिन मानिने (शनि/आइतबार त shift नभएकोले अगाडि नै Weekly Off मानिन्छ)
        // Admin ले चाहेमा is_holiday_override (true/false) पठाएर यो auto-detect लाई override गर्न सक्छ, नत्र auto value नै प्रयोग हुन्छ
        $autoHoliday = Holiday::whereDate('date', $otDate)->exists();
        if (array_key_exists('is_holiday_override', $data) && $data['is_holiday_override'] !== '' && $data['is_holiday_override'] !== null) {
            $isHolidayFinal = filter_var($data['is_holiday_override'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $isHolidayFinal = $autoHoliday;
        }

        $baseData = [
            'entry_group'          => (string) Str::uuid(),
            'employee_id'          => $employee->id,
            'event_id'             => $data['event_id'] ?? null,
            'ot_date'              => $otDate,
            'designation_snapshot' => $employee->position->name,
            'ot_rate_snapshot'     => $employee->position->ot_rate,
            'is_holiday'           => $isHolidayFinal,
            'remarks'              => $data['remarks'] ?? null,
            'purpose_id'           => $data['purpose_id'] ?? null,
            'status'               => $isIndividual ? 'Submitted' : 'Pending',
            'recommender_employee_id' => $isIndividual ? ($data['recommender_employee_id'] ?? null) : null,
            'approver_employee_id'    => $isIndividual ? ($data['approver_employee_id'] ?? null) : null,
        ];

        // ot_date कुन बार (Sunday, Monday, ...) हो पत्ता लगाउने, र त्यो बारको shift setting खोज्ने
        $dayName = Carbon::parse($otDate)->format('l');
        $shift = OfficeShift::where('day_name', $dayName)->first();

        // १. Manual Holiday को लागि (फारमबाट "यो बिदाको दिन हो?" कोरेको)
        if ($baseData['is_holiday']) {
            if ($totalMinutes < 60) {
                throw new \Exception("अतिरिक्त समय न्यूनतम १ घण्टा (६० मिनेट) हुनुपर्छ।");
            }
            $hours = $totalMinutes / 60;
            $tiffin = $this->calculateTiffin($hours, $isEligible);

            $record = OvertimeRecord::create(array_merge($baseData, [
                'from_time'     => $from->format('H:i:s'),
                'to_time'       => $to->format('H:i:s'),
                'total_hours'   => $hours,
                'tiffin_amount' => $tiffin,
                'type'          => 'Holiday'
            ]));
            $this->logCreation($record);
            return $record;
        }

        // २. त्यो बारको लागि shift setting नै छैन भने (जस्तै Saturday/Sunday) — साप्ताहिक Off मानेर गणना
        //    ध्यान दिनुहोस्: यहाँ is_holiday भने FALSE नै रहन्छ (manual बिदाबाट छुट्याउन), type मात्र 'Weekly Off' हुन्छ
        if (!$shift) {
            if ($totalMinutes < 60) {
                throw new \Exception("अतिरिक्त समय न्यूनतम १ घण्टा (६० मिनेट) हुनुपर्छ।");
            }
            $hours = $totalMinutes / 60;
            $tiffin = $this->calculateTiffin($hours, $isEligible);

            $record = OvertimeRecord::create(array_merge($baseData, [
                'from_time'     => $from->format('H:i:s'),
                'to_time'       => $to->format('H:i:s'),
                'total_hours'   => $hours,
                'tiffin_amount' => $tiffin,
                'type'          => 'Weekly Off'
            ]));
            $this->logCreation($record);
            return $record;
        }

        // ३. Regular कार्यदिनको लागि — त्यो बारको shift अनुसारको office start/end प्रयोग गर्ने
        $officeStartTime = Carbon::parse($otDate . ' ' . $shift->start_time);
        $officeEndTime = Carbon::parse($otDate . ' ' . $shift->end_time);

        $recordsToCreate = [];

        // Before Office
        if ($from->lt($officeStartTime)) {
            $beforeEnd = $to->lt($officeStartTime) ? $to : $officeStartTime;
            $minutesBefore = ($beforeEnd->timestamp - $from->timestamp) / 60;
            if ($minutesBefore > 0) {
                $recordsToCreate[] = [
                    'from_time' => $from->format('H:i:s'),
                    'to_time'   => $beforeEnd->format('H:i:s'),
                    'minutes'   => $minutesBefore,
                    'type'      => 'Before Office'
                ];
            }
        }

        // After Office
        if ($to->gt($officeEndTime)) {
            $afterStart = $from->gt($officeEndTime) ? $from : $officeEndTime;
            $minutesAfter = ($to->timestamp - $afterStart->timestamp) / 60;
            if ($minutesAfter > 0) {
                $recordsToCreate[] = [
                    'from_time' => $afterStart->format('H:i:s'),
                    'to_time'   => $to->format('H:i:s'),
                    'minutes'   => $minutesAfter,
                    'type'      => 'After Office'
                ];
            }
        }

        $validOtMinutes = array_sum(array_column($recordsToCreate, 'minutes'));

        if ($validOtMinutes < 60) {
            throw new \Exception("ओभरटाइम न्यूनतम ६० मिनेट पुगेन।");
        }

        $firstCreatedRecord = null;

        foreach ($recordsToCreate as $record) {
            $rowHours = $record['minutes'] / 60;
            $rowTiffinAmount = $this->calculateTiffin($rowHours, $isEligible);

            $created = OvertimeRecord::create(array_merge($baseData, [
                'from_time'     => $record['from_time'],
                'to_time'       => $record['to_time'],
                'total_hours'   => $rowHours,
                'tiffin_amount' => $rowTiffinAmount,
                'type'          => $record['type']
            ]));
            $this->logCreation($created);

            if (!$firstCreatedRecord) {
                $firstCreatedRecord = $created;
            }
        }

        return $firstCreatedRecord;
    }

    private function calculateTiffin($hours, $isEligible)
    {
        if (!$isEligible) {
            return 0;
        }

        $rule = SnackAllowance::where('min_hours', '<=', $hours)
                    ->where('max_hours', '>', $hours)
                    ->first();

        if ($rule) {
            return $rule->amount;
        }

        // कुनै range नमिलेमा (जस्तै hours सबैभन्दा ठूलो range भन्दा पनि बढी भए),
        // सबैभन्दा ठूलो max_hours भएको rule लाई नै लागू गर्ने (open-ended जस्तै)
        $highestRule = SnackAllowance::orderBy('max_hours', 'desc')->first();
        return $highestRule ? $highestRule->amount : 0;
    }
    // एउटा specific entry (एउटै entry_group भएका row हरू, चाहे १ वटा वा Before/After Office गरी २ वटा)
    // को मात्र tiffin पुनः गणना गर्ने — Individual र Event-based दुबैको लागि प्रयोग हुने। $isEligible
    // event को tiffin-eligibility (event-based भए) वा true (individual भए) क्लर बाट पठाइन्छ।
    // पहिले Event-based entry edit गर्दा recalculateTiffinForEvent() ले त्यो event का *सबै* record
    // (अरू employee/date समेत) recalculate गर्थ्यो — एउटा मात्र entry edit गर्दा पनि धेरै वटा
    // "recalculate भयो" भनेर देखिने confusing message आउँथ्यो। अब ठ्याक्कै त्यही entry मात्र target हुन्छ।
    public function recalculateTiffinForEntryGroup($entryGroup, $isEligible, $includeVerified = false)
    {
        $query = OvertimeRecord::where('entry_group', $entryGroup);

        if (!$includeVerified) {
            $query->where('status', '!=', 'Verified');
        }

        $records = $query->get();
        $updatedCount = 0;

        foreach ($records as $record) {
            $record->tiffin_amount = $this->calculateTiffin($record->total_hours, $isEligible);
            $record->save();
            $updatedCount++;
        }

        return $updatedCount;
    }

    private function logCreation(OvertimeRecord $record): void
    {
        OvertimeStatusLog::record(
            $record->id,
            'Created',
            null,
            $record->status
        );
    }

public function recalculateTiffinForEvent($eventId, $includeVerified = false)
{
    // १. इभेन्टको ताजा tiffin eligibility अवस्था पत्ता लगाउने
    $event = \App\Models\Event::find($eventId);
    $isEventTiffinEligible = $event ? (bool)$event->is_tiffin_eligible : false;

    $query = \App\Models\OvertimeRecord::where('event_id', $eventId);
    
    if (!$includeVerified) {
        $query->where('status', '!=', 'Verified');
    }

    $records = $query->get();
    $updatedCount = 0;

    foreach ($records as $record) {
        // २. इभेन्टको eligibility अनुसार tiffin रकम recalculate गर्ने
        $record->tiffin_amount = $this->calculateTiffin($record->total_hours, $isEventTiffinEligible);
        
        $record->save();
        $updatedCount++;
    }

    return $updatedCount;
}
}