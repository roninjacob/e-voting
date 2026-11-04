<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\CicsScCandidate;
use App\Models\CicsScElection;
use App\Models\CicsScPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CicsScElectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_election_manager_can_configure_and_open_a_cics_sc_election(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($manager)->post(route('admin.cics-sc.elections.store'), [
            'title' => 'CICS Student Council Election',
            'description' => 'College-wide leadership election.',
            'starts_at' => now()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
        ])->assertRedirect();

        $election = CicsScElection::query()->firstOrFail();
        $this->actingAs($manager)->post(route('admin.cics-sc.positions.store', $election), [
            'name' => 'President',
            'max_votes' => 1,
            'sort_order' => 1,
        ])->assertRedirect();

        $position = $election->positions()->firstOrFail();
        $this->actingAs($manager)->post(route('admin.cics-sc.candidates.store', [$election, $position]), [
            'full_name' => 'Candidate One',
            'party' => 'Independent',
            'education' => 'BS Information Technology',
            'experience' => 'Student organization officer',
            'awards' => 'Leadership award',
            'photo' => UploadedFile::fake()->create('candidate.jpg', 10, 'image/jpeg'),
        ])->assertRedirect();

        $candidate = $position->candidates()->firstOrFail();
        $this->assertDatabaseHas('cics_sc_candidates', [
            'id' => $candidate->id,
            'education' => 'BS Information Technology',
            'experience' => 'Student organization officer',
            'awards' => 'Leadership award',
        ]);
        Storage::disk('public')->assertExists($candidate->photo_path);

        $this->actingAs($manager)->post(route('admin.cics-sc.elections.transition', $election))->assertRedirect();
        $this->actingAs($manager)->post(route('admin.cics-sc.elections.transition', $election))->assertRedirect();

        $this->assertDatabaseHas('cics_sc_elections', ['id' => $election->id, 'status' => 'open']);
        $this->assertDatabaseCount('cics_sc_election_status_logs', 2);
    }

    public function test_verified_cics_student_can_submit_only_one_cics_sc_ballot(): void
    {
        Storage::fake('public');
        $block = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $voter = User::factory()->create(['role' => 'voter', 'block_id' => $block->id, 'is_verified' => true, 'is_active' => true]);
        $election = CicsScElection::create(['title' => 'CICS Student Council Election', 'starts_at' => now(), 'ends_at' => now()->addDay(), 'status' => 'open']);
        $position = CicsScPosition::create(['cics_sc_election_id' => $election->id, 'name' => 'President', 'max_votes' => 1, 'is_required' => true]);
        $photoPath = 'cics-sc-candidate-photos/candidate.jpg';
        Storage::disk('public')->put($photoPath, 'fake image');
        $candidate = CicsScCandidate::create([
            'cics_sc_position_id' => $position->id,
            'full_name' => 'Candidate One',
            'education' => 'BS Information Technology',
            'experience' => 'Student organization officer',
            'awards' => 'Leadership award',
            'photo_path' => $photoPath,
        ]);
        $payload = ['selections' => [$position->id => [$candidate->id]]];

        $this->actingAs($voter)->get(route('cics-sc.elections'))
            ->assertOk()
            ->assertSee('CICS-SC Voting')
            ->assertSee('Candidate Selection')
            ->assertSee('Eligibility of CICS Students');

        $this->get(route('cics-sc.ballot.show', $election))
            ->assertOk()
            ->assertSee('BS Information Technology')
            ->assertSee('Student organization officer')
            ->assertSee('Leadership award')
            ->assertSee(asset('storage/'.$photoPath), false);

        $this->actingAs($voter)->post(route('cics-sc.ballot.store', $election), $payload)->assertRedirect(route('cics-sc.elections'));

        $this->assertDatabaseCount('cics_sc_vote_receipts', 1);
        $this->assertDatabaseCount('vote_receipts', 0);
        $this->assertDatabaseMissing('cics_sc_vote_receipts', ['payload_hash' => (string) $candidate->id]);

        $this->actingAs($voter)->post(route('cics-sc.ballot.store', $election), $payload)->assertStatus(422);
        $this->assertDatabaseCount('cics_sc_vote_receipts', 1);
    }

    public function test_non_cics_student_cannot_access_a_cics_sc_ballot(): void
    {
        $block = Block::create(['program' => 'BSCpE', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $voter = User::factory()->create(['role' => 'voter', 'block_id' => $block->id, 'is_verified' => true, 'is_active' => true]);
        $election = CicsScElection::create(['title' => 'CICS Student Council Election', 'starts_at' => now(), 'ends_at' => now()->addDay(), 'status' => 'open']);

        $this->actingAs($voter)->get(route('cics-sc.elections'))
            ->assertOk()
            ->assertSee('CICS-SC voting is unavailable for this account.');

        $this->get(route('cics-sc.ballot.show', $election))->assertNotFound();
    }

    public function test_only_admin_can_upload_cics_sc_candidates(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'adviser', 'is_active' => true]);
        $election = CicsScElection::create(['title' => 'CICS Student Council Election', 'starts_at' => now(), 'ends_at' => now()->addDay()]);
        $position = CicsScPosition::create(['cics_sc_election_id' => $election->id, 'name' => 'President', 'max_votes' => 1]);

        $this->actingAs($manager)->post(route('admin.cics-sc.candidates.store', [$election, $position]), [
            'full_name' => 'Candidate One',
            'education' => 'BS Information Technology',
            'experience' => 'Student organization officer',
            'awards' => 'Leadership award',
            'photo' => UploadedFile::fake()->create('candidate.jpg', 10, 'image/jpeg'),
        ])->assertForbidden();

        $this->assertDatabaseCount('cics_sc_candidates', 0);
    }
}
