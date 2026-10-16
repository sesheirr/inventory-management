/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Register Page (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React, { useState } from 'react';
import { AuthLayout } from '../../layouts/AuthLayout';
import { RefreshCw, ShieldCheck, UserPlus } from 'lucide-react';

export interface RegisterProps {
    securityChallengeSvg?: string;
    csrfToken?: string;
}

export default function Register({ securityChallengeSvg = '', csrfToken = '' }: RegisterProps) {
    const [svgMarkup, setSvgMarkup] = useState<string>(securityChallengeSvg);
    const [securityCode, setSecurityCode] = useState<string>('');

    const refreshChallenge = async () => {
        const res = await fetch('/auth/session-token-sync', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        const data = (await res.json()) as { svg?: string };
        if (data.svg) {
            setSvgMarkup(data.svg);
            setSecurityCode('');
        }
    };

    return (
        <AuthLayout title="Registrasi Akun Pegawai Diskominfo">
            <div className="rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-8 shadow-xl">
                <form action="/register" method="POST" className="space-y-4">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Lengkap
                        </label>
                        <input
                            type="text"
                            name="name"
                            required
                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Email Kedinasan
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
                            Kata Sandi
                        </label>
                        <input
                            type="password"
                            name="password"
                            required
                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-3.5 py-2.5 text-sm outline-none focus:border-blue-500"
                        />
                    </div>

                    <div
                        id="secChallengeBox"
                        className="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 p-3.5 space-y-2.5"
                    >
                        <div className="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                            <span className="inline-flex items-center gap-1.5">
                                <ShieldCheck className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                <span>Verifikasi Keamanan</span>
                            </span>
                            <button
                                type="button"
                                onClick={refreshChallenge}
                                className="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400 cursor-pointer"
                            >
                                <RefreshCw className="h-3.5 w-3.5" />
                                <span>Muat Ulang</span>
                            </button>
                        </div>
                        <div
                            id="secChallengeRender"
                            className="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700 bg-white"
                            dangerouslySetInnerHTML={{ __html: svgMarkup }}
                        />
                        <input
                            id="secChallengeInput"
                            type="text"
                            name="security_code"
                            value={securityCode}
                            onChange={(e) => setSecurityCode(e.target.value.toUpperCase())}
                            required
                            placeholder="KETIK KODE VERIFIKASI"
                            className="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2 text-center font-mono text-sm font-bold tracking-widest uppercase outline-none focus:border-blue-500"
                        />
                    </div>

                    <button
                        type="submit"
                        className="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition-all cursor-pointer"
                    >
                        <UserPlus className="h-4 w-4" />
                        <span>Daftar Akun</span>
                    </button>
                </form>
            </div>
        </AuthLayout>
    );
}
