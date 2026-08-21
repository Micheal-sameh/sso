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

    public function delete(CmcPost $cmcPost): bool
    {
        return (bool) $cmcPost->delete();
    }
}
