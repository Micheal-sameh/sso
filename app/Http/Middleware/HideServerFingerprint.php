<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Strips headers that reveal the underlying stack (PHP version, framework)
 * from every response. `expose_php` should also be off in php.ini — this is
 * a defense-in-depth backstop for environments where it isn't.
 */
class HideServerFingerprint
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // PHP queues its own X-Powered-By header (via `expose_php`) outside
        // Symfony's response header bag, so removing it there alone doesn't
        // stop PHP from actually sending it — this does.
        if (function_exists('header_remove')) {
            header_remove('X-Powered-By');
        }

        return $response;
    }
}
