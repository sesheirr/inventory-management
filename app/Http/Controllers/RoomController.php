<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('search', ''));

        $rooms = Room::with('products')
            ->withCount('products')
            ->when($query !== '', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhereHas('products', function ($productQuery) use ($query) {
                        $productQuery->where('name', 'like', "%{$query}%");
                    });
            })
            ->orderBy('name')
            ->get();

        return view('rooms.index', compact('rooms', 'query'));
    }

    public function create()
    {
        return view('rooms.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:150'],
            'person_in_charge' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        Room::create($data);

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil dibuat.');
    }

    public function show(Room $room)
    {
        return view('rooms.show', compact('room'));
    }

    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:150'],
            'person_in_charge' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
        ]);

        $room->update($data);

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Room $room)
    {
        $productCount = $room->products()->count();
        if ($productCount > 0) {
            return redirect()->route('rooms.index')->with('error', 'Ruangan tidak bisa dihapus karena masih digunakan oleh ' . $productCount . ' barang.');
        }

        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Ruangan berhasil dihapus.');
    }
}
