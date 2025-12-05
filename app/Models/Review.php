<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';
    protected $primaryKey = 'review_id';

    protected $fillable = [
        'project_id',
        'reviewer_id',
        'content',
        'rating'
    ];

    /**
     * The Project being reviewed.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    /**
     * The User who wrote the review.
     * Note: Access to this relationship is guarded by ReviewResource 
     * based on the 'reviews.view_names' permission.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id', 'user_id');
    }
}
