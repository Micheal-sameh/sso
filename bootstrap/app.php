<?php

use App\Exceptions\Auth\AuthenticationException;
use App\Exceptions\Auth\InvalidMfaCodeException;
use App\Exceptions\Auth\MfaNotEnabledException;
use App\Exceptions\Auth\OAuthClientException;
use App\Exceptions\Auth\PasswordResetException;
use App\Exceptions\Auth\SelfModificationException;
use App\Exceptions\Auth\StageInUseException;
use App\Exceptions\Auth\UnauthorizedRoleChangeException;
use App\Exceptions\Auth\UserNotFoundException;
use App\Http\Middleware\EnsureMfaEnabled;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\HideServerFingerprint;
use App\Http\Middleware\MfaPending;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\SuperAdmin;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Passport\Exceptions\InvalidAuthTokenException;
use Laravel\Passport\Http\Middleware\CheckToken;
use Laravel\Passport\Http\Middleware\CheckTokenForAnyScope;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(HideServerFingerprint::class);

        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
        ]);

        $middleware->alias([
            'mfa.pending' => MfaPending::class,
            'mfa.required' => EnsureMfaEnabled::class,
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

        $exceptions->render(function (UnauthorizedRoleChangeException $e, Request $request) {
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

        // Database connectivity/query failures — deliberately vague to the
        // client (never leak the SQL, table names, or driver message), but
        // visually distinct from a generic 500 so it's obvious at a glance
        // when triaging that this was a DB-layer failure, not app code.
        $exceptions->render(function (QueryException $e, Request $request) {
            $message = "We're unable to complete your request right now. Please try again in a few minutes.";

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 500);
            }

            if (! config('app.debug')) {
                return Inertia::render('Errors/Error', ['status' => 500, 'variant' => 'database', 'message' => $message])
                    ->toResponse($request)
                    ->setStatusCode(500);
            }
        });

        // Branded error pages for the standard HTTP error statuses, rendered
        // through Inertia so they match the rest of the app. Left alone for
        // API/JSON requests (handled by shouldRenderJsonWhen above) and in
        // local debug mode (so Ignition's stack traces still show).
        $exceptions->respond(function (Response $response, \Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            if (
                ! $request->is('api/*')
                && ! $request->expectsJson()
                && ! config('app.debug')
                && ! $exception instanceof QueryException
                && in_array($status, [401, 403, 404, 419, 500, 502, 503], true)
            ) {
                return Inertia::render('Errors/Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
