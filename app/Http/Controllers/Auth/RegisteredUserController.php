<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterVoterRequest;
use App\Mail\RegistrationEmailOtp;
use App\Models\Block;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use RuntimeException;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register', ['blocks' => Block::query()->orderBy('year_level')->orderBy('section')->get()]);
    }

    public function sendEmailOtp(Request $request): RedirectResponse
    {
        $registration = $request->session()->get('registration_pending');

        if (! is_array($registration) || ! is_string($registration['email'] ?? null)) {
            return redirect()->route('register')->withErrors(['email' => 'Enter your student details before requesting a verification code.']);
        }

        $email = $registration['email'];
        $this->issueEmailOtp($email);
        $request->session()->put('registration_otp_email', $email);

        return redirect()->route('register')->with('otp_sent', 'A new 6-digit verification code was sent to your email. It expires in 10 minutes.');
    }

    public function verifyEmailOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);
        $email = $request->session()->get('registration_otp_email');
        $registration = $request->session()->get('registration_pending');

        if (! is_string($email) || ! is_array($registration) || ($registration['email'] ?? null) !== $email) {
            return redirect()->route('register')->withErrors(['otp' => 'Submit your student details to request a verification code first.']);
        }

        $cacheKey = $this->emailOtpCacheKey($email);
        $hashedCode = Cache::get($cacheKey);

        if (! is_string($hashedCode) || ! Hash::check($validated['otp'], $hashedCode)) {
            return redirect()->route('register')->withErrors(['otp' => 'The verification code is invalid or expired. Request a new code and try again.']);
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->resetPendingRegistration($request);

            return redirect()->route('register')->withErrors(['email' => 'This email is already registered.']);
        }

        User::create([
            'name' => $registration['name'],
            'email' => $email,
            'block_id' => $registration['block_id'],
            'password' => $registration['password'],
            'role' => 'voter',
            'is_verified' => false,
            'email_verified_at' => now(),
        ]);
        Cache::forget($cacheKey);
        $request->session()->forget(['registration_pending', 'registration_otp_email']);

        return redirect()->route('login')->with('status', 'Registration submitted. Wait for an administrator to verify your account.');
    }

    public function resetEmailVerification(Request $request): RedirectResponse
    {
        $this->resetPendingRegistration($request);

        return redirect()->route('register');
    }

    public function store(RegisterVoterRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $email = mb_strtolower($validated['email']);
        $previousRegistration = $request->session()->get('registration_pending');

        if (is_array($previousRegistration) && is_string($previousRegistration['email'] ?? null)) {
            Cache::forget($this->emailOtpCacheKey($previousRegistration['email']));
        }

        $request->session()->forget(['registration_pending', 'registration_otp_email']);
        $this->issueEmailOtp($email);
        $request->session()->put('registration_pending', [
            'name' => $validated['name'],
            'email' => $email,
            'block_id' => $validated['block_id'],
            'password' => Hash::make($validated['password']),
        ]);
        $request->session()->put('registration_otp_email', $email);

        return redirect()->route('register')->with('otp_sent', 'A 6-digit verification code was sent to your email. It expires in 10 minutes.');
    }

    private function issueEmailOtp(string $email): void
    {
        $code = (string) random_int(100000, 999999);
        $otpStored = Cache::put(
            $this->emailOtpCacheKey($email),
            Hash::make($code),
            now()->addMinutes(10)
        );

        if (! $otpStored) {
            throw new RuntimeException('Unable to store the registration email verification code.');
        }

        Mail::mailer('evoting')
            ->to($email)
            ->send(new RegistrationEmailOtp($code));
    }

    private function resetPendingRegistration(Request $request): void
    {
        $email = $request->session()->get('registration_otp_email');

        if (is_string($email)) {
            Cache::forget($this->emailOtpCacheKey($email));
        }

        $request->session()->forget(['registration_pending', 'registration_otp_email']);
    }

    private function emailOtpCacheKey(string $email): string
    {
        return 'registration-email-otp:'.hash('sha256', $email);
    }
}
