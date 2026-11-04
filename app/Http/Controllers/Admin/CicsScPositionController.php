<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCicsScPositionRequest;
use App\Models\CicsScElection;
use App\Models\CicsScPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CicsScPositionController extends Controller
{
    public function index(CicsScElection $cicsScElection): View
    {
        return view('admin.cics-sc.positions', ['election' => $cicsScElection->load('positions.candidates')]);
    }

    public function store(StoreCicsScPositionRequest $request, CicsScElection $cicsScElection): RedirectResponse
    {
        abort_unless($cicsScElection->status === 'draft', 422);
        $cicsScElection->positions()->create($request->validated());

        return back()->with('status', 'CICS-SC position added.');
    }

    public function destroy(CicsScElection $cicsScElection, CicsScPosition $cicsScPosition): RedirectResponse
    {
        abort_unless($cicsScPosition->cics_sc_election_id === $cicsScElection->id && $cicsScElection->status === 'draft', 404);
        $cicsScPosition->delete();

        return back()->with('status', 'CICS-SC position removed.');
    }
}
