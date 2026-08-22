<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\Token;
use App\Repositories\Contracts\TokenRepositoryContract;
use App\Services\OAuthClientService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class ConnectedAppsController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected TokenRepositoryContract $tokenRepository,
        protected OAuthClientService $oauthClientService,
    ) {}

    public function index(): Response
    {
        $tokens = $this->tokenRepository->listForUser(Auth::id())->map(fn ($token) => $token->toDisplayArray());

        return $this->inertia('Account/Apps', ['tokens' => $tokens]);
    }

    public function revoke(string $tokenId): RedirectResponse
    {
        $token = Token::find($tokenId);

        $this->tokenRepository->revoke($tokenId);

        if ($token !== null) {
            $this->oauthClientService->notifyClientLogout($token->client_id, $token->user_id);
        }

        return $this->redirectBack(__('messages.app_access_revoked'), 'success');
    }
}
