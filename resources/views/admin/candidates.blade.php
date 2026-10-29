<x-layouts.app title="Candidates — {{ $election->title }}">
<div class="page-header d-flex align-items-center gap-3">
    <a href="{{ route('admin.positions.index', $election) }}" class="btn btn-outline-secondary btn-sm" style="background:var(--card-bg);border-color:var(--card-border);color:var(--text-light);padding:7px 12px;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h2 style="margin-bottom:4px;">Candidates for {{ $election->title }}</h2>
        <div class="election-status-bar">
            <span class="badge-status badge-{{ $election->status }}">{{ ucfirst($election->status) }}</span>
            <span style="color:var(--text-muted);">BSIT {{ $election->block?->section ?? '' }}</span>
        </div>
    </div>
</div>

@foreach($election->positions as $position)
<div class="dark-card mb-4">
    <div class="card-head d-flex justify-content-between align-items-center">
        <span><i class="bi bi-diagram-3 me-1" style="color:var(--gold);"></i> {{ $position->name }} (Max votes: {{ $position->max_votes }})</span>
    </div>
    <div class="card-body-inner">
        @if($election->status === 'draft')
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.candidates.store', [$election, $position]) }}" class="row g-3 mb-4 p-3 rounded" style="background:rgba(0,0,0,0.2); border:1px solid var(--card-border);">
            @csrf
            <div class="col-md-5">
                <label class="form-label text-muted small text-uppercase fw-bold">Candidate Full Name</label>
                <input class="form-control bg-dark text-light border-secondary" name="full_name" placeholder="Full name" required>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small text-uppercase fw-bold">Party / Affiliation</label>
                <input class="form-control bg-dark text-light border-secondary" name="party" placeholder="Party (optional)">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn w-100" style="background:var(--gold);border:none;color:#061a18;font-weight:700;">Add Candidate</button>
            </div>
        </form>
        @endif

        <div class="row g-3">
            @forelse($position->candidates as $candidate)
            <div class="col-md-4">
                <div class="p-3 rounded" style="background:rgba(6,26,24,0.6); border:1px solid var(--card-border);">
                    <strong style="color:#fff; font-size:1.05rem;">{{ $candidate->full_name }}</strong><br>
                    <small style="color:var(--text-muted);">Party: {{ $candidate->party ?: 'Independent' }}</small>
                </div>
            </div>
            @empty
            <div class="col-12">
                <p style="color:var(--text-muted); margin:0;">No candidates added for this position yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endforeach
</x-layouts.app>
