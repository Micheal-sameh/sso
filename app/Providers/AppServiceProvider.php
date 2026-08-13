<?php

namespace App\Providers;

use App\Enums\OAuthScope;
use App\Http\Controllers\OAuth\AuthorizationController;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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

        Route::post('/oauth/token', [AccessTokenController::class, 'issueToken'])
            ->middleware('throttle')
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
    }
}
