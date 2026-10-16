/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Admin Observability & Telescope Studio (React 19 + Inertia v2)
 *
 * Strictly restricted to `admin` and `superadmin` roles (`middleware(['auth', 'admin'])`).
 * Features a 1-second (`1000ms`) realtime telemetry refresh loop polling `/observability/metrics`,
 * plus direct integration with official `laravel/telescope` (`/telescope`) and Admin Webhook Studio (`/webhooks`).
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 * @co-collaborator msayib <https://github.com/msayib>
 */

import React, { useEffect, useState } from 'react';
import { AppLayout } from '../../../layouts/AppLayout';
import { Activity, Cpu, Database, ExternalLink, Pause, Play, Webhook } from 'lucide-react';

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
    runtimeInfo?: {
        server_time?: string;
        db_ping_ms?: number;
    };
    telescopeEnabled?: boolean;
}

export default function AdminObservabilityIndex({
    summary: initialSummary,
    runtimeInfo: initialRuntime,
    telescopeEnabled = true,
}: ObservabilityIndexProps) {
    const [liveSummary, setLiveSummary] = useState(initialSummary ?? {});
    const [serverTime, setServerTime] = useState(initialRuntime?.server_time ?? 'LIVE');
    const [dbPingMs, setDbPingMs] = useState(initialRuntime?.db_ping_ms ?? 0);
    const [isLive, setIsLive] = useState(true);

    useEffect(() => {
        if (!isLive) return;

        const timer = window.setInterval(async () => {
            if (document.visibilityState === 'hidden') return;
            try {
                const res = await fetch('/observability/metrics', {
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
                if (data.runtimeInfo) {
                    setServerTime(data.runtimeInfo.server_time ?? new Date().toLocaleTimeString('id-ID'));
                    setDbPingMs(data.runtimeInfo.db_ping_ms ?? 0);
                }
            } catch {
                // Non-blocking 1s poll
            }
        }, 1000);

        return () => window.clearInterval(timer);
    }, [isLive]);

    const recentRequests = liveSummary.recent_requests ?? [];

    return (
        <AppLayout
            title="Observability & Laravel Telescope Telemetry"
            subtitle="Pemantauan realtime (1 detik) performa HTTP request, Server-Timing, konsumsi memori, dan integrasi resmi Laravel Telescope (Admin Only)."
        >
            <div className="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div className="flex flex-wrap items-center gap-2">
                    <div className="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-950/60 px-3.5 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">
                        <span
                            className={`h-2 w-2 rounded-full ${
                                isLive ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'
                            }`}
                        />
                        <span>LIVE 1s STREAM • {serverTime}</span>
                    </div>

                    <button
                        type="button"
                        onClick={() => setIsLive((prev) => !prev)}
                        className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-1 text-xs font-bold text-slate-700 dark:text-slate-200 cursor-pointer"
                    >
                        {isLive ? <Pause className="h-3.5 w-3.5 text-emerald-500" /> : <Play className="h-3.5 w-3.5 text-amber-500" />}
                        <span>{isLive ? 'Live 1d: ON' : 'Live 1d: PAUSED'}</span>
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
        </AppLayout>
    );
}
