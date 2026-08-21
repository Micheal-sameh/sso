<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CmcPostCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * @param  array{id: int, text: ?string, image_url: ?string, url: ?string, url_title: ?string, url_description: ?string, url_image: ?string, creator: ?array, created_at: string}  $post
     */
    public function __construct(
        public array $post,
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('cmc-posts.admin');
    }

    public function broadcastAs(): string
    {
        return 'cmc-post.created';
    }

    /**
     * @return array{post: array}
     */
    public function broadcastWith(): array
    {
        return ['post' => $this->post];
    }
}
