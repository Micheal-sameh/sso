<?php

namespace Tests\Unit;

use App\Http\Requests\OAuthClientRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ClientConfigSecurityTest extends TestCase
{
    public function test_client_redirect_uri_rejects_dangerous_values(): void
    {
        $rules = ['redirect' => (new OAuthClientRequest)->rules()['redirect']];

        foreach (['javascript://x%0aalert(1)', 'http://evil.example/cb', 'https://ok.example/cb#frag'] as $bad) {
            $this->assertTrue(Validator::make(['redirect' => $bad], $rules)->fails(), $bad);
        }

        foreach (['https://ok.example/cb', 'com.avarewase.passport://callback', 'http://localhost:3000/cb'] as $good) {
            $this->assertFalse(Validator::make(['redirect' => $good], $rules)->fails(), $good);
        }
    }

    public function test_implicit_grant_is_disabled_by_default(): void
    {
        $this->assertFalse(config('passport.implicit_grant'));
    }
}
