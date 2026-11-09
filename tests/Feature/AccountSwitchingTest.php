<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Stage;
use App\Models\User;
use App\Services\AccountSwitcher;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountSwitchingTest extends TestCase
{
    use RefreshDatabase;

    // Migrations use MySQL-only SQL: run against a throwaway MySQL database,
    // e.g. DB_CONNECTION=mysql DB_DATABASE=sso_test php artisan test.
    protected function setUp(): void
    {
        if (getenv('DB_CONNECTION') !== 'mysql') {
            $this->markTestSkipped('Requires a throwaway MySQL database.');
        }

        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function makeUser(array $attrs = []): User
    {
        $stage = Stage::first() ?? Stage::create(['name' => 'Adult', 'code' => 'adult']);

        return User::factory()->create($attrs + ['stage_id' => $stage->id, 'role' => UserRole::User]);
    }

    public function test_can_switch_between_remembered_accounts(): void
    {
        $a = $this->makeUser();
        $b = $this->makeUser();
        $switcher = app(AccountSwitcher::class);
        $switcher->remember($a);
        $switcher->remember($b);

        $this->actingAs($a->fresh())
            ->withSession(['remembered_accounts' => [
                $a->id => hash('sha256', $a->id.'|'.$a->password.'|user'),
                $b->id => hash('sha256', $b->id.'|'.$b->password.'|user'),
            ]])
            ->post('/accounts/switch', ['user_id' => $b->id])
            ->assertRedirect();
        $this->assertAuthenticatedAs($b);
    }

    public function test_cannot_switch_to_an_account_that_never_signed_in(): void
    {
        $a = $this->makeUser();
        $stranger = $this->makeUser();

        $this->actingAs($a->fresh())->post('/accounts/switch', ['user_id' => $stranger->id]);
        $this->assertAuthenticatedAs($a);
    }

    public function test_password_change_invalidates_remembered_account(): void
    {
        $a = $this->makeUser();
        $b = $this->makeUser();
        $switcher = app(AccountSwitcher::class);
        $switcher->remember($b);
        $b->update(['password' => 'a-brand-new-password']);

        $this->assertFalse($switcher->switchTo($b->id));
        $this->assertSame([], $switcher->others($a->id));
    }

    public function test_admins_can_be_remembered_and_switched_to(): void
    {
        $admin = $this->makeUser(['role' => UserRole::Admin]);
        $user = $this->makeUser();
        $switcher = app(AccountSwitcher::class);
        $switcher->remember($admin);
        $switcher->remember($user);

        $others = $switcher->others($user->id);
        $this->assertCount(1, $others);
        $this->assertSame($admin->id, $others[0]->id);
        $this->assertTrue($switcher->switchTo($admin->id));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_role_change_invalidates_remembered_account(): void
    {
        $admin = $this->makeUser(['role' => UserRole::Admin]);
        $other = $this->makeUser();
        $switcher = app(AccountSwitcher::class);
        $switcher->remember($admin);
        $admin->update(['role' => UserRole::User]);

        $this->assertSame([], $switcher->others($other->id));
        $this->assertFalse($switcher->switchTo($admin->id));
    }

    public function test_deactivated_account_is_dropped(): void
    {
        $a = $this->makeUser();
        $b = $this->makeUser();
        $switcher = app(AccountSwitcher::class);
        $switcher->remember($b);
        $b->update(['is_active' => false]);

        $this->assertSame([], $switcher->others($a->id));
    }

    public function test_list_is_capped_at_five_accounts(): void
    {
        $switcher = app(AccountSwitcher::class);
        $users = [];
        for ($i = 0; $i < 7; $i++) {
            $users[] = $this->makeUser();
            $switcher->remember($users[$i]);
        }

        $others = $switcher->others(null);
        $this->assertCount(5, $others);
        $ids = array_map(fn ($u) => $u->id, $others);
        $this->assertNotContains($users[0]->id, $ids);
        $this->assertContains($users[6]->id, $ids);
    }

    public function test_add_account_ignores_foreign_redirects(): void
    {
        $a = $this->makeUser();

        $this->actingAs($a->fresh())->post('/accounts/add', ['intended' => 'https://evil.example/x'])
            ->assertRedirect(route('login'));
        $this->assertNull(session('url.intended'));
    }
}
