<?php

namespace App\Console\Commands;

use App\Services\DonationService;
use Illuminate\Console\Command;

class SendFridayDonationReminders extends Command
{
    protected $signature = 'donations:notify-friday';

    protected $description = "Weekly follow-up for opted-in users who still haven't donated this month";

    public function handle(DonationService $donationService): int
    {
        $count = $donationService->sendFridayDonationReminders();

        $this->info("Sent Friday donation reminders to {$count} user(s).");

        return self::SUCCESS;
    }
}
