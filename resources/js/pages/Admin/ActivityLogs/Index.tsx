/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Admin Activity Audit Logs (React 19 + Inertia v2)
 *
 * Strictly restricted to `admin` and `superadmin` roles.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../../layouts/AppLayout';
import { History } from 'lucide-react';

export interface ActivityLogItem {
    id: number;
    action: string;
    description: string;
    created_at?: string;
}

export interface AdminActivityLogsIndexProps {
    logs?: {
        data?: ActivityLogItem[];
    };
}

export default function AdminActivityLogsIndex({ logs }: AdminActivityLogsIndexProps) {
    const items = logs?.data ?? [];

    return (
        <AppLayout
            title="Log Audit Aktivitas Sistem"
            subtitle="Rekam jejak seluruh perubahan data barang, ruangan, kategori, dan persetujuan mutasi."
        >
            <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs space-y-3">
                <div className="flex items-center gap-2 text-sm font-bold">
                    <History className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    <span>Log Aktivitas Terbaru</span>
                </div>
                <div className="space-y-2">
                    {items.map((log) => (
                        <div
                            key={log.id}
                            className="rounded-xl border border-slate-100 dark:border-slate-800 p-3 text-xs flex items-center justify-between"
                        >
                            <span className="font-medium text-slate-800 dark:text-slate-200">
                                {log.description}
                            </span>
                            <span className="font-mono text-slate-400">{log.action}</span>
                        </div>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
