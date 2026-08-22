<?php

namespace App\Services;

use App\DTOs\OAuthClientDTO;
use App\Enums\ClientAccessMode;
use App\Enums\ClientGrantType;
use App\Jobs\NotifyClientLogoutJob;
use App\Models\ClientMeta;
use App\Models\Token;
use App\Models\User;
use App\Repositories\Contracts\ClientMetaRepositoryContract;
use App\Repositories\Contracts\TokenRepositoryContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;

class OAuthClientService
{
    public function __construct(
        protected ClientRepository $clientRepository,
        protected ClientMetaRepositoryContract $clientMetaRepository,
        protected TokenRepositoryContract $tokenRepository,
    ) {}

    /**
     * Register a new OAuth client and its associated metadata.
     */
    public function registerClient(OAuthClientDTO $dto): array
    {
        // Implicit-grant clients (e.g. Odoo's built-in OAuth login) are always
        // non-confidential per the OAuth2 spec: the token is returned directly
        // in the browser redirect fragment, so there's nowhere to keep a secret.
        $client = $dto->grantType === ClientGrantType::Implicit
            ? $this->clientRepository->createImplicitGrantClient($dto->name, [$dto->redirect])
            : $this->clientRepository->createAuthorizationCodeGrantClient(
                name: $dto->name,
                redirectUris: [$dto->redirect],
                confidential: ! $dto->isPublicClient,
            );

        $logoutSecret = $dto->logoutUri !== null ? Str::random(40) : null;

        $meta = $this->clientMetaRepository->create($client->id, $dto, $logoutSecret);

        if ($dto->appLogo !== null) {
            $meta->addMedia($dto->appLogo)->toMediaCollection('logo');
        }

        if ($dto->accessMode === ClientAccessMode::Stage) {
            $this->clientMetaRepository->syncStageAccess($client->id, $dto->stageIds);
        } elseif ($dto->accessMode === ClientAccessMode::Specific) {
            $this->clientMetaRepository->syncUserAccess($client->id, $dto->userIds);
        } elseif ($dto->accessMode === ClientAccessMode::Group) {
            $this->clientMetaRepository->syncGroupAccess($client->id, $dto->groupIds);
        }

        return [
            'client' => $client,
            'meta' => $meta,
            'secret' => $client->plainSecret,
            'logoutSecret' => $logoutSecret,
        ];
    }

    /**
     * Update an existing OAuth client's registration details and access rules.
     *
     * @return array{meta: ClientMeta, logoutSecret: ?string} logoutSecret is set only when a
     *                                                         new one was just generated (i.e. logout_uri
     *                                                         was newly configured), so it can be revealed once.
     */
    public function updateClient(string $clientId, OAuthClientDTO $dto): array
    {
        $client = $this->clientRepository->find($clientId);

        abort_if($client === null, 404);

        $this->clientRepository->update($client, $dto->name, [$dto->redirect]);

        $meta = $this->clientMetaRepository->findByClientId($clientId);

        abort_if($meta === null, 404);

        // Only mint a signing secret the first time a logout_uri is configured;
        // otherwise keep the existing one so previously-issued signatures stay valid.
        $newLogoutSecret = null;
        $logoutSecret = $meta->logout_secret;
        if ($dto->logoutUri === null) {
            $logoutSecret = null;
        } elseif ($logoutSecret === null) {
            $logoutSecret = $newLogoutSecret = Str::random(40);
        }

        $meta = $this->clientMetaRepository->update($meta, [
            'app_name' => $dto->name,
            'homepage_url' => $dto->homepageUrl,
            'logout_uri' => $dto->logoutUri,
            'logout_secret' => $logoutSecret,
            'is_internal' => $dto->isInternal,
            'access_mode' => $dto->accessMode,
        ]);

        if ($dto->appLogo !== null) {
            $meta->addMedia($dto->appLogo)->toMediaCollection('logo');
        }

        $this->clientMetaRepository->syncStageAccess(
            $clientId,
            $dto->accessMode === ClientAccessMode::Stage ? $dto->stageIds : [],
        );

        $this->clientMetaRepository->syncUserAccess(
            $clientId,
            $dto->accessMode === ClientAccessMode::Specific ? $dto->userIds : [],
        );

        $this->clientMetaRepository->syncGroupAccess(
            $clientId,
            $dto->accessMode === ClientAccessMode::Group ? $dto->groupIds : [],
        );

        return ['meta' => $meta, 'logoutSecret' => $newLogoutSecret];
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

        $this->tokenRepository->revokeAllForUserAndClient($userId, $clientId);

        if ($meta->logout_uri !== null && $meta->logout_secret !== null) {
            // Client apps key their local users by the OIDC "sub" claim
            // (this SSO's uuid, see UserDTO::toOidcArray()), not our
            // internal integer id — send that, not $userId.
            $sub = User::where('id', $userId)->value('uuid');

            if ($sub !== null) {
                NotifyClientLogoutJob::dispatch($clientId, $sub, $meta->logout_uri, $meta->logout_secret);
            }
        }
    }
}
