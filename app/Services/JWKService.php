<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class JWKService
{
    protected string $jwksUrl;

    public function __construct()
    {
        $this->jwksUrl = config('sso.endpoints.jwks');
    }

    /**
     * Get JWKS from the SSO server.
     */
    public function getJWKS(): array
    {
        return Cache::remember('sso_jwks', 3600, function () {
            $response = Http::get($this->jwksUrl);

            return $response->json();
        });
    }

    /**
     * Verify ID Token signature using JWKS.
     *
     * @return array Decoded payload
     *
     * @throws \Exception
     */
    public function verifyIdToken(string $idToken): array
    {
        $jwks = $this->getJWKS();

        // Decode header to get kid
        $header = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $idToken)[0]))), true);
        $kid = $header['kid'] ?? null;

        if (! $kid) {
            throw new \Exception('ID Token missing kid in header');
        }

        // Find the key
        $key = null;
        foreach ($jwks['keys'] ?? [] as $jwk) {
            if ($jwk['kid'] === $kid && $jwk['use'] === 'sig' && $jwk['kty'] === 'RSA') {
                $key = $jwk;
                break;
            }
        }

        if (! $key) {
            throw new \Exception('Unable to find matching JWK');
        }

        // Convert JWK to PEM
        $pem = $this->jwkToPem($key);

        // Verify and decode
        try {
            $payload = JWT::decode($idToken, new Key($pem, 'RS256'));

            return (array) $payload;
        } catch (\Exception $e) {
            throw new \Exception('ID Token verification failed: '.$e->getMessage());
        }
    }

    /**
     * Convert JWK to PEM format.
     */
    protected function jwkToPem(array $jwk): string
    {
        $modulus = $this->base64UrlDecode($jwk['n']);
        $exponent = $this->base64UrlDecode($jwk['e']);

        $modulus = pack('Ca*a*', 2, $this->encodeLength(strlen($modulus)), $modulus);
        $exponent = pack('Ca*a*', 2, $this->encodeLength(strlen($exponent)), $exponent);

        $rsaKey = pack('Ca*a*a*', 48, $this->encodeLength(strlen($modulus) + strlen($exponent)), $modulus, $exponent);

        $publicKey = pack('Ca*a*', 3, $this->encodeLength(strlen($rsaKey)), $rsaKey);

        $publicKey = "-----BEGIN PUBLIC KEY-----\n".
                     chunk_split(base64_encode($publicKey), 64, "\n").
                     '-----END PUBLIC KEY-----';

        return $publicKey;
    }

    /**
     * Base64 URL decode.
     */
    protected function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Encode length for ASN.1.
     */
    protected function encodeLength(int $length): string
    {
        if ($length < 128) {
            return pack('C', $length);
        }

        $lenBytes = '';
        while ($length > 0) {
            $lenBytes = pack('C', $length & 0xFF).$lenBytes;
            $length >>= 8;
        }

        return pack('C', 0x80 | strlen($lenBytes)).$lenBytes;
    }
}
