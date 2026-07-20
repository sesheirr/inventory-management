<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $categoryId = $request->input('category_id');
        $roomId = $request->input('room_id');
        $condition = $request->input('condition');

        $filteredProducts = Product::with(['category', 'room']);

        if ($categoryId) {
            $filteredProducts->where('category_id', $categoryId);
        }

        if ($roomId) {
            $filteredProducts->where('room_id', $roomId);
        }

        if ($condition && $condition !== 'all') {
            $filteredProducts->where('status', $condition);
        }

        $totalProducts = (clone $filteredProducts)->count();
        $totalActive = (clone $filteredProducts)->where('status', 'active')->count();
        $totalDamaged = (clone $filteredProducts)->where('status', '<>', 'active')->count();

        $totalCategories = Category::count();
        $totalRooms = Room::count();

        $mutationQuery = Mutation::with(['product.category', 'product.room']);

        if ($dateFrom) {
            $mutationQuery->where('mutation_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $mutationQuery->where('mutation_date', '<=', $dateTo);
        }

        if ($categoryId) {
            $mutationQuery->whereHas('product', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            });
        }

        if ($roomId) {
            $mutationQuery->where(function ($query) use ($roomId) {
                $query->where('from_room_id', $roomId)
                      ->orWhere('to_room_id', $roomId);
            });
        }

        if ($condition && $condition !== 'all') {
            $mutationQuery->whereHas('product', function ($query) use ($condition) {
                $query->where('status', $condition);
            });
        }

        $totalMutations = (clone $mutationQuery)->count();

        $categoryCounts = (clone $filteredProducts)
            ->get()
            ->map(function (Product $product) {
                return $product->category?->name ?? $product->category;
            })
            ->filter()
            ->countBy()
            ->sortDesc();

        $categoryLabels = $categoryCounts->keys()->toArray();
        $categoryValues = $categoryCounts->values()->toArray();

        $driver = $mutationQuery->getQuery()->getConnection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "strftime('%Y-%m', mutation_date) as month"
            : "DATE_FORMAT(mutation_date, '%Y-%m') as month";

        $mutationByMonth = (clone $mutationQuery)
            ->selectRaw("{$monthExpression}, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $mutationLabels = $mutationByMonth->pluck('month')->toArray();
        $mutationValues = $mutationByMonth->pluck('total')->toArray();

        return view('dashboard', compact(
            'categories',
            'rooms',
            'dateFrom',
            'dateTo',
            'categoryId',
            'roomId',
            'condition',
            'totalProducts',
            'totalCategories',
            'totalRooms',
            'totalMutations',
            'totalActive',
            'totalDamaged',
            'categoryLabels',
            'categoryValues',
            'mutationLabels',
            'mutationValues'
        ));
    }

    public function exportExcel(Request $request)
    {
        $filteredProducts = Product::with(['category', 'room']);

        if ($request->filled('category_id')) {
            $filteredProducts->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('room_id')) {
            $filteredProducts->where('room_id', $request->input('room_id'));
        }

        if ($request->filled('condition') && $request->input('condition') !== 'all') {
            $filteredProducts->where('status', $request->input('condition'));
        }

        $products = $filteredProducts->get();

        $filename = 'inventory_report_' . now()->format('YmdHis') . '.csv';

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Category', 'Room', 'Status', 'Stock', 'Created At']);

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->name,
                    $product->category?->name ?? $product->category,
                    $product->room?->name,
                    $product->status,
                    $product->stock,
                    $product->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
