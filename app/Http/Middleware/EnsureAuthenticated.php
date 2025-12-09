<?php

namespace App\Http\Middleware;

use App\Services\OAuth2Service;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EnsureAuthenticated
{
    protected OAuth2Service $oauth2Service;

    public function __construct(OAuth2Service $oauth2Service)
    {
        $this->oauth2Service = $oauth2Service;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $sessionKeys = config('sso.session_keys');
        $userId = Session::get($sessionKeys['user_id']);

        // Check if user is authenticated
        if (! $userId) {
            return redirect('/auth/login');
        }

        // Check if tokens need refresh
        try {
            $this->oauth2Service->refreshTokensIfNeeded();
        } catch (\Exception $e) {
            // If refresh fails, redirect to login
            $this->oauth2Service->clearSession();

            return redirect('/auth/login');
        }

        return $next($request);
    }
}
