<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Election;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_voter_cannot_access_admin_election_management(): void
    {
        $voter = User::factory()->create(['role' => 'voter', 'is_active' => true]);
        $this->actingAs($voter)->get('/admin/elections')->assertForbidden();
    }

    public function test_manager_dashboard_displays_the_election_status_changer(): void
    {
        $block = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $manager = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $election = Election::create(['block_id' => $block->id, 'title' => 'Block Election', 'starts_at' => now()->subDay(), 'ends_at' => now(), 'status' => 'open']);
        $election->statusLogs()->create([
            'from_status' => 'draft',
            'to_status' => 'open',
            'changed_by' => $manager->id,
            'changed_at' => now(),
        ]);

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($manager->name)
            ->assertSee($election->title);
    }

    public function test_voter_can_view_only_published_results_for_their_block(): void
    {
        $block = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $otherBlock = Block::create(['program' => 'BSIT', 'section' => 'B', 'year_level' => 1, 'semester' => 1]);
        $voter = User::factory()->create(['role' => 'voter', 'block_id' => $block->id, 'is_verified' => true, 'is_active' => true]);
        $publishedElection = Election::create(['block_id' => $block->id, 'title' => 'Published Election', 'starts_at' => now()->subDay(), 'ends_at' => now(), 'status' => 'published']);
        $closedElection = Election::create(['block_id' => $block->id, 'title' => 'Closed Election', 'starts_at' => now()->subDay(), 'ends_at' => now(), 'status' => 'closed']);
        $otherElection = Election::create(['block_id' => $otherBlock->id, 'title' => 'Other Election', 'starts_at' => now()->subDay(), 'ends_at' => now(), 'status' => 'published']);

        $this->actingAs($voter)->get(route('voter.results.show', $publishedElection))->assertOk();
        $this->actingAs($voter)->get(route('voter.results.show', $closedElection))->assertNotFound();
        $this->actingAs($voter)->get(route('voter.results.show', $otherElection))->assertForbidden();
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $voter->id,
            'election_id' => $publishedElection->id,
            'action' => 'results_viewed',
        ]);
    }

    public function test_verified_voters_from_different_blocks_can_access_all_cics_election(): void
    {
        $blockA = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $blockB = Block::create(['program' => 'BSIT', 'section' => 'B', 'year_level' => 2, 'semester' => 1]);
        $voterA = User::factory()->create(['role' => 'voter', 'block_id' => $blockA->id, 'is_verified' => true, 'is_active' => true]);
        $voterB = User::factory()->create(['role' => 'voter', 'block_id' => $blockB->id, 'is_verified' => true, 'is_active' => true]);
        $election = Election::create(['scope' => 'all_cics', 'block_id' => null, 'title' => 'CICS Student Council Election', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'status' => 'open']);

        $this->actingAs($voterA)
            ->get(route('voter.elections'))
            ->assertOk()
            ->assertSee($election->title)
            ->assertSeeText('College-wide · All CICS students')
            ->assertDontSeeText('BSIT — · Year 1');
        $this->actingAs($voterB)->get(route('voter.elections'))->assertOk()->assertSee($election->title);
    }

    public function test_all_cics_results_include_verified_active_voters_across_blocks(): void
    {
        $blockA = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $blockB = Block::create(['program' => 'BSIT', 'section' => 'B', 'year_level' => 2, 'semester' => 1]);
        User::factory()->create(['role' => 'voter', 'block_id' => $blockA->id, 'is_verified' => true, 'is_active' => true]);
        User::factory()->create(['role' => 'voter', 'block_id' => $blockB->id, 'is_verified' => true, 'is_active' => true]);
        User::factory()->create(['role' => 'voter', 'block_id' => $blockB->id, 'is_verified' => false, 'is_active' => true]);
        $manager = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $election = Election::create(['scope' => 'all_cics', 'block_id' => null, 'title' => 'CICS Student Council Election', 'starts_at' => now()->subDay(), 'ends_at' => now(), 'status' => 'closed']);

        $this->actingAs($manager)->get(route('admin.results.show', $election))->assertOk()->assertSee('Eligible voters')->assertSee('2');
    }

    public function test_voter_cannot_access_manager_results_or_audit_log(): void
    {
        $voter = User::factory()->create(['role' => 'voter', 'is_active' => true]);

        $this->actingAs($voter)->get(route('admin.results.index'))->assertForbidden();
        $this->actingAs($voter)->get(route('admin.audit-log.index'))->assertForbidden();
    }

    public function test_manager_closing_and_publishing_election_creates_audit_and_result_snapshot(): void
    {
        $block = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $manager = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $election = Election::create(['block_id' => $block->id, 'title' => 'Block Election', 'starts_at' => now()->subDay(), 'ends_at' => now(), 'status' => 'open']);

        $this->actingAs($manager)->post(route('admin.elections.transition', $election))->assertRedirect();
        $this->assertDatabaseHas('audit_logs', ['election_id' => $election->id, 'action' => 'election_closed']);

        $this->actingAs($manager)->post(route('admin.elections.transition', $election->fresh()))->assertRedirect();
        $this->assertDatabaseHas('audit_logs', ['election_id' => $election->id, 'action' => 'results_published']);
        $this->assertDatabaseCount('election_result_snapshots', 1);
    }
}
