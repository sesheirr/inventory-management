<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 — Pemeliharaan Terjadwal Sistem Inventaris Diskominfo Garut</title>

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

    <style>
        @keyframes rackPulse {
            0%, 100% { opacity: 0.35; transform: scaleX(0.96); }
            50% { opacity: 1; transform: scaleX(1); }
        }
        @keyframes packetFlow {
            0% { stroke-dashoffset: 120; }
            100% { stroke-dashoffset: 0; }
        }
        @keyframes scanBeam {
            0%, 100% { transform: translateY(0px); opacity: 0.35; }
            50% { transform: translateY(96px); opacity: 0.9; }
        }
        .maint-packet-line {
            stroke-dasharray: 10 10;
            animation: packetFlow 2.6s linear infinite;
        }
        .maint-scan-beam {
            animation: scanBeam 3.4s cubic-bezier(0.22, 1, 0.36, 1) infinite;
        }
        .maint-rack-bar-1 { animation: rackPulse 2.1s ease-in-out infinite; }
        .maint-rack-bar-2 { animation: rackPulse 2.1s ease-in-out 0.45s infinite; }
        .maint-rack-bar-3 { animation: rackPulse 2.1s ease-in-out 0.9s infinite; }
    </style>
</head>
<body class="min-h-[100dvh] bg-[#F8FAFC] dark:bg-[#090D16] text-slate-900 dark:text-slate-100 flex flex-col justify-between p-4 sm:p-8 antialiased selection:bg-blue-600 selection:text-white relative overflow-x-hidden">

    <!-- Technical Grid Background -->
    <div class="fixed inset-0 pointer-events-none z-0" aria-hidden="true">
        <div class="absolute inset-0 opacity-[0.04] dark:opacity-[0.06]" style="background-image: linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px); background-size: 36px 36px;"></div>
    </div>

    <!-- Top Status Header -->
    <header class="relative z-10 max-w-5xl w-full mx-auto flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-slate-900 dark:bg-blue-600 text-white flex items-center justify-center shadow-xs">
                <i class="bi bi-hdd-network-fill text-sm"></i>
            </div>
            <div>
                <span class="text-xs sm:text-sm font-extrabold tracking-tight block leading-none">SIM-INVENTARIS TIK</span>
                <span class="text-[11px] text-slate-500 dark:text-slate-400">Diskominfo Kabupaten Garut</span>
            </div>
        </div>

        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 dark:bg-amber-950/70 border border-amber-200/80 dark:border-amber-800/70 text-[11px] font-mono font-semibold text-amber-800 dark:text-amber-300">
            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
            <span>HTTP 503 · MAINTENANCE MODE</span>
        </div>
    </header>

    <!-- Main Diagnostic Card -->
    <main class="relative z-10 max-w-5xl w-full mx-auto my-auto py-8">
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-xl overflow-hidden grid grid-cols-1 lg:grid-cols-12">
            
            <!-- Left Column: Animated NOC Server Rack Telemetry Schematic (5 cols) -->
            <div class="lg:col-span-5 bg-slate-950 text-slate-100 p-6 sm:p-8 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-slate-800 relative overflow-hidden">
                <div class="flex items-center justify-between text-[11px] font-mono text-slate-400">
                    <span>NODE: NOC-GRT-CORE-01</span>
                    <span class="text-blue-400 font-semibold">SYNCING SCHEMA</span>
                </div>

                <!-- Custom Animated SVG Server Rack & Telemetry -->
                <div class="my-6 flex items-center justify-center">
                    <svg class="w-full max-w-[260px] h-auto" viewBox="0 0 260 175" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Animasi Sinkronisasi Server Inventaris">
                        <!-- Outer Rack Frame -->
                        <rect x="20" y="12" width="220" height="150" rx="12" fill="#0F172A" stroke="#1E293B" stroke-width="2"/>
                        <!-- Scanning Diagnostic Beam -->
                        <rect class="maint-scan-beam" x="24" y="24" width="212" height="26" rx="6" fill="#2563EB" fill-opacity="0.14"/>

                        <!-- Rack Blade 1 -->
                        <rect x="34" y="26" width="192" height="32" rx="6" fill="#090D16" stroke="#334155" stroke-width="1.2"/>
                        <circle cx="48" cy="42" r="4" fill="#10B981"/>
                        <circle cx="60" cy="42" r="4" fill="#3B82F6"/>
                        <rect class="maint-rack-bar-1" x="76" y="38" width="92" height="8" rx="4" fill="#1D4ED8"/>
                        <text x="180" y="45" fill="#94A3B8" font-family="JetBrains Mono, monospace" font-size="9">DB-OK</text>

                        <!-- Rack Blade 2 -->
                        <rect x="34" y="68" width="192" height="32" rx="6" fill="#090D16" stroke="#334155" stroke-width="1.2"/>
                        <circle cx="48" cy="84" r="4" fill="#F59E0B"/>
                        <circle cx="60" cy="84" r="4" fill="#3B82F6"/>
                        <rect class="maint-rack-bar-2" x="76" y="80" width="104" height="8" rx="4" fill="#0284C7"/>
                        <text x="186" y="87" fill="#FBBF24" font-family="JetBrains Mono, monospace" font-size="9">INDEX</text>

                        <!-- Rack Blade 3 -->
                        <rect x="34" y="110" width="192" height="32" rx="6" fill="#090D16" stroke="#334155" stroke-width="1.2"/>
                        <circle cx="48" cy="126" r="4" fill="#10B981"/>
                        <circle cx="60" cy="126" r="4" fill="#10B981"/>
                        <rect class="maint-rack-bar-3" x="76" y="122" width="84" height="8" rx="4" fill="#059669"/>
                        <text x="176" y="129" fill="#94A3B8" font-family="JetBrains Mono, monospace" font-size="9">CACHE</text>

                        <!-- Animated Packet Flow Line -->
                        <path class="maint-packet-line" d="M34 152 H226" stroke="#38BDF8" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>

                <!-- Live Subsystem Checklist -->
                <div class="space-y-2 text-xs font-mono border-t border-slate-800/90 pt-4">
                    <div class="flex items-center justify-between text-slate-300">
                        <span><i class="bi bi-check-circle-fill text-emerald-400 mr-1.5"></i>Integritas Basis Data Aset</span>
                        <span class="text-emerald-400">TERJAGA</span>
                    </div>
                    <div class="flex items-center justify-between text-slate-300">
                        <span><i class="bi bi-arrow-repeat text-amber-400 mr-1.5"></i>Sinkronisasi Indeks &amp; Cache</span>
                        <span class="text-amber-400">BERJALAN</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Clear Message & Interactive Retry Controls (7 cols) -->
            <div class="lg:col-span-7 p-6 sm:p-9 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <div class="inline-flex items-center gap-2 text-xs font-mono font-bold uppercase tracking-wider text-blue-600 dark:text-blue-400">
                        <i class="bi bi-shield-check"></i>
                        <span>Pemeliharaan Berkala &amp; Optimasi Sistem</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-snug">
                        Sistem Sedang Menjalankan Pembaruan Terjadwal
                    </h1>

                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        Kami sedang melakukan pemeliharaan terukur pada layanan <strong>SIM-Inventaris Diskominfo Kabupaten Garut</strong> untuk memastikan keakuratan pencatatan stok, kecepatan pemindaian barcode, dan keamanan rekam jejak mutasi barang.
                    </p>

                    <!-- Telemetry Info Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/70">
                            <span class="text-[10px] font-mono uppercase text-slate-400 block">Status Data Inventaris</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100 mt-0.5 block">Aman &amp; Tidak Berubah</span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/70">
                            <span class="text-[10px] font-mono uppercase text-slate-400 block">Pengecekan Otomatis</span>
                            <span id="maintCountdownText" class="text-xs font-mono font-bold text-blue-600 dark:text-blue-400 mt-0.5 block">Memeriksa kembali dalam 30 dtk</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <button type="button" id="maintRetryBtn" onclick="window.location.reload()"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-bold shadow-sm transition-all cursor-pointer active:scale-[0.98]">
                            <i class="bi bi-arrow-clockwise" id="maintRetryIcon"></i>
                            <span id="maintRetryLabel">Periksa Status Layanan</span>
                        </button>
                        <a href="{{ url('/') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs sm:text-sm font-semibold transition-colors">
                            <i class="bi bi-house-door"></i>
                            <span>Beranda</span>
                        </a>
                    </div>

                    <span class="text-[11px] font-mono text-slate-400">
                        Kode: ERR_MAINTENANCE_503
                    </span>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 max-w-5xl w-full mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
        <span>Tim Infrastruktur &amp; Aptika — Diskominfo Kabupaten Garut</span>
        <span class="font-mono text-[11px]">{{ now()->format('Y-m-d H:i:s T') }}</span>
    </footer>

    <script>
        (function() {
            var remaining = 30;
            var label = document.getElementById('maintCountdownText');
            var timer = setInterval(function() {
                remaining -= 1;
                if (remaining <= 0) {
                    clearInterval(timer);
                    if (label) label.textContent = 'Memuat ulang status layanan...';
                    window.location.reload();
                    return;
                }
                if (label) {
                    label.textContent = 'Memeriksa kembali dalam ' + remaining + ' dtk';
                }
            }, 1000);
        })();
    </script>
</body>
</html>
