<?php

use App\Console\Commands\ProcessRecurringDonations;
use App\Console\Commands\SendBirthdayNotifications;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SendBirthdayNotifications::class)->dailyAt('08:00')->withoutOverlapping();
Schedule::command(ProcessRecurringDonations::class)->dailyAt('06:00')->withoutOverlapping();
