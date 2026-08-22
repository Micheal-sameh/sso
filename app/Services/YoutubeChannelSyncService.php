<?php

namespace App\Services;

use App\Models\YoutubeVideo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YoutubeChannelSyncService
{
    // Caps a single sync run to at most 500 videos (10 pages of 50). Enough
    // to cover a channel's full history in normal cases while keeping a
    // single run's Data API quota usage bounded.
    private const MAX_PAGES = 10;

    private const PAGE_SIZE = 50;

    /**
     * Pull the channel's full upload history and upsert it into
     * youtube_videos, without touching is_pinned or cmc_post_id on videos
     * that already exist (those are owned by the admin/webhook paths).
     *
     * @return int number of videos synced
     */
    public function sync(): int
    {
        $channelId = config('services.youtube.channel_id');
        $apiKey = config('services.youtube.api_key');

        if (! $channelId || ! $apiKey) {
            return 0;
        }

        $uploadsPlaylistId = $this->uploadsPlaylistId($channelId, $apiKey);

        if (! $uploadsPlaylistId) {
            return 0;
        }

        $synced = 0;
        $pageToken = null;

        for ($page = 0; $page < self::MAX_PAGES; $page++) {
            $response = Http::get('https://www.googleapis.com/youtube/v3/playlistItems', array_filter([
                'key' => $apiKey,
                'playlistId' => $uploadsPlaylistId,
                'part' => 'snippet',
                'maxResults' => self::PAGE_SIZE,
                'pageToken' => $pageToken,
            ]));

            if (! $response->successful()) {
                Log::warning('YouTube channel sync: playlistItems request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                break;
            }

            foreach ($response->json('items', []) as $item) {
                if ($this->upsertVideo($item['snippet'] ?? [])) {
                    $synced++;
                }
            }

            $pageToken = $response->json('nextPageToken');

            if (! $pageToken) {
                break;
            }
        }

        return $synced;
    }

    private function uploadsPlaylistId(string $channelId, string $apiKey): ?string
    {
        $response = Http::get('https://www.googleapis.com/youtube/v3/channels', [
            'key' => $apiKey,
            'id' => $channelId,
            'part' => 'contentDetails',
        ]);

        if (! $response->successful()) {
            Log::warning('YouTube channel sync: channels request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json('items.0.contentDetails.relatedPlaylists.uploads');
    }

    private function upsertVideo(array $snippet): bool
    {
        $videoId = $snippet['resourceId']['videoId'] ?? null;
        $title = $snippet['title'] ?? null;

        if (! $videoId || ! $title) {
            return false;
        }

        YoutubeVideo::updateOrCreate(
            ['video_id' => $videoId],
            [
                'title' => $title,
                'thumbnail_url' => $snippet['thumbnails']['high']['url']
                    ?? $snippet['thumbnails']['default']['url']
                    ?? null,
                'published_at' => $snippet['publishedAt'] ?? null,
            ],
        );

        return true;
    }
}
