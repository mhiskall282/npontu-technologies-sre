<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Automated SRE Shift Reports Scheduling ─────────────────────────────────
// Daily Shift Summary dispatched to all engineers at the end of each day (23:59 UTC)
Schedule::command('reports:send-automated --period=daily')
    ->dailyAt('23:59')
    ->name('sre-daily-eod-report')
    ->withoutOverlapping();

// Weekly SRE Performance & Compliance Digest dispatched on Sunday evenings (23:59 UTC)
Schedule::command('reports:send-automated --period=weekly')
    ->weeklyOn(0, '23:59')
    ->name('sre-weekly-digest')
    ->withoutOverlapping();

// Monthly Executive SRE Reliability & SLA Report dispatched on the 28th of each month (23:59 UTC)
Schedule::command('reports:send-automated --period=monthly')
    ->monthlyOn(28, '23:59')
    ->name('sre-monthly-executive-report')
    ->withoutOverlapping();
