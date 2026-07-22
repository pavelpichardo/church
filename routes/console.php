<?php

use App\Jobs\DetectMissedAttendanceJob;
use App\Jobs\DetectUpcomingBirthdaysJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Doors AI — daily detection jobs that fan out into the routing pipeline.
Schedule::job(new DetectMissedAttendanceJob, 'doors-ai')
    ->dailyAt('07:00')
    ->name('doors-ai.detect-missed-attendance')
    ->withoutOverlapping();

Schedule::job(new DetectUpcomingBirthdaysJob, 'doors-ai')
    ->dailyAt('06:00')
    ->name('doors-ai.detect-upcoming-birthdays')
    ->withoutOverlapping();
