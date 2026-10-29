<?php

namespace Database\Factories;

use App\Models\BallotVote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BallotVote>
 */
class BallotVoteFactory extends Factory
{
    protected $model = BallotVote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'election_id' => 1,
            'position_id' => 1,
            'candidate_id' => 1,
        ];
    }
}
