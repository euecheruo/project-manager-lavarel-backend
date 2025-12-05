<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\Reviewable;

class Project extends Model
{
    use HasFactory, SoftDeletes, Reviewable;

    protected $table = 'projects';
    protected $primaryKey = 'project_id';

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by'
    ];

    protected $hidden = ['pivot', 'deleted_at'];

    /**
     * The Executive who created the project.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    /**
     * Teams assigned to work on this project.
     * Pivot: project_teams
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'project_teams', 'project_id', 'team_id')
            ->withPivot('assigned_at');
    }

    /**
     * Internal Advisors (Contextual Role).
     * Users who have access to THIS project specifically, bypassing Team restrictions.
     * Pivot: project_advisors
     */
    public function advisors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_advisors', 'project_id', 'user_id')
            ->withPivot('assigned_at');
    }
}
