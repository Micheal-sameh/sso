<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanAccessDashboard
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    /**
     * Plain "user" accounts exist only to authenticate into other apps via
     * OAuth (/oauth/authorize, which sits outside this middleware); they have
     * no admin/secretary/superadmin role and so have nothing to do in this
     * app's own dashboard (home, account settings, admin panel). Log them out
     * rather than merely blocking the request, since leaving the session
     * alive would just bounce them straight back here on every retry.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== UserRole::User) {
            return $next($request);
        }

        $this->authService->logout();

        return redirect()->route('login')
            ->with('flash', ['type' => 'error', 'message' => __('messages.dashboard_access_denied')]);
    }
}
