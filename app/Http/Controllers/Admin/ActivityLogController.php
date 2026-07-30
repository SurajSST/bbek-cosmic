<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of system activity logs.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $action = $request->query('action');
        $sortBy = $request->query('sort', 'created_at');
        $sortDir = $request->query('dir', 'desc');

        $activityLogs = ActivityLog::with('user')
            ->when($search, function ($q) use ($search) {
                return $q->where(function ($sub) use ($search) {
                    $sub->where('user_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%");
                });
            })
            ->when($action && $action !== 'all', function ($q) use ($action) {
                return $q->where('action', $action);
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->withQueryString();

        $distinctActions = ActivityLog::distinct()->pluck('action')->filter()->values();

        return view('admin.activity_logs.index', compact(
            'activityLogs',
            'search',
            'action',
            'distinctActions',
            'sortBy',
            'sortDir'
        ));
    }
}
