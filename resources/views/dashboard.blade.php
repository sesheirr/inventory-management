@extends('layouts.app')

@section('content')
<div class="space-y-6">
    {{-- Welcome Hero Card with Diskominfo Emblem --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-800 p-6 sm:p-8 text-white shadow-xl shadow-blue-600/15">
        {{-- Background Decorative Blur Circles --}}
        <div class="absolute -right-12 -top-12 w-64 h-64 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4 sm:gap-5">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white p-2 shadow-lg shadow-blue-900/30 border border-white/40 flex items-center justify-center shrink-0 transform hover:scale-105 transition-transform duration-200">
                    <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut" class="w-full h-full object-contain">
                </div>
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/20 text-blue-100 border border-white/30 backdrop-blur-sm uppercase tracking-wider">
                            @if(auth()->user()->isSuperAdmin()) Super Administrator
                            @elseif(auth()->user()->isAdmin()) Administrator
                            @else Operator Inventaris @endif
                        </span>
                        <span class="text-xs text-blue-200 flex items-center gap-1">
                            <i class="bi bi-clock"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white leading-tight">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-xs sm:text-sm text-blue-100/90 max-w-xl">
                        Sistem Informasi Inventarisasi Barang & Aset Diskominfo Kabupaten Garut siap digunakan.
                    </p>
                </div>
            </div>

            {{-- Quick Action Buttons --}}
            <div class="flex items-center flex-wrap gap-2.5 shrink-0">
                <a href="{{ route('products.scan') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-blue-700 bg-white hover:bg-blue-50 shadow-md shadow-blue-900/20 active:scale-95 transition-all">
                    <i class="bi bi-qr-code-scan text-sm"></i>
                    <span>Scan Barcode</span>
                </a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-500/40 hover:bg-blue-500/60 border border-white/30 backdrop-blur-sm active:scale-95 transition-all">
                        <i class="bi bi-plus-lg text-sm"></i>
                        <span>Tambah Barang</span>
                    </a>
                @endif
                <a href="{{ route('mutations.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-white/15 hover:bg-white/25 border border-white/20 backdrop-blur-sm active:scale-95 transition-all">
                    <i class="bi bi-arrow-left-right text-sm"></i>
                    <span>Mutasi</span>
                </a>
            </div>
        </div>

        {{-- Quick Find Inventory Bar inside Hero --}}
        <form action="{{ route('products.index') }}" method="GET" class="relative z-10 mt-6 pt-5 border-t border-white/15">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                <div class="relative flex-1">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-blue-200 text-sm pointer-events-none"></i>
                    <input type="search" name="search" placeholder="Cari barang inventaris berdasarkan nama, kode barang, barcode, ruangan, atau kategori..."
                           class="w-full pl-11 pr-4 py-3 rounded-2xl text-xs sm:text-sm bg-white/15 hover:bg-white/20 focus:bg-white text-white focus:text-slate-900 placeholder-blue-100/80 focus:placeholder-slate-400 border border-white/25 focus:border-white focus:outline-none focus:ring-4 focus:ring-white/20 backdrop-blur-md transition-all"
                           autocomplete="off">
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl text-xs sm:text-sm font-bold text-blue-700 bg-white hover:bg-blue-50 shadow-md shadow-blue-950/20 active:scale-95 transition-all cursor-pointer shrink-0">
                        <i class="bi bi-search"></i>
                        <span>Cari Barang</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Metric Stat Cards (4 Grid) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        {{-- Total Barang --}}
        <div class="group relative rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-blue-500/40 transition-all duration-200 overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Barang</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1.5 tracking-tight">
                        {{ number_format($totalProducts) }}
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform duration-200 shrink-0">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('products.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    Lihat katalog barang <i class="bi bi-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        {{-- Total Kategori --}}
        <div class="group relative rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-emerald-500/40 transition-all duration-200 overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1.5 tracking-tight">
                        {{ number_format($totalCategories) }}
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform duration-200 shrink-0">
                    <i class="bi bi-tags"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('categories.index') }}" class="text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1">
                    Kelola kategori <i class="bi bi-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        {{-- Total Ruangan --}}
        <div class="group relative rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-amber-500/40 transition-all duration-200 overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ruangan</span>
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mt-1.5 tracking-tight">
                        {{ number_format($totalRooms) }}
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform duration-200 shrink-0">
                    <i class="bi bi-building"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('rooms.index') }}" class="text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                    Lihat daftar ruangan <i class="bi bi-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        {{-- Total Mutasi / Status Menunggu --}}
        <div class="group relative rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-sm hover:shadow-md hover:border-indigo-500/40 transition-all duration-200 overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Mutasi</span>
                    <div class="flex items-baseline gap-2 mt-1.5">
                        <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ number_format($totalMutations) }}
                        </span>
                        @if(($pendingMutationsCount ?? 0) > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400">
                                {{ $pendingMutationsCount }} pending
                            </span>
                        @endif
                    </div>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl shadow-inner group-hover:scale-110 transition-transform duration-200 shrink-0">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                <a href="{{ route('mutations.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                    Riwayat perpindahan <i class="bi bi-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Recent Products & Low Stock Attention Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Barang Terbaru Ditambahkan --}}
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-box-seam text-blue-600 dark:text-blue-400"></i>
                            <span>Barang Terbaru Ditambahkan</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Aset yang baru didaftarkan ke dalam inventaris</p>
                    </div>
                    <a href="{{ route('products.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline shrink-0">
                        Lihat Semua
                    </a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($recentProducts ?? [] as $item)
                        <a href="{{ route('products.show', $item) }}" class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($item->image_url)
                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <div class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">{{ $item->name }}</div>
                                    <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        <span><i class="bi bi-tag me-1"></i>{{ $item->category?->name ?? $item->getRawOriginal('category') ?? 'Tanpa Kategori' }}</span>
                                        <span>•</span>
                                        <span><i class="bi bi-geo-alt me-1"></i>{{ $item->room?->name ?? 'Belum ditentukan' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $item->stock > 5 ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' : ($item->stock > 0 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400') }}">
                                    {{ $item->stock }} unit
                                </span>
                                @if($item->barcode || $item->kode_barang)
                                    <div class="text-[10px] font-mono text-slate-400 mt-0.5">{{ $item->barcode ?: $item->kode_barang }}</div>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-400">
                            Belum ada data barang yang terdaftar.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Perlu Perhatian: Stok Menipis / Habis --}}
        <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-exclamation-triangle text-amber-500"></i>
                            <span>Perhatian Stok Barang</span>
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Barang dengan stok menipis (&le; 5 unit) atau stok habis</p>
                    </div>
                    <a href="{{ route('products.index', ['status' => 'low_stock']) }}" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline shrink-0">
                        Filter Stok Menipis
                    </a>
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($lowStockProducts ?? [] as $lowItem)
                        <a href="{{ route('products.show', $lowItem) }}" class="flex items-center justify-between gap-3 px-5 py-3.5 hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <div class="min-w-0">
                                <div class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white truncate">{{ $lowItem->name }}</div>
                                <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    <span><i class="bi bi-geo-alt me-1"></i>{{ $lowItem->room?->name ?? 'Belum ditentukan' }}</span>
                                    @if($lowItem->kode_barang)
                                        <span>•</span>
                                        <span class="font-mono">{{ $lowItem->kode_barang }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="shrink-0">
                                @if($lowItem->stock <= 0 || $lowItem->status === 'out_of_stock')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">
                                        <i class="bi bi-x-circle"></i> Habis (0 unit)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">
                                        <i class="bi bi-exclamation-circle"></i> Sisa {{ $lowItem->stock }} unit
                                    </span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center text-xs text-emerald-600 dark:text-emerald-400">
                            <i class="bi bi-check-circle-fill text-2xl mb-1.5 block"></i>
                            Seluruh stok barang inventaris dalam kondisi mencukupi.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Activity Logs & Recent Mutations Table Section --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">Aktivitas Sistem & Mutasi Terbaru</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Audit trail pencatatan barang dan perubahan inventaris</p>
            </div>

            <div class="flex items-center gap-2">
                @if(auth()->user()->isSuperAdmin())
                    <form action="{{ route('dashboard.clear-history') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh riwayat log aktivitas? Tindakan ini tidak dapat dibatalkan.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 hover:bg-rose-100 dark:hover:bg-rose-900/50 transition-colors cursor-pointer">
                            <i class="bi bi-trash"></i>
                            <span>Bersihkan Riwayat</span>
                        </button>
                    </form>
                @endif
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('activity-logs.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        <span>Lihat Semua Log</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                @endif
            </div>
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-6 py-3.5">Pengguna</th>
                        <th class="px-6 py-3.5">Aksi</th>
                        <th class="px-6 py-3.5">Deskripsi</th>
                        <th class="px-6 py-3.5 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @forelse($activityLogs as $log)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if(!empty($log->user?->avatar_url))
                                        <img src="{{ $log->user->avatar_url }}" alt="{{ $log->user->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0">
                                            {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ $log->user?->name ?? 'Sistem' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                @if($log->action === 'created')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">
                                        <i class="bi bi-plus-circle"></i> Dibuat
                                    </span>
                                @elseif($log->action === 'updated')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400">
                                        <i class="bi bi-pencil-square"></i> Diperbarui
                                    </span>
                                @elseif($log->action === 'deleted')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">
                                        <i class="bi bi-trash"></i> Dihapus
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-slate-700 dark:text-slate-300 font-medium max-w-md">
                                {{ $log->description }}
                            </td>
                            <td class="px-6 py-3.5 text-xs text-slate-500 dark:text-slate-400 text-right whitespace-nowrap">
                                {{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="bi bi-inbox text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Belum ada aktivitas yang tercatat dalam sistem.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards Feed --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($activityLogs as $log)
                <div class="p-4 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if(!empty($log->user?->avatar_url))
                                <img src="{{ $log->user->avatar_url }}" alt="{{ $log->user->name }}" class="w-6 h-6 rounded-full object-cover">
                            @else
                                <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 text-xs font-bold flex items-center justify-center">
                                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $log->user?->name ?? 'Sistem' }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ $log->created_at ? $log->created_at->diffForHumans() : '-' }}</span>
                    </div>
                    <p class="text-xs text-slate-700 dark:text-slate-300">{{ $log->description }}</p>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-slate-400">Tidak ada log aktivitas.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection