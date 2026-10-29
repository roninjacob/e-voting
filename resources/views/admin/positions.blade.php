<x-layouts.app title="Positions — {{ $election->title }}">
<div class="page-header d-flex align-items-center gap-3">
    <a href="{{ route('admin.elections.index') }}" class="btn btn-outline-secondary btn-sm" style="background:var(--card-bg);border-color:var(--card-border);color:var(--text-light);padding:7px 12px;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 style="margin-bottom:4px;">{{ $election->title }}</h2>
        <div class="election-status-bar">
            <span class="badge-status badge-{{ $election->status }}">{{ ucfirst($election->status) }}</span>
            <span style="color:var(--text-muted);">BSIT {{ $election->block?->section ?? '' }}</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        @if($election->status === 'draft')
        <div class="dark-card mb-4">
            <div class="card-head"><i class="bi bi-plus-circle me-1" style="color:var(--gold);"></i> Add Position</div>
            <div class="card-body-inner">
                <form method="POST" action="{{ route('admin.positions.store', $election) }}" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-bold">Position Name</label>
                        <input class="form-control bg-dark text-light border-secondary" name="name" placeholder="e.g. President" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small text-uppercase fw-bold">Max Votes</label>
                        <input class="form-control bg-dark text-light border-secondary" name="max_votes" type="number" min="1" value="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small text-uppercase fw-bold">Sort Order</label>
                        <input class="form-control bg-dark text-light border-secondary" name="sort_order" type="number" min="0" value="0" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn" style="background:var(--gold);border:none;color:#061a18;font-weight:700;">Add Position</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <div class="dark-card">
            <div class="card-head">Positions & Candidates</div>
            <div style="overflow-x:auto;">
                <table class="dark-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Position Name</th>
                            <th>Max Votes</th>
                            <th>Candidates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($election->positions as $position)
                        <tr>
                            <td style="color:var(--text-muted);">{{ $position->sort_order }}</td>
                            <td style="font-weight:600;">{{ $position->name }}</td>
                            <td style="color:var(--text-muted);">{{ $position->max_votes }}</td>
                            <td>
                                <a href="{{ route('admin.candidates.index', $election) }}" class="badge-status badge-open" style="text-decoration:none;">
                                    {{ $position->candidates->count() }} candidate(s)
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('admin.candidates.index', $election) }}" class="btn-sm-icon" title="Manage Candidates"><i class="bi bi-people"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:32px;">No positions added yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</x-layouts.app>
