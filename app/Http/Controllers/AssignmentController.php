<?php

namespace App\Http\Controllers;

use App\Services\AssignmentService;
use App\Models\Project;
use App\Http\Requests\Assignments\AssignTeamsRequest;
use App\Http\Requests\Assignments\AssignAdvisorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 * name="Assignments",
 * description="Executive-only endpoints for linking Teams and Advisors to Projects."
 * )
 */
class AssignmentController extends Controller
{
    public function __construct(protected AssignmentService $assignmentService)
    {
    }

    /**
     * @OA\Post(
     * path="/api/assignments/project-teams",
     * operationId="assignTeamsToProject",
     * tags={"Assignments"},
     * summary="Assign Teams to a Project",
     * description="Replaces the list of teams assigned to a project. Requires Executive role.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"project_id", "team_ids"},
     * @OA\Property(property="project_id", type="integer", example=15, description="The ID of the project to update"),
     * @OA\Property(
     * property="team_ids",
     * type="array",
     * description="Array of Team IDs to assign. Existing assignments not in this list will be removed.",
     * @OA\Items(type="integer", example=2)
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Assignments updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Teams assigned successfully")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden. Only Executives can assign teams."
     * ),
     * @OA\Response(
     * response=422,
     * description="Validation Error (e.g. Invalid Project ID or Team ID)"
     * )
     * )
     */
    public function assignTeams(AssignTeamsRequest $request)
    {
        $project = Project::findOrFail($request->project_id);

        $this->assignmentService->syncProjectTeams($project, $request->team_ids);

        return response()->json(['message' => 'Teams assigned successfully']);
    }

    /**
     * @OA\Post(
     * path="/api/assignments/advisors",
     * operationId="assignAdvisor",
     * tags={"Assignments"},
     * summary="Assign Internal Advisor",
     * description="Grants a user specific 'Contextual Access' to a project, bypassing team restrictions.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"project_id", "user_id"},
     * @OA\Property(property="project_id", type="integer", example=15),
     * @OA\Property(property="user_id", type="integer", example=102, description="The Manager/Associate to add as an advisor")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Advisor assigned successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Advisor assigned successfully")
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Business Rule Exception (e.g. User is inactive)"
     * )
     * )
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
     * @OA\Delete(
     * path="/api/assignments/advisors/{project}/{userId}",
     * operationId="removeAdvisor",
     * tags={"Assignments"},
     * summary="Remove Internal Advisor",
     * description="Revokes the contextual access for a specific user on a specific project.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="project",
     * in="path",
     * required=true,
     * description="Project ID",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Parameter(
     * name="userId",
     * in="path",
     * required=true,
     * description="User ID of the advisor to remove",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Advisor removed",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Advisor removed")
     * )
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden"
     * )
     * )
     */
    public function removeAdvisor(Request $request, Project $project, $userId)
    {
        Gate::authorize('projects.assign_advisors');

        $this->assignmentService->removeAdvisor($project, $userId);

        return response()->json(['message' => 'Advisor removed']);
    }
}
