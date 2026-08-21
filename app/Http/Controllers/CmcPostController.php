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
                ->through(fn (CmcPost $post) => $this->toArray($post)),
        ]);
    }

    public function show(CmcPost $cmcPost): Response
    {
        $cmcPost->loadMissing('creator');

        return $this->inertia('CmcPost/Show', [
            'post' => $this->toArray($cmcPost),
        ]);
    }

    private function toArray(CmcPost $cmcPost): array
    {
        return [
            'id' => $cmcPost->id,
            'text' => $cmcPost->text,
            'image_url' => $cmcPost->image_url,
            'url' => $cmcPost->url,
            'url_title' => $cmcPost->url_title,
            'url_description' => $cmcPost->url_description,
            'url_image' => $cmcPost->url_image,
            'creator' => $cmcPost->creator ? ['id' => $cmcPost->creator->id, 'name' => $cmcPost->creator->name] : null,
            'created_at' => $cmcPost->created_at->format('d M Y, H:i'),
        ];
    }
}
