<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'CICS E-Voting' }}</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body class="login-page login-page--portal">
    @include('components.login_brand')
    @yield('content')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePwd(button) {
        const f = document.getElementById('pwdField');
        const i = document.getElementById('eyeIcon');
        if (!f || !i) return;
        if (f.type === 'password') { f.type = 'text';     i.className = 'bi bi-eye-slash'; }
        else                       { f.type = 'password'; i.className = 'bi bi-eye'; }
        button.setAttribute('aria-label', f.type === 'text' ? 'Hide password' : 'Show password');
        button.setAttribute('aria-pressed', f.type === 'text' ? 'true' : 'false');
    }
    </script>
    @stack('scripts')
</body>
</html>
