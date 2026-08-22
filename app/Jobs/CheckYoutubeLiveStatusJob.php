<?php

namespace App\Jobs;

use App\Events\YoutubeLiveStatusUpdated;
use App\Services\YoutubeLiveStatusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Triggered by the YouTube WebSub webhook whenever a video is created or
 * updated on the channel. Cheaper and far more immediate than polling: we
 * only hit the Data API when YouTube tells us something changed, and we
 * push the result straight to connected clients over Reverb.
 */
class CheckYoutubeLiveStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $videoId,
    ) {}

    public function handle(YoutubeLiveStatusService $service): void
    {
        $status = $service->evaluateVideo($this->videoId);

        if ($status === null) {
            // Not a broadcast, or lookup failed — nothing to update unless
            // this was the video currently cached as live/upcoming, which
            // would mean it just ended or was deleted.
            return;
        }

        if ($status['status'] === 'ended') {
            $service->remember(null);
            YoutubeLiveStatusUpdated::dispatch(null);

            return;
        }

        $service->remember($status);
        YoutubeLiveStatusUpdated::dispatch($status);
    }
}
