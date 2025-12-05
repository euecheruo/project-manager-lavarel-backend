<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $exec = Role::where('role_name', 'Executive')->first();
        $manager = Role::where('role_name', 'Manager')->first();
        $associate = Role::where('role_name', 'Associate')->first();

        $allPermissions = Permission::all();
        $exec->permissions()->sync($allPermissions);

        $standardPermissionNames = [
            'teams.view_any',
            'reviews.create',
            'reviews.update_own',
            'reviews.delete_own'
        ];

        $standardPermissions = Permission::whereIn('permission_name', $standardPermissionNames)->get();

        $manager->permissions()->sync($standardPermissions);
        $associate->permissions()->sync($standardPermissions);
    }
}
