<?php

namespace App\Services;

use App\Exceptions\Auth\TooManyLoginAttemptsException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Progressive lockout for failed password logins, keyed on the membership
 * code: after 4 wrong attempts the account must wait 30s, after 5 more 2min,
 * and after every further 5 wrong attempts 5min. A successful login clears it.
 *
 * Blocked attempts are rejected without being checked or counted, so the
 * next tier only starts once the wait is over and the user fails again.
 */
class LoginAttemptLimiter
{
    /** Failures before the first lock; each following lock needs STEP more. */
    private const THRESHOLD = 4;

    private const STEP = 5;

    /** Lock lengths in seconds per tier; the last one repeats. */
    private const LOCK_SECONDS = [30, 120, 300];

    /** Forget failures after a day of no activity. */
    private const COUNTER_TTL_SECONDS = 86400;

    /**
     * @throws TooManyLoginAttemptsException
     */
    public function ensureNotLocked(string $membershipCode): void
    {
        $until = (int) Cache::get($this->lockKey($membershipCode), 0);
        $remaining = $until - now()->timestamp;

        if ($remaining > 0) {
            throw new TooManyLoginAttemptsException($remaining);
        }
    }

    public function recordFailure(string $membershipCode): void
    {
        $key = $this->failKey($membershipCode);

        Cache::add($key, 0, self::COUNTER_TTL_SECONDS);
        $failures = (int) Cache::increment($key);

        if ($failures < self::THRESHOLD || ($failures - self::THRESHOLD) % self::STEP !== 0) {
            return;
        }

        $tier = intdiv($failures - self::THRESHOLD, self::STEP);
        $seconds = self::LOCK_SECONDS[min($tier, count(self::LOCK_SECONDS) - 1)];

        Cache::put($this->lockKey($membershipCode), now()->timestamp + $seconds, $seconds);
    }

    public function clear(string $membershipCode): void
    {
        Cache::forget($this->failKey($membershipCode));
        Cache::forget($this->lockKey($membershipCode));
    }

    private function failKey(string $membershipCode): string
    {
        return 'login:fails:'.$this->normalise($membershipCode);
    }

    private function lockKey(string $membershipCode): string
    {
        return 'login:lock:'.$this->normalise($membershipCode);
    }

    private function normalise(string $membershipCode): string
    {
        return sha1(Str::lower(trim($membershipCode)));
    }
}
