<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\CmcPostDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\CmcPostRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Jobs\CreateCmcPostJob;
use App\Jobs\UpdateCmcPostJob;
use App\Models\CmcPost;
use App\Services\CmcPostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class CmcPostController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected CmcPostService $cmcPostService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/CmcPosts/Index', [
            'posts' => $this->cmcPostService->listAllPaginated(20)
                ->through(fn (CmcPost $post) => $this->cmcPostService->toArray($post)),
            'pinnedPosts' => $this->cmcPostService->listPinned()
                ->map(fn (CmcPost $post) => $this->cmcPostService->toArray($post))
                ->values(),
        ]);
    }

    public function store(CmcPostRequest $request): RedirectResponse
    {
        $dto = CmcPostDTO::fromRequest($request);

        CreateCmcPostJob::dispatch(
            text: $dto->text,
            url: $dto->url,
            creatorId: Auth::id(),
            imagePath: $dto->image instanceof UploadedFile ? $dto->image->store('cmc-post-uploads') : null,
        );

        return $this->redirectRoute('admin.cmc-posts.index', __('messages.post_being_published'));
    }

    public function update(CmcPost $cmcPost, CmcPostRequest $request): RedirectResponse
    {
        $dto = CmcPostDTO::fromRequest($request);

        UpdateCmcPostJob::dispatch(
            cmcPost: $cmcPost,
            text: $dto->text,
            url: $dto->url,
            imagePath: $dto->image instanceof UploadedFile ? $dto->image->store('cmc-post-uploads') : null,
        );

        return $this->redirectRoute('admin.cmc-posts.index', __('messages.post_being_updated'));
    }

    public function destroy(CmcPost $cmcPost): RedirectResponse
    {
        $this->cmcPostService->delete($cmcPost);

        return $this->redirectBack(__('messages.post_deleted'), 'success');
    }

    public function togglePin(CmcPost $cmcPost): RedirectResponse
    {
        $this->cmcPostService->togglePin($cmcPost);

        return $this->redirectBack();
    }

    public function moveUp(CmcPost $cmcPost): RedirectResponse
    {
        $this->cmcPostService->movePinUp($cmcPost);

        return $this->redirectBack();
    }

    public function moveDown(CmcPost $cmcPost): RedirectResponse
    {
        $this->cmcPostService->movePinDown($cmcPost);

        return $this->redirectBack();
    }
}
