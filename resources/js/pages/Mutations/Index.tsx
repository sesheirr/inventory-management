/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Mutations Ledger (React 19 + Inertia v2)
 *
 * Uses `<SwipeableRow />` for 60fps mobile swipe approval/rejection and fixed
 * equal-dimension `w-9 h-9` icon-only Approve/Reject buttons.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { SwipeableRow } from '../../components/ui/SwipeableRow';
import { ArrowRightLeft, Check, X, Eye, Plus } from 'lucide-react';

export interface MutationRow {
    id: number;
    jenis_mutasi: string;
    jumlah: number;
    tanggal_mutasi: string;
    status: 'pending' | 'approved' | 'rejected';
    keterangan?: string | null;
    product?: {
        id: number;
        name: string;
        kode_barang?: string | null;
    };
    from_room?: { name: string } | null;
    to_room?: { name: string } | null;
}

export interface MutationsIndexProps {
    mutations?: {
        data?: MutationRow[];
    };
    csrfToken?: string;
    isAdmin?: boolean;
}

export default function MutationsIndex({
    mutations,
    csrfToken = '',
    isAdmin = false,
}: MutationsIndexProps) {
    const items = mutations?.data ?? [];

    return (
        <AppLayout
            title="Riwayat & Persetujuan Mutasi Barang"
            subtitle="Alur perpindahan perangkat antar ruangan dengan pelacakan status berjenjang."
        >
            <div className="mb-5 flex justify-end">
                <a
                    href="/mutations/create"
                    className="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-bold text-white shadow-sm"
                >
                    <Plus className="h-4 w-4" />
                    <span>Ajukan Mutasi</span>
                </a>
            </div>

            <div className="space-y-3">
                {items.map((m) => (
                    <SwipeableRow
                        key={m.id}
                        leftLabel="Tolak Mutasi"
                        rightLabel="Setujui Mutasi"
                    >
                        <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 p-4 flex items-center justify-between gap-4">
                            <div className="min-w-0">
                                <div className="flex items-center gap-2">
                                    <ArrowRightLeft className="h-4 w-4 text-blue-600 dark:text-blue-400 shrink-0" />
                                    <span className="text-sm font-bold text-slate-900 dark:text-white truncate">
                                        {m.product?.name ?? 'Barang Inventaris'}
                                    </span>
                                    <span className="rounded-full bg-slate-100 dark:bg-slate-800 px-2 py-0.5 text-[10px] font-bold uppercase">
                                        {m.status}
                                    </span>
                                </div>
                                <div className="mt-1 text-xs text-slate-500">
                                    {m.from_room?.name ?? 'Gudang'} → {m.to_room?.name ?? 'Tujuan'} •{' '}
                                    {m.jumlah} unit • {m.tanggal_mutasi}
                                </div>
                            </div>

                            <div className="flex items-center gap-1.5 shrink-0">
                                {isAdmin && m.status === 'pending' && (
                                    <>
                                        <form action={`/mutations/${m.id}/approve`} method="POST">
                                            <input type="hidden" name="_token" value={csrfToken} />
                                            <input type="hidden" name="_method" value="PATCH" />
                                            <button
                                                type="submit"
                                                title="Setujui Mutasi"
                                                aria-label="Setujui Mutasi"
                                                className="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs cursor-pointer"
                                            >
                                                <Check className="h-4 w-4" />
                                            </button>
                                        </form>
                                        <form action={`/mutations/${m.id}/reject`} method="POST">
                                            <input type="hidden" name="_token" value={csrfToken} />
                                            <input type="hidden" name="_method" value="PATCH" />
                                            <button
                                                type="submit"
                                                title="Tolak Mutasi"
                                                aria-label="Tolak Mutasi"
                                                className="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-600 hover:bg-rose-700 text-white shadow-xs cursor-pointer"
                                            >
                                                <X className="h-4 w-4" />
                                            </button>
                                        </form>
                                    </>
                                )}
                                <a
                                    href={`/mutations/${m.id}`}
                                    title="Detail Transaksi Mutasi"
                                    className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300"
                                >
                                    <Eye className="h-4 w-4" />
                                </a>
                            </div>
                        </div>
                    </SwipeableRow>
                ))}
            </div>
        </AppLayout>
    );
}
