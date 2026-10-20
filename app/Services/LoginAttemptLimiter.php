<?php

namespace App\Services;

use App\Exceptions\Auth\TooManyLoginAttemptsException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Progressive lockout for failed password logins, keyed on the membership
 * code and a "bucket": after 4 wrong attempts the bucket must wait 30s, after
 * 5 more 2min, and after every further 5 wrong attempts 5min. From 50
 * failures on, every further wrong attempt locks it for 1 hour. A successful
 * login clears it.
 *
 * Buckets: a device holding a valid trusted-device token for the account has
 * its own bucket; everything else shares the account's "unknown" bucket. That
 * way strangers guessing a membership code can only lock out other strangers,
 * not the real user's own phone. Blocked attempts are rejected without being
 * checked or counted, so the next tier starts once the wait is over and the
 * user fails again.
 */
class LoginAttemptLimiter
{
    public const UNKNOWN_BUCKET = 'unknown';

    /** Failures before the first lock; each following lock needs STEP more. */
    private const THRESHOLD = 4;

    private const STEP = 5;

    /** Lock lengths in seconds per tier; the last one repeats. */
    private const LOCK_SECONDS = [30, 120, 300];

    /** From this many failures on, every further failure locks for LONG_LOCK_SECONDS. */
    private const LONG_LOCK_AFTER = 50;

    private const LONG_LOCK_SECONDS = 3600;

    /** Forget failures after a day of no activity. */
    private const COUNTER_TTL_SECONDS = 86400;

    /**
     * @throws TooManyLoginAttemptsException
     */
    public function ensureNotLocked(string $membershipCode, string $bucket = self::UNKNOWN_BUCKET): void
    {
        $until = (int) Cache::get($this->lockKey($membershipCode, $bucket), 0);
        $remaining = $until - now()->timestamp;

        if ($remaining > 0) {
            throw new TooManyLoginAttemptsException($remaining);
        }
    }

    public function recordFailure(string $membershipCode, string $bucket = self::UNKNOWN_BUCKET): void
    {
        $key = $this->failKey($membershipCode, $bucket);

        Cache::add($key, 0, self::COUNTER_TTL_SECONDS);
        $failures = (int) Cache::increment($key);

        if ($failures >= self::LONG_LOCK_AFTER) {
            $seconds = self::LONG_LOCK_SECONDS;
        } elseif ($failures < self::THRESHOLD || ($failures - self::THRESHOLD) % self::STEP !== 0) {
            return;
        } else {
            $tier = intdiv($failures - self::THRESHOLD, self::STEP);
            $seconds = self::LOCK_SECONDS[min($tier, count(self::LOCK_SECONDS) - 1)];
        }

        Cache::put($this->lockKey($membershipCode, $bucket), now()->timestamp + $seconds, $seconds);
    }

    /** Reset one bucket after a successful login. */
    public function clear(string $membershipCode, string $bucket = self::UNKNOWN_BUCKET): void
    {
        Cache::forget($this->failKey($membershipCode, $bucket));
        Cache::forget($this->lockKey($membershipCode, $bucket));
    }

    /**
     * Admin override: lift every lock and zero every counter for the account,
     * in all buckets. Buckets can't be enumerated, so this bumps an epoch that
     * is part of every key, orphaning the old entries.
     */
    public function clearAll(string $membershipCode): void
    {
        $key = $this->epochKey($membershipCode);

        Cache::add($key, 0, now()->addYears(10));
        Cache::increment($key);
    }

    private function failKey(string $membershipCode, string $bucket): string
    {
        return 'login:fails:'.$this->scope($membershipCode, $bucket);
    }

    private function lockKey(string $membershipCode, string $bucket): string
    {
        return 'login:lock:'.$this->scope($membershipCode, $bucket);
    }

    private function scope(string $membershipCode, string $bucket): string
    {
        return $this->normalise($membershipCode).':'.(int) Cache::get($this->epochKey($membershipCode), 0).':'.sha1($bucket);
    }

    private function epochKey(string $membershipCode): string
    {
        return 'login:epoch:'.$this->normalise($membershipCode);
    }

    private function normalise(string $membershipCode): string
    {
        return sha1(Str::lower(trim($membershipCode)));
    }
}
