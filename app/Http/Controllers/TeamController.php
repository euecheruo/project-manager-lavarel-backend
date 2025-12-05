<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Http\Resources\TeamResource;
use App\Http\Requests\Teams\StoreTeamRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TeamController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Team::class);
        return TeamResource::collection(Team::withCount('members')->get());
    }

    public function store(StoreTeamRequest $request)
    {
        $team = Team::create(['name' => $request->name]);
        return new TeamResource($team);
    }

    public function show(Team $team)
    {
        $this->authorize('view', $team);
        return new TeamResource($team->load('members.roles'));
    }
}
