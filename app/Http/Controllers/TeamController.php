<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Http\Resources\TeamResource;
use App\Http\Requests\Teams\StoreTeamRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Tag(
 * name="Teams",
 * description="Team management and roster assignments."
 * )
 */
class TeamController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     * path="/api/teams",
     * operationId="getTeams",
     * tags={"Teams"},
     * summary="List all teams",
     * description="Retrieve a directory of all teams with member counts.",
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="List of teams",
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TeamResource"))
     * )
     * )
     */
    public function index()
    {
        $this->authorize('viewAny', Team::class);
        // Returns list with member counts
        return TeamResource::collection(Team::withCount('members')->get());
    }

    /**
     * @OA\Post(
     * path="/api/teams",
     * operationId="createTeam",
     * tags={"Teams"},
     * summary="Create a new team",
     * description="Executive only.",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name"},
     * @OA\Property(property="name", type="string", example="Frontend Alpha", description="Unique team name")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Team created",
     * @OA\JsonContent(ref="#/components/schemas/TeamResource")
     * ),
     * @OA\Response(response=422, description="Validation Error (Name taken)")
     * )
     */
    public function store(StoreTeamRequest $request)
    {
        // Auth check is handled inside StoreTeamRequest class
        $team = Team::create(['name' => $request->name]);
        return new TeamResource($team);
    }

    /**
     * @OA\Get(
     * path="/api/teams/{team}",
     * operationId="getTeamById",
     * tags={"Teams"},
     * summary="Get team details",
     * description="View team roster. Useful for Associates to find their manager.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="team",
     * in="path",
     * description="Team ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Team details with roster",
     * @OA\JsonContent(ref="#/components/schemas/TeamResource")
     * )
     * )
     */
    public function show(Team $team)
    {
        $this->authorize('view', $team);
        // Eager load members and their roles for the roster view
        return new TeamResource($team->load('members.roles'));
    }

    /**
     * @OA\Post(
     * path="/api/teams/{team}/members",
     * operationId="addTeamMember",
     * tags={"Teams"},
     * summary="Add user to team",
     * description="Assigns a user to the team roster. Executive only.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="team",
     * in="path",
     * description="Team ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"user_id"},
     * @OA\Property(property="user_id", type="integer", example=101, description="User ID to add")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="User added successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="User assigned to team successfully"),
     * @OA\Property(property="team", ref="#/components/schemas/TeamResource")
     * )
     * ),
     * @OA\Response(response=403, description="Forbidden. Executives only.")
     * )
     */
    public function addMember(Request $request, Team $team)
    {
        // 1. Authorization: Check 'teams.manage_roster' permission via TeamPolicy
        $this->authorize('manageRoster', $team);

        // 2. Validation
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,user_id'],
        ]);

        // 3. Action: Attach user to team (syncWithoutDetaching prevents duplicates)
        $team->members()->syncWithoutDetaching([$request->user_id]);

        // Return the updated team data
        return response()->json([
            'message' => 'User assigned to team successfully',
            'team' => new TeamResource($team->load('members'))
        ]);
    }

    /**
     * @OA\Delete(
     * path="/api/teams/{team}/members/{userId}",
     * operationId="removeTeamMember",
     * tags={"Teams"},
     * summary="Remove user from team",
     * description="Removes a user from the team roster. Executive only.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="team",
     * in="path",
     * description="Team ID",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Parameter(
     * name="userId",
     * in="path",
     * description="User ID to remove",
     * required=true,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="User removed",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="User removed from team")
     * )
     * )
     * )
     */
    public function removeMember(Team $team, $userId)
    {
        // 1. Authorization
        $this->authorize('manageRoster', $team);

        // 2. Action: Detach user
        $team->members()->detach($userId);

        return response()->json(['message' => 'User removed from team']);
    }
}
