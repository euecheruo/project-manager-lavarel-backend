<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'permissions';

    /**
     * The primary key associated with the table.
     * Mapped to 'permission_id' (SERIAL).
     */
    protected $primaryKey = 'permission_id';

    /**
     * Indicates if the model should be timestamped.
     * Set to false because the table schema does not have created_at/updated_at.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['permission_name'];

    /**
     * The attributes that should be hidden for serialization.
     * Hiding 'pivot' prevents the role_permissions join data from polluting API responses.
     */
    protected $hidden = ['pivot'];

    /**
     * The Roles that have this permission.
     * Pivot Table: role_permissions
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id');
    }
}
