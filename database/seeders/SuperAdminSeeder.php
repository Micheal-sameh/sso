<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\UserRole;
use App\Models\Stage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = (string) env('ADMIN_PASSWORD', '');
        $membershipCode = (string) env('ADMIN_MEMBERSHIP_CODE', '');

        // No built-in fallbacks: a publicly known default admin login is a
        // takeover waiting to happen, so refuse to seed without real values.
        if ($membershipCode === '' || strlen($password) < 12) {
            $this->command?->warn('Skipping super admin: set ADMIN_MEMBERSHIP_CODE and an ADMIN_PASSWORD of at least 12 characters.');

            return;
        }

        $adultStage = Stage::where('code', 'adult')->firstOrFail();

        $user = User::create([
            'uuid' => Str::uuid()->toString(),
            'membership_code' => $membershipCode,
            'name' => env('ADMIN_NAME', 'admin'),
            'email' => env('ADMIN_EMAIL'),
            'password' => $password,
            'role' => UserRole::SuperAdmin,
            'stage_id' => $adultStage->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole(AdminRole::SuperAdmin->value);
    }
}
