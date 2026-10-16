@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-3">
            <a href="{{ route('products.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Kembali ke Daftar">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Detail Barang</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Informasi spesifikasi lengkap, lokasi ruangan, dan riwayat mutasi barang</p>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            <a href="{{ route('mutations.create', ['product_id' => $product->id]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/50 hover:bg-indigo-100 transition-colors">
                <i class="bi bi-arrow-left-right"></i>
                <span>Catat Mutasi</span>
            </a>

            @if($product->barcode)
                <a href="{{ route('products.barcode.print', $product) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm transition-colors">
                    <i class="bi bi-printer"></i>
                    <span>Cetak Barcode</span>
                </a>
            @endif

            <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-[0.98] transition-all">
                <i class="bi bi-pencil"></i>
                <span>Edit Barang</span>
            </a>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Product Image & Barcode Card --}}
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 text-center space-y-4">
            <div class="w-full aspect-square rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800/80 flex items-center justify-center overflow-hidden p-3">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" 
                         alt="{{ $product->name }}" 
                         class="max-w-full max-h-full object-contain rounded-xl"
                         onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}';">
                @else
                    <div class="text-center text-slate-300 dark:text-slate-600">
                        <i class="bi bi-box-seam text-6xl"></i>
                        <p class="text-xs text-slate-400 mt-2 font-medium">Tidak ada foto</p>
                    </div>
                @endif
            </div>

            {{-- Barcode Visual & Kode Barang Display --}}
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800 text-center space-y-2">
                <div class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 tracking-wider">Kode Barcode & Identitas Aset</div>
                @if($product->barcode)
                    <div class="inline-block bg-white px-3 py-2 rounded-lg border border-slate-200 shadow-xs">
                        <svg id="detail-barcode-svg" class="mx-auto max-w-full h-12"></svg>
                    </div>
                    <div class="text-sm font-mono font-bold text-slate-900 dark:text-white">{{ $product->barcode }}</div>
                @endif
                @if($product->kode_barang && $product->kode_barang !== $product->barcode)
                    <div class="text-xs font-mono text-slate-500 dark:text-slate-400">Kode Inventaris: {{ $product->kode_barang }}</div>
                @endif
            </div>
        </div>

        {{-- Right: Specifications & Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Overview Card --}}
            <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $product->name }}</h2>
                        @if($product->subcategory)
                            <p class="text-sm font-medium text-blue-600 dark:text-blue-400 mt-0.5">{{ $product->subcategory }}</p>
                        @endif
                    </div>
                    <div class="shrink-0">
                        @if(($product->stock ?? 0) <= 0 || $product->status === 'out_of_stock')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Stok Habis
                            </span>
                        @elseif($product->status === 'inactive')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Tidak Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                        @endif
                    </div>
                </div>

                <div class="pt-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">Deskripsi & Catatan Spesifikasi</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                        {{ $product->description ?: 'Tidak ada catatan deskripsi tambahan untuk barang ini.' }}
                    </p>
                </div>
            </div>

            {{-- Metric Specs Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kategori</span>
                    <div class="text-sm font-bold text-slate-900 dark:text-white mt-1 truncate">
                        @if($product->category_id)
                            <a href="{{ route('categories.show', $product->category_id) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                {{ $product->category_name }}
                            </a>
                        @else
                            {{ $product->category_name }}
                        @endif
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Lokasi Ruangan</span>
                    <div class="text-sm font-bold text-slate-900 dark:text-white mt-1 truncate">
                        @if($product->room_id)
                            <a href="{{ route('rooms.show', $product->room_id) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                {{ $product->room_name }}
                            </a>
                        @else
                            {{ $product->room_name ?: 'Belum ditentukan' }}
                        @endif
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Stok Tersedia</span>
                    <div class="text-sm sm:text-base font-bold text-blue-600 dark:text-blue-400 mt-1">
                        {{ $product->stock ?? 0 }} Unit
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Terakhir Diperbarui</span>
                    <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mt-1.5">
                        {{ $product->updated_at ? $product->updated_at->format('d M Y, H:i') : '-' }}
                    </div>
                </div>
            </div>

            {{-- Product Mutation History --}}
            <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-clock-history text-blue-600 dark:text-blue-400"></i>
                        <span>Riwayat Mutasi Barang Ini</span>
                    </h3>
                    <a href="{{ route('mutations.index', ['search' => $product->name]) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                        Lihat Semua
                    </a>
                </div>

                @if($product->relationLoaded('mutations') && $product->mutations->isNotEmpty())
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($product->mutations as $mut)
                            <div class="px-5 py-3.5 flex items-center justify-between gap-4 text-xs">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="px-2 py-0.5 rounded-md font-bold uppercase text-[10px]
                                        {{ $mut->type === 'masuk' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : ($mut->type === 'keluar' ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400') }}">
                                        {{ str_replace('_', ' ', $mut->type) }}
                                    </span>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-800 dark:text-slate-200 truncate">
                                            {{ $mut->quantity }} unit — {{ $mut->note ?: 'Tanpa catatan' }}
                                        </p>
                                        <p class="text-[11px] text-slate-400">
                                            Oleh {{ $mut->user?->name ?? 'Sistem' }} • {{ \Carbon\Carbon::parse($mut->mutation_date)->format('d M Y') }}
                                        </p>
                                    </div>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold shrink-0
                                    {{ $mut->status === 'approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : ($mut->status === 'rejected' ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400') }}">
                                    {{ $mut->status === 'approved' ? 'Disetujui' : ($mut->status === 'rejected' ? 'Ditolak' : 'Pending') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-xs text-slate-400 dark:text-slate-500">
                        Belum ada riwayat mutasi untuk barang ini.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($product->barcode)
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const svg = document.getElementById('detail-barcode-svg');
    if (svg && typeof JsBarcode !== 'undefined') {
        try {
            JsBarcode(svg, @json($product->barcode), {
                format: "CODE128",
                width: 1.6,
                height: 44,
                displayValue: false,
                margin: 0
            });
        } catch (e) {}
    }
});
</script>
@endif
@endsection