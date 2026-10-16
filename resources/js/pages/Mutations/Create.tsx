/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Mutation Create Wizard (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { Send } from 'lucide-react';

export interface MutationCreateProps {
    csrfToken?: string;
}

export default function MutationCreate({ csrfToken = '' }: MutationCreateProps) {
    return (
        <AppLayout
            title="Formulir Pengajuan Mutasi Barang"
            subtitle="Catat perpindahan ruangan, barang masuk, atau barang keluar secara terstruktur."
        >
            <form
                action="/mutations"
                method="POST"
                className="max-w-2xl rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs space-y-4"
            >
                <input type="hidden" name="_token" value={csrfToken} />
                <div>
                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Tanggal Mutasi
                    </label>
                    <input
                        type="date"
                        name="tanggal_mutasi"
                        defaultValue={new Date().toISOString().slice(0, 10)}
                        required
                        className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 text-sm outline-none"
                    />
                </div>
                <div className="flex justify-end">
                    <button
                        type="submit"
                        className="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-5 py-2.5 text-xs font-bold text-white cursor-pointer"
                    >
                        <Send className="h-4 w-4" />
                        <span>Simpan Pengajuan Mutasi</span>
                    </button>
                </div>
            </form>
        </AppLayout>
    );
}
