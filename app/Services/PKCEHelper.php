<?php

namespace App\Services;

class PKCEHelper
{
    /**
     * Generate a code challenge from a code verifier
     */
    public function generateCodeChallenge(string $codeVerifier, string $method = 'S256'): string
    {
        if ($method === 'S256') {
            return $this->base64UrlEncode(hash('sha256', $codeVerifier, true));
        }

        // Plain method (not recommended for production)
        return $codeVerifier;
    }

    /**
     * Validate a code challenge against a code verifier
     */
    public function validateCodeChallenge(string $codeVerifier, string $codeChallenge): bool
    {
        $expectedChallenge = $this->generateCodeChallenge($codeVerifier, 'S256');

        return hash_equals($expectedChallenge, $codeChallenge);
    }

    /**
     * Generate a secure code verifier
     */
    public function generateCodeVerifier(int $length = 64): string
    {
        if ($length < 43 || $length > 128) {
            throw new \InvalidArgumentException('Code verifier length must be between 43 and 128 characters');
        }

        return $this->base64UrlEncode(random_bytes($length));
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
