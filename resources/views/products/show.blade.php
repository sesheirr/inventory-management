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
                <p class="text-xs text-slate-500 dark:text-slate-400">Informasi spesifikasi lengkap dan status inventaris</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
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
        {{-- Left: Product Image Card --}}
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 text-center space-y-4">
            <div class="w-full aspect-square rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800/80 flex items-center justify-center overflow-hidden p-3">
                @if($product->hasMedia('images'))
                    <img src="{{ $product->getFirstMediaUrl('images') }}" 
                         alt="{{ $product->name }}" 
                         class="max-w-full max-h-full object-contain rounded-xl"
                         onerror="this.onerror=null; this.src='https://placehold.co/400x400?text=Gambar+Error';">
                @else
                    <div class="text-center text-slate-300 dark:text-slate-600">
                        <i class="bi bi-box-seam text-6xl"></i>
                        <p class="text-xs text-slate-400 mt-2 font-medium">Tidak ada foto</p>
                    </div>
                @endif
            </div>

            {{-- Barcode Badge Display --}}
            @if($product->barcode)
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800 text-center">
                    <div class="text-[10px] uppercase font-bold text-slate-400 dark:text-slate-500 tracking-wider">Kode Barcode</div>
                    <div class="text-sm font-mono font-bold text-slate-900 dark:text-white mt-0.5">{{ $product->barcode }}</div>
                </div>
            @endif
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
                    <div>
                        @if(($product->stock ?? 0) > 0 && $product->status !== 'inactive')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                            </span>
                        @elseif($product->status === 'inactive')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Tidak Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Stok Habis
                            </span>
                        @endif
                    </div>
                </div>

                <div class="pt-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1.5">Deskripsi Barang</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-900/40 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                        {{ $product->description ?: 'Tidak ada catatan deskripsi tambahan untuk barang ini.' }}
                    </p>
                </div>
            </div>

            {{-- Metric Specs Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kategori</span>
                    <div class="text-sm sm:text-base font-bold text-slate-900 dark:text-white mt-1">
                        {{ $product->category_name }}
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Lokasi Ruangan</span>
                    <div class="text-sm sm:text-base font-bold text-slate-900 dark:text-white mt-1">
                        {{ $product->room_name ?: 'Belum ditentukan' }}
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Stok Tersedia</span>
                    <div class="text-sm sm:text-base font-bold text-blue-600 dark:text-blue-400 mt-1">
                        {{ $product->stock ?? 0 }} Unit
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection