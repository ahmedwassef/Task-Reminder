<?php

use App\Jobs\ProcessReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule reminder processing to run hourly
Schedule::job(new ProcessReminders)
    ->hourly()
    ->withoutOverlapping()
    ->name('process-reminders');

// For testing, you can run every 5 minutes:
// Schedule::job(new ProcessReminders)->everyFiveMinutes()->withoutOverlapping();
