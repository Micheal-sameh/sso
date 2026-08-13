<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\OAuthClientDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\OAuthClientRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\OAuthClientService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ClientController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected OAuthClientService $oauthClientService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/Clients/Index', ['clients' => $this->oauthClientService->listAllClients(20)]);
    }

    public function store(OAuthClientRequest $request): RedirectResponse
    {
        $this->oauthClientService->registerClient(OAuthClientDTO::fromRequest($request));

        return $this->redirectRoute('admin.clients.index', 'Client registered successfully.');
    }

    public function destroy(string $client): RedirectResponse
    {
        $this->oauthClientService->revokeClient($client);

        return $this->redirectBack('Client revoked.', 'success');
    }
}
