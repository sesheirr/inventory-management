/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Products Catalog (React 19 + Inertia v2)
 *
 * Uses `useCatalogStore` for persistent List/Grid view switching, instant search/filter,
 * dynamic active KPI filter cards, and Vaul bottom-sheet quick preview on mobile.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 * @collaborator msayib <https://github.com/msayib> — Ideation, System Design, Architecture, Performance, Refactor, Tune-Up, UI/UX & Mobile-First High-Level Engineering
 */

import React, { useMemo, useState } from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { useCatalogStore } from '../../stores/useCatalogStore';
import { ResponsiveModal } from '../../components/ui/ResponsiveModal';
import { LayoutList, LayoutGrid, Plus, Search, QrCode, Eye, RotateCcw } from 'lucide-react';

export interface ProductRow {
    id: number;
    name: string;
    kode_barang?: string | null;
    barcode?: string | null;
    stock: number;
    status: string;
    category_name?: string;
    room_name?: string | null;
    image_url?: string | null;
}

export interface ProductsIndexProps {
    products?: {
        data?: ProductRow[];
    };
    summary?: {
        total?: number;
        active?: number;
        low_stock?: number;
        out_of_stock?: number;
    };
}

export default function ProductsIndex({ products, summary }: ProductsIndexProps) {
    const { viewMode, setViewMode, searchQuery, setSearchQuery } = useCatalogStore();
    const [activeProduct, setActiveProduct] = useState<ProductRow | null>(null);
    const [activeFilterCard, setActiveFilterCard] = useState<'all' | 'active' | 'low_stock' | 'out_of_stock' | null>(null);

    const items = useMemo(() => {
        const raw = products?.data ?? [];
        return raw.filter((item) => {
            if (searchQuery.trim()) {
                const q = searchQuery.toLowerCase();
                const match =
                    item.name.toLowerCase().includes(q) ||
                    (item.kode_barang ?? '').toLowerCase().includes(q) ||
                    (item.barcode ?? '').toLowerCase().includes(q) ||
                    (item.room_name ?? '').toLowerCase().includes(q);
                if (!match) return false;
            }
            if (activeFilterCard === 'active') return item.status === 'active' && item.stock > 0;
            if (activeFilterCard === 'low_stock') return item.stock > 0 && item.stock <= 5;
            if (activeFilterCard === 'out_of_stock') return item.status === 'out_of_stock' || item.stock <= 0;
            return true;
        });
    }, [products, searchQuery, activeFilterCard]);

    const handleResetFilters = () => {
        setSearchQuery('');
        setActiveFilterCard(null);
    };

    return (
        <AppLayout
            title="Katalog Barang & Perangkat TIK"
            subtitle="Kelola inventaris perangkat dengan tampilan List/Grid adaptif dan pratinjau cepat."
        >
            <div className="flex flex-wrap items-center justify-between gap-3 mb-5">
                <div className="flex items-center gap-2 flex-1 min-w-[220px] max-w-lg">
                    <div className="relative flex-1">
                        <Search className="h-4 w-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                        <input
                            type="search"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            placeholder="Cari nama barang, kode, barcode, ruangan..."
                            className="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 pl-10 pr-4 py-2 text-xs sm:text-sm outline-none focus:border-blue-500"
                        />
                    </div>
                    {(activeFilterCard !== null || searchQuery.trim() !== '') && (
                        <button
                            type="button"
                            onClick={handleResetFilters}
                            className="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 hover:border-blue-400"
                        >
                            <RotateCcw className="h-3.5 w-3.5" />
                            <span>Reset</span>
                        </button>
                    )}
                </div>

                <div className="flex items-center gap-2">
                    <div className="inline-flex rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-1">
                        <button
                            type="button"
                            data-product-view-btn="list"
                            onClick={() => setViewMode('list')}
                            className={`inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold cursor-pointer ${
                                viewMode === 'list'
                                    ? 'bg-blue-600 text-white'
                                    : 'text-slate-600 dark:text-slate-400'
                            }`}
                        >
                            <LayoutList className="h-3.5 w-3.5" />
                            <span>List</span>
                        </button>
                        <button
                            type="button"
                            data-product-view-btn="grid"
                            onClick={() => setViewMode('grid')}
                            className={`inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold cursor-pointer ${
                                viewMode === 'grid'
                                    ? 'bg-blue-600 text-white'
                                    : 'text-slate-600 dark:text-slate-400'
                            }`}
                        >
                            <LayoutGrid className="h-3.5 w-3.5" />
                            <span>Grid</span>
                        </button>
                    </div>

                    <a
                        href="/products/create"
                        className="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 px-3.5 py-2 text-xs font-bold text-white shadow-sm"
                    >
                        <Plus className="h-4 w-4" />
                        <span>Tambah Barang</span>
                    </a>
                </div>
            </div>

            {summary && (
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                    <button
                        type="button"
                        data-quick-status="all"
                        data-card-active={activeFilterCard === 'all' ? 'true' : 'false'}
                        onClick={() => setActiveFilterCard((prev) => (prev === 'all' ? null : 'all'))}
                        className={`quick-status-card text-left rounded-2xl border p-3.5 transition-all ${
                            activeFilterCard === 'all'
                                ? 'border-blue-500 ring-2 ring-blue-500/25 bg-blue-50/40 dark:bg-blue-950/25'
                                : 'border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900'
                        }`}
                    >
                        <div className="flex items-center justify-between text-[11px] font-bold text-slate-500">
                            <span>Total Item</span>
                            {activeFilterCard === 'all' && <span className="h-2 w-2 rounded-full bg-blue-500 animate-pulse" />}
                        </div>
                        <div className="text-lg font-extrabold mt-0.5 tabular-nums">{summary.total ?? 0}</div>
                    </button>

                    <button
                        type="button"
                        data-quick-status="active"
                        data-card-active={activeFilterCard === 'active' ? 'true' : 'false'}
                        onClick={() => setActiveFilterCard((prev) => (prev === 'active' ? null : 'active'))}
                        className={`quick-status-card text-left rounded-2xl border p-3.5 transition-all ${
                            activeFilterCard === 'active'
                                ? 'border-emerald-500 ring-2 ring-emerald-500/25 bg-emerald-50/40 dark:bg-emerald-950/25'
                                : 'border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900'
                        }`}
                    >
                        <div className="flex items-center justify-between text-[11px] font-bold text-slate-500">
                            <span>Aktif</span>
                            {activeFilterCard === 'active' && <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />}
                        </div>
                        <div className="text-lg font-extrabold text-emerald-600 mt-0.5 tabular-nums">{summary.active ?? 0}</div>
                    </button>

                    <button
                        type="button"
                        data-quick-status="low_stock"
                        data-card-active={activeFilterCard === 'low_stock' ? 'true' : 'false'}
                        onClick={() => setActiveFilterCard((prev) => (prev === 'low_stock' ? null : 'low_stock'))}
                        className={`quick-status-card text-left rounded-2xl border p-3.5 transition-all ${
                            activeFilterCard === 'low_stock'
                                ? 'border-amber-500 ring-2 ring-amber-500/25 bg-amber-50/40 dark:bg-amber-950/25'
                                : 'border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900'
                        }`}
                    >
                        <div className="flex items-center justify-between text-[11px] font-bold text-slate-500">
                            <span>Stok Menipis</span>
                            {activeFilterCard === 'low_stock' && <span className="h-2 w-2 rounded-full bg-amber-500 animate-pulse" />}
                        </div>
                        <div className="text-lg font-extrabold text-amber-500 mt-0.5 tabular-nums">{summary.low_stock ?? 0}</div>
                    </button>

                    <button
                        type="button"
                        data-quick-status="out_of_stock"
                        data-card-active={activeFilterCard === 'out_of_stock' ? 'true' : 'false'}
                        onClick={() => setActiveFilterCard((prev) => (prev === 'out_of_stock' ? null : 'out_of_stock'))}
                        className={`quick-status-card text-left rounded-2xl border p-3.5 transition-all ${
                            activeFilterCard === 'out_of_stock'
                                ? 'border-rose-500 ring-2 ring-rose-500/25 bg-rose-50/40 dark:bg-rose-950/25'
                                : 'border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900'
                        }`}
                    >
                        <div className="flex items-center justify-between text-[11px] font-bold text-slate-500">
                            <span>Stok Habis</span>
                            {activeFilterCard === 'out_of_stock' && <span className="h-2 w-2 rounded-full bg-rose-500 animate-pulse" />}
                        </div>
                        <div className="text-lg font-extrabold text-rose-500 mt-0.5 tabular-nums">{summary.out_of_stock ?? 0}</div>
                    </button>
                </div>
            )}

            <div
                className={
                    viewMode === 'grid'
                        ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4'
                        : 'space-y-2.5'
                }
            >
                {items.map((item) => (
                    <div
                        key={item.id}
                        className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 flex items-center justify-between gap-4 shadow-2xs"
                    >
                        <div className="min-w-0">
                            <div className="text-sm font-bold text-slate-900 dark:text-white truncate">
                                {item.name}
                            </div>
                            <div className="mt-0.5 flex items-center gap-2 text-xs text-slate-500">
                                <span className="font-mono">{item.kode_barang ?? item.barcode}</span>
                                <span>•</span>
                                <span>Stok: {item.stock}</span>
                            </div>
                        </div>
                        <div className="flex items-center gap-1.5 shrink-0">
                            <button
                                type="button"
                                onClick={() => setActiveProduct(item)}
                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 cursor-pointer"
                                title="Pratinjau Cepat"
                            >
                                <Eye className="h-4 w-4" />
                            </button>
                            <a
                                href={`/products/${item.id}`}
                                className="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400"
                                title="Detail"
                            >
                                <QrCode className="h-4 w-4" />
                            </a>
                        </div>
                    </div>
                ))}
            </div>

            <ResponsiveModal
                open={activeProduct !== null}
                onOpenChange={(open) => !open && setActiveProduct(null)}
                title={activeProduct?.name ?? 'Pratinjau Barang'}
                description={`Kode: ${activeProduct?.kode_barang ?? '-'}`}
            >
                {activeProduct && (
                    <div className="space-y-3 text-xs">
                        <div className="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                            <span className="text-slate-500">Stok Tersedia</span>
                            <span className="font-bold">{activeProduct.stock} unit</span>
                        </div>
                        <div className="flex justify-between py-1.5 border-b border-slate-100 dark:border-slate-800">
                            <span className="text-slate-500">Barcode</span>
                            <span className="font-mono font-bold">{activeProduct.barcode ?? '-'}</span>
                        </div>
                    </div>
                )}
            </ResponsiveModal>
        </AppLayout>
    );
}
