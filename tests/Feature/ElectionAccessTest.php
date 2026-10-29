<?php

namespace Tests\Feature;

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
}
