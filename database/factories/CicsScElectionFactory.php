<?php

namespace Database\Factories;

use App\Models\CicsScElection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CicsScElection>
 */
class CicsScElectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'status' => 'draft',
            'created_by' => User::factory(),
        ];
    }
}
