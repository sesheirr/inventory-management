<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Cloudinary\Cloudinary;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

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
                    ->orWhere('room', 'like', "%{$query}%")
                    ->orWhere('edition', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            });
        }

        $products = $products->latest()->paginate(10);

        return view('products.index', compact('products', 'query'));
    }

    public function create()
    {
        return view('products.create', [
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', Rule::in($this->categoryOptions())],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'room' => ['nullable', 'string', 'max:150'],
            'edition' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,out_of_stock'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        

        if ($request->hasFile('image')) {
            try {
                $cloudinary = $this->getCloudinary();
                $uploadedFile = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath());
                $data['image'] = $uploadedFile['secure_url'] ?? null;
                $data['image_public_id'] = $uploadedFile['public_id'] ?? null;
            } catch (\Throwable $e) {
                $storedImage = $request->file('image')->store('products', 'public');
                $data['image'] = $storedImage;
                $data['image_public_id'] = null;

                // create thumbnail for local image
                try {
                    Storage::disk('public')->makeDirectory('products/thumbs');
                    $originalPath = storage_path('app/public/' . $storedImage);
                    $thumbPath = storage_path('app/public/products/thumbs/' . basename($storedImage));
                    Image::make($originalPath)->fit(300, 300, function ($constraint) {
                        $constraint->upsize();
                    })->save($thumbPath);
                } catch (\Throwable $ei) {
                    // ignore thumbnail generation errors
                }
            }
        }

        $data['kode_barang'] = 'BRG-' . strtoupper(Str::random(6));

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', Rule::in($this->categoryOptions())],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'room' => ['nullable', 'string', 'max:150'],
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
                    $cloudinary = $this->getCloudinary();
                    $cloudinary->uploadApi()->destroy($product->image_public_id);
                } catch (\Throwable $e) {
                    // ignore cloudinary deletion errors
                }
            }

            // remove previous local file if exists
            if ($product->image && $this->isLocalImage($product->image) && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            try {
                $cloudinary = $this->getCloudinary();
                $uploadedFile = $cloudinary->uploadApi()->upload($request->file('image')->getRealPath());
                $data['image'] = $uploadedFile['secure_url'] ?? null;
                $data['image_public_id'] = $uploadedFile['public_id'] ?? null;
            } catch (\Throwable $e) {
                $storedImage = $request->file('image')->store('products', 'public');
                $data['image'] = $storedImage;
                $data['image_public_id'] = null;

                // create thumbnail for local image
                try {
                    Storage::disk('public')->makeDirectory('products/thumbs');
                    $originalPath = storage_path('app/public/' . $storedImage);
                    $thumbPath = storage_path('app/public/products/thumbs/' . basename($storedImage));
                    Image::make($originalPath)->fit(300, 300, function ($constraint) {
                        $constraint->upsize();
                    })->save($thumbPath);
                } catch (\Throwable $ei) {
                    // ignore thumbnail generation errors
                }
            }
        } elseif ($request->boolean('remove_image')) {
            // delete from Cloudinary if exists
            if (!empty($product->image_public_id)) {
                try {
                    $cloudinary = $this->getCloudinary();
                    $cloudinary->uploadApi()->destroy($product->image_public_id);
                } catch (\Throwable $e) {
                }
            }

            if ($product->image && $this->isLocalImage($product->image) && Storage::disk('public')->exists($product->image)) {
                // delete image and thumbnail
                Storage::disk('public')->delete($product->image);
                $thumb = 'products/thumbs/' . basename($product->image);
                if (Storage::disk('public')->exists($thumb)) {
                    Storage::disk('public')->delete($thumb);
                }
            }

            $data['image'] = null;
            $data['image_public_id'] = null;
        }

        $product->update($data);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->deleteProductAssets($product);
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function destroySelected(Request $request)
    {
        $validated = $request->validate([
            'selected_ids' => ['required', 'array'],
            'selected_ids.*' => ['required', 'integer', 'exists:products,id'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['selected_ids'])));

        if ($ids === []) {
            return redirect()->route('products.index')->with('error', 'No products selected.');
        }

        $products = Product::whereIn('id', $ids)->get();

        foreach ($products as $product) {
            $this->deleteProductAssets($product);
        }

        Product::whereIn('id', $ids)->delete();

        return redirect()->route('products.index')->with('success', 'Selected products deleted successfully.');
    }

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

    public function exportExcel()
    {
        return Excel::download(new BarangExport, 'laporan-inventaris-barang.xlsx');
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    private function deleteProductAssets(Product $product): void
    {
        if (!empty($product->image_public_id)) {
            try {
                $cloudinary = $this->getCloudinary();
                $cloudinary->uploadApi()->destroy($product->image_public_id);
            } catch (\Throwable $e) {
                // ignore Cloudinary deletion errors
            }
        }

        if ($product->image && $this->isLocalImage($product->image) && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);

            $thumb = 'products/thumbs/' . basename($product->image);
            if (Storage::disk('public')->exists($thumb)) {
                Storage::disk('public')->delete($thumb);
            }
        }
    }

    private function isLocalImage(?string $path): bool
    {
        if (empty($path)) {
            return false;
        }

        return !str_starts_with($path, 'http');
    }

    private function getCloudinary(): Cloudinary
    {
        $cloudinaryUrl = env('CLOUDINARY_URL');

        if (empty($cloudinaryUrl)) {
            // fallback: try to read .env directly
            $envPath = base_path('.env');
            if (file_exists($envPath)) {
                $contents = file_get_contents($envPath);
                if (preg_match('/^CLOUDINARY_URL=(.*)$/m', $contents, $m)) {
                    $cloudinaryUrl = trim($m[1]);
                    // strip surrounding quotes
                    $cloudinaryUrl = preg_replace('/^"|"$|^\'|\'$/', '', $cloudinaryUrl);
                }
            }
        }

        if (empty($cloudinaryUrl)) {
            throw new \RuntimeException('CLOUDINARY_URL is not set. Please configure Cloudinary in your .env');
        }

        // parse cloudinary://API_KEY:API_SECRET@CLOUD_NAME
        $parts = parse_url($cloudinaryUrl);

        if (!$parts || empty($parts['scheme']) || ($parts['scheme'] !== 'cloudinary')) {
            throw new \RuntimeException('Invalid CLOUDINARY_URL format.');
        }

        $cloudName = $parts['host'] ?? null;
        $apiKey = $parts['user'] ?? null;
        $apiSecret = $parts['pass'] ?? null;

        if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
            throw new \RuntimeException('Incomplete Cloudinary credentials in CLOUDINARY_URL.');
        }

        return new Cloudinary([
            'cloud' => [
                'cloud_name' => $cloudName,
                'api_key' => $apiKey,
                'api_secret' => $apiSecret,
            ],
        ]);
    }
}