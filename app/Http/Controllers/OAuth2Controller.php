<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OAuth2Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Laravel\Passport\Client;

class OAuth2Controller extends Controller
{
    protected OAuth2Service $oauth2Service;

    public function __construct(OAuth2Service $oauth2Service)
    {
        $this->oauth2Service = $oauth2Service;
    }

    /**
     * Authorization endpoint
     */
    public function authorize(Request $request)
    {
        // Rate limiting
        if (RateLimiter::tooManyAttempts('oauth-authorize:'.$request->ip(), 60)) {
            Log::warning('Rate limit exceeded for authorization endpoint', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'error' => 'too_many_requests',
                'error_description' => 'Rate limit exceeded',
            ], 429);
        }

        RateLimiter::hit('oauth-authorize:'.$request->ip(), 60);

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|string',
            'redirect_uri' => 'required|url',
            'response_type' => 'required|in:code',
            'scope' => 'nullable|string',
            'state' => 'nullable|string',
            'nonce' => 'nullable|string',
            'code_challenge' => 'nullable|string',
            'code_challenge_method' => 'nullable|in:plain,S256',
        ]);

        if ($validator->fails()) {
            return $this->redirectWithError($request->input('redirect_uri'), 'invalid_request', 'Invalid parameters', $request->input('state'));
        }

        // Validate client
        $client = Client::where('id', $request->client_id)->first();
        if (! $client) {
            return $this->redirectWithError($request->input('redirect_uri'), 'invalid_client', 'Unknown client', $request->input('state'));
        }

        // Validate redirect URI
        if (! in_array($request->redirect_uri, explode(',', $client->redirect))) {
            return $this->redirectWithError($request->input('redirect_uri'), 'invalid_request', 'Invalid redirect URI', $request->input('state'));
        }

        // Redirect to login if not authenticated
        if (! auth()->check()) {
            return redirect()->route('login', $request->all());
        }

        // For now, directly generate auth code (consent handling would be in AuthController)
        $user = auth()->user();
        $code = $this->oauth2Service->createAuthCode([
            'user_id' => $user->id,
            'client_id' => $request->client_id,
            'scopes' => explode(' ', $request->scope ?? ''),
            'redirect_uri' => $request->redirect_uri,
            'code_challenge' => $request->code_challenge,
            'code_challenge_method' => $request->code_challenge_method ?? 'plain',
        ]);

        return redirect($request->redirect_uri.'?'.http_build_query([
            'code' => $code,
            'state' => $request->state,
        ]));
    }

    /**
     * Token endpoint
     */
    public function token(Request $request)
    {
        // Rate limiting
        if (RateLimiter::tooManyAttempts('oauth-token:'.$request->ip(), 120)) {
            return response()->json([
                'error' => 'too_many_requests',
                'error_description' => 'Rate limit exceeded',
            ], 429);
        }

        RateLimiter::hit('oauth-token:'.$request->ip(), 120);

        $grantType = $request->input('grant_type');

        switch ($grantType) {
            case 'authorization_code':
                return $this->handleAuthorizationCodeGrant($request);
            case 'refresh_token':
                return $this->handleRefreshTokenGrant($request);
            default:
                return response()->json([
                    'error' => 'unsupported_grant_type',
                    'error_description' => 'The grant type is not supported',
                ], 400);
        }
    }

    /**
     * Handle authorization code grant
     */
    private function handleAuthorizationCodeGrant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'redirect_uri' => 'required|url',
            'code_verifier' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'Invalid parameters',
            ], 400);
        }

        // Validate client credentials
        $client = Client::where('id', $request->client_id)
            ->where('secret', $request->client_secret)
            ->first();

        if (! $client) {
            return response()->json([
                'error' => 'invalid_client',
                'error_description' => 'Invalid client credentials',
            ], 401);
        }

        // Validate authorization code
        $authCode = $this->oauth2Service->validateAuthCode(
            $request->code,
            $request->client_id,
            $request->redirect_uri,
            $request->code_verifier
        );

        if (! $authCode) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => 'Invalid authorization code',
            ], 400);
        }

        $user = User::find($authCode['user_id']);
        $scopes = json_decode($authCode['scopes'], true);

        $tokens = $this->oauth2Service->createTokens($user, $client, $scopes);

        // Add ID token if OIDC scopes are requested
        if (in_array('openid', $scopes)) {
            $tokens['id_token'] = $this->oauth2Service->createIdToken($user, $client, $request->input('nonce'));
        }

        return response()->json($tokens);
    }

    /**
     * Handle refresh token grant
     */
    private function handleRefreshTokenGrant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
            'scope' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'Invalid parameters',
            ], 400);
        }

        // Validate client credentials
        $client = Client::where('id', $request->client_id)
            ->where('secret', $request->client_secret)
            ->first();

        if (! $client) {
            return response()->json([
                'error' => 'invalid_client',
                'error_description' => 'Invalid client credentials',
            ], 401);
        }

        $tokens = $this->oauth2Service->refreshToken($request->refresh_token);

        if (! $tokens) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => 'Invalid refresh token',
            ], 400);
        }

        return response()->json($tokens);
    }

    /**
     * Revoke token endpoint
     */
    public function revoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'Invalid parameters',
            ], 400);
        }

        // Validate client credentials
        $client = Client::where('id', $request->client_id)
            ->where('secret', $request->client_secret)
            ->first();

        if (! $client) {
            return response()->json([
                'error' => 'invalid_client',
                'error_description' => 'Invalid client credentials',
            ], 401);
        }

        $revoked = $this->oauth2Service->revokeToken($request->token);

        return response()->json([
            'revoked' => $revoked,
        ]);
    }

    /**
     * Userinfo endpoint
     */
    public function userinfo(Request $request)
    {
        // Rate limiting
        if (RateLimiter::tooManyAttempts('oauth-userinfo:'.$request->ip(), 300)) {
            return response()->json([
                'error' => 'too_many_requests',
                'error_description' => 'Rate limit exceeded',
            ], 429);
        }

        RateLimiter::hit('oauth-userinfo:'.$request->ip(), 300);

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'error' => 'invalid_token',
                'error_description' => 'Invalid access token',
            ], 401);
        }

        return response()->json([
            'sub' => $user->sso_id ?? $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => ! is_null($user->email_verified_at),
        ]);
    }

    /**
     * Redirect with error
     */
    private function redirectWithError(string $redirectUri, string $error, string $description, ?string $state): \Illuminate\Http\RedirectResponse
    {
        $params = [
            'error' => $error,
            'error_description' => $description,
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return redirect($redirectUri.'?'.http_build_query($params));
    }
}
