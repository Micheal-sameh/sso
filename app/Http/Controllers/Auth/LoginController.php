<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\LoginDTO;
use App\Http\Controllers\BaseController;
use App\Http\Requests\LoginRequest;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class LoginController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected AuthService $authService,
    ) {}

    public function showLogin(): Response
    {
        return $this->inertia('Auth/Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = $this->authService->attempt(LoginDTO::fromRequest($request));

        if ($this->authService->requiresMfa($user)) {
            session(['mfa_pending' => true, 'mfa_user_id' => $user->id]);

            return $this->redirectRoute('mfa.show');
        }

        return $this->redirectRoute('account.show', "Welcome back, {$user->name}!");
    }
}
