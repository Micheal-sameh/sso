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
            ->orderByDesc('published_at')
            ->get();
    }

    public function togglePin(YoutubeVideo $youtubeVideo): YoutubeVideo
    {
        $youtubeVideo->update(['is_pinned' => ! $youtubeVideo->is_pinned]);

        return $youtubeVideo;
    }
}
