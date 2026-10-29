<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BallotSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_voter_can_submit_only_one_ballot_per_election(): void
    {
        $block = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $voter = User::factory()->create(['block_id' => $block->id, 'is_verified' => true, 'is_active' => true]);
        $election = Election::create(['block_id' => $block->id, 'title' => 'Block Election', 'starts_at' => now(), 'ends_at' => now()->addDay(), 'status' => 'open']);
        $position = Position::create(['election_id' => $election->id, 'name' => 'President', 'max_votes' => 1, 'is_required' => true]);
        $candidate = Candidate::create(['position_id' => $position->id, 'full_name' => 'Candidate One']);
        $payload = ['selections' => [$position->id => [$candidate->id]]];

        $this->actingAs($voter)->post(route('voter.ballot.store', $election), $payload)->assertRedirect(route('voter.elections'));
        $this->assertDatabaseCount('vote_receipts', 1);
        $this->assertDatabaseCount('ballot_votes', 1);
        $this->assertDatabaseHas('ballot_votes', [
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate->id,
        ]);
        $this->assertDatabaseMissing('vote_receipts', ['payload_hash' => (string) $candidate->id]);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $voter->id,
            'election_id' => $election->id,
            'action' => 'ballot_submitted',
        ]);
        $this->actingAs($voter)->post(route('voter.ballot.store', $election), $payload)->assertStatus(422);
        $this->assertDatabaseCount('vote_receipts', 1);
        $this->assertDatabaseCount('ballot_votes', 1);
    }
}
