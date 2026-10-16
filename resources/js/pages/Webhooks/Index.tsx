/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Admin Webhook Studio (React 19 + Inertia v2)
 *
 * Strictly restricted to `admin` and `superadmin` roles. Manages outgoing
 * HMAC-SHA256 signed webhook endpoints and live delivery audit logs.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { Webhook, ShieldCheck, Activity, Send, CheckCircle2, XCircle } from 'lucide-react';

export interface WebhookEndpointItem {
    id: number;
    name: string;
    target_url: string;
    events: string[];
    is_active: boolean;
    last_triggered_at?: string | null;
    deliveries_count?: number;
}

export interface WebhookDeliveryItem {
    id: number;
    event_name: string;
    response_status: number | null;
    duration_ms: number | null;
    status: string;
    created_at: string;
    endpoint?: {
        id: number;
        name: string;
        target_url: string;
    };
}

export interface WebhooksIndexProps {
    endpoints: WebhookEndpointItem[];
    recentDeliveries: WebhookDeliveryItem[];
    availableEvents: Record<string, string>;
}

export default function WebhooksIndex({
    endpoints = [],
    recentDeliveries = [],
}: WebhooksIndexProps) {
    return (
        <AppLayout
            title="Admin Webhook Studio"
            subtitle="Integrasi real-time event inventaris & mutasi ke sistem eksternal Diskominfo dengan tanda tangan HMAC-SHA256."
        >
            <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div className="lg:col-span-2 space-y-4">
                    <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs">
                        <div className="flex items-center justify-between">
                            <h2 className="inline-flex items-center gap-2 text-base font-bold text-slate-900 dark:text-white">
                                <Webhook className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                <span>Endpoint Terdaftar ({endpoints.length})</span>
                            </h2>
                            <span className="inline-flex items-center gap-1 rounded-full bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:text-emerald-300">
                                <ShieldCheck className="h-3.5 w-3.5" />
                                HMAC-SHA256 Signed
                            </span>
                        </div>

                        <div className="mt-4 space-y-3">
                            {endpoints.map((ep) => (
                                <div
                                    key={ep.id}
                                    className="rounded-xl border border-slate-200/70 dark:border-slate-800 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3"
                                >
                                    <div>
                                        <div className="flex items-center gap-2">
                                            <span className="text-sm font-bold text-slate-900 dark:text-white">
                                                {ep.name}
                                            </span>
                                            <span
                                                className={`rounded-full px-2 py-0.5 text-[10px] font-bold ${
                                                    ep.is_active
                                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                                        : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                                }`}
                                            >
                                                {ep.is_active ? 'Aktif' : 'Dijeda'}
                                            </span>
                                        </div>
                                        <p className="mt-1 font-mono text-xs text-slate-500 dark:text-slate-400 break-all">
                                            {ep.target_url}
                                        </p>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <span className="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400">
                                            <Send className="h-3.5 w-3.5" />
                                            {ep.deliveries_count ?? 0} pengiriman
                                        </span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs">
                    <h2 className="inline-flex items-center gap-2 text-base font-bold text-slate-900 dark:text-white">
                        <Activity className="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                        <span>Riwayat Pengiriman Terakhir</span>
                    </h2>
                    <div className="mt-4 space-y-2.5">
                        {recentDeliveries.map((del) => (
                            <div
                                key={del.id}
                                className="flex items-center justify-between rounded-xl border border-slate-100 dark:border-slate-800 p-3 text-xs"
                            >
                                <div className="flex items-center gap-2">
                                    {del.status === 'delivered' ? (
                                        <CheckCircle2 className="h-4 w-4 text-emerald-500 shrink-0" />
                                    ) : (
                                        <XCircle className="h-4 w-4 text-rose-500 shrink-0" />
                                    )}
                                    <div>
                                        <div className="font-mono font-bold text-slate-800 dark:text-slate-200">
                                            {del.event_name}
                                        </div>
                                        <div className="text-[11px] text-slate-500">
                                            {del.endpoint?.name ?? 'Endpoint'}
                                        </div>
                                    </div>
                                </div>
                                <div className="text-right font-mono">
                                    <div className="font-bold">
                                        HTTP {del.response_status ?? 'ERR'}
                                    </div>
                                    <div className="text-[10px] text-slate-400">
                                        {del.duration_ms ?? 0}ms
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
