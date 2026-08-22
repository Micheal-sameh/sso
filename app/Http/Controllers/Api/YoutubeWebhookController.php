<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\CreateCmcPostFromYoutubeJob;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Receives YouTube's WebSub (PubSubHubbub) push notifications for new
 * uploads on the configured channel and turns each into a CMC post.
 *
 * @see https://developers.google.com/youtube/v3/guides/push_notifications
 */
class YoutubeWebhookController extends Controller
{
    /**
     * The hub verifies a (re)subscription by GETing the callback URL and
     * expecting the challenge echoed back verbatim.
     */
    public function verify(Request $request): Response
    {
        return response($request->query('hub_challenge', ''), 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * The hub POSTs an Atom feed here whenever the channel publishes or
     * edits a video.
     */
    public function handle(Request $request): Response
    {
        if (! $this->hasValidSignature($request)) {
            Log::warning('YouTube webhook: invalid signature');

            return response('', 403);
        }

        $xml = simplexml_load_string($request->getContent());

        if ($xml === false) {
            return response('', 400);
        }

        $xml->registerXPathNamespace('yt', 'http://www.youtube.com/xml/schemas/2015');
        $xml->registerXPathNamespace('atom', 'http://www.w3.org/2005/Atom');

        foreach ($xml->xpath('//atom:entry') as $entry) {
            $entry->registerXPathNamespace('yt', 'http://www.youtube.com/xml/schemas/2015');
            $videoId = (string) ($entry->xpath('yt:videoId')[0] ?? '');
            $title = (string) ($entry->title ?? '');
            $publishedAt = (string) ($entry->published ?? '') ?: null;

            if ($videoId === '' || $title === '') {
                continue;
            }

            CreateCmcPostFromYoutubeJob::dispatch($videoId, $title, $publishedAt);
        }

        return response('', 200);
    }

    private function hasValidSignature(Request $request): bool
    {
        $secret = config('services.youtube.webhook_secret');

        if (! $secret) {
            return true;
        }

        $header = $request->header('X-Hub-Signature', '');

        if (! str_starts_with($header, 'sha1=')) {
            return false;
        }

        $expected = hash_hmac('sha1', $request->getContent(), $secret);

        return hash_equals($expected, substr($header, 5));
    }
}
