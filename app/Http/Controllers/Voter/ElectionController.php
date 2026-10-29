<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ElectionController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $elections = Election::query()->where('block_id', $user->block_id)->with('block')->orderByDesc('starts_at')->get();
        $votedElectionIds = $user->voteReceipts()->pluck('election_id')->all();

        return view('voter.elections', compact('elections', 'votedElectionIds'));
    }
}
