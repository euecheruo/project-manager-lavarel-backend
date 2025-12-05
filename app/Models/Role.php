<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'roles';

    /**
     * The primary key associated with the table.
     * Mapped to 'role_id' (SERIAL).
     */
    protected $primaryKey = 'role_id';

    /**
     * Indicates if the model should be timestamped.
     * Set to false because our migration did not include $table->timestamps().
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['role_name'];

    /**
     * The attributes that should be hidden for serialization.
     * We hide 'pivot' to keep JSON responses clean.
     */
    protected $hidden = ['pivot'];

    /**
     * The Users that have this role.
     * Pivot Table: user_roles
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')
            ->withPivot('assigned_at');
    }

    /**
     * The Permissions associated with this role.
     * Pivot Table: role_permissions
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Constants
    |--------------------------------------------------------------------------
    | Useful for avoiding magic strings in your code. 
    | Usage: Role::EXECUTIVE
    */
    public const EXECUTIVE = 'Executive';
    public const MANAGER = 'Manager';
    public const ASSOCIATE = 'Associate';
}
