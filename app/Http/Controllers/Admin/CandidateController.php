<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCandidateRequest;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CandidateController extends Controller
{
    public function index(Election $election): View
    {
        return view('admin.candidates', ['election' => $election->load('positions.candidates')]);
    }

    public function store(StoreCandidateRequest $request, Election $election, Position $position): RedirectResponse
    {
        abort_unless($position->election_id === $election->id && $election->status === 'draft', 404);
        $data = $request->safe()->except('photo');
        $data['is_active'] = $request->boolean('is_active', true);
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('candidate-photos');
        }
        $position->candidates()->create($data);

        return back()->with('status', 'Candidate added.');
    }

    public function destroy(Election $election, Candidate $candidate): RedirectResponse
    {
        abort_unless($candidate->position->election_id === $election->id && $election->status === 'draft', 404);
        $candidate->delete();

        return back()->with('status', 'Candidate removed.');
    }
}
