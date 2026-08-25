<?php

namespace App\Repositories\Eloquent;

use App\DTOs\OAuthClientDTO;
use App\Enums\ClientAccessMode;
use App\Models\ClientMeta;
use App\Repositories\Contracts\ClientMetaRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ClientMetaRepository implements ClientMetaRepositoryContract
{
    /**
     * Find client metadata by the associated OAuth client id, eager loading
     * the relations needed to evaluate access control.
     */
    public function findByClientId(string $clientId): ?ClientMeta
    {
        return ClientMeta::with(['oauthClient', 'stages', 'allowedUsers', 'groups'])
            ->where('oauth_client_id', $clientId)
            ->first();
    }

    /**
     * Create client metadata for a newly registered OAuth client.
     */
    public function create(string $clientId, OAuthClientDTO $dto, ?string $logoutSecret): ClientMeta
    {
        return ClientMeta::create([
            'oauth_client_id' => $clientId,
            'app_name' => $dto->name,
            'homepage_url' => $dto->homepageUrl,
            'logout_uri' => $dto->logoutUri,
            'logout_secret' => $logoutSecret,
            'is_internal' => $dto->isInternal,
            'access_mode' => $dto->accessMode,
        ]);
    }

    /**
     * Update existing client metadata.
     */
    public function update(ClientMeta $meta, array $data): ClientMeta
    {
        $meta->update($data);

        return $meta->fresh(['oauthClient', 'stages', 'allowedUsers', 'groups']);
    }

    /**
     * Paginate all registered clients with their metadata.
     */
    public function listAll(int $perPage = 20): LengthAwarePaginator
    {
        return ClientMeta::with(['oauthClient', 'stages', 'allowedUsers', 'groups', 'media'])->latest()->paginate($perPage)->withQueryString();
    }

    /**
     * Replace the set of stages allowed to authenticate with this client.
     */
    public function syncStageAccess(string $oauthClientId, array $stageIds): void
    {
        ClientMeta::where('oauth_client_id', $oauthClientId)->firstOrFail()->stages()->sync($stageIds);
    }

    /**
     * Replace the set of individual users allowed to authenticate with this client.
     */
    public function syncUserAccess(string $oauthClientId, array $userIds): void
    {
        ClientMeta::where('oauth_client_id', $oauthClientId)->firstOrFail()->allowedUsers()->sync($userIds);
    }

    /**
     * Replace the set of groups allowed to authenticate with this client.
     */
    public function syncGroupAccess(string $oauthClientId, array $groupIds): void
    {
        ClientMeta::where('oauth_client_id', $oauthClientId)->firstOrFail()->groups()->sync($groupIds);
    }

    /**
     * All clients using the given access mode.
     *
     * @return Collection<int, ClientMeta>
     */
    public function listByAccessMode(ClientAccessMode $mode): Collection
    {
        return ClientMeta::with(['oauthClient', 'allowedUsers'])
            ->where('access_mode', $mode)
            ->orderBy('app_name')
            ->get();
    }

    /**
     * Grant a single user access to a "specific users" client without disturbing existing access.
     */
    public function attachUser(string $oauthClientId, int $userId): void
    {
        ClientMeta::where('oauth_client_id', $oauthClientId)->firstOrFail()->allowedUsers()->syncWithoutDetaching([$userId]);
    }

    /**
     * Revoke a single user's access to a "specific users" client.
     */
    public function detachUser(string $oauthClientId, int $userId): void
    {
        ClientMeta::where('oauth_client_id', $oauthClientId)->firstOrFail()->allowedUsers()->detach($userId);
    }
}
