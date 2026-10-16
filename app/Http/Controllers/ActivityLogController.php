<?php

/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Comprehensive Audit & Activity Log Controller
 *
 * Provides rich multi-criteria filtering, dynamic sorting, pagination, and summary KPIs
 * across all tracked user and system actions.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 *
 * @co-collaborator msayib <https://github.com/msayib>
 */

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $action = trim((string) $request->input('action', ''));
        $subjectType = trim((string) $request->input('subject_type', ''));
        $userId = $request->filled('user_id') ? (int) $request->input('user_id') : null;
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        $allowedSortFields = ['created_at', 'action', 'subject_type', 'id'];
        $sortBy = in_array($request->input('sort_by'), $allowedSortFields, true)
            ? (string) $request->input('sort_by')
            : 'created_at';
        $sortDir = strtolower((string) $request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, [10, 20, 30, 50, 100], true)) {
            $perPage = 20;
        }

        $query = ActivityLog::with('user')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('description', 'like', "%{$search}%")
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('subject_type', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->when($action !== '' && $action !== 'all', fn ($q) => $q->where('action', $action))
            ->when($subjectType !== '' && $subjectType !== 'all', function ($q) use ($subjectType) {
                $q->where('subject_type', 'like', "%{$subjectType}%");
            })
            ->when($userId !== null && $userId > 0, fn ($q) => $q->where('user_id', $userId))
            ->when($dateFrom !== '', fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id', $sortDir);

        $activityLogs = $query->paginate($perPage)->withQueryString();

        $summary = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', now()->toDateString())->count(),
            'mutations' => ActivityLog::where('subject_type', 'like', '%Mutation%')->count(),
            'products' => ActivityLog::where('subject_type', 'like', '%Product%')->count(),
        ];

        $availableActions = ActivityLog::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->filter()
            ->values();

        $users = User::query()
            ->select(['id', 'name', 'role'])
            ->orderBy('name')
            ->get();

        $filters = [
            'search' => $search,
            'action' => $action,
            'subject_type' => $subjectType,
            'user_id' => $userId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'sort_by' => $sortBy,
            'sort_dir' => $sortDir,
            'per_page' => $perPage,
        ];

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Admin/ActivityLogs/Index', [
                'logs' => $activityLogs,
                'summary' => $summary,
                'filters' => $filters,
                'availableActions' => $availableActions,
                'users' => $users,
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'logs' => $activityLogs,
                'summary' => $summary,
                'filters' => $filters,
            ]);
        }

        return view('activity-logs.index', compact(
            'activityLogs',
            'summary',
            'filters',
            'availableActions',
            'users',
            'search',
            'action',
            'subjectType',
            'userId',
            'dateFrom',
            'dateTo',
            'sortBy',
            'sortDir',
            'perPage'
        ));
    }
}
