<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

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
                            ->orWhere('room', 'like', "%{$query}%")
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
            'name' => ['required', 'string', 'max:100'],
        ]);

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
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
