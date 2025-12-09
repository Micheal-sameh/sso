<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\Client;
use Laravel\Passport\Token;

class OAuth2Service
{
    protected JWKService $jwkService;

    public function __construct(JWKService $jwkService)
    {
        $this->jwkService = $jwkService;
    }

    /**
     * Create an authorization code
     */
    public function createAuthCode(array $data): string
    {
        $code = Str::random(40);

        DB::table('oauth_auth_codes')->insert([
            'id' => $code,
            'user_id' => $data['user_id'],
            'client_id' => $data['client_id'],
            'scopes' => json_encode($data['scopes'] ?? []),
            'redirect_uri' => $data['redirect_uri'],
            'code_challenge' => $data['code_challenge'] ?? null,
            'code_challenge_method' => $data['code_challenge_method'] ?? 'plain',
            'expires_at' => Carbon::now()->addMinutes(10),
            'created_at' => Carbon::now(),
        ]);

        return $code;
    }

    /**
     * Validate authorization code
     */
    public function validateAuthCode(string $code, string $clientId, string $redirectUri, ?string $codeVerifier = null): ?array
    {
        $authCode = DB::table('oauth_auth_codes')
            ->where('id', $code)
            ->where('client_id', $clientId)
            ->where('redirect_uri', $redirectUri)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (! $authCode) {
            return null;
        }

        // Validate PKCE if code challenge exists
        if ($authCode->code_challenge && $codeVerifier) {
            $pkceHelper = app(PKCEHelper::class);
            if (! $pkceHelper->validateCodeChallenge($codeVerifier, $authCode->code_challenge)) {
                return null;
            }
        }

        // Delete the auth code (one-time use)
        DB::table('oauth_auth_codes')->where('id', $code)->delete();

        return (array) $authCode;
    }

    /**
     * Create access and refresh tokens
     */
    public function createTokens(User $user, Client $client, array $scopes = []): array
    {
        $accessToken = $this->createAccessToken($user, $client, $scopes);
        $refreshToken = $this->createRefreshToken($user, $client, $scopes);

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => config('passport.tokens.expire_in'),
            'refresh_token' => $refreshToken,
            'scope' => implode(' ', $scopes),
        ];
    }

    /**
     * Create access token
     */
    private function createAccessToken(User $user, Client $client, array $scopes): string
    {
        $token = $user->createToken($client->name, $scopes)->accessToken;

        return $token;
    }

    /**
     * Create refresh token
     */
    private function createRefreshToken(User $user, Client $client, array $scopes): string
    {
        $refreshToken = Str::random(40);

        DB::table('oauth_refresh_tokens')->insert([
            'id' => $refreshToken,
            'access_token_id' => DB::getPdo()->lastInsertId(), // This might need adjustment
            'expires_at' => Carbon::now()->addDays(30),
            'created_at' => Carbon::now(),
        ]);

        return $refreshToken;
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): ?array
    {
        $refreshTokenRecord = DB::table('oauth_refresh_tokens')
            ->where('id', $refreshToken)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (! $refreshTokenRecord) {
            return null;
        }

        $accessToken = DB::table('oauth_access_tokens')
            ->where('id', $refreshTokenRecord->access_token_id)
            ->first();

        if (! $accessToken) {
            return null;
        }

        $user = User::find($accessToken->user_id);
        $client = Client::find($accessToken->client_id);

        if (! $user || ! $client) {
            return null;
        }

        // Revoke old tokens
        DB::table('oauth_access_tokens')->where('id', $accessToken->id)->delete();
        DB::table('oauth_refresh_tokens')->where('id', $refreshToken)->delete();

        // Create new tokens
        return $this->createTokens($user, $client, json_decode($accessToken->scopes, true));
    }

    /**
     * Revoke token
     */
    public function revokeToken(string $token): bool
    {
        $accessToken = DB::table('oauth_access_tokens')
            ->where('id', $token)
            ->first();

        if ($accessToken) {
            DB::table('oauth_access_tokens')->where('id', $token)->delete();
            DB::table('oauth_refresh_tokens')->where('access_token_id', $accessToken->id)->delete();

            return true;
        }

        return false;
    }

    /**
     * Create ID Token for OIDC
     */
    public function createIdToken(User $user, Client $client, ?string $nonce = null): string
    {
        $jwks = $this->jwkService->getJwks();
        $key = $jwks['keys'][0]; // Use first key

        $payload = [
            'iss' => config('sso.issuer'),
            'sub' => $user->sso_id ?? $user->id,
            'aud' => $client->id,
            'exp' => Carbon::now()->addMinutes(10)->timestamp,
            'iat' => Carbon::now()->timestamp,
            'auth_time' => Carbon::now()->timestamp,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => ! is_null($user->email_verified_at),
        ];

        if ($nonce) {
            $payload['nonce'] = $nonce;
        }

        $privateKey = $this->jwkService->getPrivateKey($key['kid']);

        return JWT::encode($payload, $privateKey, 'RS256', $key['kid']);
    }
}
