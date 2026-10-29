<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePositionRequest;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(Election $election): View
    {
        return view('admin.positions', ['election' => $election->load('positions.candidates')]);
    }

    public function store(StorePositionRequest $request, Election $election): RedirectResponse
    {
        abort_unless($election->status === 'draft', 422);
        $election->positions()->create($request->validated());

        return back()->with('status', 'Position added.');
    }

    public function destroy(Election $election, Position $position): RedirectResponse
    {
        abort_unless($position->election_id === $election->id && $election->status === 'draft', 404);
        $position->delete();

        return back()->with('status', 'Position removed.');
    }
}
