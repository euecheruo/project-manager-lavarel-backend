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
        // 1. Setup
        $exec = $this->createExecutive();
        $author = User::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        $project = Project::factory()->create();

        // Create a review
        Review::factory()->create([
            'project_id' => $project->project_id,
            'reviewer_id' => $author->user_id,
            'rating' => 5,
            'content' => 'Great work'
        ]);

        // 2. Action: Exec requests reviews
        // Ensure we use the correct URL structure
        $response = $this->authenticateAs($exec)
            ->getJson("/api/projects/{$project->project_id}/reviews");

        // 3. Assert: 200 OK + Name Visible
        $response->assertStatus(200)
            ->assertJsonPath('0.reviewer.name', 'John Doe');
        // Note: '0.reviewer.name' assumes response is [ {reviewer: {name: ...}} ]
        // If your resource returns wrapped data, might be 'data.0.reviewer.name'
    }
}
