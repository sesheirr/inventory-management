/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 SuperAdmin User Management (React 19 + Inertia v2)
 *
 * Strictly restricted to `superadmin` role.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../../layouts/AppLayout';
import { Users, Shield } from 'lucide-react';

export interface UserItem {
    id: number;
    name: string;
    email: string;
    role: string;
}

export interface AdminUsersIndexProps {
    users?: UserItem[];
}

export default function AdminUsersIndex({ users = [] }: AdminUsersIndexProps) {
    return (
        <AppLayout
            title="Manajemen Akun & Hak Akses (Super Admin)"
            subtitle="Kelola peran pegawai, administrator, dan super administrator Diskominfo Garut."
        >
            <div className="rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-xs space-y-3">
                <div className="flex items-center gap-2 text-sm font-bold">
                    <Users className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    <span>Daftar Pengguna Terdaftar ({users.length})</span>
                </div>
                <div className="divide-y divide-slate-100 dark:divide-slate-800">
                    {users.map((u) => (
                        <div key={u.id} className="py-3 flex items-center justify-between gap-4">
                            <div>
                                <div className="text-sm font-bold text-slate-900 dark:text-white">
                                    {u.name}
                                </div>
                                <div className="text-xs text-slate-500">{u.email}</div>
                            </div>
                            <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 dark:bg-slate-800 px-3 py-1 text-xs font-bold uppercase">
                                <Shield className="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                {u.role}
                            </span>
                        </div>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
