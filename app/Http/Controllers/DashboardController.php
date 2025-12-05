<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\User;
use App\Models\Review;

class DashboardController extends Controller
{
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
