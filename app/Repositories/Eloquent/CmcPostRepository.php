<?php

namespace App\Repositories\Eloquent;

use App\Models\CmcPost;
use App\Repositories\Contracts\CmcPostRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CmcPostRepository implements CmcPostRepositoryContract
{
    public function create(array $data): CmcPost
    {
        return CmcPost::create($data);
    }

    public function update(CmcPost $cmcPost, array $data): CmcPost
    {
        $cmcPost->update($data);

        return $cmcPost->fresh('creator');
    }

    public function paginateAll(int $perPage = 20): LengthAwarePaginator
    {
        return CmcPost::with('creator')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function recent(int $limit = 5): Collection
    {
        return CmcPost::with('creator')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function pinned(): Collection
    {
        return CmcPost::with('creator')
            ->where('is_pinned', true)
            ->orderBy('pin_order')
            ->get();
    }

    public function togglePin(CmcPost $cmcPost): CmcPost
    {
        if ($cmcPost->is_pinned) {
            $cmcPost->update(['is_pinned' => false, 'pin_order' => null]);

            return $cmcPost;
        }

        $nextOrder = (int) (CmcPost::where('is_pinned', true)->max('pin_order')) + 1;
        $cmcPost->update(['is_pinned' => true, 'pin_order' => $nextOrder]);

        return $cmcPost;
    }

    /**
     * Swap a pinned post's position with its immediate neighbor in pin
     * order. No-op if the post isn't pinned or is already at that end.
     */
    public function movePin(CmcPost $cmcPost, string $direction): void
    {
        if (! $cmcPost->is_pinned) {
            return;
        }

        $neighbor = CmcPost::where('is_pinned', true)
            ->where('pin_order', $direction === 'up' ? '<' : '>', $cmcPost->pin_order)
            ->orderBy('pin_order', $direction === 'up' ? 'desc' : 'asc')
            ->first();

        if (! $neighbor) {
            return;
        }

        $ownOrder = $cmcPost->pin_order;
        $cmcPost->update(['pin_order' => $neighbor->pin_order]);
        $neighbor->update(['pin_order' => $ownOrder]);
    }

    public function delete(CmcPost $cmcPost): bool
    {
        return (bool) $cmcPost->delete();
    }
}
