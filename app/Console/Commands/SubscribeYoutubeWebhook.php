<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SubscribeYoutubeWebhook extends Command
{
    protected $signature = 'youtube:subscribe-webhook';

    protected $description = 'Subscribe (or renew the subscription) to WebSub push notifications for the configured YouTube channel';

    private const HUB_URL = 'https://pubsubhubbub.appspot.com/subscribe';

    public function handle(): int
    {
        $channelId = config('services.youtube.channel_id');

        if (! $channelId) {
            $this->error('YOUTUBE_CHANNEL_ID is not configured.');

            return self::FAILURE;
        }

        $response = Http::asForm()->post(self::HUB_URL, [
            'hub.mode' => 'subscribe',
            'hub.topic' => "https://www.youtube.com/xml/feeds/videos.xml?channel_id={$channelId}",
            'hub.callback' => route('webhooks.youtube'),
            'hub.verify' => 'async',
            'hub.secret' => config('services.youtube.webhook_secret'),
        ]);

        if (! $response->successful()) {
            $this->error("Subscription request failed: HTTP {$response->status()}");

            return self::FAILURE;
        }

        $this->info('Subscription request accepted; the hub will verify it asynchronously.');

        return self::SUCCESS;
    }
}
