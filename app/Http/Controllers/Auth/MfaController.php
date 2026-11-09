<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Auth\InvalidMfaCodeException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\MfaVerifyRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Models\User;
use App\Services\AccountSwitcher;
use App\Services\AuthService;
use App\Services\MfaService;
use App\Services\TrustedDeviceService;
use Illuminate\Support\Facades\Cookie;
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
        protected AuthService $authService,
        protected TrustedDeviceService $trustedDeviceService,
        protected AccountSwitcher $accountSwitcher,
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
        $deviceToken = $request->cookie(TrustedDeviceService::COOKIE);

        // Same progressive lockout as the password step.
        $this->authService->ensureMfaAllowed($user, $deviceToken);

        if (! $this->mfaService->verify($user, $request->string('code')->toString())) {
            $session->increment('mfa_attempts');
            $this->authService->recordMfaFailure($user, $deviceToken);

            throw new InvalidMfaCodeException;
        }

        $this->authService->recordMfaSuccess($user, $deviceToken);
        Cookie::queue($this->trustedDeviceService->cookie($user, $request));

        Auth::login($user, (bool) $session->get('mfa_remember', false));
        $this->accountSwitcher->remember($user);

        $session->forget(['mfa_pending', 'mfa_user_id', 'mfa_remember', 'mfa_started_at', 'mfa_attempts']);

        return $this->redirectIntended('account.show', __('messages.mfa_verified'));
    }
}
