<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The table associated with the model.
     * Default is 'users', but defined explicitly for clarity.
     */
    protected $table = 'users';

    /**
     * The primary key associated with the table.
     * Mapped to your custom PostgreSQL SERIAL column.
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password_hash',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * We hide 'pivot' so API responses aren't cluttered with join table data.
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
        'pivot',
        'deleted_at'
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Overrides the default 'password' column name for Laravel Auth.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * PHP 8.5 / Laravel 12 Attribute for "Full Name".
     * Usage: $user->full_name
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * The Roles that belong to the user.
     * Pivot Table: user_roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
            ->withPivot('assigned_at');
    }

    /**
     * The Teams the user belongs to.
     * Pivot Table: team_members
     * Note: This defines "Who works where", regardless of if they are Mgr/Assoc.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id')
            ->withPivot('joined_at');
    }

    /**
     * The Reviews this user has written.
     * One-to-Many
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id', 'user_id');
    }

    /**
     * The Refresh Tokens for JWT Rotation.
     * One-to-Many
     */
    public function refreshTokens(): HasMany
    {
        return $this->hasMany(RefreshToken::class, 'user_id', 'user_id');
    }

    /**
     * CONTEXTUAL ROLE: Projects where this user is an "Internal Advisor".
     * Pivot Table: project_advisors
     * This bypasses the Team requirement.
     */
    public function advisedProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_advisors', 'user_id', 'project_id')
            ->withPivot('assigned_at');
    }

    /**
     * Get all permissions flattened from all roles.
     * Helpful for Policy checks.
     */
    public function permissions()
    {
        return $this->roles->flatMap(function ($role) {
            return $role->permissions;
        })->unique('permission_name');
    }

    /**
     * Check if user is assigned to a specific project EITHER via Team OR as Advisor.
     * Useful for Middleware/Policy checks.
     */
    public function hasAccessToProject($projectId): bool
    {
        if ($this->hasRole('Executive')) {
            return true;
        }

        $isAdvisor = $this->advisedProjects()->where('projects.project_id', $projectId)->exists();
        if ($isAdvisor)
            return true;

        return $this->teams()
            ->whereHas('projects', function ($q) use ($projectId) {
                $q->where('projects.project_id', $projectId);
            })
            ->exists();
    }
}
