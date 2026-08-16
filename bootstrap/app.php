<?php

use App\Exceptions\Auth\AuthenticationException;
use App\Exceptions\Auth\InvalidMfaCodeException;
use App\Exceptions\Auth\MfaNotEnabledException;
use App\Exceptions\Auth\OAuthClientException;
use App\Exceptions\Auth\PasswordResetException;
use App\Exceptions\Auth\SelfModificationException;
use App\Exceptions\Auth\StageInUseException;
use App\Exceptions\Auth\UserNotFoundException;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\MfaPending;
use App\Http\Middleware\SuperAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Passport\Exceptions\InvalidAuthTokenException;
use Laravel\Passport\Http\Middleware\CheckToken;
use Laravel\Passport\Http\Middleware\CheckTokenForAnyScope;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'mfa.pending' => MfaPending::class,
            'superadmin' => SuperAdmin::class,
            'scopes' => CheckToken::class,
            'scope' => CheckTokenForAnyScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            return back()->with('flash', ['type' => 'error', 'message' => $e->getMessage()]);
        });

        $exceptions->render(function (InvalidMfaCodeException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            return back()->with('flash', ['type' => 'error', 'message' => $e->getMessage()]);
        });

        $exceptions->render(function (MfaNotEnabledException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            return back()->with('flash', ['type' => 'error', 'message' => $e->getMessage()]);
        });

        $exceptions->render(function (OAuthClientException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            return back()->with('flash', ['type' => 'error', 'message' => $e->getMessage()]);
        });

        $exceptions->render(function (SelfModificationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            return back()->with('flash', ['type' => 'error', 'message' => $e->getMessage()]);
        });

        $exceptions->render(function (StageInUseException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            return back()->with('flash', ['type' => 'error', 'message' => $e->getMessage()]);
        });

        $exceptions->render(function (PasswordResetException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            return back()->with('flash', ['type' => 'error', 'message' => $e->getMessage()]);
        });

        $exceptions->render(function (UserNotFoundException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
            }

            return back()->with('flash', ['type' => 'error', 'message' => $e->getMessage()]);
        });

        $exceptions->render(function (InvalidAuthTokenException $e, Request $request) {
            $message = 'That authorization request has expired or was already used. Please try signing in again from the app.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 419);
            }

            return back()->with('flash', ['type' => 'error', 'message' => $message]);
        });
    })->create();
