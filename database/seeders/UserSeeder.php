<?php

namespace Database\Seeders;

use App\Models\SSOUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'membership_code' => 'E1C1F1NR1',
            'password' => Hash::make('password'),
            'name' => 'Super Admin',
        ]);

        SSOUser::create([
            'user_id' => $user->id,
            'created_by' => $user->id,
        ]);
    }
}
