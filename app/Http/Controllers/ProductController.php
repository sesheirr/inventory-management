<?php

namespace App\Http\Controllers;

use App\Exports\BarangExport;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use App\Notifications\LowStockAlert;
use App\Notifications\ProductCreatedNotice;
use App\Services\WebhookDispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('search', ''));
        $categoryId = $request->input('category_id');
        $roomId = $request->input('room_id');
        $status = (string) $request->input('status', 'all');
        $sort = (string) $request->input('sort', 'latest');

        $productsQuery = $this->buildFilteredProductsQuery($query, $categoryId, $roomId, $status, $sort);

        $products = $productsQuery
            ->with(['category', 'room', 'media'])
            ->paginate(10)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();

        $summary = [
            'total' => Product::count(),
            'active' => Product::where('status', 'active')->where('stock', '>', 0)->count(),
            'low_stock' => Product::where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'out_of_stock' => Product::where(fn ($q) => $q->where('status', 'out_of_stock')->orWhere('stock', '<=', 0))->count(),
        ];

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Products/Index', [
                'products' => $products,
                'summary' => $summary,
                'categories' => $categories,
                'rooms' => $rooms,
            ]);
        }

        return view('products.index', compact(
            'products',
            'query',
            'categories',
            'rooms',
            'categoryId',
            'roomId',
            'status',
            'sort',
            'summary'
        ));
    }

    private function buildFilteredProductsQuery(string $query, $categoryId, $roomId, string $status, string $sort)
    {
        $productsQuery = Product::query();

        if ($query !== '') {
            $productsQuery->where(function ($q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('kode_barang', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%")
                    ->orWhere('subcategory', 'like', "%{$query}%")
                    ->orWhere('edition', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$query}%"))
                    ->orWhereHas('room', fn ($rq) => $rq->where('name', 'like', "%{$query}%"));
            });
        }

        if (! empty($categoryId)) {
            $categoryModel = Category::find($categoryId);
            $productsQuery->where(function ($q) use ($categoryId, $categoryModel): void {
                $q->where('category_id', $categoryId);
                if ($categoryModel) {
                    $q->orWhere('category', $categoryModel->name);
                }
            });
        }

        if (! empty($roomId)) {
            $productsQuery->where('room_id', $roomId);
        }

        if ($status !== '' && $status !== 'all') {
            if ($status === 'low_stock') {
                $productsQuery->where('stock', '>', 0)->where('stock', '<=', 5);
            } elseif ($status === 'out_of_stock') {
                $productsQuery->where(function ($q): void {
                    $q->where('status', 'out_of_stock')->orWhere('stock', '<=', 0);
                });
            } else {
                $productsQuery->where('status', $status);
            }
        }

        match ($sort) {
            'name_asc' => $productsQuery->orderBy('name', 'asc'),
            'name_desc' => $productsQuery->orderBy('name', 'desc'),
            'stock_asc' => $productsQuery->orderBy('stock', 'asc'),
            'stock_desc' => $productsQuery->orderBy('stock', 'desc'),
            'oldest' => $productsQuery->oldest(),
            default => $productsQuery->latest(),
        };

        return $productsQuery;
    }

    public function create(Request $request)
    {
        $categories = Category::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Products/Form', [
                'mode' => 'create',
                'categories' => $categories,
                'rooms' => $rooms,
                'csrfToken' => csrf_token(),
            ]);
        }

        return view('products.create', compact('categories', 'rooms'));
    }

    public function store(Request $request)
    {
        if ($request->filled('barcode')) {
            $request->merge([
                'barcode' => strtoupper(trim((string) $request->input('barcode'))),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'kode_barang' => ['nullable', 'string', 'max:50', Rule::unique('products', 'kode_barang')],
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
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'uploaded_tmp_files' => ['nullable', 'array', 'max:8'],
            'uploaded_tmp_files.*' => ['string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9\-]+$/', Rule::unique('products', 'barcode')],
        ]);

        unset($data['images'], $data['uploaded_tmp_files']);

        $data = $this->resolveCategoryData($data, $request);
        $data = $this->resolveRoomData($data, $request);

        if (empty($data['kode_barang'])) {
            do {
                $kodeBarang = 'BRG-'.strtoupper(Str::random(6));
            } while (Product::where('kode_barang', $kodeBarang)->exists());

            $data['kode_barang'] = $kodeBarang;
        }

        $product = Product::create($data);

        $this->attachMultiProductMedia($product, $request);

        // BARCODE FEATURE: generate otomatis jika tidak diisi
        if (empty($product->barcode)) {
            $product->barcode = $this->generateUniqueBarcode();
            $product->save();
        }

        $this->dispatchProductNotifications($product, $request->user(), true);

        return redirect()->route('products.index')->with('success', 'Barang berhasil disimpan.');
    }

    public function show(Request $request, Product $product)
    {
        $product->load([
            'category',
            'room',
            'media',
            'mutations' => fn ($q) => $q->with(['user', 'approver', 'fromRoom', 'toRoom'])->latest()->take(15),
        ]);
        $rooms = Room::orderBy('name')->get();

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Products/Show', [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'kode_barang' => $product->kode_barang,
                    'barcode' => $product->barcode,
                    'stock' => (int) $product->stock,
                    'status_label' => $product->status_label,
                    'category_name' => $product->category_name,
                    'room_name' => $product->room_name,
                    'image_url' => $product->image_url,
                    'gallery_urls' => $product->gallery_urls,
                ],
                'rooms' => $rooms,
            ]);
        }

        return view('products.show', compact('product', 'rooms'));
    }

    public function edit(Request $request, Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $rooms = Room::orderBy('name')->get();

        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Products/Form', [
                'mode' => 'edit',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'kode_barang' => $product->kode_barang,
                    'barcode' => $product->barcode,
                    'stock' => (int) $product->stock,
                    'status' => $product->status,
                    'gallery_urls' => $product->gallery_urls,
                ],
                'categories' => $categories,
                'rooms' => $rooms,
                'csrfToken' => csrf_token(),
            ]);
        }

        return view('products.edit', compact('product', 'categories', 'rooms'));
    }

    public function update(Request $request, Product $product)
    {
        if ($request->filled('barcode')) {
            $request->merge([
                'barcode' => strtoupper(trim((string) $request->input('barcode'))),
            ]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'kode_barang' => ['nullable', 'string', 'max:50', Rule::unique('products', 'kode_barang')->ignore($product->id)],
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
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'uploaded_tmp_files' => ['nullable', 'array', 'max:8'],
            'uploaded_tmp_files.*' => ['string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:50', 'regex:/^[A-Z0-9\-]+$/', Rule::unique('products', 'barcode')->ignore($product->id)],
        ]);

        unset($data['images'], $data['uploaded_tmp_files']);

        $data = $this->resolveCategoryData($data, $request);
        $data = $this->resolveRoomData($data, $request, $product);

        if (empty($data['kode_barang'])) {
            unset($data['kode_barang']);
        }

        $product->update($data);

        if (empty($product->barcode)) {
            $product->barcode = $this->generateUniqueBarcode();
            $product->save();
        }

        $hasNewMedia = $request->hasFile('image')
            || $request->hasFile('images')
            || ! empty($request->input('uploaded_tmp_files'));

        if ($hasNewMedia) {
            if ($request->hasFile('image') && ! $request->hasFile('images') && empty($request->input('uploaded_tmp_files'))) {
                $product->clearMediaCollection('images');
            }
            $this->attachMultiProductMedia($product, $request);
        } elseif ($request->boolean('remove_image')) {
            $product->clearMediaCollection('images');
        }

        $this->dispatchProductNotifications($product, $request->user(), false);

        return redirect()->route('products.index')->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Barang berhasil dihapus.');
    }

    public function destroySelected(Request $request)
    {
        $validated = $request->validate([
            'selected_ids' => ['required', 'array'],
            'selected_ids.*' => ['required', 'integer', 'exists:products,id'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['selected_ids'])));

        if ($ids === []) {
            return redirect()->route('products.index')->with('error', 'Tidak ada barang yang dipilih.');
        }

        $products = Product::whereIn('id', $ids)->get();

        DB::transaction(function () use ($products): void {
            $products->each->delete();
        });

        return redirect()->route('products.index')->with('success', count($ids).' barang terpilih berhasil dihapus.');
    }

    private function dispatchProductNotifications(Product $product, ?User $actor, bool $isCreated): void
    {
        try {
            $product->loadMissing('room');
            $admins = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])->get();

            if ($isCreated) {
                $otherAdmins = $actor
                    ? $admins->filter(fn (User $u) => (int) $u->id !== (int) $actor->id)
                    : $admins;

                if ($otherAdmins->isNotEmpty()) {
                    Notification::send($otherAdmins, new ProductCreatedNotice($product, $actor));
                }
            }

            if ((int) $product->stock <= 5 || $product->status === 'out_of_stock') {
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new LowStockAlert($product));
                }
            }

            $dispatcher = app(WebhookDispatcher::class);
            $dispatcher->broadcast(
                $isCreated ? 'product.created' : 'product.updated',
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'kode_barang' => $product->kode_barang,
                    'stock' => (int) $product->stock,
                    'room' => $product->room_name,
                ],
                $actor
            );

            if ((int) $product->stock <= 5 || $product->status === 'out_of_stock') {
                $dispatcher->broadcast(
                    'stock.low',
                    [
                        'id' => $product->id,
                        'name' => $product->name,
                        'stock' => (int) $product->stock,
                    ],
                    $actor
                );
            }
        } catch (\Throwable $e) {
            // Non-blocking notification
        }
    }

    private function attachMultiProductMedia(Product $product, Request $request): void
    {
        if ($request->hasFile('image')) {
            $this->attachProductImage($product, $request->file('image'));
        }

        if ($request->hasFile('images')) {
            foreach ((array) $request->file('images') as $imgFile) {
                if ($imgFile instanceof UploadedFile) {
                    $this->attachProductImage($product, $imgFile);
                }
            }
        }

        $tmpFiles = (array) $request->input('uploaded_tmp_files', []);
        foreach ($tmpFiles as $tmpPath) {
            $cleanPath = trim((string) $tmpPath);
            if ($cleanPath !== '' && str_starts_with($cleanPath, 'tmp/uploads/') && ! str_contains($cleanPath, '..')) {
                $this->attachProductImageFromTmpPath($product, $cleanPath);
            }
        }
    }

    private function attachProductImage(Product $product, UploadedFile $file): void
    {
        $storedPath = $file->store('products', 'public');
        $dataUri = $this->buildImageDataUri($file);

        try {
            $product->addMedia($file->getRealPath())
                ->preservingOriginal()
                ->usingFileName($file->getClientOriginalName() ?: basename((string) $storedPath))
                ->withCustomProperties(array_filter([
                    'legacy_path' => $storedPath,
                    'data_url' => $dataUri,
                ]))
                ->toMediaCollection('images');
        } catch (\Throwable $e) {
            // Media library fallback already stored in public/products
        }
    }

    private function attachProductImageFromTmpPath(Product $product, string $tmpPath): void
    {
        try {
            if (! Storage::disk('public')->exists($tmpPath)) {
                return;
            }

            $filename = basename($tmpPath);
            $permanentPath = 'products/'.$filename;
            Storage::disk('public')->copy($tmpPath, $permanentPath);

            $fullPath = Storage::disk('public')->path($permanentPath);
            $product->addMedia($fullPath)
                ->preservingOriginal()
                ->usingFileName($filename)
                ->withCustomProperties([
                    'legacy_path' => $permanentPath,
                ])
                ->toMediaCollection('images');

            Storage::disk('public')->delete($tmpPath);
        } catch (\Throwable $e) {
            // Non-blocking fallback
        }
    }

    private function buildImageDataUri(UploadedFile $file): ?string
    {
        try {
            $realPath = $file->getRealPath();
            if (! $realPath || ! is_readable($realPath)) {
                return null;
            }

            $raw = file_get_contents($realPath);
            if ($raw === false || $raw === '') {
                return null;
            }

            if (function_exists('imagecreatefromstring')) {
                $src = @imagecreatefromstring($raw);
                if ($src !== false) {
                    $width = imagesx($src);
                    $height = imagesy($src);
                    $maxDim = 480;

                    if ($width > $maxDim || $height > $maxDim) {
                        $ratio = min($maxDim / max($width, 1), $maxDim / max($height, 1));
                        $newW = max(1, (int) round($width * $ratio));
                        $newH = max(1, (int) round($height * $ratio));
                        $dst = imagecreatetruecolor($newW, $newH);
                        imagealphablending($dst, false);
                        imagesavealpha($dst, true);
                        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $width, $height);
                        imagedestroy($src);
                        $src = $dst;
                    }

                    ob_start();
                    imagejpeg($src, null, 78);
                    $compressed = ob_get_clean();
                    imagedestroy($src);

                    if (is_string($compressed) && $compressed !== '') {
                        return 'data:image/jpeg;base64,'.base64_encode($compressed);
                    }
                }
            }

            if (strlen($raw) <= 512 * 1024) {
                $mime = $file->getMimeType() ?: 'image/jpeg';

                return 'data:'.$mime.';base64,'.base64_encode($raw);
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    private function resolveCategoryData(array $data, Request $request): array
    {
        if ($request->filled('category')) {
            $name = trim((string) $request->input('category'));
            if ($name !== '') {
                $category = Category::firstOrCreate(
                    ['name' => $name],
                    ['description' => null]
                );

                $data['category_id'] = $category->id;
                $data['category'] = $category->name;

                return $data;
            }
        }

        if ($request->filled('category_id')) {
            $category = Category::find($request->input('category_id'));
            if ($category) {
                $data['category_id'] = $category->id;
                $data['category'] = $category->name;

                return $data;
            }
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

    private function resolveRoomData(array $data, Request $request, ?Product $existingProduct = null): array
    {
        unset($data['room']);

        if ($request->filled('room')) {
            $name = trim((string) $request->input('room'));
            if ($name !== '') {
                $room = Room::firstOrCreate(['name' => $name]);
                $data['room_id'] = $room->id;

                return $data;
            }
        }

        if ($request->filled('room_id')) {
            $room = Room::find($request->input('room_id'));
            if ($room) {
                $data['room_id'] = $room->id;

                return $data;
            }
        }

        if ($existingProduct && $existingProduct->room_id) {
            $room = Room::find($existingProduct->room_id);
            if ($room) {
                $data['room_id'] = $room->id;

                return $data;
            }
        }

        $room = Room::query()->first();
        if ($room) {
            $data['room_id'] = $room->id;

            return $data;
        }

        $data['room_id'] = null;

        return $data;
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'room_id', 'status']);

        ActivityLog::record(
            'export',
            'Mengekspor laporan inventaris barang ke format Excel (.xlsx)',
            Product::class
        );

        return Excel::download(new BarangExport($filters), 'laporan-inventaris-barang.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = trim((string) $request->input('search', ''));
        $categoryId = $request->input('category_id');
        $roomId = $request->input('room_id');
        $status = (string) $request->input('status', 'all');
        $sort = (string) $request->input('sort', 'latest');

        $products = $this->buildFilteredProductsQuery($query, $categoryId, $roomId, $status, $sort)
            ->with(['category', 'room'])
            ->get();

        $summary = [
            'total_items' => $products->count(),
            'total_units' => $products->sum('stock'),
            'active_count' => $products->where('status', 'active')->where('stock', '>', 0)->count(),
            'low_stock_count' => $products->where('stock', '>', 0)->where('stock', '<=', 5)->count(),
            'out_of_stock_count' => $products->filter(fn ($p) => ($p->stock ?? 0) <= 0 || $p->status === 'out_of_stock')->count(),
        ];

        $filterMeta = [
            'search' => $query,
            'category' => $categoryId ? Category::find($categoryId)?->name : 'Semua Kategori',
            'room' => $roomId ? Room::find($roomId)?->name : 'Semua Lokasi Ruangan',
            'status' => $status,
        ];

        ActivityLog::record(
            'export',
            'Mencetak / mengekspor laporan inventaris barang ke format PDF ('.$products->count().' item)',
            Product::class
        );

        return view('products.pdf', compact('products', 'summary', 'filterMeta'));
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    // BARCODE FEATURE: generate kode urut otomatis format BRG-000001
    private function generateUniqueBarcode(): string
    {
        return DB::transaction(function () {
            $last = Product::where('barcode', 'like', 'BRG-%')
                ->lockForUpdate()
                ->latest('id')
                ->value('barcode');

            $nextNumber = 1;
            if ($last && preg_match('/^BRG-(\d+)$/', $last, $m)) {
                $nextNumber = ((int) $m[1]) + 1;
            }

            do {
                $code = 'BRG-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
                $nextNumber++;
            } while (Product::where('barcode', $code)->exists());

            return $code;
        });
    }

    // BARCODE FEATURE: halaman cetak stiker barcode
    public function printBarcode(Product $product)
    {
        $product->loadMissing(['category', 'room']);

        ActivityLog::record(
            'print',
            "Mencetak label stiker barcode untuk {$product->name} ({$product->barcode})",
            $product,
            $product->id
        );

        return view('products.barcode-print', compact('product'));
    }

    // BARCODE FEATURE: halaman scan barcode
    public function scanBarcode(Request $request)
    {
        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Products/Scan');
        }

        return view('products.scan-barcode');
    }

    // BARCODE FEATURE: cari produk berdasarkan hasil scan barcode
    public function scanBarcodeSearch(Request $request)
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9\-]+$/'],
        ]);

        $barcode = strtoupper(trim($validated['barcode']));

        $product = Product::where('barcode', $barcode)
            ->orWhere('kode_barang', $barcode)
            ->first();

        if (! $product) {
            return response()->json([
                'found' => false,
                'message' => 'Barang dengan barcode tersebut tidak ditemukan.',
            ], 404);
        }

        ActivityLog::record(
            'scan',
            "Memindai barcode aset {$product->name} ({$barcode})",
            $product,
            $product->id
        );

        return response()->json([
            'found' => true,
            'redirect_url' => route('products.show', $product->id),
        ]);
    }
}
