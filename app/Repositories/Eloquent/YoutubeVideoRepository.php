<?php

namespace App\Repositories\Eloquent;

use App\Models\YoutubeVideo;
use App\Repositories\Contracts\YoutubeVideoRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class YoutubeVideoRepository implements YoutubeVideoRepositoryContract
{
    public function paginateAll(int $perPage = 20): LengthAwarePaginator
    {
        return YoutubeVideo::orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function pinned(): Collection
    {
        return YoutubeVideo::where('is_pinned', true)
            ->orderBy('pin_order')
            ->get();
    }

    public function togglePin(YoutubeVideo $youtubeVideo): YoutubeVideo
    {
        if ($youtubeVideo->is_pinned) {
            $youtubeVideo->update(['is_pinned' => false, 'pin_order' => null]);

            return $youtubeVideo;
        }

        $nextOrder = (int) (YoutubeVideo::where('is_pinned', true)->max('pin_order')) + 1;
        $youtubeVideo->update(['is_pinned' => true, 'pin_order' => $nextOrder]);

        return $youtubeVideo;
    }

    /**
     * Swap a pinned video's position with its immediate neighbor in pin
     * order. No-op if the video isn't pinned or is already at that end.
     */
    public function movePin(YoutubeVideo $youtubeVideo, string $direction): void
    {
        if (! $youtubeVideo->is_pinned) {
            return;
        }

        $neighbor = YoutubeVideo::where('is_pinned', true)
            ->where('pin_order', $direction === 'up' ? '<' : '>', $youtubeVideo->pin_order)
            ->orderBy('pin_order', $direction === 'up' ? 'desc' : 'asc')
            ->first();

        if (! $neighbor) {
            return;
        }

        $ownOrder = $youtubeVideo->pin_order;
        $youtubeVideo->update(['pin_order' => $neighbor->pin_order]);
        $neighbor->update(['pin_order' => $ownOrder]);
    }
}
