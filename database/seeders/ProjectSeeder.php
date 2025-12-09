<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::create([
            'name' => 'rashi',
            'secret_key' => bin2hex(random_bytes(64)),
        ]);

        Project::create([
            'name' => 'visits',
            'secret_key' => bin2hex(random_bytes(64)),
        ]);

        Project::create([
            'name' => 'eftkad',
            'secret_key' => bin2hex(random_bytes(64)),
        ]);
    }
}
