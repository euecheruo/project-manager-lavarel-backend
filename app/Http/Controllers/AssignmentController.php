<?php

namespace App\Http\Controllers;

use App\Services\AssignmentService;
use App\Models\Project;
use App\Http\Requests\Assignments\AssignTeamsRequest;
use App\Http\Requests\Assignments\AssignAdvisorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AssignmentController extends Controller
{
    public function __construct(protected AssignmentService $assignmentService)
    {
    }

    /**
     * Assign Teams to Project.
     */
    public function assignTeams(AssignTeamsRequest $request)
    {
        $project = Project::findOrFail($request->project_id);

        $this->assignmentService->syncProjectTeams($project, $request->team_ids);

        return response()->json(['message' => 'Teams assigned successfully']);
    }

    /**
     * Add Internal Advisor to Project.
     */
    public function assignAdvisor(AssignAdvisorRequest $request)
    {
        $project = Project::findOrFail($request->project_id);

        try {
            $this->assignmentService->assignAdvisor($project, $request->user_id);
            return response()->json(['message' => 'Advisor assigned successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove Internal Advisor.
     */
    public function removeAdvisor(Request $request, Project $project, $userId)
    {
        Gate::authorize('projects.assign_advisors');

        $this->assignmentService->removeAdvisor($project, $userId);

        return response()->json(['message' => 'Advisor removed']);
    }
}
