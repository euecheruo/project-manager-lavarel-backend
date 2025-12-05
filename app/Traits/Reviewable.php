<?php

namespace App\Traits;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Reviewable
{
    /**
     * Get all reviews for this entity.
     * Assumes the database table has a 'project_id' column.
     * * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'project_id', 'project_id');
    }

    /**
     * Accessor: Calculate the average rating dynamically.
     * Usage: $project->average_rating
     * * @return float
     */
    public function getAverageRatingAttribute(): float
    {
        if ($this->relationLoaded('reviews')) {
            $avg = $this->reviews->avg('rating');
            return $avg ? round((float) $avg, 1) : 0.0;
        }

        $val = $this->reviews()->avg('rating');

        return $val ? round((float) $val, 1) : 0.0;
    }

    /**
     * Accessor: Get the total count of reviews.
     * Usage: $project->review_count
     * * @return int
     */
    public function getReviewCountAttribute(): int
    {
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->count();
        }

        return $this->reviews()->count();
    }

    /**
     * Scope: Filter query to only include items with a minimum rating.
     * Usage: Project::minRating(4)->get();
     * * @param $query
     * @param int $minRating
     * @return void
     */
    public function scopeMinRating($query, int $minRating)
    {
        $query->whereHas('reviews', function ($q) use ($minRating) {
            $q->selectRaw('avg(rating) as aggregate')
                ->groupBy('project_id')
                ->havingRaw('avg(rating) >= ?', [$minRating]);
        });
    }
}
