<?php

namespace Tests\Unit;

use App\Exceptions\Auth\TooManyLoginAttemptsException;
use App\Services\LoginAttemptLimiter;
use Tests\TestCase;

class LoginAttemptLimiterTest extends TestCase
{
    private LoginAttemptLimiter $limiter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->limiter = new LoginAttemptLimiter;
    }

    private function failAttempts(int $times, string $code = 'AB-1'): void
    {
        for ($i = 0; $i < $times; $i++) {
            $this->limiter->recordFailure($code);
        }
    }

    private function assertLockedFor(int $seconds, string $code = 'AB-1'): void
    {
        try {
            $this->limiter->ensureNotLocked($code);
            $this->fail('Expected a lockout.');
        } catch (TooManyLoginAttemptsException $e) {
            $this->assertEqualsWithDelta($seconds, $e->retryAfter, 1);
            $this->assertSame(429, $e->getStatusCode());
        }
    }

    public function test_three_failures_do_not_lock(): void
    {
        $this->failAttempts(3);

        $this->limiter->ensureNotLocked('AB-1');
        $this->addToAssertionCount(1);
    }

    public function test_locks_30s_then_2min_then_5min(): void
    {
        $this->failAttempts(4);
        $this->assertLockedFor(30);

        $this->travel(31)->seconds();
        $this->limiter->ensureNotLocked('AB-1');
        $this->failAttempts(4); // 8 total: still in the 30s tier, not yet 5 more
        $this->limiter->ensureNotLocked('AB-1');

        $this->failAttempts(1); // 9 total -> 2 minutes
        $this->assertLockedFor(120);

        $this->travel(121)->seconds();
        $this->failAttempts(5); // 14 total -> 5 minutes
        $this->assertLockedFor(300);

        $this->travel(301)->seconds();
        $this->failAttempts(5); // 19 total -> stays at 5 minutes
        $this->assertLockedFor(300);
    }

    public function test_success_resets_the_counter(): void
    {
        $this->failAttempts(3);
        $this->limiter->clear('AB-1');
        $this->failAttempts(3);

        $this->limiter->ensureNotLocked('AB-1');
        $this->addToAssertionCount(1);

        $this->failAttempts(1); // only 4 since the reset
        $this->assertLockedFor(30);

        $this->limiter->clear('AB-1');
        $this->limiter->ensureNotLocked('AB-1');
    }

    public function test_codes_are_tracked_independently_and_case_insensitively(): void
    {
        $this->failAttempts(4, 'AB-1');
        $this->assertLockedFor(30, 'ab-1');

        $this->limiter->ensureNotLocked('OTHER');
        $this->addToAssertionCount(1);
    }
}
