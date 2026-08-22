<?php

namespace App\Services;

use App\Models\YoutubeVideo;
use App\Repositories\Contracts\YoutubeVideoRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class YoutubeVideoService
{
    public function __construct(
        protected YoutubeVideoRepositoryContract $youtubeVideoRepository,
        protected YoutubeChannelSyncService $youtubeChannelSyncService,
    ) {}

    public function listAllPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return $this->youtubeVideoRepository->paginateAll($perPage);
    }

    public function listPinned(): Collection
    {
        return $this->youtubeVideoRepository->pinned();
    }

    public function togglePin(YoutubeVideo $youtubeVideo): YoutubeVideo
    {
        return $this->youtubeVideoRepository->togglePin($youtubeVideo);
    }

    public function sync(): int
    {
        return $this->youtubeChannelSyncService->sync();
    }

    /**
     * @return array{id: int, video_id: string, title: string, thumbnail_url: ?string, published_at: ?string, is_pinned: bool, watch_url: string}
     */
    public function toArray(YoutubeVideo $youtubeVideo): array
    {
        return [
            'id' => $youtubeVideo->id,
            'video_id' => $youtubeVideo->video_id,
            'title' => $youtubeVideo->title,
            'thumbnail_url' => $youtubeVideo->thumbnail_url,
            'published_at' => $youtubeVideo->published_at?->format('d M Y'),
            'is_pinned' => $youtubeVideo->is_pinned,
            'watch_url' => "https://www.youtube.com/watch?v={$youtubeVideo->video_id}",
        ];
    }
}
