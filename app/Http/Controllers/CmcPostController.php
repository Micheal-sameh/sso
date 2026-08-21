<?php

namespace App\Http\Controllers;

use App\Http\Traits\HandlesInertiaResponses;
use App\Models\CmcPost;
use App\Services\CmcPostService;
use Inertia\Response;

class CmcPostController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected CmcPostService $cmcPostService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('CmcPost/Index', [
            'posts' => $this->cmcPostService->listAllPaginated(20)
                ->through(fn (CmcPost $post) => $this->cmcPostService->toArray($post)),
        ]);
    }

    public function show(CmcPost $cmcPost): Response
    {
        $cmcPost->loadMissing('creator');

        return $this->inertia('CmcPost/Show', [
            'post' => $this->cmcPostService->toArray($cmcPost),
        ]);
    }
}
