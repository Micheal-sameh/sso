<?php

namespace App\Providers\Socialite;

use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class SsoProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     */
    protected $scopes = ['openid', 'profile', 'email'];

    /**
     * The separating character for the requested scopes.
     */
    protected $scopeSeparator = ' ';

    /**
     * Get the authentication URL for the provider.
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            config('services.sso.base_url').'/oauth/authorize',
            $state
        );
    }

    /**
     * Get the token URL for the provider.
     */
    protected function getTokenUrl(): string
    {
        return config('services.sso.base_url').'/oauth/token';
    }

    /**
     * Get the raw user for the given access token.
     */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(
            config('services.sso.base_url').'/oauth/userinfo',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     */
    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'],
            'name' => $user['name'],
            'email' => $user['email'],
            'email_verified' => $user['email_verified'],
        ]);
    }

    /**
     * Get the POST fields for the token request.
     */
    protected function getTokenFields($code): array
    {
        return Arr::add(
            parent::getTokenFields($code),
            'grant_type',
            'authorization_code'
        );
    }

    /**
     * Get the code challenge for PKCE.
     */
    protected function getCodeChallenge(): ?string
    {
        if (! $this->usesPKCE()) {
            return null;
        }

        $codeVerifier = $this->getCodeVerifier();

        return $this->generateCodeChallenge($codeVerifier);
    }

    /**
     * Generate a code challenge from a code verifier.
     */
    protected function generateCodeChallenge(string $codeVerifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');
    }

    /**
     * Get the code verifier for PKCE.
     */
    protected function getCodeVerifier(): string
    {
        return $this->request->session()->get('code_verifier', $this->generateCodeVerifier());
    }

    /**
     * Generate a secure code verifier.
     */
    protected function generateCodeVerifier(): string
    {
        $codeVerifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $this->request->session()->put('code_verifier', $codeVerifier);

        return $codeVerifier;
    }

    /**
     * Determine if the provider uses PKCE.
     */
    protected function usesPKCE(): bool
    {
        return true;
    }

    /**
     * Add PKCE parameters to the authorization URL.
     */
    protected function buildAuthUrlFromBase($url, $state): string
    {
        $url = parent::buildAuthUrlFromBase($url, $state);

        if ($this->usesPKCE()) {
            $codeChallenge = $this->getCodeChallenge();
            $url .= '&code_challenge='.$codeChallenge;
            $url .= '&code_challenge_method=S256';
        }

        return $url;
    }

    /**
     * Add the code verifier to the token request.
     */
    protected function getTokenFields($code): array
    {
        $fields = parent::getTokenFields($code);

        if ($this->usesPKCE()) {
            $codeVerifier = $this->getCodeVerifier();
            $fields['code_verifier'] = $codeVerifier;
        }

        return $fields;
    }
}
