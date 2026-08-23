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
        Model::preventLazyLoading(! app()->isProduction());

        // 30 requests/second per authenticated user, falling back to per-IP
        // for unauthenticated requests.
        RateLimiter::for('per-second', function (Request $request) {
            return Limit::perSecond(30)->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });

        Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
            ->middleware('throttle:per-second')
            ->name('passport.token');

        Passport::tokensExpireIn(now()->addDay());
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

        // Disabled by default; needed for legacy OAuth consumers (e.g. Odoo's
        // built-in auth_oauth module) that only support the implicit grant.
        Passport::enableImplicitGrant();

        // Passport's own Token::user() throws when eager-loaded (see
        // App\Models\Token for why); use our override everywhere.
        Passport::useTokenModel(\App\Models\Token::class);
    }
}
