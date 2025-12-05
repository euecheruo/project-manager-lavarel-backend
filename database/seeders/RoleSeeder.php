<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Role::firstOrCreate(['role_name' => 'Executive']);

        Role::firstOrCreate(['role_name' => 'Manager']);

        Role::firstOrCreate(['role_name' => 'Associate']);
    }
}
