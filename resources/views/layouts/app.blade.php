<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="author" content="fwzann-abd (https://github.com/fwzann-abd)">
    <meta name="co-collaborator" content="msayib (https://github.com/msayib) — Ideation, System Design, Architecture, Performance, Refactor, Tune-Up, UI/UX & Mobile-First High-Level Engineering">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Inventaris Diskominfo">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-diskominfo.png') }}">

    @php
        $resolvedPageTitle = trim($__env->yieldContent('page-title', 'Dashboard Inventaris'));
        $resolvedPageSubtitle = trim($__env->yieldContent('page-subtitle', 'Sistem Informasi Inventarisasi Barang & Aset Diskominfo Kabupaten Garut.'));
        $resolvedShareImage = trim($__env->yieldContent('share-image', asset('images/logo-diskominfo.png')));
        $resolvedShareCode = $shareShortCode ?? (function () {
            $r = request()->route();
            $name = $r ? $r->getName() : '';
            return match ($name) {
                'products.show' => 'p-' . ($r->parameter('product')?->id ?? $r->parameter('product')),
                'rooms.show' => 'r-' . ($r->parameter('room')?->id ?? $r->parameter('room')),
                'categories.show' => 'c-' . ($r->parameter('category')?->id ?? $r->parameter('category')),
                'products.index' => 'products',
                'rooms.index' => 'rooms',
                'categories.index' => 'categories',
                'mutations.index' => 'mutations',
                'products.scan' => 'scan',
                'notifications.index' => 'notifications',
                default => 'dash',
            };
        })();
    @endphp

    <title>{{ $resolvedPageTitle }} — {{ config('app.name', 'Inventaris Diskominfo Garut') }}</title>
    <meta name="description" content="{{ $resolvedPageSubtitle }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Inventaris Diskominfo Kabupaten Garut">
    <meta property="og:title" content="{{ $resolvedPageTitle }} — Diskominfo Garut">
    <meta property="og:description" content="{{ $resolvedPageSubtitle }}">
    <meta property="og:image" content="{{ $resolvedShareImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $resolvedPageTitle }} — Diskominfo Garut">
    <meta name="twitter:description" content="{{ $resolvedPageSubtitle }}">
    <meta name="twitter:image" content="{{ $resolvedShareImage }}">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    {{-- Prevent Dark Mode & Sidebar Collapse Flash --}}
    <script>
        (function () {
            try {
                const saved = localStorage.getItem('theme') || localStorage.getItem('color-theme') || 'light';
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = saved === 'dark' || (saved === 'system' && prefersDark);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
                document.documentElement.dataset.themeMode = saved;
                if (localStorage.getItem('sidebar_collapsed') === '1') {
                    document.documentElement.setAttribute('data-sidebar-collapsed', 'true');
                }
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/app.tsx'])
    @stack('styles')
</head>
<body class="min-h-screen flex bg-[#F8FAFC] dark:bg-[#12100e] text-slate-800 dark:text-[#f5f5f4] antialiased selection:bg-blue-600 selection:text-white relative {{ request()->routeIs('products.scan') ? 'scanner-fullscreen-active' : '' }}"
      data-page-share-code="{{ $resolvedShareCode }}"
      data-page-share-title="{{ $resolvedPageTitle }} — Inventaris Diskominfo Garut"
      data-page-share-description="{{ $resolvedPageSubtitle }}"
      data-page-share-image="{{ $resolvedShareImage }}">

    {{-- Subtle Technical Grid Backdrop (Shared Aesthetic with Landing Page + Warm Dark Glow) --}}
    <div id="appGridBackdrop" class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute inset-0 opacity-[0.032] dark:opacity-[0.048] text-slate-900 dark:text-amber-100" style="background-image: linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div class="absolute -top-48 right-1/4 w-[38rem] h-[38rem] rounded-full bg-blue-600/7 dark:bg-amber-500/8 blur-3xl"></div>
        <div class="absolute bottom-0 left-10 w-[30rem] h-[30rem] rounded-full bg-cyan-500/5 dark:bg-orange-500/6 blur-3xl"></div>
    </div>

    {{-- Desktop Left Sidebar Navigation (Hidden in Fullscreen Barcode Scanner Mode) --}}
    @if (!request()->routeIs('products.scan'))
        @include('partials.sidebar')
    @endif

    {{-- Main Content Area --}}
    <div class="relative flex-1 flex flex-col min-w-0 min-h-screen">
        {{-- Top Navbar (Hidden in Fullscreen Barcode Scanner Mode) --}}
        @if (!request()->routeIs('products.scan'))
            @include('partials.navbar', ['shareShortCode' => $resolvedShareCode])
        @endif

        {{-- Server-side Flash Trigger for Sonner Toast --}}
        @if (session('success') || session('error') || session('status'))
            @php
                $flashToastPayload = [
                    'type' => session('error') ? 'error' : 'success',
                    'title' => session('error') ? 'Permintaan Gagal' : 'Berhasil',
                    'description' => session('error') ?? session('success') ?? session('status'),
                ];
            @endphp
            <div id="initialFlashToastData" class="hidden" data-toast="{{ json_encode($flashToastPayload) }}"></div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var el = document.getElementById('initialFlashToastData');
                    if (el && el.dataset.toast && window.toast) {
                        try {
                            var f = JSON.parse(el.dataset.toast);
                            var fn = window.toast[f.type] || window.toast;
                            fn(f.title, { description: f.description });
                        } catch (e) {}
                    }
                });
            </script>
        @endif

        {{-- Native iOS-Style Content-Pushing Pull-to-Refresh Reveal Slot (All Pages on Mobile/Touch) --}}
        <div id="nativePtrSlot" class="native-ptr-slot" aria-live="polite">
            <div id="nativePtrSpinner" class="native-ptr-spinner">
                @for ($spoke = 0; $spoke < 8; $spoke++)
                    <span class="native-ptr-spoke" style="transform: rotate({{ $spoke * 45 }}deg); opacity: {{ number_format(0.25 + ($spoke * 0.09), 2) }};"></span>
                @endfor
            </div>
            <span id="nativePtrLabel" class="text-[11px] font-bold tracking-tight text-slate-500 dark:text-stone-400">
                Tarik ke bawah untuk memuat ulang
            </span>
        </div>

        {{-- Page Body --}}
        <main id="appMainContent" class="flex-1 {{ request()->routeIs('products.scan') ? 'p-0 max-w-none' : 'px-3.5 sm:px-6 lg:px-8 py-4 sm:py-6 pb-24 lg:pb-8 max-w-[1600px]' }} w-full mx-auto">
            @yield('content')
        </main>

        {{-- Footer (Desktop Only to keep Mobile Native App Feel Clean) --}}
        @if (!request()->routeIs('products.scan'))
            <div class="hidden lg:block">
                @include('partials.footer')
            </div>
        @endif
    </div>

    {{-- Native Mobile Bottom Navigation Dock (Hidden in Fullscreen Barcode Scanner Mode) --}}
    @if (!request()->routeIs('products.scan'))
        <nav id="mobileBottomDock"
             aria-label="Navigasi Bawah Mobile"
             class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-white/95 dark:bg-[#1c1917]/95 backdrop-blur-xl border-t border-slate-200/80 dark:border-stone-800/90 px-2 py-1.5 shadow-[0_-8px_24px_-6px_rgba(15,23,42,0.08)]">
            <div class="grid grid-cols-5 items-center max-w-md mx-auto">
                <a href="{{ route('dashboard') }}"
                   class="flex flex-col items-center justify-center py-1 rounded-xl text-[10px] font-bold transition-colors {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-amber-400' : 'text-slate-500 dark:text-stone-400' }}">
                    <i class="bi {{ request()->routeIs('dashboard') ? 'bi-grid-fill' : 'bi-grid' }} text-base"></i>
                    <span class="mt-0.5">Beranda</span>
                </a>

                <a href="{{ route('products.index') }}"
                   class="flex flex-col items-center justify-center py-1 rounded-xl text-[10px] font-bold transition-colors {{ request()->routeIs('products.*') && !request()->routeIs('products.scan') ? 'text-blue-600 dark:text-amber-400' : 'text-slate-500 dark:text-stone-400' }}">
                    <i class="bi {{ request()->routeIs('products.*') && !request()->routeIs('products.scan') ? 'bi-box-seam-fill' : 'bi-box-seam' }} text-base"></i>
                    <span class="mt-0.5">Barang</span>
                </a>

                <div class="flex items-center justify-center -mt-5">
                    <a href="{{ route('products.scan') }}"
                       class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-600/35 border-4 border-white dark:border-[#12100e] flex items-center justify-center"
                       title="Scan Barcode Fullscreen">
                        <i class="bi bi-upc-scan text-lg"></i>
                    </a>
                </div>

                <a href="{{ route('mutations.index') }}"
                   class="flex flex-col items-center justify-center py-1 rounded-xl text-[10px] font-bold transition-colors {{ request()->routeIs('mutations.*') ? 'text-blue-600 dark:text-amber-400' : 'text-slate-500 dark:text-stone-400' }}">
                    <i class="bi bi-arrow-left-right text-base"></i>
                    <span class="mt-0.5">Mutasi</span>
                </a>

                <button type="button"
                        id="mobileBottomMenuBtn"
                        data-modal-target="mobileAppMenuSheet"
                        data-draggable-menu-opener="true"
                        aria-label="Buka atau tarik menu ke atas"
                        class="relative flex flex-col items-center justify-center py-1 rounded-xl text-[10px] font-bold transition-colors touch-none select-none {{ request()->routeIs('rooms.*', 'categories.*', 'notifications.*', 'settings', 'profile', 'users.*', 'activity-logs.*') ? 'text-blue-600 dark:text-amber-400' : 'text-slate-500 dark:text-stone-400' }}">
                    <span class="w-4 h-0.5 rounded-full bg-slate-300 dark:bg-stone-600 mb-0.5"></span>
                    <i class="bi bi-grid-3x3-gap-fill text-base"></i>
                    <span class="mt-0.5">Menu</span>
                </button>
            </div>
        </nav>
    @endif

    {{-- =====================================================================
         NATIVE MOBILE APP MENU LAUNCHER BOTTOM SHEET (DRAGGABLE UP & DOWN)
         ===================================================================== --}}
    @php
        $mobileSheetUnread = 0;
        $mobileSheetPending = 0;
        if (auth()->check()) {
            try {
                $mobileSheetUnread = auth()->user()->unreadNotifications()->count();
                if (auth()->user()->isAdmin()) {
                    $mobileSheetPending = \App\Models\Mutation::where('status', 'pending')->count();
                }
            } catch (\Throwable $e) {}
        }
    @endphp
    <div id="mobileAppMenuSheet"
         data-snap-state="compact"
         class="modal-container responsive-bottom-sheet fixed inset-0 z-50 hidden items-end sm:items-center justify-center bg-slate-900/60 backdrop-blur-xs p-0 sm:p-4">
        <div id="mobileAppMenuDialog" class="sheet-dialog bg-white dark:bg-[#1c1917] border border-slate-200 dark:border-stone-800 rounded-t-3xl sm:rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in">
            {{-- Mobile Sheet Grab Handle (Drag Up to Expand / Drag Down to Collapse or Close) --}}
            <div class="sheet-grab-area pt-2.5 pb-1.5 cursor-pointer touch-none select-none flex flex-col items-center justify-center sm:hidden" data-sheet-drag-handle="true">
                <div class="sheet-grab-handle w-12 h-1.5 rounded-full bg-slate-300 dark:bg-stone-600"></div>
                <span id="mobileMenuSheetSnapHint" class="text-[9px] font-bold text-slate-400 dark:text-stone-500 mt-1">Geser ke atas untuk layar penuh • ke bawah untuk tutup</span>
            </div>

            {{-- Compact User Card --}}
            <div class="px-5 pt-3 pb-3.5 border-b border-slate-100 dark:border-stone-800 flex items-center justify-between gap-3">
                <a href="{{ route('profile') }}" class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white font-extrabold text-sm flex items-center justify-center shadow-sm shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-extrabold text-slate-900 dark:text-stone-100 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] font-semibold text-blue-600 dark:text-amber-400 truncate">{{ auth()->user()->roleLabel() }}</p>
                    </div>
                </a>
                <button type="button" data-modal-close class="w-8 h-8 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-stone-200 hover:bg-slate-100 dark:hover:bg-stone-800">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            {{-- Native App Icon Grid --}}
            <div class="p-4 space-y-4">
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-stone-500 px-1 mb-2">Modul Inventaris</p>
                    <div class="grid grid-cols-3 gap-2.5">
                        <a href="{{ route('dashboard') }}"
                           class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-stone-800/50 border-slate-200/70 dark:border-stone-800 text-slate-700 dark:text-stone-200' }}">
                            <span class="w-9 h-9 rounded-xl bg-blue-600/10 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <i class="bi bi-grid-fill text-base"></i>
                            </span>
                            <span class="text-[11px] font-bold">Beranda</span>
                        </a>

                        <a href="{{ route('products.index') }}"
                           class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('products.*') && !request()->routeIs('products.scan') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-stone-800/50 border-slate-200/70 dark:border-stone-800 text-slate-700 dark:text-stone-200' }}">
                            <span class="w-9 h-9 rounded-xl bg-indigo-600/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <i class="bi bi-box-seam-fill text-base"></i>
                            </span>
                            <span class="text-[11px] font-bold">Barang</span>
                        </a>

                        <a href="{{ route('rooms.index') }}"
                           class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('rooms.*') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-stone-800/50 border-slate-200/70 dark:border-stone-800 text-slate-700 dark:text-stone-200' }}">
                            <span class="w-9 h-9 rounded-xl bg-emerald-600/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <i class="bi bi-building text-base"></i>
                            </span>
                            <span class="text-[11px] font-bold">Ruangan</span>
                        </a>

                        <a href="{{ route('categories.index') }}"
                           class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('categories.*') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-stone-800/50 border-slate-200/70 dark:border-stone-800 text-slate-700 dark:text-stone-200' }}">
                            <span class="w-9 h-9 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <i class="bi bi-tags-fill text-base"></i>
                            </span>
                            <span class="text-[11px] font-bold">Kategori</span>
                        </a>

                        <a href="{{ route('mutations.index') }}"
                           class="relative flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('mutations.*') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-stone-800/50 border-slate-200/70 dark:border-stone-800 text-slate-700 dark:text-slate-200' }}">
                            @if($mobileSheetPending > 0)
                                <span class="absolute top-2 right-2 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-500 text-white">{{ $mobileSheetPending }}</span>
                            @endif
                            <span class="w-9 h-9 rounded-xl bg-violet-600/10 dark:bg-violet-500/20 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                                <i class="bi bi-arrow-left-right text-base"></i>
                            </span>
                            <span class="text-[11px] font-bold">Mutasi</span>
                        </a>

                        <a href="{{ route('products.scan') }}"
                           class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('products.scan') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-stone-800/50 border-slate-200/70 dark:border-stone-800 text-slate-700 dark:text-stone-200' }}">
                            <span class="w-9 h-9 rounded-xl bg-cyan-600/10 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                                <i class="bi bi-upc-scan text-base"></i>
                            </span>
                            <span class="text-[11px] font-bold">Scan Barcode</span>
                        </a>

                        <a href="{{ route('notifications.index') }}"
                           class="relative flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('notifications.*') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-slate-800/50 border-slate-200/70 dark:border-slate-800 text-slate-700 dark:text-slate-200' }}">
                            @if($mobileSheetUnread > 0)
                                <span class="absolute top-2 right-2 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold bg-rose-600 text-white">{{ $mobileSheetUnread }}</span>
                            @endif
                            <span class="w-9 h-9 rounded-xl bg-rose-600/10 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                                <i class="bi bi-bell-fill text-base"></i>
                            </span>
                            <span class="text-[11px] font-bold">Notifikasi</span>
                        </a>

                        <a href="{{ route('settings') }}"
                           class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('settings') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-slate-800/50 border-slate-200/70 dark:border-slate-800 text-slate-700 dark:text-slate-200' }}">
                            <span class="w-9 h-9 rounded-xl bg-slate-600/10 dark:bg-slate-500/20 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                                <i class="bi bi-sliders text-base"></i>
                            </span>
                            <span class="text-[11px] font-bold">Pengaturan</span>
                        </a>

                        <a href="{{ route('profile') }}"
                           class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('profile') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-slate-800/50 border-slate-200/70 dark:border-slate-800 text-slate-700 dark:text-slate-200' }}">
                            <span class="w-9 h-9 rounded-xl bg-teal-600/10 dark:bg-teal-500/20 text-teal-600 dark:text-teal-400 flex items-center justify-center">
                                <i class="bi bi-person-circle text-base"></i>
                            </span>
                            <span class="text-[11px] font-bold">Profil</span>
                        </a>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('activity-logs.index') }}"
                               class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('activity-logs.*') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-slate-800/50 border-slate-200/70 dark:border-slate-800 text-slate-700 dark:text-slate-200' }}">
                                <span class="w-9 h-9 rounded-xl bg-sky-600/10 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                                    <i class="bi bi-clock-history text-base"></i>
                                </span>
                                <span class="text-[11px] font-bold">Log Audit</span>
                            </a>

                            <a href="{{ route('observability.index') }}"
                               class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('observability.*') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-slate-800/50 border-slate-200/70 dark:border-slate-800 text-slate-700 dark:text-slate-200' }}">
                                <span class="w-9 h-9 rounded-xl bg-emerald-600/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                    <i class="bi bi-activity text-base"></i>
                                </span>
                                <span class="text-[11px] font-bold">Observability</span>
                            </a>
                        @endif

                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('users.index') }}"
                               class="flex flex-col items-center justify-center gap-1.5 p-3 rounded-2xl border transition-all {{ request()->routeIs('users.*') ? 'bg-blue-50/90 dark:bg-blue-950/50 border-blue-300 dark:border-blue-800 text-blue-600 dark:text-blue-400' : 'bg-slate-50/80 dark:bg-slate-800/50 border-slate-200/70 dark:border-slate-800 text-slate-700 dark:text-slate-200' }}">
                                <span class="w-9 h-9 rounded-xl bg-fuchsia-600/10 dark:bg-fuchsia-500/20 text-fuchsia-600 dark:text-fuchsia-400 flex items-center justify-center">
                                    <i class="bi bi-people-fill text-base"></i>
                                </span>
                                <span class="text-[11px] font-bold">User</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Mobile/Tablet Install to Home Screen & Native Permissions Trigger --}}
                <button type="button"
                        id="openPwaInstallModalBtn"
                        class="w-full flex items-center justify-between gap-3 p-3 rounded-2xl bg-gradient-to-r from-blue-600/10 via-indigo-600/10 to-cyan-500/10 dark:from-amber-500/15 dark:via-orange-500/10 dark:to-blue-500/10 border border-blue-200/80 dark:border-stone-700 text-left transition-all cursor-pointer">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <span class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                            <i class="bi bi-phone-fill text-sm"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold text-slate-900 dark:text-stone-100 truncate">Install Aplikasi ke Home Screen</p>
                            <p class="text-[10px] text-slate-500 dark:text-stone-400 truncate">1-Click Install • Izin Kamera, GPS & Deep Link</p>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-xs text-blue-600 dark:text-amber-400 shrink-0"></i>
                </button>

                {{-- Bottom Actions: Theme Switch & Logout Side by Side --}}
                <div class="grid grid-cols-2 gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button"
                            id="darkModeToggleMobile"
                            class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200/70 dark:hover:bg-slate-700 transition-colors">
                        <i id="darkModeIconMobile" class="bi bi-moon-stars text-slate-500"></i>
                        <span id="darkModeTextMobile">Tema</span>
                    </button>

                    <button type="button"
                            data-modal-close
                            data-modal-target="logoutModal"
                            class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-950/60 transition-colors">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Keluar</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         GLOBAL BARCODE STICKER POPUP (DESKTOP) / HALF-SCREEN SHEET (MOBILE)
         ===================================================================== --}}
    <div id="globalBarcodeSheetModal"
         class="modal-container responsive-bottom-sheet fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div class="sheet-dialog bg-white dark:bg-[#0f1b38] border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in">
            {{-- Mobile Grab Handle --}}
            <div class="sheet-grab-handle w-11 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto mt-3 md:hidden"></div>

            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="bi bi-upc-scan text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Stiker Barcode</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Siap cetak langsung</p>
                    </div>
                </div>
                <button type="button" data-modal-close class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <div class="p-5 space-y-4">
                {{-- Sticker Card Preview --}}
                <div class="rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-700 bg-white text-slate-900 p-4 text-center shadow-inner">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-blue-700 border-b border-slate-200 pb-1.5 mb-2">
                        INVENTARIS DISKOMINFO KAB. GARUT
                    </p>
                    <p id="stickerPopupProductName" class="text-xs font-extrabold text-slate-900 truncate mb-2">
                        Barang Inventaris
                    </p>
                    <div class="flex justify-center py-1 bg-white">
                        <svg id="stickerPopupBarcodeSvg" class="max-w-full h-14"></svg>
                    </div>
                    <div class="mt-2 flex items-center justify-center gap-1.5">
                        <span id="stickerPopupBarcodeText" class="font-mono text-xs font-black tracking-wider text-slate-900">BRG-00001</span>
                        <button type="button"
                                id="stickerPopupCopyBtn"
                                data-copy="BRG-00001"
                                data-copy-label="Kode Barcode"
                                class="p-1 rounded-md text-slate-500 hover:text-blue-600 hover:bg-slate-100 transition-colors"
                                title="Salin Kode">
                            <i class="bi bi-copy text-xs"></i>
                        </button>
                    </div>
                    <p id="stickerPopupMetaText" class="text-[10px] font-medium text-slate-500 mt-1 truncate">
                        Inventaris Aset • Diskominfo Garut
                    </p>
                </div>

                <div class="flex items-center justify-between gap-3">
                    <label for="stickerCopiesSelect" class="text-xs font-bold text-slate-700 dark:text-slate-300 shrink-0">
                        Jumlah Stiker:
                    </label>
                    <div class="w-40">
                        <select id="stickerCopiesSelect" data-native-select="true" class="w-full px-3 py-2 text-xs font-bold bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-100">
                            <option value="1">1 Stiker</option>
                            <option value="2">2 Stiker</option>
                            <option value="4" selected>4 Stiker</option>
                            <option value="8">8 Stiker</option>
                            <option value="12">12 Stiker</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 pt-1">
                    <a id="stickerPopupOpenPrintUrl"
                       href="#"
                       target="_blank"
                       class="px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200/70 dark:hover:bg-slate-700 transition-colors inline-flex items-center gap-1.5">
                        <i class="bi bi-arrows-fullscreen"></i>
                        <span>Penuh</span>
                    </a>
                    <button type="button"
                            id="stickerPopupPrintNowBtn"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20 transition-all">
                        <i class="bi bi-printer-fill"></i>
                        <span>Cetak</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         GLOBAL DEEP-LINK SHARE MODAL / MOBILE BOTTOM SHEET
         ===================================================================== --}}
    <div id="globalShareSheetModal"
         class="modal-container responsive-bottom-sheet fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
        <div class="sheet-dialog bg-white dark:bg-[#0f1b38] border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in">
            <div class="sheet-grab-handle w-11 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700 mx-auto mt-3 md:hidden"></div>

            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                        <i class="bi bi-share-fill text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Bagikan Tautan</h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Deep link pendek dengan pratinjau</p>
                    </div>
                </div>
                <button type="button" data-modal-close class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <div class="p-5 space-y-4">
                {{-- Thumbnail & Description Preview Card --}}
                <div class="flex items-start gap-3.5 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80">
                    <img id="shareModalThumbnail"
                         src="{{ asset('images/logo-diskominfo.png') }}"
                         alt="Thumbnail Preview"
                         class="w-14 h-14 rounded-xl object-cover bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shrink-0 p-1">
                    <div class="min-w-0 flex-1">
                        <span class="inline-block px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 mb-1">
                            Diskominfo Kab. Garut
                        </span>
                        <h4 id="shareModalTitle" class="text-xs font-extrabold text-slate-900 dark:text-white line-clamp-1">
                            Inventaris Diskominfo Garut
                        </h4>
                        <p id="shareModalDescription" class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-2 mt-0.5 leading-relaxed">
                            Sistem Informasi Inventarisasi Barang & Aset Diskominfo Kabupaten Garut.
                        </p>
                    </div>
                </div>

                {{-- Short Link Input + Copy --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">
                        Tautan Pendek:
                    </label>
                    <div class="flex items-center gap-2">
                        <input id="shareModalShortUrlInput"
                               type="text"
                               readonly
                               value="{{ url('/s/' . $resolvedShareCode) }}"
                               class="flex-1 px-3.5 py-2.5 rounded-xl text-xs font-mono font-bold bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-100 focus:outline-none">
                        <button type="button"
                                id="shareModalCopyBtn"
                                data-copy="{{ url('/s/' . $resolvedShareCode) }}"
                                data-copy-label="Tautan Deep Link"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-sm transition-all shrink-0">
                            <i class="bi bi-copy"></i>
                            <span>Salin</span>
                        </button>
                    </div>
                </div>

                {{-- Quick Share Channels --}}
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <a id="shareModalWhatsappBtn"
                       href="#"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/15 hover:bg-emerald-100 dark:hover:bg-emerald-500/25 border border-emerald-200/70 dark:border-emerald-800/60 transition-all">
                        <i class="bi bi-whatsapp text-sm"></i>
                        <span>WhatsApp</span>
                    </a>
                    <a id="shareModalTelegramBtn"
                       href="#"
                       target="_blank"
                       rel="noopener"
                       class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs font-bold text-sky-700 dark:text-sky-300 bg-sky-50 dark:bg-sky-500/15 hover:bg-sky-100 dark:hover:bg-sky-500/25 border border-sky-200/70 dark:border-sky-800/60 transition-all">
                        <i class="bi bi-telegram text-sm"></i>
                        <span>Telegram</span>
                    </a>
                </div>

                <button type="button"
                        id="shareModalNativeBtn"
                        class="hidden w-full items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 transition-all">
                    <i class="bi bi-phone"></i>
                    <span>Bagikan ke Aplikasi Lain</span>
                </button>
            </div>
        </div>
    </div>

    {{-- =====================================================================
         MOBILE & TABLET HOME SCREEN INSTALL REMINDER + NATIVE PERMISSIONS MODAL
         ===================================================================== --}}
    <div id="mobilePwaInstallPromptModal"
         class="modal-container responsive-bottom-sheet fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
         role="dialog"
         aria-modal="true"
         aria-labelledby="pwaInstallModalTitle">
        <div class="sheet-dialog bg-white dark:bg-[#1c1917] border border-slate-200 dark:border-stone-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-fade-in">
            <div class="sheet-grab-handle w-11 h-1.5 rounded-full bg-slate-300 dark:bg-stone-700 mx-auto mt-3 md:hidden"></div>

            <div class="px-5 py-4 border-b border-slate-100 dark:border-stone-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-diskominfo.png') }}"
                         alt="Logo Diskominfo"
                         class="w-10 h-10 rounded-2xl object-contain bg-blue-50 dark:bg-stone-800 p-1.5 border border-blue-100 dark:border-stone-700 shrink-0">
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h3 id="pwaInstallModalTitle" class="text-sm font-extrabold text-slate-900 dark:text-stone-100">Install ke Home Screen</h3>
                            <span class="px-1.5 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider bg-blue-100 dark:bg-amber-500/20 text-blue-700 dark:text-amber-300">PWA Native</span>
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-stone-400">Pengalaman aplikasi penuh di Mobile & Tablet</p>
                    </div>
                </div>
                <button type="button"
                        data-pwa-close
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-stone-200 hover:bg-slate-100 dark:hover:bg-stone-800 cursor-pointer">
                    <i class="bi bi-x-lg text-xs"></i>
                </button>
            </div>

            <div class="p-5 space-y-4 max-h-[78vh] overflow-y-auto">
                <p class="text-xs text-slate-600 dark:text-stone-300 leading-relaxed">
                    Pasang <strong>SIM-Inventaris Diskominfo Garut</strong> di layar utama perangkat Anda untuk akses 1-tap, pemindaian barcode layar penuh, serta pembukaan tautan otomatis layaknya aplikasi native.
                </p>

                {{-- Native Hardware & Deep-Link Permissions Checklist --}}
                <div class="rounded-2xl bg-slate-50 dark:bg-[#26221e] border border-slate-200/80 dark:border-stone-700/80 p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-stone-400">Izin Perangkat & Deep Link</span>
                        <button type="button"
                                id="pwaRequestPermissionsBtn"
                                class="text-[11px] font-extrabold text-blue-600 dark:text-amber-400 hover:underline cursor-pointer">
                            Aktifkan Semua Izin
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-2">
                        <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-white dark:bg-[#1c1917] border border-slate-200/70 dark:border-stone-800">
                            <div class="flex items-center gap-2.5">
                                <i class="bi bi-camera-video-fill text-blue-600 dark:text-amber-400 text-sm"></i>
                                <div>
                                    <p class="text-xs font-bold text-slate-800 dark:text-stone-100">Kamera (Scan Barcode)</p>
                                    <p class="text-[10px] text-slate-500 dark:text-stone-400">Akses pemindaian QR/Barcode aset</p>
                                </div>
                            </div>
                            <span id="pwaPermCameraBadge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-stone-800 text-slate-600 dark:text-stone-300">Siap Diizinkan</span>
                        </div>

                        <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-white dark:bg-[#1c1917] border border-slate-200/70 dark:border-stone-800">
                            <div class="flex items-center gap-2.5">
                                <i class="bi bi-geo-alt-fill text-emerald-600 dark:text-emerald-400 text-sm"></i>
                                <div>
                                    <p class="text-xs font-bold text-slate-800 dark:text-stone-100">Koordinat GPS (Geolocation)</p>
                                    <p class="text-[10px] text-slate-500 dark:text-stone-400">Validasi titik koordinat ruangan & mutasi</p>
                                </div>
                            </div>
                            <span id="pwaPermLocationBadge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-100 dark:bg-stone-800 text-slate-600 dark:text-stone-300">Siap Diizinkan</span>
                        </div>

                        <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-white dark:bg-[#1c1917] border border-slate-200/70 dark:border-stone-800">
                            <div class="flex items-center gap-2.5">
                                <i class="bi bi-link-45deg text-indigo-600 dark:text-indigo-400 text-base"></i>
                                <div>
                                    <p class="text-xs font-bold text-slate-800 dark:text-stone-100">Deep Link Otomatis</p>
                                    <p class="text-[10px] text-slate-500 dark:text-stone-400">Buka tautan <code class="font-mono">/s/*</code> langsung di aplikasi</p>
                                </div>
                            </div>
                            <span id="pwaPermDeeplinkBadge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-300">Aktif</span>
                        </div>
                    </div>
                </div>

                {{-- 1-Click Automatic Install Button --}}
                <button type="button"
                        id="pwaOneClickInstallBtn"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-2xl text-xs font-extrabold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-600/25 transition-all cursor-pointer">
                    <i class="bi bi-download text-sm"></i>
                    <span>Install 1-Klik ke Home Screen</span>
                </button>

                {{-- Smart LAN HTTPS Switcher & Root CA Certificate Helper (Auto-shown on LAN HTTP / LAN IP) --}}
                <div id="pwaLanHttpsHelperBox" class="hidden rounded-2xl bg-indigo-50/90 dark:bg-indigo-950/35 border border-indigo-200/80 dark:border-indigo-800/60 p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5 text-indigo-800 dark:text-indigo-300 font-extrabold text-xs">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>Mode Jaringan Lokal (LAN HTTPS)</span>
                        </div>
                        <span id="pwaLanProtocolBadge" class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-indigo-600/15 text-indigo-700 dark:text-indigo-300">LAN PWA</span>
                    </div>
                    <p class="text-[11px] text-indigo-900/90 dark:text-stone-300 leading-relaxed">
                        Chrome Mobile mewajibkan <strong>HTTPS</strong> di jaringan lokal (<code id="pwaLanIpLabel" class="font-mono font-bold">LAN</code>) agar tombol <strong>Install Aplikasi (PWA)</strong> &amp; kamera scanner aktif.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-0.5">
                        <a id="pwaSwitchToLanHttpsBtn"
                           href="#"
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-[11px] font-extrabold text-white bg-indigo-600 hover:bg-indigo-700 shadow-xs transition-all">
                            <i class="bi bi-lock-fill"></i>
                            <span>Buka Mode HTTPS (:8443)</span>
                        </a>
                        <a id="pwaDownloadLanCaBtn"
                           href="/lan-ca.crt"
                           download="Inventaris-Diskominfo-LAN-CA.crt"
                           class="inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl text-[11px] font-bold text-indigo-700 dark:text-indigo-200 bg-white dark:bg-stone-800 border border-indigo-200 dark:border-indigo-700 hover:bg-indigo-50 transition-all">
                            <i class="bi bi-patch-check-fill"></i>
                            <span>Unduh Sertifikat CA</span>
                        </a>
                    </div>
                    <p class="text-[10px] text-indigo-800/75 dark:text-stone-400 leading-snug">
                        Tips: Jika Chrome menampilkan peringatan sertifikat lokal pada port <code>:8443</code>, pilih <strong>Lanjutan (Advanced) &rarr; Lanjutkan</strong> atau pasang <em>Sertifikat CA</em> di Pengaturan Keamanan HP.
                    </p>
                </div>

                {{-- Manual Install Guide Fallback (iOS Safari / iPadOS / Browser Tanpa Auto-Prompt) --}}
                <div id="pwaManualInstallGuideBox" class="hidden rounded-2xl bg-amber-50/90 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/60 p-3.5 space-y-2">
                    <div class="flex items-center gap-2 text-amber-800 dark:text-amber-300 font-extrabold text-xs">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>Panduan Install Manual ke Home Screen:</span>
                    </div>
                    <ol class="text-[11px] text-amber-900/90 dark:text-stone-300 space-y-1.5 list-decimal list-inside leading-relaxed">
                        <li><strong>iPhone / iPad (Safari):</strong> Ketuk ikon <strong>Bagikan (Share <i class="bi bi-box-arrow-up"></i>)</strong> di bilah browser, lalu pilih <strong>"Tambah ke Layar Utama" (Add to Home Screen)</strong>.</li>
                        <li><strong>Android (Chrome / Edge / Samsung):</strong> Buka melalui <strong>Mode HTTPS (:8443)</strong> di atas, lalu ketuk menu <strong>titik tiga (⋮)</strong> &rarr; <strong>"Instal Aplikasi"</strong> atau <strong>"Tambahkan ke Layar Utama"</strong>.</li>
                    </ol>
                </div>

                <div class="flex items-center justify-between gap-2 pt-1">
                    <button type="button"
                            id="pwaToggleManualGuideBtn"
                            class="text-[11px] font-bold text-slate-500 dark:text-stone-400 hover:text-blue-600 dark:hover:text-amber-400 cursor-pointer">
                        Lihat Panduan Manual
                    </button>
                    <button type="button"
                            id="pwaDismissInstallBtn"
                            data-pwa-close
                            class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-stone-300 bg-slate-100 dark:bg-stone-800 hover:bg-slate-200 transition-colors cursor-pointer">
                        Nanti Saja
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Logout Confirmation Modal --}}
    <x-logout-modal />

    @stack('scripts')
</body>
</html>
