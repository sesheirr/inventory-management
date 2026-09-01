<?php

namespace App\Http\Controllers;

use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MutationController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $categoryId = $request->input('category_id');
        $roomId = $request->input('room_id');
        $type = $request->input('type');
        $search = trim((string) $request->input('search', ''));

        $categories = \App\Models\Category::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();

        $mutationQuery = Mutation::with(['product.category', 'product.room', 'fromRoom', 'toRoom', 'user', 'approver']);

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

        if ($type) {
            $mutationQuery->where('type', $type);
        }

        if ($search !== '') {
            $mutationQuery->where(function ($query) use ($search) {
                $query->whereHas('product', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                          ->orWhere('kode_barang', 'like', "%{$search}%");
                })
                ->orWhere('note', 'like', "%{$search}%");
            });
        }

        $mutations = $mutationQuery->latest('mutation_date')->paginate(10)->withQueryString();

        return view('mutations.index', compact(
            'mutations',
            'categories',
            'rooms',
            'dateFrom',
            'dateTo',
            'categoryId',
            'roomId',
            'type',
            'search'
        ));
    }

    public function create()
    {
        $items = Product::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();

        return view('mutations.create', compact('items', 'rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', 'in:masuk,keluar,pindah_ruang'],
            'quantity' => ['required', 'integer', 'min:1'],
            'to_room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'mutation_date' => ['required', 'date'],
            'note' => ['nullable', 'string'],
        ]);

        if ($data['type'] === 'pindah_ruang' && empty($data['to_room_id'])) {
            throw ValidationException::withMessages(['to_room_id' => 'Ruangan tujuan wajib diisi untuk mutasi pindah ruang.']);
        }

        // PENTING: store() sekarang HANYA mencatat pengajuan mutasi.
        // Stok & lokasi barang TIDAK diubah di sini — baru diubah nanti saat approve().
        $product = Product::find($data['product_id']);

        if (!$product) {
            throw ValidationException::withMessages(['product_id' => 'Barang tidak ditemukan.']);
        }

        $type = $data['type'];
        $toRoomId = $data['to_room_id'] ?? null;

        $mutationData = [
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'quantity' => $data['quantity'],
            'mutation_date' => $data['mutation_date'],
            'note' => $data['note'] ?? null,
            'status' => 'pending',
        ];

        if ($type === 'masuk') {
            if ($toRoomId) {
                $mutationData['to_room_id'] = $toRoomId;
            }
        } elseif ($type === 'pindah_ruang') {
            // from_room_id dicatat dari lokasi barang SAAT INI (belum berubah, masih pending)
            $mutationData['from_room_id'] = $product->room_id;
            $mutationData['to_room_id'] = $toRoomId;
        }
        // untuk type 'keluar', tidak perlu from_room_id / to_room_id

        Mutation::create($mutationData);

        return redirect()->route('mutations.index')->with('success', 'Pengajuan mutasi berhasil dikirim dan menunggu persetujuan admin.');
    }

    public function approve(Mutation $mutation)
    {
        if (! auth()->user() || ! auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin untuk memproses persetujuan mutasi.');
        }

        if ($mutation->status !== 'pending') {
            return back()->with('error', 'Mutasi ini sudah diproses sebelumnya.');
        }

        DB::transaction(function () use ($mutation) {
            $product = Product::lockForUpdate()->find($mutation->product_id);

            if (!$product) {
                throw ValidationException::withMessages(['product_id' => 'Barang tidak ditemukan.']);
            }

            if ($mutation->type === 'masuk') {
                $product->stock = (int) $product->stock + $mutation->quantity;
                if ($mutation->to_room_id) {
                    $product->room_id = $mutation->to_room_id;
                }
            } elseif ($mutation->type === 'keluar') {
                if ((int) $product->stock < $mutation->quantity) {
                    throw ValidationException::withMessages(['quantity' => 'Stok tidak mencukupi untuk disetujui.']);
                }
                $product->stock = (int) $product->stock - $mutation->quantity;
            } elseif ($mutation->type === 'pindah_ruang') {
                $product->room_id = $mutation->to_room_id;
            }

            $product->save();

            $mutation->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_note' => null,
            ]);
        });

        return back()->with('success', 'Mutasi berhasil disetujui dan stok/lokasi barang telah diperbarui.');
    }

    public function reject(Request $request, Mutation $mutation)
    {
        if (! auth()->user() || ! auth()->user()->isAdmin()) {
            abort(403, 'Anda tidak memiliki izin untuk memproses persetujuan mutasi.');
        }

        if ($mutation->status !== 'pending') {
            return back()->with('error', 'Mutasi ini sudah diproses sebelumnya.');
        }

        $request->validate([
            'rejection_note' => ['required', 'string', 'max:500'],
        ], [
            'rejection_note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $mutation->update([
            'status' => 'rejected',
            'rejection_note' => $request->rejection_note,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Mutasi telah ditolak.');
    }

    public function show(Mutation $mutation)
    {
        $mutation->load(['product.category', 'product.room', 'fromRoom', 'toRoom', 'user', 'approver']);

        return view('mutations.show', compact('mutation'));
    }

    public function destroy(Mutation $mutation)
    {
        $mutation->delete();

        return redirect()->route('mutations.index')->with('success', 'Mutasi barang berhasil dihapus.');
    }
}