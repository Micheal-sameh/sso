<?php

namespace App\Repositories\Contracts;

use App\Models\CmcPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CmcPostRepositoryContract
{
    public function create(array $data): CmcPost;

    public function update(CmcPost $cmcPost, array $data): CmcPost;

    public function paginateAll(int $perPage = 20): LengthAwarePaginator;

    public function delete(CmcPost $cmcPost): bool;
}
