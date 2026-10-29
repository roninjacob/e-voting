<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlockRequest;
use App\Models\Block;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BlockController extends Controller
{
    public function index(): View
    {
        return view('admin.blocks', ['blocks' => Block::query()->withCount('users')->orderBy('year_level')->orderBy('section')->get()]);
    }

    public function store(StoreBlockRequest $request): RedirectResponse
    {
        Block::create($request->validated());

        return back()->with('status', 'Block created.');
    }

    public function destroy(Block $block): RedirectResponse
    {
        abort_if($block->elections()->exists(), 422, 'Blocks with elections cannot be deleted.');
        $block->delete();

        return back()->with('status', 'Block removed.');
    }
}
