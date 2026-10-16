@extends('layouts.app')

@section('title', 'Observability & Telemetry Performa')
@section('meta_description', 'Panel pemantauan performa HTTP request, eksekusi query SQL, memori, dan kesehatan database SIM-Inventaris Diskominfo Kabupaten Garut.')

@section('content')
<div class="space-y-6 pb-8" data-observability-realtime="1000ms">
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-200/70 dark:border-emerald-800/70 text-[11px] font-mono font-bold text-emerald-700 dark:text-emerald-300">
                    <span id="obsLivePulseDot" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>LIVE 1s STREAM • TELESCOPE ACTIVE</span>
                </div>
                <span id="obsLiveClockBadge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-[11px] font-mono font-semibold text-slate-600 dark:text-slate-300">
                    <i class="bi bi-broadcast text-blue-600 dark:text-blue-400"></i>
                    <span id="obsLiveTimestamp">{{ $runtimeInfo['server_time'] ?? now()->format('H:i:s') }}</span>
                    <span class="text-[10px] text-slate-400">(1d refresh)</span>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Observability &amp; Diagnostik Performa Sistem
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Pemantauan realtime (1 detik) waktu respons HTTP, latensi P95, jumlah &amp; durasi query SQL, penggunaan memori PHP, serta kesehatan basis data.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
            <button type="button"
                id="obsLiveToggleBtn"
                data-live-active="1"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 hover:bg-emerald-100 dark:hover:bg-emerald-900/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/70 text-xs font-bold transition-colors cursor-pointer">
                <i id="obsLiveToggleIcon" class="bi bi-pause-circle-fill"></i>
                <span id="obsLiveToggleLabel">Live 1d: ON</span>
            </button>

            <a href="{{ url('/telescope') }}" target="_blank"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition-colors">
                <i class="bi bi-binoculars-fill"></i>
                <span>Buka Laravel Telescope</span>
            </a>

            <a href="{{ route('maintenance.preview') }}" target="_blank"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-50 dark:bg-amber-950/60 hover:bg-amber-100 dark:hover:bg-amber-900/70 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/70 text-xs font-bold transition-colors">
                <i class="bi bi-hdd-rack"></i>
                <span>Pratinjau Mode 503</span>
            </a>

            <form action="{{ route('observability.clear') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-50 dark:bg-rose-950/60 hover:bg-rose-100 dark:hover:bg-rose-900/70 text-rose-700 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/70 text-xs font-bold transition-colors cursor-pointer">
                    <i class="bi bi-trash3"></i>
                    <span>Bersihkan Trace</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Key Telemetry Metrics Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3.5 sm:gap-4">
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400 dark:text-slate-500">
                <span>RATA-RATA LATENSI</span>
                <i class="bi bi-stopwatch text-blue-600 dark:text-blue-400"></i>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span id="obsMetricAvgDuration" class="text-2xl font-extrabold font-mono text-slate-900 dark:text-white tabular-nums">{{ $summary['avg_duration_ms'] }}</span>
                <span class="text-xs font-mono text-slate-500">ms</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">Dari <span id="obsMetricTotalReqs" class="font-bold tabular-nums">{{ $summary['total_requests'] }}</span> request terakhir</p>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400 dark:text-slate-500">
                <span>LATENSI P95</span>
                <i class="bi bi-speedometer2 text-indigo-600 dark:text-indigo-400"></i>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span id="obsMetricP95Duration" class="text-2xl font-extrabold font-mono text-slate-900 dark:text-white tabular-nums">{{ $summary['p95_duration_ms'] }}</span>
                <span class="text-xs font-mono text-slate-500">ms</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">Persentil ke-95 waktu respons</p>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400 dark:text-slate-500">
                <span>RATA-RATA SQL / REQ</span>
                <i class="bi bi-database-check text-emerald-600 dark:text-emerald-400"></i>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span id="obsMetricAvgQueries" class="text-2xl font-extrabold font-mono text-slate-900 dark:text-white tabular-nums">{{ $summary['avg_queries'] }}</span>
                <span class="text-xs font-mono text-slate-500">queries</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">DB Ping: <span id="obsMetricDbPing" class="font-mono font-bold tabular-nums">{{ $runtimeInfo['db_ping_ms'] }}</span> ms</p>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400 dark:text-slate-500">
                <span>SLOW QUERIES (&ge;50ms)</span>
                <i class="bi bi-exclamation-triangle text-amber-500"></i>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span id="obsMetricSlowQueries" class="text-2xl font-extrabold font-mono tabular-nums {{ $summary['slow_queries_total'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ $summary['slow_queries_total'] }}
                </span>
                <span class="text-xs font-mono text-slate-500">kejadian</span>
            </div>
            <p id="obsMetricSlowHint" class="mt-1 text-[11px] text-slate-500">{{ $summary['slow_queries_total'] === 0 ? 'Semua query optimal' : 'Perlu inspeksi indeks' }}</p>
        </div>

        <div class="col-span-2 lg:col-span-1 p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400 dark:text-slate-500">
                <span>PUNCAK MEMORI PHP</span>
                <i class="bi bi-cpu text-sky-600 dark:text-sky-400"></i>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span id="obsMetricPeakMemory" class="text-2xl font-extrabold font-mono text-slate-900 dark:text-white tabular-nums">{{ $summary['peak_memory_mb'] }}</span>
                <span class="text-xs font-mono text-slate-500">MB</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">PHP {{ $runtimeInfo['php_version'] }} · Laravel {{ $runtimeInfo['laravel_version'] }}</p>
        </div>
    </div>

    <!-- Runtime & Database Table Counts -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
        <div class="lg:col-span-5 p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-hdd-network text-blue-600 dark:text-blue-400"></i>
                <span>Konfigurasi Runtime &amp; Koneksi</span>
            </h2>
            <div class="grid grid-cols-2 gap-2.5 text-xs">
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                    <span class="text-[10px] font-mono uppercase text-slate-400 block">Environment</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-100">{{ strtoupper($runtimeInfo['environment']) }}</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                    <span class="text-[10px] font-mono uppercase text-slate-400 block">DB Driver</span>
                    <span id="obsRuntimeDbStatus" class="font-mono font-bold text-slate-800 dark:text-slate-100">{{ strtoupper($runtimeInfo['db_connection']) }} ({{ $runtimeInfo['db_connected'] ? 'ONLINE' : 'OFFLINE' }})</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                    <span class="text-[10px] font-mono uppercase text-slate-400 block">Cache Driver</span>
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-100">{{ strtoupper($runtimeInfo['cache_driver']) }}</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60">
                    <span class="text-[10px] font-mono uppercase text-slate-400 block">Server-Timing Header</span>
                    <span class="font-mono font-bold text-emerald-600 dark:text-emerald-400">AKTIF (W3C)</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-7 p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs space-y-3">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="bi bi-table text-blue-600 dark:text-blue-400"></i>
                <span>Populasi Tabel Basis Data Saat Ini</span>
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 text-center">
                @foreach ($tableStats as $tableName => $count)
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/60 dark:border-slate-700/60">
                        <div data-obs-table="{{ $tableName }}" class="text-lg font-extrabold font-mono text-slate-900 dark:text-white tabular-nums">{{ number_format($count) }}</div>
                        <div class="text-[10px] font-mono uppercase text-slate-500 dark:text-slate-400 truncate">{{ $tableName }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Request & SQL Query Traces Table -->
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs overflow-hidden">
        <div class="p-5 border-b border-slate-200/80 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white">
                    Log Jejak Request HTTP &amp; Query SQL Terkini
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Diperbarui otomatis setiap 1 detik. Klik tombol SQL untuk melihat daftar query pada setiap request.
                </p>
            </div>
            <span class="text-xs font-mono text-slate-400">
                Menampilkan <span id="obsShowingCount" class="font-bold text-slate-600 dark:text-slate-200">{{ count($entries) }}</span> / {{ \App\Http\Middleware\RequestObservabilityMiddleware::MAX_ENTRIES }} trace
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50 text-[11px] font-mono uppercase text-slate-500 dark:text-slate-400">
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-3">Metode &amp; Path</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3">Durasi HTTP</th>
                        <th class="py-3 px-3">SQL Queries</th>
                        <th class="py-3 px-3">Memori</th>
                        <th class="py-3 px-3">Pengguna</th>
                        <th class="py-3 px-4 text-right">Inspeksi SQL</th>
                    </tr>
                </thead>
                <tbody id="obsTracesTableBody" class="divide-y divide-slate-200/60 dark:divide-slate-800/70">
                    @forelse ($entries as $idx => $entry)
                        @php
                            $status = (int) ($entry['status'] ?? 200);
                            $statusClass = $status >= 500
                                ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300'
                                : ($status >= 400
                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300'
                                    : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300');
                            $dur = (float) ($entry['duration_ms'] ?? 0);
                            $durColor = $dur >= 500 ? 'text-rose-600 dark:text-rose-400 font-bold' : ($dur >= 200 ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-slate-800 dark:text-slate-200');
                            $traceKey = ($entry['timestamp'] ?? '') . '|' . ($entry['method'] ?? '') . '|' . ($entry['path'] ?? '');
                        @endphp
                        <tr data-trace-key="{{ $traceKey }}" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">{{ $entry['timestamp'] ?? '-' }}</td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">{{ $entry['method'] ?? 'GET' }}</span>
                                    <span class="font-mono font-semibold text-slate-900 dark:text-white truncate max-w-[240px]">{{ $entry['path'] ?? '/' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-md font-mono text-[11px] font-bold {{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td class="py-3 px-3 font-mono {{ $durColor }} whitespace-nowrap">{{ $dur }} ms</td>
                            <td class="py-3 px-3 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                {{ $entry['query_count'] ?? 0 }} q <span class="text-slate-400">({{ $entry['sql_ms'] ?? 0 }} ms)</span>
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $entry['memory_mb'] ?? 0 }} MB</td>
                            <td class="py-3 px-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ $entry['user_name'] ?? 'Guest' }}</td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @if (!empty($entry['queries']))
                                    <button type="button"
                                        data-sql-toggle="obsSqlRow{{ $idx }}"
                                        onclick="document.getElementById('obsSqlRow{{ $idx }}').classList.toggle('hidden')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-950/60 text-slate-700 dark:text-slate-300 font-mono text-[11px] cursor-pointer">
                                        <i class="bi bi-code-slash"></i>
                                        <span>{{ count($entry['queries']) }} SQL</span>
                                    </button>
                                @else
                                    <span class="text-slate-400 font-mono text-[11px]">0 SQL</span>
                                @endif
                            </td>
                        </tr>
                        @if (!empty($entry['queries']))
                            <tr id="obsSqlRow{{ $idx }}" data-sql-detail-for="{{ $traceKey }}" class="hidden bg-slate-950 text-slate-200">
                                <td colspan="8" class="p-4">
                                    <div class="space-y-1.5 font-mono text-[11px]">
                                        <div class="text-slate-400 text-[10px] uppercase tracking-wider mb-2">Daftar Eksekusi Query SQL ({{ $entry['path'] }}):</div>
                                        @foreach ($entry['queries'] as $qIdx => $q)
                                            <div class="flex items-start justify-between gap-4 p-2 rounded bg-slate-900/90 border border-slate-800">
                                                <span class="text-sky-300 break-all">{{ $qIdx + 1 }}. {{ $q['sql'] }}</span>
                                                <span class="shrink-0 px-2 py-0.5 rounded {{ ($q['time_ms'] ?? 0) >= 50 ? 'bg-amber-950 text-amber-300' : 'bg-slate-800 text-emerald-300' }}">
                                                    {{ $q['time_ms'] ?? 0 }} ms
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                Belum ada rekaman trace. Navigasikan beberapa halaman untuk melihat live stream.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const metricsUrl = '{{ route('observability.metrics', [], false) }}';
    const toggleBtn = document.getElementById('obsLiveToggleBtn');
    const toggleIcon = document.getElementById('obsLiveToggleIcon');
    const toggleLabel = document.getElementById('obsLiveToggleLabel');
    const pulseDot = document.getElementById('obsLivePulseDot');
    const timestampEl = document.getElementById('obsLiveTimestamp');

    const avgDurEl = document.getElementById('obsMetricAvgDuration');
    const totalReqsEl = document.getElementById('obsMetricTotalReqs');
    const p95DurEl = document.getElementById('obsMetricP95Duration');
    const avgQueriesEl = document.getElementById('obsMetricAvgQueries');
    const dbPingEl = document.getElementById('obsMetricDbPing');
    const slowQueriesEl = document.getElementById('obsMetricSlowQueries');
    const slowHintEl = document.getElementById('obsMetricSlowHint');
    const peakMemEl = document.getElementById('obsMetricPeakMemory');
    const showingCountEl = document.getElementById('obsShowingCount');
    const tbodyEl = document.getElementById('obsTracesTableBody');

    let isLive = true;
    let isFetching = false;
    let lastEntriesHash = '';

    function escapeHtml(val) {
        return String(val ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    toggleBtn?.addEventListener('click', function () {
        isLive = !isLive;
        this.setAttribute('data-live-active', isLive ? '1' : '0');
        if (toggleLabel) toggleLabel.textContent = isLive ? 'Live 1d: ON' : 'Live 1d: PAUSED';
        if (toggleIcon) toggleIcon.className = isLive ? 'bi bi-pause-circle-fill' : 'bi bi-play-circle-fill';
        if (pulseDot) {
            pulseDot.classList.toggle('bg-emerald-500', isLive);
            pulseDot.classList.toggle('animate-pulse', isLive);
            pulseDot.classList.toggle('bg-amber-500', !isLive);
        }
    });

    function renderEntriesTable(entries) {
        if (!tbodyEl || !Array.isArray(entries)) return;

        // Track currently expanded SQL rows by trace key so 1s live refresh never closes an open SQL inspection
        const expandedKeys = new Set();
        tbodyEl.querySelectorAll('[data-sql-detail-for]').forEach((row) => {
            if (!row.classList.contains('hidden')) {
                expandedKeys.add(row.getAttribute('data-sql-detail-for'));
            }
        });

        const hash = entries.map((e) => `${e.timestamp}|${e.method}|${e.path}|${e.duration_ms}`).join(';;');
        if (hash === lastEntriesHash) return;
        lastEntriesHash = hash;

        if (entries.length === 0) {
            tbodyEl.innerHTML = `
                <tr>
                    <td colspan="8" class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">
                        Belum ada rekaman trace. Navigasikan beberapa halaman untuk melihat live stream.
                    </td>
                </tr>
            `;
            return;
        }

        tbodyEl.innerHTML = entries.map((entry, idx) => {
            const status = Number(entry.status || 200);
            const statusClass = status >= 500
                ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300'
                : (status >= 400
                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300'
                    : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300');
            const dur = Number(entry.duration_ms || 0);
            const durColor = dur >= 500
                ? 'text-rose-600 dark:text-rose-400 font-bold'
                : (dur >= 200 ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-slate-800 dark:text-slate-200');
            const queries = Array.isArray(entry.queries) ? entry.queries : [];
            const traceKey = `${entry.timestamp || ''}|${entry.method || ''}|${entry.path || ''}`;
            const isExpanded = expandedKeys.has(traceKey);
            const sqlRowId = `obsSqlRowLive${idx}`;

            const sqlBtnHtml = queries.length > 0
                ? `<button type="button"
                        onclick="document.getElementById('${sqlRowId}').classList.toggle('hidden')"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-50 dark:hover:bg-blue-950/60 text-slate-700 dark:text-slate-300 font-mono text-[11px] cursor-pointer">
                        <i class="bi bi-code-slash"></i>
                        <span>${queries.length} SQL</span>
                   </button>`
                : `<span class="text-slate-400 font-mono text-[11px]">0 SQL</span>`;

            const sqlDetailHtml = queries.length > 0
                ? `<tr id="${sqlRowId}" data-sql-detail-for="${escapeHtml(traceKey)}" class="${isExpanded ? '' : 'hidden'} bg-slate-950 text-slate-200">
                        <td colspan="8" class="p-4">
                            <div class="space-y-1.5 font-mono text-[11px]">
                                <div class="text-slate-400 text-[10px] uppercase tracking-wider mb-2">Daftar Eksekusi Query SQL (${escapeHtml(entry.path)}):</div>
                                ${queries.map((q, qIdx) => `
                                    <div class="flex items-start justify-between gap-4 p-2 rounded bg-slate-900/90 border border-slate-800">
                                        <span class="text-sky-300 break-all">${qIdx + 1}. ${escapeHtml(q.sql)}</span>
                                        <span class="shrink-0 px-2 py-0.5 rounded ${(q.time_ms || 0) >= 50 ? 'bg-amber-950 text-amber-300' : 'bg-slate-800 text-emerald-300'}">
                                            ${escapeHtml(q.time_ms || 0)} ms
                                        </span>
                                    </div>
                                `).join('')}
                            </div>
                        </td>
                   </tr>`
                : '';

            return `
                <tr data-trace-key="${escapeHtml(traceKey)}" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                    <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">${escapeHtml(entry.timestamp || '-')}</td>
                    <td class="py-3 px-3">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">${escapeHtml(entry.method || 'GET')}</span>
                            <span class="font-mono font-semibold text-slate-900 dark:text-white truncate max-w-[240px]">${escapeHtml(entry.path || '/')}</span>
                        </div>
                    </td>
                    <td class="py-3 px-3">
                        <span class="px-2 py-0.5 rounded-md font-mono text-[11px] font-bold ${statusClass}">${status}</span>
                    </td>
                    <td class="py-3 px-3 font-mono ${durColor} whitespace-nowrap">${dur} ms</td>
                    <td class="py-3 px-3 font-mono text-slate-700 dark:text-slate-300 whitespace-nowrap">
                        ${Number(entry.query_count || 0)} q <span class="text-slate-400">(${Number(entry.sql_ms || 0)} ms)</span>
                    </td>
                    <td class="py-3 px-3 font-mono text-slate-600 dark:text-slate-400 whitespace-nowrap">${Number(entry.memory_mb || 0)} MB</td>
                    <td class="py-3 px-3 text-slate-700 dark:text-slate-300 whitespace-nowrap">${escapeHtml(entry.user_name || 'Guest')}</td>
                    <td class="py-3 px-4 text-right whitespace-nowrap">${sqlBtnHtml}</td>
                </tr>
                ${sqlDetailHtml}
            `;
        }).join('');
    }

    async function refreshObservabilityRealtime() {
        if (!isLive || isFetching || document.visibilityState === 'hidden') return;
        isFetching = true;
        try {
            const res = await fetch(metricsUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!res.ok) return;
            const data = await res.json();
            if (!data || !data.ok) return;

            if (data.runtimeInfo && timestampEl) {
                timestampEl.textContent = data.runtimeInfo.server_time || new Date().toLocaleTimeString('id-ID');
            }
            if (data.runtimeInfo && dbPingEl) {
                dbPingEl.textContent = data.runtimeInfo.db_ping_ms;
            }

            if (data.summary) {
                if (avgDurEl) avgDurEl.textContent = data.summary.avg_duration_ms;
                if (totalReqsEl) totalReqsEl.textContent = data.summary.total_requests;
                if (p95DurEl) p95DurEl.textContent = data.summary.p95_duration_ms;
                if (avgQueriesEl) avgQueriesEl.textContent = data.summary.avg_queries;
                if (peakMemEl) peakMemEl.textContent = data.summary.peak_memory_mb;
                if (slowQueriesEl) {
                    const sq = Number(data.summary.slow_queries_total || 0);
                    slowQueriesEl.textContent = sq;
                    slowQueriesEl.className = `text-2xl font-extrabold font-mono tabular-nums ${sq > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'}`;
                    if (slowHintEl) {
                        slowHintEl.textContent = sq === 0 ? 'Semua query optimal' : 'Perlu inspeksi indeks';
                    }
                }
            }

            if (data.tableStats) {
                Object.entries(data.tableStats).forEach(([tbl, cnt]) => {
                    const el = document.querySelector(`[data-obs-table="${tbl}"]`);
                    if (el) el.textContent = Number(cnt).toLocaleString('id-ID');
                });
            }

            if (Array.isArray(data.entries)) {
                if (showingCountEl) showingCountEl.textContent = data.entries.length;
                renderEntriesTable(data.entries);
            }
        } catch (e) {
            // Non-blocking 1s refresh
        } finally {
            isFetching = false;
        }
    }

    setInterval(refreshObservabilityRealtime, 1000);
});
</script>
@endsection
