<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory;

    protected $table = 'teams';
    protected $primaryKey = 'team_id';

    protected $fillable = ['name'];

    protected $hidden = ['pivot', 'created_at', 'updated_at'];

    /**
     * All users in the team (Managers AND Associates).
     * Pivot: team_members
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members', 'team_id', 'user_id')
            ->withPivot('joined_at');
    }

    /**
     * Projects assigned to this team.
     * Pivot: project_teams
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_teams', 'team_id', 'project_id')
            ->withPivot('assigned_at');
    }

    /**
     * dynamically retrieve the Manager(s) of this team.
     * Logic: Get Members WHERE user also has 'Manager' Role.
     */
    public function managers()
    {
        return $this->members()->whereHas('roles', function ($query) {
            $query->where('role_name', Role::MANAGER);
        });
    }

    /**
     * Dynamically retrieve the Associates of this team.
     */
    public function associates()
    {
        return $this->members()->whereHas('roles', function ($query) {
            $query->where('role_name', Role::ASSOCIATE);
        });
    }
}
