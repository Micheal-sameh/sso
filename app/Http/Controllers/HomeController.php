<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use App\Models\CmcPost;
use App\Models\YoutubeVideo;
use App\Services\CmcPostService;
use App\Services\CopticCalendarService;
use App\Services\SpiritualNoteService;
use App\Services\YoutubeLiveStatusService;
use App\Services\YoutubeVideoService;
use Illuminate\Http\Request;
use Inertia\Response;

class HomeController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected CmcPostService $cmcPostService,
        protected CopticCalendarService $copticCalendarService,
        protected SpiritualNoteService $spiritualNoteService,
        protected YoutubeLiveStatusService $youtubeLiveStatusService,
        protected YoutubeVideoService $youtubeVideoService,
    ) {}

    public function index(Request $request): Response
    {
        $spiritualNotesToday = $this->spiritualNoteService->today($request->user());
        $spiritualNotesChecked = count(array_filter($spiritualNotesToday, fn (array $entry) => $entry['is_checked']));

        return $this->inertia('Home/Index', [
            'coptic' => $this->copticCalendarService->today(),
            'spiritualNotesToday' => [
                'total' => count($spiritualNotesToday),
                'checked' => $spiritualNotesChecked,
                // Matches when the 6pm/10pm reminder notification fires, so the
                // widget stays visibly flagged until the user checks something.
                'reminderDue' => now()->hour >= 18 && $spiritualNotesChecked === 0,
            ],
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
