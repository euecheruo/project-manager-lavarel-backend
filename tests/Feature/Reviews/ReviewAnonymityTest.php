<?php

namespace Tests\Feature\Reviews;

use App\Models\Project;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAnonymityTest extends TestCase
{
    use RefreshDatabase;

    public function test_executive_can_see_reviewer_name()
    {
        $exec = $this->createExecutive();
        $author = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $project = Project::factory()->create();

        Review::factory()->create([
            'project_id' => $project->project_id,
            'reviewer_id' => $author->user_id,
            'rating' => 5,
            'content' => 'Great work'
        ]);

        $response = $this->authenticateAs($exec)
            ->getJson("/api/projects/{$project->project_id}/reviews");

        $response->assertStatus(200)
            ->assertJsonPath('0.reviewer.name', 'John Doe');
    }
}
