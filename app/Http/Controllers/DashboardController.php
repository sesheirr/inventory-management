<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ensure 6-month historical sample mutations exist on seeded Diskominfo environments
        if (
            Mutation::count() < 12
            && Product::where('kode_barang', 'like', 'DKI-GRT-%')->count() >= 15
        ) {
            app(ProductSeeder::class)->run();
        }

        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->where('stock', '>', 0)->count();
        $totalUnits = (int) Product::sum('stock');

        $totalCategories = Category::count();
        $totalRooms = Room::count();
        $activeRooms = Room::has('products')->count();

        $totalMutations = Mutation::count();
        $pendingMutationsCount = Mutation::where('status', 'pending')->count();

        $outOfStockCount = Product::where(function ($q) {
            $q->where('status', 'out_of_stock')
                ->orWhere('stock', '<=', 0);
        })->count();

        $lowStockCount = Product::where(function ($q) {
            $q->where('stock', '<=', 5)
                ->orWhere('status', 'out_of_stock');
        })->count();

        $healthyStockCount = Product::where('status', 'active')->where('stock', '>', 5)->count();
        $warningOnlyStockCount = Product::where('stock', '>', 0)->where('stock', '<=', 5)->count();
        $inactiveProductsCount = Product::where('status', 'inactive')->count();
        $stockHealthScore = $totalProducts > 0
            ? (int) round(($healthyStockCount / $totalProducts) * 100)
            : 100;

        $stockHealth = [
            'healthy' => $healthyStockCount,
            'low' => $warningOnlyStockCount,
            'out' => $outOfStockCount,
            'inactive' => $inactiveProductsCount,
            'score' => $stockHealthScore,
        ];

        $approvedMutationsCount = Mutation::where('status', 'approved')->count();
        $rejectedMutationsCount = Mutation::where('status', 'rejected')->count();

        $mutationAnalytics = [
            'approved_count' => $approvedMutationsCount,
            'pending_count' => $pendingMutationsCount,
            'rejected_count' => $rejectedMutationsCount,
            'approval_rate' => $totalMutations > 0
                ? (int) round(($approvedMutationsCount / $totalMutations) * 100)
                : 100,
            'masuk_units' => (int) Mutation::where('type', 'masuk')->sum('quantity'),
            'pindah_units' => (int) Mutation::where('type', 'pindah_ruang')->sum('quantity'),
            'keluar_units' => (int) Mutation::where('type', 'keluar')->sum('quantity'),
        ];

        // 6-Month Analytics Series for Semi-Gradient Dashboard Chart
        $monthlyAnalytics = collect(range(5, 0))->map(function (int $monthsAgo) {
            $monthDate = now()->startOfMonth()->subMonths($monthsAgo);
            $start = $monthDate->copy()->startOfMonth()->toDateString();
            $end = $monthDate->copy()->endOfMonth()->toDateString();

            $monthMutations = Mutation::whereBetween('mutation_date', [$start, $end]);

            $masukUnits = (int) (clone $monthMutations)->where('type', 'masuk')->sum('quantity');
            $pindahUnits = (int) (clone $monthMutations)->where('type', 'pindah_ruang')->sum('quantity');
            $keluarUnits = (int) (clone $monthMutations)->where('type', 'keluar')->sum('quantity');
            $txCount = (int) (clone $monthMutations)->count();
            $newProductsCount = (int) Product::whereBetween('created_at', [
                $monthDate->copy()->startOfMonth(),
                $monthDate->copy()->endOfMonth(),
            ])->count();

            return [
                'label' => $monthDate->translatedFormat('M'),
                'full_label' => $monthDate->translatedFormat('F Y'),
                'masuk_units' => $masukUnits,
                'pindah_units' => $pindahUnits,
                'keluar_units' => $keluarUnits,
                'total_units' => $masukUnits + $pindahUnits + $keluarUnits,
                'tx_count' => $txCount,
                'new_products' => $newProductsCount,
            ];
        })->values();

        $roomDistribution = Room::withCount('products')
            ->withSum('products as total_stock', 'stock')
            ->orderByDesc('products_count')
            ->limit(6)
            ->get();

        $categoryDistribution = Category::withCount('products')
            ->withSum('products as total_stock', 'stock')
            ->orderByDesc('products_count')
            ->limit(6)
            ->get();

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

        /** @var User|null $currentUser */
        $currentUser = $request->user();

        if (! $currentUser || ! $currentUser->isAdmin()) {
            $activityLogsQuery->where('user_id', $currentUser?->id);
        }

        $activityLogs = $activityLogsQuery->limit(8)->get();
        $recentLogs = $activityLogs;

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Dashboard/Index', [
                'metrics' => [
                    'totalProducts' => $totalProducts,
                    'activeProducts' => $activeProducts,
                    'totalUnits' => $totalUnits,
                    'totalRooms' => $totalRooms,
                    'activeRooms' => $activeRooms,
                    'totalCategories' => $totalCategories,
                    'lowStockCount' => $lowStockCount,
                    'outOfStockCount' => $outOfStockCount,
                    'totalMutations' => $totalMutations,
                    'pendingMutations' => $pendingMutationsCount,
                ],
                'mutationAnalytics' => $mutationAnalytics,
                'monthlyAnalytics' => $monthlyAnalytics,
                'stockHealth' => $stockHealth,
            ]);
        }

        return view('dashboard', compact(
            'totalProducts',
            'activeProducts',
            'totalUnits',
            'totalCategories',
            'totalRooms',
            'activeRooms',
            'totalMutations',
            'pendingMutationsCount',
            'outOfStockCount',
            'lowStockCount',
            'stockHealth',
            'mutationAnalytics',
            'monthlyAnalytics',
            'roomDistribution',
            'categoryDistribution',
            'lowStockProducts',
            'recentProducts',
            'activityLogs',
            'recentLogs'
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
