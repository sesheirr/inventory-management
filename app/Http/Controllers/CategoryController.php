<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private function categoryOptions(): array
    {
        return [
            'Peralatan IT & Jaringan',
            'Perangkat Multimedia & Penyiaran',
            'Elektronik Kantor',
            'Mebel & Furniture',
            'Kendaraan Operasional',
            'Barang Habis Pakai (BHP)',
        ];
    }

    public function index(Request $request)
    {
        $query = trim((string) $request->input('search', ''));
        $categoryOptions = $this->categoryOptions();

        $categories = collect($categoryOptions)->map(function ($option) use ($query) {
            $products = Product::query()
                ->where('category', $option)
                ->when($query !== '', function ($q) use ($query) {
                    $q->where(function ($sub) use ($query) {
                        $sub->where('name', 'like', "%{$query}%")
                            ->orWhere('subcategory', 'like', "%{$query}%")
                            ->orWhere('room', 'like', "%{$query}%")
                            ->orWhere('edition', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    });
                })
                ->latest()
                ->get();

            return (object) [
                'name' => $option,
                'products' => $products,
                'count' => $products->count(),
            ];
        });

        return view('categories.index', compact('categories', 'query'));
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
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
