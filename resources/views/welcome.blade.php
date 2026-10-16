<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="fwzann-abd (https://github.com/fwzann-abd)">
    <meta name="co-collaborator" content="msayib (https://github.com/msayib) — Ideation, System Design, Architecture, Performance, Refactor, Tune-Up, UI/UX & Mobile-First High-Level Engineering">
    <meta name="description" content="Sistem Informasi Manajemen Inventaris, Pemetaan Ruangan, dan Pelacakan Mutasi Aset TIK Dinas Komunikasi dan Informatika Kabupaten Garut.">
    <title>SIM-Inventaris TIK — Diskominfo Kabupaten Garut</title>

    <script>
        (function() {
            var theme = localStorage.getItem('color-theme') || 'system';
            var isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                document.documentElement.classList.add('dark');
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F8FAFC] dark:bg-[#090D16] text-slate-900 dark:text-slate-100 antialiased selection:bg-blue-600 selection:text-white transition-colors duration-300 overflow-x-hidden">

    <!-- Subtle Technical Grid Backdrop + Slow-Drifting Random Color-Morphing Ambient Orbs -->
    <div id="landingAmbientBackdrop" class="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute inset-0 opacity-[0.035] dark:opacity-[0.055]" style="background-image: linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px); background-size: 40px 40px;"></div>
        <div data-ambient-orb="a" class="landing-ambient-orb-a absolute -top-40 right-1/5 w-[38rem] h-[38rem] rounded-full bg-blue-600/10 dark:bg-blue-500/14 blur-3xl"></div>
        <div data-ambient-orb="b" class="landing-ambient-orb-b absolute bottom-0 left-8 w-[32rem] h-[32rem] rounded-full bg-cyan-500/8 dark:bg-sky-500/12 blur-3xl"></div>
        <div data-ambient-orb="c" class="landing-ambient-orb-c absolute top-1/3 left-1/2 -translate-x-1/2 w-[30rem] h-[30rem] rounded-full bg-indigo-500/7 dark:bg-violet-500/10 blur-3xl"></div>
    </div>

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-50 border-b border-slate-200/80 dark:border-slate-800/80 bg-white/85 dark:bg-[#090D16]/85 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-18 flex items-center justify-between gap-3">
            <!-- Official Brand Identity -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group focus:outline-none">
                <div class="w-10 h-10 rounded-xl bg-slate-900 dark:bg-blue-600 text-white flex items-center justify-center shadow-sm group-hover:scale-[1.03] transition-transform">
                    <i class="bi bi-hdd-network-fill text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm sm:text-base font-extrabold tracking-tight text-slate-900 dark:text-white">SIM-INVENTARIS</span>
                        <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-semibold uppercase tracking-wider bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200/70 dark:border-blue-800/70">TIK v2.6</span>
                    </div>
                    <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 leading-none mt-0.5">Diskominfo Kabupaten Garut</p>
                </div>
            </a>

            <!-- Desktop Navigation Links -->
            <nav class="hidden md:flex items-center gap-7 text-xs font-semibold text-slate-600 dark:text-slate-300">
                <a href="#kapabilitas" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Kapabilitas Sistem</a>
                <a href="#alur-mutasi" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Alur Mutasi & Audit</a>
                <a href="#bidang-unit" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Cakupan Ruang & Unit</a>
            </nav>

            <!-- Right Actions: Collapsible Theme Switcher + CTA -->
            <div class="flex items-center gap-2.5">
                <div class="relative" id="landingThemeSwitcherRoot">
                    <button type="button" id="landingThemeBtn"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-slate-100/90 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-700/80 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-blue-500/40 transition-all cursor-pointer"
                        aria-label="Pilih tema tampilan" aria-expanded="false">
                        <i id="landingThemeIcon" class="bi bi-circle-half text-blue-600 dark:text-blue-400 text-xs"></i>
                        <span id="landingThemeLabel" class="hidden sm:inline text-[11px]">Sistem</span>
                        <i class="bi bi-chevron-down text-[9px] text-slate-400 transition-transform duration-200" id="landingThemeCaret"></i>
                    </button>
                    <div id="landingThemeMenu"
                        class="hidden absolute right-0 mt-2 w-36 p-1 rounded-xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border border-slate-200/90 dark:border-slate-700/90 shadow-xl origin-top-right z-50">
                        <button type="button" data-landing-theme="light" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                            <span class="flex items-center gap-2"><i class="bi bi-sun-fill text-amber-500"></i> Terang</span>
                            <i class="bi bi-check2 text-blue-600 dark:text-blue-400 opacity-0" data-theme-check="light"></i>
                        </button>
                        <button type="button" data-landing-theme="dark" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                            <span class="flex items-center gap-2"><i class="bi bi-moon-stars-fill text-indigo-400"></i> Gelap</span>
                            <i class="bi bi-check2 text-blue-600 dark:text-blue-400 opacity-0" data-theme-check="dark"></i>
                        </button>
                        <button type="button" data-landing-theme="system" class="w-full flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                            <span class="flex items-center gap-2"><i class="bi bi-display text-sky-500"></i> Sistem</span>
                            <i class="bi bi-check2 text-blue-600 dark:text-blue-400 opacity-0" data-theme-check="system"></i>
                        </button>
                    </div>
                </div>

                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-sm hover:shadow transition-all">
                        <span>Buka Dashboard</span>
                        <i class="bi bi-arrow-right text-xs"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 dark:bg-blue-600 hover:bg-slate-800 dark:hover:bg-blue-500 text-white text-xs sm:text-sm font-bold shadow-sm transition-all">
                        <i class="bi bi-box-arrow-in-right text-xs"></i>
                        <span>Masuk Sistem</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10">
        <!-- Asymmetric Split Hero Section -->
        <section class="min-h-[calc(100dvh-4.25rem)] flex items-center py-10 sm:py-16 lg:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
                    
                    <!-- Left Column: Institutional Purpose & Primary Actions (7 cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        <div class="inline-flex items-center gap-2.5 px-3 py-1.5 rounded-full bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[11px] font-mono font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                Portal Tata Kelola Aset &amp; Logistik TIK Pemerintah Daerah
                            </span>
                        </div>

                        <h1 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-extrabold tracking-tight text-slate-900 dark:text-white leading-[1.14]">
                            Kendali Penuh Inventaris, Lokasi Ruangan, dan Mutasi Aset
                            <span class="text-blue-600 dark:text-blue-400">Diskominfo Kabupaten Garut.</span>
                        </h1>

                        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed max-w-2xl">
                            Platform terpusat untuk pencatatan perangkat jaringan NOC, komputer operasional, peralatan siaran IKP, hingga perangkat keamanan siber. Dilengkapi kode batang terstandarisasi, persetujuan mutasi berjenjang, dan pengingat batas waktu peminjaman otomatis.
                        </p>

                        <!-- Primary & Secondary CTAs -->
                        <div class="flex flex-wrap items-center gap-3 pt-1">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2.5 px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-600/15 transition-all active:scale-[0.99]">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>Lanjutkan ke Dashboard Operasional</span>
                                </a>
                                <a href="{{ route('products.scan') }}" class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-800 text-sm font-semibold transition-all">
                                    <i class="bi bi-upc-scan text-blue-600 dark:text-blue-400"></i>
                                    <span>Pindai Barcode Barang</span>
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center gap-2.5 px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold shadow-md shadow-blue-600/15 transition-all active:scale-[0.99]">
                                    <i class="bi bi-shield-lock-fill"></i>
                                    <span>Masuk ke Portal Inventaris</span>
                                </a>
                                <a href="#kapabilitas" class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-800 text-sm font-semibold transition-all">
                                    <span>Pelajari Alur Kerja</span>
                                    <i class="bi bi-arrow-down-short text-base"></i>
                                </a>
                            @endauth
                        </div>

                        <!-- Live Database Telemetry Strip -->
                        <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800/80 grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                            <div class="p-3.5 rounded-xl bg-white/90 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-800/80">
                                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Jenis Aset</div>
                                <div class="mt-1 flex items-baseline gap-1.5">
                                    <span class="text-xl sm:text-2xl font-extrabold font-mono text-slate-900 dark:text-white">{{ number_format($metrics['products'] ?? 20) }}</span>
                                    <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400">SKU</span>
                                </div>
                            </div>
                            <div class="p-3.5 rounded-xl bg-white/90 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-800/80">
                                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Unit Tersedia</div>
                                <div class="mt-1 flex items-baseline gap-1.5">
                                    <span class="text-xl sm:text-2xl font-extrabold font-mono text-slate-900 dark:text-white">{{ number_format($metrics['total_stock'] ?? 142) }}</span>
                                    <span class="text-[11px] font-medium text-blue-600 dark:text-blue-400">Unit</span>
                                </div>
                            </div>
                            <div class="p-3.5 rounded-xl bg-white/90 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-800/80">
                                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Ruang &amp; Bidang</div>
                                <div class="mt-1 flex items-baseline gap-1.5">
                                    <span class="text-xl sm:text-2xl font-extrabold font-mono text-slate-900 dark:text-white">{{ number_format($metrics['rooms'] ?? 8) }}</span>
                                    <span class="text-[11px] font-medium text-slate-500">Lokasi</span>
                                </div>
                            </div>
                            <div class="p-3.5 rounded-xl bg-white/90 dark:bg-slate-900/80 border border-slate-200/70 dark:border-slate-800/80">
                                <div class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Log Mutasi</div>
                                <div class="mt-1 flex items-baseline gap-1.5">
                                    <span class="text-xl sm:text-2xl font-extrabold font-mono text-slate-900 dark:text-white">{{ number_format($metrics['mutations'] ?? 15) }}</span>
                                    <span class="text-[11px] font-medium text-amber-600 dark:text-amber-400">Transaksi</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Curated NOC Visual + Live Asset Manifest Card (5 cols) -->
                    <div class="lg:col-span-5">
                        <div class="relative rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl overflow-hidden">
                            <!-- Curated Real Data Center / Fiber Optic Network Photography (Unsplash) -->
                            <div class="relative h-56 sm:h-64 overflow-hidden bg-slate-950">
                                <img
                                    src="https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=1200&q=85"
                                    alt="Infrastruktur Ruang Server dan Jaringan Fiber Optik NOC Diskominfo"
                                    class="w-full h-full object-cover object-center opacity-75 scale-105 transition-transform duration-700 hover:scale-100"
                                    loading="eager"
                                    onerror="this.style.display='none'"
                                >
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/45 to-transparent"></div>

                                <!-- Top Overlay Badge -->
                                <div class="absolute top-3.5 left-3.5 right-3.5 flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-950/80 backdrop-blur-md border border-slate-700/80 text-[11px] font-mono font-semibold text-slate-200">
                                        <i class="bi bi-hdd-rack text-blue-400"></i>
                                        NOC DATA CENTER LT. 2
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-950/85 backdrop-blur-md border border-emerald-700/70 text-[10px] font-mono font-bold text-emerald-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        TERVERIFIKASI
                                    </span>
                                </div>

                                <!-- Bottom Caption inside Photo -->
                                <div class="absolute bottom-3 left-4 right-4 flex items-end justify-between text-white">
                                    <div>
                                        <p class="text-[10px] font-mono uppercase tracking-widest text-blue-300">Sampel Kartu Aset Digital</p>
                                        <h2 class="text-sm sm:text-base font-bold tracking-tight">Router Core MikroTik CCR2116-12G-4S+</h2>
                                    </div>
                                    <span class="text-xs font-mono font-bold px-2 py-0.5 rounded bg-white/10 border border-white/15">BRG-000001</span>
                                </div>
                            </div>

                            <!-- Interactive Asset Telemetry & Shipment Manifest Preview -->
                            <div class="p-4 sm:p-5 bg-slate-950 text-slate-200 space-y-4">
                                <!-- Route / Placement Flow -->
                                <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <span class="text-[10px] font-mono uppercase text-slate-400 block">Lokasi Penempatan</span>
                                        <span class="text-xs font-bold text-white truncate block">Ruang Server &amp; NOC Lt. 2</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-blue-400 shrink-0 px-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                        <span class="w-6 border-t border-dashed border-blue-400/60"></span>
                                        <i class="bi bi-shield-check text-xs"></i>
                                    </div>
                                    <div class="text-right min-w-0">
                                        <span class="text-[10px] font-mono uppercase text-slate-400 block">Penanggung Jawab</span>
                                        <span class="text-xs font-semibold text-slate-200 truncate block">Hendra Wijaya, M.T.</span>
                                    </div>
                                </div>

                                <!-- Barcode & Checksum Row -->
                                <div class="flex items-center justify-between gap-3 pt-1">
                                    <div class="space-y-0.5">
                                        <div class="text-[10px] font-mono text-slate-400">KODE INVENTARIS DAERAH</div>
                                        <div class="text-xs font-mono font-bold text-blue-400">DKI-GRT-2026-001</div>
                                    </div>
                                    <!-- Crisp Vector Barcode Graphic -->
                                    <svg class="h-8 w-32 text-slate-200" viewBox="0 0 160 36" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Ilustrasi Barcode Inventaris">
                                        <rect x="2" y="2" width="3" height="32" fill="currentColor"/>
                                        <rect x="8" y="2" width="1.5" height="32" fill="currentColor"/>
                                        <rect x="13" y="2" width="4" height="32" fill="currentColor"/>
                                        <rect x="20" y="2" width="2" height="32" fill="currentColor"/>
                                        <rect x="25" y="2" width="1.5" height="32" fill="currentColor"/>
                                        <rect x="30" y="2" width="5" height="32" fill="currentColor"/>
                                        <rect x="38" y="2" width="2" height="32" fill="currentColor"/>
                                        <rect x="43" y="2" width="3" height="32" fill="currentColor"/>
                                        <rect x="50" y="2" width="1.5" height="32" fill="currentColor"/>
                                        <rect x="55" y="2" width="4" height="32" fill="currentColor"/>
                                        <rect x="63" y="2" width="2" height="32" fill="currentColor"/>
                                        <rect x="68" y="2" width="3.5" height="32" fill="currentColor"/>
                                        <rect x="75" y="2" width="1.5" height="32" fill="currentColor"/>
                                        <rect x="80" y="2" width="4" height="32" fill="currentColor"/>
                                        <rect x="88" y="2" width="2" height="32" fill="currentColor"/>
                                        <rect x="93" y="2" width="5" height="32" fill="currentColor"/>
                                        <rect x="101" y="2" width="1.5" height="32" fill="currentColor"/>
                                        <rect x="106" y="2" width="3" height="32" fill="currentColor"/>
                                        <rect x="113" y="2" width="2" height="32" fill="currentColor"/>
                                        <rect x="118" y="2" width="4" height="32" fill="currentColor"/>
                                        <rect x="126" y="2" width="1.5" height="32" fill="currentColor"/>
                                        <rect x="131" y="2" width="3" height="32" fill="currentColor"/>
                                        <rect x="138" y="2" width="2" height="32" fill="currentColor"/>
                                        <rect x="143" y="2" width="4.5" height="32" fill="currentColor"/>
                                        <rect x="151" y="2" width="2" height="32" fill="currentColor"/>
                                        <rect x="156" y="2" width="3" height="32" fill="currentColor"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Bento Capabilities Grid -->
        <section id="kapabilitas" class="py-14 sm:py-20 border-t border-slate-200/80 dark:border-slate-800/80 bg-white/60 dark:bg-slate-900/35">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-2xl">
                    <span class="text-xs font-mono font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">Arsitektur &amp; Modul Inti</span>
                    <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Dirancang untuk Ketertiban Administrasi Barang Milik Daerah
                    </h2>
                    <p class="mt-2.5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                        Setiap fitur meminimalkan selisih stok fisik dan mempercepat proses verifikasi peminjaman maupun pemindahan perangkat antar bidang.
                    </p>
                </div>

                <div class="mt-10 grid grid-cols-1 md:grid-cols-12 gap-5">
                    <!-- Feature 1: 7 cols -->
                    <div class="md:col-span-7 p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/80 text-blue-600 dark:text-blue-400 flex items-center justify-center mb-4">
                                <i class="bi bi-arrow-left-right text-lg"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Manajemen Mutasi &amp; Peminjaman Berjenjang</h3>
                            <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                Mendukung dua skema operasional: <strong>Pindah Permanen</strong> antar ruangan dan <strong>Peminjaman Sementara</strong> dengan tanggal jatuh tempo pengembalian. Setiap pengajuan staf memerlukan persetujuan Administrator sebelum stok dipindahkan.
                            </p>
                        </div>
                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex flex-wrap items-center gap-2 text-[11px] font-mono text-slate-500 dark:text-slate-400">
                            <span class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-800">1. Pengajuan Staf</span>
                            <i class="bi bi-arrow-right"></i>
                            <span class="px-2.5 py-1 rounded-md bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300">2. Verifikasi Admin</span>
                            <i class="bi bi-arrow-right"></i>
                            <span class="px-2.5 py-1 rounded-md bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300">3. Selesai &amp; Tercatat di Audit Log</span>
                        </div>
                    </div>

                    <!-- Feature 2: 5 cols -->
                    <div class="md:col-span-5 p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs flex flex-col justify-between">
                        <div>
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/80 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-4">
                                <i class="bi bi-upc-scan text-lg"></i>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pemindaian Barcode Kamera &amp; Cetak Label</h3>
                            <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                                Pindai label fisik perangkat menggunakan kamera ponsel atau pemindai USB untuk membuka rincian barang, lokasi ruangan terkini, dan riwayat mutasi secara instan.
                            </p>
                        </div>
                        <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                            <span class="font-mono text-slate-500">Format: Code-128 / BRG-XXXXXX</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">Siap Cetak PDF</span>
                        </div>
                    </div>

                    <!-- Feature 3: 5 cols -->
                    <div class="md:col-span-5 p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/80 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-4">
                            <i class="bi bi-bell-fill text-lg"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Pengingat Jatuh Tempo &amp; Stok Menipis</h3>
                        <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Notifikasi otomatis memperingatkan peminjam dan admin ketika perangkat liputan atau perangkat kerja melewati batas waktu pengembalian, serta saat stok habis pakai mencapai ambang minimum.
                        </p>
                    </div>

                    <!-- Feature 4: 7 cols -->
                    <div id="alur-mutasi" class="md:col-span-7 p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 dark:bg-sky-950/80 text-sky-600 dark:text-sky-400 flex items-center justify-center mb-4">
                            <i class="bi bi-activity text-lg"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Observability Performa &amp; Rekam Jejak Audit</h3>
                        <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
                            Dilengkapi panel pemantauan telemetri internal (durasi eksekusi query SQL, penggunaan memori, serta waktu respons HTTP) dan pencatatan perubahan data barang maupun ekspor laporan Excel/PDF.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Covered Organizational Units Section -->
        <section id="bidang-unit" class="py-14 sm:py-18 border-t border-slate-200/80 dark:border-slate-800/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-8">
                    <div>
                        <span class="text-xs font-mono font-bold uppercase tracking-widest text-blue-600 dark:text-blue-400">Distribusi Ruangan</span>
                        <h2 class="mt-1.5 text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            Cakupan Bidang &amp; Ruang Operasional Diskominfo
                        </h2>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Jl. Pembangunan No. 181, Tarogong Kidul, Kabupaten Garut
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
                    @php
                        $unitCards = [
                            ['code' => 'NOC-01', 'name' => 'Ruang Server & NOC', 'desc' => 'Data Center Lt. 2, Router Core, Switch Distribusi & Rak Server'],
                            ['code' => 'APTIKA', 'name' => 'Bidang Aplikasi Informatika', 'desc' => 'Workstation Pengembang, Server Staging & Perangkat Pengujian'],
                            ['code' => 'IKP-MC', 'name' => 'Bidang IKP & Studio Podcast', 'desc' => 'Kamera Liputan, Drone Dokumentasi, Audio Mixer & Lensa'],
                            ['code' => 'GCC-OPS', 'name' => 'Garut Command Center', 'desc' => 'Video Wall Controller, Perangkat Konferensi & Pemantauan Daerah'],
                        ];
                    @endphp
                    @foreach ($unitCards as $unit)
                        <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 hover:border-blue-500/40 transition-colors">
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ $unit['code'] }}</span>
                            <h3 class="mt-2.5 text-sm font-bold text-slate-900 dark:text-white">{{ $unit['name'] }}</h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $unit['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-[#090D16] py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
            <div class="flex items-center gap-2.5">
                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                <span>&copy; {{ date('Y') }} Dinas Komunikasi dan Informatika Kabupaten Garut. Hak Cipta Dilindungi.</span>
            </div>
            <div class="flex items-center gap-5 font-medium">
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Dashboard</a>
                    <a href="{{ route('products.index') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Data Barang</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Login Pegawai</a>
                    <a href="{{ route('register') }}" class="hover:text-blue-600 dark:hover:text-blue-400">Registrasi</a>
                @endauth
            </div>
        </div>
    </footer>

    <!-- Lightweight Theme Controller for Landing Page -->
    <script>
        (function() {
            var btn = document.getElementById('landingThemeBtn');
            var menu = document.getElementById('landingThemeMenu');
            var icon = document.getElementById('landingThemeIcon');
            var label = document.getElementById('landingThemeLabel');
            var caret = document.getElementById('landingThemeCaret');
            if (!btn || !menu) return;

            function applyTheme(mode) {
                localStorage.setItem('color-theme', mode);
                var isDark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');

                var meta = {
                    light: { icon: 'bi-sun-fill text-amber-500', text: 'Terang' },
                    dark: { icon: 'bi-moon-stars-fill text-indigo-400', text: 'Gelap' },
                    system: { icon: 'bi-circle-half text-blue-600 dark:text-blue-400', text: 'Sistem' }
                }[mode] || { icon: 'bi-circle-half text-blue-600', text: 'Sistem' };

                if (icon) icon.className = 'bi ' + meta.icon + ' text-xs';
                if (label) label.textContent = meta.text;

                document.querySelectorAll('[data-theme-check]').forEach(function(el) {
                    el.classList.toggle('opacity-100', el.getAttribute('data-theme-check') === mode);
                    el.classList.toggle('opacity-0', el.getAttribute('data-theme-check') !== mode);
                });
            }

            applyTheme(localStorage.getItem('color-theme') || 'system');

            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                var hidden = menu.classList.toggle('hidden');
                btn.setAttribute('aria-expanded', hidden ? 'false' : 'true');
                if (caret) caret.style.transform = hidden ? '' : 'rotate(180deg)';
            });

            menu.querySelectorAll('[data-landing-theme]').forEach(function(opt) {
                opt.addEventListener('click', function() {
                    applyTheme(opt.getAttribute('data-landing-theme'));
                    menu.classList.add('hidden');
                    btn.setAttribute('aria-expanded', 'false');
                    if (caret) caret.style.transform = '';
                });
            });

            document.addEventListener('click', function() {
                if (!menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                    btn.setAttribute('aria-expanded', 'false');
                    if (caret) caret.style.transform = '';
                }
            });
        })();
    </script>
</body>
</html>
