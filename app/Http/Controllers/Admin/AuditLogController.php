<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with(['actor', 'election']);

        if ($request->filled('election_id')) {
            $query->where('election_id', $request->input('election_id'));
        }

        $logs = $query->latest('created_at')->paginate(20);

        return view('admin.audit-log', ['logs' => $logs]);
    }
}
