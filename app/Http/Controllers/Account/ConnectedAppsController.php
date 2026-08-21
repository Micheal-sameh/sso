<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Repositories\Contracts\TokenRepositoryContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class ConnectedAppsController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected TokenRepositoryContract $tokenRepository,
    ) {}

    public function index(): Response
    {
        $tokens = $this->tokenRepository->listForUser(Auth::id())->map(fn ($token) => $token->toDisplayArray());

        return $this->inertia('Account/Apps', ['tokens' => $tokens]);
    }

    public function revoke(string $tokenId): RedirectResponse
    {
        $this->tokenRepository->revoke($tokenId);

        return $this->redirectBack('App access revoked.', 'success');
    }
}
