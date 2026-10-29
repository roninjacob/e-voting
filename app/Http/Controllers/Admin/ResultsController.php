<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Election;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResultsController extends Controller
{
    public function index(): View
    {
        $elections = Election::query()->with('block')->latest()->get();

        return view('admin.results', ['elections' => $elections, 'election' => null]);
    }

    public function show(Request $request, Election $election): View
    {
        abort_unless(in_array($election->status, ['closed', 'published'], true), 404);

        $eligibleVoters = User::query()
            ->where('role', 'voter')
            ->where('is_verified', true)
            ->where('is_active', true)
            ->when(! $election->isAllCics(), fn ($query) => $query->where('block_id', $election->block_id))
            ->count();
        $receiptCount = $election->voteReceipts()->count();

        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'actor_role' => $request->user()?->role,
            'election_id' => $election->id,
            'action' => 'results_viewed',
            'metadata' => ['view_type' => 'manager'],
            'ip_address' => $request->ip(),
        ]);

        $turnout = [
            'eligible' => $eligibleVoters,
            'cast' => $receiptCount,
            'percent' => $eligibleVoters > 0 ? round(($receiptCount / $eligibleVoters) * 100, 2) : 0,
        ];

        $positionStats = [];
        foreach ($election->positions()->with('candidates')->get() as $position) {
            $voteCounts = DB::table('ballot_votes')
                ->select('candidate_id', DB::raw('COUNT(*) as votes'))
                ->where('election_id', $election->id)
                ->where('position_id', $position->id)
                ->groupBy('candidate_id')
                ->pluck('votes', 'candidate_id');
            $candidateRows = $position->candidates
                ->map(fn ($candidate): object => (object) [
                    'candidate_id' => $candidate->id,
                    'name' => $candidate->full_name,
                    'is_active' => $candidate->is_active,
                    'votes' => (int) ($voteCounts[$candidate->id] ?? 0),
                ])
                ->sortByDesc('votes')
                ->values();

            $positionBallots = (int) $candidateRows->sum('votes');
            $maxAllowed = $receiptCount * max(1, $position->max_votes);
            $unusedSelections = max(0, $maxAllowed - $positionBallots);
            $topVotes = $candidateRows->max('votes') ?? 0;
            $ties = $candidateRows->filter(fn ($row) => (int) $row->votes === (int) $topVotes)->count();

            $positionStats[] = [
                'position' => $position,
                'candidate_rows' => $candidateRows,
                'position_ballots' => $positionBallots,
                'unused_selections' => $unusedSelections,
                'eligible' => $eligibleVoters,
                'reconciles' => $positionBallots <= $maxAllowed,
                'tie' => $topVotes > 0 && $ties > 1,
            ];
        }

        $reconciliation = true;
        foreach ($positionStats as $stat) {
            if (! $stat['reconciles']) {
                $reconciliation = false;
            }
        }

        return view('admin.results', [
            'elections' => Election::query()->with('block')->latest()->get(),
            'election' => $election,
            'turnout' => $turnout,
            'positionStats' => $positionStats,
            'reconciliation' => $reconciliation,
            'receiptCount' => $receiptCount,
        ]);
    }
}
