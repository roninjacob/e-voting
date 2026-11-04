<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\VoteReceipt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VotingRecordController extends Controller
{
    public function index(Request $request): View
    {
        $receipts = VoteReceipt::query()
            ->where('voter_id', $request->user()->id)
            ->with(['election.block'])
            ->latest('submitted_at')
            ->get();

        return view('voter.records', ['receipts' => $receipts]);
    }
}
