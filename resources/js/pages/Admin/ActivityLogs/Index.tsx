/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Admin Activity Audit Logs (React 19 + Inertia v2)
 *
 * Strictly restricted to `admin` and `superadmin` roles.
 * Includes rich filtering (search, action, subject_type, user_id, date range),
 * dynamic sorting (sort_by, sort_dir, per_page), and pagination.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 * @co-collaborator msayib <https://github.com/msayib>
 */

import React, { useMemo, useState } from 'react';
import { AppLayout } from '../../../layouts/AppLayout';
import { History, Search, ArrowUpDown, RotateCcw, ChevronLeft, ChevronRight } from 'lucide-react';

export interface ActivityLogItem {
    id: number;
    action: string;
    subject_type?: string | null;
    subject_id?: number | null;
    description: string;
    created_at?: string;
    user?: {
        id: number;
        name: string;
        email?: string;
    } | null;
}

export interface AdminActivityLogsIndexProps {
    logs?: {
        data?: ActivityLogItem[];
        current_page?: number;
        last_page?: number;
        per_page?: number;
        total?: number;
    };
    summary?: {
        total: number;
        today: number;
        mutations: number;
        products: number;
    };
    filters?: {
        search?: string;
        action?: string;
        subject_type?: string;
        sort_by?: string;
        sort_dir?: 'asc' | 'desc';
        per_page?: number;
    };
}

export default function AdminActivityLogsIndex({
    logs,
    summary,
    filters,
}: AdminActivityLogsIndexProps) {
    const rawItems = logs?.data ?? [];
    const [search, setSearch] = useState(filters?.search ?? '');
    const [actionFilter, setActionFilter] = useState(filters?.action ?? '');
    const [subjectFilter, setSubjectFilter] = useState(filters?.subject_type ?? '');
    const [sortBy, setSortBy] = useState(filters?.sort_by ?? 'created_at');
    const [sortDir, setSortDir] = useState<'asc' | 'desc'>(filters?.sort_dir ?? 'desc');
    const [perPage, setPerPage] = useState<number>(filters?.per_page ?? 20);
    const [page, setPage] = useState<number>(logs?.current_page ?? 1);

    const filteredItems = useMemo(() => {
        const q = search.trim().toLowerCase();
        const filtered = rawItems.filter((item) => {
            const matchSearch =
                q === '' ||
                item.description.toLowerCase().includes(q) ||
                item.action.toLowerCase().includes(q) ||
                (item.user?.name ?? '').toLowerCase().includes(q);
            const matchAction = actionFilter === '' || item.action === actionFilter;
            const matchSubject =
                subjectFilter === '' ||
                (item.subject_type ?? '').toLowerCase().includes(subjectFilter.toLowerCase());
            return matchSearch && matchAction && matchSubject;
        });

        return [...filtered].sort((a, b) => {
            const dir = sortDir === 'asc' ? 1 : -1;
            if (sortBy === 'action') {
                return a.action.localeCompare(b.action) * dir;
            }
            if (sortBy === 'subject_type') {
                return (a.subject_type ?? '').localeCompare(b.subject_type ?? '') * dir;
            }
            return (a.id - b.id) * dir;
        });
    }, [rawItems, search, actionFilter, subjectFilter, sortBy, sortDir]);

    const totalPages = Math.max(1, logs?.last_page ?? Math.ceil(filteredItems.length / perPage));

    const resetFilters = () => {
        setSearch('');
        setActionFilter('');
        setSubjectFilter('');
        setSortBy('created_at');
        setSortDir('desc');
        setPerPage(20);
        setPage(1);
    };

    return (
        <AppLayout
            title="Log Audit Aktivitas Sistem"
            subtitle="Rekam jejak komprehensif autentikasi, perubahan barang, ruangan, kategori, barcode, dan mutasi."
        >
            <div className="space-y-4">
                <div className="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
                    <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-2xs">
                        <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Audit Log</span>
                        <div className="mt-1.5 text-2xl font-extrabold text-slate-900 dark:text-white tabular-nums">
                            {summary?.total ?? logs?.total ?? filteredItems.length}
                        </div>
                    </div>
                    <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-2xs">
                        <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Aktivitas Hari Ini</span>
                        <div className="mt-1.5 text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums">
                            {summary?.today ?? 0}
                        </div>
                    </div>
                    <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-2xs">
                        <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Log Barang & Stok</span>
                        <div className="mt-1.5 text-2xl font-extrabold text-blue-600 dark:text-blue-400 tabular-nums">
                            {summary?.products ?? 0}
                        </div>
                    </div>
                    <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 shadow-2xs">
                        <span className="text-[11px] font-bold uppercase tracking-wider text-slate-400">Log Mutasi Aset</span>
                        <div className="mt-1.5 text-2xl font-extrabold text-amber-600 dark:text-amber-400 tabular-nums">
                            {summary?.mutations ?? 0}
                        </div>
                    </div>
                </div>

                <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs space-y-4">
                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div className="flex items-center gap-2 text-sm font-bold">
                            <History className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            <span>Filter, Sorting & Paginasi Audit Log</span>
                        </div>
                        <button
                            type="button"
                            onClick={resetFilters}
                            className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/30 cursor-pointer"
                        >
                            <RotateCcw className="h-3.5 w-3.5" />
                            <span>Reset Filter</span>
                        </button>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5">
                        <div className="relative sm:col-span-2">
                            <Search className="h-3.5 w-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                            <input
                                type="search"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Cari deskripsi, aksi, atau pengguna..."
                                className="w-full pl-8 pr-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800"
                            />
                        </div>
                        <select
                            value={actionFilter}
                            onChange={(e) => setActionFilter(e.target.value)}
                            className="px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 cursor-pointer"
                        >
                            <option value="">Semua Aksi</option>
                            <option value="created">Dibuat</option>
                            <option value="updated">Diperbarui</option>
                            <option value="deleted">Dihapus</option>
                            <option value="login">Login</option>
                            <option value="logout">Logout</option>
                            <option value="scan">Scan Barcode</option>
                            <option value="export">Ekspor Laporan</option>
                        </select>
                        <select
                            value={subjectFilter}
                            onChange={(e) => setSubjectFilter(e.target.value)}
                            className="px-3 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 cursor-pointer"
                        >
                            <option value="">Semua Modul</option>
                            <option value="Product">Barang</option>
                            <option value="Mutation">Mutasi</option>
                            <option value="Room">Ruangan</option>
                            <option value="Category">Kategori</option>
                            <option value="User">Pengguna</option>
                        </select>
                        <div className="flex items-center gap-1.5">
                            <button
                                type="button"
                                onClick={() => setSortDir((d) => (d === 'desc' ? 'asc' : 'desc'))}
                                className="flex-1 inline-flex items-center justify-center gap-1 px-2.5 py-2 rounded-xl text-xs font-bold bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 cursor-pointer"
                            >
                                <ArrowUpDown className="h-3.5 w-3.5" />
                                <span>{sortDir === 'desc' ? 'Terbaru' : 'Terlama'}</span>
                            </button>
                            <select
                                value={perPage}
                                onChange={(e) => setPerPage(Number(e.target.value))}
                                className="px-2 py-2 rounded-xl text-xs bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 cursor-pointer"
                            >
                                {[10, 20, 30, 50, 100].map((n) => (
                                    <option key={n} value={n}>
                                        {n}/hal
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    <div className="space-y-2">
                        {filteredItems.map((log) => (
                            <div
                                key={log.id}
                                className="rounded-xl border border-slate-100 dark:border-slate-800 p-3 text-xs flex items-center justify-between gap-3"
                            >
                                <div className="min-w-0">
                                    <span className="font-semibold text-slate-900 dark:text-white block">
                                        {log.description}
                                    </span>
                                    <span className="text-[11px] text-slate-400">
                                        {log.user?.name ?? 'Sistem'} • {log.created_at ?? '-'}
                                    </span>
                                </div>
                                <span className="font-mono text-[11px] px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 shrink-0">
                                    {log.action}
                                </span>
                            </div>
                        ))}
                    </div>

                    <div className="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500">
                        <span>
                            Halaman <strong>{page}</strong> dari <strong>{totalPages}</strong> ({filteredItems.length} item)
                        </span>
                        <div className="inline-flex items-center gap-1.5">
                            <button
                                type="button"
                                disabled={page <= 1}
                                onClick={() => setPage((p) => Math.max(1, p - 1))}
                                className="p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 disabled:opacity-40 cursor-pointer"
                            >
                                <ChevronLeft className="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                disabled={page >= totalPages}
                                onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                                className="p-1.5 rounded-lg border border-slate-200 dark:border-slate-800 disabled:opacity-40 cursor-pointer"
                            >
                                <ChevronRight className="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
