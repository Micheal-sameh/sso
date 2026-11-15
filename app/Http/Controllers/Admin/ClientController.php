<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\OAuthClientDTO;
use App\Models\ExternalSyncClient;
use App\Http\Controllers\BaseController;
use App\Http\Requests\OAuthClientRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Repositories\Contracts\UserRepositoryContract;
use App\Services\GroupService;
use App\Services\OAuthClientService;
use App\Services\StageService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ClientController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected OAuthClientService $oauthClientService,
        protected StageService $stageService,
        protected UserRepositoryContract $userRepository,
        protected GroupService $groupService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/Clients/Index', [
            'clients' => $this->oauthClientService->listAllClients(20),
            'stages' => $this->stageService->listAll(),
            'users' => $this->userRepository->allLite(),
            'groups' => $this->groupService->listAll(),
            'syncClientIds' => ExternalSyncClient::query()->pluck('oauth_client_id'),
            'canManageUserSync' => $this->canManageUserSync(),
            'newClientSecret' => session()->pull('newClientSecret'),
            'newLogoutSecret' => session()->pull('newLogoutSecret'),
        ]);
    }

    public function store(OAuthClientRequest $request): RedirectResponse
    {
        $result = $this->oauthClientService->registerClient(OAuthClientDTO::fromRequest($request));
        $this->applyUserSync($request, $result['client']->id);

        $message = $result['secret'] !== null
            ? __('messages.client_registered_with_secret')
            : __('messages.client_registered');

        return $this->redirectRoute('admin.clients.index', $message)
            ->with('newClientSecret', $result['secret'])
            ->with('newLogoutSecret', $result['logoutSecret']);
    }

    public function update(string $client, OAuthClientRequest $request): RedirectResponse
    {
        $result = $this->oauthClientService->updateClient($client, OAuthClientDTO::fromRequest($request));
        $this->applyUserSync($request, $client);

        return $this->redirectRoute('admin.clients.index', __('messages.client_updated'))
            ->with('newLogoutSecret', $result['logoutSecret']);
    }

    /** Letting a client create/modify users is privileged: super admins only. */
    private function canManageUserSync(): bool
    {
        return (bool) auth()->user()?->role?->isSuperAdmin();
    }

    private function applyUserSync(OAuthClientRequest $request, string $clientId): void
    {
        if ($request->has('allow_user_sync') && $this->canManageUserSync()) {
            ExternalSyncClient::setAllowed($clientId, $request->boolean('allow_user_sync'));
        }
    }

    public function destroy(string $client): RedirectResponse
    {
        $this->oauthClientService->revokeClient($client);

        return $this->redirectBack(__('messages.client_revoked'), 'success');
    }
}
