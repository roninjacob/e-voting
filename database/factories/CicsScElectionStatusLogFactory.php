<?php

namespace Database\Factories;

use App\Models\CicsScElection;
use App\Models\CicsScElectionStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CicsScElectionStatusLog>
 */
class CicsScElectionStatusLogFactory extends Factory
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
            'from_status' => 'draft',
            'to_status' => 'configured',
            'changed_by' => User::factory(),
            'changed_at' => now(),
        ];
    }
}
