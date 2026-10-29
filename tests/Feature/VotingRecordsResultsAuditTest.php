<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BallotVote;
use App\Models\Block;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use App\Models\VoteReceipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VotingRecordsResultsAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_voter_record_lists_only_the_authenticated_voters_receipts_without_choices(): void
    {
        $block = $this->createBlock();
        $voter = User::factory()->create(['role' => 'voter', 'block_id' => $block->id, 'is_verified' => true, 'is_active' => true]);
        $otherVoter = User::factory()->create(['role' => 'voter', 'block_id' => $block->id, 'is_verified' => true, 'is_active' => true]);
        $election = $this->createElection($block, 'published');
        $ownReceipt = $this->createReceipt($election, $voter, 'own-receipt');
        $this->createReceipt($election, $otherVoter, 'other-receipt');

        $this->actingAs($voter)
            ->get(route('voter.records'))
            ->assertOk()
            ->assertSee($ownReceipt->receipt_reference)
            ->assertDontSee('other-receipt')
            ->assertSee('<h1>My Voting Record</h1>', false)
            ->assertSee('Swipe or scroll sideways to see all receipt details.')
            ->assertSee('candidate choices are never shown');
    }

    public function test_published_results_show_aggregate_counts_and_export_csv(): void
    {
        $block = $this->createBlock();
        $voter = User::factory()->create(['role' => 'voter', 'block_id' => $block->id, 'is_verified' => true, 'is_active' => true]);
        $election = $this->createElection($block, 'published');
        $position = Position::create(['election_id' => $election->id, 'name' => 'President', 'max_votes' => 1, 'is_required' => true]);
        $candidate = Candidate::create(['position_id' => $position->id, 'full_name' => 'Candidate One']);
        $this->createReceipt($election, $voter, 'aggregate-receipt');
        BallotVote::create([
            'id' => (string) Str::uuid(),
            'election_id' => $election->id,
            'position_id' => $position->id,
            'candidate_id' => $candidate->id,
        ]);

        $this->actingAs($voter)
            ->get(route('voter.results.show', $election))
            ->assertOk()
            ->assertSee('Candidate One')
            ->assertSeeText('1 confirmed ballot')
            ->assertSee('aggregate totals only');

        $this->actingAs($voter)
            ->get(route('voter.results.export', $election))
            ->assertOk()
            ->assertDownload('election-'.$election->id.'-results.csv')
            ->assertStreamedContent("position,candidate,votes\nPresident,\"Candidate One\",1\n");

        $this->assertDatabaseHas('audit_logs', ['actor_id' => $voter->id, 'action' => 'results_exported']);
    }

    public function test_audit_log_is_restricted_to_managers_and_can_filter_by_election(): void
    {
        $block = $this->createBlock();
        $manager = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $election = $this->createElection($block, 'closed');
        $otherElection = $this->createElection($block, 'closed', 'Other Election');
        AuditLog::create(['actor_id' => $manager->id, 'actor_role' => 'admin', 'election_id' => $election->id, 'action' => 'results_viewed', 'metadata' => ['view_type' => 'manager'], 'ip_address' => '127.0.0.1']);
        AuditLog::create(['actor_id' => $manager->id, 'actor_role' => 'admin', 'election_id' => $otherElection->id, 'action' => 'results_exported', 'metadata' => ['format' => 'csv'], 'ip_address' => '127.0.0.1']);

        $this->actingAs($manager)
            ->get(route('admin.audit-log.index', ['election_id' => $election->id]))
            ->assertOk()
            ->assertSee('Results Viewed')
            ->assertDontSee('Results Exported')
            ->assertDontSee('candidate_id');
    }

    private function createBlock(): Block
    {
        return Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
    }

    private function createElection(Block $block, string $status, string $title = 'Block Election'): Election
    {
        return Election::create([
            'block_id' => $block->id,
            'title' => $title,
            'starts_at' => now()->subDay(),
            'ends_at' => now(),
            'status' => $status,
        ]);
    }

    private function createReceipt(Election $election, User $voter, string $reference): VoteReceipt
    {
        return VoteReceipt::create([
            'election_id' => $election->id,
            'voter_id' => $voter->id,
            'block_id' => $voter->block_id,
            'receipt_reference' => $reference,
            'payload_hash' => hash('sha256', $reference),
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
    }
}
