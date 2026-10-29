<?php

namespace Tests\Feature;

use App\Mail\RegistrationEmailOtp;
use App\Models\Block;
use App\Models\User;
use Database\Seeders\BlockSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BlockRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_eight_blocks_per_year_major_and_semester(): void
    {
        $this->seed();

        foreach (range(1, 4) as $yearLevel) {
            $tracks = $yearLevel < 3 ? [null] : ['BA', 'NT'];

            foreach (range(1, 2) as $semester) {
                foreach ($tracks as $track) {
                    $this->assertSame(
                        8,
                        Block::query()
                            ->where('year_level', $yearLevel)
                            ->where('semester', $semester)
                            ->where('track', $track)
                            ->count()
                    );
                }
            }
        }

        $this->assertDatabaseCount('blocks', 96);

        $this->seed();

        $this->assertDatabaseCount('blocks', 96);
    }

    public function test_registration_lists_major_names_and_keeps_first_and_second_year_blocks_untracked(): void
    {
        $this->seed(BlockSeeder::class);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Business Analytics')
            ->assertSee('Network Technology')
            ->assertDontSee('name="student_id"', false)
            ->assertSee('Block 1')
            ->assertSee('Block 8')
            ->assertSee('Block 1 — Business Analytics')
            ->assertSee('First Semester')
            ->assertSee('Second Semester')
            ->assertSee('data-semester="1"', false)
            ->assertSee('data-semester="2"', false)
            ->assertSee('data-year="1"', false)
            ->assertSee('data-year="2"', false)
            ->assertSee("yearSelect.addEventListener('change'", false)
            ->assertSee("if (yr === '3' || yr === '4')", false)
            ->assertSee('semesterSelect.addEventListener', false)
            ->assertSee('const yearLevel = null;', false)
            ->assertSee("semesterSelect.dispatchEvent(new Event('change'))", false);
    }

    public function test_registration_saves_a_first_or_second_year_block_without_a_major(): void
    {
        $this->seed(BlockSeeder::class);
        $email = '26-00001@g.batstate-u.edu.ph';
        $block = Block::query()->where([
            'year_level' => 2,
            'semester' => 2,
            'track' => null,
        ])->firstOrFail();

        $this->completeRegistration([
            'name' => 'Student One',
            'email' => $email,
            'year_level' => 2,
            'track' => null,
            'semester' => 2,
            'block_id' => $block->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'block_id' => $block->id,
            'student_id' => null,
        ]);
    }

    public function test_registration_saves_a_third_or_fourth_year_block_with_its_major(): void
    {
        $this->seed(BlockSeeder::class);
        $verificationCount = 0;

        foreach ([3, 4] as $yearLevel) {
            foreach (['BA', 'NT'] as $major) {
                if ($verificationCount > 0) {
                    $this->travel(61)->seconds();
                }

                $block = Block::query()->where([
                    'year_level' => $yearLevel,
                    'semester' => 1,
                    'track' => $major,
                ])->firstOrFail();
                $email = sprintf('26-%05d@g.batstate-u.edu.ph', $yearLevel * 100 + ($major === 'BA' ? 1 : 2));
                $verificationCount++;

                $this->completeRegistration([
                    'name' => 'Student '.$email,
                    'email' => $email,
                    'year_level' => $yearLevel,
                    'track' => $major,
                    'semester' => 1,
                    'block_id' => $block->id,
                    'password' => 'password123',
                    'password_confirmation' => 'password123',
                ]);

                $this->assertDatabaseHas('users', [
                    'email' => $email,
                    'block_id' => $block->id,
                    'student_id' => null,
                ]);
            }
        }
    }

    public function test_registration_rejects_a_block_from_another_semester_or_major(): void
    {
        $this->seed(BlockSeeder::class);
        $email = '26-00003@g.batstate-u.edu.ph';
        $block = Block::query()->where([
            'year_level' => 3,
            'semester' => 1,
            'track' => 'BA',
        ])->firstOrFail();

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Student Three',
            'email' => $email,
            'year_level' => 3,
            'track' => 'NT',
            'semester' => 2,
            'block_id' => $block->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register'))
            ->assertSessionHasErrors('block_id');

        $this->assertDatabaseMissing('users', ['email' => $email]);
    }

    public function test_registration_restores_year_major_and_semester_after_block_validation_error(): void
    {
        $this->seed(BlockSeeder::class);
        $email = '26-00004@g.batstate-u.edu.ph';

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Student Four',
            'email' => $email,
            'year_level' => 4,
            'track' => 'NT',
            'semester' => 2,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register'))
            ->assertSessionHasErrors('block_id');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('value="4" selected', false)
            ->assertSee('id="selectedTrack" value="NT"', false)
            ->assertSee('value="2" selected>Second Semester', false)
            ->assertSee('const yearLevel = 4;', false);
    }

    public function test_admin_block_list_displays_full_major_names(): void
    {
        $this->seed(BlockSeeder::class);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('admin.blocks.index'))
            ->assertOk()
            ->assertSee('Business Analytics')
            ->assertSee('Network Technology');
    }

    public function test_admin_can_distinguish_same_section_across_majors(): void
    {
        $this->seed(BlockSeeder::class);
        $businessAnalyticsBlock = Block::query()
            ->where(['year_level' => 3, 'semester' => 1, 'track' => 'BA'])
            ->firstOrFail();
        $networkTechnologyBlock = Block::query()
            ->where(['year_level' => 3, 'semester' => 1, 'track' => 'NT'])
            ->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        User::factory()->create([
            'name' => 'Analytics Student',
            'role' => 'voter',
            'block_id' => $businessAnalyticsBlock->id,
        ]);
        User::factory()->create([
            'name' => 'Network Student',
            'role' => 'voter',
            'block_id' => $networkTechnologyBlock->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.index', ['block_id' => $businessAnalyticsBlock->id]))
            ->assertOk()
            ->assertSee('Analytics Student')
            ->assertDontSee('Network Student');

        $this->get(route('admin.elections.index'))
            ->assertOk()
            ->assertSee('BSIT 3101 — Year 3, Sem 1 — Business Analytics')
            ->assertSee('BSIT 3101 — Year 3, Sem 1 — Network Technology');
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function completeRegistration(array $details): void
    {
        Mail::fake();

        $this->post(route('register.store'), $details)
            ->assertRedirect(route('register'))
            ->assertSessionHas('otp_sent');

        $code = null;
        Mail::assertSent(RegistrationEmailOtp::class, function (RegistrationEmailOtp $mail) use ($details, &$code): bool {
            $code = $mail->code;

            return $mail->hasTo(strtolower($details['email']));
        });

        $this->assertIsString($code);
        $this->post(route('register.otp.verify'), ['otp' => $code])
            ->assertRedirect(route('login'));
    }
}
