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
        $userId = (int) Auth::id();

        // Scope to the signed-in user: a token id from another account must
        // look exactly like a missing one (404), never get revoked.
        $token = Token::where('id', $tokenId)->where('user_id', $userId)->first();

        if ($token === null || ! $this->tokenRepository->revokeForUser($tokenId, $userId)) {
            abort(404);
        }

        $this->oauthClientService->notifyClientLogout($token->client_id, $token->user_id);

        return $this->redirectBack(__('messages.app_access_revoked'), 'success');
    }
}
