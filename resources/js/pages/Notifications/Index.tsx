/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Notifications Center (React 19 + Inertia v2)
 *
 * Uses `<SwipeableRow />` for 60fps mobile swipe-to-mark-read / swipe-to-dismiss,
 * translucent unread badge (`bg-blue-500/15`), and full-card click navigation
 * without a separate "Buka" button.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { SwipeableRow } from '../../components/ui/SwipeableRow';
import { Bell } from 'lucide-react';

export interface NotificationRow {
    id: string;
    title: string;
    message: string;
    read_at?: string | null;
    created_at_human?: string;
}

export interface NotificationsIndexProps {
    notifications?: NotificationRow[];
}

export default function NotificationsIndex({ notifications = [] }: NotificationsIndexProps) {
    return (
        <AppLayout
            title="Pusat Notifikasi"
            subtitle="Geser ke kiri/kanan pada mobile untuk menandai sudah dibaca, atau ketuk kartu notifikasi untuk menuju objek terkait."
        >
            <div className="space-y-3 max-w-3xl">
                {notifications.map((n) => (
                    <SwipeableRow
                        key={n.id}
                        leftLabel="Tandai Dibaca"
                        rightLabel="Selesai"
                    >
                        <a
                            href={`/notifications/${n.id}/go`}
                            className="block rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 hover:border-blue-500/40 transition-all"
                        >
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex items-start gap-3">
                                    <Bell className="h-4 w-4 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" />
                                    <div>
                                        <div className="text-sm font-bold text-slate-900 dark:text-white">
                                            {n.title}
                                        </div>
                                        <p className="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                            {n.message}
                                        </p>
                                    </div>
                                </div>
                                {!n.read_at && (
                                    <span className="rounded-full bg-blue-500/15 text-blue-600 dark:text-blue-300 border border-blue-500/25 backdrop-blur-md px-2.5 py-0.5 text-[10px] font-bold">
                                        Baru
                                    </span>
                                )}
                            </div>
                        </a>
                    </SwipeableRow>
                ))}
            </div>
        </AppLayout>
    );
}
