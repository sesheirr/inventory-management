<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Cloudinary\Cloudinary;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('search', ''));

        $products = Product::query();

        if ($query !== '') {
            $products->where(function ($q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%")
                    ->orWhere('subcategory', 'like', "%{$query}%")
                    ->orWhere('edition', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        $products = $products->latest()->paginate(10);

        return view('products.index', compact('products', 'query'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
       
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'edition' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,out_of_stock'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

       

        if ($request->hasFile('image')) {
            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $uploadedFile = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath());
            $data['image'] = $uploadedFile['secure_url'] ?? null;
            $data['image_public_id'] = $uploadedFile['public_id'] ?? null;
        }

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:100'],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'edition' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,out_of_stock'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

    

        if ($request->hasFile('image')) {
            // remove previous Cloudinary image if exists
            if (!empty($product->image_public_id)) {
                try {
                    $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                    $cloudinary->uploadApi()->destroy($product->image_public_id);
                } catch (\Throwable $e) {
                    // ignore cloudinary deletion errors
                }
            }

            // remove previous local file if exists
            if ($product->image && $this->isLocalImage($product->image) && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
            $uploadedFile = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath());
            $data['image'] = $uploadedFile['secure_url'] ?? null;
            $data['image_public_id'] = $uploadedFile['public_id'] ?? null;
        } elseif ($request->boolean('remove_image')) {
            // delete from Cloudinary if exists
            if (!empty($product->image_public_id)) {
                try {
                    $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                    $cloudinary->uploadApi()->destroy($product->image_public_id);
                } catch (\Throwable $e) {
                }
            }

            if ($product->image && $this->isLocalImage($product->image) && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = null;
            $data['image_public_id'] = null;
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        // delete remote Cloudinary image if present
        if (!empty($product->image_public_id)) {
            try {
                $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                $cloudinary->uploadApi()->destroy($product->image_public_id);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // delete local image if present
        if ($product->image && $this->isLocalImage($product->image) && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function destroySelected(Request $request)
    {
        if ($request->selected_ids) {
            $ids = explode(',', $request->selected_ids);

            $products = Product::whereIn('id', $ids)->get();

            foreach ($products as $product) {
                if (!empty($product->image_public_id)) {
                    try {
                        $cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
                        $cloudinary->uploadApi()->destroy($product->image_public_id);
                    } catch (\Throwable $e) {
                    }
                }

                if ($product->image && $this->isLocalImage($product->image) && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
            }

            Product::whereIn('id', $ids)->delete();

            return redirect()->route('products.index')->with('success', 'Selected products deleted successfully.');
        }

        return redirect()->route('products.index')->with('error', 'No products selected.');
    }

    public function exportExcel()
    {
        return Excel::download(new BarangExport, 'laporan-inventaris-barang.xlsx');
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    private function isLocalImage(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return !str_starts_with($path, 'http');
    }
}