<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\MutationPending;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    private function currentUser(): User
    {
        /** @var User $user */
        $user = auth()->user();

        return $user;
    }

    private function formatRecentNotifications(User $user): array
    {
        return $user->notifications()
            ->latest()
            ->take(8)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Pemberitahuan Sistem',
                'message' => $n->data['message'] ?? '',
                'category' => $n->data['category'] ?? 'info',
                'icon' => $n->data['icon'] ?? 'bi-bell-fill',
                'is_unread' => is_null($n->read_at),
                'read_at' => $n->read_at?->toIso8601String(),
                'created_at_human' => $n->created_at?->diffForHumans(),
                'go_url' => route('notifications.go', $n->id, false),
                'mark_read_url' => route('notifications.markAsRead', $n->id, false),
                'delete_url' => route('notifications.destroy', $n->id, false),
            ])
            ->values()
            ->all();
    }

    public function index(Request $request)
    {
        $user = $this->currentUser();
        $filter = (string) $request->input('filter', 'all');
        $query = $user->notifications();

        if ($filter === 'unread') {
            $query = $user->unreadNotifications();
        } elseif (in_array($filter, ['approval', 'approved', 'rejected', 'low_stock', 'out_of_stock', 'info'], true)) {
            $query->where('data->category', $filter);
        }

        $notifications = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => $user->notifications()->count(),
            'unread' => $user->unreadNotifications()->count(),
        ];

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Notifications/Index', [
                'notifications' => $notifications->through(fn ($n) => [
                    'id' => $n->id,
                    'title' => $n->data['title'] ?? 'Notifikasi Sistem',
                    'message' => $n->data['message'] ?? '',
                    'read_at' => $n->read_at?->toIso8601String(),
                    'created_at_human' => $n->created_at?->diffForHumans(),
                ])->items(),
                'counts' => $counts,
            ]);
        }

        return view('notifications.index', compact('notifications', 'filter', 'counts'));
    }

    public function go(string $id)
    {
        $notification = $this->currentUser()->notifications()->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $data = (array) $notification->data;

        if (! empty($data['url'])) {
            $rawUrl = trim((string) $data['url']);
            $parsed = parse_url($rawUrl);
            $path = $parsed['path'] ?? '/';
            if ($path === '' || $path[0] !== '/') {
                $path = '/'.ltrim($path, '/');
            }
            $query = isset($parsed['query']) && $parsed['query'] !== '' ? '?'.$parsed['query'] : '';
            $fragment = isset($parsed['fragment']) && $parsed['fragment'] !== '' ? '#'.$parsed['fragment'] : '';

            return redirect()->to($path.$query.$fragment);
        }

        if (! empty($data['product_id']) && Product::whereKey($data['product_id'])->exists()) {
            return redirect()->route('products.show', $data['product_id']);
        }

        if (! empty($data['mutation_id'])) {
            if (Mutation::whereKey($data['mutation_id'])->exists()) {
                return redirect()->route('mutations.show', $data['mutation_id']);
            }

            return redirect()->route('mutations.index', ['highlight' => $data['mutation_id']]);
        }

        return redirect()->route('dashboard');
    }

    public function markAsRead(string $id)
    {
        $user = $this->currentUser();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        ActivityLog::record(
            'notification_read',
            'Menandai notifikasi "'.($notification->data['title'] ?? 'Sistem').'" sebagai sudah dibaca.',
            'Notification'
        );

        if (request()->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Notifikasi ditandai sudah dibaca.',
                'unread_count' => $user->unreadNotifications()->count(),
                'total_count' => $user->notifications()->count(),
                'recent_notifications' => $this->formatRecentNotifications($user),
            ]);
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead()
    {
        $user = $this->currentUser();
        $user->unreadNotifications->markAsRead();

        ActivityLog::record(
            'notification_read_all',
            'Menandai seluruh notifikasi sebagai sudah dibaca.',
            'Notification'
        );

        if (request()->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Semua notifikasi ditandai sudah dibaca.',
                'unread_count' => 0,
                'total_count' => $user->notifications()->count(),
                'recent_notifications' => $this->formatRecentNotifications($user),
            ]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function checkReminders()
    {
        $user = $this->currentUser();
        $generated = 0;

        // 1. Check critical/low stock items (top 5 most urgent)
        $lowStockItems = Product::with('room')
            ->where(function ($q) {
                $q->where('stock', '<=', 5)->orWhere('status', 'out_of_stock');
            })
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        $existingUnreadProductIds = $user->unreadNotifications()
            ->get()
            ->pluck('data.product_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($lowStockItems as $product) {
            if (! in_array((int) $product->id, $existingUnreadProductIds, true)) {
                $user->notify(new LowStockAlert($product));
                $generated++;
            }
        }

        // 2. If user is Admin/SuperAdmin, also remind about pending mutations
        if ($user->isAdmin()) {
            $pendingMutations = Mutation::with(['product', 'user'])
                ->where('status', 'pending')
                ->latest()
                ->take(5)
                ->get();

            $existingUnreadMutationIds = $user->unreadNotifications()
                ->get()
                ->pluck('data.mutation_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($pendingMutations as $mutation) {
                if (! in_array((int) $mutation->id, $existingUnreadMutationIds, true)) {
                    $user->notify(new MutationPending($mutation));
                    $generated++;
                }
            }
        }

        $msg = $generated > 0
            ? "{$generated} pengingat stok & persetujuan penting berhasil disinkronkan."
            : 'Semua pengingat stok dan persetujuan mutasi sudah mutakhir.';

        ActivityLog::record(
            'notification_synced',
            "Menyinkronkan pengingat notifikasi realtime ({$generated} baru).",
            'Notification'
        );

        if (request()->expectsJson()) {
            return response()->json([
                'ok' => true,
                'generated' => $generated,
                'message' => $msg,
                'unread_count' => $user->unreadNotifications()->count(),
                'total_count' => $user->notifications()->count(),
                'recent_notifications' => $this->formatRecentNotifications($user),
            ]);
        }

        if ($generated > 0) {
            return back()->with('success', $msg);
        }

        return back()->with('info', $msg);
    }

    public function destroy(string $id)
    {
        $user = $this->currentUser();
        $notification = $user->notifications()->findOrFail($id);
        $title = $notification->data['title'] ?? 'Sistem';
        $notification->delete();

        ActivityLog::record(
            'notification_deleted',
            "Menghapus notifikasi \"{$title}\".",
            'Notification'
        );

        if (request()->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Notifikasi berhasil dihapus.',
                'unread_count' => $user->unreadNotifications()->count(),
                'total_count' => $user->notifications()->count(),
                'recent_notifications' => $this->formatRecentNotifications($user),
            ]);
        }

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}
