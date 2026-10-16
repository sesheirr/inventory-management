@extends('layouts.app')

@section('page-title', 'Barang')
@section('page-subtitle', 'Katalog & pengelolaan inventaris barang')
@section('title', 'Daftar Inventaris Barang — Diskominfo Kabupaten Garut')
@section('share_code', 'cat')
@section('og_title', 'Katalog Inventaris Barang — Diskominfo Kabupaten Garut')
@section('og_description', 'Pusat pencarian cepat data perangkat TIK, jaringan, multimedia, dan aset kantor Dinas Komunikasi dan Informatika Kabupaten Garut.')

@section('content')
<div class="space-y-4 sm:space-y-6" data-framer-page>
    {{-- Page Header & Quick Action Strip --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">Inventaris Barang</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">Katalog aset Diskominfo Kab. Garut</p>
        </div>

        <div class="grid grid-cols-4 sm:flex items-center gap-1.5 sm:gap-2 shrink-0">
            {{-- Scan Shortcut --}}
            <a href="{{ route('products.scan') }}"
               class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-[#0f1b38] hover:bg-slate-50 dark:hover:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-2xs transition-all whitespace-nowrap">
                <i class="bi bi-qr-code-scan text-blue-600 dark:text-blue-400"></i>
                <span>Scan</span>
            </a>

            {{-- PDF Button --}}
            <a id="export-pdf-btn"
               href="{{ route('products.export.pdf', request()->only(['search', 'category_id', 'room_id', 'status', 'sort'])) }}"
               class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3.5 py-2 rounded-xl text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-800/40 transition-all whitespace-nowrap">
                <i class="bi bi-file-earmark-pdf-fill"></i>
                <span>PDF</span>
            </a>

            {{-- Excel Button --}}
            <a id="export-btn"
               href="{{ route('products.export', request()->only(['search', 'category_id', 'room_id', 'status'])) }}"
               class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/30 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 border border-emerald-200 dark:border-emerald-800/40 transition-all whitespace-nowrap">
                <i class="bi bi-file-earmark-excel"></i>
                <span>Excel</span>
            </a>

            {{-- Tambah CTA --}}
            <a id="add-product-btn"
               href="{{ route('products.create') }}"
               class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2 rounded-xl text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/20 transition-all whitespace-nowrap">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah</span>
            </a>
        </div>
    </div>

    {{-- Summary Filter Pills (Live Updated via LiveSyncEngine & Dynamic Active Filter State) --}}
    @if(isset($summary))
        @php
            $hasExplicitFilter = request()->filled('search')
                || request()->filled('category_id')
                || request()->filled('room_id')
                || request()->filled('stock_level')
                || (request()->filled('status') && request('status') !== 'all')
                || request()->boolean('card_active');
            $resolvedStatusKey = $status ?? (request('stock_level') === 'low' ? 'low_stock' : 'all');
            $activeCardKey = $hasExplicitFilter ? $resolvedStatusKey : null;
        @endphp
        <div id="productQuickStatusCardsGrid" class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4" data-framer-stagger>
            <button type="button"
                    data-quick-status="all"
                    data-card-active="{{ $activeCardKey === 'all' ? 'true' : 'false' }}"
                    class="quick-status-card text-left p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#1c1917] border {{ $activeCardKey === 'all' ? 'border-blue-500 ring-2 ring-blue-500/25 bg-blue-50/35 dark:bg-blue-950/25' : 'border-slate-200/80 dark:border-slate-800/80' }} shadow-2xs hover:border-blue-400 transition-all cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        <span>Total</span>
                        <span data-card-active-dot class="{{ $activeCardKey === 'all' ? 'inline-block' : 'hidden' }} w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    </span>
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="bi bi-box-seam text-xs sm:text-sm"></i>
                    </span>
                </div>
                <div data-live-summary="total" class="text-lg sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-1 tabular-nums">{{ $summary['total'] ?? 0 }}</div>
            </button>

            <button type="button"
                    data-quick-status="active"
                    data-card-active="{{ $activeCardKey === 'active' ? 'true' : 'false' }}"
                    class="quick-status-card text-left p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#1c1917] border {{ $activeCardKey === 'active' ? 'border-emerald-500 ring-2 ring-emerald-500/25 bg-emerald-50/35 dark:bg-emerald-950/25' : 'border-slate-200/80 dark:border-slate-800/80' }} shadow-2xs hover:border-emerald-400 transition-all cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        <span>Aktif</span>
                        <span data-card-active-dot class="{{ $activeCardKey === 'active' ? 'inline-block' : 'hidden' }} w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </span>
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i class="bi bi-check2-circle text-xs sm:text-sm"></i>
                    </span>
                </div>
                <div data-live-summary="active" class="text-lg sm:text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 tabular-nums">{{ $summary['active'] ?? 0 }}</div>
            </button>

            <button type="button"
                    data-quick-status="low_stock"
                    data-card-active="{{ $activeCardKey === 'low_stock' ? 'true' : 'false' }}"
                    class="quick-status-card text-left p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#1c1917] border {{ $activeCardKey === 'low_stock' ? 'border-amber-500 ring-2 ring-amber-500/25 bg-amber-50/35 dark:bg-amber-950/25' : 'border-slate-200/80 dark:border-slate-800/80' }} shadow-2xs hover:border-amber-400 transition-all cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        <span>Menipis</span>
                        <span data-card-active-dot class="{{ $activeCardKey === 'low_stock' ? 'inline-block' : 'hidden' }} w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    </span>
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle text-xs sm:text-sm"></i>
                    </span>
                </div>
                <div data-live-summary="low_stock" class="text-lg sm:text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-1 tabular-nums">{{ $summary['low_stock'] ?? 0 }}</div>
            </button>

            <button type="button"
                    data-quick-status="out_of_stock"
                    data-card-active="{{ $activeCardKey === 'out_of_stock' ? 'true' : 'false' }}"
                    class="quick-status-card text-left p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#1c1917] border {{ $activeCardKey === 'out_of_stock' ? 'border-rose-500 ring-2 ring-rose-500/25 bg-rose-50/35 dark:bg-rose-950/25' : 'border-slate-200/80 dark:border-slate-800/80' }} shadow-2xs hover:border-rose-400 transition-all cursor-pointer">
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        <span>Habis</span>
                        <span data-card-active-dot class="{{ $activeCardKey === 'out_of_stock' ? 'inline-block' : 'hidden' }} w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    </span>
                    <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <i class="bi bi-x-octagon text-xs sm:text-sm"></i>
                    </span>
                </div>
                <div data-live-summary="out_of_stock" class="text-lg sm:text-2xl font-extrabold text-rose-600 dark:text-rose-400 mt-1 tabular-nums">{{ $summary['out_of_stock'] ?? 0 }}</div>
            </button>
        </div>
    @endif

    {{-- Top Toolbar & Filter Card --}}
    <div data-framer-card class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-3.5 sm:p-5 shadow-2xs space-y-3">
        @if(request('from') === 'categories')
            <div class="pb-2 border-b border-slate-100 dark:border-slate-800">
                <a href="{{ route('categories.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        @elseif(request('from') === 'rooms')
            <div class="pb-2 border-b border-slate-100 dark:border-slate-800">
                <a href="{{ route('rooms.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        @endif

        <form id="productFilterForm" action="{{ route('products.index') }}" method="GET" class="space-y-2.5">
            <div class="grid grid-cols-2 lg:grid-cols-12 gap-2.5 items-center">
                {{-- Search Bar --}}
                <div class="relative col-span-2 lg:col-span-4">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text"
                           id="realtimeSearch"
                           name="search"
                           value="{{ $query ?? '' }}"
                           placeholder="Cari barang, kode, ruangan, barcode..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors"
                           autocomplete="off">
                </div>

                {{-- Filter Kategori --}}
                <div class="col-span-1 lg:col-span-2">
                    <select name="category_id" id="filterCategory" class="product-filter-select w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                        <option value="">Semua Kategori</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" @selected(($categoryId ?? '') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Ruangan --}}
                <div class="col-span-1 lg:col-span-3">
                    <select name="room_id" id="filterRoom" class="product-filter-select w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                        <option value="">Semua Ruangan</option>
                        @foreach($rooms ?? [] as $rm)
                            <option value="{{ $rm->id }}" @selected(($roomId ?? '') == $rm->id)>{{ $rm->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Status & Sort --}}
                <div class="col-span-2 lg:col-span-3 grid grid-cols-2 gap-2.5">
                    <select name="status" id="filterStatus" class="product-filter-select w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200">
                        <option value="all" @selected(($status ?? 'all') === 'all')>Semua Status</option>
                        <option value="active" @selected(($status ?? '') === 'active')>Aktif</option>
                        <option value="low_stock" @selected(($status ?? '') === 'low_stock')>Menipis (1-5)</option>
                        <option value="out_of_stock" @selected(($status ?? '') === 'out_of_stock')>Habis (0)</option>
                        <option value="inactive" @selected(($status ?? '') === 'inactive')>Nonaktif</option>
                    </select>

                    <select name="sort" id="filterSort" class="product-filter-select w-full px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200" title="Urutkan">
                        <option value="latest" @selected(($sort ?? 'latest') === 'latest')>Terbaru</option>
                        <option value="name_asc" @selected(($sort ?? '') === 'name_asc')>Nama A-Z</option>
                        <option value="name_desc" @selected(($sort ?? '') === 'name_desc')>Nama Z-A</option>
                        <option value="stock_desc" @selected(($sort ?? '') === 'stock_desc')>Stok Terbanyak</option>
                        <option value="stock_asc" @selected(($sort ?? '') === 'stock_asc')>Stok Terdikit</option>
                    </select>
                </div>
            </div>

            {{-- Bottom Toolbar: Active Filter Reset, View Mode Switcher & Bulk Delete Controls --}}
            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                <div class="flex items-center flex-wrap gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <button type="button" id="resetFilterBtn" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl text-xs font-bold text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors cursor-pointer">
                        <i class="bi bi-arrow-counterclockwise"></i>
                        <span>Reset</span>
                    </button>

                    {{-- List / Grid View Switcher --}}
                    <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800" role="group" aria-label="Mode Tampilan Barang">
                        <button type="button"
                                data-product-view-btn="list"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-2xs"
                                title="Tampilan Daftar (List)">
                            <i class="bi bi-list-ul text-xs"></i>
                            <span>List</span>
                        </button>
                        <button type="button"
                                data-product-view-btn="grid"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200"
                                title="Tampilan Katalog (Grid)">
                            <i class="bi bi-grid-fill text-[11px]"></i>
                            <span>Grid</span>
                        </button>
                    </div>
                </div>

                {{-- Delete Mode Controls --}}
                <div class="flex items-center flex-wrap gap-2 justify-end">
                    <button type="button"
                            id="delete-mode-toggle"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 border border-rose-200 dark:border-rose-900/40 transition-colors cursor-pointer">
                        <i class="bi bi-trash"></i>
                        <span>Hapus Massal</span>
                    </button>

                    <div id="delete-toolbar" class="hidden items-center gap-2">
                        <button type="button"
                                id="select-all-btn"
                                class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            Pilih Semua
                        </button>
                        <button type="button"
                                id="btn-bulk-delete"
                                data-modal-target="bulkDeleteModal"
                                disabled
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 disabled:opacity-50 disabled:pointer-events-none transition-colors cursor-pointer">
                            <i class="bi bi-trash-fill"></i>
                            <span>Hapus</span>
                        </button>
                        <span id="selected-summary" class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 whitespace-nowrap tabular-nums">
                            0 dipilih
                        </span>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Data List Card --}}
    <div id="productsListWrapper" class="space-y-4">
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs overflow-hidden">
            {{-- LIST VIEW CONTAINER --}}
            <div id="productsListView" class="products-view-section">
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
                            <th class="px-4 py-3.5 text-center">Stok</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Kode & Stiker</th>
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
                                        <a href="{{ route('products.show', $product) }}" class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden flex items-center justify-center shrink-0 group skeleton-shimmer">
                                            <img src="{{ $product->image_url ?? asset('images/fallback-torn-paper.svg') }}"
                                                 alt="{{ $product->name }}"
                                                 loading="lazy"
                                                 onload="this.parentElement.classList.remove('skeleton-shimmer')"
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform"
                                                 onerror="this.onerror=null; this.src='{{ asset('images/fallback-torn-paper.svg') }}'; this.parentElement.classList.remove('skeleton-shimmer');">
                                        </a>
                                        <div class="min-w-0">
                                            <a href="{{ route('products.show', $product) }}" class="font-bold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors truncate block max-w-xs">
                                                {{ $product->name }}
                                            </a>
                                            @if($product->subcategory)
                                                <span class="text-xs text-slate-500 dark:text-slate-400 truncate block max-w-xs">{{ $product->subcategory }}</span>
                                            @elseif($product->description)
                                                <span class="text-xs text-slate-400 dark:text-slate-500 truncate block max-w-xs">{{ \Illuminate\Support\Str::limit($product->description, 45) }}</span>
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
                                <td class="px-4 py-3.5 text-center tabular-nums">
                                    <span class="font-bold {{ ($product->stock ?? 0) <= 0 ? 'text-rose-600 dark:text-rose-400' : (($product->stock ?? 0) <= 5 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white') }}">
                                        {{ $product->stock ?? 0 }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 block">unit</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if(($product->stock ?? 0) <= 0 || $product->status === 'out_of_stock')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Habis
                                        </span>
                                    @elseif($product->status === 'inactive')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Nonaktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $displayCode = $product->barcode ?: $product->kode_barang;
                                    @endphp
                                    @if($displayCode)
                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                    data-copy="{{ $displayCode }}"
                                                    data-copy-label="Kode Barang"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-900 dark:bg-slate-800 text-white hover:bg-blue-600 font-mono text-[11px] font-bold border border-slate-700 transition-colors cursor-pointer"
                                                    title="Salin {{ $displayCode }}">
                                                <span>{{ $displayCode }}</span>
                                                <i class="bi bi-copy text-[10px] text-slate-300"></i>
                                            </button>

                                            @if($product->barcode)
                                                <button type="button"
                                                        data-barcode-modal="true"
                                                        data-barcode="{{ $product->barcode }}"
                                                        data-product-name="{{ $product->name }}"
                                                        data-product-room="{{ $product->room_name ?: 'Diskominfo Garut' }}"
                                                        data-product-category="{{ $product->category_name }}"
                                                        data-print-url="{{ route('products.barcode.print', $product) }}"
                                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                                                        title="Stiker Barcode">
                                                    <i class="bi bi-printer text-xs"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 dark:text-slate-500 italic">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="inline-flex items-center justify-end gap-1">
                                        <button type="button"
                                                data-share-code="p-{{ $product->id }}"
                                                data-share-title="{{ $product->name }} — Inventaris Diskominfo Garut"
                                                data-share-description="Kategori: {{ $product->category_name }} • Lokasi: {{ $product->room_name ?: 'Diskominfo' }} • Stok: {{ $product->stock }} unit"
                                                data-share-image="{{ $product->image_url ?? asset('images/logo-diskominfo.png') }}"
                                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                                title="Bagikan">
                                            <i class="bi bi-share text-xs"></i>
                                        </button>
                                        <a href="{{ route('products.show', $product) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-slate-800 transition-colors" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" data-modal-target="deleteModal{{ $product->id }}" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                    <i class="bi bi-box-seam text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                    <p class="font-bold text-slate-600 dark:text-slate-300">Barang tidak ditemukan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Native Cards Feed --}}
            <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($products as $product)
                    @php
                        $mobileCode = $product->barcode ?: $product->kode_barang;
                    @endphp
                    <div class="p-3.5 space-y-2.5 product-mobile-card">
                        <div class="flex items-start gap-3">
                            <div class="delete-mode-cell hidden pt-1">
                                <input type="checkbox" class="product-checkbox w-4 h-4 rounded text-rose-600 border-slate-300 dark:border-slate-600" value="{{ $product->id }}">
                            </div>

                            <a href="{{ route('products.show', $product) }}" class="w-13 h-13 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden flex items-center justify-center shrink-0 skeleton-shimmer">
                                <img src="{{ $product->image_url ?? asset('images/fallback-torn-paper.svg') }}"
                                     alt="{{ $product->name }}"
                                     loading="lazy"
                                     onload="this.parentElement.classList.remove('skeleton-shimmer')"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.src='{{ asset('images/fallback-torn-paper.svg') }}'; this.parentElement.classList.remove('skeleton-shimmer');">
                            </a>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <a href="{{ route('products.show', $product) }}" class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white truncate block">
                                        {{ $product->name }}
                                    </a>
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold shrink-0 tabular-nums {{ ($product->stock ?? 0) <= 0 ? 'bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400' : (($product->stock ?? 0) <= 5 ? 'bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400' : 'bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400') }}">
                                        {{ $product->stock ?? 0 }} unit
                                    </span>
                                </div>

                                <div class="mt-1 flex items-center flex-wrap gap-1.5">
                                    @if($mobileCode)
                                        <button type="button"
                                                data-copy="{{ $mobileCode }}"
                                                data-copy-label="Kode Barang"
                                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-900 dark:bg-slate-800 text-white font-mono text-[10px] font-bold cursor-pointer">
                                            <span>{{ $mobileCode }}</span>
                                            <i class="bi bi-copy text-[9px]"></i>
                                        </button>
                                    @endif
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 truncate max-w-[115px]">
                                        {{ $product->category_name }}
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 truncate max-w-[125px]">
                                        {{ $product->room_name ?: '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Actions bottom bar on mobile --}}
                        <div class="flex items-center gap-1.5 pt-2 border-t border-slate-100 dark:border-slate-800/80">
                            <a href="{{ route('products.show', $product) }}" class="flex-1 py-1.5 rounded-lg text-center text-[11px] font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200">
                                Detail
                            </a>
                            @if($product->barcode)
                                <button type="button"
                                        data-barcode-modal="true"
                                        data-barcode="{{ $product->barcode }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-room="{{ $product->room_name ?: 'Diskominfo Garut' }}"
                                        data-product-category="{{ $product->category_name }}"
                                        data-print-url="{{ route('products.barcode.print', $product) }}"
                                        class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 cursor-pointer"
                                        title="Stiker">
                                    <i class="bi bi-printer"></i> Stiker
                                </button>
                            @endif
                            <button type="button"
                                    data-share-code="p-{{ $product->id }}"
                                    data-share-title="{{ $product->name }} — Inventaris Diskominfo Garut"
                                    data-share-description="Kategori: {{ $product->category_name }} • Lokasi: {{ $product->room_name ?: 'Diskominfo' }} • Stok: {{ $product->stock }} unit"
                                    data-share-image="{{ $product->image_url ?? asset('images/logo-diskominfo.png') }}"
                                    class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/30 cursor-pointer"
                                    title="Bagikan">
                                <i class="bi bi-share"></i>
                            </button>
                            <a href="{{ route('products.edit', $product) }}" class="px-2.5 py-1.5 rounded-lg text-center text-[11px] font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100">
                                Edit
                            </a>
                            <button type="button" data-modal-target="deleteModal{{ $product->id }}" class="px-2.5 py-1.5 rounded-lg text-[11px] font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 cursor-pointer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400 dark:text-slate-500">
                        Barang tidak ditemukan.
                    </div>
                @endforelse
            </div>
            </div>{{-- End #productsListView --}}

            {{-- GRID VIEW CONTAINER (Responsive 2-Col Mobile / 3-Col MD / 4-Col XL) --}}
            <div id="productsGridView" class="products-view-section hidden p-3.5 sm:p-5">
                @if($products->isEmpty())
                    <div class="py-12 text-center text-slate-400 dark:text-slate-500">
                        <i class="bi bi-grid-3x3-gap text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                        <p class="font-bold text-sm text-slate-600 dark:text-slate-300">Barang tidak ditemukan</p>
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4">
                        @foreach($products as $product)
                            @php
                                $gridCode = $product->barcode ?: $product->kode_barang;
                            @endphp
                            <div class="product-grid-card group relative flex flex-col rounded-2xl bg-slate-50/60 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 hover:border-blue-400 dark:hover:border-blue-500/60 hover:shadow-md transition-all overflow-hidden">
                                {{-- Top Image & Status Overlay --}}
                                <div class="relative aspect-4/3 w-full bg-slate-100 dark:bg-slate-800 overflow-hidden skeleton-shimmer">
                                    <a href="{{ route('products.show', $product) }}" class="block w-full h-full">
                                        <img src="{{ $product->image_url ?? asset('images/fallback-torn-paper.svg') }}"
                                             alt="{{ $product->name }}"
                                             loading="lazy"
                                             onload="this.closest('.skeleton-shimmer')?.classList.remove('skeleton-shimmer')"
                                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                             onerror="this.onerror=null; this.src='{{ asset('images/fallback-torn-paper.svg') }}'; this.closest('.skeleton-shimmer')?.classList.remove('skeleton-shimmer');">
                                    </a>

                                    {{-- Bulk Delete Checkbox Overlay --}}
                                    <div class="delete-mode-cell hidden absolute top-2.5 left-2.5 z-10">
                                        <label class="w-7 h-7 rounded-lg bg-white/90 dark:bg-slate-900/90 backdrop-blur-xs border border-slate-300 dark:border-slate-700 shadow-xs flex items-center justify-center cursor-pointer">
                                            <input type="checkbox" class="product-checkbox w-4 h-4 rounded text-rose-600 border-slate-300 dark:border-slate-600" value="{{ $product->id }}">
                                        </label>
                                    </div>

                                    {{-- Stock & Status Badge Overlay --}}
                                    <div class="absolute top-2.5 right-2.5 flex items-center gap-1">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold backdrop-blur-md shadow-2xs tabular-nums {{ ($product->stock ?? 0) <= 0 ? 'bg-rose-600/90 text-white' : (($product->stock ?? 0) <= 5 ? 'bg-amber-500/90 text-white' : 'bg-slate-900/80 text-white') }}">
                                            {{ $product->stock ?? 0 }} unit
                                        </span>
                                    </div>

                                    @if($gridCode)
                                        <div class="absolute bottom-2 left-2 right-2 flex items-center justify-between gap-1">
                                            <button type="button"
                                                    data-copy="{{ $gridCode }}"
                                                    data-copy-label="Kode Barang"
                                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-950/80 backdrop-blur-xs text-white hover:bg-blue-600 font-mono text-[10px] font-bold transition-colors cursor-pointer truncate max-w-full"
                                                    title="Salin {{ $gridCode }}">
                                                <span class="truncate">{{ $gridCode }}</span>
                                                <i class="bi bi-copy text-[9px] shrink-0"></i>
                                            </button>

                                            @if($product->barcode)
                                                <button type="button"
                                                        data-barcode-modal="true"
                                                        data-barcode="{{ $product->barcode }}"
                                                        data-product-name="{{ $product->name }}"
                                                        data-product-room="{{ $product->room_name ?: 'Diskominfo Garut' }}"
                                                        data-product-category="{{ $product->category_name }}"
                                                        data-print-url="{{ route('products.barcode.print', $product) }}"
                                                        class="w-6 h-6 rounded-md bg-white/90 dark:bg-slate-900/90 text-slate-700 dark:text-slate-200 hover:text-blue-600 flex items-center justify-center shadow-2xs shrink-0 cursor-pointer"
                                                        title="Stiker Barcode">
                                                    <i class="bi bi-printer text-[11px]"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Card Body --}}
                                <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between gap-2.5">
                                    <div>
                                        <a href="{{ route('products.show', $product) }}" class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors line-clamp-1">
                                            {{ $product->name }}
                                        </a>
                                        <div class="mt-1.5 flex flex-wrap items-center gap-1">
                                            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 truncate max-w-full">
                                                {{ $product->category_name }}
                                            </span>
                                            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 truncate max-w-full inline-flex items-center gap-1">
                                                <i class="bi bi-geo-alt text-[9px] text-slate-400"></i>
                                                <span class="truncate">{{ $product->room_name ?: '-' }}</span>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Footer Action Buttons --}}
                                    <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between gap-1">
                                        <a href="{{ route('products.show', $product) }}" class="flex-1 py-1.5 px-2 rounded-lg text-center text-[11px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50/80 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-colors">
                                            Detail
                                        </a>
                                        <a href="{{ route('products.edit', $product) }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-slate-800 transition-colors" title="Edit">
                                            <i class="bi bi-pencil text-xs"></i>
                                        </a>
                                        <button type="button"
                                                data-share-code="p-{{ $product->id }}"
                                                data-share-title="{{ $product->name }} — Inventaris Diskominfo Garut"
                                                data-share-description="Kategori: {{ $product->category_name }} • Lokasi: {{ $product->room_name ?: 'Diskominfo' }} • Stok: {{ $product->stock }} unit"
                                                data-share-image="{{ $product->image_url ?? asset('images/logo-diskominfo.png') }}"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                                title="Bagikan">
                                            <i class="bi bi-share text-xs"></i>
                                        </button>
                                        <button type="button" data-modal-target="deleteModal{{ $product->id }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors cursor-pointer" title="Hapus">
                                            <i class="bi bi-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>{{-- End #productsGridView --}}

            {{-- Pagination Footer --}}
            <div class="p-3.5 sm:p-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs text-slate-500 dark:text-slate-400">
                <div class="tabular-nums">
                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ $products->firstItem() ?? 0 }}</span>–<span class="font-bold text-slate-700 dark:text-slate-200">{{ $products->lastItem() ?? 0 }}</span> dari <span class="font-bold text-slate-700 dark:text-slate-200">{{ $products->total() }}</span> barang
                </div>
                <div>
                    {{ $products->links() }}
                </div>
            </div>
        </div>

        {{-- Single Delete Modals for Products --}}
        @foreach($products as $product)
            <div id="deleteModal{{ $product->id }}" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
                <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-center animate-fade-in">
                    <button type="button" data-modal-close class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>

                    <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl mx-auto mb-3">
                        <i class="bi bi-trash"></i>
                    </div>

                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5">Hapus Barang?</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-5 leading-relaxed">
                        Hapus <strong class="text-slate-800 dark:text-slate-200">{{ $product->name }}</strong> dari inventaris?
                    </p>

                    <div class="flex items-center gap-2.5">
                        <button type="button" data-modal-close class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                            Batal
                        </button>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="flex-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition-all cursor-pointer">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Bulk Delete Modal --}}
<div id="bulkDeleteModal" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 text-center animate-fade-in">
        <button type="button" data-modal-close class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
            <i class="bi bi-x-lg text-sm"></i>
        </button>

        <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl mx-auto mb-3">
            <i class="bi bi-exclamation-triangle"></i>
        </div>

        <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5">Hapus Barang Terpilih?</h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-5 leading-relaxed">
            Hapus <span id="bulk-delete-count" class="font-bold text-rose-600">0</span> barang yang dipilih?
        </p>

        <div class="flex items-center gap-2.5">
            <button type="button" data-modal-close class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                Batal
            </button>
            <form id="bulk-delete-form" action="{{ route('products.destroySelected') }}" method="POST" class="flex-1">
                @csrf
                <div id="selected-ids-container"></div>
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-xs transition-all cursor-pointer">
                    Hapus Semua
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
    const exportPdfButton = document.getElementById('export-pdf-btn');
    const deleteModeToggle = document.getElementById('delete-mode-toggle');
    const deleteToolbar = document.getElementById('delete-toolbar');
    const selectAllBtn = document.getElementById('select-all-btn');
    const btnBulkDelete = document.getElementById('btn-bulk-delete');
    const selectedSummary = document.getElementById('selected-summary');
    const bulkDeleteCount = document.getElementById('bulk-delete-count');
    const selectedIdsContainer = document.getElementById('selected-ids-container');
    const viewButtons = document.querySelectorAll('[data-product-view-btn]');

    const VIEW_STORAGE_KEY = 'inv_products_view_mode';
    let currentViewMode = localStorage.getItem(VIEW_STORAGE_KEY) === 'grid' ? 'grid' : 'list';
    let deleteModeEnabled = false;
    let debounceTimer = null;
    let activeController = null;

    function applyProductViewMode(mode, animateSwitch = false) {
        currentViewMode = mode === 'grid' ? 'grid' : 'list';
        localStorage.setItem(VIEW_STORAGE_KEY, currentViewMode);

        const listEl = document.getElementById('productsListView');
        const gridEl = document.getElementById('productsGridView');

        viewButtons.forEach(btn => {
            const isTarget = btn.getAttribute('data-product-view-btn') === currentViewMode;
            btn.classList.toggle('bg-white', isTarget);
            btn.classList.toggle('dark:bg-slate-800', isTarget);
            btn.classList.toggle('text-blue-600', isTarget);
            btn.classList.toggle('dark:text-blue-400', isTarget);
            btn.classList.toggle('shadow-2xs', isTarget);
            btn.classList.toggle('text-slate-500', !isTarget);
            btn.classList.toggle('dark:text-slate-400', !isTarget);
        });

        const showEl = currentViewMode === 'grid' ? gridEl : listEl;
        const hideEl = currentViewMode === 'grid' ? listEl : gridEl;

        hideEl?.classList.add('hidden');
        if (showEl) {
            showEl.classList.remove('hidden');
            if (animateSwitch && typeof showEl.animate === 'function') {
                showEl.animate(
                    [
                        { opacity: 0, transform: 'translateY(6px) scale(0.992)' },
                        { opacity: 1, transform: 'translateY(0) scale(1)' }
                    ],
                    { duration: 220, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
                );
            }
        }
    }

    viewButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const targetMode = this.getAttribute('data-product-view-btn') || 'list';
            if (targetMode !== currentViewMode) {
                applyProductViewMode(targetMode, true);
            }
        });
    });

    function getCheckboxes() {
        return Array.from(document.querySelectorAll('.product-checkbox'));
    }

    function updateRowHighlight(checkbox) {
        const row = checkbox.closest('tr, .product-mobile-card, .product-grid-card');
        if (!row) return;
        if (checkbox.checked) {
            row.classList.add('bg-rose-50/50', 'dark:bg-rose-950/20', 'ring-2', 'ring-rose-500/40');
        } else {
            row.classList.remove('bg-rose-50/50', 'dark:bg-rose-950/20', 'ring-2', 'ring-rose-500/40');
        }
    }

    function syncSelectionState() {
        const checkboxes = getCheckboxes();
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const uniqueValues = Array.from(new Set(checkboxes.map(ch => ch.value)));
        const selected = Array.from(new Set(checkboxes.filter(ch => ch.checked).map(ch => ch.value)));
        const count = selected.length;
        const isAllSelected = uniqueValues.length > 0 && selected.length === uniqueValues.length;

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
            deleteModeToggle.innerHTML = '<i class="bi bi-trash me-1"></i>Hapus Massal';
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
        const uniqueValues = Array.from(new Set(checkboxes.map(ch => ch.value)));
        const selected = Array.from(new Set(checkboxes.filter(ch => ch.checked).map(ch => ch.value)));
        const allSelected = uniqueValues.length > 0 && selected.length === uniqueValues.length;
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

        const exportParams = new URLSearchParams();
        ['search', 'category_id', 'room_id', 'status', 'sort'].forEach(k => {
            const v = params.get(k);
            if (v) exportParams.set(k, v);
        });
        const qs = exportParams.toString() ? '?' + exportParams.toString() : '';

        if (exportButton) {
            exportButton.href = `{{ route('products.export') }}${qs}`;
        }
        if (exportPdfButton) {
            exportPdfButton.href = `{{ route('products.export.pdf') }}${qs}`;
        }

        if (activeController) {
            activeController.abort();
        }
        activeController = new AbortController();

        const currentWrapper = document.getElementById('productsListWrapper');
        currentWrapper?.classList.add('ajax-loading-skeleton');

        try {
            const res = await fetch(targetUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                signal: activeController.signal
            });
            if (!res.ok) return;
            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newWrapper = doc.getElementById('productsListWrapper');

            if (newWrapper && currentWrapper) {
                currentWrapper.innerHTML = newWrapper.innerHTML;
                window.history.replaceState({}, '', targetUrl);
                applyProductViewMode(currentViewMode, false);
                bindListCheckboxes();
                syncSelectionState();
                syncQuickStatusCardsActiveState();
                if (typeof window.runFramerMotionEntrance === 'function') {
                    window.runFramerMotionEntrance(currentWrapper);
                }
            }
        } catch (err) {
            if (err.name !== 'AbortError') {
                console.error(err);
            }
        } finally {
            currentWrapper?.classList.remove('ajax-loading-skeleton');
        }
    }

    let explicitAllCardClicked = {{ request()->boolean('card_active') ? 'true' : 'false' }};

    function syncQuickStatusCardsActiveState(forceClearAll = false) {
        const statusVal = filterStatusSelect ? filterStatusSelect.value : 'all';
        const searchVal = (realtimeSearch?.value || '').trim();
        let hasOtherFilter = searchVal.length > 0;
        filterSelects.forEach(s => {
            if ((s.name === 'category_id' || s.name === 'room_id') && s.value) {
                hasOtherFilter = true;
            }
        });

        let activeKey = null;
        if (!forceClearAll) {
            if (statusVal && statusVal !== 'all') {
                activeKey = statusVal;
            } else if (explicitAllCardClicked || hasOtherFilter) {
                activeKey = 'all';
            }
        }

        const activeStylesMap = {
            all: ['border-blue-500', 'ring-2', 'ring-blue-500/25', 'bg-blue-50/35', 'dark:bg-blue-950/25'],
            active: ['border-emerald-500', 'ring-2', 'ring-emerald-500/25', 'bg-emerald-50/35', 'dark:bg-emerald-950/25'],
            low_stock: ['border-amber-500', 'ring-2', 'ring-amber-500/25', 'bg-amber-50/35', 'dark:bg-amber-950/25'],
            out_of_stock: ['border-rose-500', 'ring-2', 'ring-rose-500/25', 'bg-rose-50/35', 'dark:bg-rose-950/25'],
        };
        const allPossibleActiveClasses = Object.values(activeStylesMap).flat();

        quickStatusCards.forEach(card => {
            const key = card.getAttribute('data-quick-status') || '';
            const isCardActive = activeKey !== null && key === activeKey;
            card.setAttribute('data-card-active', isCardActive ? 'true' : 'false');
            card.classList.remove(...allPossibleActiveClasses, 'border-slate-200/80', 'dark:border-slate-800/80');

            if (isCardActive && activeStylesMap[key]) {
                card.classList.add(...activeStylesMap[key]);
            } else {
                card.classList.add('border-slate-200/80', 'dark:border-slate-800/80');
            }

            const dot = card.querySelector('[data-card-active-dot]');
            if (dot) {
                dot.classList.toggle('hidden', !isCardActive);
                dot.classList.toggle('inline-block', isCardActive);
            }
        });
    }

    window.refreshProductsTableRealtime = fetchProductsAjax;

    filterForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        syncQuickStatusCardsActiveState();
        fetchProductsAjax();
    });

    realtimeSearch?.addEventListener('input', function () {
        syncQuickStatusCardsActiveState();
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fetchProductsAjax, 260);
    });

    filterSelects.forEach(select => {
        select.addEventListener('change', function () {
            if (select.name === 'status' && select.value !== 'all') {
                explicitAllCardClicked = false;
            }
            syncQuickStatusCardsActiveState();
            fetchProductsAjax();
        });
    });

    quickStatusCards.forEach(card => {
        card.addEventListener('click', function () {
            const targetStatus = this.getAttribute('data-quick-status') || 'all';
            const currentlyActive = this.getAttribute('data-card-active') === 'true';

            if (currentlyActive) {
                // Clicking an already-active card toggles it off (resets status filter)
                explicitAllCardClicked = false;
                if (filterStatusSelect) {
                    filterStatusSelect.value = 'all';
                    if (typeof filterStatusSelect._ssRefresh === 'function') filterStatusSelect._ssRefresh();
                }
                syncQuickStatusCardsActiveState(true);
                fetchProductsAjax();
                return;
            }

            explicitAllCardClicked = (targetStatus === 'all');
            if (filterStatusSelect) {
                filterStatusSelect.value = targetStatus;
                if (typeof filterStatusSelect._ssRefresh === 'function') filterStatusSelect._ssRefresh();
                syncQuickStatusCardsActiveState();
                fetchProductsAjax();
            }
        });
    });

    resetFilterBtn?.addEventListener('click', function () {
        explicitAllCardClicked = false;
        if (realtimeSearch) realtimeSearch.value = '';
        filterSelects.forEach(select => {
            if (select.name === 'status') select.value = 'all';
            else if (select.name === 'sort') select.value = 'latest';
            else select.value = '';
            if (typeof select._ssRefresh === 'function') select._ssRefresh();
        });
        syncQuickStatusCardsActiveState(true);
        fetchProductsAjax();
    });

    applyProductViewMode(currentViewMode, false);
    bindListCheckboxes();
    syncSelectionState();
    syncQuickStatusCardsActiveState();
});
</script>
@endsection