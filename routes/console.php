<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Petrol Month auto-open/close र सम्बन्धित Notification/Email हरू — रोजाना बिहान चल्ने
Schedule::command('petrol:sync-months')->dailyAt('00:05');
Schedule::command('petrol:remind-bill-entry')->dailyAt('07:00');
Schedule::command('repair:remind-claim')->dailyAt('07:05');
