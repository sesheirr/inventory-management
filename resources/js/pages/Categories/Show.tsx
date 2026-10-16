/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Category Detail Page (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { Tags } from 'lucide-react';

export interface CategoryShowProps {
    category: {
        id: number;
        name: string;
        description?: string | null;
    };
}

export default function CategoryShow({ category }: CategoryShowProps) {
    return (
        <AppLayout
            title={`Kategori: ${category.name}`}
            subtitle={category.description ?? 'Daftar barang dalam kategori ini.'}
        >
            <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs flex items-center gap-3">
                <Tags className="h-6 w-6 text-blue-600 dark:text-blue-400" />
                <div>
                    <h2 className="text-base font-extrabold text-slate-900 dark:text-white">
                        {category.name}
                    </h2>
                    <p className="text-xs text-slate-500">
                        {category.description ?? 'Klasifikasi aset TIK Diskominfo Kabupaten Garut.'}
                    </p>
                </div>
            </div>
        </AppLayout>
    );
}
