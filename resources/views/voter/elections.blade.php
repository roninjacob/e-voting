<x-layouts.app title="My Dashboard">
@php
    $user = auth()->user();
    $readyCount = $elections->filter(fn($el) => $el->status === 'open' && !in_array($el->id, $votedElectionIds))->count();
    $submittedCount = count($votedElectionIds);
    $firstName = explode(' ', trim($user->name))[0];
    $studentBlock = $user->block;
    $dashboardDate = now()->timezone('Asia/Manila');
@endphp

<div class="dashboard-topline"><span><i class="bi bi-grid" aria-hidden="true"></i> Student workspace <span>/</span> My dashboard</span><time datetime="{{ $dashboardDate->format('Y-m-d') }}">{{ $dashboardDate->format('M d, Y') }}</time></div>

<section class="dashboard-hero dashboard-hero--student">
    <div class="dashboard-hero-content">
        <span class="dashboard-eyebrow">HELLO, {{ strtoupper($firstName) }}.</span>
        <h1>Your voice <em>makes a difference.</em></h1>
        <p>A stronger campus starts with you. Meet your future leaders and take part in shaping our community.</p>
        <a class="dashboard-button" href="#student-election-list">{{ $readyCount > 0 ? 'Explore your ballots' : 'Explore your elections' }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
    <div class="student-hero-art" aria-hidden="true"><div class="student-hero-orbit"></div><span class="student-hero-ballot"><i class="bi bi-check2-square"></i></span><span class="student-hero-spark"><i class="bi bi-stars"></i></span><span class="student-hero-art-label">ONE VOTE. A SHARED FUTURE.</span></div>
</section>

<div class="dashboard-stats dashboard-stats--student">
    @php $cards = [
        ['label'=>'Ready to vote', 'value'=>$readyCount, 'icon'=>'bi-check2-square', 'tone'=>'teal', 'note'=>'Open ballots waiting for you'],
        ['label'=>'Votes submitted', 'value'=>$submittedCount, 'icon'=>'bi-check-circle', 'tone'=>'green', 'note'=>'Your participation so far'],
        ['label'=>'Available elections', 'value'=>$elections->count(), 'icon'=>'bi-stack', 'tone'=>'blue', 'note'=>'Elections for your block'],
    ]; @endphp
    @foreach ($cards as $card)
    <article class="dashboard-stat"><div class="dashboard-stat-top"><span>{{ $card['label'] }}</span><span class="dashboard-icon dashboard-icon--{{ $card['tone'] }}"><i class="bi {{ $card['icon'] }}" aria-hidden="true"></i></span></div><strong>{{ $card['value'] }}</strong><small>{{ $card['note'] }}</small></article>
    @endforeach
</div>

<div class="dashboard-columns dashboard-columns--student">
    <section id="student-election-list" aria-labelledby="student-elections-heading">
        <div class="dashboard-section-head"><div><span class="dashboard-eyebrow">YOUR NEXT CHAPTER</span><h2 id="student-elections-heading">My block elections</h2><p>Find your ballot and make your choice count.</p></div><span class="dashboard-soft-label">{{ $elections->count() }} election{{ $elections->count() !== 1 ? 's' : '' }}</span></div>
        @if ($elections->isEmpty())
        <div class="dashboard-panel dashboard-empty"><span class="dashboard-empty-icon"><i class="bi bi-inbox" aria-hidden="true"></i></span><h3>Something good is on the way.</h3><p>No elections are available for your block yet. Check back here when your campus is ready to vote.</p><span class="dashboard-soft-label"><i class="bi bi-clock" aria-hidden="true"></i> Stay tuned</span></div>
        @else
        <div class="student-election-grid">
            @foreach ($elections as $el)
            @php
                $hasVoted = in_array($el->id, $votedElectionIds);
                $statusClasses = ['draft'=>'badge-draft','configured'=>'badge-configured','open'=>'badge-open','closed'=>'badge-closed','published'=>'badge-published'];
                $statusClass = $statusClasses[$el->status] ?? 'badge-draft';
            @endphp
            <article class="student-election-card{{ $el->status === 'open' && !$hasVoted ? ' is-open' : '' }}">
                <div class="student-election-top"><span class="dashboard-icon dashboard-icon--{{ $hasVoted ? 'green' : 'teal' }}"><i class="bi {{ $hasVoted ? 'bi-check-circle' : 'bi-check2-square' }}" aria-hidden="true"></i></span><span class="badge-status {{ $statusClass }}">{{ ucfirst($el->status) }}</span></div>
                <span class="student-election-block">BSIT {{ $el->block?->section ?? '—' }} &middot; Year {{ $el->block?->year_level ?? '1' }}@if($el->block) &middot; Sem {{ $el->block->semester }}@if($el->block->track) &middot; {{ $el->block->majorName() }}@endif @endif</span>
                <h3>{{ $el->title }}</h3>
                <p class="student-election-description">{{ $el->description ?: 'Help choose the representatives who will lead your block and serve your community.' }}</p>
                <div class="student-election-dates"><div><span><i class="bi bi-calendar2" aria-hidden="true"></i> Opens</span><strong>{{ $el->starts_at?->format('M d, Y') }}</strong><small>{{ $el->starts_at?->format('g:i A') }}</small></div><div><span><i class="bi bi-calendar2-check" aria-hidden="true"></i> Closes</span><strong>{{ $el->ends_at?->format('M d, Y') }}</strong><small>{{ $el->ends_at?->format('g:i A') }}</small></div></div>
                <div class="student-election-action">
                    @if ($hasVoted)
                    <div class="student-vote-submitted"><i class="bi bi-check-circle-fill" aria-hidden="true"></i><span>Vote submitted<small>Thank you for taking part.</small></span></div>
                    @elseif ($el->status === 'open' && $user->is_verified)
                    <a href="{{ route('voter.ballot.show', $el) }}" class="dashboard-button">Vote now <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    @elseif (!$user->is_verified)
                    <span class="student-voting-closed"><i class="bi bi-hourglass-split" aria-hidden="true"></i> Account pending verification</span>
                    @else
                    <span class="student-voting-closed"><i class="bi bi-lock" aria-hidden="true"></i> {{ $el->status === 'published' ? 'Results have been published' : 'Voting has closed' }}</span>
                    @endif
                </div>
            </article>
            @endforeach
        </div>
        @endif
    </section>

    <aside class="dashboard-stack" aria-label="Student voting guide">
        <section class="dashboard-panel student-guide"><div class="dashboard-panel-head"><div><span class="dashboard-eyebrow">A LITTLE GUIDANCE</span><h2>Make your vote count.</h2></div></div><div class="dashboard-panel-body"><ol><li><span>01</span><div><strong>Get to know the candidates</strong><p>Read their platforms and see what they stand for.</p></div></li><li><span>02</span><div><strong>Choose your representatives</strong><p>Select the people you want to lead your block.</p></div></li><li><span>03</span><div><strong>Review, then submit</strong><p>Check your choices before sending your ballot.</p></div></li></ol><div class="student-guide-tip"><i class="bi bi-lightbulb" aria-hidden="true"></i><p>Take your time. Your choices help shape the CICS community.</p></div></div></section>
        <section class="student-community-card"><img src="{{ asset('assets/img/cics-logo.png') }}" alt="CICS"><span class="dashboard-eyebrow">YOUR COMMUNITY</span><h2>{{ $studentBlock ? 'BSIT ' . $studentBlock->section : 'CICS student community' }}</h2><p>{{ $studentBlock ? 'BSIT ' . $studentBlock->section . ' — Year ' . $studentBlock->year_level . ', Sem ' . $studentBlock->semester . ($studentBlock->track ? ' (' . $studentBlock->majorName() . ')' : '') : 'Your block elections will appear here once a block is assigned.' }}</p></section>
    </aside>
</div>
</x-layouts.app>
