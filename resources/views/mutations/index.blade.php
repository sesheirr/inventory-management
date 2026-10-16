@extends('layouts.app')

@section('page-title', 'Mutasi')
@section('page-subtitle', 'Riwayat barang masuk, keluar & pindah ruangan')

@section('content')
@php
    $pCounts = $pipelineCounts ?? [
        'all' => $mutations->total(),
        'pending' => 0,
        'approved' => 0,
        'rejected' => 0,
        'masuk' => 0,
        'pindah_ruang' => 0,
        'keluar' => 0,
    ];
    $activeStatus = $status ?? '';
    $activeType = $type ?? '';
    $highlightId = (int) request('highlight', 0);
@endphp
<div class="space-y-4 sm:space-y-5" data-framer-page>
    {{-- Logistics Status Pipeline Bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 sm:gap-3" data-framer-stagger>
        <a href="{{ route('mutations.index', array_filter(['search' => $search, 'type' => $type, 'product_id' => $productId])) }}"
           class="p-3.5 rounded-2xl border transition-all flex items-center justify-between gap-2 group {{ $activeStatus === '' ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-600/20' : 'bg-white dark:bg-[#0f1b38] text-slate-700 dark:text-slate-200 border-slate-200/80 dark:border-slate-800 hover:border-blue-400' }}">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider {{ $activeStatus === '' ? 'text-blue-100' : 'text-slate-400 dark:text-slate-500' }}">Semua Transaksi</p>
                <p class="text-lg sm:text-xl font-extrabold tabular-nums leading-tight mt-0.5">{{ $pCounts['all'] ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $activeStatus === '' ? 'bg-white/20 text-white' : 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400' }}">
                <i class="bi bi-layers-fill text-sm"></i>
            </div>
        </a>

        <a href="{{ route('mutations.index', array_filter(['status' => 'pending', 'search' => $search, 'type' => $type, 'product_id' => $productId])) }}"
           class="p-3.5 rounded-2xl border transition-all flex items-center justify-between gap-2 group {{ $activeStatus === 'pending' ? 'bg-amber-500 text-white border-amber-500 shadow-md shadow-amber-500/20' : 'bg-white dark:bg-[#0f1b38] text-slate-700 dark:text-slate-200 border-slate-200/80 dark:border-slate-800 hover:border-amber-400' }}">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider {{ $activeStatus === 'pending' ? 'text-amber-50' : 'text-amber-600 dark:text-amber-400' }}">Menunggu Verifikasi</p>
                <p class="text-lg sm:text-xl font-extrabold tabular-nums leading-tight mt-0.5">{{ $pCounts['pending'] ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $activeStatus === 'pending' ? 'bg-white/20 text-white' : 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400' }}">
                <i class="bi bi-hourglass-split text-sm"></i>
            </div>
        </a>

        <a href="{{ route('mutations.index', array_filter(['status' => 'approved', 'search' => $search, 'type' => $type, 'product_id' => $productId])) }}"
           class="p-3.5 rounded-2xl border transition-all flex items-center justify-between gap-2 group {{ $activeStatus === 'approved' ? 'bg-emerald-600 text-white border-emerald-600 shadow-md shadow-emerald-600/20' : 'bg-white dark:bg-[#0f1b38] text-slate-700 dark:text-slate-200 border-slate-200/80 dark:border-slate-800 hover:border-emerald-400' }}">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider {{ $activeStatus === 'approved' ? 'text-emerald-100' : 'text-emerald-600 dark:text-emerald-400' }}">Selesai / Disetujui</p>
                <p class="text-lg sm:text-xl font-extrabold tabular-nums leading-tight mt-0.5">{{ $pCounts['approved'] ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $activeStatus === 'approved' ? 'bg-white/20 text-white' : 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400' }}">
                <i class="bi bi-check2-circle text-sm"></i>
            </div>
        </a>

        <a href="{{ route('mutations.index', array_filter(['status' => 'rejected', 'search' => $search, 'type' => $type, 'product_id' => $productId])) }}"
           class="p-3.5 rounded-2xl border transition-all flex items-center justify-between gap-2 group {{ $activeStatus === 'rejected' ? 'bg-rose-600 text-white border-rose-600 shadow-md shadow-rose-600/20' : 'bg-white dark:bg-[#0f1b38] text-slate-700 dark:text-slate-200 border-slate-200/80 dark:border-slate-800 hover:border-rose-400' }}">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-wider {{ $activeStatus === 'rejected' ? 'text-rose-100' : 'text-rose-600 dark:text-rose-400' }}">Ditolak</p>
                <p class="text-lg sm:text-xl font-extrabold tabular-nums leading-tight mt-0.5">{{ $pCounts['rejected'] ?? 0 }}</p>
            </div>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $activeStatus === 'rejected' ? 'bg-white/20 text-white' : 'bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400' }}">
                <i class="bi bi-x-octagon text-sm"></i>
            </div>
        </a>
    </div>

    {{-- Header & Top Controls --}}
    <div data-framer-card class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-2xs space-y-3.5 relative z-20">
        <div class="flex items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">Mutasi Barang</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">Kendali alur barang masuk, distribusi keluar & perpindahan antar-ruangan</p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('mutations.export.pdf', request()->only(['search', 'product_id', 'date_from', 'date_to', 'category_id', 'room_id', 'type', 'status'])) }}"
                   class="inline-flex items-center gap-1.5 px-3 sm:px-3.5 py-2 rounded-xl text-xs font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 border border-rose-200 dark:border-rose-800/40 active:scale-95 transition-all whitespace-nowrap shrink-0">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                    <span>PDF</span>
                </a>
                <a href="{{ route('mutations.create', !empty($productId) ? ['product_id' => $productId] : []) }}" class="inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-xl text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/20 active:scale-95 transition-all whitespace-nowrap shrink-0">
                    <i class="bi bi-plus-lg"></i>
                    <span>Tambah</span>
                </a>
            </div>
        </div>

        @if(!empty($filteredProduct))
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 px-3.5 py-2.5 rounded-xl bg-blue-50/80 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-800/60 text-xs">
                <div class="flex items-center gap-2 min-w-0">
                    <i class="bi bi-funnel-fill text-blue-600 dark:text-blue-400 shrink-0"></i>
                    <span class="text-slate-600 dark:text-slate-300 truncate">
                        Barang:
                        <a href="{{ route('products.show', $filteredProduct) }}" class="font-bold text-blue-700 dark:text-blue-300 hover:underline">{{ $filteredProduct->name }}</a>
                    </span>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <a href="{{ route('products.show', $filteredProduct) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-bold text-blue-700 dark:text-blue-300 bg-white dark:bg-slate-900 border border-blue-200 dark:border-blue-800 hover:bg-blue-50 transition-colors">
                        <i class="bi bi-box-seam"></i> Detail
                    </a>
                    <a href="{{ route('mutations.index') }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 transition-colors">
                        <i class="bi bi-x-lg text-[10px]"></i> Semua
                    </a>
                </div>
            </div>
        @endif

        {{-- Filter & Search Panel --}}
        <form method="GET" action="{{ route('mutations.index') }}" id="filterForm" class="space-y-3 pt-2.5 border-t border-slate-100 dark:border-slate-800">
            @if(!empty($productId))
                <input type="hidden" name="product_id" value="{{ $productId }}">
            @endif
            @php
                $isFiltered = ($dateFrom ?? '') || ($dateTo ?? '') || ($categoryId ?? '') || ($roomId ?? '') || ($type ?? '') || ($status ?? '');
            @endphp

            <div class="flex items-center gap-2">
                {{-- Search Bar --}}
                <div class="relative flex-1 min-w-0">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text" id="realtimeSearch" name="search" value="{{ $search ?? '' }}"
                           placeholder="Cari nama barang, kode, petugas, atau catatan..."
                           class="w-full pl-9 pr-4 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors" autocomplete="off">
                </div>

                {{-- Toggle Filter Button --}}
                <button type="button" id="filterToggleBtn"
                        aria-expanded="{{ $isFiltered ? 'true' : 'false' }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition-all cursor-pointer whitespace-nowrap shrink-0">
                    <i class="bi bi-sliders2"></i>
                    <span id="filterToggleText">{{ $isFiltered ? 'Tutup' : 'Filter' }}</span>
                    <i class="bi {{ $isFiltered ? 'bi-chevron-up' : 'bi-chevron-down' }} text-[10px] transition-transform duration-200" id="filterChevron"></i>
                </button>

                <a href="{{ route('mutations.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition-all shrink-0" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>

            {{-- Collapsible Advanced Filters --}}
            <div id="advancedFilter" data-collapsible-panel class="{{ $isFiltered ? 'block' : 'hidden' }} pt-3 border-t border-slate-100 dark:border-slate-800/80 transition-all duration-200">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2.5">
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="trigger-auto-submit w-full px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="trigger-auto-submit w-full px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Kategori</label>
                        <select name="category_id" class="trigger-auto-submit w-full px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(($categoryId ?? '') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Ruangan</label>
                        <select name="room_id" class="trigger-auto-submit w-full px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                            <option value="">Semua Ruangan</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected(($roomId ?? '') == $room->id)>{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Jenis</label>
                        <select name="type" class="trigger-auto-submit w-full px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                            <option value="">Semua Jenis</option>
                            <option value="masuk" @selected(($type ?? '') === 'masuk')>Masuk</option>
                            <option value="keluar" @selected(($type ?? '') === 'keluar')>Keluar</option>
                            <option value="pindah_ruang" @selected(($type ?? '') === 'pindah_ruang')>Pindah Ruang</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</label>
                        <select name="status" class="trigger-auto-submit w-full px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(($status ?? '') === 'pending')>Pending</option>
                            <option value="approved" @selected(($status ?? '') === 'approved')>Disetujui</option>
                            <option value="rejected" @selected(($status ?? '') === 'rejected')>Ditolak</option>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Main Mutations Data Table --}}
    <div id="mutationResults" class="transition-opacity duration-150 relative z-10">
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs overflow-hidden">

        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-4 py-3.5 whitespace-nowrap">Tanggal & Ref</th>
                        <th class="px-5 py-3.5 min-w-[260px] lg:min-w-[330px] w-[32%]">Nama Barang</th>
                        <th class="px-3.5 py-3.5 whitespace-nowrap">Jenis</th>
                        <th class="px-3 py-3.5 text-center whitespace-nowrap">Jml</th>
                        <th class="px-3.5 py-3.5">Dari</th>
                        <th class="px-3.5 py-3.5">Ke</th>
                        <th class="px-3.5 py-3.5 max-w-[120px] lg:max-w-[155px] w-[12%]">Catatan</th>
                        <th class="px-3.5 py-3.5 whitespace-nowrap">Status</th>
                        <th class="px-4 py-3.5 text-right whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @forelse($mutations as $mutation)
                        @php
                            $isHighlighted = $highlightId === (int) $mutation->id;
                            $rowRef = 'MUT-' . str_pad((string) $mutation->id, 4, '0', STR_PAD_LEFT);
                        @endphp
                        <tr class="group transition-colors {{ $isHighlighted ? 'bg-amber-50/70 dark:bg-amber-950/30 ring-1 ring-inset ring-amber-400/50' : 'hover:bg-blue-50/40 dark:hover:bg-slate-800/50' }}">
                            <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap tabular-nums">
                                <div class="font-bold text-slate-700 dark:text-slate-200">{{ optional($mutation->mutation_date)->format('d/m/Y') }}</div>
                                <a href="{{ route('mutations.show', $mutation) }}" class="text-[10px] font-mono font-semibold text-blue-600 dark:text-blue-400 hover:underline">{{ $rowRef }}</a>
                            </td>
                            <td class="px-5 py-3.5 min-w-[260px] lg:min-w-[330px]">
                                <div class="space-y-1">
                                    <a href="{{ route('mutations.show', $mutation) }}" class="block font-bold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors leading-snug">
                                        {{ $mutation->product->name ?? '-' }}
                                    </a>
                                    <div class="flex items-center flex-wrap gap-2 text-[11px] text-slate-500 dark:text-slate-400">
                                        @if($mutation->product && ($mutation->product->barcode || $mutation->product->kode_barang))
                                            <span class="font-mono px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                {{ $mutation->product->barcode ?: $mutation->product->kode_barang }}
                                            </span>
                                        @endif
                                        @if($mutation->product)
                                            <a href="{{ route('products.show', $mutation->product) }}" class="inline-flex items-center gap-1 text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                                <i class="bi bi-box-seam text-[10px]"></i>
                                                <span>Barang</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-3.5 py-3.5 whitespace-nowrap">
                                @switch($mutation->type)
                                    @case('masuk')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40">
                                            <i class="bi bi-box-arrow-in-down"></i> Masuk
                                        </span>
                                        @break
                                    @case('keluar')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200/60 dark:border-rose-800/40">
                                            <i class="bi bi-box-arrow-up"></i> Keluar
                                        </span>
                                        @break
                                    @case('pindah_ruang')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800/40">
                                            <i class="bi bi-arrow-left-right"></i> Pindah
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ ucfirst(str_replace('_', ' ', $mutation->type)) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-3 py-3.5 text-center font-bold text-slate-900 dark:text-white tabular-nums">
                                {{ $mutation->quantity }}
                            </td>
                            <td class="px-3.5 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                                {{ $mutation->fromRoom?->name ?? '-' }}
                            </td>
                            <td class="px-3.5 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                                {{ $mutation->toRoom?->name ?? '-' }}
                            </td>
                            <td class="px-3.5 py-3.5 text-xs text-slate-500 dark:text-slate-400 max-w-[120px] lg:max-w-[155px]">
                                <div class="truncate" title="{{ $mutation->note ?: 'Tanpa catatan' }}">
                                    {{ $mutation->note ?: '-' }}
                                </div>
                            </td>
                            <td class="px-3.5 py-3.5 whitespace-nowrap">
                                @switch($mutation->status)
                                    @case('pending')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                            <i class="bi bi-clock"></i> Pending
                                        </span>
                                        @break
                                    @case('approved')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                            <i class="bi bi-check-circle"></i> Disetujui
                                        </span>
                                        @break
                                    @case('rejected')
                                        <div>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
                                                <i class="bi bi-x-circle"></i> Ditolak
                                            </span>
                                            @if($mutation->rejection_note)
                                                <button type="button" data-modal-target="reasonModal{{ $mutation->id }}" class="block text-[11px] text-rose-500 hover:underline mt-0.5 font-medium cursor-pointer">
                                                    Alasan
                                                </button>
                                            @endif
                                        </div>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                            {{ ucfirst($mutation->status) }}
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center justify-end gap-1.5 flex-nowrap">
                                    @if($mutation->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                                        <form action="{{ route('mutations.approve', $mutation) }}" method="POST" class="inline-flex m-0 shrink-0" onsubmit="return confirm('Setujui mutasi ini?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-white bg-emerald-600 hover:bg-emerald-700 active:scale-90 transition-all shadow-2xs cursor-pointer shrink-0" title="Setujui" aria-label="Setujui">
                                                <i class="bi bi-check-lg text-sm"></i>
                                            </button>
                                        </form>

                                        <button type="button" data-modal-target="rejectModal{{ $mutation->id }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-rose-600 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-600 hover:text-white active:scale-90 transition-all cursor-pointer shrink-0" title="Tolak" aria-label="Tolak">
                                            <i class="bi bi-x-lg text-sm"></i>
                                        </button>
                                    @endif

                                    <a href="{{ route('mutations.show', $mutation) }}" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-950/40 dark:hover:text-blue-400 active:scale-90 transition-all shrink-0" title="Detail">
                                        <i class="bi bi-eye text-xs"></i>
                                    </a>

                                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                        <form action="{{ route('mutations.destroy', $mutation) }}" method="POST" class="inline-flex m-0 shrink-0" onsubmit="return confirm('Hapus data mutasi ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 active:scale-90 transition-all cursor-pointer shrink-0" title="Hapus">
                                                <i class="bi bi-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500">
                                <i class="bi bi-arrow-left-right text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Tidak ada data mutasi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Native Cards Feed --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($mutations as $mutation)
                @php
                    $isHighlighted = $highlightId === (int) $mutation->id;
                    $rowRef = 'MUT-' . str_pad((string) $mutation->id, 4, '0', STR_PAD_LEFT);
                @endphp
                <div class="p-3.5 space-y-2.5 transition-colors {{ $isHighlighted ? 'bg-amber-50/70 dark:bg-amber-950/30' : 'hover:bg-slate-50/60 dark:hover:bg-slate-800/30' }}">
                    <div class="flex items-start justify-between gap-2.5">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <span class="text-[10px] font-mono font-bold text-blue-600 dark:text-blue-400">{{ $rowRef }}</span>
                                <span class="text-[10px] text-slate-300 dark:text-slate-700">&bull;</span>
                                <span class="text-[10px] text-slate-400 tabular-nums">{{ optional($mutation->mutation_date)->format('d/m/Y') }}</span>
                            </div>
                            <a href="{{ route('mutations.show', $mutation) }}" class="font-extrabold text-xs sm:text-sm text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors block truncate">
                                {{ $mutation->product->name ?? '-' }}
                            </a>
                            <div class="flex items-center flex-wrap gap-1.5 mt-1">
                                @switch($mutation->type)
                                    @case('masuk')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">Masuk</span>
                                        @break
                                    @case('keluar')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">Keluar</span>
                                        @break
                                    @case('pindah_ruang')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400">Pindah</span>
                                        @break
                                @endswitch

                                @switch($mutation->status)
                                    @case('pending')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">Pending</span>
                                        @break
                                    @case('approved')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">Disetujui</span>
                                        @break
                                    @case('rejected')
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">Ditolak</span>
                                        @break
                                @endswitch
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-xs font-extrabold text-slate-900 dark:text-white shrink-0 tabular-nums">
                            {{ $mutation->quantity }} unit
                        </span>
                    </div>

                    <div class="flex items-center gap-2 text-[11px] text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-900/50 px-2.5 py-1.5 rounded-xl">
                        <span class="truncate">{{ $mutation->fromRoom?->name ?? '-' }}</span>
                        <i class="bi bi-arrow-right text-slate-400 text-[10px] shrink-0"></i>
                        <span class="truncate font-bold text-slate-800 dark:text-slate-200">{{ $mutation->toRoom?->name ?? '-' }}</span>
                    </div>

                    <div class="flex items-center justify-between gap-1.5 pt-1">
                        <div class="flex items-center gap-1.5 flex-1 min-w-0">
                            <a href="{{ route('mutations.show', $mutation) }}" class="flex-1 py-1.5 px-3 rounded-lg text-center text-[11px] font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 transition-colors">
                                Detail Transaksi
                            </a>
                            @if($mutation->product)
                                <a href="{{ route('products.show', $mutation->product) }}" class="px-3 py-1.5 rounded-lg text-center text-[11px] font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/40 shrink-0">
                                    Barang
                                </a>
                            @endif
                        </div>

                        {{-- Equal-Sized Icon Approval Buttons on Mobile (Setujui / Tolak) --}}
                        @if($mutation->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                            <div class="flex items-center gap-1.5 shrink-0">
                                <form action="{{ route('mutations.approve', $mutation) }}" method="POST" class="inline-flex m-0 shrink-0">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-white bg-emerald-600 hover:bg-emerald-700 active:scale-90 transition-all shadow-2xs cursor-pointer shrink-0"
                                            title="Setujui"
                                            aria-label="Setujui">
                                        <i class="bi bi-check-lg text-sm"></i>
                                    </button>
                                </form>
                                <button type="button"
                                        data-modal-target="rejectModal{{ $mutation->id }}"
                                        class="w-8 h-8 rounded-lg inline-flex items-center justify-center text-rose-600 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-600 hover:text-white active:scale-90 transition-all cursor-pointer shrink-0"
                                        title="Tolak"
                                        aria-label="Tolak">
                                    <i class="bi bi-x-lg text-sm"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-slate-400 dark:text-slate-500">
                    Tidak ada mutasi ditemukan.
                </div>
            @endforelse
        </div>

        {{-- Footer Pagination --}}
        <div class="p-3.5 sm:p-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs text-slate-500 dark:text-slate-400">
            <div class="tabular-nums">
                {{ $mutations->firstItem() ?? 0 }}–{{ $mutations->lastItem() ?? 0 }} dari {{ $mutations->total() }} mutasi
            </div>
            <div>
                {{ $mutations->links() }}
            </div>
        </div>
    </div>
    </div>
</div>

{{-- Rejection & Reason Modals --}}
<div id="mutationModals">
@foreach($mutations as $mutation)
    @if($mutation->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
        <div id="rejectModal{{ $mutation->id }}" class="modal-container responsive-bottom-sheet fixed inset-0 z-50 hidden items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="sheet-dialog relative w-full sm:max-w-md rounded-t-3xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-5 sm:p-6 animate-fade-in">
                <div class="sheet-grab-handle w-11 h-1.5 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto mb-3 sm:hidden"></div>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <h3 class="text-sm sm:text-base font-extrabold text-rose-600 dark:text-rose-400">Tolak Mutasi</h3>
                    <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                        <i class="bi bi-x-lg text-xs"></i>
                    </button>
                </div>

                <form action="{{ route('mutations.reject', $mutation) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <p class="text-xs text-slate-600 dark:text-slate-400">
                        Barang: <strong class="text-slate-900 dark:text-white">{{ $mutation->product->name ?? '-' }}</strong> ({{ $mutation->quantity }} unit)
                    </p>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Alasan Penolakan <span class="text-rose-500">*</span></label>
                        <textarea name="rejection_note" rows="3" required placeholder="Tuliskan alasan penolakan..." 
                                  class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500/40 focus:border-rose-500 transition-colors"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-xs">
                            Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($mutation->status === 'rejected' && $mutation->rejection_note)
        <div id="reasonModal{{ $mutation->id }}" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 animate-fade-in text-center">
                <button type="button" data-modal-close class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>

                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 flex items-center justify-center text-xl mx-auto mb-3">
                    <i class="bi bi-exclamation-circle"></i>
                </div>

                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Alasan Penolakan</h3>
                <p class="text-xs text-slate-500 mb-4">{{ $mutation->product->name ?? '-' }} ({{ $mutation->quantity }} unit)</p>

                <div class="p-4 rounded-xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 text-xs text-rose-700 dark:text-rose-300 text-left leading-relaxed mb-4">
                    {{ $mutation->rejection_note }}
                </div>

                <button type="button" data-modal-close class="w-full px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800">
                    Tutup
                </button>
            </div>
        </div>
    @endif
@endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const advancedFilter = document.getElementById('advancedFilter');
    const filterToggleText = document.getElementById('filterToggleText');
    const filterChevron = document.getElementById('filterChevron');

    filterToggleBtn?.addEventListener('click', function () {
        if (!advancedFilter) return;
        const willShow = advancedFilter.classList.contains('hidden');
        if (typeof window.animateCollapsiblePanel === 'function') {
            window.animateCollapsiblePanel(advancedFilter, willShow);
        } else {
            advancedFilter.classList.toggle('hidden', !willShow);
        }
        if (filterToggleText) filterToggleText.textContent = willShow ? 'Tutup' : 'Filter';
        if (filterChevron) {
            filterChevron.classList.toggle('bi-chevron-up', willShow);
            filterChevron.classList.toggle('bi-chevron-down', !willShow);
        }
    });

    const SEARCH_MIN_CHARS = 2;
    const SEARCH_DELAY_MS  = 500;
    const CLEAR_DELAY_MS   = 300;

    const filterForm     = document.getElementById('filterForm');
    const realtimeSearch = document.getElementById('realtimeSearch');
    const resultsBox     = document.getElementById('mutationResults');
    const modalsBox      = document.getElementById('mutationModals');
    const baseUrl        = "{{ route('mutations.index') }}";

    let debounceTimer = null;
    let controller    = null;
    let isComposing   = false;

    function buildUrl() {
        const params = new URLSearchParams();
        for (const [key, value] of new FormData(filterForm).entries()) {
            if (value !== null && String(value).trim() !== '') params.append(key, String(value).trim());
        }
        const qs = params.toString();
        return qs ? `${baseUrl}?${qs}` : baseUrl;
    }

    let lastUrl = buildUrl();

    function bindModals() {
        // Handled globally by app.js [data-modal-target] / [data-modal-close] spring modal engine
    }

    async function updateResults(url = buildUrl(), { force = false } = {}) {
        if (!resultsBox || !filterForm) return;
        if (!force && url === lastUrl) return;

        if (controller) controller.abort();
        const myController = controller = new AbortController();
        resultsBox.classList.add('opacity-60');
        resultsBox.setAttribute('aria-busy', 'true');

        try {
            const res = await fetch(url, {
                headers: { 'Accept': 'text/html' },
                credentials: 'same-origin',
                signal: myController.signal,
            });

            if (res.redirected && !res.url.startsWith(baseUrl)) {
                window.location.href = res.url;
                return;
            }
            if (!res.ok) throw new Error('HTTP ' + res.status);

            const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
            const newResults = doc.getElementById('mutationResults');
            if (!newResults) throw new Error('Format respons tidak dikenali');

            resultsBox.innerHTML = newResults.innerHTML;
            if (modalsBox) modalsBox.innerHTML = doc.getElementById('mutationModals')?.innerHTML ?? '';

            bindModals(resultsBox);
            if (modalsBox) bindModals(modalsBox);
            if (typeof window.runFramerMotionEntrance === 'function') {
                window.runFramerMotionEntrance(resultsBox);
            }

            lastUrl = url;
            history.replaceState(null, '', url);
        } catch (err) {
            if (err.name === 'AbortError') return;
            window.location.href = url;
        } finally {
            if (controller === myController) {
                resultsBox.classList.remove('opacity-60');
                resultsBox.removeAttribute('aria-busy');
            }
        }
    }

    function scheduleSearch() {
        clearTimeout(debounceTimer);
        if (isComposing) return;
        const len = realtimeSearch.value.trim().length;
        if (len > 0 && len < SEARCH_MIN_CHARS) return;
        debounceTimer = setTimeout(() => updateResults(), len === 0 ? CLEAR_DELAY_MS : SEARCH_DELAY_MS);
    }

    realtimeSearch?.addEventListener('input', scheduleSearch);
    realtimeSearch?.addEventListener('compositionstart', () => { isComposing = true; });
    realtimeSearch?.addEventListener('compositionend', () => { isComposing = false; scheduleSearch(); });

    filterForm?.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(debounceTimer);
        updateResults();
    });

    document.querySelectorAll('.trigger-auto-submit').forEach(el => {
        el.addEventListener('change', () => { clearTimeout(debounceTimer); updateResults(); });
    });

    resultsBox?.addEventListener('click', function (e) {
        const link = e.target.closest('a[href*="page="]');
        if (!link || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
        e.preventDefault();
        clearTimeout(debounceTimer);
        updateResults(link.href).then(() => resultsBox.scrollIntoView({ behavior: 'smooth', block: 'start' }));
    });
});
</script>
@endsection