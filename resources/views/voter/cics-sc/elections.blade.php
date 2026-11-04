<x-layouts.app title="CICS-SC Elections">
@php
    $user = auth()->user();
    $readyCount = $elections->filter(fn ($election) => $election->status === 'open' && !in_array($election->id, $votedElectionIds))->count();
    $submittedCount = count($votedElectionIds);
    $firstName = explode(' ', trim($user->name))[0];
    $dashboardDate = now()->timezone('Asia/Manila');
@endphp

<div class="dashboard-topline"><span><i class="bi bi-grid" aria-hidden="true"></i> Student workspace <span>/</span> CICS-SC elections</span><time datetime="{{ $dashboardDate->format('Y-m-d') }}">{{ $dashboardDate->format('M d, Y') }}</time></div>

<section class="dashboard-hero dashboard-hero--student">
    <div class="dashboard-hero-content">
        <span class="dashboard-eyebrow">CICS STUDENT COUNCIL</span>
        <h1>Lead your college <em>with your vote.</em></h1>
        <p>Welcome, {{ $firstName }}. Get to know the candidates and choose the student leaders who will serve the CICS community.</p>
        @if($isEligible)
        <a class="dashboard-button" href="#cics-sc-election-list">{{ $readyCount > 0 ? 'Explore your ballots' : 'Explore elections' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        @endif
    </div>
    <div class="student-hero-art" aria-hidden="true"><div class="student-hero-orbit"></div><span class="student-hero-ballot"><i class="bi bi-check2-square"></i></span><span class="student-hero-spark"><i class="bi bi-stars"></i></span><span class="student-hero-art-label">ONE COLLEGE. ONE VOICE.</span></div>
</section>

@if($isEligible)
<div class="dashboard-stats dashboard-stats--student">
    @php $cards = [
        ['label' => 'CICS-SC Voting', 'value' => $readyCount, 'icon' => 'bi-check2-square', 'tone' => 'teal', 'note' => 'Open CICS-SC ballots waiting for you'],
        ['label' => 'Candidate Selection', 'value' => $elections->count(), 'icon' => 'bi-person-check', 'tone' => 'green', 'note' => 'Review candidates and choose your leaders'],
        ['label' => 'Eligibility of CICS Students', 'value' => 'Verified', 'icon' => 'bi-shield-check', 'tone' => 'blue', 'note' => 'Active, verified CICS student voters only'],
    ]; @endphp
    @foreach($cards as $card)
    <article class="dashboard-stat"><div class="dashboard-stat-top"><span>{{ $card['label'] }}</span><span class="dashboard-icon dashboard-icon--{{ $card['tone'] }}"><i class="bi {{ $card['icon'] }}" aria-hidden="true"></i></span></div><strong>{{ $card['value'] }}</strong><small>{{ $card['note'] }}</small></article>
    @endforeach
</div>

<div class="dashboard-columns dashboard-columns--student">
    <section id="cics-sc-election-list" aria-labelledby="cics-sc-elections-heading">
        <div class="dashboard-section-head"><div><span class="dashboard-eyebrow">YOUR CICS COMMUNITY</span><h2 id="cics-sc-elections-heading">CICS-SC elections</h2><p>Choose the representatives who will lead the college.</p></div><span class="dashboard-soft-label">{{ $elections->count() }} election{{ $elections->count() !== 1 ? 's' : '' }}</span></div>
        @if($elections->isEmpty())
        <div class="dashboard-panel dashboard-empty"><span class="dashboard-empty-icon"><i class="bi bi-inbox" aria-hidden="true"></i></span><h3>Something good is on the way.</h3><p>No CICS-SC elections are available yet. Check back when your college is ready to vote.</p><span class="dashboard-soft-label"><i class="bi bi-clock" aria-hidden="true"></i> Stay tuned</span></div>
        @else
        <div class="student-election-grid">
            @foreach($elections as $election)
            @php
                $hasVoted = in_array($election->id, $votedElectionIds);
                $statusClasses = ['draft' => 'badge-draft', 'configured' => 'badge-configured', 'open' => 'badge-open', 'closed' => 'badge-closed', 'published' => 'badge-published'];
                $statusClass = $statusClasses[$election->status] ?? 'badge-draft';
            @endphp
            <article class="student-election-card{{ $election->status === 'open' && !$hasVoted ? ' is-open' : '' }}">
                <div class="student-election-top"><span class="dashboard-icon dashboard-icon--{{ $hasVoted ? 'green' : 'teal' }}"><i class="bi {{ $hasVoted ? 'bi-check-circle' : 'bi-people' }}" aria-hidden="true"></i></span><span class="badge-status {{ $statusClass }}">{{ ucfirst($election->status) }}</span></div>
                <span class="student-election-block">CICS Student Council &middot; College-wide election</span>
                <h3>{{ $election->title }}</h3>
                <p class="student-election-description">{{ $election->description ?: 'Help choose the student leaders who will represent the CICS community.' }}</p>
                <div class="student-election-dates"><div><span><i class="bi bi-calendar2" aria-hidden="true"></i> Opens</span><strong>{{ $election->starts_at?->format('M d, Y') }}</strong><small>{{ $election->starts_at?->format('g:i A') }}</small></div><div><span><i class="bi bi-calendar2-check" aria-hidden="true"></i> Closes</span><strong>{{ $election->ends_at?->format('M d, Y') }}</strong><small>{{ $election->ends_at?->format('g:i A') }}</small></div></div>
                <div class="student-election-action">
                    @if($hasVoted)
                    <div class="student-vote-submitted"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Vote submitted<small>Thank you for taking part.</small></span></div>
                    @elseif($election->status === 'open')
                    <a href="{{ route('cics-sc.ballot.show', $election) }}" class="dashboard-button">Vote now <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    @else
                    <span class="student-voting-closed"><i class="bi bi-lock" aria-hidden="true"></i> {{ $election->status === 'published' ? 'Results have been published' : 'Voting has closed' }}</span>
                    @endif
                </div>
            </article>
            @endforeach
        </div>
        @endif
    </section>

    <aside class="dashboard-stack" aria-label="CICS-SC voting guide">
        <section class="dashboard-panel student-guide"><div class="dashboard-panel-head"><div><span class="dashboard-eyebrow">A LITTLE GUIDANCE</span><h2>Make your vote count.</h2></div></div><div class="dashboard-panel-body"><ol><li><span>01</span><div><strong>Get to know the candidates</strong><p>Read their platforms and see what they stand for.</p></div></li><li><span>02</span><div><strong>Choose your leaders</strong><p>Select the people you want to lead the CICS community.</p></div></li><li><span>03</span><div><strong>Review, then submit</strong><p>Check your choices before sending your ballot.</p></div></li></ol><div class="student-guide-tip"><i class="bi bi-lightbulb" aria-hidden="true"></i><p>Your choices help shape the future of CICS.</p></div></div></section>
    </aside>
</div>
@else
<div class="dashboard-panel dashboard-empty"><span class="dashboard-empty-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></span><h3>CICS-SC voting is unavailable for this account.</h3><p>Only verified, active CICS students assigned to a BSIT block are eligible to vote in CICS-SC elections.</p><a href="{{ route('voter.elections') }}" class="dashboard-button">View my block elections <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
@endif
</x-layouts.app>
