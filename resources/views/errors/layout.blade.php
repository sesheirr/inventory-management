<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — @yield('title') | Inventaris Diskominfo Garut</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-diskominfo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('theme') || 'light';
                var isDark = saved === 'dark' || (saved === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
            } catch (e) {}
        })();
    </script>
    <style>
        @keyframes floatDiskominfo {
            0%, 100% { transform: translate3d(0, 0, 0) rotate(0deg); }
            50% { transform: translate3d(0, -10px, 0) rotate(1.5deg); }
        }
        @keyframes pulseRing {
            0% { transform: scale3d(0.92, 0.92, 1); opacity: 0.65; }
            70% { transform: scale3d(1.22, 1.22, 1); opacity: 0; }
            100% { transform: scale3d(1.22, 1.22, 1); opacity: 0; }
        }
        .animate-float-logo {
            animation: floatDiskominfo 4.2s cubic-bezier(0.45, 0, 0.55, 1) infinite;
            will-change: transform;
        }
        .animate-pulse-ring {
            animation: pulseRing 2.8s cubic-bezier(0.22, 1, 0.36, 1) infinite;
            will-change: transform, opacity;
        }
    </style>
</head>
<body class="min-h-full bg-slate-50 dark:bg-[#0b132b] text-slate-900 dark:text-slate-100 flex items-center justify-center p-4 sm:p-6 overflow-x-hidden">
    <div class="relative max-w-lg w-full">
        {{-- Ambient Glow --}}
        <div class="absolute -top-16 -left-16 w-64 h-64 rounded-full bg-blue-500/15 dark:bg-blue-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-16 -right-16 w-64 h-64 rounded-full bg-indigo-500/15 dark:bg-indigo-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative rounded-3xl bg-white/95 dark:bg-[#0f1b38]/95 backdrop-blur-xl border border-slate-200/80 dark:border-slate-800 shadow-2xl p-6 sm:p-10 text-center space-y-6">
            {{-- Animated Diskominfo Emblem Highlight --}}
            <div class="relative mx-auto w-28 h-28 flex items-center justify-center">
                <div class="absolute inset-0 rounded-3xl bg-blue-500/20 dark:bg-blue-400/20 animate-pulse-ring"></div>
                <div class="animate-float-logo relative w-24 h-24 rounded-3xl bg-white dark:bg-slate-900 p-3 shadow-xl shadow-blue-600/15 border-2 border-blue-500/30 dark:border-blue-400/30 flex items-center justify-center">
                    <img src="{{ asset('images/logo-diskominfo.png') }}"
                         alt="Logo Diskominfo Kabupaten Garut"
                         class="w-full h-full object-contain">
                </div>
                <span class="absolute -bottom-2 -right-2 px-2.5 py-1 rounded-xl text-xs font-extrabold tracking-wider bg-blue-600 text-white shadow-lg shadow-blue-600/30 border-2 border-white dark:border-[#0f1b38]">
                    @yield('code')
                </span>
            </div>

            {{-- Institution Identity Pill --}}
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border border-blue-200/70 dark:border-blue-800/50">
                <i class="bi @yield('icon', 'bi-shield-exclamation')"></i>
                <span>Inventaris Diskominfo Kabupaten Garut</span>
            </div>

            {{-- Error Heading & Explanation --}}
            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    @yield('title')
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed max-w-md mx-auto">
                    @yield('message')
                </p>
            </div>

            {{-- Recovery Actions --}}
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ url('/dashboard') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/25 active:scale-95 transition-all cursor-pointer">
                    <i class="bi bi-grid-fill"></i>
                    <span>Ke Dashboard Utama</span>
                </a>
                <a href="{{ url('/products') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all cursor-pointer">
                    <i class="bi bi-box-seam"></i>
                    <span>Cari Data Barang</span>
                </a>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                <span>Dinas Komunikasi dan Informatika Kab. Garut</span>
                <button type="button" onclick="window.history.back()" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline cursor-pointer">
                    <i class="bi bi-arrow-left"></i> Kembali
                </button>
            </div>
        </div>
    </div>
</body>
</html>
