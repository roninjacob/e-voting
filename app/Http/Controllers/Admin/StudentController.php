<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $activeBlockId = $request->input('block_id');

        $pendingQuery = User::where('role', 'voter')->where('is_verified', false)->with('block');
        $verifiedQuery = User::where('role', 'voter')->where('is_verified', true)->with('block');

        if ($activeBlockId) {
            $pendingQuery->where('block_id', $activeBlockId);
            $verifiedQuery->where('block_id', $activeBlockId);
        }

        $pending = $pendingQuery->get()->groupBy(fn ($user) => $user->block_id ?? 'Unassigned')->sortKeys();
        $verified = $verifiedQuery->get()->groupBy(fn ($user) => $user->block_id ?? 'Unassigned')->sortKeys();
        $allBlocks = Block::query()->orderBy('year_level')->orderBy('semester')->orderBy('section')->orderBy('track')->get();

        $totalPendingCount = User::where('role', 'voter')->where('is_verified', false)->count();
        $totalVerifiedCount = User::where('role', 'voter')->where('is_verified', true)->count();

        return view('admin.students', compact('pending', 'verified', 'allBlocks', 'activeBlockId', 'totalPendingCount', 'totalVerifiedCount'));
    }

    public function verify(User $user): RedirectResponse
    {
        if ($user->role === 'voter') {
            $user->update(['is_verified' => true]);

            return back()->with('success', 'Student verified successfully.');
        }

        return back()->with('error', 'Invalid student record.');
    }

    public function reject(User $user): RedirectResponse
    {
        if ($user->role === 'voter' && ! $user->is_verified) {
            $user->delete();

            return back()->with('success', 'Student registration rejected and removed.');
        }

        return back()->with('error', 'Unable to reject student.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role === 'voter') {
            $user->delete();

            return back()->with('success', 'Student removed.');
        }

        return back()->with('error', 'Unable to remove user.');
    }
}
