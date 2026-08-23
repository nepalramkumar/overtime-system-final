<?php

use Nilambar\NepaliDate\NepaliDate;

if (!function_exists('adToBs')) {
    /**
     * AD date (YYYY-MM-DD) लाई BS मा बदल्ने, "YYYY-MM-DD" फर्काउँछ (BS मा)
     */
    function adToBs($date) {
        if (empty($date)) {
            return '';
        }

        $dateParts = explode('-', $date);
        $y = (int) $dateParts[0];
        $m = (int) $dateParts[1];
        $d = (int) $dateParts[2];

        $converter = new NepaliDate();
        $bs = $converter->convertAdToBs($y, $m, $d);

        // Package ले array फर्काउँछ: ['year' => ..., 'month' => ..., 'day' => ...]
        return sprintf('%04d-%02d-%02d', $bs['year'], $bs['month'], $bs['day']);
    }
}

if (!function_exists('bsToAd')) {
    /**
     * BS date (YYYY-MM-DD) लाई AD मा बदल्ने, "YYYY-MM-DD" फर्काउँछ (AD मा)।
     * Form बाट BS date आउने ठाउँमा (जस्तै BS datepicker ले पठाएको hidden
     * field पहिल्यै AD मा convert भइसकेको हुन्छ), तर server-side double-check
     * वा अन्य ठाउँबाट BS string सिधै आइहाले भने यो helper प्रयोग गर्न मिल्छ।
     */
    function bsToAd($bsDate) {
        if (empty($bsDate)) {
            return '';
        }

        $dateParts = explode('-', $bsDate);
        $y = (int) $dateParts[0];
        $m = (int) $dateParts[1];
        $d = (int) $dateParts[2];

        $converter = new NepaliDate();
        $ad = $converter->convertBsToAd($y, $m, $d);

        if (empty($ad)) {
            return '';
        }

        return sprintf('%04d-%02d-%02d', $ad['year'], $ad['month'], $ad['day']);
    }
}

if (!function_exists('hoursToHm')) {
    function hoursToHm($totalHours)
    {
        $wholeHours = floor($totalHours);
        $minutes = round(($totalHours - $wholeHours) * 60);
        if ($minutes == 60) {
            $wholeHours++;
            $minutes = 0;
        }
        return $wholeHours . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('userCan')) {
    /**
     * Login भएको user लाई दिइएको permission छ कि छैन जाँच्ने (Admin सधैं bypass)।
     * Menu/UI मा link देखाउने/लुकाउने निर्णय गर्न प्रयोग हुन्छ।
     */
    function userCan($permission) {
        if (!auth()->check()) {
            return false;
        }
        if (auth()->user()->role === 'admin') {
            return true;
        }
        return \App\Models\RolePermission::where('role', auth()->user()->role)
                ->where('permission', $permission)
                ->exists();
    }
}