<?php

namespace Tests\Feature\Projects;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_can_view_all_projects()
    {
        $exec = $this->createExecutive();
        Project::factory()->count(3)->create(); // Create 3 unassigned projects

        $this->authenticateAs($exec)
            ->getJson('/api/projects')
            ->assertStatus(200)
            ->assertJsonCount(3, 'data'); // Should see all 3
    }

    public function test_manager_cannot_view_unassigned_projects()
    {
        $manager = $this->createManager();
        Project::factory()->create(); // Unassigned project

        $this->authenticateAs($manager)
            ->getJson('/api/projects')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data'); // Should see 0
    }

    public function test_internal_advisor_can_view_project()
    {
        // 1. Setup
        $manager = $this->createManager();
        $project = Project::factory()->create();

        // 2. Assign as Advisor (Directly in DB for test setup)
        $project->advisors()->attach($manager->user_id);

        // 3. Test Access
        $this->authenticateAs($manager)
            ->getJson('/api/projects')
            ->assertStatus(200)
            ->assertJsonFragment(['id' => $project->project_id]);
    }
}
