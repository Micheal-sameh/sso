<?php

namespace App\Repositories\Contracts;

use App\DTOs\OAuthClientDTO;
use App\Models\ClientMeta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClientMetaRepositoryContract
{
    public function findByClientId(string $clientId): ?ClientMeta;

    public function create(string $clientId, OAuthClientDTO $dto): ClientMeta;

    public function update(ClientMeta $meta, array $data): ClientMeta;

    public function listAll(int $perPage = 20): LengthAwarePaginator;
}
