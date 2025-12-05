<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Review;

/**
 * @OA\Tag(
 * name="Dashboard",
 * description="Central hub stats and metrics based on user role."
 * )
 */
class DashboardController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/dashboard",
     * operationId="getDashboardStats",
     * tags={"Dashboard"},
     * summary="Get Dashboard Metrics",
     * description="Returns a different set of statistics depending on whether the user is an Executive, Manager, or Associate.",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Role-specific dashboard data",
     * @OA\JsonContent(
     * oneOf={
     * @OA\Schema(
     * title="Executive Dashboard",
     * @OA\Property(property="role", type="string", example="Executive"),
     * @OA\Property(property="stats", type="object",
     * @OA\Property(property="active_projects", type="integer", example=12),
     * @OA\Property(property="total_employees", type="integer", example=45),
     * @OA\Property(property="recent_reviews", type="integer", example=8)
     * ),
     * @OA\Property(property="needs_attention", type="array", description="Projects with no teams assigned",
     * @OA\Items(
     * @OA\Property(property="project_id", type="integer", example=5),
     * @OA\Property(property="name", type="string", example="Q3 Marketing Campaign")
     * )
     * )
     * ),
     * @OA\Schema(
     * title="Manager Dashboard",
     * @OA\Property(property="role", type="string", example="Manager"),
     * @OA\Property(property="my_teams", type="array", @OA\Items(ref="#/components/schemas/TeamResource")),
     * @OA\Property(property="active_projects_count", type="integer", example=3),
     * @OA\Property(property="advisory_projects", type="array",
     * @OA\Items(
     * @OA\Property(property="project_id", type="integer", example=7),
     * @OA\Property(property="name", type="string", example="Legacy System Migration")
     * )
     * )
     * ),
     * @OA\Schema(
     * title="Associate Dashboard",
     * @OA\Property(property="role", type="string", example="Associate"),
     * @OA\Property(property="assigned_projects_count", type="integer", example=2),
     * @OA\Property(property="my_reviews_count", type="integer", example=15)
     * )
     * }
     * )
     * )
     * )
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('Executive')) {
            return response()->json([
                'role' => 'Executive',
                'stats' => [
                    'active_projects' => Project::where('status', 'active')->count(),
                    'total_employees' => User::count(),
                    'recent_reviews' => Review::where('created_at', '>=', now()->subDays(7))->count(),
                ],
                'needs_attention' => Project::whereDoesntHave('teams')->get(['project_id', 'name']),
            ]);
        }

        if ($user->hasRole('Manager')) {
            $myTeamIds = $user->teams->pluck('team_id');

            return response()->json([
                'role' => 'Manager',
                'my_teams' => $user->teams->loadCount('members'),
                'active_projects_count' => Project::whereHas('teams', fn($q) => $q->whereIn('teams.team_id', $myTeamIds))->count(),
                'advisory_projects' => $user->advisedProjects()->get(['projects.project_id', 'name']),
            ]);
        }

        return response()->json([
            'role' => 'Associate',
            'assigned_projects_count' => Project::whereHas(
                'teams',
                fn($q) =>
                $q->whereHas('members', fn($u) => $u->where('users.user_id', $user->user_id))
            )->count(),
            'my_reviews_count' => $user->reviews()->count(),
        ]);
    }
}
