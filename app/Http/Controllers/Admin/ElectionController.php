<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreElectionRequest;
use App\Models\AuditLog;
use App\Models\Block;
use App\Models\Election;
use App\Models\ElectionResultSnapshot;
use App\Models\ElectionStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ElectionController extends Controller
{
    public function index(): View
    {
        return view('admin.elections', ['elections' => Election::query()->with('block')->latest()->get(), 'blocks' => Block::query()->orderBy('year_level')->orderBy('section')->get()]);
    }

    public function store(StoreElectionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if ($validated['scope'] === 'all_cics') {
            $validated['block_id'] = null;
        }

        Election::create([...$validated, 'created_by' => $request->user()->id]);

        return back()->with('status', 'Draft election created.');
    }

    public function update(StoreElectionRequest $request, Election $election): RedirectResponse
    {
        abort_unless($election->status === 'draft', 422, 'Only draft elections can be edited.');
        $validated = $request->validated();
        if ($validated['scope'] === 'all_cics') {
            $validated['block_id'] = null;
        }
        $election->update($validated);

        return back()->with('status', 'Election updated.');
    }

    public function destroy(Election $election): RedirectResponse
    {
        abort_unless(in_array($election->status, ['draft', 'configured'], true), 422);
        $election->delete();

        return back()->with('status', 'Election deleted.');
    }

    public function transition(Request $request, Election $election): RedirectResponse
    {
        $next = ['draft' => 'configured', 'configured' => 'open', 'open' => 'closed', 'closed' => 'published'][$election->status] ?? null;
        abort_unless($next, 422, 'This election cannot be advanced.');
        if ($next === 'open') {
            abort_unless($election->positions()->whereHas('candidates', fn ($query) => $query->where('is_active', true))->exists(), 422, 'Add at least one active candidate before opening.');
        }
        DB::transaction(function () use ($election, $next, $request): void {
            $from = $election->status;
            $election->update(['status' => $next]);
            ElectionStatusLog::create(['election_id' => $election->id, 'from_status' => $from, 'to_status' => $next, 'changed_by' => $request->user()->id]);

            if ($next === 'published') {
                ElectionResultSnapshot::create([
                    'election_id' => $election->id,
                    'results' => $this->resultSnapshot($election),
                    'published_by' => $request->user()->id,
                    'published_at' => now(),
                ]);
            }

            if (in_array($next, ['closed', 'published'], true)) {
                AuditLog::create([
                    'actor_id' => $request->user()->id,
                    'actor_role' => $request->user()->role,
                    'election_id' => $election->id,
                    'action' => $next === 'closed' ? 'election_closed' : 'results_published',
                    'metadata' => ['from_status' => $from, 'to_status' => $next],
                    'ip_address' => $request->ip(),
                ]);
            }
        });

        return back()->with('status', "Election is now {$next}.");
    }

    /**
     * @return array{receipt_count: int, positions: array<int, array{id: int, name: string, votes: array<int, array{candidate_id: int, candidate_name: string, votes: int}>}>}
     */
    private function resultSnapshot(Election $election): array
    {
        $positions = $election->positions()->with('candidates')->get()->map(function ($position) use ($election): array {
            $voteCounts = $position->ballotVotes()
                ->select('candidate_id', DB::raw('COUNT(*) as votes'))
                ->where('election_id', $election->id)
                ->groupBy('candidate_id')
                ->pluck('votes', 'candidate_id');

            return [
                'id' => $position->id,
                'name' => $position->name,
                'votes' => $position->candidates->map(fn ($candidate): array => [
                    'candidate_id' => $candidate->id,
                    'candidate_name' => $candidate->full_name,
                    'votes' => (int) ($voteCounts[$candidate->id] ?? 0),
                ])->values()->all(),
            ];
        })->all();

        return [
            'receipt_count' => $election->voteReceipts()->count(),
            'positions' => $positions,
        ];
    }
}
