<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Pushes a back-channel notification to a client app when an admin changes a
 * user's details, so the client can refresh its own locally-cached copy of
 * that user (e.g. via the SSO's userinfo endpoint) instead of waiting for
 * the user's next login.
 */
class NotifyClientUserUpdatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    /**
     * @param  array  $userInfo  the OIDC-shaped profile (UserDTO::toOidcArray()) — same
     *                           shape the client already consumes from /api/userinfo at
     *                           login, so it can be applied the same way without the
     *                           client needing a stored access token to fetch it itself
     */
    public function __construct(
        protected string $clientId,
        protected array $userInfo,
        protected string $webhookUri,
        protected string $webhookSecret,
    ) {}

    public function handle(): void
    {
        $payload = [
            ...$this->userInfo,
            'event' => 'user.updated',
            'client_id' => $this->clientId,
            'updated_at' => now()->toIso8601String(),
            'nonce' => Str::random(16),
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $this->webhookSecret);

        try {
            $options = WebhookGuard::options($this->webhookUri);
        } catch (InvalidArgumentException $e) {
            // Unsafe target: retrying won't help, so fail the job outright.
            $this->fail($e);

            return;
        }

        $response = Http::withOptions($options)
            ->timeout(5)
            ->withHeaders(['X-SSO-Signature' => 'sha256='.$signature])
            ->post($this->webhookUri, $payload);

        if ($response->failed()) {
            // Throwing lets the queue's retry/backoff policy above take over.
            $response->throw();
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::warning('Back-channel user-updated notification failed permanently', [
            'client_id' => $this->clientId,
            'sub' => $this->userInfo['sub'] ?? null,
            'webhook_uri' => $this->webhookUri,
            'error' => $exception->getMessage(),
        ]);
    }
}
