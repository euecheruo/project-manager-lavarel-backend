<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 * name="Projects",
 * description="Core project management endpoints."
 * )
 */
class ProjectController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/projects",
     * operationId="getProjects",
     * tags={"Projects"},
     * summary="List all projects",
     * description="Executives see all projects. Managers and Associates see only projects they are assigned to (via Team or Advisory).",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="page",
     * in="query",
     * description="Page number",
     * required=false,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="List of projects",
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ProjectResource"))
     * )
     * )
     */
    public function index(Request $request)
    {
        if (Gate::allows('projects.view_all')) {
            return ProjectResource::collection(
                Project::with(['creator', 'teams'])->latest()->paginate(15)
            );
        }

        $user = $request->user();

        $projects = Project::query()
            ->with('teams')
            ->whereHas('teams', function ($q) use ($user) {
                $q->whereIn('teams.team_id', $user->teams->pluck('team_id'));
            })
            ->orWhereHas('advisors', function ($q) use ($user) {
                $q->where('users.user_id', $user->user_id);
            })
            ->latest()
            ->paginate(15);

        return ProjectResource::collection($projects);
    }

    /**
     * @OA\Post(
     * path="/api/projects",
     * operationId="createProject",
     * tags={"Projects"},
     * summary="Create a new project",
     * description="Creates a new project record. Restricted to Executives.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name"},
     * @OA\Property(property="name", type="string", example="Q4 Marketing Refresh"),
     * @OA\Property(property="description", type="string", example="Overhaul of the main landing pages."),
     * @OA\Property(property="status", type="string", enum={"active", "hold", "completed"}, default="active")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Project created successfully",
     * @OA\JsonContent(ref="#/components/schemas/ProjectResource")
     * ),
     * @OA\Response(response=403, description="Forbidden. Executives only.")
     * )
     */
    public function store(StoreProjectRequest $request)
    {
        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
            'created_by' => $request->user()->user_id,
        ]);

        return new ProjectResource($project);
    }

    /**
     * @OA\Get(
     * path="/api/projects/{project}",
     * operationId="getProjectById",
     * tags={"Projects"},
     * summary="Get project details",
     * description="View details, including assigned teams and advisors. Users must have access rights.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="project",
     * in="path",
     * description="Project ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Project details",
     * @OA\JsonContent(ref="#/components/schemas/ProjectResource")
     * ),
     * @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        $project->load(['teams', 'advisors', 'creator']);

        return new ProjectResource($project);
    }

    /**
     * @OA\Put(
     * path="/api/projects/{project}",
     * operationId="updateProject",
     * tags={"Projects"},
     * summary="Update project details",
     * description="Update metadata or status. Executives only.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="project",
     * in="path",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string"),
     * @OA\Property(property="description", type="string"),
     * @OA\Property(property="status", type="string", enum={"active", "hold", "completed", "archived"})
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Project updated",
     * @OA\JsonContent(ref="#/components/schemas/ProjectResource")
     * )
     * )
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $project->update($request->validated());

        return new ProjectResource($project);
    }

    /**
     * @OA\Delete(
     * path="/api/projects/{project}",
     * operationId="deleteProject",
     * tags={"Projects"},
     * summary="Archive/Delete project",
     * description="Soft deletes the project. Executives only.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="project",
     * in="path",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Project archived",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Project archived")
     * )
     * )
     * )
     */
    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Project archived']);
    }
}
