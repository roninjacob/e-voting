<x-layouts.app title="Manage Elections">
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2>Manage Elections</h2>
        <div class="election-status-bar">
            <span style="color:var(--text-muted);">All block elections</span>
        </div>
    </div>
    <a href="{{ route('admin.elections.index') }}" class="btn-gold" onclick="event.preventDefault(); document.getElementById('createElectionCard').scrollIntoView({behavior: 'smooth'});">
        <i class="bi bi-plus-lg"></i> New Election
    </a>
</div>

<div class="dark-card mb-4" id="createElectionCard">
    <div class="card-head">
        <i class="bi bi-plus-circle me-1" style="color:var(--gold);"></i> Create Draft Election
    </div>
    <div class="card-body-inner">
        <form method="POST" action="{{ route('admin.elections.store') }}" class="row g-3">
            @csrf
            <div class="col-md-2">
                <label class="form-label text-muted small text-uppercase fw-bold">Election Scope</label>
                <select class="form-control bg-dark text-light border-secondary" name="scope" id="electionScope" required>
                    <option value="block">Block election</option>
                    <option value="all_cics">Student Council — All CICS</option>
                </select>
            </div>
            <div class="col-md-2" id="electionBlockField">
                <label class="form-label text-muted small text-uppercase fw-bold">Block</label>
                <select class="form-control bg-dark text-light border-secondary" name="block_id" id="electionBlock">
                    <option value="">— Select Block —</option>
                    @foreach($blocks as $block)
                        <option value="{{ $block->id }}">BSIT {{ $block->section }} — Year {{ $block->year_level }}, Sem {{ $block->semester }}@if($block->track) — {{ $block->majorName() }}@endif</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small text-uppercase fw-bold">Election Title</label>
                <input class="form-control bg-dark text-light border-secondary" name="title" placeholder="e.g. BSIT 1101 Student Council Election" required>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small text-uppercase fw-bold">Start Date</label>
                <input class="form-control bg-dark text-light border-secondary" type="datetime-local" name="starts_at" required>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small text-uppercase fw-bold">End Date</label>
                <input class="form-control bg-dark text-light border-secondary" type="datetime-local" name="ends_at" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn w-100" style="background:var(--gold);border:none;color:#061a18;font-weight:700;">Create draft</button>
            </div>
        </form>
    </div>
</div>

<div class="dark-card">
    <div class="card-body-inner" style="border-bottom: 1px solid var(--card-border); font-size: 0.85rem; color: var(--text-muted);">
        Add positions and active candidates, then click <strong>Advance</strong> to open voting. Open elections appear in My Ballot for students in the assigned block.
    </div>
    <div style="overflow-x:auto;">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Block</th>
                    <th>Status</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($elections as $el)
                @php
                    $statusClasses = ['draft'=>'badge-draft','configured'=>'badge-configured','open'=>'badge-open','closed'=>'badge-closed','published'=>'badge-published'];
                    $statusClass = $statusClasses[$el->status] ?? 'badge-draft';
                    $transitionLabels = [
                        'draft' => 'Finish Setup',
                        'configured' => 'Open Voting',
                        'open' => 'Close Voting',
                        'closed' => 'Publish Election',
                    ];
                @endphp
                <tr>
                    <td style="color:var(--text-muted);">{{ $el->id }}</td>
                    <td style="font-weight:600;">{{ $el->title }}</td>
                    <td style="color:var(--text-muted);">
                        {{ $el->scopeLabel() }}
                        @if($el->block)
                        — Year {{ $el->block->year_level }}, Sem {{ $el->block->semester }}@if($el->block->track) — {{ $el->block->majorName() }}@endif
                        @endif
                    </td>
                    <td><span class="badge-status {{ $statusClass }}">{{ ucfirst($el->status) }}</span></td>
                    <td style="color:var(--text-muted);font-size:.78rem;">{{ $el->starts_at?->format('M d, Y') }}</td>
                    <td style="color:var(--text-muted);font-size:.78rem;">{{ $el->ends_at?->format('M d, Y') }}</td>
                    <td>
                        <div style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                            <a href="{{ route('admin.positions.index', $el) }}" class="btn-sm-icon" title="Manage Positions"><i class="bi bi-diagram-3"></i></a>
                            <a href="{{ route('admin.candidates.index', $el) }}" class="btn-sm-icon" title="Manage Candidates"><i class="bi bi-people"></i></a>
                            @if(in_array($el->status, ['closed', 'published']))
                            <a href="{{ route('admin.results.show', $el) }}" class="btn-sm-icon" title="View Results"><i class="bi bi-bar-chart"></i></a>
                            @endif

                            @if(!in_array($el->status, ['published']))
                            <form method="POST" action="{{ route('admin.elections.transition', $el) }}" style="display:inline;" onsubmit="return confirm('Advance election status?');">
                                @csrf
                                <button type="submit" class="btn-gold" style="white-space:nowrap;padding:4px 10px;font-size:0.75rem;">
                                    <i class="bi bi-arrow-right-circle"></i> {{ $transitionLabels[$el->status] ?? 'Advance' }}
                                </button>
                            </form>
                            @endif

                            @if(in_array($el->status, ['draft', 'configured']))
                            <form method="POST" action="{{ route('admin.elections.destroy', $el) }}" style="display:inline;" onsubmit="return confirm('Delete election?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-sm-icon danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px;">No elections found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    const electionScope = document.getElementById('electionScope');
    const electionBlockField = document.getElementById('electionBlockField');
    const electionBlock = document.getElementById('electionBlock');

    function updateElectionScope() {
        const isBlockElection = electionScope.value === 'block';
        electionBlockField.style.display = isBlockElection ? '' : 'none';
        electionBlock.required = isBlockElection;
        if (!isBlockElection) {
            electionBlock.value = '';
        }
    }

    electionScope.addEventListener('change', updateElectionScope);
    updateElectionScope();
</script>
@endpush
</x-layouts.app>
