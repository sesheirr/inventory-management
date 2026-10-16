/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Product Detail & Multi-Gallery (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React, { useState } from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { QrCode, ArrowRightLeft, Edit3 } from 'lucide-react';

export interface ProductShowProps {
    product: {
        id: number;
        name: string;
        kode_barang?: string | null;
        barcode?: string | null;
        stock: number;
        status_label?: string;
        category_name?: string;
        room_name?: string | null;
        image_url?: string | null;
        gallery_urls?: string[];
    };
}

export default function ProductShow({ product }: ProductShowProps) {
    const gallery = product.gallery_urls && product.gallery_urls.length > 0
        ? product.gallery_urls
        : product.image_url
          ? [product.image_url]
          : [];

    const [activeImg, setActiveImg] = useState<string | null>(gallery[0] ?? null);

    return (
        <AppLayout title={product.name} subtitle={`Kode Inventaris: ${product.kode_barang ?? '-'}`}>
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 space-y-4">
                    <div className="aspect-square rounded-xl bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center overflow-hidden">
                        {activeImg ? (
                            <img src={activeImg} alt={product.name} className="h-full w-full object-contain" />
                        ) : (
                            <QrCode className="h-16 w-16 text-slate-400" />
                        )}
                    </div>
                    {gallery.length > 1 && (
                        <div className="grid grid-cols-4 gap-2">
                            {gallery.map((url, idx) => (
                                <button
                                    key={`${url}-${idx}`}
                                    type="button"
                                    onClick={() => setActiveImg(url)}
                                    className={`aspect-square rounded-lg overflow-hidden border-2 cursor-pointer ${
                                        activeImg === url
                                            ? 'border-blue-600'
                                            : 'border-slate-200 dark:border-slate-700'
                                    }`}
                                >
                                    <img src={url} alt={`Galeri ${idx + 1}`} className="h-full w-full object-cover" />
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                <div className="lg:col-span-2 rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 space-y-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 className="text-lg font-extrabold text-slate-900 dark:text-white">
                                Spesifikasi & Lokasi Aset
                            </h2>
                            <p className="text-xs text-slate-500">
                                Barcode: <span className="font-mono font-bold">{product.barcode ?? '-'}</span>
                            </p>
                        </div>
                        <div className="flex items-center gap-2">
                            <a
                                href={`/mutations/create?product_id=${product.id}`}
                                className="inline-flex items-center gap-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 px-3.5 py-2 text-xs font-bold text-indigo-700 dark:text-indigo-300"
                            >
                                <ArrowRightLeft className="h-3.5 w-3.5" />
                                <span>Mutasi</span>
                            </a>
                            <a
                                href={`/products/${product.id}/edit`}
                                className="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-2 text-xs font-bold text-white"
                            >
                                <Edit3 className="h-3.5 w-3.5" />
                                <span>Edit</span>
                            </a>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4 pt-2 text-xs">
                        <div className="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3.5">
                            <div className="text-slate-500">Kategori</div>
                            <div className="mt-1 font-bold text-sm">{product.category_name ?? 'Umum'}</div>
                        </div>
                        <div className="rounded-xl bg-slate-50 dark:bg-slate-800/50 p-3.5">
                            <div className="text-slate-500">Lokasi Ruangan</div>
                            <div className="mt-1 font-bold text-sm">{product.room_name ?? 'Belum Ditetapkan'}</div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
