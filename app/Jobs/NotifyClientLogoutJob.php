<?php

namespace App\Jobs;

use App\Support\WebhookGuard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Pushes a back-channel logout notification to a client app when a user's
 * access to that client is revoked, so the client can invalidate whatever
 * local session/cookie it keeps independently of the OAuth token.
 */
class NotifyClientLogoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    /**
     * @param  string  $sub  the OIDC "sub" claim (this SSO's user uuid) — the same
     *                       value client apps store as their local "avarewase_sub" key
     */
    public function __construct(
        protected string $clientId,
        protected string $sub,
        protected string $logoutUri,
        protected string $logoutSecret,
    ) {}

    public function handle(): void
    {
        $payload = [
            'event' => 'user.access_revoked',
            'client_id' => $this->clientId,
            'sub' => $this->sub,
            'revoked_at' => now()->toIso8601String(),
            'nonce' => Str::random(16),
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $this->logoutSecret);

        try {
            $options = WebhookGuard::options($this->logoutUri);
        } catch (InvalidArgumentException $e) {
            // Unsafe target: retrying won't help, so fail the job outright.
            $this->fail($e);

            return;
        }

        $response = Http::withOptions($options)
            ->timeout(5)
            ->withHeaders(['X-SSO-Signature' => 'sha256='.$signature])
            ->post($this->logoutUri, $payload);

        if ($response->failed()) {
            // Throwing lets the queue's retry/backoff policy above take over.
            $response->throw();
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::warning('Back-channel logout notification failed permanently', [
            'client_id' => $this->clientId,
            'sub' => $this->sub,
            'logout_uri' => $this->logoutUri,
            'error' => $exception->getMessage(),
        ]);
    }
}
