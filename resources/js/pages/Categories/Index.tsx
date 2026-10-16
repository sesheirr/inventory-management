/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Categories Directory (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { Tags } from 'lucide-react';

export interface CategoryItem {
    id: number;
    name: string;
    description?: string | null;
    products_count?: number;
}

export interface CategoriesIndexProps {
    categories?: CategoryItem[];
}

export default function CategoriesIndex({ categories = [] }: CategoriesIndexProps) {
    return (
        <AppLayout
            title="Klasifikasi Kategori Barang"
            subtitle="Kelompokkan perangkat TIK berdasarkan jenis fungsi dan spesifikasi teknis."
        >
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {categories.map((cat) => (
                    <a
                        key={cat.id}
                        href={`/categories/${cat.id}`}
                        className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-2xs hover:border-blue-500/50 transition-all space-y-2"
                    >
                        <Tags className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        <div className="text-base font-bold text-slate-900 dark:text-white">
                            {cat.name}
                        </div>
                        <p className="text-xs text-slate-500 line-clamp-2">
                            {cat.description ?? 'Kategori perangkat inventaris Diskominfo Garut.'}
                        </p>
                    </a>
                ))}
            </div>
        </AppLayout>
    );
}
