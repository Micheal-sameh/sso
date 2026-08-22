<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use App\Models\YoutubeVideo;
use App\Services\YoutubeVideoService;
use Inertia\Response;

class VideoController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected YoutubeVideoService $youtubeVideoService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Video/Index', [
            'videos' => $this->youtubeVideoService->listAllPaginated(20)
                ->through(fn (YoutubeVideo $video) => $this->youtubeVideoService->toArray($video)),
        ]);
    }
}
