<?php

use App\Console\Commands\ProcessRecurringDonations;
use App\Console\Commands\SendBirthdayNotifications;
use App\Console\Commands\SendFridayDonationReminders;
use App\Console\Commands\SendMonthlyDonationReminders;
use App\Console\Commands\SendSpiritualNoteReminders;
use App\Console\Commands\SubscribeYoutubeWebhook;
use App\Console\Commands\SyncYoutubeChannel;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SendBirthdayNotifications::class)->dailyAt('08:00')->withoutOverlapping();
Schedule::command(ProcessRecurringDonations::class)->dailyAt('06:00')->withoutOverlapping();
Schedule::command(SendMonthlyDonationReminders::class)->dailyAt('09:00')->withoutOverlapping();
// "Every Friday at 11am" is a local-time expectation for donors, not a UTC one
// (app timezone is UTC — see config/app.php) — pin explicitly to the church's timezone.
Schedule::command(SendFridayDonationReminders::class)->weeklyOn(5, '11:00')->timezone('Africa/Cairo')->withoutOverlapping();

// Nudge whoever hasn't checked a single Spiritual Note label yet — once at
// 6pm, and again at 10pm for anyone still at zero.
Schedule::command(SendSpiritualNoteReminders::class)->dailyAt('18:00')->timezone('Africa/Cairo')->withoutOverlapping();
Schedule::command(SendSpiritualNoteReminders::class)->dailyAt('22:00')->timezone('Africa/Cairo')->withoutOverlapping();

// YouTube WebSub subscriptions expire after ~5 days; renew before then.
Schedule::command(SubscribeYoutubeWebhook::class)->cron('0 0 */4 * *')->withoutOverlapping();

// Backfills/refreshes the full upload history; the webhook only covers new
// uploads going forward, so this is what keeps older videos in sync.
Schedule::command(SyncYoutubeChannel::class)->hourly()->withoutOverlapping();
