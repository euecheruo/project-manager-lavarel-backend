<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Team;

class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Team',
            'created_at' => fake()->dateTimeBetween('-2 years', '-6 months'),
            'updated_at' => now(),
        ];
    }
}
