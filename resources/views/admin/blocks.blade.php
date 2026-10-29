<x-layouts.app title="Manage Blocks">
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2>Manage Blocks</h2>
        <div class="election-status-bar">
            <span style="color:var(--text-muted);">{{ $blocks->count() }} blocks total</span>
        </div>
    </div>
    <button class="btn-gold" onclick="document.getElementById('addModal').style.display='flex'">
        <i class="bi bi-plus-lg"></i> Add Block
    </button>
</div>

@if ($errors->any())
<div style="background:rgba(192,57,43,.15);border:1px solid rgba(192,57,43,.4);border-radius:8px;padding:10px 14px;font-size:.83rem;color:#e74c3c;margin-bottom:16px;">
    @foreach ($errors->all() as $error)
        <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
    @endforeach
</div>
@endif

<div class="dark-card">
    <div style="overflow-x:auto;">
        <table class="dark-table">
            <thead>
                <tr>
                    <th>Section</th>
                    <th>Year</th>
                    <th>Semester</th>
                    <th>Major</th>
                    <th>Students</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($blocks as $b)
                <tr>
                    <td style="font-weight:700;color:var(--gold);">BSIT {{ $b->section }}</td>
                    <td>Year {{ $b->year_level }}</td>
                    <td>Sem {{ $b->semester }}</td>
                    <td>
                        @if($b->track)
                        <span style="background:rgba(240,165,0,.15);color:var(--gold);padding:2px 8px;border-radius:4px;font-size:.75rem;font-weight:700;">
                            {{ $b->majorName() }}
                        </span>
                        @else
                        <span style="color:var(--text-muted);font-size:.78rem;">—</span>
                        @endif
                    </td>
                    <td style="color:var(--text-muted);">{{ $b->users_count }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.blocks.destroy', $b) }}" onsubmit="return confirm('Remove block BSIT {{ $b->section }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-sm-icon danger" title="Remove Block">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:32px;">No blocks found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Block Modal -->
<div id="addModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:12px;padding:28px;width:100%;max-width:400px;backdrop-filter:blur(12px);">
        <h5 style="color:#fff;margin-bottom:20px;"><i class="bi bi-plus-circle me-2" style="color:var(--gold);"></i>Add New Block</h5>
        <form method="POST" action="{{ route('admin.blocks.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label text-muted small text-uppercase fw-bold">Year Level</label>
                <select name="year_level" id="addYear" class="form-control bg-dark text-light border-secondary" onchange="toggleTrack()" required>
                    <option value="">— Select —</option>
                    <option value="1">Year 1</option>
                    <option value="2">Year 2</option>
                    <option value="3">Year 3</option>
                    <option value="4">Year 4</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label text-muted small text-uppercase fw-bold">Semester</label>
                <select name="semester" class="form-control bg-dark text-light border-secondary" required>
                    <option value="">— Select —</option>
                    <option value="1">Semester 1</option>
                    <option value="2">Semester 2</option>
                </select>
            </div>
            <div class="mb-3" id="trackGroup" style="display:none;">
                <label class="form-label text-muted small text-uppercase fw-bold">Major (Year 3 & 4)</label>
                <select name="track" class="form-control bg-dark text-light border-secondary">
                    <option value="">— None —</option>
                    <option value="BA">Business Analytics (BA)</option>
                    <option value="NT">Network Technology (NT)</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;margin-top:24px;">
                <button type="submit" class="btn btn-primary flex-grow-1" style="background:var(--gold);border:none;color:#061a18;font-weight:700;">Add Block</button>
                <button type="button" onclick="document.getElementById('addModal').style.display='none'" class="btn btn-outline-secondary flex-grow-1">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleTrack() {
    const yr = document.getElementById('addYear').value;
    const tg = document.getElementById('trackGroup');
    if (yr === '3' || yr === '4') {
        tg.style.display = 'block';
    } else {
        tg.style.display = 'none';
        tg.querySelector('select').value = '';
    }
}
</script>
</x-layouts.app>
