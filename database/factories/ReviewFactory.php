<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Review;
use App\Models\Project;
use App\Models\User;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'reviewer_id' => User::factory(),
            'content' => fake()->paragraph(rand(2, 5)),
            'rating' => fake()->numberBetween(1, 5),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'updated_at' => now(),
        ];
    }

    /**
     * State: A generic 5-star review.
     */
    public function fiveStar(): static
    {
        return $this->state(fn(array $attributes) => [
            'rating' => 5,
            'content' => 'Exceptional work, exceeded all expectations.',
        ]);
    }

    /**
     * State: A generic critical review.
     */
    public function critical(): static
    {
        return $this->state(fn(array $attributes) => [
            'rating' => 1,
            'content' => 'Project failed to meet core requirements.',
        ]);
    }
}
