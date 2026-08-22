<?php

namespace App\Console\Commands;

use App\Services\DonationService;
use Illuminate\Console\Command;

class ProcessRecurringDonations extends Command
{
    protected $signature = 'donations:process-recurring';

    protected $description = 'Charge every recurring donation plan due today';

    public function handle(DonationService $donationService): int
    {
        $donationService->processRecurringPlans();

        $this->info('Recurring donation plans processed.');

        return self::SUCCESS;
    }
}
