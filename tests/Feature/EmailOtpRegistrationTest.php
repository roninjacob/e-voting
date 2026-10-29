<?php

namespace Tests\Feature;

use App\Mail\RegistrationEmailOtp;
use App\Models\Block;
use App\Models\User;
use Database\Seeders\BlockSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailOtpRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_details_are_requested_before_email_verification(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee(route('register.store'))
            ->assertSee('id="regForm"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertDontSee('id="otpField"', false);

        $this->seed(BlockSeeder::class);
        Mail::fake();

        $this->post(route('register.store'), [
            'name' => 'Student Missing Block',
            'email' => 'unverified@g.batstate-u.edu.ph',
            'year_level' => 1,
            'semester' => 1,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('block_id');

        $this->assertDatabaseMissing('users', ['email' => 'unverified@g.batstate-u.edu.ph']);
        Mail::assertNothingSent();
    }

    public function test_otp_is_emailed_and_account_is_created_only_after_successful_verification(): void
    {
        $this->seed(BlockSeeder::class);
        Mail::fake();
        $email = 'student@g.batstate-u.edu.ph';
        $block = Block::query()->where([
            'year_level' => 2,
            'semester' => 2,
            'track' => null,
        ])->firstOrFail();

        $this->post(route('register.store'), [
            'name' => 'Verified Student',
            'email' => $email,
            'year_level' => 2,
            'semester' => 2,
            'block_id' => $block->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertRedirect(route('register'))
            ->assertSessionHas('otp_sent');

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('id="otpField"', false)
            ->assertSee($email)
            ->assertDontSee('id="regForm"', false);

        $this->assertDatabaseMissing('users', ['email' => $email]);
        $code = null;
        Mail::assertSent(RegistrationEmailOtp::class, function (RegistrationEmailOtp $mail) use ($email, &$code): bool {
            $code = $mail->code;
            $mail->assertSeeInHtml($code);

            return $mail->hasTo($email);
        });
        $this->assertIsString($code);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);

        $this->post(route('register.otp.verify'), ['otp' => $code === '000000' ? '000001' : '000000'])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('otp');
        $this->assertDatabaseMissing('users', ['email' => $email]);

        $this->post(route('register.otp.verify'), ['otp' => $code])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->post(route('register.otp.verify'), ['otp' => $code])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('otp');

        $user = User::query()->where('email', $email)->firstOrFail();
        $this->assertSame($block->id, $user->block_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse($user->is_verified);
        $this->assertGuest();
    }

    public function test_expired_otp_cannot_be_used_to_create_an_account(): void
    {
        Mail::fake();
        $email = 'expired@g.batstate-u.edu.ph';
        $this->seed(BlockSeeder::class);
        $block = Block::query()->where([
            'year_level' => 1,
            'semester' => 1,
            'track' => null,
        ])->firstOrFail();

        $this->post(route('register.store'), [
            'name' => 'Expired Student',
            'email' => $email,
            'year_level' => 1,
            'semester' => 1,
            'block_id' => $block->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register'));

        $cacheKey = 'registration-email-otp:'.hash('sha256', $email);
        Cache::forget($cacheKey);

        $this->post(route('register.otp.verify'), ['otp' => '123456'])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('otp');

        $this->assertDatabaseMissing('users', ['email' => $email]);
    }

    public function test_registered_email_cannot_start_registration_or_request_an_otp(): void
    {
        Mail::fake();
        $this->seed(BlockSeeder::class);
        User::factory()->create(['email' => 'existing@g.batstate-u.edu.ph']);
        $block = Block::query()->where([
            'year_level' => 1,
            'semester' => 1,
            'track' => null,
        ])->firstOrFail();

        $this->post(route('register.store'), [
            'name' => 'Existing Student',
            'email' => 'existing@g.batstate-u.edu.ph',
            'year_level' => 1,
            'semester' => 1,
            'block_id' => $block->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }
}
