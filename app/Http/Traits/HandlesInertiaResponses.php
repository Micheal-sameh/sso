<?php

namespace App\Http\Traits;

use Illuminate\Http\RedirectResponse;
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
     * given route if no intended URL was stored.
     */
    protected function redirectIntended(string $fallbackRoute, ?string $message = null, string $type = 'success'): RedirectResponse
    {
        return redirect()->intended(route($fallbackRoute))->with('flash', ['type' => $type, 'message' => $message]);
    }

    protected function redirectBack(?string $message = null, string $type = 'error'): RedirectResponse
    {
        return back()->with('flash', ['type' => $type, 'message' => $message]);
    }
}
