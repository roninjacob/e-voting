<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(BlockSeeder::class);
        $block1 = Block::query()->where([
            'program' => 'BSIT',
            'section' => '1101',
            'year_level' => 1,
            'semester' => 1,
            'track' => null,
        ])->firstOrFail();

        User::firstOrCreate(
            ['email' => 'admin@cics.batstate-u.edu.ph'],
            [
                'name' => 'System Administrator',
                'student_id' => 'ADMIN001',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_verified' => true,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'adviser@cics.batstate-u.edu.ph'],
            [
                'name' => 'Maria Santos',
                'student_id' => 'ADV001',
                'password' => Hash::make('password123'),
                'role' => 'adviser',
                'block_id' => $block1->id,
                'is_verified' => true,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => '23-01041@g.batstate-u.edu.ph'],
            [
                'name' => 'Juan dela Cruz',
                'student_id' => '23-01041',
                'password' => Hash::make('password123'),
                'role' => 'voter',
                'block_id' => $block1->id,
                'is_verified' => true,
                'is_active' => true,
            ]
        );
    }
}
