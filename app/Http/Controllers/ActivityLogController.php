<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManageUsers(), 403);

        $activityLogs = ActivityLog::query()
            ->with('user')
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('activity-logs.table-index', [
            'activityLogs' => $activityLogs,
        ]);
    }
}
