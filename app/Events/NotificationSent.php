<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<int, int>  $recipientIds
     * @param  array{id: int, message: string, image_url: ?string, is_read: bool, created_at: string}  $notification
     */
    public function __construct(
        public array $recipientIds,
        public array $notification,
    ) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return array_map(
            fn (int $userId) => new PrivateChannel("App.Models.User.{$userId}"),
            $this->recipientIds,
        );
    }

    public function broadcastAs(): string
    {
        return 'notification.sent';
    }

    /**
     * @return array{notification: array}
     */
    public function broadcastWith(): array
    {
        return ['notification' => $this->notification];
    }
}
