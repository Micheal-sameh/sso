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
        $adultStage = Stage::where('code', 'adult')->firstOrFail();

        $user = User::create([
            'uuid' => Str::uuid()->toString(),
            'membership_code' => env('ADMIN_MEMBERSHIP_CODE', 'e1c1f1nr1'),
            'name' => env('ADMIN_NAME', 'admin'),
            'email' => env('ADMIN_EMAIL'),
            'password' => env('ADMIN_PASSWORD', 'Misho$1234'),
            'role' => UserRole::SuperAdmin,
            'stage_id' => $adultStage->id,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole(AdminRole::SuperAdmin->value);
    }
}
