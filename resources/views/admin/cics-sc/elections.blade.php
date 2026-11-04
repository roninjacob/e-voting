<x-layouts.app title="Manage CICS-SC Elections">
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2>Manage CICS-SC Elections</h2>
        <div class="election-status-bar">
            <span style="color:var(--text-muted);">College-wide Student Council elections</span>
        </div>
    </div>
    <a href="{{ route('admin.cics-sc.elections.index') }}" class="btn-gold" onclick="event.preventDefault(); document.getElementById('createElectionCard').scrollIntoView({behavior: 'smooth'});">
        <i class="bi bi-plus-lg"></i> New Election
    </a>
</div>

<div class="dark-card mb-4" id="createElectionCard">
    <div class="card-head">
        <i class="bi bi-plus-circle me-1" style="color:var(--gold);"></i> Create CICS-SC Draft Election
    </div>
    <div class="card-body-inner">
        <form method="POST" action="{{ route('admin.cics-sc.elections.store') }}" class="row g-3">
            @csrf
            <div class="col-md-4">
                <label class="form-label text-muted small text-uppercase fw-bold">Election Title</label>
                <input class="form-control bg-dark text-light border-secondary" name="title" placeholder="e.g. CICS Student Council Election 2026" required>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small text-uppercase fw-bold">Start Date</label>
                <input class="form-control bg-dark text-light border-secondary" type="datetime-local" name="starts_at" required>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small text-uppercase fw-bold">End Date</label>
                <input class="form-control bg-dark text-light border-secondary" type="datetime-local" name="ends_at" required>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small text-uppercase fw-bold">Description</label>
                <input class="form-control bg-dark text-light border-secondary" name="description" placeholder="Optional election description">
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn" style="background:var(--gold);border:none;color:#061a18;font-weight:700;">Create draft</button>
            </div>
        </form>
    </div>
</div>

<div class="dark-card">
    <div class="card-body-inner" style="border-bottom: 1px solid var(--card-border); font-size: 0.85rem; color: var(--text-muted);">
        Add positions and active candidates, then click <strong>Advance</strong> to open voting. Only verified, active CICS students assigned to a BSIT block can vote.
    </div>
    <div style="overflow-x:auto;">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Eligible Voters</th>
                    <th>Status</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($elections as $election)
                @php
                    $statusClasses = ['draft' => 'badge-draft', 'configured' => 'badge-configured', 'open' => 'badge-open', 'closed' => 'badge-closed', 'published' => 'badge-published'];
                    $statusClass = $statusClasses[$election->status] ?? 'badge-draft';
                    $transitionLabels = [
                        'draft' => 'Finish Setup',
                        'configured' => 'Open Voting',
                        'open' => 'Close Voting',
                        'closed' => 'Publish Election',
                    ];
                @endphp
                <tr>
                    <td style="color:var(--text-muted);">{{ $election->id }}</td>
                    <td style="font-weight:600;">{{ $election->title }}</td>
                    <td style="color:var(--text-muted);">Verified CICS students</td>
                    <td><span class="badge-status {{ $statusClass }}">{{ ucfirst($election->status) }}</span></td>
                    <td style="color:var(--text-muted);font-size:.78rem;">{{ $election->starts_at?->format('M d, Y') }}</td>
                    <td style="color:var(--text-muted);font-size:.78rem;">{{ $election->ends_at?->format('M d, Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                            <a href="{{ route('admin.cics-sc.positions.index', $election) }}" class="btn-sm-icon" title="Manage Positions"><i class="bi bi-diagram-3"></i></a>
                            <a href="{{ route('admin.cics-sc.candidates.index', $election) }}" class="btn-sm-icon" title="Manage Candidates"><i class="bi bi-people"></i></a>

                            @if(!in_array($election->status, ['published'], true))
                            <form method="POST" action="{{ route('admin.cics-sc.elections.transition', $election) }}" style="display:inline;" onsubmit="return confirm('Advance election status?');">
                                @csrf
                                <button type="submit" class="btn-gold" style="white-space:nowrap;padding:4px 10px;font-size:0.75rem;">
                                    <i class="bi bi-arrow-right-circle"></i> {{ $transitionLabels[$election->status] ?? 'Advance' }}
                                </button>
                            </form>
                            @endif

                            @if(in_array($election->status, ['draft', 'configured'], true))
                            <form method="POST" action="{{ route('admin.cics-sc.elections.destroy', $election) }}" style="display:inline;" onsubmit="return confirm('Delete election?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px;">No CICS-SC elections found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-layouts.app>
