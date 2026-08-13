<?php

namespace Database\Seeders;

use App\Enums\AdminPermission;
use App\Enums\AdminRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $manageSso = Permission::firstOrCreate(['name' => AdminPermission::ManageSso->value, 'guard_name' => 'web']);

        Role::firstOrCreate(['name' => AdminRole::SuperAdmin->value, 'guard_name' => 'web'])
            ->givePermissionTo($manageSso);
    }
}
