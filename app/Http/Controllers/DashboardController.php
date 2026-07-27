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

        $activityLogs = ActivityLog::with('user')->latest()->limit(10)->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalRooms',
            'totalMutations',
            'activityLogs'
        ));
    }
}
