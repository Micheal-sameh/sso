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
        $dto = LoginDTO::fromRequest($request);
        $user = $this->authService->attempt($dto);

        if ($this->authService->requiresMfa($user)) {
            session(['mfa_pending' => true, 'mfa_user_id' => $user->id, 'mfa_remember' => $dto->remember]);

            return $this->redirectRoute('mfa.show');
        }

        return $this->redirectIntended('cmc.index', "Welcome back, {$user->name}!");
    }
}
