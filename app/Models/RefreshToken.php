<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'refresh_tokens';

    /**
     * The primary key associated with the table.
     * Mapped to 'token_id' (SERIAL).
     */
    protected $primaryKey = 'token_id';

    /**
     * Timestamp Configuration.
     * The schema has 'created_at' but NO 'updated_at'.
     * We must disable the UPDATED_AT constant so Laravel doesn't try to write to a column that doesn't exist.
     */
    public $timestamps = true;
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'token_hash',
        'is_revoked',
        'expires_at'
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'is_revoked' => 'boolean',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * The user who owns this token.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Check if the token is valid (not revoked and not expired).
     * Usage: if ($token->isValid()) { ... }
     */
    public function isValid(): bool
    {
        return !$this->is_revoked && $this->expires_at->isFuture();
    }

    /**
     * Revoke the token immediately.
     */
    public function revoke(): bool
    {
        $this->is_revoked = true;
        return $this->save();
    }
}
