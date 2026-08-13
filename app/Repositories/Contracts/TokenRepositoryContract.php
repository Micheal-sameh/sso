<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TokenRepositoryContract
{
    public function listAllPaginated(int $perPage = 20): LengthAwarePaginator;

    public function listForUser(int $userId): Collection;

    public function revoke(string $tokenId): void;
}
