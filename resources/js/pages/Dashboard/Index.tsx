/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Dashboard Page (React 19 + Inertia v2)
 *
 * Renders live inventory KPIs, Stock Health, and an interactive 6-month semi-gradient
 * SVG telemetry chart with hover/tap floating tooltips.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 * @co-collaborator msayib <https://github.com/msayib>
 */

import React, { useMemo, useState } from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { Package, CheckCircle2, AlertTriangle, ArrowRightLeft, ShieldCheck } from 'lucide-react';

export interface DashboardMetrics {
    totalProducts?: number;
    activeProducts?: number;
    totalUnits?: number;
    totalRooms?: number;
    activeRooms?: number;
    totalCategories?: number;
    lowStockCount?: number;
    outOfStockCount?: number;
    totalMutations?: number;
    pendingMutations?: number;
}

export interface MonthlyAnalyticPoint {
    label: string;
    full_label: string;
    masuk_units: number;
    pindah_units: number;
    keluar_units: number;
    total_units: number;
    tx_count: number;
    new_products: number;
}

export interface MutationAnalyticsSummary {
    approved_count: number;
    pending_count: number;
    rejected_count: number;
    approval_rate: number;
    masuk_units: number;
    pindah_units: number;
    keluar_units: number;
}

export interface StockHealthSummary {
    healthy: number;
    low: number;
    out: number;
    inactive: number;
    score: number;
}

export interface ShadcnLineSeriesConfig {
    key: keyof MonthlyAnalyticPoint;
    label: string;
    color: string;
    dashed?: boolean;
}

export const SHADCN_LINE_CHART_CONFIG: {
    type: 'shadcn-line-chart';
    series: Record<string, ShadcnLineSeriesConfig>;
} = {
    type: 'shadcn-line-chart',
    series: {
        total_units: { key: 'total_units', label: 'Total Volume Unit', color: '#2563eb' },
        masuk_units: { key: 'masuk_units', label: 'Barang Masuk', color: '#10b981', dashed: true },
        pindah_units: { key: 'pindah_units', label: 'Transfer Ruangan', color: '#6366f1', dashed: true },
        tx_count: { key: 'tx_count', label: 'Frekuensi Mutasi', color: '#10b981' },
    },
};

export interface DashboardIndexProps {
    metrics?: DashboardMetrics;
    monthlyAnalytics?: MonthlyAnalyticPoint[];
    mutationAnalytics?: MutationAnalyticsSummary;
    stockHealth?: StockHealthSummary;
    chartJson?: {
        type: 'shadcn-line-chart';
        config: typeof SHADCN_LINE_CHART_CONFIG.series;
        data: MonthlyAnalyticPoint[];
    };
}

const DEFAULT_MONTHLY_SAMPLE: MonthlyAnalyticPoint[] = [
    { label: 'Mei', full_label: 'Mei 2026', masuk_units: 4, pindah_units: 8, keluar_units: 0, total_units: 12, tx_count: 2, new_products: 5 },
    { label: 'Jun', full_label: 'Juni 2026', masuk_units: 6, pindah_units: 3, keluar_units: 2, total_units: 11, tx_count: 3, new_products: 4 },
    { label: 'Jul', full_label: 'Juli 2026', masuk_units: 7, pindah_units: 5, keluar_units: 0, total_units: 12, tx_count: 2, new_products: 3 },
    { label: 'Agu', full_label: 'Agustus 2026', masuk_units: 10, pindah_units: 2, keluar_units: 3, total_units: 15, tx_count: 3, new_products: 5 },
    { label: 'Sep', full_label: 'September 2026', masuk_units: 4, pindah_units: 2, keluar_units: 2, total_units: 8, tx_count: 3, new_products: 4 },
    { label: 'Okt', full_label: 'Oktober 2026', masuk_units: 4, pindah_units: 3, keluar_units: 0, total_units: 7, tx_count: 3, new_products: 4 },
];

export default function DashboardIndex({
    metrics = {},
    monthlyAnalytics = DEFAULT_MONTHLY_SAMPLE,
    mutationAnalytics,
    stockHealth,
    chartJson,
}: DashboardIndexProps) {
    const resolvedChartJson = useMemo(
        () =>
            chartJson ?? {
                type: 'shadcn-line-chart' as const,
                config: SHADCN_LINE_CHART_CONFIG.series,
                data: monthlyAnalytics.length > 0 ? monthlyAnalytics : DEFAULT_MONTHLY_SAMPLE,
            },
        [chartJson, monthlyAnalytics],
    );
    const series = resolvedChartJson.data;
    const [chartMode, setChartMode] = useState<'units' | 'tx'>('units');
    const [activeIdx, setActiveIdx] = useState<number | null>(null);

    const svgW = 640;
    const svgH = 200;
    const padX = 48;
    const padTop = 34;
    const padBot = 36;
    const usableW = svgW - padX * 2;
    const usableH = svgH - padTop - padBot;
    const baselineY = padTop + usableH;

    const buildCatmullCurve = (pts: Array<{ x: number; y: number }>): string => {
        if (pts.length === 0) return '';
        let d = `M ${pts[0].x} ${pts[0].y}`;
        for (let i = 0; i < pts.length - 1; i++) {
            const curr = pts[i];
            const next = pts[i + 1];
            const cp1x = Number((curr.x + (next.x - curr.x) * 0.42).toFixed(1));
            const cp2x = Number((next.x - (next.x - curr.x) * 0.42).toFixed(1));
            d += ` C ${cp1x} ${curr.y}, ${cp2x} ${next.y}, ${next.x} ${next.y}`;
        }
        return d;
    };

    const chartGeometry = useMemo(() => {
        const maxVal = Math.max(
            1,
            ...series.map((item) => (chartMode === 'units' ? item.total_units : item.tx_count)),
        );
        const stepX = series.length > 1 ? usableW / (series.length - 1) : 0;

        const points = series.map((item, idx) => {
            const val = chartMode === 'units' ? item.total_units : item.tx_count;
            const ratio = val / maxVal;
            const x = Number((padX + idx * stepX).toFixed(1));
            const y = Number((padTop + usableH - ratio * usableH).toFixed(1));
            const masukY = Number((padTop + usableH - (item.masuk_units / maxVal) * usableH).toFixed(1));
            const pindahY = Number((padTop + usableH - (item.pindah_units / maxVal) * usableH).toFixed(1));
            return { x, y, masukY, pindahY, val, item };
        });

        const linePath = buildCatmullCurve(points.map((p) => ({ x: p.x, y: p.y })));
        const masukLinePath = buildCatmullCurve(points.map((p) => ({ x: p.x, y: p.masukY })));
        const pindahLinePath = buildCatmullCurve(points.map((p) => ({ x: p.x, y: p.pindahY })));

        const areaPath =
            points.length > 0
                ? `${linePath} L ${points[points.length - 1].x} ${baselineY} L ${points[0].x} ${baselineY} Z`
                : '';

        return { points, linePath, masukLinePath, pindahLinePath, areaPath };
    }, [series, chartMode, usableW, usableH, baselineY]);

    const activePoint = activeIdx !== null ? chartGeometry.points[activeIdx] : null;
    const totalUnits = metrics.totalUnits ?? series.reduce((acc, s) => acc + s.total_units, 0);
    const totalMutations = metrics.totalMutations ?? series.reduce((acc, s) => acc + s.tx_count, 0);
    const healthScore = stockHealth?.score ?? 92;

    return (
        <AppLayout
            title="Dashboard Inventaris"
            subtitle="Ringkasan real-time aset TIK, kesehatan stok, dan aktivitas mutasi Diskominfo Kabupaten Garut."
        >
            <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-xs">
                    <Package className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    <div className="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">
                        {metrics.totalProducts ?? 0}
                    </div>
                    <div className="text-xs font-semibold text-slate-500">Total Barang</div>
                    <div className="mt-1 text-[10px] text-slate-400 truncate">
                        {totalUnits} total unit fisik terdaftar
                    </div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-xs">
                    <CheckCircle2 className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <div className="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">
                        {metrics.activeProducts ?? 0}
                    </div>
                    <div className="text-xs font-semibold text-slate-500">Barang Aktif</div>
                    <div className="mt-1 text-[10px] text-slate-400 truncate">Siap operasional</div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-xs">
                    <AlertTriangle className="h-5 w-5 text-amber-500" />
                    <div className="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">
                        {metrics.lowStockCount ?? 0}
                    </div>
                    <div className="text-xs font-semibold text-slate-500">Stok Menipis</div>
                    <div className="mt-1 text-[10px] text-slate-400 truncate">Perlu perhatian logistik</div>
                </div>
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-xs">
                    <ArrowRightLeft className="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                    <div className="mt-2 text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">
                        {metrics.pendingMutations ?? 0}
                    </div>
                    <div className="text-xs font-semibold text-slate-500">Mutasi Pending</div>
                    <div className="mt-1 text-[10px] text-slate-400 truncate">
                        {totalMutations} total transaksi mutasi
                    </div>
                </div>
            </div>

            <div className="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-12">
                <div className="xl:col-span-8 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-xs">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h2 className="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white">
                                Analitik Pergerakan & Distribusi Aset
                            </h2>
                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Shadcn Line Chart multi-series 6 bulan terakhir (tanpa bar gabungan, konsumsi JSON deklaratif)
                            </p>
                        </div>
                        <div className="inline-flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200/70 dark:border-slate-700 self-start sm:self-auto">
                            <button
                                type="button"
                                onClick={() => setChartMode('units')}
                                className={`px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer ${
                                    chartMode === 'units'
                                        ? 'bg-white dark:bg-slate-900 text-blue-600 dark:text-blue-400 shadow-2xs'
                                        : 'text-slate-500 dark:text-slate-400'
                                }`}
                            >
                                Volume Unit
                            </button>
                            <button
                                type="button"
                                onClick={() => setChartMode('tx')}
                                className={`px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all cursor-pointer ${
                                    chartMode === 'tx'
                                        ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-2xs'
                                        : 'text-slate-500 dark:text-slate-400'
                                }`}
                            >
                                Frekuensi Mutasi
                            </button>
                        </div>
                    </div>

                    <div
                        data-shadcn-line-chart="true"
                        className="relative mt-4 w-full overflow-hidden rounded-2xl bg-slate-50/75 dark:bg-slate-950/60 border border-slate-200/70 dark:border-slate-800 p-3 sm:p-4 select-none"
                        onMouseLeave={() => setActiveIdx(null)}
                    >
                        {activePoint && (
                            <div className="pointer-events-none absolute z-20 top-2.5 left-3 sm:left-4 max-w-xs rounded-xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-blue-200/80 dark:border-blue-500/30 shadow-lg px-3 py-2 text-[11px]">
                                <div className="flex items-center justify-between gap-3">
                                    <span className="font-extrabold text-slate-900 dark:text-white">
                                        {activePoint.item.full_label}
                                    </span>
                                    <span className="px-1.5 py-0.5 rounded-md bg-blue-500/10 text-blue-600 dark:text-blue-400 font-extrabold tabular-nums">
                                        {activePoint.item.total_units} Unit • {activePoint.item.tx_count} Mutasi
                                    </span>
                                </div>
                                <div className="mt-1.5 grid grid-cols-3 gap-2 pt-1.5 border-t border-slate-100 dark:border-slate-800 text-[10px]">
                                    <div>
                                        <span className="text-slate-400 block">Masuk</span>
                                        <strong className="text-emerald-600 dark:text-emerald-400 tabular-nums">
                                            {activePoint.item.masuk_units} unit
                                        </strong>
                                    </div>
                                    <div>
                                        <span className="text-slate-400 block">Transfer</span>
                                        <strong className="text-blue-600 dark:text-blue-400 tabular-nums">
                                            {activePoint.item.pindah_units} unit
                                        </strong>
                                    </div>
                                    <div>
                                        <span className="text-slate-400 block">Keluar</span>
                                        <strong className="text-amber-600 dark:text-amber-400 tabular-nums">
                                            {activePoint.item.keluar_units} unit
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        )}

                        <svg viewBox={`0 0 ${svgW} ${svgH}`} className="h-44 sm:h-52 w-full block">
                            <defs>
                                <linearGradient id="dashPrimaryAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stopColor="#2563eb" stopOpacity="0.22" />
                                    <stop offset="100%" stopColor="#2563eb" stopOpacity="0.0" />
                                </linearGradient>
                                <linearGradient id="dashSecondaryAreaGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stopColor="#10b981" stopOpacity="0.20" />
                                    <stop offset="100%" stopColor="#10b981" stopOpacity="0.0" />
                                </linearGradient>
                            </defs>

                            {activePoint && (
                                <line
                                    x1={activePoint.x}
                                    y1={padTop - 8}
                                    x2={activePoint.x}
                                    y2={baselineY}
                                    stroke="#3b82f6"
                                    strokeWidth="1.5"
                                    strokeDasharray="3 3"
                                />
                            )}

                            <path
                                d={chartGeometry.areaPath}
                                fill={chartMode === 'units' ? 'url(#dashPrimaryAreaGrad)' : 'url(#dashSecondaryAreaGrad)'}
                            />
                            {chartMode === 'units' && (
                                <>
                                    <path
                                        d={chartGeometry.masukLinePath}
                                        fill="none"
                                        stroke="#10b981"
                                        strokeWidth="1.75"
                                        strokeDasharray="4 4"
                                        strokeLinecap="round"
                                        opacity="0.8"
                                    />
                                    <path
                                        d={chartGeometry.pindahLinePath}
                                        fill="none"
                                        stroke="#6366f1"
                                        strokeWidth="1.75"
                                        strokeDasharray="2 3"
                                        strokeLinecap="round"
                                        opacity="0.75"
                                    />
                                </>
                            )}
                            <path
                                d={chartGeometry.linePath}
                                fill="none"
                                stroke={chartMode === 'units' ? '#2563eb' : '#10b981'}
                                strokeWidth="2.75"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            />

                            {chartGeometry.points.map((pt, idx) => (
                                <g
                                    key={pt.item.label}
                                    className="cursor-pointer"
                                    onMouseEnter={() => setActiveIdx(idx)}
                                    onClick={() => setActiveIdx(idx)}
                                >
                                    <circle
                                        cx={pt.x}
                                        cy={pt.y}
                                        r="5"
                                        className="fill-white dark:fill-slate-900 stroke-blue-600 dark:stroke-blue-400"
                                        strokeWidth="2.5"
                                    />
                                    <text
                                        x={pt.x}
                                        y={Math.max(16, pt.y - 11)}
                                        textAnchor="middle"
                                        className="fill-slate-700 dark:fill-slate-200 text-[10px] font-extrabold tabular-nums"
                                    >
                                        {pt.val}
                                    </text>
                                    <text
                                        x={pt.x}
                                        y={svgH - 10}
                                        textAnchor="middle"
                                        className="fill-slate-500 dark:fill-slate-400 text-[10px] font-bold"
                                    >
                                        {pt.item.label}
                                    </text>
                                    <rect
                                        x={pt.x - 36}
                                        y={0}
                                        width={72}
                                        height={svgH}
                                        fill="transparent"
                                    />
                                </g>
                            ))}
                        </svg>
                    </div>
                </div>

                <div className="xl:col-span-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div className="flex items-center justify-between gap-2">
                            <h3 className="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                                <ShieldCheck className="h-4 w-4 text-emerald-500" />
                                <span>Kesehatan Stok & Mutasi</span>
                            </h3>
                            <span className="px-2.5 py-1 rounded-xl text-xs font-extrabold bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 tabular-nums">
                                {healthScore}% Sehat
                            </span>
                        </div>
                        <p className="mt-2 text-xs text-slate-500 dark:text-slate-400">
                            Pemantauan komposisi ketersediaan unit fisik dan rasio persetujuan mutasi lintas ruangan.
                        </p>
                    </div>

                    <div className="mt-6 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>Total Volume Unit & Mutasi</span>
                        <span className="font-extrabold text-slate-900 dark:text-white tabular-nums">
                            {totalUnits} Unit • {totalMutations} Mutasi
                        </span>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
