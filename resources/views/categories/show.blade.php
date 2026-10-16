@extends('layouts.app')

@section('page-title', 'Detail Kategori')
@section('page-subtitle', 'Katalog aset ' . $category->name)
@section('title', 'Kategori ' . $category->name . ' — Inventaris Diskominfo Garut')
@section('share_code', 'c-' . $category->id)
@section('og_title', 'Kategori ' . $category->name . ' — Inventaris Diskominfo Garut')
@section('og_description', 'Katalog aset dan barang kategori ' . $category->name . ' di lingkungan Diskominfo Kabupaten Garut.')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 sm:space-y-6" data-framer-page>
    <div class="flex items-center justify-between gap-3 pb-3 sm:pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
            <a href="{{ route('categories.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors shrink-0" title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-base sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white truncate">Kategori {{ $category->name }}</h1>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 truncate">Detail kategori & daftar barang</p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button"
                    data-share-code="c-{{ $category->id }}"
                    data-share-title="Kategori {{ $category->name }} — Inventaris Diskominfo Garut"
                    data-share-description="Katalog aset kategori {{ $category->name }} ({{ $category->products_count ?? $category->products->count() }} jenis barang)."
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 transition-colors cursor-pointer">
                <i class="bi bi-share text-indigo-600 dark:text-indigo-400"></i>
                <span>Bagikan</span>
            </button>

            @if(auth()->user()->isAdmin())
                <a href="{{ route('categories.edit', $category) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-all">
                    <i class="bi bi-pencil"></i>
                    <span>Edit</span>
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:gap-4" data-framer-stagger>
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Kategori</span>
            <div class="text-sm sm:text-lg font-extrabold text-slate-900 dark:text-white truncate">{{ $category->name }}</div>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Barang</span>
            <div class="text-sm sm:text-lg font-extrabold text-blue-600 dark:text-blue-400 tabular-nums">{{ $category->products_count ?? $category->products->count() }} Barang</div>
        </div>

        @if($category->description)
            <div class="col-span-2 p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs space-y-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Deskripsi</span>
                <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                    {{ $category->description }}
                </p>
            </div>
        @endif
    </div>

    {{-- Daftar Barang dalam Kategori Ini --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">Daftar Barang</h2>
            <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                <span>Katalog</span>
                <i class="bi bi-arrow-right text-[10px]"></i>
            </a>
        </div>

        @if(isset($products) && $products->isNotEmpty())
            {{-- Mobile Compact List --}}
            <div class="sm:hidden divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($products as $product)
                    <a href="{{ route('products.show', $product) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $product->name }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                <span class="font-mono text-[10px] text-blue-600 dark:text-blue-400">{{ $product->barcode ?: ($product->kode_barang ?: '-') }}</span>
                                • {{ $product->room?->name ?? '-' }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-extrabold tabular-nums bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 shrink-0">
                            {{ $product->stock }} unit
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Desktop Table --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-5 py-3">Nama Barang</th>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Ruangan</th>
                            <th class="px-4 py-3">Stok</th>
                            <th class="px-4 py-3 text-right">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs sm:text-sm">
                        @foreach($products as $product)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-5 py-3 font-bold text-slate-900 dark:text-white">
                                    <a href="{{ route('products.show', $product) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                        {{ $product->name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-500 dark:text-slate-400">
                                    {{ $product->barcode ?: ($product->kode_barang ?: '-') }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">
                                    {{ $product->room?->name ?? 'Belum ditentukan' }}
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-white tabular-nums">
                                    {{ $product->stock }} unit
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $product->status === 'active' && $product->stock > 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400' }}">
                                        {{ $product->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $products->links() }}
                </div>
            @endif
        @else
            <div class="p-8 text-center text-xs text-slate-400">
                Belum ada barang dalam kategori ini.
            </div>
        @endif
    </div>
</div>
@endsection