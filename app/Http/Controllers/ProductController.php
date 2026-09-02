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
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'room' => ['nullable', 'string', 'max:150'],
            'edition' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'stock' => ['required', 'integer', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive,out_of_stock'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'barcode' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9\-]+$/', Rule::unique('products', 'barcode')],
        ]);

        $data = $this->resolveCategoryData($data, $request);
        $data = $this->resolveRoomData($data, $request);

        $data['kode_barang'] = 'BRG-' . strtoupper(Str::random(6));

        $product = Product::create($data);

        if ($request->hasFile('image')) {
            $request->file('image')->store('products', 'public');
            try {
                $product->addMediaFromRequest('image')->toMediaCollection('images');
            } catch (\Throwable $e) {
                // Media library fallback
            }
        }
        // BARCODE FEATURE: generate otomatis jika tidak diisi
        if (empty($product->barcode)) {
            $product->barcode = $this->generateUniqueBarcode();
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

        $product->update($data);

        if ($request->hasFile('image')) {
            $product->clearMediaCollection('images');
            $product->addMediaFromRequest('image')->toMediaCollection('images');
        } elseif ($request->boolean('remove_image')) {
            $product->clearMediaCollection('images');
        }

        return redirect()->route('products.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
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



    private function resolveRoomData(array $data, Request $request): array
    {
        if ($request->filled('room_id')) {
            $room = Room::find($request->input('room_id'));
            if ($room) {
                $data['room_id'] = $room->id;
                $data['room'] = $room->name;

                return $data;
            }
        }

        if ($request->filled('room')) {
            $name = trim((string) $request->input('room'));
            $room = Room::where('name', $name)->first();

            if (!$room) {
                $room = Room::create([
                    'name' => $name,
                    'description' => null,
                ]);
            }

            $data['room_id'] = $room->id;
            $data['room'] = $room->name;

            return $data;
        }

        $room = Room::query()->first();
        if ($room) {
            $data['room_id'] = $room->id;
            $data['room'] = $room->name;

            return $data;
        }

        $data['room_id'] = null;
        $data['room'] = null;

        return $data;
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

    // BARCODE FEATURE: generate kode urut otomatis format BRG-000001
    private function generateUniqueBarcode(): string
    {
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $last = Product::where('barcode', 'like', 'BRG-%')
                ->lockForUpdate()
                ->latest('id')
                ->value('barcode');

            $nextNumber = 1;
            if ($last && preg_match('/^BRG-(\d+)$/', $last, $m)) {
                $nextNumber = ((int) $m[1]) + 1;
            }

            do {
                $code = 'BRG-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
                $nextNumber++;
            } while (Product::where('barcode', $code)->exists());

            return $code;
        });
    }

    // BARCODE FEATURE: halaman cetak stiker barcode
    public function printBarcode(Product $product)
    {
        return view('products.barcode-print', compact('product'));
    }// BARCODE FEATURE: halaman scan barcode
public function scanBarcode()
{
    return view('products.scan-barcode');
}

// BARCODE FEATURE: cari produk berdasarkan hasil scan barcode
public function scanBarcodeSearch(Request $request)
{
    $validated = $request->validate([
        'barcode' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-]+$/'],
    ]);

    $barcode = strtoupper(trim($validated['barcode']));

    $product = Product::where('barcode', $barcode)->first();

    if (!$product) {
        return response()->json([
            'found' => false,
            'message' => 'Barang dengan barcode tersebut tidak ditemukan.',
        ], 404);
    }

    return response()->json([
        'found' => true,
        'redirect_url' => route('products.show', $product->id),
    ]);
}


}

