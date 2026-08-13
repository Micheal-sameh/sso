<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;

class LogoutController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected AuthService $authService,
    ) {}

    public function logout(): RedirectResponse
    {
        $this->authService->logout();

        return $this->redirectRoute('login', 'You have been logged out.', 'info');
    }
}
