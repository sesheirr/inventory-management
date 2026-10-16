/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Dashboard Page (React 19 + Inertia v2)
 *
 * Renders live inventory KPIs and a semi-gradient SVG telemetry chart.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { Package, CheckCircle2, AlertTriangle, ArrowRightLeft } from 'lucide-react';

export interface DashboardMetrics {
    totalProducts?: number;
    activeProducts?: number;
    lowStockCount?: number;
    pendingMutations?: number;
}

export interface DashboardIndexProps {
    metrics?: DashboardMetrics;
}

export default function DashboardIndex({ metrics = {} }: DashboardIndexProps) {
    return (
        <AppLayout
            title="Dashboard Inventaris"
            subtitle="Ringkasan real-time aset TIK, kesehatan stok, dan aktivitas mutasi Diskominfo Kabupaten Garut."
        >
            <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-xs">
                    <Package className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    <div className="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                        {metrics.totalProducts ?? 0}
                    </div>
                    <div className="text-xs font-semibold text-slate-500">Total Barang</div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-xs">
                    <CheckCircle2 className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <div className="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                        {metrics.activeProducts ?? 0}
                    </div>
                    <div className="text-xs font-semibold text-slate-500">Barang Aktif</div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-xs">
                    <AlertTriangle className="h-5 w-5 text-amber-500" />
                    <div className="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                        {metrics.lowStockCount ?? 0}
                    </div>
                    <div className="text-xs font-semibold text-slate-500">Stok Menipis</div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-xs">
                    <ArrowRightLeft className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    <div className="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white">
                        {metrics.pendingMutations ?? 0}
                    </div>
                    <div className="text-xs font-semibold text-slate-500">Mutasi Pending</div>
                </div>
            </div>

            <div className="mt-6 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs">
                <h2 className="text-sm font-bold text-slate-900 dark:text-white">
                    Tren Pergerakan Stok & Aktivitas Mutasi (Semi-Gradient Telemetry)
                </h2>
                <div className="mt-4 h-48 w-full">
                    <svg viewBox="0 0 600 180" className="h-full w-full overflow-visible">
                        <defs>
                            <linearGradient id="dashPrimaryAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stopColor="#2563eb" stopOpacity="0.42" />
                                <stop offset="100%" stopColor="#2563eb" stopOpacity="0.0" />
                            </linearGradient>
                            <linearGradient id="dashSecondaryAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stopColor="#10b981" stopOpacity="0.35" />
                                <stop offset="100%" stopColor="#10b981" stopOpacity="0.0" />
                            </linearGradient>
                        </defs>
                        <path
                            d="M0,140 C100,110 200,75 300,88 C400,100 500,45 600,30 L600,170 L0,170 Z"
                            fill="url(#dashPrimaryAreaGrad)"
                        />
                        <path
                            d="M0,140 C100,110 200,75 300,88 C400,100 500,45 600,30"
                            fill="none"
                            stroke="#2563eb"
                            strokeWidth="3"
                        />
                    </svg>
                </div>
            </div>
        </AppLayout>
    );
}
