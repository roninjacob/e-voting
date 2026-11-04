<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCicsScElectionRequest;
use App\Models\CicsScElection;
use App\Models\CicsScElectionStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CicsScElectionController extends Controller
{
    public function index(): View
    {
        return view('admin.cics-sc.elections', ['elections' => CicsScElection::query()->latest()->get()]);
    }

    public function store(StoreCicsScElectionRequest $request): RedirectResponse
    {
        CicsScElection::create([...$request->validated(), 'created_by' => $request->user()->id]);

        return back()->with('status', 'CICS-SC draft election created.');
    }

    public function update(StoreCicsScElectionRequest $request, CicsScElection $cicsScElection): RedirectResponse
    {
        abort_unless($cicsScElection->status === 'draft', 422, 'Only draft elections can be edited.');
        $cicsScElection->update($request->validated());

        return back()->with('status', 'CICS-SC election updated.');
    }

    public function destroy(CicsScElection $cicsScElection): RedirectResponse
    {
        abort_unless(in_array($cicsScElection->status, ['draft', 'configured'], true), 422);
        $cicsScElection->delete();

        return back()->with('status', 'CICS-SC election deleted.');
    }

    public function transition(Request $request, CicsScElection $cicsScElection): RedirectResponse
    {
        $next = ['draft' => 'configured', 'configured' => 'open', 'open' => 'closed', 'closed' => 'published'][$cicsScElection->status] ?? null;
        abort_unless($next, 422, 'This election cannot be advanced.');
        if ($next === 'open') {
            abort_unless($cicsScElection->positions()->whereHas('candidates', fn ($query) => $query->where('is_active', true))->exists(), 422, 'Add at least one active candidate before opening.');
        }
        DB::transaction(function () use ($cicsScElection, $next, $request): void {
            $from = $cicsScElection->status;
            $cicsScElection->update(['status' => $next]);
            CicsScElectionStatusLog::create(['cics_sc_election_id' => $cicsScElection->id, 'from_status' => $from, 'to_status' => $next, 'changed_by' => $request->user()->id]);
        });

        return back()->with('status', "CICS-SC election is now {$next}.");
    }
}
