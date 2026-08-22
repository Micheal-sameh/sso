<?php

namespace App\Console\Commands;

use App\Services\DonationService;
use Illuminate\Console\Command;

class SendMonthlyDonationReminders extends Command
{
    protected $signature = 'donations:notify-monthly';

    protected $description = "Notify every opted-in user whose donation reminder day is today, unless they've already donated this month";

    public function handle(DonationService $donationService): int
    {
        $count = $donationService->sendMonthlyDonationReminders();

        $this->info("Sent monthly donation reminders to {$count} user(s).");

        return self::SUCCESS;
    }
}
