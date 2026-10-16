<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class RoomController extends Controller
{
    private function getUniquePersonInChargeList(?int $ignoreRoomId = null): Collection
    {
        return Room::query()
            ->when($ignoreRoomId, fn ($q) => $q->where('id', '!=', $ignoreRoomId))
            ->whereNotNull('person_in_charge')
            ->where('person_in_charge', '!=', '')
            ->pluck('person_in_charge')
            ->map(fn ($name) => preg_replace('/\s+/', ' ', trim((string) $name)))
            ->filter(fn ($name) => $name !== '')
            ->unique(fn ($name) => mb_strtolower($name))
            ->sort(fn ($a, $b) => strcasecmp($a, $b))
            ->values();
    }

    public function index(Request $request)
    {
        $query = trim((string) $request->input('search', ''));
        $pic = trim((string) $request->input('pic', ''));
        $picList = $this->getUniquePersonInChargeList();

        $rooms = Room::with(['products' => fn ($q) => $q->with('category')->latest()])
            ->withCount('products')
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('name', 'like', "%{$query}%")
                        ->orWhere('person_in_charge', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhereHas('products', function ($productQuery) use ($query) {
                            $productQuery->where('name', 'like', "%{$query}%")
                                ->orWhere('kode_barang', 'like', "%{$query}%")
                                ->orWhere('barcode', 'like', "%{$query}%");
                        });
                });
            })
            ->when($pic !== '', function ($q) use ($pic) {
                if ($pic === '__unassigned__') {
                    $q->where(function ($sub) {
                        $sub->whereNull('person_in_charge')
                            ->orWhere('person_in_charge', '');
                    });
                } else {
                    $q->whereRaw('LOWER(TRIM(person_in_charge)) = ?', [mb_strtolower($pic)]);
                }
            })
            ->orderBy('name')
            ->get();

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Rooms/Index', [
                'rooms' => $rooms,
                'picList' => $picList,
            ]);
        }

        return view('rooms.index', compact('rooms', 'query', 'pic', 'picList'));
    }

    public function create()
    {
        $picList = $this->getUniquePersonInChargeList();

        return view('rooms.create', compact('picList'));
    }

    public function store(Request $request)
    {
        $picMode = (string) $request->input('pic_mode', 'select');
        $rawPic = $picMode === 'manual' && $request->has('person_in_charge_manual')
            ? $request->input('person_in_charge_manual')
            : $request->input('person_in_charge');

        $normalizedName = preg_replace('/\s+/', ' ', trim((string) $request->input('name', '')));
        $normalizedPic = $rawPic !== null ? preg_replace('/\s+/', ' ', trim((string) $rawPic)) : null;
        if ($normalizedPic === '') {
            $normalizedPic = null;
        }

        $request->merge([
            'name' => $normalizedName,
            'person_in_charge' => $normalizedPic,
        ]);

        $existingPicLower = $this->getUniquePersonInChargeList()
            ->map(fn ($p) => mb_strtolower($p))
            ->all();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $exists = Room::query()
                        ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim((string) $value))])
                        ->exists();
                    if ($exists) {
                        $fail('Nama ruangan sudah terdaftar. Tidak boleh ada 2 nama ruangan identik yang sama.');
                    }
                },
            ],
            'location' => ['nullable', 'string', 'max:150'],
            'person_in_charge' => [
                'nullable',
                'string',
                'max:150',
                function (string $attribute, mixed $value, \Closure $fail) use ($picMode, $existingPicLower) {
                    if ($picMode === 'manual' && $value !== null && $value !== '') {
                        if (in_array(mb_strtolower(trim((string) $value)), $existingPicLower, true)) {
                            $fail('Nama penanggung jawab tersebut sudah ada di daftar pilihan. Tidak boleh ada 2 nama identik yang sama, silakan pilih dari dropdown.');
                        }
                    }
                },
            ],
            'description' => ['nullable', 'string'],
        ]);

        Room::create($data);

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil dibuat.');
    }

    public function show(Request $request, Room $room)
    {
        $room->loadCount('products');
        $products = $room->products()
            ->with(['category', 'media'])
            ->latest()
            ->paginate(10);

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Rooms/Show', [
                'room' => $room,
                'products' => $products,
            ]);
        }

        return view('rooms.show', compact('room', 'products'));
    }

    public function edit(Room $room)
    {
        $picList = $this->getUniquePersonInChargeList();

        return view('rooms.edit', compact('room', 'picList'));
    }

    public function update(Request $request, Room $room)
    {
        $picMode = (string) $request->input('pic_mode', 'select');
        $rawPic = $picMode === 'manual' && $request->has('person_in_charge_manual')
            ? $request->input('person_in_charge_manual')
            : $request->input('person_in_charge');

        $normalizedName = preg_replace('/\s+/', ' ', trim((string) $request->input('name', '')));
        $normalizedPic = $rawPic !== null ? preg_replace('/\s+/', ' ', trim((string) $rawPic)) : null;
        if ($normalizedPic === '') {
            $normalizedPic = null;
        }

        $request->merge([
            'name' => $normalizedName,
            'person_in_charge' => $normalizedPic,
        ]);

        $existingPicLower = $this->getUniquePersonInChargeList($room->id)
            ->map(fn ($p) => mb_strtolower($p))
            ->all();

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail) use ($room) {
                    $exists = Room::query()
                        ->where('id', '!=', $room->id)
                        ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim((string) $value))])
                        ->exists();
                    if ($exists) {
                        $fail('Nama ruangan sudah terdaftar. Tidak boleh ada 2 nama ruangan identik yang sama.');
                    }
                },
            ],
            'location' => ['nullable', 'string', 'max:150'],
            'person_in_charge' => [
                'nullable',
                'string',
                'max:150',
                function (string $attribute, mixed $value, \Closure $fail) use ($picMode, $existingPicLower) {
                    if ($picMode === 'manual' && $value !== null && $value !== '') {
                        if (in_array(mb_strtolower(trim((string) $value)), $existingPicLower, true)) {
                            $fail('Nama penanggung jawab tersebut sudah ada di daftar pilihan. Tidak boleh ada 2 nama identik yang sama, silakan pilih dari dropdown.');
                        }
                    }
                },
            ],
            'description' => ['nullable', 'string'],
        ]);

        $room->update($data);

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Request $request, Room $room)
    {
        $productCount = $room->products()->count();
        if ($productCount > 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Ruangan tidak bisa dihapus karena masih digunakan oleh '.$productCount.' barang.',
                ], 422);
            }

            return redirect()->route('rooms.index')->with('error', 'Ruangan tidak bisa dihapus karena masih digunakan oleh '.$productCount.' barang.');
        }

        $room->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => 'Ruangan berhasil dihapus.']);
        }

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil dihapus.');
    }
}
