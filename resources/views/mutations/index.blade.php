@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Header & Top Controls --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Mutasi & Perpindahan Barang</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Pantau riwayat barang masuk, keluar, dan distribusi antar ruangan</p>
            </div>

            <a href="{{ route('mutations.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-500/25 active:scale-[0.98] transition-all shrink-0">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Mutasi</span>
            </a>
        </div>

        {{-- Filter & Search Panel --}}
        <form method="GET" action="{{ route('mutations.index') }}" id="filterForm" class="space-y-3 pt-3 border-t border-slate-100 dark:border-slate-800">
            @php
                $isFiltered = ($dateFrom ?? '') || ($dateTo ?? '') || ($categoryId ?? '') || ($roomId ?? '') || ($type ?? '') || ($status ?? '');
            @endphp

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                {{-- Search Bar --}}
                <div class="relative flex-1">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="realtimeSearch" name="search" value="{{ $search ?? '' }}"
                           placeholder="Cari nama barang, kode, atau catatan..." 
                           class="w-full pl-9 pr-4 py-2.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500 transition-colors" autocomplete="off">
                </div>

                {{-- Toggle Filter Button --}}
                <div class="flex items-center gap-2">
                    <button type="button" id="filterToggleBtn"
                            class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer">
                        <i class="bi bi-funnel"></i>
                        <span id="filterToggleText">{{ $isFiltered ? 'Sembunyikan Filter' : 'Filter Lanjutan' }}</span>
                        <i class="bi {{ $isFiltered ? 'bi-chevron-up' : 'bi-chevron-down' }} text-[10px] ml-1" id="filterChevron"></i>
                    </button>

                    <a href="{{ route('mutations.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors shrink-0" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>

            {{-- Collapsible Advanced Filters --}}
            <div id="advancedFilter" class="{{ $isFiltered ? 'block' : 'hidden' }} pt-3 border-t border-slate-100 dark:border-slate-800/80">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
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
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Jenis Mutasi</label>
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
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden">
        
        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Barang</th>
                        <th class="px-4 py-3.5">Jenis Mutasi</th>
                        <th class="px-4 py-3.5 text-center">Jumlah</th>
                        <th class="px-4 py-3.5">Dari Ruangan</th>
                        <th class="px-4 py-3.5">Ke Ruangan</th>
                        <th class="px-4 py-3.5">Catatan</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @forelse($mutations as $mutation)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                {{ optional($mutation->mutation_date)->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('mutations.show', $mutation) }}" class="font-semibold text-slate-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    {{ $mutation->product->name ?? '-' }}
                                </a>
                            </td>
                            <td class="px-4 py-3.5">
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
                            <td class="px-4 py-3.5 text-center font-bold text-slate-900 dark:text-white">
                                {{ $mutation->quantity }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                                {{ $mutation->fromRoom?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600 dark:text-slate-400">
                                {{ $mutation->toRoom?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500 dark:text-slate-400 max-w-xs truncate" title="{{ $mutation->note }}">
                                {{ $mutation->note ?? '-' }}
                            </td>
                            <td class="px-4 py-3.5">
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
                                                <button type="button" data-modal-target="reasonModal{{ $mutation->id }}" class="block text-[11px] text-rose-500 hover:underline mt-0.5 font-medium">
                                                    Lihat Alasan
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
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Approval Quick Buttons for Pending Mutations --}}
                                    @if($mutation->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                                        <form action="{{ route('mutations.approve', $mutation) }}" method="POST" class="inline-block" onsubmit="return confirm('Setujui mutasi ini? Stok barang akan disesuaikan otomatis.');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="w-7 h-7 rounded-lg flex items-center justify-center text-white bg-emerald-600 hover:bg-emerald-700 transition-colors shadow-xs" title="Setujui Mutasi">
                                                <i class="bi bi-check-lg text-sm"></i>
                                            </button>
                                        </form>

                                        <button type="button" data-modal-target="rejectModal{{ $mutation->id }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-rose-600 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors" title="Tolak Mutasi">
                                            <i class="bi bi-x-lg text-sm"></i>
                                        </button>
                                    @endif

                                    <div class="relative inline-block text-left">
                                        <button type="button" data-dropdown-toggle="mut-dropdown-{{ $mutation->id }}" class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                            <i class="bi bi-three-dots-vertical text-xs"></i>
                                        </button>
                                        <div id="mut-dropdown-{{ $mutation->id }}" data-dropdown-menu class="hidden absolute right-0 mt-1 w-36 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl py-1 z-20 text-left">
                                            <a href="{{ route('mutations.show', $mutation) }}" class="flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                                                <i class="bi bi-eye text-slate-400"></i> Detail
                                            </a>
                                            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                                <form action="{{ route('mutations.destroy', $mutation) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data mutasi ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="w-full flex items-center gap-2 px-3.5 py-1.5 text-xs font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30">
                                                        <i class="bi bi-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="bi bi-arrow-left-right text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Tidak ada data mutasi yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards Feed --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($mutations as $mutation)
                <div class="p-4 space-y-2.5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-bold text-sm text-slate-900 dark:text-white">{{ $mutation->product->name ?? '-' }}</h3>
                            <span class="text-[11px] text-slate-400">{{ optional($mutation->mutation_date)->format('d/m/Y') }}</span>
                        </div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white">
                            {{ $mutation->quantity }} unit
                        </span>
                    </div>

                    <div class="flex items-center flex-wrap gap-1.5">
                        @switch($mutation->type)
                            @case('masuk')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">Masuk</span>
                                @break
                            @case('keluar')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">Keluar</span>
                                @break
                            @case('pindah_ruang')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400">Pindah</span>
                                @break
                        @endswitch

                        @switch($mutation->status)
                            @case('pending')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">Pending</span>
                                @break
                            @case('approved')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">Disetujui</span>
                                @break
                            @case('rejected')
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">Ditolak</span>
                                @break
                        @endswitch
                    </div>

                    <div class="text-xs text-slate-600 dark:text-slate-400 space-y-0.5 pt-1">
                        <div>Dari: <strong>{{ $mutation->fromRoom?->name ?? '-' }}</strong></div>
                        <div>Ke: <strong>{{ $mutation->toRoom?->name ?? '-' }}</strong></div>
                    </div>

                    @if($mutation->note)
                        <p class="text-xs text-slate-500 dark:text-slate-400 italic">"{{ $mutation->note }}"</p>
                    @endif

                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <a href="{{ route('mutations.show', $mutation) }}" class="flex-1 py-1.5 rounded-lg text-center text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800">
                            Detail
                        </a>
                        @if($mutation->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
                            <form action="{{ route('mutations.approve', $mutation) }}" method="POST" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full py-1.5 rounded-lg text-xs font-semibold text-white bg-emerald-600">
                                    Setujui
                                </button>
                            </form>
                            <button type="button" data-modal-target="rejectModal{{ $mutation->id }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-rose-600 bg-rose-50">
                                Tolak
                            </button>
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
        <div class="p-4 sm:p-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 dark:text-slate-400">
            <div>
                Menampilkan {{ $mutations->firstItem() ?? 0 }} - {{ $mutations->lastItem() ?? 0 }} dari {{ $mutations->total() }} data mutasi
            </div>
            <div>
                {{ $mutations->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Rejection & Reason Modals --}}
@foreach($mutations as $mutation)
    @if($mutation->status === 'pending' && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
        <div id="rejectModal{{ $mutation->id }}" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 animate-fade-in">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <h3 class="text-base font-bold text-rose-600 dark:text-rose-400">Tolak Permohonan Mutasi</h3>
                    <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                        <i class="bi bi-x-lg text-sm"></i>
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
                        <textarea name="rejection_note" rows="3" required placeholder="Tuliskan alasan penolakan mutasi barang ini..." 
                                  class="w-full px-4 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500/40 focus:border-rose-500 transition-colors"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                        <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 shadow-sm">
                            Tolak Mutasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($mutation->status === 'rejected' && $mutation->rejection_note)
        <div id="reasonModal{{ $mutation->id }}" class="modal-container fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 animate-fade-in text-center">
                <button type="button" data-modal-close class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>

                <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 flex items-center justify-center text-xl mx-auto mb-3">
                    <i class="bi bi-exclamation-circle"></i>
                </div>

                <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">Catatan Penolakan Mutasi</h3>
                <p class="text-xs text-slate-500 mb-4">{{ $mutation->product->name ?? '-' }} ({{ $mutation->quantity }} unit)</p>

                <div class="p-4 rounded-xl bg-rose-50/50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/40 text-xs text-rose-700 dark:text-rose-300 text-left leading-relaxed mb-4">
                    {{ $mutation->rejection_note }}
                </div>

                <button type="button" data-modal-close class="w-full px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800">
                    Tutup
                </button>
            </div>
        </div>
    @endif
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const advancedFilter = document.getElementById('advancedFilter');
    const filterToggleText = document.getElementById('filterToggleText');
    const filterChevron = document.getElementById('filterChevron');

    filterToggleBtn?.addEventListener('click', function () {
        advancedFilter.classList.toggle('hidden');
        const isShown = !advancedFilter.classList.contains('hidden');
        if (filterToggleText) filterToggleText.textContent = isShown ? 'Sembunyikan Filter' : 'Filter Lanjutan';
        if (filterChevron) {
            filterChevron.classList.toggle('bi-chevron-up', isShown);
            filterChevron.classList.toggle('bi-chevron-down', !isShown);
        }
    });

    const filterForm = document.getElementById('filterForm');
    const realtimeSearch = document.getElementById('realtimeSearch');
    const baseUrl = "{{ route('mutations.index') }}";

    function updateResults() {
        if (!filterForm) return;
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        for (const [key, value] of formData.entries()) {
            if (value !== null && value !== '') {
                params.append(key, value);
            }
        }
        const queryString = params.toString();
        window.location.href = queryString ? `${baseUrl}?${queryString}` : baseUrl;
    }

    let debounceTimer = null;
    realtimeSearch?.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(updateResults, 350);
    });

    document.querySelectorAll('.trigger-auto-submit').forEach(el => {
        el.addEventListener('change', updateResults);
    });
});
</script>
@endsection