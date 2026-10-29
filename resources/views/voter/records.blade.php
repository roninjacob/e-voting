<x-layouts.app title="My Voting Record">
<div class="page-header">
    <h1>My Voting Record</h1>
    <div class="election-status-bar">
        <span style="color:var(--text-muted);">Submission receipts only — your candidate choices are never shown here.</span>
    </div>
</div>

<div class="dark-card">
    <div class="card-head"><i class="bi bi-receipt me-1" style="color:var(--gold);"></i> Confirmed submissions</div>
    <div class="voting-record-scroll" role="region" aria-label="Voting submission receipts" tabindex="0">
        <p class="voting-record-scroll-hint"><i class="bi bi-arrow-left-right" aria-hidden="true"></i> Swipe or scroll sideways to see all receipt details.</p>
        <table class="dark-table voting-record-table">
            <thead>
                <tr>
                    <th>Election</th>
                    <th>Block</th>
                    <th>Receipt reference</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Results</th>
                </tr>
            </thead>
            <tbody>
            @forelse($receipts as $receipt)
                <tr>
                    <td style="font-weight:600;">{{ $receipt->election?->title ?? 'Deleted election' }}</td>
                    <td style="color:var(--text-muted);">{{ $receipt->election?->scopeLabel() ?? '—' }}</td>
                    <td><code>{{ $receipt->receipt_reference }}</code></td>
                    <td style="color:var(--text-muted);">{{ $receipt->submitted_at?->format('M d, Y g:i A') }}</td>
                    <td><span class="badge-status badge-open">{{ ucfirst($receipt->status) }}</span></td>
                    <td>
                        @if($receipt->election?->status === 'published')
                            <a href="{{ route('voter.results.show', $receipt->election) }}" class="btn-sm-icon" title="View published results"><i class="bi bi-bar-chart"></i></a>
                        @else
                            <span style="color:var(--text-muted);">Not published</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:32px;">You have not submitted a ballot yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-layouts.app>
