<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * "Trusted device" tokens: issued after a successful password login and
 * bound to one user. A device that presents a valid token for the account
 * it is logging into gets its own failed-attempt counter in
 * LoginAttemptLimiter, so someone guessing that account's password from
 * elsewhere can't lock the real user's phone out.
 *
 * The token is encrypted with the app key, so it can't be forged or moved
 * to another account; it carries no authority beyond choosing a counter.
 */
class TrustedDeviceService
{
    public const COOKIE = 'trusted_device';

    public const HEADER = 'X-Device-Token';

    public const LIFETIME_MINUTES = 60 * 24 * 90;

    public function issue(User $user, ?string $existing = null): string
    {
        if ($existing !== null && $this->deviceId($existing, $user) !== null) {
            return $existing;
        }

        return Crypt::encryptString(json_encode([
            'uid' => $user->id,
            'did' => Str::random(32),
        ]));
    }

    /**
     * The device id inside the token, or null when the token is missing,
     * invalid, or was issued for a different account.
     */
    public function deviceId(?string $token, User $user): ?string
    {
        if ($token === null || $token === '') {
            return null;
        }

        try {
            $payload = json_decode(Crypt::decryptString($token), true);
        } catch (DecryptException) {
            return null;
        }

        if (! is_array($payload) || ($payload['uid'] ?? null) !== $user->id || ! is_string($payload['did'] ?? null)) {
            return null;
        }

        return $payload['did'];
    }
}
