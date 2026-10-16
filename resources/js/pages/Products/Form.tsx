/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Product Create/Edit Form (React 19 + Inertia v2)
 *
 * Embeds Filament-grade `<MultiMediaUploader />` (FilePond + Spatie MediaLibrary)
 * with instant multi-image thumbnail previews and drag-and-drop reorder.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { MultiMediaUploader } from '../../components/upload/MultiMediaUploader';
import { Save } from 'lucide-react';

export interface ProductFormProps {
    mode?: 'create' | 'edit';
    product?: {
        id?: number;
        name?: string;
        kode_barang?: string;
        barcode?: string;
        stock?: number;
        status?: string;
        gallery_urls?: string[];
    };
    csrfToken?: string;
}

export default function ProductForm({
    mode = 'create',
    product,
    csrfToken = '',
}: ProductFormProps) {
    const isEdit = mode === 'edit' && product?.id;
    const actionUrl = isEdit ? `/products/${product.id}` : '/products';

    return (
        <AppLayout
            title={isEdit ? `Edit Barang: ${product?.name}` : 'Tambah Barang Baru'}
            subtitle="Unggah beberapa foto perangkat sekaligus menggunakan FilePond Multi-Media Uploader."
        >
            <form
                action={actionUrl}
                method="POST"
                encType="multipart/form-data"
                className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs space-y-5 max-w-3xl"
            >
                <input type="hidden" name="_token" value={csrfToken} />
                {isEdit && <input type="hidden" name="_method" value="PUT" />}

                <div>
                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Nama Barang / Perangkat TIK
                    </label>
                    <input
                        type="text"
                        name="name"
                        defaultValue={product?.name ?? ''}
                        required
                        className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500"
                    />
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Jumlah Stok
                        </label>
                        <input
                            type="number"
                            name="stock"
                            min={0}
                            defaultValue={product?.stock ?? 1}
                            required
                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Status Operasional
                        </label>
                        <select
                            name="status"
                            defaultValue={product?.status ?? 'active'}
                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500"
                        >
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="out_of_stock">Stok Habis</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
                        Foto & Galeri Barang (Multi-Upload FilePond)
                    </label>
                    <MultiMediaUploader
                        allowMultiple
                        maxFiles={8}
                        initialGalleryUrls={product?.gallery_urls ?? []}
                        csrfToken={csrfToken}
                    />
                </div>

                <div className="flex justify-end">
                    <button
                        type="submit"
                        className="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-5 py-2.5 text-xs font-bold text-white shadow-md cursor-pointer"
                    >
                        <Save className="h-4 w-4" />
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </AppLayout>
    );
}
