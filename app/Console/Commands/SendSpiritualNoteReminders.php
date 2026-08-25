<?php

namespace App\Console\Commands;

use App\Services\SpiritualNoteService;
use Illuminate\Console\Command;

class SendSpiritualNoteReminders extends Command
{
    protected $signature = 'spiritual-notes:notify-reminder';

    protected $description = "Notify every active user who hasn't checked a single Spiritual Note label yet today";

    public function handle(SpiritualNoteService $spiritualNoteService): int
    {
        $count = $spiritualNoteService->sendReminderToUsersMissingToday();

        $this->info("Sent spiritual note reminders to {$count} user(s).");

        return self::SUCCESS;
    }
}
