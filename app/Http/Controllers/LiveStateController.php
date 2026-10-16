<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveStateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->where('stock', '>', 0)->count();
        $lowStockProducts = Product::where('stock', '>', 0)->where('stock', '<=', 5)->count();
        $outOfStockProducts = Product::where(fn ($q) => $q->where('status', 'out_of_stock')->orWhere('stock', '<=', 0))->count();

        $totalCategories = Category::count();
        $totalRooms = Room::count();
        $totalMutations = Mutation::count();
        $pendingMutations = Mutation::where('status', 'pending')->count();

        $latestProductUpdated = (string) (Product::max('updated_at') ?? '');
        $latestMutationUpdated = (string) (Mutation::max('updated_at') ?? '');
        $latestProductRow = Product::with(['room', 'category'])->latest('updated_at')->first();

        $version = substr(sha1(implode('|', [
            $totalProducts,
            $activeProducts,
            $lowStockProducts,
            $outOfStockProducts,
            $totalCategories,
            $totalRooms,
            $totalMutations,
            $pendingMutations,
            $latestProductUpdated,
            $latestMutationUpdated,
        ])), 0, 16);

        /** @var User|null $user */
        $user = $request->user();
        $unreadCount = 0;
        $latestNotifications = [];

        if ($user) {
            try {
                $unreadCount = $user->unreadNotifications()->count();
                $latestNotifications = $user->unreadNotifications()
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(function ($n) {
                        $data = (array) $n->data;

                        return [
                            'id' => $n->id,
                            'title' => $data['title'] ?? 'Notifikasi Sistem',
                            'message' => $data['message'] ?? 'Pembaruan sistem inventaris.',
                            'icon' => $data['icon'] ?? 'bi-bell-fill',
                            'category' => $data['category'] ?? 'info',
                            'priority' => $data['priority'] ?? 'normal',
                            'go_url' => route('notifications.go', $n->id, false),
                            'mark_read_url' => route('notifications.markAsRead', $n->id, false),
                            'delete_url' => route('notifications.destroy', $n->id, false),
                            'created_at_human' => $n->created_at ? $n->created_at->diffForHumans() : 'Baru saja',
                            'timestamp' => $n->created_at?->timestamp ?? time(),
                        ];
                    })
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                $unreadCount = 0;
                $latestNotifications = [];
            }
        }

        return response()->json([
            'ok' => true,
            'version' => $version,
            'timestamp' => now()->toIso8601String(),
            'summary' => [
                'total' => $totalProducts,
                'active' => $activeProducts,
                'low_stock' => $lowStockProducts,
                'out_of_stock' => $outOfStockProducts,
            ],
            'dashboard' => [
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'total_rooms' => $totalRooms,
                'total_mutations' => $totalMutations,
                'pending_mutations' => $pendingMutations,
            ],
            'latest_product' => $latestProductRow ? [
                'id' => $latestProductRow->id,
                'name' => $latestProductRow->name,
                'kode_barang' => $latestProductRow->kode_barang,
                'stock' => $latestProductRow->stock,
                'room_name' => $latestProductRow->room_name,
                'url' => route('products.show', $latestProductRow->id, false),
            ] : null,
            'notifications' => [
                'unread_count' => $unreadCount,
                'latest' => $latestNotifications,
            ],
        ]);
    }
}
