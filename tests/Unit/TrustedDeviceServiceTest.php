<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\TrustedDeviceService;
use Tests\TestCase;

class TrustedDeviceServiceTest extends TestCase
{
    private function user(int $id): User
    {
        $user = new User;
        $user->id = $id;

        return $user;
    }

    public function test_token_is_only_valid_for_the_account_it_was_issued_to(): void
    {
        $service = new TrustedDeviceService;
        $token = $service->issue($this->user(1));

        $this->assertNotNull($service->deviceId($token, $this->user(1)));
        $this->assertNull($service->deviceId($token, $this->user(2)));
    }

    public function test_garbage_or_missing_tokens_are_rejected(): void
    {
        $service = new TrustedDeviceService;

        $this->assertNull($service->deviceId(null, $this->user(1)));
        $this->assertNull($service->deviceId('', $this->user(1)));
        $this->assertNull($service->deviceId('not-a-real-token', $this->user(1)));
    }

    public function test_a_valid_existing_token_is_reused(): void
    {
        $service = new TrustedDeviceService;
        $token = $service->issue($this->user(1));

        $this->assertSame($token, $service->issue($this->user(1), $token));
        $this->assertNotSame($token, $service->issue($this->user(2), $token));
    }
}
