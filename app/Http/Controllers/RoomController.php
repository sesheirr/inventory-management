<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        // New behavior: derive room names from products table (distinct room values)
        $query = trim((string) $request->input('search', ''));

        // get distinct non-empty room names from products
        $roomNamesQuery = \App\Models\Product::query()
            ->whereNotNull('room')
            ->where('room', '<>', '');

        if ($query !== '') {
            $roomNamesQuery->where('room', 'like', "%{$query}%");
        }

        $roomNames = $roomNamesQuery->select('room')->distinct()->pluck('room');

        // build rooms collection where each item contains name and products list
        $rooms = $roomNames->map(function ($name) {
            $products = \App\Models\Product::where('room', $name)->latest()->get();
            return (object) [
                'name' => $name,
                'products' => $products,
                'count' => $products->count(),
            ];
        });

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

        return redirect()->route('rooms.index')->with('success', 'Room created successfully.');
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

        return redirect()->route('rooms.index')->with('success', 'Room updated successfully.');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Room deleted successfully.');
    }
}
