<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class YoutubeLiveStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array{status: string, video_id: string, title: ?string, thumbnail_url: ?string, scheduled_start_time: ?string}|null  $status
     */
    public function __construct(
        public ?array $status,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('youtube-live');
    }

    public function broadcastAs(): string
    {
        return 'status.updated';
    }

    /**
     * @return array{status: array|null}
     */
    public function broadcastWith(): array
    {
        return ['status' => $this->status];
    }
}
