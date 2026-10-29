<x-layouts.app title="Manage Students">
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2>Manage Students</h2>
        <div class="election-status-bar">
            @if($totalPendingCount > 0)
            <div class="status-dot"></div>
            <span class="status-label">{{ $totalPendingCount }} pending verification</span>
            @else
            <span style="color:var(--text-muted);">No pending registrations</span>
            @endif
        </div>
    </div>
</div>

<!-- Block Dropdown -->
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
    <label style="color:var(--text-muted);font-size:.82rem;white-space:nowrap;">Filter by Block:</label>
    <select onchange="window.location=this.value" class="form-control-dark" style="max-width:260px;background:#071e1c;color:#fff;border:1px solid #1a3c39;padding:8px 12px;border-radius:6px;">
        <option value="{{ route('admin.students.index') }}" {{ $activeBlockId === null ? 'selected' : '' }}>All Blocks</option>
        @foreach($allBlocks as $blk)
        @php
            $label = 'BSIT ' . $blk->section . ' — Year ' . $blk->year_level . ', Sem ' . $blk->semester;
            if ($blk->track) {
                $label .= ' — ' . $blk->majorName();
            }
        @endphp
        <option value="{{ route('admin.students.index', ['block_id' => $blk->id]) }}" {{ $activeBlockId == $blk->id ? 'selected' : '' }}>
            {{ $label }}
        </option>
        @endforeach
    </select>
</div>

<!-- Pending Verification -->
@if($pending->isNotEmpty())
<div class="dark-card mb-4">
    <div class="card-head" style="color:#f0a500;">
        <i class="bi bi-hourglass-split me-2"></i>Pending Verification ({{ $totalPendingCount }})
    </div>
    @foreach($pending as $blockId => $students)
    @php($block = $students->first()?->block)
    <div style="padding:10px 18px 4px;border-top:1px solid var(--card-border);">
        <span style="font-size:.75rem;font-weight:700;color:var(--gold);">
            @if($block)
            BSIT {{ $block->section }} — Year {{ $block->year_level }}, Sem {{ $block->semester }}
            @if($block->track)
            <span style="color:var(--text-muted);font-weight:400;">({{ $block->majorName() }})</span>
            @endif
            @else
            Unassigned
            @endif
        </span>
    </div>
    <div style="overflow-x:auto;">
        <table class="dark-table">
            <thead><tr>
                <th>Student ID</th><th>Full Name</th><th>Email</th><th>Registered</th><th>Actions</th>
            </tr></thead>
            <tbody>
            @foreach($students as $s)
            <tr>
                <td style="color:var(--gold);font-weight:700;">{{ $s->student_id }}</td>
                <td>{{ $s->name }}</td>
                <td style="color:var(--text-muted);font-size:.8rem;">{{ $s->email }}</td>
                <td style="color:var(--text-muted);font-size:.78rem;">{{ $s->created_at?->format('M d, Y') }}</td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <form method="POST" action="{{ route('admin.students.verify', $s) }}" style="display:inline;">
                            @csrf
                            <button class="btn-sm-icon" style="color:#2ecc71;border-color:#2ecc71;background:transparent;border:1px solid #2ecc71;padding:4px 8px;border-radius:4px;" title="Approve">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.students.reject', $s) }}" style="display:inline;"
                              onsubmit="return confirm('Reject and remove this registration?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn-sm-icon danger" style="color:#e74c3c;border-color:#e74c3c;background:transparent;border:1px solid #e74c3c;padding:4px 8px;border-radius:4px;" title="Reject"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
</div>
@endif

<!-- Verified Students -->
<div class="dark-card">
    <div class="card-head">
        <i class="bi bi-people me-2"></i>
        @if($activeBlockId)
        @php($activeBlock = $allBlocks->firstWhere('id', $activeBlockId))
        Verified Students — BSIT {{ $activeBlock?->section ?? 'Selected Block' }}
        @if($activeBlock)
        — Year {{ $activeBlock->year_level }}, Sem {{ $activeBlock->semester }}@if($activeBlock->track) — {{ $activeBlock->majorName() }}@endif
        @endif
        @else
        Verified Students ({{ $totalVerifiedCount }})
        @endif
    </div>
    @if($verified->isEmpty())
    <div style="text-align:center;color:var(--text-muted);padding:32px;">
        {{ $activeBlockId ? 'No verified students in this block.' : 'No verified students yet.' }}
    </div>
    @else
    @foreach($verified as $blockId => $students)
    @php($block = $students->first()?->block)
    <div style="padding:10px 18px 4px;border-top:1px solid var(--card-border);">
        <span style="font-size:.75rem;font-weight:700;color:var(--gold);">
            @if($block)
            BSIT {{ $block->section }} — Year {{ $block->year_level }}, Sem {{ $block->semester }}
            @if($block->track)
            <span style="color:var(--text-muted);font-weight:400;">({{ $block->majorName() }})</span>
            @endif
            @else
            Unassigned
            @endif
            <span style="color:var(--text-muted);font-weight:400;font-size:.7rem;"> — {{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }}</span>
        </span>
    </div>
    <div style="overflow-x:auto;">
        <table class="dark-table">
            <thead><tr>
                <th>Student ID</th><th>Full Name</th><th>Email</th><th>Status</th><th>Actions</th>
            </tr></thead>
            <tbody>
            @foreach($students as $s)
            <tr>
                <td style="color:var(--gold);font-weight:700;">{{ $s->student_id }}</td>
                <td>{{ $s->name }}</td>
                <td style="color:var(--text-muted);font-size:.8rem;">{{ $s->email }}</td>
                <td>
                    @if($s->is_active)
                    <span style="color:#2ecc71;font-size:.78rem;font-weight:700;"><i class="bi bi-check-circle me-1"></i>Active</span>
                    @else
                    <span style="color:#e74c3c;font-size:.78rem;font-weight:700;"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                    @endif
                </td>
                <td>
                    <form method="POST" action="{{ route('admin.students.destroy', $s) }}" style="display:inline;"
                          onsubmit="return confirm('Remove {{ addslashes($s->name) }} from the system?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn-sm-icon danger" style="color:#e74c3c;border-color:#e74c3c;background:transparent;border:1px solid #e74c3c;padding:4px 8px;border-radius:4px;" title="Remove Student">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
    @endif
</div>
</x-layouts.app>
