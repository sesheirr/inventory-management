@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-3">
            <a href="{{ route('categories.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Detail Kategori</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Informasi kategori barang dan total aset terkait</p>
            </div>
        </div>

        @if(auth()->user()->isAdmin())
            <a href="{{ route('categories.edit', $category) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all">
                <i class="bi bi-pencil"></i>
                <span>Edit Kategori</span>
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-6 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Nama Kategori</span>
            <div class="text-xl font-bold text-slate-900 dark:text-white">{{ $category->name }}</div>
        </div>

        <div class="p-6 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Jenis Barang Terdaftar</span>
            <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $category->products_count ?? $category->products->count() }} Barang</div>
        </div>

        <div class="md:col-span-2 p-6 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Deskripsi</span>
            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                {{ $category->description ?: 'Tidak ada deskripsi tambahan untuk kategori ini.' }}
            </p>
        </div>
    </div>

    {{-- Daftar Barang dalam Kategori Ini --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Daftar Barang dalam Kategori "{{ $category->name }}"</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Klik pada nama barang untuk melihat detail, barcode, dan riwayat mutasinya</p>
            </div>
            <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                Buka di Katalog <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @if(isset($products) && $products->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th class="px-5 py-3">Nama Barang</th>
                            <th class="px-4 py-3">Kode / Barcode</th>
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
                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
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
                Belum ada barang yang terdaftar dalam kategori ini.
            </div>
        @endif
    </div>
</div>
@endsection