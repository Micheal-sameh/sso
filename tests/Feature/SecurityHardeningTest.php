<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Stage;
use App\Models\User;
use App\Models\UserMfa;
use App\Services\MfaService;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The migrations use MySQL-only SQL, so these need a throwaway MySQL
     * database (never your real one — RefreshDatabase wipes it), e.g.
     * DB_CONNECTION=mysql DB_DATABASE=sso_test php artisan test.
     */
    protected function setUp(): void
    {
        if (getenv('DB_CONNECTION') !== 'mysql') {
            $this->markTestSkipped('Requires a throwaway MySQL database (set DB_CONNECTION=mysql, DB_DATABASE=<test db>).');
        }

        parent::setUp();
    }

    private function makeUser(array $attrs = []): User
    {
        $stage = Stage::first() ?? Stage::create(['name' => 'Adult', 'code' => 'adult']);

        return User::factory()->create(['stage_id' => $stage->id] + $attrs);
    }

    public function test_api_login_is_rate_limited_per_account(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/auth/login', ['membership_code' => 'NOPE', 'password' => 'wrongpass'])
                ->assertStatus(401);
        }

        $this->postJson('/api/auth/login', ['membership_code' => 'NOPE', 'password' => 'wrongpass'])
            ->assertStatus(429);
    }

    public function test_totp_code_cannot_be_replayed(): void
    {
        $service = app(MfaService::class);
        $user = $this->makeUser();
        $secret = $service->generateSecret();
        UserMfa::create(['user_id' => $user->id, 'secret' => $secret, 'is_enabled' => true, 'recovery_codes' => []]);

        $code = (new \PragmaRX\Google2FA\Google2FA)->getCurrentOtp($secret);
        $user = $user->fresh();

        $this->assertTrue($service->verify($user, $code));
        $this->assertFalse($service->verify($user, $code));
    }

    public function test_recovery_code_is_single_use(): void
    {
        $service = app(MfaService::class);
        $user = $this->makeUser();
        $codes = $service->generateRecoveryCodes();
        UserMfa::create(['user_id' => $user->id, 'secret' => $service->generateSecret(), 'is_enabled' => true, 'recovery_codes' => $codes]);

        $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/', $codes[0]);
        $this->assertTrue($service->verify($user->fresh(), $codes[0]));
        $this->assertFalse($service->verify($user->fresh(), $codes[0]));
    }

    public function test_password_reset_link_tokens_are_stored_hashed(): void
    {
        $user = $this->makeUser();
        $repo = app(\App\Repositories\Contracts\PasswordResetLinkTokenRepositoryContract::class);

        $record = $repo->create($user);

        $this->assertNotNull($repo->findValid($record->token));
        $this->assertDatabaseMissing('password_reset_link_tokens', ['token' => $record->token]);
        $this->assertDatabaseHas('password_reset_link_tokens', ['token' => hash('sha256', $record->token)]);
    }

    public function test_password_change_revokes_other_api_tokens(): void
    {
        $user = $this->makeUser();
        $this->actingAs($user);
        $this->artisan('passport:client', ['--personal' => true, '--name' => 'test', '--provider' => 'users']);
        $token = $user->createToken('mobile')->token;

        app(UserService::class)->updateOwnPassword($user, 'a-brand-new-password');

        $this->assertTrue((bool) $token->fresh()->revoked);
    }

    public function test_secretary_cannot_act_on_a_super_admin_account(): void
    {
        $secretary = $this->makeUser(['role' => UserRole::Secretary]);
        $superAdmin = $this->makeUser(['role' => UserRole::SuperAdmin]);
        $plain = $this->makeUser();

        Auth::login($secretary);
        $service = app(UserService::class);

        $service->assertCanManage($plain);
        $service->assertCanManage($secretary, allowSelf: true);

        $this->expectException(\App\Exceptions\Auth\UnauthorizedRoleChangeException::class);
        $service->assertCanManage($superAdmin);
    }

    protected function tearDown(): void
    {
        if ($this->app !== null) {
            Cache::flush();
        }

        parent::tearDown();
    }
}
