<?php

namespace App\Services;

use App\DTOs\OAuthClientDTO;
use App\Repositories\Contracts\ClientMetaRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

        return [
            'client' => $client,
            'meta' => $meta,
            'secret' => $client->plainSecret,
        ];
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
     * Determine whether the given client is marked internal (auto-approved).
     */
    public function isInternalClient(string $clientId): bool
    {
        return $this->clientMetaRepository->findByClientId($clientId)?->is_internal ?? false;
    }
}
