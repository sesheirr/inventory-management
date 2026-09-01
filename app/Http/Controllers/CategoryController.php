<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('search', ''));

        $categories = Category::with(['products.room'])
            ->withCount('products')
            ->when($query !== '', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhereHas('products', function ($productQuery) use ($query) {
                        $productQuery->where('name', 'like', "%{$query}%")
                            ->orWhere('subcategory', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%")
                            ->orWhereHas('room', function ($roomQuery) use ($query) {
                                $roomQuery->where('name', 'like', "%{$query}%");
                            });
                    });
            })
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories', 'query'));
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string'],
        ]);

        $data['name'] = trim((string) $data['name']);

        Category::create($data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Category $category)
    {
        return view('categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string'],
        ]);

        $data['name'] = trim((string) $data['name']);
        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, Category $category)
    {
        $productCount = $category->products()->count();

        if ($productCount > 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Kategori tidak bisa dihapus karena masih digunakan oleh ' . $productCount . ' barang.'
                ], 422);
            }

            return redirect()->route('categories.index')->with('error', 'Kategori tidak bisa dihapus karena masih digunakan oleh ' . $productCount . ' barang.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
