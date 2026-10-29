<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitBallotRequest;
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
        DB::transaction(function () use ($request, $election, $selections): void {
            $lockedElection = Election::query()->lockForUpdate()->findOrFail($election->id);
            abort_if(VoteReceipt::query()->where('election_id', $lockedElection->id)->where('voter_id', $request->user()->id)->exists(), 422, 'You have already submitted this ballot.');
            $positions = $lockedElection->positions()->with(['candidates' => fn ($query) => $query->where('is_active', true)])->get()->keyBy('id');
            foreach ($positions as $position) {
                $selected = $selections[$position->id] ?? [];
                abort_if($position->is_required && $selected === [], 422, "{$position->name} requires a selection.");
                abort_if(count($selected) > $position->max_votes || $position->candidates->whereIn('id', $selected)->count() !== count($selected), 422, 'Invalid ballot selection.');
            }
            VoteReceipt::create(['election_id' => $lockedElection->id, 'voter_id' => $request->user()->id, 'block_id' => $request->user()->block_id, 'receipt_reference' => (string) Str::uuid(), 'payload_hash' => hash('sha256', json_encode($selections, JSON_THROW_ON_ERROR)), 'status' => 'confirmed']);
        });

        return redirect()->route('voter.elections')->with('status', 'Your ballot has been submitted. Your vote choices were not stored in the receipt.');
    }

    private function eligible(Request $request, Election $election): bool
    {
        return $request->user()->is_verified && $request->user()->block_id === $election->block_id;
    }
}
