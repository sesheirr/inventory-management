<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\MutationPending;
use App\Notifications\MutationStatusUpdated;
use App\Services\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MutationController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $categoryId = $request->input('category_id');
        $roomId = $request->input('room_id');
        $productId = $request->integer('product_id') ?: null;
        $type = $request->input('type');
        $status = $request->input('status');

        $query = $this->buildFilteredQuery($request);
        $mutations = $query->paginate(10)->withQueryString();

        $categories = Category::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();
        $filteredProduct = $productId ? Product::find($productId) : null;

        $pipelineCounts = [
            'all' => Mutation::count(),
            'pending' => Mutation::where('status', 'pending')->count(),
            'approved' => Mutation::where('status', 'approved')->count(),
            'rejected' => Mutation::where('status', 'rejected')->count(),
            'masuk' => Mutation::where('type', 'masuk')->count(),
            'pindah_ruang' => Mutation::where('type', 'pindah_ruang')->count(),
            'keluar' => Mutation::where('type', 'keluar')->count(),
        ];

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Mutations/Index', [
                'mutations' => $mutations,
                'pipelineCounts' => $pipelineCounts,
                'csrfToken' => csrf_token(),
                'isAdmin' => (bool) $request->user()?->isAdmin(),
            ]);
        }

        return view('mutations.index', compact(
            'mutations',
            'categories',
            'rooms',
            'search',
            'dateFrom',
            'dateTo',
            'categoryId',
            'roomId',
            'productId',
            'filteredProduct',
            'type',
            'status',
            'pipelineCounts'
        ));
    }

    public function exportPdf(Request $request)
    {
        $mutations = $this->buildFilteredQuery($request)->get();
        $filters = $request->only(['search', 'date_from', 'date_to', 'category_id', 'room_id', 'product_id', 'type', 'status']);

        return view('mutations.pdf', compact('mutations', 'filters'));
    }

    private function buildFilteredQuery(Request $request)
    {
        $query = Mutation::with(['product.category', 'product.room', 'fromRoom', 'toRoom', 'user', 'approver'])
            ->latest('mutation_date')
            ->latest('id');

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('kode_barang', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                })
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('mutation_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('mutation_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->input('category_id');
            $query->whereHas('product', fn ($pq) => $pq->where('category_id', $categoryId));
        }

        if ($request->filled('room_id')) {
            $roomId = $request->input('room_id');
            $query->where(function ($rq) use ($roomId) {
                $rq->where('from_room_id', $roomId)->orWhere('to_room_id', $roomId);
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        return $query;
    }

    public function create(Request $request)
    {
        $products = Product::with('room')->orderBy('name')->get();
        $items = $products;
        $rooms = Room::orderBy('name')->get();
        $selectedProductId = $request->integer('product_id') ?: null;

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Mutations/Create', [
                'products' => $products,
                'rooms' => $rooms,
                'selectedProductId' => $selectedProductId,
                'csrfToken' => csrf_token(),
            ]);
        }

        return view('mutations.create', compact('items', 'products', 'rooms', 'selectedProductId'));
    }

    public function show(Request $request, Mutation $mutation)
    {
        $mutation->load(['product.category', 'product.room', 'fromRoom', 'toRoom', 'user', 'approver']);

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Mutations/Show', [
                'mutation' => $mutation,
                'csrfToken' => csrf_token(),
                'isAdmin' => (bool) $request->user()?->isAdmin(),
            ]);
        }

        return view('mutations.show', compact('mutation'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'type' => 'required|in:masuk,keluar,pindah_ruang',
            'from_room_id' => 'nullable|exists:rooms,id',
            'to_room_id' => 'nullable|required_if:type,pindah_ruang|exists:rooms,id|different:from_room_id',
            'quantity' => 'required|integer|min:1',
            'mutation_date' => 'required|date',
            'note' => 'nullable|string',
        ], [
            'to_room_id.different' => 'Ruangan tujuan harus berbeda dengan ruangan asal.',
        ]);

        return DB::transaction(function () use ($request) {
            $product = Product::lockForUpdate()->findOrFail($request->product_id);
            /** @var User|null $user */
            $user = Auth::user();
            $isAdmin = $user && $user->isAdmin();

            if (in_array($request->type, ['keluar', 'pindah_ruang'], true) && $product->stock < $request->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok barang saat ini ('.$product->stock.') tidak mencukupi untuk mutasi ini.',
                ]);
            }

            if ($request->type === 'pindah_ruang' && $product->room_id && (int) $request->from_room_id !== (int) $product->room_id) {
                throw ValidationException::withMessages([
                    'from_room_id' => 'Ruangan asal tidak sesuai dengan lokasi barang saat ini.',
                ]);
            }

            $status = $isAdmin ? 'approved' : 'pending';

            $mutation = Mutation::create([
                'product_id' => $request->product_id,
                'user_id' => Auth::id(),
                'type' => $request->type,
                'from_room_id' => $request->type === 'masuk' ? null : ($request->from_room_id ?: $product->room_id),
                'to_room_id' => $request->type === 'keluar' ? null : $request->to_room_id,
                'quantity' => $request->quantity,
                'mutation_date' => $request->mutation_date,
                'status' => $status,
                'approved_by' => $isAdmin ? Auth::id() : null,
                'approved_at' => $isAdmin ? now() : null,
                'note' => $request->note,
            ]);

            if ($isAdmin) {
                $this->applyStockMutation($product, $mutation);
                $this->notifyLowStockIfNeeded($product);

                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'created',
                    'description' => "Mencatat & menyetujui mutasi ({$mutation->type}) sebanyak {$mutation->quantity} unit untuk {$product->name}",
                ]);

                $redirectRoute = $request->input('redirect_to') === 'product'
                    ? redirect()->route('products.show', $product)
                    : redirect()->route('mutations.index');

                return $redirectRoute->with('success', 'Data mutasi barang berhasil dicatat dan langsung disetujui!');
            }

            $mutation->load(['product', 'user']);
            $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new MutationPending($mutation));
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'created',
                'description' => "Mengajukan permohonan mutasi ({$mutation->type}) sebanyak {$mutation->quantity} unit untuk {$product->name} (Menunggu Persetujuan)",
            ]);

            $redirectRoute = $request->input('redirect_to') === 'product'
                ? redirect()->route('products.show', $product)
                : redirect()->route('mutations.index');

            return $redirectRoute->with('success', 'Permohonan mutasi barang berhasil diajukan dan sedang menunggu persetujuan Admin.');
        });
    }

    public function approve(Mutation $mutation)
    {
        return DB::transaction(function () use ($mutation) {
            $mutation = Mutation::lockForUpdate()->findOrFail($mutation->id);

            if ($mutation->status !== 'pending') {
                return redirect()->route('mutations.index')->with('error', 'Mutasi ini sudah diproses sebelumnya.');
            }

            $product = Product::lockForUpdate()->findOrFail($mutation->product_id);

            if (in_array($mutation->type, ['keluar', 'pindah_ruang'], true) && $product->stock < $mutation->quantity) {
                return redirect()->route('mutations.index')->with('error', 'Gagal menyetujui: Stok barang saat ini ('.$product->stock.') tidak mencukupi.');
            }

            $this->applyStockMutation($product, $mutation);

            $mutation->status = 'approved';
            $mutation->approved_by = Auth::id();
            $mutation->approved_at = now();
            $mutation->save();

            $mutation->load(['product', 'approver', 'user']);
            if ($mutation->user && (int) $mutation->user_id !== (int) Auth::id()) {
                $mutation->user->notify(new MutationStatusUpdated($mutation, 'approved'));
            }

            $this->notifyLowStockIfNeeded($product);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'updated',
                'description' => "Menyetujui permohonan mutasi ({$mutation->type}) sebanyak {$mutation->quantity} unit untuk {$product->name}",
            ]);

            /** @var User|null $actor */
            $actor = Auth::user();
            app(WebhookDispatcher::class)->broadcast(
                'mutation.approved',
                [
                    'mutation_id' => $mutation->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'type' => $mutation->type,
                    'quantity' => (int) $mutation->quantity,
                    'status' => 'approved',
                ],
                $actor
            );

            return redirect()->route('mutations.index')->with('success', 'Permohonan mutasi berhasil disetujui dan stok barang telah diperbarui.');
        });
    }

    public function reject(Request $request, Mutation $mutation)
    {
        $request->validate([
            'rejection_note' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($request, $mutation) {
            $mutation = Mutation::with('product')->lockForUpdate()->findOrFail($mutation->id);

            if ($mutation->status !== 'pending') {
                return redirect()->route('mutations.index')->with('error', 'Mutasi ini sudah diproses sebelumnya.');
            }

            $mutation->status = 'rejected';
            $mutation->rejection_note = trim((string) $request->input('rejection_note')) ?: 'Ditolak oleh Administrator.';
            $mutation->approved_by = Auth::id();
            $mutation->approved_at = now();
            $mutation->save();

            $mutation->load(['product', 'approver', 'user']);
            if ($mutation->user && (int) $mutation->user_id !== (int) Auth::id()) {
                $mutation->user->notify(new MutationStatusUpdated($mutation, 'rejected'));
            }

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'updated',
                'description' => 'Menolak permohonan mutasi ('.$mutation->type.') untuk '.($mutation->product?->name ?? 'Barang'),
            ]);

            /** @var User|null $actor */
            $actor = Auth::user();
            app(WebhookDispatcher::class)->broadcast(
                'mutation.rejected',
                [
                    'mutation_id' => $mutation->id,
                    'product_id' => $mutation->product_id,
                    'product_name' => $mutation->product?->name,
                    'type' => $mutation->type,
                    'quantity' => (int) $mutation->quantity,
                    'status' => 'rejected',
                ],
                $actor
            );

            return redirect()->route('mutations.index')->with('success', 'Permohonan mutasi telah ditolak.');
        });
    }

    public function destroy(Mutation $mutation)
    {
        return DB::transaction(function () use ($mutation) {
            $mutation = Mutation::lockForUpdate()->findOrFail($mutation->id);
            $product = Product::lockForUpdate()->find($mutation->product_id);

            if ($product && $mutation->status === 'approved') {
                if ($mutation->type === 'masuk') {
                    $product->stock = max(0, $product->stock - $mutation->quantity);
                } elseif ($mutation->type === 'keluar') {
                    $product->stock += $mutation->quantity;
                } elseif ($mutation->type === 'pindah_ruang') {
                    $product->room_id = $mutation->from_room_id;
                }

                if ($product->stock <= 0) {
                    $product->status = 'out_of_stock';
                } elseif ($product->status === 'out_of_stock') {
                    $product->status = 'active';
                }

                $product->save();
            }

            $mutation->delete();

            return redirect()->route('mutations.index')->with('success', 'Data mutasi berhasil dihapus dan perubahan stok telah dikembalikan.');
        });
    }

    private function applyStockMutation(Product $product, Mutation $mutation): void
    {
        if ($mutation->type === 'masuk') {
            $product->stock += $mutation->quantity;
            if ($mutation->to_room_id) {
                $product->room_id = $mutation->to_room_id;
            }
        } elseif ($mutation->type === 'keluar') {
            $product->stock = max(0, $product->stock - $mutation->quantity);
        } elseif ($mutation->type === 'pindah_ruang') {
            $product->room_id = $mutation->to_room_id;
        }

        if ($product->stock <= 0) {
            $product->status = 'out_of_stock';
        } elseif ($product->status === 'out_of_stock') {
            $product->status = 'active';
        }

        $product->save();
    }

    private function notifyLowStockIfNeeded(Product $product): void
    {
        if ((int) $product->stock > 5 && $product->status !== 'out_of_stock') {
            return;
        }

        try {
            $product->loadMissing('room');
            $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new LowStockAlert($product));
            }
        } catch (\Throwable $e) {
            // Non-blocking notification
        }
    }
}
