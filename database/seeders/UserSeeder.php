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
            'membership_code' => 'e1c1f1nr1',
            'password' => Hash::make('password'),
            'name' => 'John Doe',
        ]);
    }
}
