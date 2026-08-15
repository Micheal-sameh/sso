<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\OAuthClientDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\OAuthClientRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Repositories\Contracts\UserRepositoryContract;
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
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/Clients/Index', [
            'clients' => $this->oauthClientService->listAllClients(20),
            'stages' => $this->stageService->listAll(),
            'users' => $this->userRepository->allLite(),
        ]);
    }

    public function store(OAuthClientRequest $request): RedirectResponse
    {
        $this->oauthClientService->registerClient(OAuthClientDTO::fromRequest($request));

        return $this->redirectRoute('admin.clients.index', 'Client registered successfully.');
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
