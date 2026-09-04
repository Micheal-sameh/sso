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
        $accessAdminPanel = Permission::firstOrCreate(['name' => AdminPermission::AccessAdminPanel->value, 'guard_name' => 'web']);
        $manageAdminAccess = Permission::firstOrCreate(['name' => AdminPermission::ManageAdminAccess->value, 'guard_name' => 'web']);

        Role::firstOrCreate(['name' => AdminRole::SuperAdmin->value, 'guard_name' => 'web'])
            ->syncPermissions([$accessAdminPanel, $manageAdminAccess]);

        Role::firstOrCreate(['name' => AdminRole::Admin->value, 'guard_name' => 'web'])
            ->syncPermissions([$accessAdminPanel]);

        Role::firstOrCreate(['name' => AdminRole::Secretary->value, 'guard_name' => 'web'])
            ->syncPermissions([$accessAdminPanel]);
    }
}
