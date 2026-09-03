@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80 dark:border-slate-800/80">
        <div class="flex items-center gap-3">
            <a href="{{ route('mutations.index') }}" class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Detail Mutasi Barang</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Rincian status proses, pihak pengaju, dan catatan pemindahan</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @switch($mutation->status)
                @case('pending')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                        <i class="bi bi-clock"></i> Menunggu Persetujuan
                    </span>
                    @break
                @case('approved')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                        <i class="bi bi-check-circle"></i> Disetujui
                    </span>
                    @break
                @case('rejected')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-800/40">
                        <i class="bi bi-x-circle"></i> Ditolak
                    </span>
                    @break
            @endswitch
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        {{-- Info Barang --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Barang</span>
            <div class="text-base font-bold text-slate-900 dark:text-white">{{ $mutation->product->name ?? '-' }}</div>
            <div class="text-xs font-mono text-slate-500">{{ $mutation->product->barcode ?? ($mutation->product->kode_barang ?? '-') }}</div>
        </div>

        {{-- Jenis Mutasi --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Jenis Mutasi</span>
            <div class="text-base font-bold text-slate-900 dark:text-white capitalize">
                {{ ucfirst(str_replace('_', ' ', $mutation->type)) }}
            </div>
            <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold">{{ $mutation->quantity }} Unit Dimutasi</div>
        </div>

        {{-- Lokasi Asal & Tujuan --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Ruangan Asal</span>
            <div class="text-base font-bold text-slate-900 dark:text-white">{{ $mutation->fromRoom?->name ?? 'Gudang / Tidak Ditentukan' }}</div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Ruangan Tujuan</span>
            <div class="text-base font-bold text-slate-900 dark:text-white">{{ $mutation->toRoom?->name ?? 'Gudang / Tidak Ditentukan' }}</div>
        </div>

        {{-- Tanggal & Catatan --}}
        <div class="p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Tanggal Pengajuan Mutasi</span>
            <div class="text-base font-bold text-slate-900 dark:text-white">{{ optional($mutation->mutation_date)->translatedFormat('l, d F Y') }}</div>
        </div>

        <div class="p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Pengaju Mutasi</span>
            <div class="text-base font-bold text-slate-900 dark:text-white">{{ $mutation->user->name ?? 'Staf Diskominfo' }}</div>
        </div>

        <div class="md:col-span-2 p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Catatan Mutasi</span>
            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-900/50 p-3.5 rounded-xl border border-slate-100 dark:border-slate-800">
                {{ $mutation->note ?: 'Tidak ada catatan tambahan untuk mutasi ini.' }}
            </p>
        </div>

        {{-- Status Verifikasi / Approval Detail --}}
        @if($mutation->status !== 'pending' && $mutation->approver)
            <div class="md:col-span-2 p-5 rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm space-y-1">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Riwayat Verifikasi Administrator</span>
                <div class="text-sm text-slate-800 dark:text-slate-200">
                    {{ $mutation->status === 'approved' ? 'Disetujui oleh' : 'Ditolak oleh' }}
                    <strong class="text-blue-600 dark:text-blue-400">{{ $mutation->approver->name ?? 'Administrator' }}</strong>
                    pada {{ optional($mutation->approved_at)->translatedFormat('d F Y, H:i WIB') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection