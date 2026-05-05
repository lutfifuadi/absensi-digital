<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs   = $query->paginate(25)->withQueryString();
        $users  = User::orderBy('name')->get(['id', 'name']);
        $modules = ActivityLog::distinct('module')->pluck('module')->filter()->values();
        $actions = ActivityLog::distinct('action')->pluck('action')->filter()->values();

        return view('admin.activity-log', compact('logs', 'users', 'modules', 'actions'));
    }

    /**
     * AJAX: detail row (old vs new JSON diff).
     */
    public function show(ActivityLog $activityLog)
    {
        return response()->json([
            'old_data' => $activityLog->old_data,
            'new_data' => $activityLog->new_data,
        ]);
    }

    /**
     * Hapus SEMUA log (Truncate table).
     */
    public function destroyAll()
    {
        ActivityLog::truncate();
        return redirect()->back()->with('success', 'Semua log aktivitas berhasil dihapus.');
    }

    /**
     * Hapus log lama (older than N days) — opsional via console command.
     */
    public function purge(int $days = 90): int
    {
        return ActivityLog::where('created_at', '<', now()->subDays($days))->delete();
    }
}
