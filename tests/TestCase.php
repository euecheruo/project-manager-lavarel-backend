<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Services\AuthService;
use Database\Seeders\DatabaseSeeder;

abstract class TestCase extends BaseTestCase
{
    /**
     * Run before every single test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // FIX: Run the Master Seeder.
        // This populates roles, permissions, AND the pivot table connecting them.
        // Without this, an Executive is just a user with a label but no power.
        $this->seed(DatabaseSeeder::class);
    }

    /**
     * Helper to authenticate a user via JWT for API testing.
     */
    protected function authenticateAs(User $user): self
    {
        $authService = app(AuthService::class);
        $tokens = $authService->generateTokens($user);
        
        // Inject the token into the headers for subsequent requests in this test chain
        $this->withHeader('Authorization', 'Bearer ' . $tokens['access_token']);
        
        return $this;
    }

    /**
     * Create an Executive user with ALL permissions explicitly synced.
     * Prevents "403 Unauthorized" if seeders fail to link permissions.
     */
    protected function createExecutive(): User
    {
        $user = User::factory()->create();

        // 1. Find or Create the Executive Role
       $role = Role::firstOrCreate(['role_name' => 'Executive']);

        // 2. EXPLICITLY Sync Permissions
        // We fetch all permissions because Executives have access to everything.
        $allPermissions = Permission::all();
        
        // Defensive Coding: If the Permission table is empty (seeder failed), create critical ones manually.
        if ($allPermissions->isEmpty()) {
             $viewAll = Permission::create(['permission_name' => 'projects.view_all']);
             $viewNames = Permission::create(['permission_name' => 'reviews.view_names']);
             $deleteAny = Permission::create(['permission_name' => 'reviews.delete_any']);
             $allPermissions = collect([$viewAll, $viewNames, $deleteAny]);
        }

        $role->permissions()->sync($allPermissions);

        // 3. Attach Role to User
        // Use syncWithoutDetaching to avoid duplicates
        $user->roles()->syncWithoutDetaching([$role->role_id]);

        return $user;
    }

    /**
     * Create a Manager user with standard permissions explicitly synced.
     */
    protected function createManager(): User
    {
        $user = User::factory()->create();
        
        // 1. Find or Create the Manager Role
        $role = Role::firstOrCreate(['role_name' => 'Manager']);

        // 2. Define Manager-specific permissions
        $managerPermissionNames = [
            'teams.view_any',
            'reviews.create',
            'reviews.update_own',
            'reviews.delete_own'
        ];

        // 3. Fetch just those IDs
        $managerPermissions = Permission::whereIn('permission_name', $managerPermissionNames)->get();

        // Defensive Coding: Create them if missing
        if ($managerPermissions->isEmpty()) {
            foreach ($managerPermissionNames as $name) {
                Permission::firstOrCreate(['permission_name' => $name]);
            }
            $managerPermissions = Permission::whereIn('permission_name', $managerPermissionNames)->get();
        }

        // 4. Sync Permissions to Role
        $role->permissions()->sync($managerPermissions);

        // 5. Attach Role to User
        $user->roles()->syncWithoutDetaching([$role->role_id]);

        return $user;
    }
}
