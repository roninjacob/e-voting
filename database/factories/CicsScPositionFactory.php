<?php

namespace Database\Factories;

use App\Models\CicsScElection;
use App\Models\CicsScPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CicsScPosition>
 */
class CicsScPositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cics_sc_election_id' => CicsScElection::factory(),
            'name' => fake()->randomElement(['President', 'Vice President', 'Secretary', 'Treasurer']),
            'max_votes' => 1,
            'is_required' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
