<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PetrolMonth extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = "petrol_months";
    protected $fillable = ['month', 'year', 'status', 'start_date', 'end_date', 'extended_end_date'];

    protected $casts = [
        'start_date'        => 'date',
        'end_date'          => 'date',
        'extended_end_date' => 'date',
    ];

    public function bills()
    {
        return $this->hasMany(PetrolBill::class, 'petrol_month_id');
    }

    // वास्तविक बन्द हुने मिति — Admin ले थप गते दिएको भए त्यही, नत्र डिफल्ट (अर्को महिनाको ५ गते)
    public function getEffectiveEndDateAttribute()
    {
        return $this->extended_end_date ?? $this->end_date;
    }

    // Enabled (status=1) भएका र आजको मिति [start_date, effective end date] भित्र परेका Month मात्र
    // (नयाँ Bill entry गर्दा dropdown मा देखाउन) — पहिले status मात्र हेरिन्थ्यो, अब मिति पनि हेरिन्छ।
    public function scopeActive($query)
    {
        $today = Carbon::today()->format('Y-m-d');

        return $query->where('status', 1)
            ->whereNotNull('start_date')
            ->where('start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->where(function ($q2) use ($today) {
                    $q2->whereNotNull('extended_end_date')->where('extended_end_date', '>=', $today);
                })->orWhere(function ($q2) use ($today) {
                    $q2->whereNull('extended_end_date')->where('end_date', '>=', $today);
                });
            });
    }

    /**
     * दिइएको BS month/year को PetrolMonth record नभएको खण्डमा auto-create गर्ने
     * (start_date = सोही महिनाको १ गते, end_date = अर्को महिनाको ५ गते)।
     * पहिले नै भइसकेको भए त्यही existing record फर्काउँछ (idempotent — daily cron बाट बारम्बार
     * चले पनि duplicate बन्दैन)।
     */
    public static function ensureExists(string $bsMonthName, int $bsYear): self
    {
        $existing = self::where('month', $bsMonthName)->where('year', $bsYear)->first();
        if ($existing) {
            return $existing;
        }

        $monthIndex = array_search($bsMonthName, \App\Http\Controllers\PetrolMonthController::BS_MONTHS);
        if ($monthIndex === false) {
            throw new \InvalidArgumentException("Invalid BS month name: {$bsMonthName}");
        }
        $bsMonth = $monthIndex + 1;

        $nextBsMonth = $bsMonth + 1;
        $nextBsYear  = $bsYear;
        if ($nextBsMonth > 12) {
            $nextBsMonth = 1;
            $nextBsYear++;
        }

        $startBs = sprintf('%04d-%02d-01', $bsYear, $bsMonth);
        $endBs   = sprintf('%04d-%02d-05', $nextBsYear, $nextBsMonth);

        return self::create([
            'month'      => $bsMonthName,
            'year'       => $bsYear,
            'status'     => 1,
            'start_date' => bsToAd($startBs) ?: null,
            'end_date'   => bsToAd($endBs) ?: null,
        ]);
    }
}