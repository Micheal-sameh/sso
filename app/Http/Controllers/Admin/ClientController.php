<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\OAuthClientDTO;
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
            'newClientSecret' => session()->pull('newClientSecret'),
        ]);
    }

    public function store(OAuthClientRequest $request): RedirectResponse
    {
        $result = $this->oauthClientService->registerClient(OAuthClientDTO::fromRequest($request));

        $message = $result['secret'] !== null
            ? 'Client registered. Copy the secret now — it will not be shown again.'
            : 'Client registered successfully.';

        return $this->redirectRoute('admin.clients.index', $message)
            ->with('newClientSecret', $result['secret']);
    }

    public function update(string $client, OAuthClientRequest $request): RedirectResponse
    {
        $this->oauthClientService->updateClient($client, OAuthClientDTO::fromRequest($request));

        return $this->redirectRoute('admin.clients.index', 'Client updated successfully.');
    }

    public function destroy(string $client): RedirectResponse
    {
        $this->oauthClientService->revokeClient($client);

        return $this->redirectBack('Client revoked.', 'success');
    }
}
