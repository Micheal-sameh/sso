<?php

namespace App\Repositories\Contracts;

use App\DTOs\OAuthClientDTO;
use App\Enums\ClientAccessMode;
use App\Models\ClientMeta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ClientMetaRepositoryContract
{
    public function findByClientId(string $clientId): ?ClientMeta;

    public function create(string $clientId, OAuthClientDTO $dto, ?string $logoutSecret): ClientMeta;

    public function update(ClientMeta $meta, array $data): ClientMeta;

    public function listAll(int $perPage = 20): LengthAwarePaginator;

    public function syncStageAccess(string $oauthClientId, array $stageIds): void;

    public function syncUserAccess(string $oauthClientId, array $userIds): void;

    public function syncGroupAccess(string $oauthClientId, array $groupIds): void;

    /**
     * @return Collection<int, ClientMeta>
     */
    public function listByAccessMode(ClientAccessMode $mode): Collection;

    public function attachUser(string $oauthClientId, int $userId): void;

    public function detachUser(string $oauthClientId, int $userId): void;
}
