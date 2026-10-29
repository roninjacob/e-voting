<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublishedResultsController extends Controller
{
    public function show(Request $request, Election $election): View
    {
        abort_unless($election->status === 'published', 404);
        abort_unless($election->isEligible($request->user()), 403);

        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'actor_role' => $request->user()?->role,
            'election_id' => $election->id,
            'action' => 'results_viewed',
            'metadata' => ['view_type' => 'voter'],
            'ip_address' => $request->ip(),
        ]);

        $receiptCount = $election->voteReceipts()->count();
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
                    'votes' => (int) ($voteCounts[$candidate->id] ?? 0),
                ])
                ->sortByDesc('votes')
                ->values();
            $totalVotes = (int) $candidateRows->sum('votes');

            $positionStats[] = [
                'position' => $position,
                'candidate_rows' => $candidateRows,
                'total_votes' => $totalVotes,
                'unused_selections' => max(0, ($receiptCount * max(1, $position->max_votes)) - $totalVotes),
            ];
        }

        return view('voter.results', ['election' => $election, 'positionStats' => $positionStats, 'receiptCount' => $receiptCount]);
    }

    public function export(Request $request, Election $election): StreamedResponse
    {
        abort_unless($election->status === 'published', 404);
        abort_unless($election->isEligible($request->user()), 403);

        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'actor_role' => $request->user()?->role,
            'election_id' => $election->id,
            'action' => 'results_exported',
            'metadata' => ['format' => 'csv'],
            'ip_address' => $request->ip(),
        ]);

        $filename = 'election-'.$election->id.'-results.csv';

        return response()->streamDownload(function () use ($election): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['position', 'candidate', 'votes']);

            foreach ($election->positions()->with('candidates')->get() as $position) {
                $voteCounts = DB::table('ballot_votes')
                    ->select('candidate_id', DB::raw('COUNT(*) as votes'))
                    ->where('election_id', $election->id)
                    ->where('position_id', $position->id)
                    ->groupBy('candidate_id')
                    ->pluck('votes', 'candidate_id');
                $rows = $position->candidates
                    ->map(fn ($candidate): object => (object) [
                        'name' => $candidate->full_name,
                        'votes' => (int) ($voteCounts[$candidate->id] ?? 0),
                    ])
                    ->sortByDesc('votes');

                foreach ($rows as $row) {
                    fputcsv($handle, [$position->name, $row->name, $row->votes]);
                }
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
