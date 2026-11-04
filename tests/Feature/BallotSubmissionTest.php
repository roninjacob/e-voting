<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BallotSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_election_manager_can_upload_a_section_candidate_profile(): void
    {
        Storage::fake('public');
        $manager = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $block = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $election = Election::create([
            'block_id' => $block->id,
            'title' => 'Block Election',
            'starts_at' => now(),
            'ends_at' => now()->addDay(),
            'status' => 'draft',
        ]);
        $position = Position::create(['election_id' => $election->id, 'name' => 'President', 'max_votes' => 1]);

        $this->actingAs($manager)->post(route('admin.candidates.store', [$election, $position]), [
            'full_name' => 'Candidate One',
            'education' => 'BS Information Technology',
            'experience' => 'Class representative',
            'awards' => "Dean's List",
            'photo' => UploadedFile::fake()->create('candidate.jpg', 10, 'image/jpeg'),
        ])->assertRedirect();

        $candidate = Candidate::query()->firstOrFail();
        $this->assertDatabaseHas('candidates', [
            'id' => $candidate->id,
            'education' => 'BS Information Technology',
            'experience' => 'Class representative',
            'awards' => "Dean's List",
        ]);
        Storage::disk('public')->assertExists($candidate->photo_path);
    }

    public function test_verified_voter_can_submit_only_one_ballot_per_election(): void
    {
        Storage::fake('public');
        $block = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $voter = User::factory()->create(['role' => 'voter', 'block_id' => $block->id, 'is_verified' => true, 'is_active' => true]);
        $election = Election::create(['block_id' => $block->id, 'title' => 'Block Election', 'starts_at' => now()->subMinute(), 'ends_at' => now()->addDay(), 'status' => 'open']);
        $position = Position::create(['election_id' => $election->id, 'name' => 'President', 'max_votes' => 1, 'is_required' => true]);
        $photoPath = 'candidate-photos/candidate.jpg';
        Storage::disk('public')->put($photoPath, 'fake image');
        $candidate = Candidate::create([
            'position_id' => $position->id,
            'full_name' => 'Candidate One',
            'education' => 'BS Information Technology',
            'experience' => 'Class representative',
            'awards' => "Dean's List",
            'photo_path' => $photoPath,
        ]);
        $payload = ['selections' => [$position->id => [$candidate->id]]];

        $this->actingAs($voter)->get(route('voter.ballot.show', $election))
            ->assertOk()
            ->assertSee('BS Information Technology')
            ->assertSee('Class representative')
            ->assertSee("Dean's List")
            ->assertSee(asset('storage/'.$photoPath), false);

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

    public function test_voter_cannot_submit_a_ballot_outside_the_election_schedule(): void
    {
        $block = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $voter = User::factory()->create(['block_id' => $block->id, 'is_verified' => true, 'is_active' => true]);
        $election = Election::create(['block_id' => $block->id, 'title' => 'Scheduled Election', 'starts_at' => now()->addHour(), 'ends_at' => now()->addDay(), 'status' => 'open']);
        $position = Position::create(['election_id' => $election->id, 'name' => 'President', 'max_votes' => 1, 'is_required' => true]);
        $candidate = Candidate::create(['position_id' => $position->id, 'full_name' => 'Candidate One']);

        $this->actingAs($voter)
            ->post(route('voter.ballot.store', $election), ['selections' => [$position->id => [$candidate->id]]])
            ->assertNotFound();

        $this->assertDatabaseCount('vote_receipts', 0);
        $this->assertDatabaseCount('ballot_votes', 0);
    }

    public function test_ballot_rejects_positions_from_another_election_without_recording_votes(): void
    {
        $block = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $voter = User::factory()->create(['role' => 'voter', 'block_id' => $block->id, 'is_verified' => true, 'is_active' => true]);
        $election = Election::create(['title' => 'Election', 'block_id' => $block->id, 'status' => 'open', 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);
        $position = Position::create(['name' => 'President', 'election_id' => $election->id, 'max_votes' => 1]);
        $candidate = Candidate::create(['full_name' => 'Candidate', 'position_id' => $position->id]);
        $otherElection = Election::create(['title' => 'Other election', 'block_id' => $block->id, 'starts_at' => now(), 'ends_at' => now()->addDay()]);
        $otherPosition = Position::create(['name' => 'President', 'election_id' => $otherElection->id, 'max_votes' => 1]);
        $otherCandidate = Candidate::create(['full_name' => 'Other candidate', 'position_id' => $otherPosition->id]);

        $this->actingAs($voter)->post(route('voter.ballot.store', $election), ['selections' => [
            $position->id => [$candidate->id],
            $otherPosition->id => [$otherCandidate->id],
        ]])->assertStatus(422);

        $this->assertDatabaseCount('vote_receipts', 0);
        $this->assertDatabaseCount('ballot_votes', 0);
    }
}
