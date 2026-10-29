<?php

namespace Database\Seeders;

use App\Models\Block;
use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, 4) as $yearLevel) {
            $tracks = $yearLevel < 3 ? [null] : ['BA', 'NT'];

            foreach (range(1, 2) as $semester) {
                foreach ($tracks as $track) {
                    foreach (range(1, 8) as $blockNumber) {
                        Block::firstOrCreate([
                            'program' => 'BSIT',
                            'section' => sprintf('%d%d%02d', $yearLevel, $semester, $blockNumber),
                            'year_level' => $yearLevel,
                            'semester' => $semester,
                            'track' => $track,
                        ]);
                    }
                }
            }
        }
    }
}
