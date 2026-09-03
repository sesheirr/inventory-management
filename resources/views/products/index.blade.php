@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Top Toolbar Card --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-sm space-y-4">
        
        {{-- Back Button if came from categories --}}
        @if(request('from') === 'categories')
            <div class="pb-2 border-b border-slate-100 dark:border-slate-800">
                <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Kategori
                </a>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
            {{-- Search Bar --}}
            <div class="relative w-full lg:max-w-md">
                <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" 
                       id="realtimeSearch" 
                       name="search" 
                       value="{{ $query ?? '' }}" 
                       placeholder="Cari nama barang, kategori, ruangan, atau barcode..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors" 
                       autocomplete="off">
            </div>

            {{-- Action Buttons Toolbar --}}
            <div class="flex items-center flex-wrap gap-2 justify-end">
                {{-- Delete Mode Toggle --}}
                <button type="button" 
                        id="delete-mode-toggle" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-900/40 transition-colors cursor-pointer">
                    <i class="bi bi-trash"></i>
                    <span>Mode Hapus</span>
                </button>

                {{-- Bulk Delete Controls (Hidden until delete mode is enabled) --}}
                <div id="delete-toolbar" class="hidden items-center gap-2">
                    <button type="button" 
                            id="select-all-btn" 
                            class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                        Pilih Semua
                    </button>
                    
                    <button type="button" 
                            id="btn-bulk-delete" 
                            data-modal-target="bulkDeleteModal"
                            disabled 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:pointer-events-none transition-colors cursor-pointer">
                        <i class="bi bi-trash-fill"></i>
                        <span>Hapus Pilihan</span>
                    </button>

                    <span id="selected-summary" class="text-xs font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">
                        0 dipilih
                    </span>
                </div>

                {{-- Export Excel Button --}}
                <a id="export-btn" 
                   href="{{ route('products.export') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/30 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800/40 transition-colors">
                    <i class="bi bi-file-earmark-excel"></i>
                    <span>Export Excel</span>
                </a>

                {{-- Tambah Barang CTA --}}
                <a id="add-product-btn" 
                   href="{{ route('products.create') }}" 
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-[0.98] transition-all">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah Barang</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Main Data List Card --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden">
        
        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th style="width: 48px;" class="px-4 py-3.5 delete-mode-cell hidden text-center">
                            <input type="checkbox" id="select-all-checkbox" class="w-4 h-4 rounded text-blue-600 border-slate-300 dark:border-slate-600 focus:ring-blue-500" @if($products->isEmpty()) disabled @endif>
                        </th>
                        <th class="px-5 py-3.5">Barang</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5">Ruangan</th>
                        <th class="px-4 py-3.5 text-center">Jumlah</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5">Barcode</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody id="productTableBody" class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @forelse($products as $product)
                        <tr class="product-row hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-3.5 delete-mode-cell hidden text-center">
                                <input type="checkbox" class="product-checkbox w-4 h-4 rounded text-rose-600 border-slate-300 dark:border-slate-600 focus:ring-rose-500" value="{{ $product->id }}">
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden flex items-center justify-center shrink-0">
                                        <img src="{{ $product->hasMedia('images') ? $product->getFirstMediaUrl('images') : asset('images/no-image.png') }}" 
                                             alt="{{ $product->name }}" 
                                             class="w-full h-full object-cover" 
                                             onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                                    </div>
                                    <div class="min-w-0">
                                        <a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors truncate block max-w-xs">
                                            {{ $product->name }}
                                        </a>
                                        @if($product->subcategory)
                                            <span class="text-xs text-slate-400 dark:text-slate-500 truncate block">{{ $product->subcategory }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/40">
                                    {{ $product->category_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    <i class="bi bi-building me-1.5 text-slate-400"></i>
                                    {{ $product->room_name ?: 'Belum diisi' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center font-bold text-slate-900 dark:text-white">
                                {{ $product->stock ?? 0 }}
                            </td>
                            <td class="px-4 py-3.5">
                                @if(($product->stock ?? 0) > 0 && $product->status !== 'inactive')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                @elseif($product->status === 'inactive')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Tidak Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Habis
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if($product->barcode)
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded-md bg-slate-900 text-white font-mono text-[11px] font-bold">
                                            {{ $product->barcode }}
                                        </span>
                                        <a href="{{ route('products.barcode.print', $product) }}" target="_blank" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Cetak Stiker Barcode">
                                            <i class="bi bi-printer text-xs"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400 dark:text-slate-500 italic">Belum ada</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="relative inline-block text-left">
                                    <button type="button" data-dropdown-toggle="dropdown-{{ $product->id }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>

                                    <div id="dropdown-{{ $product->id }}" data-dropdown-menu class="hidden absolute right-0 mt-1 w-44 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl py-1 z-30 animate-fade-in text-left">
                                        <a href="{{ route('products.show', $product) }}" class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600">
                                            <i class="bi bi-eye text-slate-400"></i> Detail Barang
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600">
                                            <i class="bi bi-pencil text-slate-400"></i> Edit Data
                                        </a>
                                        @if(auth()->user()->isAdmin())
                                            <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                                            <button type="button" data-modal-target="deleteModal{{ $product->id }}" class="w-full flex items-center gap-2 px-4 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                                <i class="bi bi-trash"></i> Hapus Barang
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="bi bi-box-seam text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Tidak ada data barang yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards Feed --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($products as $product)
                <div class="p-4 space-y-3 product-mobile-card">
                    <div class="flex items-start gap-3">
                        <div class="delete-mode-cell hidden pt-1">
                            <input type="checkbox" class="product-checkbox w-4 h-4 rounded text-rose-600 border-slate-300 dark:border-slate-600" value="{{ $product->id }}">
                        </div>

                        <div class="w-14 h-14 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden flex items-center justify-center shrink-0">
                            <img src="{{ $product->hasMedia('images') ? $product->getFirstMediaUrl('images') : asset('images/no-image.png') }}" 
                                 alt="{{ $product->name }}" 
                                 class="w-full h-full object-cover" 
                                 onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=No+Image';">
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-1">
                                <a href="{{ route('products.show', $product) }}" class="font-bold text-sm text-slate-900 dark:text-white truncate block">
                                    {{ $product->name }}
                                </a>
                                <span class="text-xs font-bold text-blue-600 dark:text-blue-400 shrink-0">
                                    Stok: {{ $product->stock ?? 0 }}
                                </span>
                            </div>

                            @if($product->subcategory)
                                <p class="text-[11px] text-slate-400 truncate">{{ $product->subcategory }}</p>
                            @endif

                            <div class="mt-2 flex items-center flex-wrap gap-1.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400">
                                    {{ $product->category_name }}
                                </span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ $product->room_name ?: 'Ruangan -' }}
                                </span>
                                @if(($product->stock ?? 0) > 0 && $product->status !== 'inactive')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">
                                        Habis
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Actions bottom bar on mobile --}}
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                        <a href="{{ route('products.show', $product) }}" class="flex-1 py-1.5 rounded-lg text-center text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200">
                            Detail
                        </a>
                        <a href="{{ route('products.edit', $product) }}" class="flex-1 py-1.5 rounded-lg text-center text-xs font-semibold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100">
                            Edit
                        </a>
                        @if(auth()->user()->isAdmin())
                            <button type="button" data-modal-target="deleteModal{{ $product->id }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100">
                                <i class="bi bi-trash"></i>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-slate-400 dark:text-slate-500">
                    Tidak ada barang ditemukan.
                </div>
            @endforelse
        </div>

        {{-- Pagination Footer --}}
        <div class="p-4 sm:p-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
            <div>
                Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} barang
            </div>
            <div>
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Single Delete Modals for Products --}}
@if(auth()->user()->isAdmin())
    @foreach($products as $product)
        <div id="deleteModal{{ $product->id }}" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-center animate-fade-in">
                <button type="button" data-modal-close class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>

                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="bi bi-trash"></i>
                </div>

                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Hapus Barang?</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
                    Apakah Anda yakin ingin menghapus <strong class="text-slate-800 dark:text-slate-200">{{ $product->name }}</strong> dari inventaris? Tindakan ini tidak dapat dibatalkan.
                </p>

                <div class="flex items-center gap-3">
                    <button type="button" data-modal-close class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                        Batal
                    </button>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-all">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

{{-- Bulk Delete Modal --}}
<div id="bulkDeleteModal" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-center animate-fade-in">
        <button type="button" data-modal-close class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="bi bi-x-lg text-sm"></i>
        </button>

        <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl mx-auto mb-4">
            <i class="bi bi-exclamation-triangle"></i>
        </div>

        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2">Hapus Barang Terpilih?</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-6 leading-relaxed">
            Yakin ingin menghapus <span id="bulk-delete-count" class="font-bold text-rose-600">0</span> barang yang dipilih secara masal?
        </p>

        <div class="flex items-center gap-3">
            <button type="button" data-modal-close class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors">
                Batal
            </button>
            <form id="bulk-delete-form" action="{{ route('products.destroySelected') }}" method="POST" class="flex-1">
                @csrf
                <div id="selected-ids-container"></div>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-all">
                    Ya, Hapus Semua
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModeToggle = document.getElementById('delete-mode-toggle');
    const deleteToolbar = document.getElementById('delete-toolbar');
    const deleteModeCells = document.querySelectorAll('.delete-mode-cell');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const selectAllCheckbox = document.getElementById('select-all-checkbox');
    const selectAllBtn = document.getElementById('select-all-btn');
    const btnBulkDelete = document.getElementById('btn-bulk-delete');
    const selectedSummary = document.getElementById('selected-summary');
    const bulkDeleteCount = document.getElementById('bulk-delete-count');
    const selectedIdsContainer = document.getElementById('selected-ids-container');
    const realtimeSearch = document.getElementById('realtimeSearch');
    const exportButton = document.getElementById('export-btn');
    const addProductButton = document.getElementById('add-product-btn');

    let deleteModeEnabled = false;
    let debounceTimer = null;

    function updateRowHighlight(checkbox) {
        const row = checkbox.closest('tr, .product-mobile-card');
        if (!row) return;
        if (checkbox.checked) {
            row.classList.add('bg-rose-50/50', 'dark:bg-rose-950/20');
        } else {
            row.classList.remove('bg-rose-50/50', 'dark:bg-rose-950/20');
        }
    }

    function syncSelectionState() {
        const selected = Array.from(checkboxes).filter(ch => ch.checked).map(ch => ch.value);
        const count = selected.length;
        const isAllSelected = checkboxes.length > 0 && count === checkboxes.length;

        if (selectedSummary) selectedSummary.textContent = `${count} dipilih`;
        if (bulkDeleteCount) bulkDeleteCount.textContent = count;
        if (btnBulkDelete) btnBulkDelete.disabled = count === 0;
        if (selectAllCheckbox) selectAllCheckbox.checked = isAllSelected;
        
        if (selectAllBtn) {
            selectAllBtn.textContent = isAllSelected ? 'Batal Semua' : 'Pilih Semua';
        }

        if (selectedIdsContainer) {
            selectedIdsContainer.innerHTML = '';
            selected.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids[]';
                input.value = id;
                selectedIdsContainer.appendChild(input);
            });
        }
    }

    function toggleDeleteMode() {
        deleteModeEnabled = !deleteModeEnabled;
        
        if (deleteModeEnabled) {
            deleteModeToggle.innerHTML = '<i class="bi bi-x-circle me-1"></i>Selesai';
            deleteModeToggle.classList.remove('text-rose-600', 'bg-rose-50');
            deleteModeToggle.classList.add('text-white', 'bg-rose-600');
            deleteToolbar?.classList.remove('hidden');
            deleteToolbar?.classList.add('flex');
            exportButton?.classList.add('hidden');
            addProductButton?.classList.add('hidden');
        } else {
            deleteModeToggle.innerHTML = '<i class="bi bi-trash me-1"></i>Mode Hapus';
            deleteModeToggle.classList.remove('text-white', 'bg-rose-600');
            deleteModeToggle.classList.add('text-rose-600', 'bg-rose-50');
            deleteToolbar?.classList.add('hidden');
            deleteToolbar?.classList.remove('flex');
            exportButton?.classList.remove('hidden');
            addProductButton?.classList.remove('hidden');
        }

        deleteModeCells.forEach(cell => cell.classList.toggle('hidden', !deleteModeEnabled));

        if (!deleteModeEnabled) {
            checkboxes.forEach(ch => {
                ch.checked = false;
                updateRowHighlight(ch);
            });
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            syncSelectionState();
        }
    }

    deleteModeToggle?.addEventListener('click', toggleDeleteMode);

    selectAllBtn?.addEventListener('click', function () {
        const allSelected = Array.from(checkboxes).every(ch => ch.checked);
        checkboxes.forEach(ch => {
            ch.checked = !allSelected;
            updateRowHighlight(ch);
        });
        syncSelectionState();
    });

    selectAllCheckbox?.addEventListener('change', function () {
        checkboxes.forEach(ch => {
            ch.checked = this.checked;
            updateRowHighlight(ch);
        });
        syncSelectionState();
    });

    checkboxes.forEach(ch => ch.addEventListener('change', function () {
        updateRowHighlight(this);
        syncSelectionState();
    }));

    let isUserTyping = false;

    realtimeSearch?.addEventListener('focus', () => {
        isUserTyping = true;
    });

    realtimeSearch?.addEventListener('input', function () {
        if (!isUserTyping) return;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const params = new URLSearchParams(window.location.search);
            params.set('search', this.value || '');
            window.location.href = `?${params.toString()}`;
        }, 350);
    });
});
</script>
@endsection