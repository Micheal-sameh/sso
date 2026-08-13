<?php

namespace App\Repositories\Eloquent;

use App\DTOs\OAuthClientDTO;
use App\Models\ClientMeta;
use App\Repositories\Contracts\ClientMetaRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClientMetaRepository implements ClientMetaRepositoryContract
{
    /**
     * Find client metadata by the associated OAuth client id.
     */
    public function findByClientId(string $clientId): ?ClientMeta
    {
        return ClientMeta::with(['oauthClient'])->where('oauth_client_id', $clientId)->first();
    }

    /**
     * Create client metadata for a newly registered OAuth client.
     */
    public function create(string $clientId, OAuthClientDTO $dto): ClientMeta
    {
        return ClientMeta::create([
            'oauth_client_id' => $clientId,
            'app_name' => $dto->name,
            'app_logo_url' => $dto->appLogoUrl,
            'homepage_url' => $dto->homepageUrl,
            'is_internal' => $dto->isInternal,
        ]);
    }

    /**
     * Update existing client metadata.
     */
    public function update(ClientMeta $meta, array $data): ClientMeta
    {
        $meta->update($data);

        return $meta->fresh(['oauthClient']);
    }

    /**
     * Paginate all registered clients with their metadata.
     */
    public function listAll(int $perPage = 20): LengthAwarePaginator
    {
        return ClientMeta::with(['oauthClient'])->latest()->paginate($perPage)->withQueryString();
    }
}
