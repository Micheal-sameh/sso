<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OAuth2Service
{
    protected string $issuer;

    protected string $clientId;

    protected ?string $clientSecret;

    protected string $redirectUri;

    protected array $endpoints;

    protected JWKService $jwkService;

    public function __construct(JWKService $jwkService)
    {
        $this->issuer = config('sso.issuer');
        $this->clientId = config('sso.client_id');
        $this->clientSecret = config('sso.client_secret');
        $this->redirectUri = config('sso.redirect_uri');
        $this->endpoints = config('sso.endpoints');
        $this->jwkService = $jwkService;
    }

    /**
     * Exchange authorization code for tokens.
     *
     * @throws \Exception
     */
    public function exchangeCodeForTokens(string $code, string $codeVerifier): array
    {
        $response = Http::asForm()->post($this->endpoints['token'], [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code_verifier' => $codeVerifier,
        ]);

        if ($response->failed()) {
            Log::error('Token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Token exchange failed: '.$response->body());
        }

        $data = $response->json();

        // Verify ID Token
        if (isset($data['id_token'])) {
            $payload = $this->jwkService->verifyIdToken($data['id_token']);

            // Validate claims
            $this->validateIdTokenClaims($payload);
        }

        return $data;
    }

    /**
     * Refresh access token.
     *
     * @throws \Exception
     */
    public function refreshToken(string $refreshToken): array
    {
        $response = Http::asForm()->post($this->endpoints['token'], [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if ($response->failed()) {
            Log::error('Token refresh failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Token refresh failed: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Revoke refresh token.
     */
    public function revokeToken(string $refreshToken): bool
    {
        if (! isset($this->endpoints['revoke'])) {
            return true; // Skip if revoke endpoint not available
        }

        $response = Http::asForm()->post($this->endpoints['revoke'], [
            'token' => $refreshToken,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        return $response->successful();
    }

    /**
     * Validate ID Token claims.
     *
     * @throws \Exception
     */
    protected function validateIdTokenClaims(array $payload): void
    {
        // Validate issuer
        if (($payload['iss'] ?? null) !== $this->issuer) {
            throw new \Exception('Invalid issuer in ID Token');
        }

        // Validate audience
        if (($payload['aud'] ?? null) !== $this->clientId) {
            throw new \Exception('Invalid audience in ID Token');
        }

        // Validate expiration
        if (($payload['exp'] ?? 0) < time()) {
            throw new \Exception('ID Token has expired');
        }
    }

    /**
     * Create or update user from ID Token payload.
     */
    public function createOrUpdateUser(array $payload, array $tokens): User
    {
        $user = User::where('sub', $payload['sub'])->first();

        $userData = [
            'sub' => $payload['sub'],
            'email' => $payload['email'] ?? null,
            'name' => $payload['name'] ?? $payload['email'] ?? 'Unknown',
            'membership_code' => $payload['membership_code'] ?? null,
            'refresh_token' => isset($tokens['refresh_token']) ? Crypt::encryptString($tokens['refresh_token']) : null,
        ];

        if ($user) {
            $user->update($userData);
        } else {
            $user = User::create($userData);
        }

        return $user;
    }

    /**
     * Store tokens in session.
     */
    public function storeTokensInSession(array $tokens, User $user): void
    {
        $sessionKeys = config('sso.session_keys');

        Session::put([
            $sessionKeys['user_id'] => $user->id,
            $sessionKeys['id_token'] => $tokens['id_token'] ?? null,
            $sessionKeys['access_token'] => $tokens['access_token'] ?? null,
            $sessionKeys['refresh_token'] => isset($tokens['refresh_token']) ? Crypt::encryptString($tokens['refresh_token']) : null,
            $sessionKeys['token_expires_at'] => isset($tokens['expires_in']) ? time() + $tokens['expires_in'] : null,
        ]);
    }

    /**
     * Check if access token needs refresh.
     */
    public function needsTokenRefresh(): bool
    {
        $sessionKeys = config('sso.session_keys');
        $expiresAt = Session::get($sessionKeys['token_expires_at']);

        if (! $expiresAt) {
            return false;
        }

        $buffer = config('sso.token_refresh.buffer_time', 300);

        return (time() + $buffer) >= $expiresAt;
    }

    /**
     * Refresh tokens if needed.
     *
     * @return bool True if refreshed, false if not needed
     *
     * @throws \Exception
     */
    public function refreshTokensIfNeeded(): bool
    {
        if (! $this->needsTokenRefresh()) {
            return false;
        }

        $sessionKeys = config('sso.session_keys');
        $encryptedRefreshToken = Session::get($sessionKeys['refresh_token']);

        if (! $encryptedRefreshToken) {
            throw new \Exception('No refresh token available');
        }

        $refreshToken = Crypt::decryptString($encryptedRefreshToken);
        $newTokens = $this->refreshToken($refreshToken);

        // Update session with new tokens
        $userId = Session::get($sessionKeys['user_id']);
        $user = User::find($userId);

        if ($user) {
            $this->storeTokensInSession($newTokens, $user);

            // Update refresh token in database if provided
            if (isset($newTokens['refresh_token'])) {
                $user->update([
                    'refresh_token' => Crypt::encryptString($newTokens['refresh_token']),
                ]);
            }
        }

        return true;
    }

    /**
     * Clear session data.
     */
    public function clearSession(): void
    {
        $sessionKeys = config('sso.session_keys');

        Session::forget([
            $sessionKeys['user_id'],
            $sessionKeys['id_token'],
            $sessionKeys['access_token'],
            $sessionKeys['refresh_token'],
            $sessionKeys['token_expires_at'],
            $sessionKeys['state'],
            $sessionKeys['code_verifier'],
        ]);
    }
}
