<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $activityLogs = ActivityLog::with('user')->latest()->get();

        return view('activity-logs.index', compact('activityLogs'));
    }
}
