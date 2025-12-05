<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    /**
     * List projects (Filtered by Access).
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
     * Create Project (Exec Only).
     * Auth check is handled in StoreProjectRequest::authorize()
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
     * View Single Project.
     */
    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        $project->load(['teams', 'advisors', 'creator']);

        return new ProjectResource($project);
    }

    /**
     * Update Project (Exec Only).
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $project->update($request->validated());

        return new ProjectResource($project);
    }

    /**
     * Archive/Delete Project (Exec Only).
     */
    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $project->delete(); // Soft Delete

        return response()->json(['message' => 'Project archived']);
    }
}
