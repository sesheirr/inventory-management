/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Login Page (React 19 + Inertia v2)
 *
 * Includes 1-Click Quick Sign-In saved profile (7-day expiry) and AdBlock-proof
 * inline SVG security verification (`/auth/session-token-sync`).
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React, { useState } from 'react';
import { AuthLayout } from '../../layouts/AuthLayout';
import { RefreshCw, ShieldCheck, LogIn, Trash2 } from 'lucide-react';

export interface SavedQuickProfile {
    id: number;
    name: string;
    email: string;
    role_label: string;
    avatar_url?: string | null;
}

export interface LoginProps {
    securityChallengeSvg?: string;
    savedQuickProfile?: SavedQuickProfile | null;
    csrfToken?: string;
}

export default function Login({
    securityChallengeSvg = '',
    savedQuickProfile = null,
    csrfToken = '',
}: LoginProps) {
    const [svgMarkup, setSvgMarkup] = useState<string>(securityChallengeSvg);
    const [securityCode, setSecurityCode] = useState<string>('');
    const [refreshing, setRefreshing] = useState<boolean>(false);

    const refreshChallenge = async () => {
        setRefreshing(true);
        try {
            const res = await fetch('/auth/session-token-sync', {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
            const data = (await res.json()) as { svg?: string };
            if (data.svg) {
                setSvgMarkup(data.svg);
                setSecurityCode('');
            }
        } finally {
            setRefreshing(false);
        }
    };

    return (
        <AuthLayout title="Masuk Sistem Inventaris Diskominfo">
            <div className="rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 sm:p-8 shadow-xl space-y-6">
                {savedQuickProfile && (
                    <div className="rounded-2xl border border-blue-200/80 dark:border-blue-900/60 bg-blue-50/50 dark:bg-blue-950/30 p-4 space-y-3">
                        <div className="flex items-center justify-between">
                            <span className="text-[11px] font-bold uppercase tracking-wider text-blue-700 dark:text-blue-300">
                                Masuk Cepat 1-Klik
                            </span>
                            <form action="/login/forget-profile" method="POST">
                                <input type="hidden" name="_token" value={csrfToken} />
                                <button
                                    type="submit"
                                    className="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline cursor-pointer"
                                >
                                    <Trash2 className="h-3 w-3" />
                                    <span>Hapus</span>
                                </button>
                            </form>
                        </div>
                        <form action="/login/quick" method="POST">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <button
                                type="submit"
                                className="w-full flex items-center justify-between gap-3 rounded-xl bg-white dark:bg-slate-900 p-3 border border-slate-200 dark:border-slate-800 hover:border-blue-500 transition-all cursor-pointer text-left"
                            >
                                <div>
                                    <div className="text-sm font-bold text-slate-900 dark:text-white">
                                        {savedQuickProfile.name}
                                    </div>
                                    <div className="text-xs text-slate-500 dark:text-slate-400">
                                        {savedQuickProfile.email} • {savedQuickProfile.role_label}
                                    </div>
                                </div>
                                <LogIn className="h-4 w-4 text-blue-600 dark:text-blue-400 shrink-0" />
                            </button>
                        </form>
                    </div>
                )}

                <form action="/login" method="POST" className="space-y-4">
                    <input type="hidden" name="_token" value={csrfToken} />
                    <div>
                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Email Pegawai
                        </label>
                        <input
                            type="email"
                            name="email"
                            required
                            placeholder="nama@garutkab.go.id"
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

                    {/* AdBlock-Proof Security Verification Box */}
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
                                <RefreshCw className={`h-3.5 w-3.5 ${refreshing ? 'animate-spin' : ''}`} />
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
                        <LogIn className="h-4 w-4" />
                        <span>Masuk Sekarang</span>
                    </button>
                </form>
            </div>
        </AuthLayout>
    );
}
