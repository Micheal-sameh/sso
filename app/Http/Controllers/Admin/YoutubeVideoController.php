<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\YoutubeVideo;
use App\Services\YoutubeVideoService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class YoutubeVideoController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected YoutubeVideoService $youtubeVideoService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/Videos/Index', [
            'videos' => $this->youtubeVideoService->listAllPaginated(20)
                ->through(fn (YoutubeVideo $video) => $this->youtubeVideoService->toArray($video)),
        ]);
    }

    public function sync(): RedirectResponse
    {
        $synced = $this->youtubeVideoService->sync();

        return $this->redirectBack(__('messages.videos_synced', ['count' => $synced]), 'success');
    }

    public function togglePin(YoutubeVideo $youtubeVideo): RedirectResponse
    {
        $this->youtubeVideoService->togglePin($youtubeVideo);

        return $this->redirectBack();
    }
}
