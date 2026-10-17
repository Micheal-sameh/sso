<?php

namespace App\Http\Traits;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

trait HandlesInertiaResponses
{
    protected function inertia(string $component, array $props = []): Response
    {
        return Inertia::render($component, $props);
    }

    protected function redirectRoute(string $route, ?string $message = null, string $type = 'success', array $params = []): RedirectResponse
    {
        return redirect()->route($route, $params)->with('flash', ['type' => $type, 'message' => $message]);
    }

    /**
     * Redirect to the URL the user was originally trying to reach before
     * being bounced to login (e.g. /oauth/authorize), falling back to the
     * given route if no intended URL was stored. Plain "user" accounts have
     * no dashboard access, so for them the account fallback becomes the
     * welcome page rather than a redirect that would deny and log them out.
     */
    protected function redirectIntended(string $fallbackRoute, ?string $message = null, string $type = 'success'): RedirectResponse
    {
        if ($fallbackRoute === 'account.show' && Auth::user()?->role === UserRole::User) {
            $fallbackRoute = 'welcome';
        }

        return redirect()->intended(route($fallbackRoute))->with('flash', ['type' => $type, 'message' => $message]);
    }

    protected function redirectBack(?string $message = null, string $type = 'error'): RedirectResponse
    {
        return back()->with('flash', ['type' => $type, 'message' => $message]);
    }
}
