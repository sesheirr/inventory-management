<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function resolve(Request $request, string $code)
    {
        $cleanCode = strtolower(trim($code));
        $meta = $this->resolveTargetMeta($cleanCode);

        if ($request->boolean('redirect')) {
            return redirect()->to($meta['target_url']);
        }

        return response()->view('share.preview', [
            'code' => $cleanCode,
            'meta' => $meta,
            'shortUrl' => route('share.short', ['code' => $cleanCode]),
        ]);
    }

    private function resolveTargetMeta(string $code): array
    {
        $defaultImage = asset('images/logo-diskominfo.png');

        if (preg_match('/^p-(\d+)$/', $code, $m)) {
            $product = Product::with(['category', 'room', 'media'])->find((int) $m[1]);
            if ($product) {
                $roomName = $product->room_name ?: 'Diskominfo Garut';
                $catName = $product->category_name ?: 'Inventaris';
                $codeText = $product->barcode ?: $product->kode_barang;

                return [
                    'type' => 'Barang Inventaris',
                    'badge' => $codeText ?: 'ASET DISKOMINFO',
                    'title' => "{$product->name} — Inventaris Diskominfo Garut",
                    'description' => "Kategori: {$catName} • Lokasi: {$roomName} • Stok: {$product->stock} unit. ".($product->description ?: 'Klik tautan untuk membuka detail spesifikasi dan riwayat mutasi barang.'),
                    'image' => $product->image_url ?: $defaultImage,
                    'target_url' => route('products.show', $product->id),
                ];
            }
        }

        if (preg_match('/^r-(\d+)$/', $code, $m)) {
            $room = Room::withCount('products')->find((int) $m[1]);
            if ($room) {
                return [
                    'type' => 'Lokasi Ruangan',
                    'badge' => ($room->products_count ?? 0).' Jenis Barang',
                    'title' => "Ruangan {$room->name} — Inventaris Diskominfo Garut",
                    'description' => ($room->description ?: "Daftar penempatan aset dan perangkat inventaris di {$room->name} Diskominfo Kabupaten Garut.")." Total terdata: {$room->products_count} jenis barang.",
                    'image' => $defaultImage,
                    'target_url' => route('rooms.show', $room->id),
                ];
            }
        }

        if (preg_match('/^c-(\d+)$/', $code, $m)) {
            $category = Category::withCount('products')->find((int) $m[1]);
            if ($category) {
                return [
                    'type' => 'Kategori Inventaris',
                    'badge' => ($category->products_count ?? 0).' Jenis Barang',
                    'title' => "Kategori {$category->name} — Inventaris Diskominfo Garut",
                    'description' => ($category->description ?: "Katalog aset dan barang kategori {$category->name} di lingkungan Diskominfo Kabupaten Garut."),
                    'image' => $defaultImage,
                    'target_url' => route('categories.show', $category->id),
                ];
            }
        }

        if (preg_match('/^m-(\d+)$/', $code, $m)) {
            $mutation = Mutation::with(['product', 'user', 'fromRoom', 'toRoom'])->find((int) $m[1]);
            if ($mutation) {
                $productName = $mutation->product?->name ?? 'Barang Inventaris';
                $statusMap = [
                    'pending' => 'Menunggu Persetujuan',
                    'approved' => 'Disetujui',
                    'rejected' => 'Ditolak',
                ];
                $statusLabel = $statusMap[$mutation->status] ?? ucfirst((string) $mutation->status);

                return [
                    'type' => 'Mutasi Barang',
                    'badge' => $statusLabel,
                    'title' => "Mutasi {$productName} ({$mutation->quantity} Unit) — Diskominfo Garut",
                    'description' => 'Catatan mutasi ('.str_replace('_', ' ', (string) $mutation->type).") sebanyak {$mutation->quantity} unit oleh ".($mutation->user?->name ?? 'Petugas').". Status: {$statusLabel}.",
                    'image' => $mutation->product?->image_url ?: $defaultImage,
                    'target_url' => route('mutations.index', ['highlight' => $mutation->id]),
                ];
            }
        }

        if ($code === 'cat' || $code === 'products') {
            return [
                'type' => 'Katalog Inventaris',
                'badge' => Product::count().' Total Aset',
                'title' => 'Katalog Inventaris Barang — Diskominfo Kabupaten Garut',
                'description' => 'Pusat pencarian cepat data perangkat TIK, jaringan, multimedia, dan aset kantor Dinas Komunikasi dan Informatika Kabupaten Garut.',
                'image' => $defaultImage,
                'target_url' => route('products.index'),
            ];
        }

        if ($code === 'scan') {
            return [
                'type' => 'Pemindai Aset',
                'badge' => 'Barcode Scanner',
                'title' => 'Scan Barcode Inventaris — Diskominfo Kabupaten Garut',
                'description' => 'Pindai stiker barcode barang untuk melihat spesifikasi, lokasi ruangan, dan riwayat mutasi secara instan.',
                'image' => $defaultImage,
                'target_url' => route('products.scan'),
            ];
        }

        return [
            'type' => 'Sistem Informasi Inventaris',
            'badge' => 'DISKOMINFO GARUT',
            'title' => 'Sistem Inventaris Barang — Diskominfo Kabupaten Garut',
            'description' => 'Aplikasi pengelolaan data barang, lokasi ruangan, barcode stiker, dan mutasi aset Dinas Komunikasi dan Informatika Kabupaten Garut.',
            'image' => $defaultImage,
            'target_url' => route('dashboard'),
        ];
    }
}
