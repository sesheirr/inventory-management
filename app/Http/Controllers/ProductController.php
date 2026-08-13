<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Models\Category;
use App\Models\Product;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
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
                    ->orWhere('edition', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhereHas('room', fn ($rq) => $rq->where('name', 'like', "%{$query}%"));
            });
        }

        $products = $products->latest()->paginate(10);

        return view('products.index', compact('products', 'query'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();

        return view('products.create', compact('categories', 'rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'category' => ['nullable', 'string', 'max:100'],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'edition' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,out_of_stock'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'barcode' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9\-]+$/', Rule::unique('products', 'barcode')],
        ]);

        $data = $this->resolveCategoryData($data, $request);

        if ($request->hasFile('image')) {
            [$data['image'], $data['image_public_id']] = $this->handleProductImageUpload($request->file('image'));
        }

        $data['kode_barang'] = 'BRG-' . strtoupper(Str::random(6));

        $product = Product::create($data);
        // BARCODE FEATURE: generate otomatis jika tidak diisi
        if (empty($product->barcode)) {
            $product->barcode = $this->generateUniqueBarcode($product->id);
            $product->save();
        }

        return redirect()->route('products.index')->with('success', 'Barang berhasil disimpan.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'rooms'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'category' => ['nullable', 'string', 'max:100'],
            'subcategory' => ['nullable', 'string', 'max:100'],
            'room' => ['nullable', 'string', 'max:150'],
            'edition' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive,out_of_stock'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'barcode' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9\-]+$/', Rule::unique('products', 'barcode')->ignore($product->id)],
        ]);

        $data = $this->resolveCategoryData($data, $request);

        if ($request->hasFile('image')) {
            try {
                if (!empty($product->image_public_id)) {
                    try {
                        $cloudinary = $this->getCloudinary();
                        $cloudinary->uploadApi()->destroy($product->image_public_id);
                    } catch (\Throwable $e) {
                        // ignore cloudinary deletion errors
                    }
                }

                if ($product->image && $this->isLocalImage($product->image) && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                [$data['image'], $data['image_public_id']] = $this->handleProductImageUpload($request->file('image'));
            } catch (\Throwable $e) {
                $data['image'] = null;
                $data['image_public_id'] = null;
            }
        } elseif ($request->boolean('remove_image')) {
            if (!empty($product->image_public_id)) {
                try {
                    $cloudinary = $this->getCloudinary();
                    $cloudinary->uploadApi()->destroy($product->image_public_id);
                } catch (\Throwable $e) {
                }
            }

            if ($product->image && $this->isLocalImage($product->image) && Storage::disk('public')->exists($product->image)) {
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

        return redirect()->route('products.index')->with('success', 'Barang berhasil diperbarui.');
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

    private function resolveCategoryData(array $data, Request $request): array
    {
        if ($request->filled('category_id')) {
            $category = Category::find($request->input('category_id'));
            if ($category) {
                $data['category_id'] = $category->id;
                $data['category'] = $category->name;

                return $data;
            }
        }

        if ($request->filled('category')) {
            $name = trim((string) $request->input('category'));
            $category = Category::where('name', $name)->first();

            if (!$category) {
                $category = Category::create([
                    'name' => $name,
                    'description' => null,
                ]);
            }

            $data['category_id'] = $category->id;
            $data['category'] = $category->name;

            return $data;
        }

        $category = Category::query()->first();
        if ($category) {
            $data['category_id'] = $category->id;
            $data['category'] = $category->name;

            return $data;
        }

        $data['category_id'] = null;
        $data['category'] = null;

        return $data;
    }

    private function handleProductImageUpload($file): array
    {
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        $uploadPreset = env('CLOUDINARY_UPLOAD_PRESET', 'q46tbsqz');

        if (!empty($cloudName) && !empty($uploadPreset)) {
            try {
                $response = Http::withOptions(['verify' => false])
                    ->asMultipart()
                    ->post(
                        'https://api.cloudinary.com/v1_1/' . $cloudName . '/image/upload',
                        [
                            'file' => fopen($file->getRealPath(), 'r'),
                            'upload_preset' => $uploadPreset,
                        ]
                    );

                if ($response->successful()) {
                    $uploadedFile = $response->json();

                    return [$uploadedFile['secure_url'] ?? null, $uploadedFile['public_id'] ?? null];
                }
            } catch (\Throwable $e) {
                // fall back to local storage
            }
        }

        $path = $file->store('products', 'public');

        return [$path, null];
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

    // BARCODE FEATURE: generate kode unik format INV-YYYY-XXXXX-XXXX
    private function generateUniqueBarcode(int $productId): string
    {
        do {
            $code = 'INV-' . date('Y') . '-' . str_pad($productId, 5, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(4));
        } while (Product::where('barcode', $code)->exists());

        return $code;
    }

    // BARCODE FEATURE: halaman cetak stiker barcode
    public function printBarcode(Product $product)
    {
        return view('products.barcode-print', compact('product'));
    }
}