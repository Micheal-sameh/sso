<?php

namespace App\Providers;

use App\Enums\OAuthScope;
use App\Http\Controllers\OAuth\AuthorizationController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Responses\SimpleViewResponse;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Passport::ignoreRoutes();

        $this->app->when(AuthorizationController::class)
            ->needs(StatefulGuard::class)
            ->give(fn () => Auth::guard(config('passport.guard', null)));

        // Our AuthorizationController always resolves its own Inertia consent
        // screen or auto-approves; this satisfies the method-injected contract
        // without ever being invoked.
        $this->app->singleton(AuthorizationViewResponse::class, fn () => new SimpleViewResponse(
            fn () => response('', 204),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // nginx/Cloudflare terminate TLS and proxy plain HTTP to this app,
        // so without this, route()/url()/asset() generate http:// links
        // that the browser blocks as mixed content on the https:// page.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        Model::preventLazyLoading(! app()->isProduction());

        // 30 requests/second per authenticated user, falling back to per-IP
        // for unauthenticated requests.
        RateLimiter::for('per-second', function (Request $request) {
            return Limit::perSecond(30)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        // Brute-force protection: keyed on the targeted account as well as the
        // client IP, so neither rotating IPs nor a shared NAT defeats it.
        RateLimiter::for('login', function (Request $request) {
            $code = Str::lower((string) $request->input('membership_code'));

            return [
                Limit::perMinute(5)->by('login:'.$code.'|'.$request->ip()),
                Limit::perMinute(10)->by('login-account:'.$code),
                Limit::perMinute(30)->by('login-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('mfa', function (Request $request) {
            $subject = match (true) {
                $request->hasSession() && $request->session()->has('mfa_user_id') => 'u'.$request->session()->get('mfa_user_id'),
                $request->user() !== null => 'a'.$request->user()->getAuthIdentifier(),
                $request->filled('mfa_token') => 't'.sha1((string) $request->input('mfa_token')),
                default => 'ip'.$request->ip(),
            };

            return [
                Limit::perMinute(5)->by('mfa:'.$subject),
                Limit::perMinute(20)->by('mfa-ip:'.$request->ip()),
            ];
        });

        RateLimiter::for('sensitive', function (Request $request) {
            return Limit::perMinute(5)->by('sensitive:'.$request->ip());
        });

        Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
            ->middleware('throttle:per-second')
            ->name('passport.token');

        Passport::tokensExpireIn(now()->addMinutes(config('passport.access_token_ttl_minutes')));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        Passport::tokensCan([
            OAuthScope::OpenId->value => OAuthScope::OpenId->description(),
            OAuthScope::Profile->value => OAuthScope::Profile->description(),
            OAuthScope::Email->value => OAuthScope::Email->description(),
        ]);

        Passport::setDefaultScope([
            OAuthScope::OpenId->value,
            OAuthScope::Profile->value,
            OAuthScope::Email->value,
        ]);

        // Opt-in (OAUTH_IMPLICIT_GRANT=true); needed only for legacy OAuth
        // consumers (e.g. Odoo's built-in auth_oauth module).
        if (config('passport.implicit_grant')) {
            Passport::enableImplicitGrant();
        }

        // Passport's own Token::user() throws when eager-loaded (see
        // App\Models\Token for why); use our override everywhere.
        Passport::useTokenModel(\App\Models\Token::class);
    }
}
