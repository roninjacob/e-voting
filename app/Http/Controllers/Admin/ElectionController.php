<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreElectionRequest;
use App\Models\Block;
use App\Models\Election;
use App\Models\ElectionStatusLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ElectionController extends Controller
{
    public function index(): View
    {
        return view('admin.elections', ['elections' => Election::query()->with('block')->latest()->get(), 'blocks' => Block::query()->orderBy('year_level')->orderBy('section')->get()]);
    }

    public function store(StoreElectionRequest $request): RedirectResponse
    {
        Election::create([...$request->validated(), 'created_by' => $request->user()->id]);

        return back()->with('status', 'Draft election created.');
    }

    public function update(StoreElectionRequest $request, Election $election): RedirectResponse
    {
        abort_unless($election->status === 'draft', 422, 'Only draft elections can be edited.');
        $election->update($request->validated());

        return back()->with('status', 'Election updated.');
    }

    public function destroy(Election $election): RedirectResponse
    {
        abort_unless(in_array($election->status, ['draft', 'configured'], true), 422);
        $election->delete();

        return back()->with('status', 'Election deleted.');
    }

    public function transition(Request $request, Election $election): RedirectResponse
    {
        $next = ['draft' => 'configured', 'configured' => 'open', 'open' => 'closed', 'closed' => 'published'][$election->status] ?? null;
        abort_unless($next, 422, 'This election cannot be advanced.');
        if ($next === 'open') {
            abort_unless($election->positions()->whereHas('candidates', fn ($query) => $query->where('is_active', true))->exists(), 422, 'Add at least one active candidate before opening.');
        }
        DB::transaction(function () use ($election, $next, $request): void {
            $from = $election->status;
            $election->update(['status' => $next]);
            ElectionStatusLog::create(['election_id' => $election->id, 'from_status' => $from, 'to_status' => $next, 'changed_by' => $request->user()->id]);
        });

        return back()->with('status', "Election is now {$next}.");
    }
}
