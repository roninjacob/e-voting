<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCicsScCandidateRequest;
use App\Models\CicsScCandidate;
use App\Models\CicsScElection;
use App\Models\CicsScPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CicsScCandidateController extends Controller
{
    public function index(CicsScElection $cicsScElection): View
    {
        return view('admin.cics-sc.candidates', ['election' => $cicsScElection->load('positions.candidates')]);
    }

    public function store(StoreCicsScCandidateRequest $request, CicsScElection $cicsScElection, CicsScPosition $cicsScPosition): RedirectResponse
    {
        abort_unless($cicsScPosition->cics_sc_election_id === $cicsScElection->id && $cicsScElection->status === 'draft', 404);
        $data = $request->safe()->except('photo');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['photo_path'] = $request->file('photo')->store('cics-sc-candidate-photos', 'public');
        $cicsScPosition->candidates()->create($data);

        return back()->with('status', 'CICS-SC candidate added.');
    }

    public function destroy(CicsScElection $cicsScElection, CicsScCandidate $cicsScCandidate): RedirectResponse
    {
        abort_unless($cicsScCandidate->position->cics_sc_election_id === $cicsScElection->id && $cicsScElection->status === 'draft', 404);
        Storage::disk('public')->delete($cicsScCandidate->photo_path);
        $cicsScCandidate->delete();

        return back()->with('status', 'CICS-SC candidate removed.');
    }
}
