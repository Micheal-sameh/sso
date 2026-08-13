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

    protected function redirectRoute(string $route, ?string $message = null, string $type = 'success'): RedirectResponse
    {
        return redirect()->route($route)->with('flash', ['type' => $type, 'message' => $message]);
    }

    protected function redirectBack(?string $message = null, string $type = 'error'): RedirectResponse
    {
        return back()->with('flash', ['type' => $type, 'message' => $message]);
    }
}
