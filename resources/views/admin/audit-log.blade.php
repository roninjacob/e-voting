<x-layouts.app title="Audit Log">
<div class="page-header">
    <h2>Audit Log</h2>
    <div class="election-status-bar"><span style="color:var(--text-muted);">Security-relevant election activity without ballot selections</span></div>
</div>

<div class="dark-card">
    <div style="overflow-x:auto;">
        <table class="dark-table">
            <thead><tr><th>Time</th><th>Action</th><th>Election</th><th>Actor</th><th>Role</th><th>Details</th><th>IP address</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="color:var(--text-muted);white-space:nowrap;">{{ $log->created_at?->format('M d, Y g:i A') }}</td>
                    <td><strong>{{ str($log->action)->replace('_', ' ')->title() }}</strong></td>
                    <td>{{ $log->election?->title ?? '—' }}</td>
                    <td>{{ $log->actor?->name ?? 'System' }}</td>
                    <td>{{ $log->actor_role ? ucfirst($log->actor_role) : '—' }}</td>
                    <td><code>{{ $log->metadata ? json_encode($log->metadata, JSON_UNESCAPED_SLASHES) : '—' }}</code></td>
                    <td style="color:var(--text-muted);">{{ $log->ip_address ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px;">No audit events have been recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-body-inner">{{ $logs->withQueryString()->links() }}</div>
    @endif
</div>
</x-layouts.app>
