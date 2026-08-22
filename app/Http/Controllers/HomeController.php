<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use App\Models\CmcPost;
use App\Models\YoutubeVideo;
use App\Services\CmcPostService;
use App\Services\CopticCalendarService;
use App\Services\YoutubeLiveStatusService;
use App\Services\YoutubeVideoService;
use Inertia\Response;

class HomeController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected CmcPostService $cmcPostService,
        protected CopticCalendarService $copticCalendarService,
        protected YoutubeLiveStatusService $youtubeLiveStatusService,
        protected YoutubeVideoService $youtubeVideoService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Home/Index', [
            'coptic' => $this->copticCalendarService->today(),
            'recentPosts' => $this->cmcPostService->listRecent(5)
                ->map(fn (CmcPost $post) => $this->cmcPostService->toArray($post))
                ->values(),
            'pinnedPosts' => $this->cmcPostService->listPinned()
                ->map(fn (CmcPost $post) => $this->cmcPostService->toArray($post))
                ->values(),
            'youtubeLive' => $this->youtubeLiveStatusService->current(),
            'pinnedVideos' => $this->youtubeVideoService->listPinned()
                ->map(fn (YoutubeVideo $video) => $this->youtubeVideoService->toArray($video))
                ->values(),
        ]);
    }
}
