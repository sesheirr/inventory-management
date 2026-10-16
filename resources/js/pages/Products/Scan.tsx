/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Barcode/QR Scanner Page (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React, { useState } from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { QrCode, Search } from 'lucide-react';

export default function ProductScan() {
    const [code, setCode] = useState<string>('');

    return (
        <AppLayout
            title="Pindai Barcode & QR Aset TIK"
            subtitle="Verifikasi cepat identitas perangkat menggunakan kamera atau pemindai kode inventaris."
        >
            <div className="mx-auto max-w-xl rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs space-y-4">
                <div className="flex items-center gap-3">
                    <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                        <QrCode className="h-6 w-6" />
                    </div>
                    <div>
                        <h2 className="text-sm font-bold text-slate-900 dark:text-white">
                            Pencarian Cepat Kode Barcode / Kode Barang
                        </h2>
                        <p className="text-xs text-slate-500">
                            Ketik atau scan kode inventaris untuk membuka detail perangkat.
                        </p>
                    </div>
                </div>

                <form action="/scan-barcode/cari" method="POST" className="flex gap-2">
                    <input
                        type="text"
                        name="barcode"
                        value={code}
                        onChange={(e) => setCode(e.target.value.toUpperCase())}
                        placeholder="Contoh: INV-2026-0001 atau BRG-XXXXXX"
                        className="flex-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 font-mono text-sm uppercase outline-none focus:border-blue-500"
                    />
                    <button
                        type="submit"
                        className="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-xs font-bold text-white cursor-pointer"
                    >
                        <Search className="h-4 w-4" />
                        <span>Cari</span>
                    </button>
                </form>
            </div>
        </AppLayout>
    );
}
