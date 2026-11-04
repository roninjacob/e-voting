<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\CicsScElection;
use App\Models\CicsScVoteReceipt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CicsScElectionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isEligible = CicsScElection::userIsEligible($user);
        $elections = $isEligible
            ? CicsScElection::query()->orderByDesc('starts_at')->get()
            : collect();
        $votedElectionIds = $isEligible
            ? CicsScVoteReceipt::query()->where('voter_id', $user->id)->pluck('cics_sc_election_id')->all()
            : [];

        return view('voter.cics-sc.elections', compact('elections', 'isEligible', 'votedElectionIds'));
    }
}
