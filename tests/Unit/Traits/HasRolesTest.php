<?php

namespace Tests\Unit\Traits;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HasRolesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Scenario A: Relations are NOT loaded.
     * The trait should execute a database query to find the permission.
     */
    public function test_has_permission_via_database_query()
    {
        // 1. Setup: Create User, Role, and Permission
        $user = User::factory()->create();
        $role = Role::create(['role_name' => 'Editor']);
        $permission = Permission::create(['permission_name' => 'articles.edit']);

        // 2. Link them: Permission -> Role -> User
        $role->permissions()->attach($permission->permission_id);
        $user->roles()->attach($role->role_id);

        // 3. Action: Check permission without loading relationships
        // This hits the "Scenario B" logic in HasRoles.php
        $this->assertTrue($user->hasPermission('articles.edit'));
        
        // 4. Verify negative case
        $this->assertFalse($user->hasPermission('articles.delete'));
    }

    /**
     * Test Scenario B: Relations ARE eager loaded.
     * The trait should check the collection in memory without running new queries.
     */
    public function test_has_permission_via_eager_loaded_relation()
    {
        // 1. Setup
        $user = User::factory()->create();
        $role = Role::create(['role_name' => 'Viewer']);
        $permission = Permission::create(['permission_name' => 'reports.view']);

        $role->permissions()->attach($permission->permission_id);
        $user->roles()->attach($role->role_id);

        // 2. Action: Eager load the nested relationships
        // This prepares the user for "Scenario A" logic in HasRoles.php
        $user->load('roles.permissions');

        // 3. Enable Query Logging to prove no DB calls happen
        \Illuminate\Support\Facades\DB::enableQueryLog();

        // 4. Assertion
        $this->assertTrue($user->hasPermission('reports.view'));
        
        // 5. Verify no queries were executed during the check
        $this->assertEmpty(\Illuminate\Support\Facades\DB::getQueryLog());
    }

    /**
     * Test Duplicate Permissions across multiple roles.
     * If a user has two roles that both grant the same permission, it should still pass.
     */
    public function test_has_permission_via_multiple_roles()
    {
        $user = User::factory()->create();
        
        $roleA = Role::create(['role_name' => 'Role A']);
        $roleB = Role::create(['role_name' => 'Role B']);
        $permission = Permission::create(['permission_name' => 'shared.access']);

        // Assign permission to BOTH roles
        $roleA->permissions()->attach($permission->permission_id);
        $roleB->permissions()->attach($permission->permission_id);

        // Assign BOTH roles to user
        $user->roles()->attach([$roleA->role_id, $roleB->role_id]);

        $this->assertTrue($user->hasPermission('shared.access'));
    }
}
