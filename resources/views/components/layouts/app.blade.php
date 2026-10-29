<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'CICS E-Voting' }} — CICS E-Voting</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    @stack('styles')
</head>
<body class="app-page app-dashboard">
<a class="app-skip-link" href="#main-content">Skip to content</a>

<!-- ── Top Banner ── -->
<header class="top-banner app-banner">
    <div class="app-brand">
      <div class="logos-group">
        <img src="{{ asset('assets/img/batstate-logo-main.png') }}" class="logo" alt="BatStateU" onerror="this.style.display='none'">
        <img src="{{ asset('assets/img/cics-logo.png') }}" class="logo" alt="CICS" onerror="this.style.display='none'">
      </div>
      <div class="app-brand-title"><strong>CICS E-Voting</strong><span>Batangas State University &middot; ARASOF Campus</span></div>
    </div>
    <div class="app-header-actions">
        @auth
        <span class="app-role-pill">
            <span aria-hidden="true"></span>
            @if(auth()->user()->isElectionManager())
                {{ ucfirst(auth()->user()->role) }} workspace
            @else
                Student workspace
            @endif
        </span>
        <img src="{{ asset('assets/img/student-council-logo.png') }}" class="logo" alt="CICS Student Council">
        <button type="button" class="app-nav-toggle" id="navToggle" aria-controls="appSidebar" aria-expanded="false" aria-label="Open navigation"><i class="bi bi-list" aria-hidden="true"></i></button>
        @endauth
    </div>
</header>

<!-- ── App Layout ── -->
<div class="app-layout">

    @auth
    <!-- ── Sidebar ── -->
    <aside class="sidebar" id="appSidebar" aria-label="Workspace navigation">
        <div class="sidebar-user">
            <div class="cics-badge">
                <span class="app-user-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <div>
                    <div class="welcome-name">{{ auth()->user()->name }}</div>
                    @if(auth()->user()->isElectionManager())
                        <div class="student-id">{{ ucfirst(auth()->user()->role) }} Account</div>
                    @else
                        <div class="student-id">Student ID: {{ auth()->user()->student_id ?: explode('@', auth()->user()->email)[0] }}</div>
                    @endif
                </div>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Main navigation">
            @if(auth()->user()->isElectionManager())
            <div class="nav-section">Election management</div>
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Election Overview
            </a>
            <a href="{{ route('admin.elections.index') }}" class="{{ request()->routeIs('admin.elections.*') ? 'active' : '' }}">
                <i class="bi bi-list-check"></i> Manage Elections
            </a>
            <a href="{{ route('admin.blocks.index') }}" class="{{ request()->routeIs('admin.blocks.*') ? 'active' : '' }}">
                <i class="bi bi-grid-3x3-gap"></i> Manage Blocks
            </a>
            <div class="nav-section">Voter</div>
            @endif

            <a href="{{ route('voter.elections') }}" class="{{ request()->routeIs('voter.elections') ? 'active' : '' }}">
                <i class="bi bi-clipboard2-check"></i> {{ auth()->user()->isElectionManager() ? 'Student elections' : 'My dashboard' }}
            </a>
            @unless(auth()->user()->isElectionManager())
                <a href="{{ route('voter.records') }}" class="{{ request()->routeIs('voter.records') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> My Voting Record
                </a>
            @endunless

            <div class="nav-section">Account</div>
            <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                @csrf
                <a href="#" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </form>
        </nav>
        <div class="app-sidebar-note"><img src="{{ asset('assets/img/student-council-logo.png') }}" alt=""><strong>Your campus.<br>Your voice.</strong><span>Building our community,<br>one vote at a time.</span></div>
    </aside>
    @endauth

    <!-- ── Main Content ── -->
    <main class="main-content" id="main-content">
        <div class="flash-wrap">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-1"></i> {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        {{ $slot }}

        <footer class="app-content-footer">
            <span>CICS E-Voting &middot; Batangas State University ARASOF Campus</span>
            <span>Leading Innovations. Transforming Lives. Building the Nation.</span>
        </footer>
    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const navToggle = document.getElementById('navToggle');
    const appSidebar = document.getElementById('appSidebar');
    if (navToggle && appSidebar) {
        navToggle.addEventListener('click', () => {
            appSidebar.classList.toggle('show');
            const expanded = navToggle.getAttribute('aria-expanded') === 'true';
            navToggle.setAttribute('aria-expanded', !expanded);
        });
    }
</script>
@stack('scripts')
</body>
</html>
