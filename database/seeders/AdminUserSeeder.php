<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@company.com'],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'password_hash' => Hash::make('password123'),
                'is_active' => true,
            ]
        );

        $execRole = Role::where('role_name', 'Executive')->first();

        $admin->roles()->syncWithoutDetaching([$execRole->role_id]);
    }
}
