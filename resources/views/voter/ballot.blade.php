<x-layouts.app title="My Ballot">
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1>My Ballot</h1>
        <div class="election-status-bar">
            <div class="status-dot"></div>
            <span class="status-label">{{ $election->title }}</span>
        </div>
    </div>
    <button type="button" id="btnConfirm" class="dashboard-button" data-bs-toggle="modal" data-bs-target="#confirmModal">
        <i class="bi bi-send"></i> Submit Ballot
    </button>
</div>

<form id="ballotForm" method="POST" action="{{ route('voter.ballot.store', $election) }}">
    @csrf

    @foreach($election->positions as $position)
    <div class="dark-card mb-4">
        <div class="card-head">
            {{ $position->name }}
            <span style="font-size:.875rem;color:var(--text-muted);font-weight:400;text-transform:none;">
                Select up to {{ $position->max_votes }}
                @if($position->is_required) <span style="color:#e74c3c;">*</span> @endif
            </span>
        </div>
        <div class="card-body-inner" style="padding:20px;">
            @if($position->candidates->isEmpty())
                <p style="color:var(--text-muted);">No candidates for this position.</p>
            @else
            <div class="row g-3 candidate-group"
                 data-max="{{ $position->max_votes }}"
                 data-required="{{ $position->is_required ? '1' : '0' }}"
                 data-position="{{ $position->name }}">
                @foreach($position->candidates as $candidate)
                <div class="col-6 col-md-4 col-lg-3">
                    <label class="candidate-card w-100 h-100 text-center p-3 d-block" data-id="{{ $candidate->id }}" style="cursor:pointer;background:#061a18;border:1px solid #1a3c39;border-radius:8px;transition:all .2s;">
                        <input type="checkbox"
                               name="selections[{{ $position->id }}][]"
                               value="{{ $candidate->id }}"
                               class="visually-hidden candidate-checkbox">
                        @if($candidate->photo_path)
                        <img src="{{ asset($candidate->photo_path) }}"
                             class="rounded-circle mb-2" style="width:64px;height:64px;object-fit:cover;" alt="{{ $candidate->full_name }}">
                        @else
                        <div class="mb-2">
                            <i class="bi bi-person-circle" style="font-size:2.5rem;color:var(--text-muted);"></i>
                        </div>
                        @endif
                        <div style="font-size:.9375rem;font-weight:700;color:#fff;" class="fw-semibold">{{ $candidate->full_name }}</div>
                        @if($candidate->party)
                        <div style="font-size:.8125rem;color:var(--gold);">{{ $candidate->party }}</div>
                        @endif
                    </label>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    @endforeach

    <div class="text-end mb-4">
        <button type="button" id="btnConfirmBottom" class="dashboard-button" style="padding:12px 32px;font-size:.95rem;" data-bs-toggle="modal" data-bs-target="#confirmModal">
            <i class="bi bi-send"></i> Submit Ballot
        </button>
    </div>
</form>

<!-- Confirm Modal -->
<div class="modal fade modal-dark" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:#071e1c;color:#fff;border:1px solid #1a3c39;">
            <div class="modal-header" style="border-bottom:1px solid #1a3c39;">
                <h5 class="modal-title">Confirm Your Votes!</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:.9375rem;color:var(--text-muted);">
                    Are you sure you want to finalize your selections?<br>
                    Changes cannot be made after submission.
                </p>
                <div id="confirmSummary"></div>
            </div>
            <div class="modal-footer flex-column gap-2" style="border-top:1px solid #1a3c39;">
                <button type="button" id="btnSubmitVotes" class="btn btn-danger w-100 justify-content-center" style="padding:12px;">
                    Yes, Submit Votes
                </button>
                <button type="button" class="btn btn-outline-secondary w-100 justify-content-center" data-bs-dismiss="modal" style="padding:11px;color:#fff;border-color:#1a3c39;">
                    Back to Ballot
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const confirmModal = document.getElementById('confirmModal');
    if (confirmModal) {
        document.body.appendChild(confirmModal);
    }

    document.querySelectorAll('.candidate-group').forEach(group => {
        const max = parseInt(group.dataset.max) || 1;
        group.querySelectorAll('.candidate-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const checked = group.querySelectorAll('.candidate-checkbox:checked');
                if (checked.length > max) {
                    cb.checked = false;
                    alert('You can select up to ' + max + ' candidate(s) for this position.');
                }
                group.querySelectorAll('.candidate-card').forEach(card => {
                    const input = card.querySelector('.candidate-checkbox');
                    if (input.checked) {
                        card.style.borderColor = '#f0a500';
                        card.style.background = '#0a2a27';
                    } else {
                        card.style.borderColor = '#1a3c39';
                        card.style.background = '#061a18';
                    }
                });
            });
        });
    });

    document.getElementById('btnSubmitVotes')?.addEventListener('click', () => {
        document.getElementById('ballotForm').submit();
    });
});
</script>
@endpush
</x-layouts.app>
