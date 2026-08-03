<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\Category;
use App\Models\Room;
use App\Models\Mutation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalRooms = Room::count();
        $totalMutations = Mutation::count();

        $activityLogsQuery = ActivityLog::with('user')->latest();

        if (! auth()->user()->isAdmin()) {
            $activityLogsQuery->where('user_id', auth()->id());
        }

        $activityLogs = $activityLogsQuery->limit(10)->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalRooms',
            'totalMutations',
            'activityLogs'
        ));
    }

    /**
     * Menghapus seluruh riwayat aktivitas
     */
    public function clearHistory()
    {
        ActivityLog::truncate();

        return redirect()->back()->with('success', 'Semua riwayat aktivitas berhasil dibersihkan!');
    }
}