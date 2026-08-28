<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YoutubeLiveStatusService
{
    public const CACHE_KEY = 'youtube:live-status';

    // Kept short as a fallback for the polling path in case a webhook push
    // is missed; the webhook path (see CheckYoutubeLiveStatusJob) updates
    // this cache immediately and doesn't wait on the TTL.
    private const CACHE_TTL_SECONDS = 60;

    public function current(): ?array
    {
        $channelId = config('services.youtube.channel_id');
        $apiKey = config('services.youtube.api_key');

        if (! $channelId || ! $apiKey) {
            return null;
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () use ($channelId, $apiKey) {
            return $this->fetch($channelId, $apiKey);
        });
    }

    public function remember(?array $status): void
    {
        if ($status === null) {
            Cache::forget(self::CACHE_KEY);

            return;
        }

        Cache::put(self::CACHE_KEY, $status, self::CACHE_TTL_SECONDS);
    }

    /**
     * Looks up a single video (used by the YouTube webhook, which tells us
     * a specific video was created/updated but not whether it's a live
     * broadcast) and classifies it as live, upcoming, ended, or not a
     * broadcast at all.
     */
    public function evaluateVideo(string $videoId): ?array
    {
        $apiKey = config('services.youtube.api_key');
        if (! $apiKey) {
            return null;
        }

        $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
            'key' => $apiKey,
            'id' => $videoId,
            'part' => 'snippet,liveStreamingDetails',
        ]);

        if (! $response->successful()) {
            Log::warning('YouTube video lookup failed', [
                'video_id' => $videoId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $item = $response->json('items.0');
        if (! $item) {
            return null;
        }

        $details = $item['liveStreamingDetails'] ?? null;
        if (! $details) {
            return null;
        }

        $status = match (true) {
            isset($details['actualStartTime']) && ! isset($details['actualEndTime']) => 'live',
            isset($details['actualEndTime']) => 'ended',
            isset($details['scheduledStartTime']) => 'upcoming',
            default => null,
        };

        if ($status === null) {
            return null;
        }

        return [
            'status' => $status,
            'video_id' => $videoId,
            'title' => $item['snippet']['title'] ?? null,
            'thumbnail_url' => $item['snippet']['thumbnails']['high']['url']
                ?? $item['snippet']['thumbnails']['default']['url']
                ?? null,
            'scheduled_start_time' => $details['scheduledStartTime'] ?? null,
        ];
    }

    private function fetch(string $channelId, string $apiKey): ?array
    {
        $live = $this->search($channelId, $apiKey, 'live');
        if ($live) {
            return $live;
        }

        return $this->search($channelId, $apiKey, 'upcoming');
    }

    private function search(string $channelId, string $apiKey, string $eventType): ?array
    {
        $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
            'key' => $apiKey,
            'channelId' => $channelId,
            'eventType' => $eventType,
            'type' => 'video',
            'part' => 'snippet',
            'order' => 'date',
            'maxResults' => 1,
        ]);

        if (! $response->successful()) {
            Log::warning('YouTube live status lookup failed', [
                'event_type' => $eventType,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $item = $response->json('items.0');
        $videoId = $item['id']['videoId'] ?? null;
        if (! $item || ! $videoId) {
            return null;
        }

        // search.list's eventType filter is index-driven and can lag well
        // behind the real broadcast lifecycle (e.g. a finished stream can
        // still surface under eventType=upcoming for a while). Trust
        // liveStreamingDetails, not the query we searched with, for the
        // actual status.
        $details = $this->liveStreamingDetails($videoId, $apiKey);

        $status = match (true) {
            isset($details['actualStartTime']) && ! isset($details['actualEndTime']) => 'live',
            isset($details['actualEndTime']) => 'ended',
            isset($details['scheduledStartTime']) => 'upcoming',
            default => null,
        };

        if ($status === null || $status === 'ended') {
            return null;
        }

        return [
            'status' => $status,
            'video_id' => $videoId,
            'title' => $item['snippet']['title'] ?? null,
            'thumbnail_url' => $item['snippet']['thumbnails']['high']['url']
                ?? $item['snippet']['thumbnails']['default']['url']
                ?? null,
            'scheduled_start_time' => $details['scheduledStartTime'] ?? null,
        ];
    }

    private function liveStreamingDetails(string $videoId, string $apiKey): array
    {
        $response = Http::get('https://www.googleapis.com/youtube/v3/videos', [
            'key' => $apiKey,
            'id' => $videoId,
            'part' => 'liveStreamingDetails',
        ]);

        if (! $response->successful()) {
            return [];
        }

        return $response->json('items.0.liveStreamingDetails', []);
    }
}
