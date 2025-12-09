<?php

namespace App\Services;

use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class JWKService
{
    private const CACHE_KEY = 'sso_jwks';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get the JWKS (JSON Web Key Set) for OIDC
     */
    public function getJwks(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->generateJwks();
        });
    }

    /**
     * Generate a new JWKS
     */
    private function generateJwks(): array
    {
        $keys = [];

        // Generate RSA key pair
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $publicKey = openssl_pkey_get_details($privateKey)['key'];

        // Get key details
        $keyDetails = openssl_pkey_get_details($privateKey);
        $modulus = $this->base64UrlEncode($keyDetails['rsa']['n']);
        $exponent = $this->base64UrlEncode($keyDetails['rsa']['e']);

        // Create kid (key ID) - using timestamp for uniqueness
        $kid = 'rsa-'.time();

        $keys[] = [
            'kty' => 'RSA',
            'use' => 'sig',
            'kid' => $kid,
            'n' => $modulus,
            'e' => $exponent,
            'alg' => 'RS256',
        ];

        // Store private key securely
        Storage::put('keys/private_'.$kid.'.pem', $privateKey);

        return [
            'keys' => $keys,
        ];
    }

    /**
     * Get the private key for signing
     */
    public function getPrivateKey(string $kid): ?string
    {
        $keyPath = 'keys/private_'.$kid.'.pem';

        return Storage::exists($keyPath) ? Storage::get($keyPath) : null;
    }

    /**
     * Get the public key for verification
     */
    public function getPublicKey(string $kid): ?Key
    {
        $jwks = $this->getJwks();
        foreach ($jwks['keys'] as $key) {
            if ($key['kid'] === $kid) {
                // Reconstruct public key from JWKS
                $modulus = $this->base64UrlDecode($key['n']);
                $exponent = $this->base64UrlDecode($key['e']);

                $publicKey = "-----BEGIN PUBLIC KEY-----\n".
                    chunk_split(base64_encode($this->constructRsaPublicKey($modulus, $exponent)), 64).
                    "-----END PUBLIC KEY-----\n";

                return new Key($publicKey, 'RS256');
            }
        }

        return null;
    }

    /**
     * Rotate keys (generate new key pair)
     */
    public function rotateKeys(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->getJwks(); // This will generate new keys
    }

    /**
     * Base64 URL encode
     */
    private function base64UrlEncode(string $data): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Base64 URL decode
     */
    private function base64UrlDecode(string $data): string
    {
        $data = str_replace(['-', '_'], ['+', '/'], $data);

        return base64_decode($data);
    }

    /**
     * Construct RSA public key from modulus and exponent
     */
    private function constructRsaPublicKey(string $modulus, string $exponent): string
    {
        $modulus = pack('H*', str_pad($modulus, 512, '0', STR_PAD_LEFT));
        $exponent = pack('H*', str_pad($exponent, 6, '0', STR_PAD_LEFT));

        $rsaKey = pack('H*', '30819f300d06092a864886f70d010101050003818d0030818902818100').
                  $modulus.
                  pack('H*', '0203').
                  $exponent;

        return $rsaKey;
    }
}
