<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Guards outbound webhook calls against SSRF: the admin-supplied URL must
 * resolve only to public addresses, so it can't be aimed at internal services
 * or cloud metadata endpoints. Returns Guzzle options that pin the connection
 * to the vetted IP (closing the DNS-rebinding gap) and disable redirects.
 */
class WebhookGuard
{
    /**
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException
     */
    public static function options(string $url): array
    {
        $parts = parse_url($url);
        $host = $parts['host'] ?? null;

        if ($host === null || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true)) {
            throw new InvalidArgumentException('Webhook URL is not a valid http(s) URL.');
        }

        $port = $parts['port'] ?? (($parts['scheme'] === 'https') ? 443 : 80);
        $ips = filter_var($host, FILTER_VALIDATE_IP) ? [$host] : (gethostbynamel($host) ?: []);

        if ($ips === []) {
            throw new InvalidArgumentException("Webhook host [{$host}] does not resolve.");
        }

        // Loopback is allowed outside production so local development against
        // an app on localhost keeps working (the request rule permits it too).
        $allowLoopback = ! app()->isProduction();

        foreach ($ips as $ip) {
            $isLoopback = $ip === '::1' || str_starts_with($ip, '127.');

            if ($isLoopback && $allowLoopback) {
                continue;
            }

            if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new InvalidArgumentException("Webhook host [{$host}] resolves to a non-public address.");
            }
        }

        return [
            'allow_redirects' => false,
            'curl' => [CURLOPT_RESOLVE => ["{$host}:{$port}:{$ips[0]}"]],
        ];
    }
}
