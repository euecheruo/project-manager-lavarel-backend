<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;
use App\Models\User;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => fake()->catchPhrase(),
            'description' => fake()->paragraph(3),
            'status' => fake()->randomElement(['active', 'active', 'active', 'completed', 'hold']),
            'created_by' => User::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', '-1 month'),
            'updated_at' => now(),
        ];
    }

    /**
     * State: Mark project as archived.
     */
    public function archived(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
