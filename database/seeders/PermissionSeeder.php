<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.create',
            'users.view_any',
            'users.update',
            'users.delete',
            'teams.create',
            'teams.update',
            'teams.manage_roster',
            'teams.view_any',
            'projects.create',
            'projects.update',
            'projects.delete',
            'projects.view_all',
            'projects.assign_teams',
            'projects.assign_advisors',
            'reviews.create',
            'reviews.update_own',
            'reviews.delete_own',
            'reviews.delete_any',
            'reviews.view_names',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['permission_name' => $permission]);
        }
    }
}
