<?php

namespace App\Console\Commands;

use App\Services\YoutubeVideoService;
use Illuminate\Console\Command;

class SyncYoutubeChannel extends Command
{
    protected $signature = 'youtube:sync-channel';

    protected $description = 'Sync the configured YouTube channel\'s full upload history into youtube_videos';

    public function handle(YoutubeVideoService $youtubeVideoService): int
    {
        $synced = $youtubeVideoService->sync();

        $this->info("Synced {$synced} video(s).");

        return self::SUCCESS;
    }
}
