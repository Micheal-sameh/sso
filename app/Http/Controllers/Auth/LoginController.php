<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\LoginDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\AuthService;
use App\Services\TrustedDeviceService;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class LoginController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected AuthService $authService,
        protected TrustedDeviceService $trustedDeviceService,
    ) {}

    public function showLogin(): Response
    {
        return $this->inertia('Auth/Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $dto = LoginDTO::fromRequest($request);
        $user = $this->authService->attempt($dto, $request->cookie(TrustedDeviceService::COOKIE));

        if ($this->authService->requiresMfa($user)) {
            session(['mfa_pending' => true, 'mfa_user_id' => $user->id, 'mfa_remember' => $dto->remember, 'mfa_started_at' => now()->timestamp, 'mfa_attempts' => 0]);

            return $this->redirectRoute('mfa.show');
        }

        // Remember this device so wrong guesses from elsewhere can't lock it out.
        // Only once the login is complete (MFA-protected accounts get it after the
        // second factor), so knowing the password alone can't mint fresh counters.
        Cookie::queue($this->trustedDeviceService->cookie($user, $request));

        return $this->redirectIntended('account.show', __('messages.welcome_back', ['name' => $user->name]));
    }
}
