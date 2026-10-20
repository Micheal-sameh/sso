<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline browser hardening headers plus a Content-Security-Policy.
 *
 * CSP_MODE=report-only (default) lets you watch the browser console for
 * violations without breaking anything; switch to 'enforce' once clean, or
 * 'off' to disable. Skipped outside production so the Vite dev server works.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()', false);

        $this->applyContentSecurityPolicy($response);

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', false);
        }

        return $response;
    }

    private function applyContentSecurityPolicy(Response $response): void
    {
        $mode = config('app.csp_mode', 'report-only');

        if ($mode === 'off' || ! app()->isProduction()) {
            return;
        }

        $policy = implode('; ', [
            "default-src 'self'",
            "script-src 'self' https://challenges.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'",
            'frame-src https:',
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);

        $header = $mode === 'enforce' ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only';

        $response->headers->set($header, $policy, false);
    }
}
