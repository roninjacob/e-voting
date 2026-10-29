<x-layouts.app title="Published Results">
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2>{{ $election->title }}</h2>
        <div class="election-status-bar"><span class="badge-status badge-published">Published results</span></div>
    </div>
    <a href="{{ route('voter.results.export', $election) }}" class="btn-gold"><i class="bi bi-download"></i> Export CSV</a>
</div>

<div class="dark-card mb-4">
    <div class="card-body-inner d-flex justify-content-between align-items-center">
        <div><strong>{{ $receiptCount }}</strong> confirmed ballot{{ $receiptCount === 1 ? '' : 's' }}</div>
        <span class="text-muted">Results contain aggregate totals only.</span>
    </div>
</div>

@foreach($positionStats as $stat)
    <div class="dark-card mb-4">
        <div class="card-head">{{ $stat['position']->name }}</div>
        <div style="overflow-x:auto;">
            <table class="dark-table">
                <thead><tr><th>Candidate</th><th>Votes</th><th>Share of selections</th></tr></thead>
                <tbody>
                @forelse($stat['candidate_rows'] as $row)
                    <tr>
                        <td style="font-weight:600;">{{ $row->name }}</td>
                        <td>{{ $row->votes }}</td>
                        <td>{{ $stat['total_votes'] > 0 ? number_format(($row->votes / $stat['total_votes']) * 100, 2) : '0.00' }}%</td>
                    </tr>
                @empty
                    <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:32px;">No selections were recorded for this position.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body-inner small text-muted">Selections recorded: {{ $stat['total_votes'] }} · Unused selection slots: {{ $stat['unused_selections'] }}</div>
    </div>
@endforeach
</x-layouts.app>
