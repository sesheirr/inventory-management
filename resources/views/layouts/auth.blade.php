<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="fwzann-abd (https://github.com/fwzann-abd)">
    <meta name="co-collaborator" content="msayib (https://github.com/msayib) — Ideation, System Design, Architecture, Performance, Refactor, Tune-Up, UI/UX & Mobile-First High-Level Engineering">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') - Inventaris Diskominfo</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    <script>
        (function () {
            function applyAuthTheme() {
                const stored = localStorage.getItem('theme') || localStorage.getItem('color-theme') || 'system';
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const isDark = stored === 'dark' || (stored === 'system' && prefersDark);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
                document.documentElement.dataset.themeMode = stored;
            }
            applyAuthTheme();
            window.__applyAuthTheme = applyAuthTheme;
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
                const stored = localStorage.getItem('theme') || localStorage.getItem('color-theme') || 'system';
                if (stored === 'system') {
                    applyAuthTheme();
                }
            });
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/app.tsx'])
    @yield('extra-css')
</head>
<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased selection:bg-blue-600 selection:text-white min-h-[100dvh]">
    {{-- Top-Right Pinned Collapsible Theme Switcher (Desktop & Mobile) --}}
    <div id="authThemeSwitcherRoot" class="fixed top-3.5 right-3.5 sm:top-5 sm:right-5 z-50">
        <div class="relative">
            <button type="button"
                    id="authThemeCollapsibleBtn"
                    aria-expanded="false"
                    aria-haspopup="true"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 shadow-md shadow-slate-900/5 dark:shadow-black/40 backdrop-blur-xl text-xs font-bold text-slate-700 dark:text-slate-200 hover:border-blue-500/40 transition-all active:scale-95 cursor-pointer">
                <i id="authThemeCurrentIcon" class="bi bi-sun-fill text-amber-500 text-xs"></i>
                <span id="authThemeCurrentLabel">Terang</span>
                <i id="authThemeChevron" class="bi bi-chevron-down text-[10px] text-slate-400 transition-transform duration-200"></i>
            </button>

            <div id="authThemeCollapsibleMenu"
                 role="menu"
                 aria-label="Pilih Tema Tampilan"
                 class="hidden absolute right-0 mt-2 w-40 rounded-2xl bg-white/95 dark:bg-slate-900/95 border border-slate-200/90 dark:border-slate-800 shadow-xl shadow-slate-900/10 dark:shadow-black/50 backdrop-blur-xl p-1.5 space-y-0.5 origin-top-right">
                <button type="button"
                        data-auth-theme-btn="light"
                        class="w-full px-2.5 py-1.5 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 transition-all cursor-pointer text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/70">
                    <span class="inline-flex items-center gap-2">
                        <i class="bi bi-sun-fill text-amber-500"></i>
                        <span>Terang</span>
                    </span>
                    <i data-auth-theme-check="light" class="bi bi-check2 text-blue-600 dark:text-blue-400 hidden"></i>
                </button>
                <button type="button"
                        data-auth-theme-btn="dark"
                        class="w-full px-2.5 py-1.5 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 transition-all cursor-pointer text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/70">
                    <span class="inline-flex items-center gap-2">
                        <i class="bi bi-moon-stars-fill text-indigo-500 dark:text-indigo-400"></i>
                        <span>Gelap</span>
                    </span>
                    <i data-auth-theme-check="dark" class="bi bi-check2 text-blue-600 dark:text-blue-400 hidden"></i>
                </button>
                <button type="button"
                        data-auth-theme-btn="system"
                        class="w-full px-2.5 py-1.5 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 transition-all cursor-pointer text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/70">
                    <span class="inline-flex items-center gap-2">
                        <i class="bi bi-laptop text-blue-500"></i>
                        <span>Sistem</span>
                    </span>
                    <i data-auth-theme-check="system" class="bi bi-check2 text-blue-600 dark:text-blue-400 hidden"></i>
                </button>
            </div>
        </div>
    </div>

    @yield('content')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const triggerBtn = document.getElementById('authThemeCollapsibleBtn');
            const menuEl = document.getElementById('authThemeCollapsibleMenu');
            const iconEl = document.getElementById('authThemeCurrentIcon');
            const labelEl = document.getElementById('authThemeCurrentLabel');
            const chevronEl = document.getElementById('authThemeChevron');
            const themeBtns = document.querySelectorAll('[data-auth-theme-btn]');

            const themeMeta = {
                light: { label: 'Terang', icon: 'bi bi-sun-fill text-amber-500 text-xs' },
                dark: { label: 'Gelap', icon: 'bi bi-moon-stars-fill text-indigo-400 text-xs' },
                system: { label: 'Sistem', icon: 'bi bi-laptop text-blue-500 text-xs' },
            };

            function syncThemeUI() {
                const current = localStorage.getItem('theme') || localStorage.getItem('color-theme') || 'system';
                const meta = themeMeta[current] || themeMeta.system;
                if (iconEl) iconEl.className = meta.icon;
                if (labelEl) labelEl.textContent = meta.label;

                themeBtns.forEach(function (btn) {
                    const mode = btn.getAttribute('data-auth-theme-btn');
                    const isActive = mode === current;
                    btn.className = isActive
                        ? 'w-full px-2.5 py-1.5 rounded-xl text-xs font-bold flex items-center justify-between gap-2 transition-all cursor-pointer bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300'
                        : 'w-full px-2.5 py-1.5 rounded-xl text-xs font-semibold flex items-center justify-between gap-2 transition-all cursor-pointer text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800/70';
                    const checkIcon = btn.querySelector('[data-auth-theme-check]');
                    if (checkIcon) checkIcon.classList.toggle('hidden', !isActive);
                });
            }

            function toggleMenu(forceOpen) {
                if (!menuEl || !triggerBtn) return;
                const willOpen = typeof forceOpen === 'boolean' ? forceOpen : menuEl.classList.contains('hidden');
                triggerBtn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                if (chevronEl) chevronEl.classList.toggle('rotate-180', willOpen);

                if (typeof window.animateCollapsiblePanel === 'function') {
                    window.animateCollapsiblePanel(menuEl, willOpen);
                } else {
                    menuEl.classList.toggle('hidden', !willOpen);
                }
            }

            triggerBtn?.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleMenu();
            });

            themeBtns.forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    const mode = btn.getAttribute('data-auth-theme-btn') || 'system';
                    localStorage.setItem('theme', mode);
                    localStorage.setItem('color-theme', mode);

                    const commit = function () {
                        if (typeof window.__applyAuthTheme === 'function') {
                            window.__applyAuthTheme();
                        }
                        syncThemeUI();
                    };

                    if (typeof document.startViewTransition === 'function') {
                        document.documentElement.setAttribute('data-theme-switching', '1');
                        const vt = document.startViewTransition(commit);
                        vt.finished.finally(function () {
                            document.documentElement.removeAttribute('data-theme-switching');
                        });
                    } else {
                        commit();
                    }

                    toggleMenu(false);
                });
            });

            document.addEventListener('click', function (e) {
                if (menuEl && !menuEl.classList.contains('hidden') && !e.target.closest('#authThemeSwitcherRoot')) {
                    toggleMenu(false);
                }
            });

            syncThemeUI();
        });
    </script>
    @yield('extra-js')
</body>
</html>
