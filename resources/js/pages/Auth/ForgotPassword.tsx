/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 ForgotPassword Page (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AuthLayout } from '../../layouts/AuthLayout';
import { KeyRound } from 'lucide-react';

export interface ForgotPasswordProps {
    csrfToken?: string;
}

export default function ForgotPassword({ csrfToken = '' }: ForgotPasswordProps) {
    return (
        <AuthLayout title="Atur Ulang Kata Sandi">
            <div className="rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-8 shadow-xl">
                <form action="/forgot-password" method="POST" className="space-y-4">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Email Terdaftar
                        </label>
                        <input
                            type="email"
                            name="email"
                            required
                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Kata Sandi Baru
                        </label>
                        <input
                            type="password"
                            name="password"
                            required
                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            required
                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500"
                        />
                    </div>
                    <button
                        type="submit"
                        className="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition-all cursor-pointer"
                    >
                        <KeyRound className="h-4 w-4" />
                        <span>Perbarui Kata Sandi</span>
                    </button>
                </form>
            </div>
        </AuthLayout>
    );
}
