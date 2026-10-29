<x-layouts.app title="Election Overview">
@php
    $statusCards = [
        ['label' => 'Total elections', 'value' => $elections->count(), 'icon' => 'bi-stack', 'tone' => 'teal', 'note' => 'Across all blocks'],
        ['label' => 'Open for voting', 'value' => $elections->where('status','open')->count(), 'icon' => 'bi-broadcast', 'tone' => 'green', 'note' => 'Accepting ballots'],
        ['label' => 'In draft', 'value' => $elections->where('status','draft')->count(), 'icon' => 'bi-pencil-square', 'tone' => 'amber', 'note' => 'Being prepared'],
        ['label' => 'Vote receipts', 'value' => $receiptCount, 'icon' => 'bi-patch-check', 'tone' => 'blue', 'note' => 'Ballots submitted'],
    ];
    $openCount = $elections->where('status','open')->count();
    $firstName = explode(' ', trim(auth()->user()->name))[0];
    $dashboardDate = now()->timezone('Asia/Manila');
@endphp

<div class="dashboard-topline">
    <span><i class="bi bi-grid" aria-hidden="true"></i> Workspace <span>/</span> Election overview</span>
    <time datetime="{{ $dashboardDate->format('Y-m-d') }}">{{ $dashboardDate->format('M d, Y') }}</time>
</div>

<section class="dashboard-hero">
    <div class="dashboard-hero-content">
        <span class="dashboard-eyebrow">ELECTION MANAGEMENT</span>
        <h1>Your campus, <em>at a glance.</em></h1>
        <p>Welcome back, {{ $firstName }}. Here is what is happening across your elections.</p>
        <span class="dashboard-live-pill">
            <span class="{{ $openCount > 0 ? 'is-live' : '' }}" aria-hidden="true"></span>
            {{ $openCount > 0 ? $openCount . ' election' . ($openCount > 1 ? 's' : '') . ' open for voting' : 'No elections currently open' }}
        </span>
    </div>
    <div class="dashboard-hero-actions">
        <a class="dashboard-button" href="{{ route('admin.elections.index') }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> New election</a>
        <a class="dashboard-button dashboard-button--outline" href="{{ route('admin.elections.index') }}">Manage elections <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
</section>

<div class="dashboard-stats">
    @foreach ($statusCards as $card)
    <article class="dashboard-stat">
        <div class="dashboard-stat-top">
            <span>{{ $card['label'] }}</span>
            <span class="dashboard-icon dashboard-icon--{{ $card['tone'] }}"><i class="bi {{ $card['icon'] }}" aria-hidden="true"></i></span>
        </div>
        <strong>{{ number_format((int) $card['value']) }}</strong><small>{{ $card['note'] }}</small>
    </article>
    @endforeach
</div>

<div class="dashboard-columns">
    <div class="dashboard-stack">
        <section class="dashboard-panel" aria-labelledby="recent-elections-heading">
            <div class="dashboard-panel-head">
                <div><h2 id="recent-elections-heading">Recent elections</h2><p>A quick look at your latest election activity.</p></div>
                <a class="dashboard-text-link" href="{{ route('admin.elections.index') }}">View all <i class="bi bi-arrow-up-right" aria-hidden="true"></i></a>
            </div>
            @if ($elections->isEmpty())
            <div class="dashboard-empty">
                <span class="dashboard-empty-icon"><i class="bi bi-check2-square" aria-hidden="true"></i></span>
                <h3>Your first election starts here.</h3>
                <p>Create an election to bring your campus community together.</p>
                <a class="dashboard-button" href="{{ route('admin.elections.index') }}">Create an election <i class="bi bi-plus-lg" aria-hidden="true"></i></a>
            </div>
            @else
            <div class="dashboard-table-scroll">
                <table class="dashboard-table">
                    <caption class="visually-hidden">The six most recently created elections</caption>
                    <thead><tr><th scope="col">Election</th><th scope="col">Block</th><th scope="col">Status</th><th scope="col">Period</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    @foreach ($elections->take(6) as $el)
                    @php
                        $statusClasses = ['draft'=>'badge-draft','configured'=>'badge-configured','open'=>'badge-open','closed'=>'badge-closed','published'=>'badge-published'];
                        $statusClass = $statusClasses[$el->status] ?? 'badge-draft';
                    @endphp
                    <tr>
                        <td><div class="dashboard-election-title"><span class="dashboard-table-icon"><i class="bi bi-check2-square" aria-hidden="true"></i></span><div><strong>{{ $el->title }}</strong><small>{{ $el->created_at?->format('M d, Y') }}</small></div></div></td>
                        <td><span class="dashboard-block-pill">{{ $el->block?->section ?? '—' }}</span></td>
                        <td><span class="badge-status {{ $statusClass }}">{{ ucfirst($el->status) }}</span></td>
                        <td class="dashboard-table-date">{{ $el->start_at?->format('M d') }} &ndash; {{ $el->end_at?->format('M d, Y') }}</td>
                        <td><a href="{{ route('admin.positions.index', $el) }}" class="dashboard-icon-link" aria-label="Manage {{ $el->title }}"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></a></td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </section>

        <section class="dashboard-panel" aria-labelledby="activity-heading">
            <div class="dashboard-panel-head">
                <div><h2 id="activity-heading">Latest activity</h2><p>Recent updates from your election workspace.</p></div>
                <span class="dashboard-soft-label"><i class="bi bi-clock-history" aria-hidden="true"></i> Status history</span>
            </div>
            <div class="dashboard-panel-body">
                @if ($recentLogs->isEmpty())
                <div class="dashboard-inline-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><div><strong>A fresh start.</strong><p>Election status updates will appear here.</p></div></div>
                @else
                <ol class="dashboard-timeline">
                    @foreach ($recentLogs as $log)
                    <li>
                        <span class="dashboard-timeline-dot{{ $log->to_status === 'open' ? ' is-live' : '' }}" aria-hidden="true"></span>
                        <div>
                            <p><strong>{{ $log->changedBy?->name ?? 'System' }}</strong> moved <strong>{{ $log->election?->title }}</strong> to <span class="badge-status {{ $statusClasses[$log->to_status] ?? 'badge-draft' }}">{{ ucfirst($log->to_status) }}</span></p>
                            <time datetime="{{ $log->changed_at }}">{{ \Carbon\Carbon::parse($log->changed_at)->diffForHumans() }}</time>
                        </div>
                    </li>
                    @endforeach
                </ol>
                @endif
            </div>
        </section>
    </div>
</div>
</x-layouts.app>
