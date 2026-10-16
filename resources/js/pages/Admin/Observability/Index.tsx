/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Admin Observability & Telescope Studio (React 19 + Inertia v2)
 *
 * Strictly restricted to `admin` and `superadmin` roles (`middleware(['auth', 'admin'])`).
 * Integrates directly with official `laravel/telescope` (`/telescope`) and Admin Webhook Studio (`/webhooks`).
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../../layouts/AppLayout';
import { Activity, Cpu, Database, ExternalLink, Webhook } from 'lucide-react';

export interface ObservabilityIndexProps {
    summary?: {
        avg_duration_ms?: number;
        max_duration_ms?: number;
        avg_memory_mb?: number;
        total_sampled?: number;
    };
    telescopeEnabled?: boolean;
}

export default function AdminObservabilityIndex({
    summary,
    telescopeEnabled = true,
}: ObservabilityIndexProps) {
    return (
        <AppLayout
            title="Observability & Laravel Telescope Telemetry"
            subtitle="Pemantauan performa HTTP request, Server-Timing, konsumsi memori, dan integrasi resmi Laravel Telescope (Admin Only)."
        >
            <div className="flex flex-wrap items-center justify-between gap-3 mb-6">
                <div className="inline-flex items-center gap-2 rounded-full bg-emerald-50 dark:bg-emerald-950/60 px-3.5 py-1 text-xs font-bold text-emerald-700 dark:text-emerald-300 border border-emerald-200/60 dark:border-emerald-800/60">
                    <Activity className="h-3.5 w-3.5" />
                    <span>Admin & Super Admin Only • Telemetry Active</span>
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

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <Activity className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    <div className="mt-2 text-2xl font-extrabold font-mono">
                        {summary?.avg_duration_ms ?? 0} ms
                    </div>
                    <div className="text-xs text-slate-500">Rata-rata Latensi HTTP</div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <Cpu className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <div className="mt-2 text-2xl font-extrabold font-mono">
                        {summary?.avg_memory_mb ?? 0} MB
                    </div>
                    <div className="text-xs text-slate-500">Rata-rata Memori PHP</div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5">
                    <Database className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    <div className="mt-2 text-2xl font-extrabold font-mono">
                        {summary?.total_sampled ?? 0}
                    </div>
                    <div className="text-xs text-slate-500">Sampel Request Tercatat</div>
                </div>
            </div>
        </AppLayout>
    );
}
