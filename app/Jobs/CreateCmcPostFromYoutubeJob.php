<?php

namespace App\Jobs;

use App\Enums\NotificationType;
use App\Models\YoutubeVideo;
use App\Services\CmcPostService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateCmcPostFromYoutubeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $videoId,
        protected string $title,
        protected ?string $publishedAt,
    ) {}

    public function handle(CmcPostService $cmcPostService): void
    {
        // The feed can redeliver the same entry (WebSub doesn't guarantee
        // exactly-once), so skip if we've already stored this video.
        if (YoutubeVideo::where('video_id', $this->videoId)->exists()) {
            return;
        }

        $thumbnailUrl = "https://i.ytimg.com/vi/{$this->videoId}/hqdefault.jpg";

        $video = YoutubeVideo::create([
            'video_id' => $this->videoId,
            'title' => $this->title,
            'thumbnail_url' => $thumbnailUrl,
            'published_at' => $this->publishedAt,
        ]);

        $post = $cmcPostService->create(
            text: null,
            url: "https://www.youtube.com/watch?v={$this->videoId}",
            image: null,
            creator: null,
            preview: [
                'title' => $this->title,
                'description' => null,
                'image' => $thumbnailUrl,
            ],
            notificationType: NotificationType::Youtube,
        );

        $video->update(['cmc_post_id' => $post->id]);
    }
}
