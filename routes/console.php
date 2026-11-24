<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Market snapshots are refreshed continuously. The settlement service also
// requests a fresh snapshot itself whenever an expired trade needs one.
Schedule::command('shell:markets:refresh')
    ->everyMinute()
    ->withoutOverlapping(2);

// Run settlement throughout the minute, not only once per minute. A short lock
// expiry prevents a killed scheduler process from blocking settlement all day.
Schedule::command('shell:paper:settle')
    ->everySecond()
    ->withoutOverlapping(1);
