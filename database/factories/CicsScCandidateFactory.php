<?php

namespace Database\Factories;

use App\Models\CicsScCandidate;
use App\Models\CicsScPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CicsScCandidate>
 */
class CicsScCandidateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cics_sc_position_id' => CicsScPosition::factory(),
            'full_name' => fake()->name(),
            'party' => fake()->optional()->company(),
            'platform' => fake()->optional()->paragraph(),
            'is_active' => true,
        ];
    }
}
