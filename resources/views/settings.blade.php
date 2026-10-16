@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    {{-- Page Header --}}
    <div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Sistem</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Sesuaikan tema tampilan (Terang, Gelap, atau Ikuti Sistem), aplikasi PWA, dan notifikasi perangkat</p>
    </div>

    {{-- Appearance / Theme Card (3 Options: Light, Dark, System) --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 space-y-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-lg">
                    <i class="bi bi-palette"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Tema & Tampilan Antarmuka</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pilih skema warna atau biarkan mengikuti pengaturan sistem operasi perangkat Anda</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
            {{-- Light Theme Option --}}
            <button type="button"
                    data-theme-option="light"
                    class="relative flex items-center gap-3.5 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 text-left transition-all cursor-pointer group">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shadow-xs border border-amber-200/60 shrink-0">
                    <i class="bi bi-sun-fill"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">Mode Terang</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Cerah, bersih & kontras tinggi</div>
                </div>
                <span class="theme-check-indicator hidden w-5 h-5 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center shrink-0">
                    <i class="bi bi-check2"></i>
                </span>
            </button>

            {{-- Dark Theme Option --}}
            <button type="button"
                    data-theme-option="dark"
                    class="relative flex items-center gap-3.5 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 text-left transition-all cursor-pointer group">
                <div class="w-11 h-11 rounded-xl bg-slate-900 text-blue-400 flex items-center justify-center text-xl shadow-xs border border-slate-700 shrink-0">
                    <i class="bi bi-moon-stars-fill"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">Mode Gelap</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Nyaman di mata & hemat daya</div>
                </div>
                <span class="theme-check-indicator hidden w-5 h-5 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center shrink-0">
                    <i class="bi bi-check2"></i>
                </span>
            </button>

            {{-- Follow System Theme Option --}}
            <button type="button"
                    data-theme-option="system"
                    class="relative flex items-center gap-3.5 p-4 rounded-2xl border border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 text-left transition-all cursor-pointer group">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center text-xl shadow-xs shrink-0">
                    <i class="bi bi-laptop"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-bold text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">Ikuti Sistem</div>
                    <div class="text-[11px] text-slate-500 dark:text-slate-400">Otomatis sesuai tema perangkat</div>
                </div>
                <span class="theme-check-indicator hidden w-5 h-5 rounded-full bg-blue-600 text-white text-xs flex items-center justify-center shrink-0">
                    <i class="bi bi-check2"></i>
                </span>
            </button>
        </div>
    </div>

    {{-- PWA & Push Notification Card --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg shrink-0">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Notifikasi Perangkat & Aplikasi PWA</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Aktifkan notifikasi instan untuk persetujuan mutasi berjenjang & peringatan stok barang di Tablet, Mobile, maupun Desktop</p>
                </div>
            </div>

            <button type="button"
                    id="enableWebPushBtn"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm shadow-blue-600/20 transition-all cursor-pointer shrink-0">
                <i class="bi bi-broadcast"></i>
                <span>Aktifkan Notifikasi Perangkat</span>
            </button>
        </div>
    </div>

    {{-- Quick Account Links --}}
    <div class="rounded-2xl bg-white dark:bg-[#0f1b38] border border-slate-200/80 dark:border-slate-800/80 shadow-sm p-6 space-y-4">
        <h2 class="text-base font-bold text-slate-900 dark:text-white">Akses Cepat Akun</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="{{ route('profile') }}" class="flex items-center justify-between p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <div class="flex items-center gap-3">
                    <i class="bi bi-person-circle text-xl text-blue-600 dark:text-blue-400"></i>
                    <div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white">Profil & Keamanan</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Perbarui nama, foto profil, dan kata sandi</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right text-slate-400"></i>
            </a>

            <a href="{{ route('notifications.index') }}" class="flex items-center justify-between p-4 rounded-xl border border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                <div class="flex items-center gap-3">
                    <i class="bi bi-bell text-xl text-amber-500"></i>
                    <div>
                        <div class="text-sm font-bold text-slate-900 dark:text-white">Pusat Notifikasi</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">Lihat riwayat pengajuan mutasi & peringatan stok</div>
                    </div>
                </div>
                <i class="bi bi-chevron-right text-slate-400"></i>
            </a>
        </div>
    </div>
</div>
@endsection
