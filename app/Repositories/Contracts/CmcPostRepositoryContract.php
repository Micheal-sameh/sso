<?php

namespace App\Repositories\Contracts;

use App\Models\CmcPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CmcPostRepositoryContract
{
    public function create(array $data): CmcPost;

    public function update(CmcPost $cmcPost, array $data): CmcPost;

    public function paginateAll(int $perPage = 20): LengthAwarePaginator;

    public function recent(int $limit = 5): Collection;

    public function pinned(): Collection;

    public function togglePin(CmcPost $cmcPost): CmcPost;

    public function movePin(CmcPost $cmcPost, string $direction): void;

    public function delete(CmcPost $cmcPost): bool;
}
