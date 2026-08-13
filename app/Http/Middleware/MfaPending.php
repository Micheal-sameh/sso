<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MfaPending
{
    public function handle(Request $request, Closure $next): Response
    {
        $pending = $request->session()->get('mfa_pending', false);

        if ($pending && ! $request->routeIs('mfa.show', 'mfa.verify', 'logout')) {
            return redirect()->route('mfa.show');
        }

        if (! $pending && $request->routeIs('mfa.show', 'mfa.verify')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
