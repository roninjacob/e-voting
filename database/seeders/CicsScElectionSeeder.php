<?php

namespace Database\Seeders;

use App\Models\CicsScElection;
use App\Models\User;
use Illuminate\Database\Seeder;

class CicsScElectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $election = CicsScElection::firstOrCreate(
            ['title' => 'CICS Student Council Election'],
            [
                'description' => 'Choose the student leaders who will represent the CICS community.',
                'starts_at' => now()->addWeek(),
                'ends_at' => now()->addWeek()->addDays(2),
                'created_by' => User::query()->whereIn('role', ['admin', 'adviser', 'committee'])->value('id'),
            ],
        );

        $president = $election->positions()->firstOrCreate(
            ['name' => 'President'],
            ['max_votes' => 1, 'is_required' => true, 'sort_order' => 1],
        );

        $president->candidates()->firstOrCreate(
            ['full_name' => 'Sample Candidate'],
            ['party' => 'Independent', 'platform' => 'Serving the CICS student community.'],
        );
    }
}
