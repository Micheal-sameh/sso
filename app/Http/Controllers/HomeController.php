<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use App\Models\CmcPost;
use App\Services\CmcPostService;
use App\Services\CopticCalendarService;
use App\Services\YoutubeLiveStatusService;
use Inertia\Response;

class HomeController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected CmcPostService $cmcPostService,
        protected CopticCalendarService $copticCalendarService,
        protected YoutubeLiveStatusService $youtubeLiveStatusService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Home/Index', [
            'coptic' => $this->copticCalendarService->today(),
            'recentPosts' => $this->cmcPostService->listRecent(5)
                ->map(fn (CmcPost $post) => $this->cmcPostService->toArray($post))
                ->values(),
            'youtubeLive' => $this->youtubeLiveStatusService->current(),
        ]);
    }
}
