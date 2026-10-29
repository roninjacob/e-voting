<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Voter\ElectionController;
use App\Models\Election;
use App\Models\ElectionStatusLog;
use App\Models\VoteReceipt;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        if (! $user->isElectionManager()) {
            return app(ElectionController::class)->index($request);
        }

        return view('admin.dashboard', [
            'elections' => Election::query()->with('block')->latest()->get(),
            'recentLogs' => ElectionStatusLog::query()->with(['election', 'changer'])->latest('changed_at')->take(8)->get(),
            'receiptCount' => VoteReceipt::count(),
        ]);
    }
}
