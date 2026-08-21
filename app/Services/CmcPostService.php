<?php

namespace App\Services;

use App\DTOs\NotificationDTO;
use App\Enums\NotificationTargetType;
use App\Events\CmcPostCreated;
use App\Models\CmcPost;
use App\Models\User;
use App\Repositories\Contracts\CmcPostRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CmcPostService
{
    public function __construct(
        protected CmcPostRepositoryContract $cmcPostRepository,
        protected UrlPreviewService $urlPreviewService,
        protected NotificationService $notificationService,
    ) {}

    /**
     * Create a CMC post (fetching a link preview for its URL, when given
     * one) and broadcast a notification to every user that routes to it.
     */
    public function create(?string $text, ?string $url, UploadedFile|string|null $image, ?User $creator): CmcPost
    {
        $preview = $url ? $this->urlPreviewService->fetch($url) : ['title' => null, 'description' => null, 'image' => null];

        $post = $this->cmcPostRepository->create([
            'text' => $text,
            'url' => $url,
            'url_title' => $preview['title'],
            'url_description' => $preview['description'],
            'url_image' => $preview['image'],
            'created_by' => $creator?->id,
        ]);

        if ($image !== null) {
            $post->addMedia($image)->toMediaCollection('image');
        }

        $post->setRelation('creator', $creator);
        broadcast(new CmcPostCreated($this->toArray($post)));

        $this->notificationService->send(
            new NotificationDTO(
                message: $this->summarize($text, $url, $preview['title']),
                targetType: NotificationTargetType::All,
                stageIds: [],
                groupIds: [],
                userIds: [],
                image: null,
                cmcPostId: $post->id,
            ),
            $creator,
        );

        return $post;
    }

    /**
     * Update a CMC post's content. The URL preview is only re-fetched when
     * the URL actually changed, to avoid an unnecessary network call on
     * every text-only edit. Does not re-notify recipients.
     */
    public function update(CmcPost $cmcPost, ?string $text, ?string $url, UploadedFile|string|null $image): CmcPost
    {
        $preview = match (true) {
            $url === null => ['title' => null, 'description' => null, 'image' => null],
            $url === $cmcPost->url => [
                'title' => $cmcPost->url_title,
                'description' => $cmcPost->url_description,
                'image' => $cmcPost->url_image,
            ],
            default => $this->urlPreviewService->fetch($url),
        };

        $cmcPost = $this->cmcPostRepository->update($cmcPost, [
            'text' => $text,
            'url' => $url,
            'url_title' => $preview['title'],
            'url_description' => $preview['description'],
            'url_image' => $preview['image'],
        ]);

        if ($image !== null) {
            $cmcPost->addMedia($image)->toMediaCollection('image');
        }

        return $cmcPost;
    }

    public function listAllPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return $this->cmcPostRepository->paginateAll($perPage);
    }

    /**
     * Shape a post for the frontend, formatting its timestamp for display.
     *
     * @return array{id: int, text: ?string, image_url: ?string, url: ?string, url_title: ?string, url_description: ?string, url_image: ?string, creator: ?array, created_at: string}
     */
    public function toArray(CmcPost $cmcPost): array
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

    public function delete(CmcPost $cmcPost): void
    {
        $this->cmcPostRepository->delete($cmcPost);
    }

    private function summarize(?string $text, ?string $url, ?string $urlTitle): string
    {
        if ($text) {
            return Str::limit($text, 120);
        }

        if ($urlTitle) {
            return Str::limit($urlTitle, 120);
        }

        return $url ? "New post: {$url}" : 'New post';
    }
}
