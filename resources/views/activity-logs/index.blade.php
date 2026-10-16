@extends('layouts.app')

{{--
    SIM-Inventaris Diskominfo Kabupaten Garut — Rich Activity Audit Logs
    @author fwzann-abd <https://github.com/fwzann-abd>
    @co-collaborator msayib <https://github.com/msayib>
--}}

@section('content')
@php
    $currentSearch = $search ?? ($filters['search'] ?? '');
    $currentAction = $action ?? ($filters['action'] ?? '');
    $currentSubject = $subjectType ?? ($filters['subject_type'] ?? '');
    $currentUserId = $userId ?? ($filters['user_id'] ?? null);
    $currentDateFrom = $dateFrom ?? ($filters['date_from'] ?? '');
    $currentDateTo = $dateTo ?? ($filters['date_to'] ?? '');
    $currentSortBy = $sortBy ?? ($filters['sort_by'] ?? 'created_at');
    $currentSortDir = $sortDir ?? ($filters['sort_dir'] ?? 'desc');
    $currentPerPage = (int) ($perPage ?? ($filters['per_page'] ?? 20));
    $hasActiveFilter = $currentSearch !== ''
        || ($currentAction !== '' && $currentAction !== 'all')
        || ($currentSubject !== '' && $currentSubject !== 'all')
        || !empty($currentUserId)
        || $currentDateFrom !== ''
        || $currentDateTo !== ''
        || $currentSortBy !== 'created_at'
        || $currentSortDir !== 'desc'
        || $currentPerPage !== 20;
@endphp

<div class="space-y-5" data-framer-page id="activityLogsPageRoot">
    {{-- Summary KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5" data-framer-stagger>
        <div class="rounded-2xl bg-white dark:bg-[#1c1917] border border-slate-200/80 dark:border-slate-800/80 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Audit Log</span>
                <span class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i class="bi bi-journal-check text-sm"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($summary['total'] ?? $activityLogs->total()) }}</div>
            <p class="mt-0.5 text-[11px] text-slate-400">Seluruh jejak aktivitas terekam</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-[#1c1917] border border-slate-200/80 dark:border-slate-800/80 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Aktivitas Hari Ini</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i class="bi bi-lightning-charge-fill text-sm"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($summary['today'] ?? 0) }}</div>
            <p class="mt-0.5 text-[11px] text-slate-400">Transaksi & perubahan hari ini</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-[#1c1917] border border-slate-200/80 dark:border-slate-800/80 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Log Barang & Stok</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i class="bi bi-box-seam-fill text-sm"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($summary['products'] ?? 0) }}</div>
            <p class="mt-0.5 text-[11px] text-slate-400">Katalog, barcode & ekspor</p>
        </div>

        <div class="rounded-2xl bg-white dark:bg-[#1c1917] border border-slate-200/80 dark:border-slate-800/80 p-4 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Log Mutasi Aset</span>
                <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <i class="bi bi-arrow-left-right text-sm"></i>
                </span>
            </div>
            <div class="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($summary['mutations'] ?? 0) }}</div>
            <p class="mt-0.5 text-[11px] text-slate-400">Pengajuan & persetujuan mutasi</p>
        </div>
    </div>

    {{-- Rich Filter & Sorting Panel --}}
    <div class="rounded-2xl bg-white dark:bg-[#1c1917] border border-slate-200/80 dark:border-slate-800/80 p-4 sm:p-5 shadow-sm space-y-4" data-framer-card>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">Log Audit Aktivitas Sistem</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Rekam jejak komprehensif autentikasi, perubahan barang, mutasi ruangan, pemindaian barcode, dan ekspor laporan.</p>
            </div>
            @if($hasActiveFilter)
                <a href="{{ route('activity-logs.index') }}"
                   id="resetActivityLogFiltersBtn"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 border border-rose-200/60 dark:border-rose-500/20 hover:bg-rose-100 transition-colors self-start sm:self-auto cursor-pointer">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Reset Semua Filter</span>
                </a>
            @endif
        </div>

        <form action="{{ route('activity-logs.index') }}" method="GET" id="activityLogFilterForm" class="space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2.5 items-center">
                {{-- Search --}}
                <div class="relative sm:col-span-2 lg:col-span-4">
                    <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Cari deskripsi, aksi, modul, atau nama pengguna..."
                           class="w-full h-9 pl-9 pr-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-stone-900 border border-slate-200 dark:border-stone-700 text-slate-900 dark:text-stone-100 focus:outline-none focus:ring-2 focus:ring-blue-500/40">
                </div>

                {{-- Action Filter --}}
                <div class="lg:col-span-2">
                    <select name="action" onchange="this.form.submit()" class="w-full h-9 px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-stone-900 border border-slate-200 dark:border-stone-700 text-slate-700 dark:text-stone-200 cursor-pointer">
                        <option value="">Semua Jenis Aksi</option>
                        <option value="created" @selected($currentAction === 'created')>Dibuat (Created)</option>
                        <option value="updated" @selected($currentAction === 'updated')>Diperbarui (Updated)</option>
                        <option value="deleted" @selected($currentAction === 'deleted')>Dihapus (Deleted)</option>
                        <option value="login" @selected($currentAction === 'login')>Masuk (Login)</option>
                        <option value="logout" @selected($currentAction === 'logout')>Keluar (Logout)</option>
                        <option value="scan" @selected($currentAction === 'scan')>Scan Barcode</option>
                        <option value="print" @selected($currentAction === 'print')>Cetak Barcode</option>
                        <option value="export" @selected($currentAction === 'export')>Ekspor Laporan</option>
                        <option value="sync" @selected($currentAction === 'sync')>Sinkronisasi</option>
                        <option value="read" @selected($currentAction === 'read')>Baca Notifikasi</option>
                    </select>
                </div>

                {{-- Subject / Module Filter --}}
                <div class="lg:col-span-2">
                    <select name="subject_type" onchange="this.form.submit()" class="w-full h-9 px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-stone-900 border border-slate-200 dark:border-stone-700 text-slate-700 dark:text-stone-200 cursor-pointer">
                        <option value="">Semua Modul</option>
                        <option value="Product" @selected($currentSubject === 'Product')>Barang (Product)</option>
                        <option value="Mutation" @selected($currentSubject === 'Mutation')>Mutasi (Mutation)</option>
                        <option value="Room" @selected($currentSubject === 'Room')>Ruangan (Room)</option>
                        <option value="Category" @selected($currentSubject === 'Category')>Kategori (Category)</option>
                        <option value="User" @selected($currentSubject === 'User')>Pengguna & Auth</option>
                        <option value="Notification" @selected($currentSubject === 'Notification')>Notifikasi</option>
                    </select>
                </div>

                {{-- Actor / User Filter --}}
                <div class="lg:col-span-2">
                    <select name="user_id" onchange="this.form.submit()" class="w-full h-9 px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-stone-900 border border-slate-200 dark:border-stone-700 text-slate-700 dark:text-stone-200 cursor-pointer">
                        <option value="">Semua Pengguna</option>
                        @foreach($users ?? [] as $u)
                            <option value="{{ $u->id }}" @selected((int) $currentUserId === (int) $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sort & Per Page --}}
                <div class="lg:col-span-2 grid grid-cols-2 gap-1.5 items-center">
                    <select name="sort_dir" onchange="this.form.submit()" class="w-full h-9 px-2.5 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-stone-900 border border-slate-200 dark:border-stone-700 text-slate-700 dark:text-stone-200 cursor-pointer" title="Urutan Waktu">
                        <option value="desc" @selected($currentSortDir === 'desc')>Terbaru</option>
                        <option value="asc" @selected($currentSortDir === 'asc')>Terlama</option>
                    </select>
                    <select name="per_page" onchange="this.form.submit()" class="w-full h-9 px-2 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-stone-900 border border-slate-200 dark:border-stone-700 text-slate-700 dark:text-stone-200 cursor-pointer" title="Baris per Halaman">
                        @foreach([10, 20, 30, 50, 100] as $pp)
                            <option value="{{ $pp }}" @selected($currentPerPage === $pp)>{{ $pp }}/hal</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Second Row: Date Range & Sort By + Submit --}}
            <div class="flex flex-wrap items-center justify-between gap-2.5 pt-2.5 border-t border-slate-100 dark:border-stone-800/80">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 dark:text-stone-400 mr-1">
                        <i class="bi bi-calendar3"></i>
                        <span>Rentang Tanggal:</span>
                    </div>
                    <input type="date" name="date_from" value="{{ $currentDateFrom }}" onchange="this.form.submit()"
                           class="h-9 px-2.5 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-stone-900 border border-slate-200 dark:border-stone-700 text-slate-700 dark:text-stone-200">
                    <span class="text-xs text-slate-400">s/d</span>
                    <input type="date" name="date_to" value="{{ $currentDateTo }}" onchange="this.form.submit()"
                           class="h-9 px-2.5 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-stone-900 border border-slate-200 dark:border-stone-700 text-slate-700 dark:text-stone-200">

                    <select name="sort_by" onchange="this.form.submit()" class="h-9 px-2.5 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-stone-900 border border-slate-200 dark:border-stone-700 text-slate-700 dark:text-stone-200 cursor-pointer">
                        <option value="created_at" @selected($currentSortBy === 'created_at')>Urut: Waktu</option>
                        <option value="action" @selected($currentSortBy === 'action')>Urut: Jenis Aksi</option>
                        <option value="subject_type" @selected($currentSortBy === 'subject_type')>Urut: Modul</option>
                        <option value="id" @selected($currentSortBy === 'id')>Urut: ID Log</option>
                    </select>
                </div>

                <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-blue-600 hover:bg-blue-700 text-white shadow-2xs transition-colors cursor-pointer">
                    <i class="bi bi-funnel-fill text-[11px]"></i>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Activity Logs Table & Mobile Cards --}}
    <div class="rounded-2xl bg-white dark:bg-[#1c1917] border border-slate-200/80 dark:border-slate-800/80 shadow-sm overflow-hidden" data-framer-card>
        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        <th class="px-5 py-3.5">Pengguna</th>
                        <th class="px-4 py-3.5">Aksi</th>
                        <th class="px-4 py-3.5">Modul</th>
                        <th class="px-5 py-3.5">Deskripsi Aktivitas</th>
                        <th class="px-5 py-3.5 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                    @forelse($activityLogs as $log)
                        @php
                            $subjectShort = $log->subject_type ? class_basename($log->subject_type) : 'Sistem';
                            $subjectLabel = match ($subjectShort) {
                                'Product' => 'Barang',
                                'Mutation' => 'Mutasi',
                                'Room' => 'Ruangan',
                                'Category' => 'Kategori',
                                'User' => 'Pengguna',
                                'Notification' => 'Notifikasi',
                                default => $subjectShort,
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    @if(!empty($log->user?->avatar_url))
                                        <img src="{{ $log->user->avatar_url }}" alt="{{ $log->user->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 dark:border-slate-700 shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0">
                                            {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <span class="font-semibold text-slate-900 dark:text-white block truncate">{{ $log->user?->name ?? 'Sistem Otomatis' }}</span>
                                        @if($log->user?->email)
                                            <span class="text-[10px] text-slate-400 block truncate">{{ $log->user->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($log->action === 'created')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400">Dibuat</span>
                                @elseif($log->action === 'updated')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400">Diperbarui</span>
                                @elseif($log->action === 'deleted')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400">Dihapus</span>
                                @elseif($log->action === 'login')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400">Login</span>
                                @elseif($log->action === 'logout')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400">Logout</span>
                                @elseif(in_array($log->action, ['export', 'print', 'scan'], true))
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400">{{ strtoupper($log->action) }}</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ ucfirst($log->action) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                    {{ $subjectLabel }}
                                    @if($log->subject_id)
                                        <span class="text-slate-400 font-mono">#{{ $log->subject_id }}</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-700 dark:text-slate-300 font-medium max-w-lg">
                                {{ $log->description }}
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 text-right whitespace-nowrap tabular-nums">
                                <span class="block font-semibold text-slate-700 dark:text-slate-300">{{ $log->created_at ? $log->created_at->translatedFormat('d M Y, H:i:s') : '-' }}</span>
                                <span class="text-[10px] text-slate-400">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                <i class="bi bi-clock-history text-3xl mb-2 block text-slate-300 dark:text-slate-600"></i>
                                Tidak ada log aktivitas tercatat yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards Feed --}}
        <div class="md:hidden divide-y divide-slate-100 dark:divide-slate-800" data-framer-stagger>
            @forelse($activityLogs as $log)
                @php
                    $subjectShort = $log->subject_type ? class_basename($log->subject_type) : 'Sistem';
                    $subjectLabel = match ($subjectShort) {
                        'Product' => 'Barang',
                        'Mutation' => 'Mutasi',
                        'Room' => 'Ruangan',
                        'Category' => 'Kategori',
                        'User' => 'Pengguna',
                        'Notification' => 'Notifikasi',
                        default => $subjectShort,
                    };
                @endphp
                <div class="p-3.5 space-y-2">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            @if(!empty($log->user?->avatar_url))
                                <img src="{{ $log->user->avatar_url }}" alt="{{ $log->user->name }}" class="w-6 h-6 rounded-full object-cover shrink-0">
                            @else
                                <div class="w-6 h-6 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-[10px] font-bold flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                </div>
                            @endif
                            <span class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $log->user?->name ?? 'Sistem Otomatis' }}</span>
                        </div>
                        <span class="text-[10px] text-slate-400 shrink-0 tabular-nums">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : '-' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-blue-500/10 text-blue-600 dark:text-blue-400">{{ $log->action }}</span>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $subjectLabel }}</span>
                    </div>
                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">{{ $log->description }}</p>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-slate-400">Tidak ada log aktivitas tercatat.</div>
            @endforelse
        </div>

        {{-- Pagination Footer (Always rendered with summary + page navigation) --}}
        <div class="px-4 sm:px-6 py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3" id="activityLogsPaginationFooter">
            <div class="text-xs text-slate-500 dark:text-slate-400">
                Menampilkan <strong class="text-slate-800 dark:text-slate-200 tabular-nums">{{ $activityLogs->firstItem() ?? 0 }}–{{ $activityLogs->lastItem() ?? 0 }}</strong> dari <strong class="text-slate-800 dark:text-slate-200 tabular-nums">{{ $activityLogs->total() }}</strong> log aktivitas
                <span class="text-slate-400">(Halaman {{ $activityLogs->currentPage() }} / {{ max(1, $activityLogs->lastPage()) }})</span>
            </div>
            <div>
                @if(method_exists($activityLogs, 'hasPages') && $activityLogs->hasPages())
                    {{ $activityLogs->links() }}
                @else
                    <div class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-400">
                        <span class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-bold">1</span>
                        <span>Semua data ditampilkan</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection