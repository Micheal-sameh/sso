<?php

namespace App\Services;

use App\DTOs\OAuthClientDTO;
use App\Enums\ClientAccessMode;
use App\Models\ClientMeta;
use App\Repositories\Contracts\ClientMetaRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Token;

class OAuthClientService
{
    public function __construct(
        protected ClientRepository $clientRepository,
        protected ClientMetaRepositoryContract $clientMetaRepository,
    ) {}

    /**
     * Register a new OAuth client and its associated metadata.
     */
    public function registerClient(OAuthClientDTO $dto): array
    {
        $client = $this->clientRepository->createAuthorizationCodeGrantClient(
            name: $dto->name,
            redirectUris: [$dto->redirect],
            confidential: true,
        );

        $meta = $this->clientMetaRepository->create($client->id, $dto);

        if ($dto->accessMode === ClientAccessMode::Stage) {
            $this->clientMetaRepository->syncStageAccess($client->id, $dto->stageIds);
        } elseif ($dto->accessMode === ClientAccessMode::Specific) {
            $this->clientMetaRepository->syncUserAccess($client->id, $dto->userIds);
        }

        return [
            'client' => $client,
            'meta' => $meta,
            'secret' => $client->plainSecret,
        ];
    }

    /**
     * Update an existing OAuth client's registration details and access rules.
     */
    public function updateClient(string $clientId, OAuthClientDTO $dto): ClientMeta
    {
        $client = $this->clientRepository->find($clientId);

        abort_if($client === null, 404);

        $this->clientRepository->update($client, $dto->name, [$dto->redirect]);

        $meta = $this->clientMetaRepository->findByClientId($clientId);

        abort_if($meta === null, 404);

        $meta = $this->clientMetaRepository->update($meta, [
            'app_name' => $dto->name,
            'app_logo_url' => $dto->appLogoUrl,
            'homepage_url' => $dto->homepageUrl,
            'is_internal' => $dto->isInternal,
            'access_mode' => $dto->accessMode,
        ]);

        $this->clientMetaRepository->syncStageAccess(
            $clientId,
            $dto->accessMode === ClientAccessMode::Stage ? $dto->stageIds : [],
        );

        $this->clientMetaRepository->syncUserAccess(
            $clientId,
            $dto->accessMode === ClientAccessMode::Specific ? $dto->userIds : [],
        );

        return $meta;
    }

    /**
     * Revoke an OAuth client and all of its issued tokens.
     */
    public function revokeClient(string $clientId): void
    {
        $client = $this->clientRepository->find($clientId);

        if ($client !== null) {
            $client->forceFill(['revoked' => true])->save();
        }

        Token::where('client_id', $clientId)->update(['revoked' => true]);
    }

    /**
     * Paginate all registered clients with their metadata.
     */
    public function listAllClients(int $perPage = 20): LengthAwarePaginator
    {
        return $this->clientMetaRepository->listAll($perPage);
    }

    /**
     * All clients restricted to specific users, used to build the per-user access picker.
     *
     * @return Collection<int, ClientMeta>
     */
    public function listSpecificAccessClients(): Collection
    {
        return $this->clientMetaRepository->listByAccessMode(ClientAccessMode::Specific);
    }

    /**
     * Grant a single user access to a "specific users" client.
     */
    public function grantUserAccess(string $clientId, int $userId): void
    {
        $meta = $this->clientMetaRepository->findByClientId($clientId);

        abort_if($meta === null || $meta->access_mode !== ClientAccessMode::Specific, 404);

        $this->clientMetaRepository->attachUser($clientId, $userId);
    }

    /**
     * Revoke a single user's access to a "specific users" client.
     */
    public function revokeUserAccess(string $clientId, int $userId): void
    {
        $meta = $this->clientMetaRepository->findByClientId($clientId);

        abort_if($meta === null || $meta->access_mode !== ClientAccessMode::Specific, 404);

        $this->clientMetaRepository->detachUser($clientId, $userId);
    }
}
