<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UrlPreviewService
{
    /**
     * Fetch Open Graph metadata for a URL to build a link preview card.
     * Best-effort: any failure (timeout, non-HTML response, no tags found)
     * simply yields nulls instead of throwing, so posting never blocks on it.
     *
     * @return array{title: ?string, description: ?string, image: ?string}
     */
    public function fetch(string $url): array
    {
        $empty = ['title' => null, 'description' => null, 'image' => null];

        if (! $this->isSafeToFetch($url)) {
            return $empty;
        }

        try {
            $response = Http::timeout(5)
                ->withUserAgent('Mozilla/5.0 (compatible; AvarewaseLinkPreview/1.0)')
                ->withOptions(['allow_redirects' => ['max' => 5]])
                ->get($url);
        } catch (\Throwable $e) {
            Log::info('URL preview fetch failed', ['url' => $url, 'error' => $e->getMessage()]);

            return $empty;
        }

        if (! $response->successful() || ! Str::contains($response->header('Content-Type') ?? '', 'text/html')) {
            return $empty;
        }

        // Cap how much HTML we scan; OG tags live in <head>, no need to parse the whole body.
        $html = substr($response->body(), 0, 200_000);

        return [
            'title' => $this->extractMeta($html, 'og:title') ?? $this->extractTitleTag($html),
            'description' => $this->extractMeta($html, 'og:description') ?? $this->extractMeta($html, 'description'),
            'image' => $this->resolveUrl($this->extractMeta($html, 'og:image'), $url),
        ];
    }

    /**
     * Reject URLs that would make this server fetch its own internal network
     * (localhost, private ranges, cloud metadata endpoints) — the URL comes
     * from an admin/secretary but is still untrusted server-side input.
     */
    private function isSafeToFetch(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (! $host || ! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    private function extractMeta(string $html, string $property): ?string
    {
        $pattern = '/<meta[^>]+(?:property|name)=["\']'.preg_quote($property, '/').'["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/i';

        if (preg_match($pattern, $html, $matches) === 1) {
            return html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5);
        }

        // content may appear before property in the tag
        $pattern = '/<meta[^>]+content=["\']([^"\']*)["\'][^>]+(?:property|name)=["\']'.preg_quote($property, '/').'["\'][^>]*>/i';

        if (preg_match($pattern, $html, $matches) === 1) {
            return html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5);
        }

        return null;
    }

    private function extractTitleTag(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches) === 1) {
            return html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5);
        }

        return null;
    }

    private function resolveUrl(?string $maybeRelative, string $baseUrl): ?string
    {
        if (! $maybeRelative) {
            return null;
        }

        if (Str::startsWith($maybeRelative, ['http://', 'https://'])) {
            return $maybeRelative;
        }

        $base = parse_url($baseUrl);
        if (! $base || ! isset($base['scheme'], $base['host'])) {
            return null;
        }

        $origin = "{$base['scheme']}://{$base['host']}".(isset($base['port']) ? ":{$base['port']}" : '');

        return Str::startsWith($maybeRelative, '/')
            ? $origin.$maybeRelative
            : $origin.'/'.$maybeRelative;
    }
}
