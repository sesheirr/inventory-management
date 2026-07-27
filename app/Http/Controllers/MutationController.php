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

        $mutationQuery = Mutation::with(['product.category', 'product.room', 'fromRoom', 'toRoom', 'user']);

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

        $mutation = DB::transaction(function () use ($data) {
            $product = Product::lockForUpdate()->find($data['product_id']);

            if (!$product) {
                throw ValidationException::withMessages(['product_id' => 'Barang tidak ditemukan.']);
            }

            $type = $data['type'];
            $quantity = $data['quantity'];
            $toRoomId = $data['to_room_id'] ?? null;
            $mutationData = [
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => $type,
                'quantity' => $quantity,
                'mutation_date' => $data['mutation_date'],
                'note' => $data['note'] ?? null,
            ];

            if ($type === 'masuk') {
                $product->stock = (int) $product->stock + $quantity;
                if ($toRoomId) {
                    $mutationData['to_room_id'] = $toRoomId;
                    $product->room_id = $toRoomId;
                }
            } elseif ($type === 'keluar') {
                if ((int) $product->stock < $quantity) {
                    throw ValidationException::withMessages(['quantity' => 'Stok tidak mencukupi untuk keluar.']);
                }
                $product->stock = (int) $product->stock - $quantity;
            } elseif ($type === 'pindah_ruang') {
                $mutationData['from_room_id'] = $product->room_id;
                $mutationData['to_room_id'] = $toRoomId;
                $product->room_id = $toRoomId;
            }

            $product->save();

            return Mutation::create($mutationData);
        });

        return redirect()->route('mutations.index')->with('success', 'Mutasi barang berhasil direkam.');
    }

    public function show(Mutation $mutation)
    {
        $mutation->load(['product.category', 'product.room', 'fromRoom', 'toRoom', 'user']);

        return view('mutations.show', compact('mutation'));
    }

    public function destroy(Mutation $mutation)
    {
        $mutation->delete();

        return redirect()->route('mutations.index')->with('success', 'Mutasi barang berhasil dihapus.');
    }
}
