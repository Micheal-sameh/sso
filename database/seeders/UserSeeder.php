<?php

namespace Database\Seeders;

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
        User::create([
            'membership_code' => 'E1C1F1NR1',
            'password' => Hash::make('password'),
            'name' => 'Super Admin',
        ]);
    }
}
