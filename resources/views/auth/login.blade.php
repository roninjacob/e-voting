@extends('layouts.portal')

@section('content')
<main class="portal-main">
    @include('components.login_intro')
    <section class="login-card portal-card" aria-labelledby="login-heading">

        <!-- Tab Toggle -->
        <nav class="portal-tabs" aria-label="Choose your account type">
            <a href="{{ route('login') }}" class="active" aria-current="page">
                <i class="bi bi-person-fill me-1"></i>Student
            </a>
            <a href="{{ route('admin.login') }}">
                <i class="bi bi-shield-lock me-1"></i>Admin
            </a>
        </nav>

        <!-- Logo / Heading -->
        <div class="portal-card-heading">
            <div class="portal-account-icon" aria-hidden="true"><i class="bi bi-person"></i></div>
            <span class="portal-card-kicker">STUDENT ACCESS</span>
            <h2 id="login-heading">Your voice starts here.</h2>
            <p>Sign in with your BatStateU GSuite account.</p>
        </div>

        @if ($errors->any())
        <div class="portal-error" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
        </div>
        @endif

        <!-- Quick Fill Credentials Box -->
        <div style="background: rgba(212, 175, 55, 0.08); border: 1px dashed var(--gold); border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.85rem; color: var(--text-main);">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span><strong><i class="bi bi-info-circle text-warning me-1"></i> Demo Student Account</strong></span>
                <button type="button" onclick="fillStudentCreds()" class="btn btn-sm" style="background: var(--gold); color: #111; font-weight: 600; font-size: 0.75rem; padding: 2px 8px; border-radius: 4px; border: none;">Auto-Fill</button>
            </div>
            <div style="color: var(--text-muted); font-size: 0.8rem; line-height: 1.4;">
                Email: <code style="color: var(--gold);">23-01041@g.batstate-u.edu.ph</code><br>
                Password: <code style="color: var(--gold);">password123</code>
            </div>
        </div>

        <script>
        function fillStudentCreds() {
            document.getElementById('emailField').value = '23-01041@g.batstate-u.edu.ph';
            document.getElementById('pwdField').value = 'password123';
        }
        </script>

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label-dark" for="emailField">GSuite email</label>
                <div style="position:relative;">
                    <i class="bi bi-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                    <input type="email" name="email" id="emailField" class="form-control-dark" autocomplete="username"
                           style="padding-left:36px;"
                           value="{{ old('email') }}"
                           placeholder="23-01041@g.batstate-u.edu.ph"
                           required autofocus>
                </div>
                <div style="font-size:.7rem;color:var(--text-muted);margin-top:4px;">
                    Format: <code style="color:var(--gold);">YY-NNNNN@g.batstate-u.edu.ph</code>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label-dark" for="pwdField">Password</label>
                <div style="position:relative;">
                    <i class="bi bi-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                    <input type="password" name="password" id="pwdField" class="form-control-dark" autocomplete="current-password"
                           style="padding-left:36px;padding-right:40px;"
                           placeholder="Enter your password" required>
                    <button type="button" onclick="togglePwd(this)" aria-label="Show password" aria-pressed="false"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="text-end mb-4">
                <a href="#" class="portal-link">
                    Forgot Password?
                </a>
            </div>

            <button type="submit" class="portal-submit">
                Sign In <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </button>
        </form>

        <div class="portal-card-note">
            <span>New to the community? </span>
            <a href="{{ route('register') }}" class="portal-link">
                Create Account
            </a>
        </div>

    </section>
</main>
@include('components.portal_footer')
@endsection
