<?php

namespace App\Http\Controllers\OAuth;

use App\Enums\OAuthScope;
use App\Repositories\Contracts\ClientMetaRepositoryContract;
use App\Services\ClientAccessService;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Passport\Bridge\User;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Contracts\AuthorizationViewResponse;
use Laravel\Passport\Exceptions\AuthenticationException;
use Laravel\Passport\Http\Controllers\AuthorizationController as PassportAuthorizationController;
use Laravel\Passport\Passport;
use Laravel\Passport\Scope;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\RequestTypes\AuthorizationRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthorizationController extends PassportAuthorizationController
{
    public function __construct(
        AuthorizationServer $server,
        StatefulGuard $guard,
        ClientRepository $clients,
        protected ClientMetaRepositoryContract $clientMetaRepository,
        protected ClientAccessService $clientAccessService,
    ) {
        parent::__construct($server, $guard, $clients);
    }

    /**
     * Authorize a client to access the user's account, auto-approving
     * internal clients and rendering a consent screen for external ones.
     */
    public function authorize(
        ServerRequestInterface $psrRequest,
        Request $request,
        ResponseInterface $psrResponse,
        AuthorizationViewResponse $viewResponse
    ): Response|AuthorizationViewResponse {
        $authRequest = $this->withErrorHandling(
            fn (): AuthorizationRequestInterface => $this->server->validateAuthorizationRequest($psrRequest),
        );

        if ($this->guard->guest()) {
            $request->session()->put('promptedForLogin', true);

            throw new AuthenticationException(guards: isset($this->guard->name) ? [$this->guard->name] : []);
        }

        $user = $this->guard->user();
        $authRequest->setUser(new User($user->getAuthIdentifier()));

        $scopes = $this->parseScopes($authRequest);
        $client = $this->clients->find($authRequest->getClient()->getIdentifier());
        $meta = $this->clientMetaRepository->findByClientId($client->id);

        if ($meta !== null && ! $this->clientAccessService->userCanAccess($user, $meta)) {
            return Inertia::render('OAuth/AccessDenied', [
                'client' => ['name' => $meta->app_name],
            ])->toResponse($request);
        }

        if ($meta?->is_internal) {
            return $this->approveRequest($authRequest, $psrResponse);
        }

        $request->session()->put('authToken', $authToken = Str::random());
        $request->session()->put('authRequest', serialize($authRequest));

        return Inertia::render('OAuth/Authorize', [
            'client' => [
                'name' => $meta?->app_name ?? $client->name,
                'logo' => $meta?->logo_url,
                'homepage_url' => $meta?->homepage_url,
            ],
            'scopes' => collect($scopes)->map(fn ($scope) => [
                'id' => $scope->id,
                'description' => OAuthScope::tryFrom($scope->id)?->description() ?? $scope->id,
            ])->values(),
            'authRequest' => $authToken,
            'state' => $request->string('state')->toString(),
        ])->toResponse($request);
    }

    /**
     * @return Scope[]
     */
    protected function parseScopes(AuthorizationRequestInterface $authRequest): array
    {
        return Passport::scopesFor(
            collect($authRequest->getScopes())->map(
                fn ($scope): string => $scope->getIdentifier()
            )->unique()->all()
        );
    }
}
