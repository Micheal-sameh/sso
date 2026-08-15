<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Primary', 'code' => 'pri', 'sort_order' => 1],
            ['name' => 'Preparatory', 'code' => 'prep', 'sort_order' => 2],
            ['name' => 'Secondary', 'code' => 'sec', 'sort_order' => 3],
            ['name' => 'Youth', 'code' => 'youth', 'sort_order' => 4],
            ['name' => 'Adult', 'code' => 'adult', 'sort_order' => 5],
        ];

        foreach ($stages as $stage) {
            Stage::firstOrCreate(['code' => $stage['code']], $stage);
        }
    }
}
