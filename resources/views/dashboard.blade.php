@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan inventaris & aset Diskominfo Kab. Garut')

@section('content')
<div class="space-y-4 sm:space-y-6" data-framer-page>

    {{-- Hero Welcome & Quick Search Banner (Sleek Mobile-Native & Desktop Layout) --}}
    <div data-framer-card class="relative overflow-hidden rounded-2xl sm:rounded-3xl bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 p-4 sm:p-7 text-white shadow-xl shadow-blue-600/15">
        {{-- Decorative Background Accents --}}
        <div class="absolute -right-12 -top-12 w-56 h-56 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-12 -bottom-12 w-56 h-56 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6">
            {{-- Brand + Concise Greeting --}}
            <div class="flex items-center gap-3.5 sm:gap-4 min-w-0">
                <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-2xl bg-white p-1.5 sm:p-2 shadow-lg shadow-blue-950/25 border border-white/40 flex items-center justify-center shrink-0">
                    <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut" class="w-full h-full object-contain">
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 text-white border border-white/25 backdrop-blur-xs uppercase tracking-wider">
                            {{ auth()->user()->roleLabel() }}
                        </span>
                        <span class="text-[11px] text-blue-100/90 flex items-center gap-1 font-medium">
                            <i class="bi bi-calendar3 text-[10px]"></i>
                            <span>{{ now()->translatedFormat('d M Y') }}</span>
                        </span>
                    </div>
                    <h1 class="text-base sm:text-2xl font-extrabold tracking-tight text-white leading-snug truncate">
                        Halo, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-[11px] sm:text-xs text-blue-100/85 truncate mt-0.5">
                        Inventaris & Aset Diskominfo Kab. Garut
                    </p>
                </div>
            </div>

            {{-- Symmetric 3-Column Quick Actions (1 Row on Mobile, Inline on Desktop) --}}
            <div class="grid grid-cols-3 sm:flex items-center gap-2 sm:gap-2.5 shrink-0">
                <a href="{{ route('products.scan') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2.5 rounded-xl text-xs font-extrabold text-blue-700 bg-white hover:bg-blue-50 shadow-md shadow-blue-950/15 transition-all">
                    <i class="bi bi-qr-code-scan text-xs sm:text-sm"></i>
                    <span>Scan</span>
                </a>
                @if(auth()->user()->canCreateProduct())
                    <a href="{{ route('products.create') }}"
                       class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2.5 rounded-xl text-xs font-extrabold text-white bg-blue-500/40 hover:bg-blue-500/60 border border-white/30 backdrop-blur-xs transition-all">
                        <i class="bi bi-plus-lg text-xs sm:text-sm"></i>
                        <span>Tambah</span>
                    </a>
                @else
                    <a href="{{ route('products.index') }}"
                       class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2.5 rounded-xl text-xs font-extrabold text-white bg-blue-500/40 hover:bg-blue-500/60 border border-white/30 backdrop-blur-xs transition-all">
                        <i class="bi bi-box-seam text-xs sm:text-sm"></i>
                        <span>Barang</span>
                    </a>
                @endif
                <a href="{{ route('mutations.create') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-3 sm:px-4 py-2.5 rounded-xl text-xs font-extrabold text-white bg-white/15 hover:bg-white/25 border border-white/25 backdrop-blur-xs transition-all">
                    <i class="bi bi-arrow-left-right text-xs sm:text-sm"></i>
                    <span>Mutasi</span>
                </a>
            </div>
        </div>

        {{-- Integrated Single-Row Instant Search Bar --}}
        <form action="{{ route('products.index') }}" method="GET" class="relative z-10 mt-3.5 sm:mt-5 pt-3.5 sm:pt-4 border-t border-white/15">
            <div class="relative flex items-center">
                <i class="bi bi-search absolute left-3.5 sm:left-4 text-blue-100 text-xs sm:text-sm pointer-events-none"></i>
                <input type="search"
                       name="search"
                       placeholder="Cari nama barang, kode, barcode, atau ruangan..."
                       class="w-full pl-9 sm:pl-11 pr-20 sm:pr-24 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-white/15 hover:bg-white/20 focus:bg-white text-white focus:text-slate-900 placeholder:text-blue-100/80 focus:placeholder:text-slate-400 text-xs sm:text-sm border border-white/25 focus:border-white focus:outline-none focus:ring-4 focus:ring-white/20 transition-all">
                <button type="submit"
                        class="absolute right-1.5 top-1.5 bottom-1.5 px-3.5 sm:px-4 rounded-lg sm:rounded-xl text-xs font-extrabold text-blue-700 bg-white hover:bg-blue-50 shadow-xs inline-flex items-center gap-1.5 transition-all cursor-pointer">
                    <span>Cari</span>
                    <i class="bi bi-arrow-right text-[10px] hidden sm:inline-block"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- Key Metrics Grid (Compact 2x2 on Mobile, 4 Columns on Desktop) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4" data-framer-stagger>
        {{-- Total Barang --}}
        <a href="{{ route('products.index') }}" class="group bg-white dark:bg-[#0f1b38] rounded-2xl p-3.5 sm:p-5 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 transition-all">
            <div class="flex items-center justify-between gap-2">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-blue-50 dark:bg-blue-500/15 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i class="bi bi-box-seam text-base sm:text-xl"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                    <span data-live-metric="active_products">{{ $activeProducts }}</span> Aktif
                </span>
            </div>
            <div class="mt-3">
                <h3 data-live-metric="total_products" class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight tabular-nums">{{ number_format($totalProducts) }}</h3>
                <p class="text-[11px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Total Barang</p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 truncate">
                    <span data-live-metric="total_units">{{ number_format($totalUnits) }}</span> total unit fisik terdaftar
                </p>
            </div>
        </a>

        {{-- Total Ruangan --}}
        <a href="{{ route('rooms.index') }}" class="group bg-white dark:bg-[#0f1b38] rounded-2xl p-3.5 sm:p-5 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-700 transition-all">
            <div class="flex items-center justify-between gap-2">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i class="bi bi-building text-base sm:text-xl"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400">
                    {{ $activeRooms }} Aktif
                </span>
            </div>
            <div class="mt-3">
                <h3 data-live-metric="total_rooms" class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight tabular-nums">{{ number_format($totalRooms) }}</h3>
                <p class="text-[11px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Ruangan</p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 truncate">
                    Lokasi penempatan aset
                </p>
            </div>
        </a>

        {{-- Total Kategori --}}
        <a href="{{ route('categories.index') }}" class="group bg-white dark:bg-[#0f1b38] rounded-2xl p-3.5 sm:p-5 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700 transition-all">
            <div class="flex items-center justify-between gap-2">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i class="bi bi-tags text-base sm:text-xl"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    Klasifikasi
                </span>
            </div>
            <div class="mt-3">
                <h3 data-live-metric="total_categories" class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight tabular-nums">{{ number_format($totalCategories) }}</h3>
                <p class="text-[11px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Kategori</p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 truncate">
                    Pengelompokan barang
                </p>
            </div>
        </a>

        {{-- Stok Menipis & Perhatian --}}
        <a href="{{ route('products.index', ['stock_status' => 'low_stock']) }}" class="group bg-white dark:bg-[#0f1b38] rounded-2xl p-3.5 sm:p-5 border border-slate-200/80 dark:border-slate-800 shadow-xs hover:shadow-md hover:border-amber-300 dark:hover:border-amber-700 transition-all">
            <div class="flex items-center justify-between gap-2">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <i class="bi bi-exclamation-triangle text-base sm:text-xl"></i>
                </div>
                @if($outOfStockCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400">
                        <span data-live-metric="out_of_stock_count">{{ $outOfStockCount }}</span> Habis
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                        Aman
                    </span>
                @endif
            </div>
            <div class="mt-3">
                <h3 data-live-metric="low_stock_count" class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight tabular-nums">{{ number_format($lowStockCount) }}</h3>
                <p class="text-[11px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">Stok Menipis</p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 truncate">
                    <span data-live-metric="pending_mutations">{{ $pendingMutationsCount }}</span> mutasi menunggu
                </p>
            </div>
        </a>
    </div>

    {{-- Semi-Gradient Analytics Chart & Stock Health Section --}}
    @php
        $analyticsSeries = collect($monthlyAnalytics ?? []);
        $maxChartUnits = max(1, (int) $analyticsSeries->max('total_units'));
        $maxChartTx = max(1, (int) $analyticsSeries->max('tx_count'));
        $totalSixMonthUnits = (int) $analyticsSeries->sum('total_units');
        $totalSixMonthTx = (int) $analyticsSeries->sum('tx_count');
        $healthData = $stockHealth ?? ['healthy' => $activeProducts, 'low' => $lowStockCount, 'out' => $outOfStockCount, 'inactive' => 0, 'score' => 100];
        $mutData = $mutationAnalytics ?? ['approved_count' => 0, 'pending_count' => $pendingMutationsCount, 'rejected_count' => 0, 'approval_rate' => 100, 'masuk_units' => 0, 'pindah_units' => 0, 'keluar_units' => 0];
        $totalFlowUnits = max(1, $mutData['masuk_units'] + $mutData['pindah_units'] + $mutData['keluar_units']);
    @endphp
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-4 sm:gap-6 items-stretch" data-framer-stagger>
        {{-- Left (8 Cols): Semi-Gradient Movement & Activity Area/Bar Chart --}}
        <div class="xl:col-span-8 bg-white dark:bg-[#0f1b38] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 sm:p-6 flex flex-col justify-between overflow-hidden relative">
            {{-- Subtle Semi-Gradient Ambient Glow --}}
            <div class="absolute -top-20 -right-20 w-64 h-64 rounded-full bg-gradient-to-br from-blue-500/10 via-indigo-500/5 to-transparent blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800/80">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 shrink-0"></span>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white tracking-tight truncate">
                            Analitik Pergerakan & Distribusi Aset
                        </h3>
                    </div>
                    <p class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Tren volume mutasi barang & aktivitas katalog 6 bulan terakhir (arah kursor atau ketuk titik untuk rincian)
                    </p>
                </div>

                {{-- Metric Mode Switcher --}}
                <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200/70 dark:border-slate-800 self-start sm:self-auto shrink-0">
                    <button type="button"
                            data-chart-mode-btn="units"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-2xs">
                        Volume Unit
                    </button>
                    <button type="button"
                            data-chart-mode-btn="tx"
                            class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200">
                        Frekuensi Mutasi
                    </button>
                </div>
            </div>

            {{-- Highlight KPI Strip --}}
            <div class="relative z-10 grid grid-cols-3 gap-2.5 sm:gap-4 my-4">
                <div class="p-2.5 sm:p-3.5 rounded-xl bg-gradient-to-br from-emerald-500/12 via-emerald-500/5 to-transparent border border-emerald-500/20">
                    <div class="flex items-center justify-between text-[10px] sm:text-[11px] font-bold text-emerald-700 dark:text-emerald-300">
                        <span class="truncate">Stok Masuk</span>
                        <i class="bi bi-box-arrow-in-down shrink-0"></i>
                    </div>
                    <div class="text-sm sm:text-lg font-extrabold text-slate-900 dark:text-white mt-1 tabular-nums">
                        {{ number_format($mutData['masuk_units']) }} <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">unit</span>
                    </div>
                </div>

                <div class="p-2.5 sm:p-3.5 rounded-xl bg-gradient-to-br from-blue-500/12 via-indigo-500/5 to-transparent border border-blue-500/20">
                    <div class="flex items-center justify-between text-[10px] sm:text-[11px] font-bold text-blue-700 dark:text-blue-300">
                        <span class="truncate">Transfer Ruang</span>
                        <i class="bi bi-arrow-left-right shrink-0"></i>
                    </div>
                    <div class="text-sm sm:text-lg font-extrabold text-slate-900 dark:text-white mt-1 tabular-nums">
                        {{ number_format($mutData['pindah_units']) }} <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">unit</span>
                    </div>
                </div>

                <div class="p-2.5 sm:p-3.5 rounded-xl bg-gradient-to-br from-amber-500/12 via-rose-500/5 to-transparent border border-amber-500/20">
                    <div class="flex items-center justify-between text-[10px] sm:text-[11px] font-bold text-amber-700 dark:text-amber-300">
                        <span class="truncate">Stok Keluar</span>
                        <i class="bi bi-box-arrow-up-right shrink-0"></i>
                    </div>
                    <div class="text-sm sm:text-lg font-extrabold text-slate-900 dark:text-white mt-1 tabular-nums">
                        {{ number_format($mutData['keluar_units']) }} <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400">unit</span>
                    </div>
                </div>
            </div>

            {{-- Semi-Gradient SVG Area + Interactive Bar Columns + Floating Hover/Tap Tooltip --}}
            @php
                $svgW = 640;
                $svgH = 200;
                $padX = 48;
                $padTop = 34;
                $padBot = 36;
                $usableW = $svgW - ($padX * 2);
                $usableH = $svgH - $padTop - $padBot;
                $countPts = max(1, $analyticsSeries->count());
                $stepX = $countPts > 1 ? $usableW / ($countPts - 1) : 0;

                $unitPts = [];
                $txPts = [];
                foreach ($analyticsSeries as $idx => $item) {
                    $x = round($padX + ($idx * $stepX), 1);
                    $uRatio = $maxChartUnits > 0 ? ($item['total_units'] / $maxChartUnits) : 0;
                    $tRatio = $maxChartTx > 0 ? ($item['tx_count'] / $maxChartTx) : 0;
                    $yU = round($padTop + $usableH - ($uRatio * $usableH), 1);
                    $yT = round($padTop + $usableH - ($tRatio * $usableH), 1);
                    $unitPts[] = [$x, $yU];
                    $txPts[] = [$x, $yT];
                }

                $buildSmoothPath = function (array $pts) {
                    if (empty($pts)) return '';
                    $d = 'M ' . $pts[0][0] . ' ' . $pts[0][1];
                    for ($i = 0; $i < count($pts) - 1; $i++) {
                        $curr = $pts[$i];
                        $next = $pts[$i + 1];
                        $cp1x = round($curr[0] + ($next[0] - $curr[0]) * 0.42, 1);
                        $cp2x = round($next[0] - ($next[0] - $curr[0]) * 0.42, 1);
                        $d .= " C {$cp1x} {$curr[1]}, {$cp2x} {$next[1]}, {$next[0]} {$next[1]}";
                    }
                    return $d;
                };

                $unitLinePath = $buildSmoothPath($unitPts);
                $baselineY = $padTop + $usableH;
                $unitAreaPath = $unitLinePath
                    ? $unitLinePath . ' L ' . end($unitPts)[0] . ' ' . $baselineY . ' L ' . $unitPts[0][0] . ' ' . $baselineY . ' Z'
                    : '';

                $txLinePath = $buildSmoothPath($txPts);
                $txAreaPath = $txLinePath
                    ? $txLinePath . ' L ' . end($txPts)[0] . ' ' . $baselineY . ' L ' . $txPts[0][0] . ' ' . $baselineY . ' Z'
                    : '';
            @endphp

            <div class="relative z-10 mt-1">
                <div id="dashChartContainer" class="relative w-full overflow-hidden rounded-2xl bg-slate-50/75 dark:bg-slate-900/60 border border-slate-200/70 dark:border-slate-800/80 p-3 sm:p-4 select-none">
                    {{-- Floating Interactive Hover/Tap Glass Tooltip --}}
                    <div id="dashChartTooltip"
                         class="pointer-events-none opacity-0 scale-95 transition-all duration-200 ease-out absolute z-20 top-2.5 left-3 sm:left-4 max-w-[270px] sm:max-w-xs rounded-xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-blue-200/80 dark:border-blue-500/30 shadow-lg shadow-blue-950/10 px-3 py-2 text-[11px]">
                        <div class="flex items-center justify-between gap-3">
                            <span id="dashTooltipMonth" class="font-extrabold text-slate-900 dark:text-white"></span>
                            <span id="dashTooltipSummaryBadge" class="px-1.5 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-extrabold tabular-nums"></span>
                        </div>
                        <div class="mt-1.5 grid grid-cols-3 gap-2 pt-1.5 border-t border-slate-100 dark:border-slate-800 text-[10px]">
                            <div>
                                <span class="text-slate-400 block">Masuk</span>
                                <strong id="dashTooltipMasuk" class="text-emerald-600 dark:text-emerald-400 tabular-nums">0</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Transfer</span>
                                <strong id="dashTooltipPindah" class="text-blue-600 dark:text-blue-400 tabular-nums">0</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Keluar</span>
                                <strong id="dashTooltipKeluar" class="text-amber-600 dark:text-amber-400 tabular-nums">0</strong>
                            </div>
                        </div>
                    </div>

                    <svg id="dashAnalyticsSvg" viewBox="0 0 {{ $svgW }} {{ $svgH }}" class="w-full h-44 sm:h-52 block" role="img" aria-label="Grafik Pergerakan Inventaris 6 Bulan Terakhir">
                        <defs>
                            <linearGradient id="dashPrimaryAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.38" />
                                <stop offset="60%" stop-color="#6366f1" stop-opacity="0.12" />
                                <stop offset="100%" stop-color="#6366f1" stop-opacity="0.0" />
                            </linearGradient>
                            <linearGradient id="dashSecondaryAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#10b981" stop-opacity="0.36" />
                                <stop offset="65%" stop-color="#14b8a6" stop-opacity="0.11" />
                                <stop offset="100%" stop-color="#14b8a6" stop-opacity="0.0" />
                            </linearGradient>
                            <linearGradient id="dashLineStrokeGrad" x1="0" y1="0" x2="1" y2="0">
                                <stop offset="0%" stop-color="#2563eb" />
                                <stop offset="50%" stop-color="#4f46e5" />
                                <stop offset="100%" stop-color="#06b6d4" />
                            </linearGradient>
                            <linearGradient id="dashBarFillGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.24" />
                                <stop offset="100%" stop-color="#6366f1" stop-opacity="0.03" />
                            </linearGradient>
                            <linearGradient id="dashBarActiveGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#2563eb" stop-opacity="0.45" />
                                <stop offset="100%" stop-color="#4f46e5" stop-opacity="0.08" />
                            </linearGradient>
                        </defs>

                        {{-- Horizontal Guide Lines --}}
                        @foreach([0, 0.5, 1] as $gRatio)
                            @php $gy = round($padTop + ($usableH * $gRatio), 1); @endphp
                            <line x1="{{ $padX }}" y1="{{ $gy }}" x2="{{ $svgW - $padX }}" y2="{{ $gy }}" stroke="currentColor" class="text-slate-200/80 dark:text-slate-800" stroke-dasharray="4 4" stroke-width="1" />
                        @endforeach

                        {{-- Interactive Vertical Crosshair Line --}}
                        <line id="dashChartCrosshair"
                              x1="{{ end($unitPts)[0] ?? $padX }}"
                              y1="{{ $padTop - 8 }}"
                              x2="{{ end($unitPts)[0] ?? $padX }}"
                              y2="{{ $baselineY }}"
                              stroke="#3b82f6"
                              stroke-width="1.5"
                              stroke-dasharray="3 3"
                              class="opacity-0 transition-all duration-200 pointer-events-none" />

                        {{-- Semi-Gradient Vertical Bars per Month --}}
                        @foreach($unitPts as $idx => $pt)
                            @php
                                $barH = max(8, $baselineY - $pt[1]);
                                $barY = $baselineY - $barH;
                            @endphp
                            <rect data-chart-bar-index="{{ $idx }}"
                                  x="{{ $pt[0] - 16 }}"
                                  y="{{ $barY }}"
                                  width="32"
                                  height="{{ $barH }}"
                                  rx="7"
                                  fill="url(#dashBarFillGrad)"
                                  class="transition-all duration-300" />
                        @endforeach

                        {{-- Primary Mode (Units) Area & Smooth Curve --}}
                        <g id="dashChartUnitsLayer" class="transition-opacity duration-300">
                            @if($unitAreaPath)
                                <path d="{{ $unitAreaPath }}" fill="url(#dashPrimaryAreaGrad)" />
                                <path d="{{ $unitLinePath }}" fill="none" stroke="url(#dashLineStrokeGrad)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            @endif
                            @foreach($unitPts as $idx => $pt)
                                @php $mItem = $analyticsSeries[$idx]; @endphp
                                <g class="cursor-pointer" data-chart-point-index="{{ $idx }}">
                                    <circle data-chart-ring-unit="{{ $idx }}" cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="11" class="fill-blue-500/0 transition-all duration-200" />
                                    <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="5" class="fill-white dark:fill-slate-900 stroke-blue-600 dark:stroke-blue-400 transition-transform duration-200" stroke-width="2.75" />
                                    <text x="{{ $pt[0] }}" y="{{ max(16, $pt[1] - 11) }}" text-anchor="middle" class="fill-slate-700 dark:fill-slate-200 text-[10px] font-extrabold tabular-nums pointer-events-none">
                                        {{ $mItem['total_units'] }}
                                    </text>
                                </g>
                            @endforeach
                        </g>

                        {{-- Secondary Mode (Transactions) Area & Smooth Curve --}}
                        <g id="dashChartTxLayer" class="hidden transition-opacity duration-300">
                            @if($txAreaPath)
                                <path d="{{ $txAreaPath }}" fill="url(#dashSecondaryAreaGrad)" />
                                <path d="{{ $txLinePath }}" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                            @endif
                            @foreach($txPts as $idx => $pt)
                                @php $mItem = $analyticsSeries[$idx]; @endphp
                                <g class="cursor-pointer" data-chart-point-index="{{ $idx }}">
                                    <circle data-chart-ring-tx="{{ $idx }}" cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="11" class="fill-emerald-500/0 transition-all duration-200" />
                                    <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="5" class="fill-white dark:fill-slate-900 stroke-emerald-500 transition-transform duration-200" stroke-width="2.75" />
                                    <text x="{{ $pt[0] }}" y="{{ max(16, $pt[1] - 11) }}" text-anchor="middle" class="fill-emerald-700 dark:fill-emerald-300 text-[10px] font-extrabold tabular-nums pointer-events-none">
                                        {{ $mItem['tx_count'] }} tx
                                    </text>
                                </g>
                            @endforeach
                        </g>

                        {{-- Month Labels along X-Axis --}}
                        @foreach($unitPts as $idx => $pt)
                            @php $mItem = $analyticsSeries[$idx]; @endphp
                            <text data-chart-xlabel-index="{{ $idx }}" x="{{ $pt[0] }}" y="{{ $svgH - 10 }}" text-anchor="middle" class="fill-slate-500 dark:fill-slate-400 text-[10px] font-bold transition-colors duration-200 pointer-events-none">
                                {{ $mItem['label'] }}
                            </text>
                        @endforeach

                        {{-- Full-Column Invisible Hit Targets for Smooth Hover & Mobile Tap --}}
                        @foreach($unitPts as $idx => $pt)
                            @php
                                $hitW = $countPts > 1 ? round($usableW / ($countPts - 1), 1) : 80;
                                $hitX = round($pt[0] - ($hitW / 2), 1);
                            @endphp
                            <rect x="{{ max(0, $hitX) }}"
                                  y="0"
                                  width="{{ $hitW }}"
                                  height="{{ $svgH }}"
                                  fill="transparent"
                                  class="cursor-pointer"
                                  data-chart-point-index="{{ $idx }}"
                                  data-chart-pt-x="{{ $pt[0] }}" />
                        @endforeach
                    </svg>

                    {{-- Interactive Month Breakdown Pill Strip --}}
                    <div class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                        <div class="flex flex-wrap items-center gap-2 text-slate-600 dark:text-slate-300 font-semibold min-w-0">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold shrink-0" id="dashActiveMonthLabel">
                                6 Bulan Terakhir
                            </span>
                            <span id="dashActiveMonthDetail" class="text-slate-500 dark:text-slate-400 tabular-nums truncate">
                                {{ number_format($totalSixMonthUnits) }} unit berpindah dalam {{ number_format($totalSixMonthTx) }} transaksi mutasi
                            </span>
                        </div>
                        <a href="{{ route('mutations.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline inline-flex items-center gap-1 shrink-0">
                            <span>Riwayat Mutasi</span>
                            <i class="bi bi-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right (4 Cols): Stock Health & Approval Velocity Card --}}
        <div class="xl:col-span-4 bg-white dark:bg-[#0f1b38] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 sm:p-6 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -bottom-16 -left-16 w-48 h-48 rounded-full bg-gradient-to-tr from-emerald-500/10 via-blue-500/5 to-transparent blur-2xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <div class="min-w-0">
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="bi bi-shield-check text-emerald-500 shrink-0"></i>
                            <span class="truncate">Kesehatan Stok & Mutasi</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Indeks ketersediaan & rasio persetujuan</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl text-xs font-extrabold bg-gradient-to-r from-emerald-500/15 to-teal-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25 tabular-nums shrink-0">
                        {{ $healthData['score'] }}% Sehat
                    </span>
                </div>

                {{-- Semi-Gradient Stacked Bar for Stock Composition --}}
                @php
                    $totalForBar = max(1, $totalProducts);
                    $pctHealthy = round(($healthData['healthy'] / $totalForBar) * 100, 1);
                    $pctLow = round(($healthData['low'] / $totalForBar) * 100, 1);
                    $pctOut = max(0, 100 - $pctHealthy - $pctLow);
                @endphp
                <div class="p-3.5 rounded-2xl bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800 space-y-3">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700 dark:text-slate-200">Komposisi Ketersediaan Aset</span>
                        <span class="font-extrabold text-slate-900 dark:text-white tabular-nums">{{ $totalProducts }} SKU</span>
                    </div>

                    <div class="w-full h-3 rounded-full bg-slate-200/70 dark:bg-slate-800 overflow-hidden flex p-0.5 gap-0.5">
                        @if($pctHealthy > 0)
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500 transition-all duration-500" style="width: {{ max(6, $pctHealthy) }}%" title="Stok Aman: {{ $healthData['healthy'] }}"></div>
                        @endif
                        @if($pctLow > 0)
                            <div class="h-full rounded-full bg-gradient-to-r from-amber-400 to-orange-500 transition-all duration-500" style="width: {{ max(6, $pctLow) }}%" title="Stok Menipis: {{ $healthData['low'] }}"></div>
                        @endif
                        @if($healthData['out'] > 0)
                            <div class="h-full rounded-full bg-gradient-to-r from-rose-500 to-pink-600 transition-all duration-500" style="width: {{ max(6, $pctOut) }}%" title="Stok Habis: {{ $healthData['out'] }}"></div>
                        @endif
                    </div>

                    <div class="grid grid-cols-3 gap-2 pt-1 text-[11px]">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                            <span class="text-slate-500 dark:text-slate-400 truncate">Aman: <strong class="text-slate-800 dark:text-slate-200 tabular-nums">{{ $healthData['healthy'] }}</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                            <span class="text-slate-500 dark:text-slate-400 truncate">Menipis: <strong class="text-slate-800 dark:text-slate-200 tabular-nums">{{ $healthData['low'] }}</strong></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                            <span class="text-slate-500 dark:text-slate-400 truncate">Habis: <strong class="text-slate-800 dark:text-slate-200 tabular-nums">{{ $healthData['out'] }}</strong></span>
                        </div>
                    </div>
                </div>

                {{-- Mutation Fulfillment Pipeline Metrics --}}
                <div class="mt-4 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700 dark:text-slate-200">Rasio Persetujuan Mutasi</span>
                        <span class="font-extrabold text-blue-600 dark:text-blue-400 tabular-nums">{{ $mutData['approval_rate'] }}% Disetujui</span>
                    </div>
                    <div class="w-full h-2 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-500 transition-all duration-500" style="width: {{ max(5, $mutData['approval_rate']) }}%"></div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 pt-1">
                        <a href="{{ route('mutations.index', ['status' => 'approved']) }}" class="p-2.5 rounded-xl bg-emerald-50/70 dark:bg-emerald-500/10 border border-emerald-200/60 dark:border-emerald-500/20 text-center hover:border-emerald-400 transition-all">
                            <div class="text-xs sm:text-sm font-extrabold text-emerald-700 dark:text-emerald-300 tabular-nums">{{ $mutData['approved_count'] }}</div>
                            <div class="text-[10px] font-bold text-emerald-600/80 dark:text-emerald-400/80">Disetujui</div>
                        </a>
                        <a href="{{ route('mutations.index', ['status' => 'pending']) }}" class="p-2.5 rounded-xl bg-amber-50/70 dark:bg-amber-500/10 border border-amber-200/60 dark:border-amber-500/20 text-center hover:border-amber-400 transition-all">
                            <div class="text-xs sm:text-sm font-extrabold text-amber-700 dark:text-amber-300 tabular-nums">{{ $mutData['pending_count'] }}</div>
                            <div class="text-[10px] font-bold text-amber-600/80 dark:text-amber-400/80">Menunggu</div>
                        </a>
                        <a href="{{ route('mutations.index', ['status' => 'rejected']) }}" class="p-2.5 rounded-xl bg-rose-50/70 dark:bg-rose-500/10 border border-rose-200/60 dark:border-rose-500/20 text-center hover:border-rose-400 transition-all">
                            <div class="text-xs sm:text-sm font-extrabold text-rose-700 dark:text-rose-300 tabular-nums">{{ $mutData['rejected_count'] }}</div>
                            <div class="text-[10px] font-bold text-rose-600/80 dark:text-rose-400/80">Ditolak</div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="relative z-10 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400">
                <span>Total Volume Unit & Mutasi</span>
                <span class="font-extrabold text-slate-900 dark:text-white tabular-nums">{{ number_format($totalUnits) }} Unit • {{ number_format($totalMutations) }} Mutasi</span>
            </div>
        </div>
    </div>

    {{-- Middle Section: Distribution by Room & Category --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6" data-framer-stagger>
        {{-- Distribusi Barang per Ruangan --}}
        <div class="bg-white dark:bg-[#0f1b38] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3.5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-building text-blue-600 dark:text-blue-400"></i>
                    <span>Distribusi Ruangan</span>
                </h3>
                <a href="{{ route('rooms.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    <span>Semua</span>
                    <i class="bi bi-chevron-right text-[10px]"></i>
                </a>
            </div>

            <div class="space-y-2">
                @forelse($roomDistribution as $room)
                    @php
                        $percent = $totalProducts > 0 ? min(100, round(($room->products_count / $totalProducts) * 100)) : 0;
                    @endphp
                    <a href="{{ route('products.index', ['room_id' => $room->id]) }}" class="block p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 border border-transparent hover:border-slate-200/70 dark:hover:border-slate-700/70 transition-all">
                        <div class="flex items-center justify-between text-xs mb-1.5">
                            <span class="font-bold text-slate-700 dark:text-slate-200 truncate pr-2">
                                {{ $room->name }}
                            </span>
                            <span class="font-bold text-blue-600 dark:text-blue-400 shrink-0 tabular-nums">
                                {{ $room->products_count }} ({{ $room->total_stock ?? 0 }} unit)
                            </span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full transition-all duration-500" @style(['width: ' . max($percent, 4) . '%'])></div>
                        </div>
                    </a>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6">Belum ada data ruangan.</p>
                @endforelse
            </div>
        </div>

        {{-- Distribusi Barang per Kategori --}}
        <div class="bg-white dark:bg-[#0f1b38] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 sm:p-6">
            <div class="flex items-center justify-between mb-3.5">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-tags text-emerald-600 dark:text-emerald-400"></i>
                    <span>Distribusi Kategori</span>
                </h3>
                <a href="{{ route('categories.index') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                    <span>Semua</span>
                    <i class="bi bi-chevron-right text-[10px]"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                @forelse($categoryDistribution as $cat)
                    <a href="{{ route('products.index', ['category_id' => $cat->id]) }}" class="flex items-center justify-between p-3 rounded-xl bg-slate-50/80 dark:bg-slate-800/40 hover:bg-blue-50/60 dark:hover:bg-slate-800 border border-slate-200/60 dark:border-slate-800 transition-all group">
                        <div class="min-w-0 pr-2">
                            <p class="text-xs font-bold text-slate-800 dark:text-slate-100 group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate">
                                {{ $cat->name }}
                            </p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 tabular-nums">
                                {{ $cat->total_stock ?? 0 }} unit
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 shadow-2xs shrink-0 tabular-nums">
                            {{ $cat->products_count }}
                        </span>
                    </a>
                @empty
                    <p class="text-xs text-slate-400 text-center py-6 col-span-2">Belum ada kategori.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Bottom Section: Recent Inventory Items & Sidebar Alerts --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6" data-framer-stagger>
        {{-- Barang Terbaru (2 Columns Wide on XL) --}}
        <div class="xl:col-span-2 bg-white dark:bg-[#0f1b38] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs overflow-hidden flex flex-col">
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between gap-3">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-box-seam text-blue-600 dark:text-blue-400"></i>
                    <span>Barang Terbaru</span>
                </h3>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                    <span>Semua</span>
                    <i class="bi bi-arrow-right text-[10px]"></i>
                </a>
            </div>

            {{-- Mobile Native Compact List (< sm) --}}
            <div class="sm:hidden divide-y divide-slate-100 dark:divide-slate-800/70">
                @forelse($recentProducts as $product)
                    <a href="{{ route('products.show', $product->id) }}" class="flex items-center justify-between gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $product->name }}</p>
                            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                <span class="font-mono text-[10px] text-blue-600 dark:text-blue-400 font-semibold">{{ $product->kode_barang }}</span>
                                <span>•</span>
                                <span class="truncate">{{ $product->room->name ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="inline-flex px-2 py-0.5 rounded-lg text-[11px] font-extrabold tabular-nums {{ $product->stock == 0 ? 'bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400' : ($product->isLowStock() ? 'bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400' : 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400') }}">
                                {{ $product->stock }} unit
                            </span>
                        </div>
                    </a>
                @empty
                    <p class="px-4 py-8 text-center text-xs text-slate-400">Belum ada barang.</p>
                @endforelse
            </div>

            {{-- Desktop Table (sm+) --}}
            <div class="hidden sm:block overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 dark:bg-slate-800/40 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 border-b border-slate-100 dark:border-slate-800">
                            <th class="px-5 py-3">Barang</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Ruangan</th>
                            <th class="px-4 py-3 text-center">Stok</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/70 text-xs">
                        @forelse($recentProducts as $product)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="font-bold text-slate-800 dark:text-slate-100">{{ $product->name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                        {{ $product->kode_barang }}
                                    </div>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-[11px]">
                                        {{ $product->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center gap-1.5 text-slate-600 dark:text-slate-300 font-medium">
                                        <i class="bi bi-geo-alt text-blue-500"></i>
                                        {{ $product->room->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="font-bold tabular-nums {{ $product->stock == 0 ? 'text-rose-600 dark:text-rose-400' : ($product->isLowStock() ? 'text-amber-600 dark:text-amber-400' : 'text-slate-800 dark:text-slate-100') }}">
                                        {{ $product->stock }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('products.show', $product->id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-100 dark:hover:bg-blue-500/20 transition-colors">
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada data barang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right Column: Low Stock Warning & Recent Activity --}}
        <div class="space-y-4 sm:space-y-6">
            {{-- Peringatan Stok Menipis --}}
            <div class="bg-white dark:bg-[#0f1b38] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 sm:p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-exclamation-circle text-amber-500"></i>
                        <span>Stok Menipis</span>
                    </h3>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 tabular-nums">
                        {{ $lowStockCount }} Item
                    </span>
                </div>

                <div class="space-y-2">
                    @forelse($lowStockProducts as $lowItem)
                        <a href="{{ route('products.show', $lowItem->id) }}" class="flex items-center justify-between p-2.5 rounded-xl bg-amber-50/50 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-800/40 hover:bg-amber-50 dark:hover:bg-amber-950/40 transition-colors">
                            <div class="min-w-0 pr-2">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ $lowItem->name }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                    {{ $lowItem->room->name ?? '-' }} • Min: {{ $lowItem->min_stock ?? 5 }}
                                </p>
                            </div>
                            <span class="px-2 py-1 rounded-lg text-xs font-extrabold tabular-nums {{ $lowItem->stock == 0 ? 'bg-rose-600 text-white' : 'bg-amber-500 text-white' }} shrink-0">
                                {{ $lowItem->stock }}
                            </span>
                        </a>
                    @empty
                        <div class="py-5 text-center">
                            <i class="bi bi-check-circle-fill text-xl text-emerald-500"></i>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Semua stok aman.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Log Aktivitas Terbaru --}}
            <div class="bg-white dark:bg-[#0f1b38] rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs p-4 sm:p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="bi bi-activity text-blue-600 dark:text-blue-400"></i>
                        <span>Aktivitas</span>
                    </h3>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('activity-logs.index') }}" class="text-[11px] font-bold text-blue-600 dark:text-blue-400 hover:underline">
                            Semua
                        </a>
                    @endif
                </div>

                <div class="space-y-3">
                    @forelse($recentLogs as $log)
                        <div class="flex items-start gap-2.5 text-xs">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="bi bi-clock-history text-xs"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-slate-700 dark:text-slate-200 font-medium leading-snug line-clamp-2">
                                    {{ $log->description }}
                                </p>
                                <p class="text-[10px] text-slate-400 mt-0.5">
                                    {{ $log->user->name ?? 'Sistem' }} • {{ $log->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada aktivitas.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const analyticsData = @json($analyticsSeries ?? []);
    const modeBtns = document.querySelectorAll('[data-chart-mode-btn]');
    const unitsLayer = document.getElementById('dashChartUnitsLayer');
    const txLayer = document.getElementById('dashChartTxLayer');
    const activeLabelEl = document.getElementById('dashActiveMonthLabel');
    const activeDetailEl = document.getElementById('dashActiveMonthDetail');
    const chartPoints = document.querySelectorAll('[data-chart-point-index]');
    const chartBars = document.querySelectorAll('[data-chart-bar-index]');
    const chartXLabels = document.querySelectorAll('[data-chart-xlabel-index]');
    const unitRings = document.querySelectorAll('[data-chart-ring-unit]');
    const txRings = document.querySelectorAll('[data-chart-ring-tx]');
    const crosshairEl = document.getElementById('dashChartCrosshair');
    const chartContainer = document.getElementById('dashChartContainer');
    const tooltipEl = document.getElementById('dashChartTooltip');
    const tooltipMonthEl = document.getElementById('dashTooltipMonth');
    const tooltipSummaryEl = document.getElementById('dashTooltipSummaryBadge');
    const tooltipMasukEl = document.getElementById('dashTooltipMasuk');
    const tooltipPindahEl = document.getElementById('dashTooltipPindah');
    const tooltipKeluarEl = document.getElementById('dashTooltipKeluar');

    modeBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const mode = this.getAttribute('data-chart-mode-btn');
            modeBtns.forEach(b => {
                const isActive = b === this;
                b.classList.toggle('bg-white', isActive);
                b.classList.toggle('dark:bg-slate-800', isActive);
                b.classList.toggle('text-blue-600', isActive);
                b.classList.toggle('dark:text-blue-400', isActive);
                b.classList.toggle('shadow-2xs', isActive);
                b.classList.toggle('text-slate-500', !isActive);
                b.classList.toggle('dark:text-slate-400', !isActive);
            });

            const showLayer = mode === 'tx' ? txLayer : unitsLayer;
            const hideLayer = mode === 'tx' ? unitsLayer : txLayer;

            hideLayer?.classList.add('hidden');
            if (showLayer) {
                showLayer.classList.remove('hidden');
                showLayer.animate?.(
                    [
                        { opacity: 0, transform: 'translateY(6px)' },
                        { opacity: 1, transform: 'translateY(0)' }
                    ],
                    { duration: 280, easing: 'cubic-bezier(0.22, 1, 0.36, 1)' }
                );
            }
        });
    });

    const highlightIndex = (idx) => {
        const item = analyticsData[idx];
        if (!item) return;

        if (activeLabelEl && activeDetailEl) {
            activeLabelEl.textContent = item.full_label;
            activeDetailEl.textContent = `Masuk: ${item.masuk_units} • Transfer: ${item.pindah_units} • Keluar: ${item.keluar_units} unit (${item.tx_count} mutasi)`;
        }

        if (tooltipEl && tooltipMonthEl && tooltipSummaryEl && tooltipMasukEl && tooltipPindahEl && tooltipKeluarEl) {
            tooltipMonthEl.textContent = item.full_label;
            tooltipSummaryEl.textContent = `${item.total_units} Unit • ${item.tx_count} Mutasi`;
            tooltipMasukEl.textContent = `${item.masuk_units} unit`;
            tooltipPindahEl.textContent = `${item.pindah_units} unit`;
            tooltipKeluarEl.textContent = `${item.keluar_units} unit`;
            tooltipEl.classList.remove('opacity-0', 'scale-95');
            tooltipEl.classList.add('opacity-100', 'scale-100');
        }

        chartBars.forEach((bar, bIdx) => {
            bar.setAttribute('fill', bIdx === idx ? 'url(#dashBarActiveGrad)' : 'url(#dashBarFillGrad)');
        });

        chartXLabels.forEach((lbl, lIdx) => {
            lbl.classList.toggle('fill-blue-600', lIdx === idx);
            lbl.classList.toggle('dark:fill-blue-400', lIdx === idx);
            lbl.classList.toggle('fill-slate-500', lIdx !== idx);
            lbl.classList.toggle('dark:fill-slate-400', lIdx !== idx);
        });

        unitRings.forEach((ring, rIdx) => {
            ring.setAttribute('class', rIdx === idx
                ? 'fill-blue-500/20 transition-all duration-200'
                : 'fill-blue-500/0 transition-all duration-200');
        });

        txRings.forEach((ring, rIdx) => {
            ring.setAttribute('class', rIdx === idx
                ? 'fill-emerald-500/20 transition-all duration-200'
                : 'fill-emerald-500/0 transition-all duration-200');
        });

        const ptTarget = document.querySelector(`[data-chart-point-index="${idx}"][data-chart-pt-x]`);
        const ptX = ptTarget?.getAttribute('data-chart-pt-x');
        if (crosshairEl && ptX) {
            crosshairEl.setAttribute('x1', ptX);
            crosshairEl.setAttribute('x2', ptX);
            crosshairEl.classList.remove('opacity-0');
            crosshairEl.classList.add('opacity-100');
        }
    };

    chartPoints.forEach(pt => {
        const showPointDetail = () => {
            const idx = parseInt(pt.getAttribute('data-chart-point-index') || '0', 10);
            highlightIndex(idx);
        };
        pt.addEventListener('mouseenter', showPointDetail);
        pt.addEventListener('click', showPointDetail);
        pt.addEventListener('touchstart', showPointDetail, { passive: true });
    });

    chartContainer?.addEventListener('mouseleave', () => {
        if (tooltipEl) {
            tooltipEl.classList.add('opacity-0', 'scale-95');
            tooltipEl.classList.remove('opacity-100', 'scale-100');
        }
        if (crosshairEl) {
            crosshairEl.classList.add('opacity-0');
            crosshairEl.classList.remove('opacity-100');
        }
        chartBars.forEach(bar => bar.setAttribute('fill', 'url(#dashBarFillGrad)'));
    });
});
</script>
@endsection