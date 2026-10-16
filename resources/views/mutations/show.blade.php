@extends('layouts.app')

@section('page-title', 'Detail Mutasi')
@section('page-subtitle', 'Rincian transaksi mutasi #' . $mutation->id)
@section('title', 'Detail Mutasi #' . $mutation->id . ' — Inventaris Diskominfo Garut')
@section('share_code', 'm-' . $mutation->id)
@section('og_title', 'Mutasi ' . ($mutation->product->name ?? 'Barang') . ' — Inventaris Diskominfo Garut')
@section('og_description', 'Jenis: ' . strtoupper(str_replace('_', ' ', $mutation->type)) . ' (' . $mutation->quantity . ' unit) • Status: ' . strtoupper($mutation->status))

@section('content')
@php
    $trackingCode = 'MUT-' . ($mutation->created_at ? $mutation->created_at->format('Ymd') : date('Ymd')) . '-' . str_pad((string) $mutation->id, 4, '0', STR_PAD_LEFT);
    $prodCode = $mutation->product?->barcode ?: ($mutation->product?->kode_barang ?: null);
    $currentStock = (int) ($mutation->product?->stock ?? 0);

    $typeMeta = match ($mutation->type) {
        'masuk' => [
            'label' => 'Barang Masuk',
            'badge_text' => 'Masuk (+Stok)',
            'icon' => 'bi-box-arrow-in-down',
            'accent' => 'emerald',
            'sign' => '+',
            'origin' => $mutation->fromRoom?->name ?? 'Penerimaan Pengadaan / Gudang Utama',
            'origin_sub' => 'Sumber Barang Masuk',
            'destination' => $mutation->toRoom?->name ?? ($mutation->product?->room?->name ?? 'Gudang Inventaris'),
            'destination_sub' => 'Ruangan Penempatan',
        ],
        'keluar' => [
            'label' => 'Barang Keluar',
            'badge_text' => 'Keluar (-Stok)',
            'icon' => 'bi-box-arrow-up',
            'accent' => 'rose',
            'sign' => '-',
            'origin' => $mutation->fromRoom?->name ?? ($mutation->product?->room?->name ?? 'Gudang Inventaris'),
            'origin_sub' => 'Ruangan Asal Pengambilan',
            'destination' => $mutation->toRoom?->name ?? 'Distribusi / Pemakaian Operasional',
            'destination_sub' => 'Tujuan Pengeluaran',
        ],
        default => [
            'label' => 'Pindah Ruangan',
            'badge_text' => 'Transfer Antar-Ruang',
            'icon' => 'bi-arrow-left-right',
            'accent' => 'blue',
            'sign' => '⇄',
            'origin' => $mutation->fromRoom?->name ?? 'Ruangan Awal',
            'origin_sub' => 'Lokasi Asal Aset',
            'destination' => $mutation->toRoom?->name ?? 'Ruangan Tujuan',
            'destination_sub' => 'Lokasi Penempatan Baru',
        ],
    };

    $isPending = $mutation->status === 'pending';
    $isApproved = $mutation->status === 'approved';
    $isRejected = $mutation->status === 'rejected';
    $verifiedAt = $mutation->approved_at?->copy()->timezone('Asia/Jakarta')->locale('id');
    $createdAt = $mutation->created_at?->copy()->timezone('Asia/Jakarta')->locale('id');
@endphp

<div class="max-w-4xl mx-auto space-y-4 sm:space-y-5" data-framer-page>
    {{-- Top Navigation & Tracking Bar --}}
    <div class="flex flex-wrap items-center justify-between gap-3 pb-3.5 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
            <a href="{{ route('mutations.index') }}"
               class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 active:scale-95 transition-all shrink-0"
               title="Kembali ke Daftar Mutasi">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-base sm:text-xl font-extrabold tracking-tight text-slate-900 dark:text-white truncate">
                        Detail Transaksi Mutasi Barang
                    </h1>
                    <button type="button"
                            data-copy="{{ $trackingCode }}"
                            data-copy-label="Nomor Referensi Mutasi"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[11px] font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-blue-600 dark:hover:text-blue-400 border border-slate-200/80 dark:border-slate-700 transition-colors cursor-pointer"
                            title="Salin Nomor Referensi">
                        <span>{{ $trackingCode }}</span>
                        <i class="bi bi-copy text-[9px]"></i>
                    </button>
                </div>
                <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">
                    Lacak alur perpindahan lokasi, verifikasi otorisasi & rincian perubahan stok
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <button type="button"
                    data-share-code="m-{{ $mutation->id }}"
                    data-share-title="Mutasi {{ $mutation->product->name ?? 'Barang' }} — Inventaris Diskominfo Garut"
                    data-share-description="Transaksi {{ str_replace('_', ' ', $mutation->type) }} ({{ $mutation->quantity }} unit) • Status: {{ ucfirst($mutation->status) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 active:scale-95 whitespace-nowrap transition-all cursor-pointer">
                <i class="bi bi-share text-indigo-600 dark:text-indigo-400"></i>
                <span>Bagikan</span>
            </button>

            @if($isPending)
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-500/25 whitespace-nowrap">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Pending</span>
                </span>
            @elseif($isApproved)
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/25 whitespace-nowrap">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Disetujui</span>
                </span>
            @elseif($isRejected)
                <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-500/25 whitespace-nowrap">
                    <i class="bi bi-x-circle-fill"></i>
                    <span>Ditolak</span>
                </span>
            @endif
        </div>
    </div>

    {{-- Admin Quick Approval Banner with Equal-Size Icon Buttons --}}
    @if($isPending && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
        <div data-framer-card class="rounded-2xl bg-amber-50/90 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/60 p-4 flex items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-start gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-base shrink-0">
                    <i class="bi bi-shield-exclamation"></i>
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white">Menunggu Verifikasi Otorisasi Anda</h3>
                    <p class="text-[11px] sm:text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                        Tinjau rute & kuantitas di bawah. Gunakan tombol aksi untuk menyetujui atau menolak transaksi ini.
                    </p>
                </div>
            </div>

            {{-- Equal-Sized Icon Action Buttons (Setujui / Tolak) --}}
            <div class="flex items-center gap-2 shrink-0">
                <form action="{{ route('mutations.approve', $mutation) }}" method="POST" class="inline-flex m-0 shrink-0">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="w-9 h-9 rounded-xl inline-flex items-center justify-center text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm shadow-emerald-600/25 active:scale-90 transition-all cursor-pointer shrink-0"
                            title="Setujui Mutasi"
                            aria-label="Setujui Mutasi">
                        <i class="bi bi-check-lg text-base"></i>
                    </button>
                </form>

                <button type="button"
                        data-modal-target="rejectShowModal"
                        class="w-9 h-9 rounded-xl inline-flex items-center justify-center text-rose-600 dark:text-rose-300 bg-rose-100/90 dark:bg-rose-950/60 hover:bg-rose-600 hover:text-white border border-rose-200 dark:border-rose-800/60 active:scale-90 transition-all cursor-pointer shrink-0"
                        title="Tolak Mutasi"
                        aria-label="Tolak Mutasi">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
        </div>
    @endif

    {{-- Hero Route & Movement Visualizer Card (Origin -> Movement -> Destination) --}}
    <div data-framer-card class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs overflow-hidden">
        {{-- Top Banner Strip --}}
        <div class="px-4 sm:px-6 py-3 bg-slate-50/80 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-extrabold
                    {{ $mutation->type === 'masuk' ? 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/25' : ($mutation->type === 'keluar' ? 'bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-500/25' : 'bg-blue-500/15 text-blue-700 dark:text-blue-300 border border-blue-500/25') }}">
                    <i class="bi {{ $typeMeta['icon'] }}"></i>
                    <span class="capitalize">{{ ucfirst(str_replace('_', ' ', $mutation->type)) }}</span>
                </span>
                <span class="text-xs text-slate-400 dark:text-slate-500">&bull;</span>
                <span class="text-xs font-semibold text-slate-600 dark:text-slate-300">
                    <i class="bi bi-calendar3 me-1 text-slate-400"></i>
                    {{ $mutation->mutation_date?->locale('id')->translatedFormat('l, d F Y') }}
                </span>
            </div>

            <div class="flex items-center gap-2 text-xs font-extrabold text-blue-600 dark:text-blue-400 tabular-nums">
                <span class="px-2.5 py-1 rounded-lg bg-blue-50 dark:bg-blue-950/50 border border-blue-200/60 dark:border-blue-800/50">
                    {{ $mutation->quantity }} Unit
                </span>
            </div>
        </div>

        {{-- Route Flow Diagram --}}
        <div class="p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-11 gap-3 sm:gap-4 items-center">
                {{-- Origin Node --}}
                <div class="md:col-span-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800 space-y-1">
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        <span>Ruangan Asal</span>
                        <i class="bi bi-geo-alt text-slate-400"></i>
                    </div>
                    <div class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white leading-snug">
                        {{ $mutation->fromRoom?->name ?? 'Gudang / Tidak Ditentukan' }}
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $typeMeta['origin_sub'] }}</p>
                </div>

                {{-- Center Transit Connector --}}
                <div class="md:col-span-3 flex flex-col items-center justify-center py-1">
                    <div class="w-full flex items-center justify-center gap-2">
                        <div class="h-0.5 flex-1 bg-gradient-to-r from-slate-200 via-blue-400 to-blue-600 dark:from-slate-800 dark:via-blue-500 dark:to-blue-400 rounded-full"></div>
                        <div class="w-10 h-10 rounded-2xl bg-blue-600 text-white shadow-md shadow-blue-600/25 flex items-center justify-center shrink-0">
                            <i class="bi {{ $typeMeta['icon'] }} text-base"></i>
                        </div>
                        <div class="h-0.5 flex-1 bg-gradient-to-r from-blue-600 via-blue-400 to-slate-200 dark:from-blue-400 dark:via-blue-500 dark:to-slate-800 rounded-full"></div>
                    </div>
                    <span class="mt-1.5 text-[11px] font-extrabold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                        {{ $typeMeta['sign'] }}{{ $mutation->quantity }} Unit
                    </span>
                </div>

                {{-- Destination Node --}}
                <div class="md:col-span-4 p-4 rounded-2xl bg-blue-50/50 dark:bg-blue-950/25 border border-blue-200/70 dark:border-blue-800/60 space-y-1">
                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                        <span>Ruangan Tujuan</span>
                        <i class="bi bi-pin-map-fill"></i>
                    </div>
                    <div class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white leading-snug">
                        {{ $mutation->toRoom?->name ?? 'Gudang / Tidak Ditentukan' }}
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $typeMeta['destination_sub'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 4-Step Transaction Tracking Stepper --}}
    <div data-framer-card class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
                <i class="bi bi-diagram-3 text-blue-600 dark:text-blue-400"></i>
                <span>Alur Pelacakan & Status Verifikasi</span>
            </h2>
            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                {{ $isApproved ? '4/4 Tahap Selesai' : ($isRejected ? 'Dihentikan pada Tahap Verifikasi' : 'Menunggu Tahap 2') }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            {{-- Step 1: Submitted --}}
            <div class="p-3.5 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/20 border border-emerald-200/70 dark:border-emerald-800/50 space-y-1">
                <div class="flex items-center justify-between">
                    <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white text-xs font-bold flex items-center justify-center">
                        <i class="bi bi-check2"></i>
                    </span>
                    <span class="text-[10px] font-bold uppercase text-emerald-700 dark:text-emerald-400">Tahap 1</span>
                </div>
                <p class="text-xs font-extrabold text-slate-900 dark:text-white pt-1">Pengajuan Dicatat</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $mutation->user->name ?? 'Staf Diskominfo' }}</p>
                <p class="text-[10px] text-slate-400 tabular-nums">{{ $createdAt ? $createdAt->translatedFormat('d M Y, H:i') : '-' }}</p>
            </div>

            {{-- Step 2: Admin Verification --}}
            <div class="p-3.5 rounded-xl border space-y-1 {{ $isApproved ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200/70 dark:border-emerald-800/50' : ($isRejected ? 'bg-rose-50/60 dark:bg-rose-950/20 border-rose-200/70 dark:border-rose-800/50' : 'bg-amber-50/70 dark:bg-amber-950/25 border-amber-300/80 dark:border-amber-700/60') }}">
                <div class="flex items-center justify-between">
                    <span class="w-6 h-6 rounded-lg text-white text-xs font-bold flex items-center justify-center {{ $isApproved ? 'bg-emerald-600' : ($isRejected ? 'bg-rose-600' : 'bg-amber-500 animate-pulse') }}">
                        <i class="bi {{ $isApproved ? 'bi-check2' : ($isRejected ? 'bi-x-lg' : 'bi-hourglass-split') }}"></i>
                    </span>
                    <span class="text-[10px] font-bold uppercase {{ $isApproved ? 'text-emerald-700 dark:text-emerald-400' : ($isRejected ? 'text-rose-700 dark:text-rose-400' : 'text-amber-700 dark:text-amber-400') }}">Tahap 2</span>
                </div>
                <p class="text-xs font-extrabold text-slate-900 dark:text-white pt-1">Verifikasi Otorisasi</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                    {{ $mutation->approver?->name ?? ($isPending ? 'Menunggu Admin' : 'Administrator') }}
                </p>
                <p class="text-[10px] text-slate-400 tabular-nums">
                    {{ $verifiedAt ? $verifiedAt->translatedFormat('d M Y, H:i') : 'Dalam antrean' }}
                </p>
            </div>

            {{-- Step 3: Stock / Room Execution --}}
            <div class="p-3.5 rounded-xl border space-y-1 {{ $isApproved ? 'bg-emerald-50/60 dark:bg-emerald-950/20 border-emerald-200/70 dark:border-emerald-800/50' : 'bg-slate-50 dark:bg-slate-900/40 border-slate-200/70 dark:border-slate-800 opacity-75' }}">
                <div class="flex items-center justify-between">
                    <span class="w-6 h-6 rounded-lg text-xs font-bold flex items-center justify-center {{ $isApproved ? 'bg-emerald-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500' }}">
                        <i class="bi {{ $isApproved ? 'bi-box-seam-fill' : 'bi-box-seam' }}"></i>
                    </span>
                    <span class="text-[10px] font-bold uppercase {{ $isApproved ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-400' }}">Tahap 3</span>
                </div>
                <p class="text-xs font-extrabold text-slate-900 dark:text-white pt-1">Eksekusi Inventaris</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                    {{ $isApproved ? 'Stok & lokasi diperbarui' : ($isRejected ? 'Tidak dieksekusi' : 'Menunggu persetujuan') }}
                </p>
                <p class="text-[10px] text-slate-400 tabular-nums">{{ $mutation->quantity }} Unit &bull; {{ $typeMeta['label'] }}</p>
            </div>

            {{-- Step 4: Completion / Audit Ledger --}}
            <div class="p-3.5 rounded-xl border space-y-1 {{ $isApproved ? 'bg-blue-50/60 dark:bg-blue-950/25 border-blue-200/70 dark:border-blue-800/50' : ($isRejected ? 'bg-rose-50/50 dark:bg-rose-950/20 border-rose-200/60 dark:border-rose-800/40' : 'bg-slate-50 dark:bg-slate-900/40 border-slate-200/70 dark:border-slate-800 opacity-75') }}">
                <div class="flex items-center justify-between">
                    <span class="w-6 h-6 rounded-lg text-xs font-bold flex items-center justify-center {{ $isApproved ? 'bg-blue-600 text-white' : ($isRejected ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-500') }}">
                        <i class="bi {{ $isApproved ? 'bi-patch-check-fill' : ($isRejected ? 'bi-x-octagon-fill' : 'bi-journal-check') }}"></i>
                    </span>
                    <span class="text-[10px] font-bold uppercase {{ $isApproved ? 'text-blue-700 dark:text-blue-400' : ($isRejected ? 'text-rose-700 dark:text-rose-400' : 'text-slate-400') }}">Tahap 4</span>
                </div>
                <p class="text-xs font-extrabold text-slate-900 dark:text-white pt-1">Status Akhir</p>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                    {{ $isApproved ? 'Transaksi Selesai' : ($isRejected ? 'Transaksi Ditolak' : 'Belum Selesai') }}
                </p>
                <p class="text-[10px] text-slate-400 tabular-nums">Ref: {{ $trackingCode }}</p>
            </div>
        </div>
    </div>

    {{-- Asset Details, Operator Info & Audit Metadata Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5 sm:gap-5" data-framer-stagger>
        {{-- Info Barang --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Objek Barang / Aset</span>
                @if($mutation->product)
                    <a href="{{ route('products.show', $mutation->product) }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                        <span>Detail Barang</span>
                        <i class="bi bi-arrow-up-right text-[10px]"></i>
                    </a>
                @endif
            </div>
            <div class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">
                @if($mutation->product)
                    <a href="{{ route('products.show', $mutation->product) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                        {{ $mutation->product->name }}
                    </a>
                @else
                    -
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2 pt-0.5">
                @if($prodCode)
                    <span class="text-xs font-mono font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2.5 py-0.5 rounded-lg">
                        {{ $prodCode }}
                    </span>
                    <button type="button"
                            data-copy="{{ $prodCode }}"
                            data-copy-label="Kode Barang"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold text-slate-500 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                        <i class="bi bi-copy text-[10px]"></i>
                        <span>Salin</span>
                    </button>
                @endif
                @if($mutation->product?->category_name)
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300">
                        {{ $mutation->product->category_name }}
                    </span>
                @endif
                <span class="px-2 py-0.5 rounded-lg text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 tabular-nums">
                    Stok Saat Ini: {{ $currentStock }} unit
                </span>
            </div>
        </div>

        {{-- Pengaju & Waktu Transaksi --}}
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-2.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Petugas Pengaju & Jadwal</span>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($mutation->user->name ?? 'S', 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <div class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white truncate">
                        {{ $mutation->user->name ?? 'Staf Diskominfo' }}
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Tanggal Mutasi: <strong class="text-slate-700 dark:text-slate-200">{{ $mutation->mutation_date?->locale('id')->translatedFormat('l, d F Y') }}</strong>
                    </p>
                </div>
            </div>
        </div>

        {{-- Catatan Transaksi --}}
        <div class="md:col-span-2 p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-2xs space-y-1.5">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Catatan / Keterangan Operasional</span>
            <p class="text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                {{ $mutation->note ?: 'Tidak ada catatan tambahan.' }}
            </p>
        </div>

        {{-- Status Verifikasi / Approval Detail --}}
        @if($mutation->status !== 'pending' && $mutation->approver)
            @php
                $approverRole = match (strtolower((string) $mutation->approver->role)) {
                    'superadmin' => 'Super Admin',
                    'admin', 'administrator' => 'Admin',
                    default => 'Petugas',
                };
            @endphp

            <div class="md:col-span-2 p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-2xs">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 shrink-0 rounded-xl flex items-center justify-center text-lg {{ $isApproved ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400' }}">
                        <i class="bi {{ $isApproved ? 'bi-check-circle-fill' : 'bi-x-circle-fill' }}"></i>
                    </div>
                    <div class="min-w-0">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Verifikasi Administrator</span>
                        <div class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">
                            {{ $isApproved ? 'Disetujui' : 'Ditolak' }}
                            @if($verifiedAt)
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">&middot; {{ $verifiedAt->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <dl class="mt-3.5 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <div class="rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 p-3">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">{{ $isApproved ? 'Disetujui oleh' : 'Ditolak oleh' }}</dt>
                        <dd class="mt-1 flex flex-wrap items-center gap-1.5 text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                            <span class="truncate">{{ $mutation->approver->name ?? 'Administrator' }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/15 text-blue-600 dark:text-blue-300 border border-blue-500/25">{{ $approverRole }}</span>
                        </dd>
                    </div>

                    <div class="rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 p-3">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal</dt>
                        <dd class="mt-1 text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                            {{ $verifiedAt ? $verifiedAt->translatedFormat('l, d F Y') : '-' }}
                        </dd>
                    </div>

                    <div class="rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 p-3">
                        <dt class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jam</dt>
                        <dd class="mt-1 text-xs sm:text-sm font-bold text-slate-900 dark:text-white">
                            {{ $verifiedAt ? $verifiedAt->format('H:i') . ' WIB' : '-' }}
                        </dd>
                    </div>
                </dl>

                @if(! $isApproved && $mutation->rejection_note)
                    <div class="mt-3 rounded-xl bg-rose-50/70 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 p-3">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-rose-500 dark:text-rose-400">Alasan Penolakan</div>
                        <p class="mt-1 text-xs sm:text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ $mutation->rejection_note }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

@if($isPending && (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin()))
    <div id="rejectShowModal" class="modal-container responsive-bottom-sheet fixed inset-0 z-50 hidden items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="sheet-dialog relative w-full sm:max-w-md rounded-t-3xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-5 sm:p-6">
            <div class="sheet-grab-handle w-11 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto mb-3 sm:hidden"></div>
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <h3 class="text-sm sm:text-base font-extrabold text-rose-600 dark:text-rose-400">Tolak Mutasi</h3>
                <button type="button" data-modal-close class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <form action="{{ route('mutations.reject', $mutation) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Alasan Penolakan <span class="text-rose-500">*</span></label>
                    <textarea name="rejection_note" rows="3" required placeholder="Tuliskan alasan penolakan..."
                              class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white focus:ring-2 focus:ring-rose-500/40 focus:border-rose-500 transition-colors"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                    <button type="button" data-modal-close class="px-4 py-2 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-xs cursor-pointer">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection