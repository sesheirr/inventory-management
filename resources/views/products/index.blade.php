@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Page Header & Quick Summary Strip --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Daftar Inventaris Barang</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pusat pencarian dan pengelolaan seluruh data barang Diskominfo Kabupaten Garut</p>
        </div>

        <div class="flex items-center flex-wrap gap-2">
            {{-- Scan Barcode Shortcut --}}
            <a href="{{ route('products.scan') }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-[#0f1b38] hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-sm transition-colors">
                <i class="bi bi-qr-code-scan text-blue-600 dark:text-blue-400"></i>
                <span>Scan Barcode</span>
            </a>

            {{-- Export Excel Button --}}
            <a id="export-btn"
               href="{{ route('products.export', request()->only(['search', 'category_id', 'room_id', 'status'])) }}"
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

    {{-- Summary Filter Pills --}}
    @if(isset($summary))
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <button type="button" data-quick-status="all" class="quick-status-card text-left p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border {{ ($status ?? 'all') === 'all' ? 'border-blue-500 ring-2 ring-blue-500/15' : 'border-slate-200/80 dark:border-slate-800/80' }} shadow-sm hover:border-blue-400 transition-all cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Barang</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="bi bi-box-seam text-sm"></i>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ $summary['total'] ?? 0 }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Semua data terdaftar</p>
            </button>

            <button type="button" data-quick-status="active" class="quick-status-card text-left p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border {{ ($status ?? '') === 'active' ? 'border-emerald-500 ring-2 ring-emerald-500/15' : 'border-slate-200/80 dark:border-slate-800/80' }} shadow-sm hover:border-emerald-400 transition-all cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Barang Aktif</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i class="bi bi-check2-circle text-sm"></i>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">{{ $summary['active'] ?? 0 }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Siap & tersedia digunakan</p>
            </button>

            <button type="button" data-quick-status="low_stock" class="quick-status-card text-left p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border {{ ($status ?? '') === 'low_stock' ? 'border-amber-500 ring-2 ring-amber-500/15' : 'border-slate-200/80 dark:border-slate-800/80' }} shadow-sm hover:border-amber-400 transition-all cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Stok Menipis</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle text-sm"></i>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1">{{ $summary['low_stock'] ?? 0 }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Sisa stok 1 – 5 unit</p>
            </button>

            <button type="button" data-quick-status="out_of_stock" class="quick-status-card text-left p-4 rounded-2xl bg-white dark:bg-[#0f1b38] border {{ ($status ?? '') === 'out_of_stock' ? 'border-rose-500 ring-2 ring-rose-500/15' : 'border-slate-200/80 dark:border-slate-800/80' }} shadow-sm hover:border-rose-400 transition-all cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Stok Habis</span>
                    <span class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <i class="bi bi-x-octagon text-sm"></i>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1">{{ $summary['out_of_stock'] ?? 0 }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Stok 0 / perlu pengadaan</p>
            </button>
        </div>
    @endif

    {{-- Top Toolbar & Filter Card --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-sm space-y-4">
        {{-- Back Button if came from categories or rooms --}}
        @if(request('from') === 'categories')
            <div class="pb-2 border-b border-slate-100 dark:border-slate-800">
                <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Kategori
                </a>
            </div>
        @elseif(request('from') === 'rooms')
            <div class="pb-2 border-b border-slate-100 dark:border-slate-800">
                <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400">
                    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Ruangan
                </a>
            </div>
        @endif

        <form id="productFilterForm" action="{{ route('products.index') }}" method="GET" class="space-y-3">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                {{-- Search Bar --}}
                <div class="relative lg:col-span-4">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text"
                           id="realtimeSearch"
                           name="search"
                           value="{{ $query ?? '' }}"
                           placeholder="Cari nama barang, kode BRG, ruangan, atau barcode..."
                           class="w-full pl-10 pr-8 py-2.5 rounded-xl text-xs sm:text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors"
                           autocomplete="off">
                </div>

                {{-- Filter Kategori --}}
                <div class="lg:col-span-2">
                    <select name="category_id" id="filterCategory" class="product-filter-select w-full px-3 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                        <option value="">Semua Kategori</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" @selected(($categoryId ?? '') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Ruangan --}}
                <div class="lg:col-span-3">
                    <select name="room_id" id="filterRoom" class="product-filter-select w-full px-3 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                        <option value="">Semua Lokasi Ruangan</option>
                        @foreach($rooms ?? [] as $rm)
                            <option value="{{ $rm->id }}" @selected(($roomId ?? '') == $rm->id)>{{ $rm->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status --}}
                <div class="lg:col-span-3 flex items-center gap-2">
                    <select name="status" id="filterStatus" class="product-filter-select flex-1 px-3 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                        <option value="all" @selected(($status ?? 'all') === 'all')>Semua Status</option>
                        <option value="active" @selected(($status ?? '') === 'active')>Aktif</option>
                        <option value="low_stock" @selected(($status ?? '') === 'low_stock')>Stok Menipis (1-5)</option>
                        <option value="out_of_stock" @selected(($status ?? '') === 'out_of_stock')>Stok Habis (0)</option>
                        <option value="inactive" @selected(($status ?? '') === 'inactive')>Tidak Aktif / Rusak</option>
                    </select>

                    <select name="sort" id="filterSort" class="product-filter-select px-3 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/40" title="Urutkan">
                        <option value="latest" @selected(($sort ?? 'latest') === 'latest')>Terbaru</option>
                        <option value="name_asc" @selected(($sort ?? '') === 'name_asc')>Nama A-Z</option>
                        <option value="name_desc" @selected(($sort ?? '') === 'name_desc')>Nama Z-A</option>
                        <option value="stock_desc" @selected(($sort ?? '') === 'stock_desc')>Stok Terbanyak</option>
                        <option value="stock_asc" @selected(($sort ?? '') === 'stock_asc')>Stok Terdikit</option>
                    </select>
                </div>
            </div>

            {{-- Bottom Toolbar: Active Filter Reset & Bulk Delete Controls --}}
            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <span id="filterStatusIndicator" class="inline-flex items-center gap-1.5">
                        <i class="bi bi-funnel text-blue-600 dark:text-blue-400"></i>
                        <span>Pencarian & Filter Realtime Aktif</span>
                    </span>
                    <button type="button" id="resetFilterBtn" class="px-2.5 py-1 rounded-lg text-xs font-semibold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors cursor-pointer">
                        Reset Filter
                    </button>
                </div>

                {{-- Delete Mode Controls --}}
                <div class="flex items-center flex-wrap gap-2 justify-end">
                    <button type="button"
                            id="delete-mode-toggle"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-900/40 transition-colors cursor-pointer">
                        <i class="bi bi-trash"></i>
                        <span>Mode Hapus Massal</span>
                    </button>

                    <div id="delete-toolbar" class="hidden items-center gap-2">
                        <button type="button"
                                id="select-all-btn"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            Pilih Semua
                        </button>
                        <button type="button"
                                id="btn-bulk-delete"
                                data-modal-target="bulkDeleteModal"
                                disabled
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:pointer-events-none transition-colors cursor-pointer">
                            <i class="bi bi-trash-fill"></i>
                            <span>Hapus Pilihan</span>
                        </button>
                        <span id="selected-summary" class="text-xs font-medium text-slate-500 dark:text-slate-400 whitespace-nowrap">
                            0 dipilih
                        </span>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Data List Card --}}
    <div id="productsListWrapper" class="space-y-4">
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden">
            {{-- Desktop Table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <th style="width: 48px;" class="px-4 py-3.5 delete-mode-cell hidden text-center">
                                <input type="checkbox" id="select-all-checkbox" class="w-4 h-4 rounded text-blue-600 border-slate-300 dark:border-slate-600 focus:ring-blue-500" @if($products->isEmpty()) disabled @endif>
                            </th>
                            <th class="px-5 py-3.5">Barang & Spesifikasi</th>
                            <th class="px-4 py-3.5">Kategori</th>
                            <th class="px-4 py-3.5">Lokasi Ruangan</th>
                            <th class="px-4 py-3.5 text-center">Stok</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Barcode / Kode</th>
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
                                    <div class="flex items-center gap-3.5">
                                        <a href="{{ route('products.show', $product) }}" class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden flex items-center justify-center shrink-0 group">
                                            <img src="{{ $product->image_url ?? asset('images/no-image.png') }}"
                                                 alt="{{ $product->name }}"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                                 onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}';">
                                        </a>
                                        <div class="min-w-0">
                                            <a href="{{ route('products.show', $product) }}" class="font-semibold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors truncate block max-w-xs">
                                                {{ $product->name }}
                                            </a>
                                            @if($product->subcategory)
                                                <span class="text-xs text-slate-500 dark:text-slate-400 truncate block max-w-xs">{{ $product->subcategory }}</span>
                                            @elseif($product->description)
                                                <span class="text-xs text-slate-400 dark:text-slate-500 truncate block max-w-xs">{{ \Illuminate\Support\Str::limit($product->description, 50) }}</span>
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
                                    @if($product->room_id)
                                        <a href="{{ route('rooms.show', $product->room_id) }}" class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-slate-700 transition-colors">
                                            <i class="bi bi-building me-1.5 text-slate-400"></i>
                                            {{ $product->room_name ?: 'Belum diisi' }}
                                        </a>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                            <i class="bi bi-building me-1.5 text-slate-400"></i>
                                            {{ $product->room_name ?: 'Belum diisi' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="font-bold {{ ($product->stock ?? 0) <= 0 ? 'text-rose-600 dark:text-rose-400' : (($product->stock ?? 0) <= 5 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white') }}">
                                        {{ $product->stock ?? 0 }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 block">unit</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if(($product->stock ?? 0) <= 0 || $product->status === 'out_of_stock')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Stok Habis
                                        </span>
                                    @elseif($product->status === 'inactive')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Tidak Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($product->barcode)
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-2 py-0.5 rounded-md bg-slate-900 dark:bg-slate-800 text-white font-mono text-[11px] font-bold border border-slate-700">
                                                {{ $product->barcode }}
                                            </span>
                                            <a href="{{ route('products.barcode.print', $product) }}" target="_blank" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-blue-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Cetak Stiker Barcode">
                                                <i class="bi bi-printer text-xs"></i>
                                            </a>
                                        </div>
                                    @elseif($product->kode_barang)
                                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono text-[11px] font-semibold">
                                            {{ $product->kode_barang }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500 italic">Belum ada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="inline-flex items-center justify-end gap-1">
                                        <a href="{{ route('products.show', $product) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors" title="Detail Barang">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-slate-800 transition-colors" title="Edit Barang">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" data-modal-target="deleteModal{{ $product->id }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer" title="Hapus Barang">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                    <i class="bi bi-box-seam text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                    <p class="font-semibold text-slate-600 dark:text-slate-300">Tidak ada data barang yang ditemukan</p>
                                    <p class="text-xs mt-1">Coba gunakan kata kunci pencarian lain atau reset filter di atas.</p>
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
                                <img src="{{ $product->image_url ?? asset('images/no-image.png') }}"
                                     alt="{{ $product->name }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.src='{{ asset('images/no-image.png') }}';">
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
                                    @if(($product->stock ?? 0) <= 0 || $product->status === 'out_of_stock')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">
                                            Stok Habis
                                        </span>
                                    @elseif($product->status === 'inactive')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">
                                            Tidak Aktif
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                            Aktif
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
                            <button type="button" data-modal-target="deleteModal{{ $product->id }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 cursor-pointer">
                                <i class="bi bi-trash"></i>
                            </button>
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

        {{-- Single Delete Modals for Products --}}
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
                        <button type="button" data-modal-close class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-all cursor-pointer">
                                Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

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
            Yakin ingin menghapus <span id="bulk-delete-count" class="font-bold text-rose-600">0</span> barang yang dipilih secara massal?
        </p>

        <div class="flex items-center gap-3">
            <button type="button" data-modal-close class="flex-1 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                Batal
            </button>
            <form id="bulk-delete-form" action="{{ route('products.destroySelected') }}" method="POST" class="flex-1">
                @csrf
                <div id="selected-ids-container"></div>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm transition-all cursor-pointer">
                    Ya, Hapus Semua
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('productFilterForm');
    const realtimeSearch = document.getElementById('realtimeSearch');
    const filterSelects = document.querySelectorAll('.product-filter-select');
    const resetFilterBtn = document.getElementById('resetFilterBtn');
    const quickStatusCards = document.querySelectorAll('.quick-status-card');
    const filterStatusSelect = document.getElementById('filterStatus');
    const exportButton = document.getElementById('export-btn');
    const addProductButton = document.getElementById('add-product-btn');
    const deleteModeToggle = document.getElementById('delete-mode-toggle');
    const deleteToolbar = document.getElementById('delete-toolbar');
    const selectAllBtn = document.getElementById('select-all-btn');
    const btnBulkDelete = document.getElementById('btn-bulk-delete');
    const selectedSummary = document.getElementById('selected-summary');
    const bulkDeleteCount = document.getElementById('bulk-delete-count');
    const selectedIdsContainer = document.getElementById('selected-ids-container');

    let deleteModeEnabled = false;
    let debounceTimer = null;
    let activeController = null;

    function getCheckboxes() {
        return Array.from(document.querySelectorAll('.product-checkbox'));
    }

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
        const checkboxes = getCheckboxes();
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const selected = Array.from(new Set(checkboxes.filter(ch => ch.checked).map(ch => ch.value)));
        const count = selected.length;
        const isAllSelected = checkboxes.length > 0 && checkboxes.every(ch => ch.checked);

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

    function applyDeleteModeVisibility() {
        const deleteModeCells = document.querySelectorAll('.delete-mode-cell');
        deleteModeCells.forEach(cell => cell.classList.toggle('hidden', !deleteModeEnabled));
    }

    function bindListCheckboxes() {
        const checkboxes = getCheckboxes();
        const selectAllCheckbox = document.getElementById('select-all-checkbox');

        selectAllCheckbox?.addEventListener('change', function () {
            checkboxes.forEach(ch => {
                ch.checked = this.checked;
                updateRowHighlight(ch);
            });
            syncSelectionState();
        });

        checkboxes.forEach(ch => ch.addEventListener('change', function () {
            // Sync duplicate checkbox (desktop + mobile) with same product ID
            checkboxes.filter(other => other.value === this.value).forEach(other => {
                other.checked = this.checked;
                updateRowHighlight(other);
            });
            syncSelectionState();
        }));

        applyDeleteModeVisibility();
    }

    function toggleDeleteMode() {
        deleteModeEnabled = !deleteModeEnabled;

        if (deleteModeEnabled) {
            deleteModeToggle.innerHTML = '<i class="bi bi-x-circle me-1"></i>Selesai';
            deleteModeToggle.classList.remove('text-rose-600', 'bg-rose-50');
            deleteModeToggle.classList.add('text-white', 'bg-rose-600');
            deleteToolbar?.classList.remove('hidden');
            deleteToolbar?.classList.add('flex');
        } else {
            deleteModeToggle.innerHTML = '<i class="bi bi-trash me-1"></i>Mode Hapus Massal';
            deleteModeToggle.classList.remove('text-white', 'bg-rose-600');
            deleteModeToggle.classList.add('text-rose-600', 'bg-rose-50');
            deleteToolbar?.classList.add('hidden');
            deleteToolbar?.classList.remove('flex');
        }

        applyDeleteModeVisibility();

        if (!deleteModeEnabled) {
            getCheckboxes().forEach(ch => {
                ch.checked = false;
                updateRowHighlight(ch);
            });
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            syncSelectionState();
        }
    }

    deleteModeToggle?.addEventListener('click', toggleDeleteMode);

    selectAllBtn?.addEventListener('click', function () {
        const checkboxes = getCheckboxes();
        const allSelected = checkboxes.length > 0 && checkboxes.every(ch => ch.checked);
        checkboxes.forEach(ch => {
            ch.checked = !allSelected;
            updateRowHighlight(ch);
        });
        syncSelectionState();
    });

    async function fetchProductsAjax() {
        if (!filterForm) return;
        const formData = new FormData(filterForm);
        const params = new URLSearchParams(formData);
        const targetUrl = `${filterForm.action}?${params.toString()}`;

        if (exportButton) {
            const exportParams = new URLSearchParams();
            ['search', 'category_id', 'room_id', 'status'].forEach(k => {
                const v = params.get(k);
                if (v) exportParams.set(k, v);
            });
            exportButton.href = `{{ route('products.export') }}${exportParams.toString() ? '?' + exportParams.toString() : ''}`;
        }

        if (activeController) {
            activeController.abort();
        }
        activeController = new AbortController();

        try {
            const res = await fetch(targetUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeController.signal
            });
            if (!res.ok) return;
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newWrapper = doc.getElementById('productsListWrapper');
            const currentWrapper = document.getElementById('productsListWrapper');

            if (newWrapper && currentWrapper) {
                currentWrapper.innerHTML = newWrapper.innerHTML;
                window.history.replaceState({}, '', targetUrl);
                bindListCheckboxes();
                syncSelectionState();
            }
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error(err);
            }
        }
    }

    filterForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        fetchProductsAjax();
    });

    realtimeSearch?.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchProductsAjax, 280);
    });

    filterSelects.forEach(select => {
        select.addEventListener('change', fetchProductsAjax);
    });

    quickStatusCards.forEach(card => {
        card.addEventListener('click', function () {
            const targetStatus = this.getAttribute('data-quick-status') || 'all';
            if (filterStatusSelect) {
                filterStatusSelect.value = targetStatus;
                fetchProductsAjax();
            }
        });
    });

    resetFilterBtn?.addEventListener('click', function () {
        if (realtimeSearch) realtimeSearch.value = '';
        filterSelects.forEach(sel => {
            if (sel.name === 'status') sel.value = 'all';
            else if (sel.name === 'sort') sel.value = 'latest';
            else sel.value = '';
        });
        fetchProductsAjax();
    });

    bindListCheckboxes();
});
</script>
@endsection