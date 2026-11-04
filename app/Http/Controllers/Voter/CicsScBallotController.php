<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitCicsScBallotRequest;
use App\Models\CicsScElection;
use App\Models\CicsScVoteReceipt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CicsScBallotController extends Controller
{
    public function show(Request $request, CicsScElection $cicsScElection): View
    {
        abort_unless(CicsScElection::userIsEligible($request->user()) && $cicsScElection->status === 'open', 404);
        abort_if($cicsScElection->voteReceipts()->where('voter_id', $request->user()->id)->exists(), 422, 'You have already submitted this ballot.');

        return view('voter.cics-sc.ballot', ['election' => $cicsScElection->load(['positions' => fn ($query) => $query->with(['candidates' => fn ($candidateQuery) => $candidateQuery->where('is_active', true)->inRandomOrder()])])]);
    }

    public function store(SubmitCicsScBallotRequest $request, CicsScElection $cicsScElection): RedirectResponse
    {
        abort_unless(CicsScElection::userIsEligible($request->user()) && $cicsScElection->status === 'open', 404);
        $selections = $request->validated('selections');
        DB::transaction(function () use ($request, $cicsScElection, $selections): void {
            $lockedElection = CicsScElection::query()->lockForUpdate()->findOrFail($cicsScElection->id);
            abort_if(CicsScVoteReceipt::query()->where('cics_sc_election_id', $lockedElection->id)->where('voter_id', $request->user()->id)->exists(), 422, 'You have already submitted this ballot.');
            $positions = $lockedElection->positions()->with(['candidates' => fn ($query) => $query->where('is_active', true)])->get()->keyBy('id');
            foreach ($positions as $position) {
                $selected = $selections[$position->id] ?? [];
                abort_if($position->is_required && $selected === [], 422, "{$position->name} requires a selection.");
                abort_if(count($selected) > $position->max_votes || $position->candidates->whereIn('id', $selected)->count() !== count($selected), 422, 'Invalid ballot selection.');
            }
            CicsScVoteReceipt::create(['cics_sc_election_id' => $lockedElection->id, 'voter_id' => $request->user()->id, 'block_id' => $request->user()->block_id, 'receipt_reference' => (string) Str::uuid(), 'payload_hash' => hash('sha256', json_encode($selections, JSON_THROW_ON_ERROR)), 'status' => 'confirmed']);
        });

        return redirect()->route('cics-sc.elections')->with('status', 'Your CICS-SC ballot has been submitted. Your vote choices were not stored in the receipt.');
    }
}
