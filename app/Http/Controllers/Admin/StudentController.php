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
        $activeSection = $request->input('section');

        $pendingQuery = User::where('role', 'voter')->where('is_verified', false)->with('block');
        $verifiedQuery = User::where('role', 'voter')->where('is_verified', true)->with('block');

        if ($activeSection) {
            $pendingQuery->whereHas('block', fn ($q) => $q->where('section', $activeSection));
            $verifiedQuery->whereHas('block', fn ($q) => $q->where('section', $activeSection));
        }

        $pending = $pendingQuery->get()->groupBy(fn ($u) => $u->block?->section ?? 'Unassigned')->sortKeys();
        $verified = $verifiedQuery->get()->groupBy(fn ($u) => $u->block?->section ?? 'Unassigned')->sortKeys();
        $allBlocks = Block::orderBy('section')->get();

        $totalPendingCount = User::where('role', 'voter')->where('is_verified', false)->count();
        $totalVerifiedCount = User::where('role', 'voter')->where('is_verified', true)->count();

        return view('admin.students', compact('pending', 'verified', 'allBlocks', 'activeSection', 'totalPendingCount', 'totalVerifiedCount'));
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
