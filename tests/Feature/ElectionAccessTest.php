<?php

namespace Tests\Feature;

use App\Models\Block;
use App\Models\Election;
use App\Models\ElectionStatusLog;
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

    public function test_election_manager_dashboard_loads_the_status_log_changer(): void
    {
        $block = Block::create(['program' => 'BSIT', 'section' => 'A', 'year_level' => 1, 'semester' => 1]);
        $manager = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $election = Election::create(['block_id' => $block->id, 'title' => 'Block Election', 'starts_at' => now(), 'ends_at' => now()->addDay(), 'status' => 'configured']);
        ElectionStatusLog::create(['election_id' => $election->id, 'from_status' => 'draft', 'to_status' => 'configured', 'changed_by' => $manager->id]);

        $this->actingAs($manager)->get('/dashboard')
            ->assertOk()
            ->assertSee($manager->name);
    }
}
