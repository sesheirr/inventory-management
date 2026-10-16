<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $action = trim((string) $request->input('action', ''));

        $activityLogs = ActivityLog::with('user')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('model_type', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($action !== '' && $action !== 'all', fn ($q) => $q->where('action', $action))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('activity-logs.index', compact('activityLogs', 'search', 'action'));
    }
}
