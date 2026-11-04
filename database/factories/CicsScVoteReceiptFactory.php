<?php

namespace Database\Factories;

use App\Models\Block;
use App\Models\CicsScElection;
use App\Models\CicsScVoteReceipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CicsScVoteReceipt>
 */
class CicsScVoteReceiptFactory extends Factory
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
            'voter_id' => User::factory(),
            'block_id' => Block::factory(),
            'receipt_reference' => (string) Str::uuid(),
            'payload_hash' => hash('sha256', fake()->uuid()),
            'status' => 'confirmed',
            'submitted_at' => now(),
        ];
    }
}
