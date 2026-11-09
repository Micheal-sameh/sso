<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Traits\HandlesInertiaResponses;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LogoutController extends BaseController
{
    use HandlesInertiaResponses;

    public function __construct(
        protected AuthService $authService,
    ) {}

    public function logout(Request $request): RedirectResponse
    {
        // "Use another account" on the OAuth consent screen: after signing out,
        // send the next login straight back to the pending authorize request.
        // Only a same-site /oauth/authorize path is honoured (no open redirect).
        $intended = (string) $request->input('intended', '');
        $keep = preg_match('#^/oauth/authorize(\?\S*)?$#', $intended) === 1 ? $intended : null;

        $this->authService->logout();

        if ($keep !== null) {
            session(['url.intended' => url($keep)]);
        }

        return $this->redirectRoute('login', __('messages.logged_out_flash'), 'info');
    }
}
