<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitBallotRequest;
use App\Models\AuditLog;
use App\Models\BallotVote;
use App\Models\Election;
use App\Models\VoteReceipt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BallotController extends Controller
{
    public function show(Request $request, Election $election): View
    {
        abort_unless($this->eligible($request, $election) && $election->status === 'open', 404);
        abort_if($election->voteReceipts()->where('voter_id', $request->user()->id)->exists(), 422, 'You have already submitted this ballot.');

        return view('voter.ballot', ['election' => $election->load(['positions' => fn ($query) => $query->with(['candidates' => fn ($candidateQuery) => $candidateQuery->where('is_active', true)->inRandomOrder()])])]);
    }

    public function store(SubmitBallotRequest $request, Election $election): RedirectResponse
    {
        abort_unless($this->eligible($request, $election) && $election->status === 'open', 404);
        $selections = $request->validated('selections');
        $user = $request->user();

        DB::transaction(function () use ($request, $election, $selections, $user): void {
            $lockedElection = Election::query()->lockForUpdate()->findOrFail($election->id);
            abort_if(VoteReceipt::query()->where('election_id', $lockedElection->id)->where('voter_id', $user->id)->exists(), 422, 'You have already submitted this ballot.');

            $positions = $lockedElection->positions()->with(['candidates' => fn ($query) => $query->where('is_active', true)])->get()->keyBy('id');
            foreach ($positions as $position) {
                $selected = array_values(array_unique(array_map('intval', $selections[$position->id] ?? [])));
                abort_if($position->is_required && $selected === [], 422, "{$position->name} requires a selection.");
                abort_if(count($selected) > $position->max_votes || $position->candidates->whereIn('id', $selected)->count() !== count($selected), 422, 'Invalid ballot selection.');
            }

            $receiptReference = (string) Str::uuid();
            $receipt = VoteReceipt::create([
                'election_id' => $lockedElection->id,
                'voter_id' => $user->id,
                'block_id' => $user->block_id,
                'receipt_reference' => $receiptReference,
                'payload_hash' => VoteReceipt::computePayloadHash($receiptReference),
                'status' => 'confirmed',
            ]);

            $ballotRows = [];
            foreach ($selections as $positionId => $selected) {
                foreach (array_values(array_unique(array_map('intval', $selected))) as $candidateId) {
                    $ballotRows[] = [
                        'id' => (string) Str::uuid(),
                        'election_id' => $lockedElection->id,
                        'position_id' => (int) $positionId,
                        'candidate_id' => $candidateId,
                    ];
                }
            }

            shuffle($ballotRows);
            if ($ballotRows !== []) {
                foreach (array_chunk($ballotRows, 100) as $chunk) {
                    BallotVote::query()->insert($chunk);
                }
            }

            AuditLog::create([
                'actor_id' => $user->id,
                'actor_role' => $user->role,
                'election_id' => $lockedElection->id,
                'action' => 'ballot_submitted',
                'metadata' => ['receipt_reference' => $receipt->receipt_reference],
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('voter.elections')->with('status', 'Your ballot has been submitted. Your vote choices were not stored in the receipt.');
    }

    private function eligible(Request $request, Election $election): bool
    {
        return $election->isEligible($request->user());
    }
}
