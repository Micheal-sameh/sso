<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMfaEnabled
{
    /**
     * Block access to protected routes for authenticated users who haven't
     * enabled MFA yet, when MFA is required app-wide (config('mfa.required')).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! config('mfa.required') || ! $user || ($user->mfa?->is_enabled ?? false)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Two-factor authentication is required. Please enable it to continue.',
                'mfa_setup_required' => true,
            ], 403);
        }

        return redirect()->route('account.mfa')
            ->with('flash', ['type' => 'warning', 'message' => 'You must enable two-factor authentication to continue.']);
    }
}
