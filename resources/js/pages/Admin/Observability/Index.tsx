/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Admin Observability & Telescope Studio (React 19 + Inertia v2)
 *
 * Strictly restricted to `admin` and `superadmin` roles (`middleware(['auth', 'admin'])`).
 * Features a 1-second (`1000ms`) automatic heartbeat polling `/observability/metrics`,
 * trace pagination (`page`, `per_page`, `search`, `status`), and Laravel Telescope integration.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 * @co-collaborator msayib <https://github.com/msayib>
 */

import React, { useEffect, useState } from 'react';
import { AppLayout } from '../../../layouts/AppLayout';
import {
    Activity,
    Cpu,
    Database,
    ExternalLink,
    Pause,
    Play,
    Webhook,
    ChevronLeft,
    ChevronRight,
} from 'lucide-react';

export interface ObservabilityTraceEntry {
    timestamp?: string;
    method?: string;
    path?: string;
    status?: number;
    duration_ms?: number;
    query_count?: number;
    sql_ms?: number;
    memory_mb?: number;
    user_name?: string;
}

export interface ObservabilityPagination {
    current_page: number;
    per_page: number;
    total: number;
    last_page: number;
    from: number;
    to: number;
}

export interface ObservabilityIndexProps {
    summary?: {
        avg_duration_ms?: number;
        p95_duration_ms?: number;
        avg_queries?: number;
        slow_queries_total?: number;
        peak_memory_mb?: number;
        total_requests?: number;
        recent_requests?: ObservabilityTraceEntry[];
    };
    pagination?: ObservabilityPagination;
    runtimeInfo?: {
        server_time?: string;
        db_ping_ms?: number;
    };
    telescopeEnabled?: boolean;
}

export default function AdminObservabilityIndex({
    summary: initialSummary,
    pagination: initialPagination,
    runtimeInfo: initialRuntime,
    telescopeEnabled = true,
}: ObservabilityIndexProps) {
    const [liveSummary, setLiveSummary] = useState(initialSummary ?? {});
    const [pagination, setPagination] = useState<ObservabilityPagination>(
        initialPagination ?? {
            current_page: 1,
            per_page: 10,
            total: (initialSummary?.recent_requests ?? []).length,
            last_page: 1,
            from: 1,
            to: (initialSummary?.recent_requests ?? []).length,
        },
    );
    const [page, setPage] = useState<number>(initialPagination?.current_page ?? 1);
    const [perPage, setPerPage] = useState<number>(initialPagination?.per_page ?? 10);
    const [search, setSearch] = useState('');
    const [serverTime, setServerTime] = useState(initialRuntime?.server_time ?? 'LIVE');
    const [dbPingMs, setDbPingMs] = useState(initialRuntime?.db_ping_ms ?? 0);
    const [isLive, setIsLive] = useState(true);

    useEffect(() => {
        if (!isLive) return;

        const timer = window.setInterval(async () => {
            if (document.visibilityState === 'hidden') return;
            try {
                const params = new URLSearchParams({
                    page: String(page),
                    per_page: String(perPage),
                    search,
                });
                const res = await fetch(`/observability/metrics?${params.toString()}`, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!res.ok) return;
                const data = await res.json();
                if (!data?.ok) return;

                if (data.summary) {
                    setLiveSummary({
                        ...data.summary,
                        recent_requests: data.entries ?? [],
                    });
                }
                if (data.pagination) {
                    setPagination(data.pagination);
                }
                if (data.runtimeInfo) {
                    setServerTime(data.runtimeInfo.server_time ?? new Date().toLocaleTimeString('id-ID'));
                    setDbPingMs(data.runtimeInfo.db_ping_ms ?? 0);
                }
            } catch {
                // Non-blocking 1000ms heartbeat poll
            }
        }, 1000);

        return () => window.clearInterval(timer);
    }, [isLive, page, perPage, search]);

    const recentRequests = liveSummary.recent_requests ?? [];

    return (
        <AppLayout
            title="Observability & Laravel Telescope Telemetry"
            subtitle="Pemantauan realtime otomatis setiap 1000ms (1 detik) untuk HTTP request, Server-Timing, konsumsi memori, dan paginasi trace (Admin Only)."
        >
            <div className="flex flex-wrap items-center justify-between gap-3 mb-6" data-heartbeat-interval="1000">
                <div className="flex flex-wrap items-center gap-2">
                    <div className="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-950/60 px-3.5 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">
                        <span
                            className={`h-2 w-2 rounded-full ${
                                isLive ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'
                            }`}
                        />
                        <span>HEARTBEAT 1000ms • {serverTime}</span>
                    </div>

                    <button
                        type="button"
                        onClick={() => setIsLive((prev) => !prev)}
                        className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1 text-xs font-bold text-slate-700 dark:text-slate-200 cursor-pointer"
                    >
                        {isLive ? <Pause className="h-3.5 w-3.5 text-emerald-500" /> : <Play className="h-3.5 w-3.5 text-amber-500" />}
                        <span>{isLive ? 'Heartbeat 1000ms: ON' : 'Heartbeat 1000ms: PAUSED'}</span>
                    </button>
                </div>

                <div className="flex items-center gap-2">
                    <a
                        href="/webhooks"
                        className="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2 text-xs font-bold text-slate-700 dark:text-slate-200"
                    >
                        <Webhook className="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                        <span>Webhook Studio</span>
                    </a>
                    {telescopeEnabled && (
                        <a
                            href="/telescope"
                            className="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2 text-xs font-bold text-white shadow-sm"
                        >
                            <span>Buka Laravel Telescope Dashboard</span>
                            <ExternalLink className="h-3.5 w-3.5" />
                        </a>
                    )}
                </div>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <Activity className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    <div className="mt-2 text-2xl font-extrabold font-mono tabular-nums">
                        {liveSummary.avg_duration_ms ?? 0} ms
                    </div>
                    <div className="text-xs text-slate-500">Rata-rata Latensi HTTP</div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <Activity className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    <div className="mt-2 text-2xl font-extrabold font-mono tabular-nums">
                        {liveSummary.p95_duration_ms ?? 0} ms
                    </div>
                    <div className="text-xs text-slate-500">Latensi P95</div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <Cpu className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <div className="mt-2 text-2xl font-extrabold font-mono tabular-nums">
                        {liveSummary.peak_memory_mb ?? 0} MB
                    </div>
                    <div className="text-xs text-slate-500">Puncak Memori PHP</div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <Database className="h-5 w-5 text-sky-600 dark:text-sky-400" />
                    <div className="mt-2 text-2xl font-extrabold font-mono tabular-nums">
                        {liveSummary.total_requests ?? recentRequests.length}
                    </div>
                    <div className="text-xs text-slate-500">Trace Tercatat (DB Ping: {dbPingMs} ms)</div>
                </div>
            </div>

            <div className="mt-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 overflow-hidden">
                <div className="p-4 sm:p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 className="text-sm font-extrabold text-slate-900 dark:text-white">
                            Log Jejak Request HTTP & SQL (Heartbeat 1000ms + Paginasi)
                        </h3>
                    </div>
                    <div className="flex items-center gap-2">
                        <input
                            type="search"
                            value={search}
                            onChange={(e) => {
                                setSearch(e.target.value);
                                setPage(1);
                            }}
                            placeholder="Filter path atau user..."
                            className="px-3 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800"
                        />
                        <select
                            value={perPage}
                            onChange={(e) => {
                                setPerPage(Number(e.target.value));
                                setPage(1);
                            }}
                            className="px-2.5 py-1.5 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 cursor-pointer"
                        >
                            {[5, 10, 20, 30, 50].map((n) => (
                                <option key={n} value={n}>
                                    {n}/hal
                                </option>
                            ))}
                        </select>
                    </div>
                </div>

                <div className="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                    {recentRequests.map((entry, idx) => (
                        <div key={`${entry.timestamp}-${idx}`} className="px-4 py-3 flex items-center justify-between gap-3">
                            <div className="flex items-center gap-2 min-w-0">
                                <span className="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">
                                    {entry.method ?? 'GET'}
                                </span>
                                <span className="font-mono font-semibold text-slate-900 dark:text-white truncate">
                                    {entry.path ?? '/'}
                                </span>
                            </div>
                            <div className="flex items-center gap-3 font-mono text-[11px] shrink-0">
                                <span>{entry.duration_ms ?? 0} ms</span>
                                <span className="text-slate-400">{entry.query_count ?? 0} SQL</span>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="px-4 py-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                    <span>
                        Menampilkan {pagination.from}–{pagination.to} dari {pagination.total} trace (Hal {pagination.current_page} / {pagination.last_page})
                    </span>
                    <div className="inline-flex items-center gap-1.5">
                        <button
                            type="button"
                            disabled={page <= 1}
                            onClick={() => setPage((p) => Math.max(1, p - 1))}
                            className="p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 disabled:opacity-40 cursor-pointer"
                        >
                            <ChevronLeft className="h-3.5 w-3.5" />
                        </button>
                        <button
                            type="button"
                            disabled={page >= pagination.last_page}
                            onClick={() => setPage((p) => Math.min(pagination.last_page, p + 1))}
                            className="p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 disabled:opacity-40 cursor-pointer"
                        >
                            <ChevronRight className="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
