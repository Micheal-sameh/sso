<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\InvalidMfaCodeException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\MfaVerifyRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Response;

class MfaController extends BaseController
{
    use HandlesInertiaResponses;

    private const LOGIN_WINDOW_SECONDS = 300;

    private const MAX_ATTEMPTS = 5;

    public function __construct(
        protected MfaService $mfaService,
    ) {}

    public function showMfa(): Response
    {
        return $this->inertia('Auth/Mfa');
    }

    public function verify(MfaVerifyRequest $request): RedirectResponse
    {
        $session = $request->session();

        // The half-finished login is only good for a few minutes and a handful
        // of guesses; after that the user must start over with their password.
        $expired = now()->timestamp - (int) $session->get('mfa_started_at', 0) > self::LOGIN_WINDOW_SECONDS;

        if ($expired || (int) $session->get('mfa_attempts', 0) >= self::MAX_ATTEMPTS) {
            $session->forget(['mfa_pending', 'mfa_user_id', 'mfa_remember', 'mfa_started_at', 'mfa_attempts']);

            return $this->redirectRoute('login', __('messages.invalid_or_expired_verification_session'), 'error');
        }

        $user = User::findOrFail($session->get('mfa_user_id'));

        if (! $this->mfaService->verify($user, $request->string('code')->toString())) {
            $session->increment('mfa_attempts');

            throw new InvalidMfaCodeException;
        }

        Auth::login($user, (bool) $session->get('mfa_remember', false));

        $session->forget(['mfa_pending', 'mfa_user_id', 'mfa_remember', 'mfa_started_at', 'mfa_attempts']);

        return $this->redirectIntended('account.show', __('messages.mfa_verified'));
    }
}
