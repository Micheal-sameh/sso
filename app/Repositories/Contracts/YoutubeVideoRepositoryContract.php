<?php

namespace App\Repositories\Contracts;

use App\Models\YoutubeVideo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface YoutubeVideoRepositoryContract
{
    public function paginateAll(int $perPage = 20): LengthAwarePaginator;

    public function pinned(): Collection;

    public function togglePin(YoutubeVideo $youtubeVideo): YoutubeVideo;

    public function movePin(YoutubeVideo $youtubeVideo, string $direction): void;
}
