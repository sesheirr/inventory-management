@extends('layouts.app')

@section('title', 'Observability & Telemetry Performa')
@section('meta_description', 'Panel pemantauan performa HTTP request, eksekusi query SQL, memori, dan kesehatan database SIM-Inventaris Diskominfo Kabupaten Garut.')

@section('content')
<div class="space-y-6 pb-8">
    <!-- Top Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 p-5 sm:p-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-xs">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-950/70 border border-emerald-200/70 dark:border-emerald-800/70 text-[11px] font-mono font-bold text-emerald-700 dark:text-emerald-300">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>TELESCOPE / BUILT-IN OBSERVABILITY ACTIVE</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                Observability &amp; Diagnostik Performa Sistem
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                Pemantauan waktu respons HTTP, latensi P95, jumlah &amp; durasi query SQL, penggunaan memori PHP, serta kesehatan basis data secara langsung.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
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

            <a href="{{ route('observability.index') }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition-colors">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Segarkan</span>
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
                <span class="text-2xl font-extrabold font-mono text-slate-900 dark:text-white">{{ $summary['avg_duration_ms'] }}</span>
                <span class="text-xs font-mono text-slate-500">ms</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">Dari {{ $summary['total_requests'] }} request terakhir</p>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400 dark:text-slate-500">
                <span>LATENSI P95</span>
                <i class="bi bi-speedometer2 text-indigo-600 dark:text-indigo-400"></i>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-extrabold font-mono text-slate-900 dark:text-white">{{ $summary['p95_duration_ms'] }}</span>
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
                <span class="text-2xl font-extrabold font-mono text-slate-900 dark:text-white">{{ $summary['avg_queries'] }}</span>
                <span class="text-xs font-mono text-slate-500">queries</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">DB Ping: {{ $runtimeInfo['db_ping_ms'] }} ms</p>
        </div>

        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400 dark:text-slate-500">
                <span>SLOW QUERIES (&ge;50ms)</span>
                <i class="bi bi-exclamation-triangle text-amber-500"></i>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-extrabold font-mono {{ $summary['slow_queries_total'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                    {{ $summary['slow_queries_total'] }}
                </span>
                <span class="text-xs font-mono text-slate-500">kejadian</span>
            </div>
            <p class="mt-1 text-[11px] text-slate-500">{{ $summary['slow_queries_total'] === 0 ? 'Semua query optimal' : 'Perlu inspeksi indeks' }}</p>
        </div>

        <div class="col-span-2 lg:col-span-1 p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-2xs">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400 dark:text-slate-500">
                <span>PUNCAK MEMORI PHP</span>
                <i class="bi bi-cpu text-sky-600 dark:text-sky-400"></i>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-extrabold font-mono text-slate-900 dark:text-white">{{ $summary['peak_memory_mb'] }}</span>
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
                    <span class="font-mono font-bold text-slate-800 dark:text-slate-100">{{ strtoupper($runtimeInfo['db_connection']) }} ({{ $runtimeInfo['db_connected'] ? 'ONLINE' : 'OFFLINE' }})</span>
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
                        <div class="text-lg font-extrabold font-mono text-slate-900 dark:text-white">{{ number_format($count) }}</div>
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
                    Klik baris atau tombol SQL untuk melihat daftar query yang dijalankan pada setiap request.
                </p>
            </div>
            <span class="text-xs font-mono text-slate-400">Menampilkan {{ count($entries) }} / {{ \App\Http\Middleware\RequestObservabilityMiddleware::MAX_ENTRIES }} trace</span>
        </div>

        @if (empty($entries))
            <div class="p-10 text-center text-sm text-slate-500 dark:text-slate-400">
                Belum ada rekaman trace. Navigasikan beberapa halaman lalu segarkan tampilan ini.
            </div>
        @else
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
                    <tbody class="divide-y divide-slate-200/60 dark:divide-slate-800/70">
                        @foreach ($entries as $idx => $entry)
                            @php
                                $status = (int) ($entry['status'] ?? 200);
                                $statusClass = $status >= 500
                                    ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300'
                                    : ($status >= 400
                                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300'
                                        : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300');
                                $dur = (float) ($entry['duration_ms'] ?? 0);
                                $durColor = $dur >= 500 ? 'text-rose-600 dark:text-rose-400 font-bold' : ($dur >= 200 ? 'text-amber-600 dark:text-amber-400 font-semibold' : 'text-slate-800 dark:text-slate-200');
                            @endphp
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
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
                                <tr id="obsSqlRow{{ $idx }}" class="hidden bg-slate-950 text-slate-200">
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
