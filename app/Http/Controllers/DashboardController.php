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
        $pendingMutationsCount = Mutation::where('status', 'pending')->count();

        $lowStockProducts = Product::with(['room', 'category'])
            ->where(function ($q) {
                $q->where('stock', '<=', 5)
                  ->orWhere('status', 'out_of_stock');
            })
            ->orderBy('stock', 'asc')
            ->limit(6)
            ->get();

        $recentProducts = Product::with(['room', 'category', 'media'])
            ->latest()
            ->limit(5)
            ->get();

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
            'pendingMutationsCount',
            'lowStockProducts',
            'recentProducts',
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