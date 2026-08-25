<?php

namespace App\Repositories\Contracts;

use App\Models\CmcPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CmcPostRepositoryContract
{
    public function create(array $data): CmcPost;

    public function update(CmcPost $cmcPost, array $data): CmcPost;

    /**
     * @param  array<int, int>  $excludeIds  post IDs to leave out (e.g. ones already shown as pinned)
     */
    public function paginateAll(int $perPage = 20, array $excludeIds = []): LengthAwarePaginator;

    /**
     * @param  array<int, int>  $excludeIds  post IDs to leave out (e.g. ones already shown as pinned)
     */
    public function recent(int $limit = 5, array $excludeIds = []): Collection;

    public function pinned(): Collection;

    public function togglePin(CmcPost $cmcPost): CmcPost;

    public function movePin(CmcPost $cmcPost, string $direction): void;

    public function delete(CmcPost $cmcPost): bool;
}
