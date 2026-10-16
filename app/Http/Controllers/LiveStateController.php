<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use App\Services\WebhookDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LiveStateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $totalProducts = Product::count();
        $activeProducts = Product::where('status', 'active')->where('stock', '>', 0)->count();
        $totalUnits = (int) Product::sum('stock');
        $lowStockProducts = Product::where('stock', '>', 0)->where('stock', '<=', 5)->count();
        $outOfStockProducts = Product::where(fn ($q) => $q->where('status', 'out_of_stock')->orWhere('stock', '<=', 0))->count();

        $totalCategories = Category::count();
        $totalRooms = Room::count();
        $totalMutations = Mutation::count();
        $pendingMutations = Mutation::where('status', 'pending')->count();

        $latestProductUpdated = (string) (Product::max('updated_at') ?? '');
        $latestMutationUpdated = (string) (Mutation::max('updated_at') ?? '');
        $latestProductRow = Product::with(['room', 'category'])->latest('updated_at')->first();

        $webhookStream = $this->resolveWebhookStream();
        $latestWebhookId = $webhookStream[0]['id'] ?? '';

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
            $latestWebhookId,
        ])), 0, 16);

        /** @var User|null $user */
        $user = $request->user();
        [$unreadCount, $latestNotifications] = $this->resolveUserNotifications($user);

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
                'active_products' => $activeProducts,
                'total_units' => $totalUnits,
                'total_categories' => $totalCategories,
                'total_rooms' => $totalRooms,
                'total_mutations' => $totalMutations,
                'pending_mutations' => $pendingMutations,
                'low_stock_count' => $lowStockProducts + $outOfStockProducts,
                'out_of_stock_count' => $outOfStockProducts,
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
            'webhook_stream' => [
                'channel' => 'webhook-hmac-sha256',
                'cursor' => $latestWebhookId,
                'events' => array_slice($webhookStream, 0, 8),
            ],
        ]);
    }

    /**
     * Dedicated Webhook-Driven Notification Listener Stream (`GET /api/webhooks/stream`).
     */
    public function webhookStream(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();
        [$unreadCount, $latestNotifications] = $this->resolveUserNotifications($user);
        $webhookStream = $this->resolveWebhookStream();

        return response()->json([
            'ok' => true,
            'channel' => 'webhook-hmac-sha256',
            'timestamp' => now()->toIso8601String(),
            'cursor' => $webhookStream[0]['id'] ?? null,
            'events' => array_slice($webhookStream, 0, 10),
            'notifications' => [
                'unread_count' => $unreadCount,
                'latest' => $latestNotifications,
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function resolveWebhookStream(): array
    {
        try {
            $stream = Cache::get(WebhookDispatcher::STREAM_CACHE_KEY, []);

            return is_array($stream) ? array_values($stream) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @return array{0: int, 1: array<int, array<string, mixed>>}
     */
    private function resolveUserNotifications(?User $user): array
    {
        if (! $user) {
            return [0, []];
        }

        try {
            $unreadCount = $user->unreadNotifications()->count();
            $latestNotifications = $user->notifications()
                ->latest()
                ->take(6)
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
                        'is_unread' => is_null($n->read_at),
                        'go_url' => route('notifications.go', $n->id, false),
                        'mark_read_url' => route('notifications.markAsRead', $n->id, false),
                        'delete_url' => route('notifications.destroy', $n->id, false),
                        'created_at_human' => $n->created_at ? $n->created_at->diffForHumans() : 'Baru saja',
                        'timestamp' => $n->created_at?->timestamp ?? time(),
                    ];
                })
                ->values()
                ->all();

            return [$unreadCount, $latestNotifications];
        } catch (\Throwable $e) {
            return [0, []];
        }
    }
}
