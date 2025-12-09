<?php

namespace App\Services;

class PKCEHelper
{
    /**
     * Generate a code verifier for PKCE.
     */
    public function generateVerifier(): string
    {
        return bin2hex(random_bytes(32)); // 64 characters
    }

    /**
     * Generate a code challenge from a code verifier.
     */
    public function generateChallenge(string $verifier): string
    {
        $hash = hash('sha256', $verifier, true);

        return $this->base64UrlEncode($hash);
    }

    /**
     * Base64 URL encode.
     */
    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
