<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\Token;
use App\Repositories\Contracts\TokenRepositoryContract;
use App\Services\OAuthClientService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class TokenController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected TokenRepositoryContract $tokenRepository,
        protected OAuthClientService $oauthClientService,
    ) {}

    public function index(): Response
    {
        return $this->inertia('Admin/Tokens/Index', [
            'tokens' => $this->tokenRepository->listAllPaginated(20)->through(fn (Token $token) => $token->toDisplayArray()),
        ]);
    }

    public function revoke(string $token): RedirectResponse
    {
        $tokenModel = Token::find($token);

        $this->tokenRepository->revoke($token);

        if ($tokenModel !== null) {
            $this->oauthClientService->notifyClientLogout($tokenModel->client_id, $tokenModel->user_id);
        }

        return $this->redirectBack(__('messages.token_revoked'), 'success');
    }
}
