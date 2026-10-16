/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Logistics-Grade Mutation Waybill (React 19 + Inertia v2)
 *
 * Renders a 4-stage visual route tracker (Pengajuan -> Verifikasi Admin ->
 * Perpindahan Fisik -> Selesai) with Origin -> Destination waybill cards.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { Check, X, MapPin, ArrowRight, CheckCircle2, Clock } from 'lucide-react';

export interface MutationShowProps {
    mutation: {
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
    };
    csrfToken?: string;
    isAdmin?: boolean;
}

export default function MutationShow({
    mutation,
    csrfToken = '',
    isAdmin = false,
}: MutationShowProps) {
    const stages = [
        { step: 1, title: 'Pengajuan Tercatat', done: true },
        { step: 2, title: 'Verifikasi Admin', done: mutation.status !== 'pending' },
        { step: 3, title: 'Perpindahan Lokasi', done: mutation.status === 'approved' },
        { step: 4, title: 'Selesai & Tersinkron', done: mutation.status === 'approved' },
    ];

    return (
        <AppLayout
            title={`Detail Transaksi Mutasi #MT-${String(mutation.id).padStart(5, '0')}`}
            subtitle={`Perangkat: ${mutation.product?.name ?? '-'} • Jumlah: ${mutation.jumlah} unit`}
        >
            <div className="space-y-6">
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs">
                    <div className="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        {stages.map((st) => (
                            <div
                                key={st.step}
                                className={`rounded-xl border p-3.5 flex items-center gap-3 ${
                                    st.done
                                        ? 'border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/40 dark:bg-emerald-950/30'
                                        : 'border-slate-200 dark:border-slate-800'
                                }`}
                            >
                                {st.done ? (
                                    <CheckCircle2 className="h-5 w-5 text-emerald-600 shrink-0" />
                                ) : (
                                    <Clock className="h-5 w-5 text-amber-500 shrink-0" />
                                )}
                                <div>
                                    <div className="text-[10px] font-bold uppercase text-slate-400">
                                        Tahap {st.step}
                                    </div>
                                    <div className="text-xs font-bold text-slate-900 dark:text-white">
                                        {st.title}
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-6 grid grid-cols-1 sm:grid-cols-3 items-center gap-4 rounded-2xl bg-slate-50 dark:bg-slate-800/50 p-4">
                        <div className="flex items-center gap-2.5">
                            <MapPin className="h-5 w-5 text-slate-500" />
                            <div>
                                <div className="text-[10px] font-bold uppercase text-slate-400">
                                    Lokasi Asal
                                </div>
                                <div className="text-sm font-bold">
                                    {mutation.from_room?.name ?? 'Gudang Utama'}
                                </div>
                            </div>
                        </div>
                        <div className="flex justify-center">
                            <ArrowRight className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div className="flex items-center gap-2.5">
                            <MapPin className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            <div>
                                <div className="text-[10px] font-bold uppercase text-slate-400">
                                    Lokasi Tujuan
                                </div>
                                <div className="text-sm font-bold">
                                    {mutation.to_room?.name ?? 'Ruang Tujuan'}
                                </div>
                            </div>
                        </div>
                    </div>

                    {isAdmin && mutation.status === 'pending' && (
                        <div className="mt-5 flex items-center justify-end gap-2">
                            <form action={`/mutations/${mutation.id}/approve`} method="POST">
                                <input type="hidden" name="_token" value={csrfToken} />
                                <input type="hidden" name="_method" value="PATCH" />
                                <button
                                    type="submit"
                                    title="Setujui Mutasi"
                                    className="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer"
                                >
                                    <Check className="h-4 w-4" />
                                </button>
                            </form>
                            <form action={`/mutations/${mutation.id}/reject`} method="POST">
                                <input type="hidden" name="_token" value={csrfToken} />
                                <input type="hidden" name="_method" value="PATCH" />
                                <button
                                    type="submit"
                                    title="Tolak Mutasi"
                                    className="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-600 hover:bg-rose-700 text-white cursor-pointer"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
