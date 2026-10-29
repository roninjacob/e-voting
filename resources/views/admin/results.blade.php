<x-layouts.app title="Election Results">
<div class="dashboard-topline">
    <span><i class="bi bi-bar-chart" aria-hidden="true"></i> Workspace <span>/</span> Results and recording</span>
    @if($election)
        <span class="badge-status {{ $election->status === 'published' ? 'badge-published' : 'badge-closed' }}">{{ ucfirst($election->status) }}</span>
    @endif
</div>

<div class="page-header">
    <h2>Election Results</h2>
    <div class="election-status-bar"><span>Live manager tallies and reconciliation checks</span></div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <section class="dashboard-panel">
            <div class="dashboard-panel-head">
                <div><h2><i class="bi bi-list-check dashboard-heading-icon me-2" aria-hidden="true"></i>Elections</h2><p>Select a closed or published election.</p></div>
            </div>
            <div class="dashboard-panel-body results-election-list">
                @forelse($elections as $item)
                    @if(in_array($item->status, ['closed', 'published']))
                        <a href="{{ route('admin.results.show', $item) }}" class="results-election-item {{ $election?->id === $item->id ? 'is-active' : '' }}">
                            <strong>{{ $item->title }}</strong>
                            <span>{{ $item->scopeLabel() }} · {{ ucfirst($item->status) }}</span>
                        </a>
                    @else
                        <div class="results-election-item is-disabled">
                            <strong>{{ $item->title }}</strong>
                            <span>Results available after voting closes · {{ ucfirst($item->status) }}</span>
                        </div>
                    @endif
                @empty
                    <p class="dashboard-muted mb-0">No elections found.</p>
                @endforelse
            </div>
        </section>
    </div>

    <div class="col-lg-8">
        @if(!$election)
            <section class="dashboard-panel"><div class="dashboard-empty"><span class="dashboard-empty-icon"><i class="bi bi-bar-chart" aria-hidden="true"></i></span><h3>Select an election</h3><p>Choose a closed or published election to review turnout and vote totals.</p></div></section>
        @else
            <div class="dashboard-stats mb-4">
                <article class="dashboard-stat"><div class="dashboard-stat-top"><span>Eligible voters</span></div><strong>{{ $turnout['eligible'] }}</strong><small>Verified voters in the assigned block</small></article>
                <article class="dashboard-stat"><div class="dashboard-stat-top"><span>Ballots cast</span></div><strong>{{ $turnout['cast'] }}</strong><small>{{ number_format($turnout['percent'], 2) }}% turnout</small></article>
                <article class="dashboard-stat"><div class="dashboard-stat-top"><span>Reconciliation</span></div><strong>{{ $reconciliation ? 'Pass' : 'Review' }}</strong><small>{{ $reconciliation ? 'Totals remain within ballot limits' : 'A position exceeds its maximum possible votes' }}</small></article>
            </div>

            @foreach($positionStats as $stat)
                @php($topVotes = $stat['candidate_rows']->max('votes') ?? 0)
                <section class="dashboard-panel mb-4">
                    <div class="dashboard-panel-head">
                        <div><h2>{{ $stat['position']->name }}</h2><p>Candidate totals and share of recorded selections.</p></div>
                        <span class="dashboard-soft-label {{ $stat['reconciles'] ? '' : 'text-danger' }}"><i class="bi {{ $stat['reconciles'] ? 'bi-check-circle' : 'bi-exclamation-triangle' }}" aria-hidden="true"></i>{{ $stat['reconciles'] ? 'Reconciled' : 'Needs review' }}</span>
                    </div>
                    <div class="dashboard-table-scroll">
                        <table class="dashboard-table results-table">
                            <thead><tr><th>Candidate</th><th>Votes</th><th>Share</th><th>Standing</th></tr></thead>
                            <tbody>
                            @forelse($stat['candidate_rows'] as $row)
                                <tr>
                                    <td><strong>{{ $row->name }}</strong>@if(!$row->is_active) <span class="dashboard-muted"> (inactive)</span>@endif</td>
                                    <td>{{ $row->votes }}</td>
                                    <td>{{ $stat['position_ballots'] > 0 ? number_format(($row->votes / $stat['position_ballots']) * 100, 2) : '0.00' }}%</td>
                                    <td>@if($row->votes > 0 && $row->votes === $topVotes)<span class="badge-status badge-open">{{ $stat['tie'] ? 'Tied lead' : 'Leading' }}</span>@else — @endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center dashboard-muted p-4">No votes recorded for this position.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="results-summary">Selections recorded: {{ $stat['position_ballots'] }} · Unused selection slots: {{ $stat['unused_selections'] }}</div>
                </section>
            @endforeach
        @endif
    </div>
</div>
</x-layouts.app>
