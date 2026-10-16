/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Public Civic Landing Page (v2 React 19)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 * @co-collaborator msayib <https://github.com/msayib>
 */

import React, { useEffect, useState } from 'react';
import { motion } from 'motion/react';
import { ShieldCheck, QrCode, ArrowRightLeft, Building2, ArrowRight } from 'lucide-react';

export interface WelcomeProps {
    appName?: string;
    authenticated?: boolean;
}

export default function Welcome({
    appName = 'SIM-Inventaris TIK — Diskominfo Kabupaten Garut',
    authenticated = false,
}: WelcomeProps) {
    const [orbShift, setOrbShift] = useState({ x1: 0, y1: 0, hue1: 0, x2: 0, y2: 0, hue2: 0 });

    useEffect(() => {
        const timer = setInterval(() => {
            setOrbShift({
                x1: Math.round((Math.random() - 0.5) * 220),
                y1: Math.round((Math.random() - 0.5) * 160),
                hue1: Math.round((Math.random() - 0.5) * 110),
                x2: Math.round((Math.random() - 0.5) * 200),
                y2: Math.round((Math.random() - 0.5) * 160),
                hue2: Math.round((Math.random() - 0.5) * 110),
            });
        }, 9000);
        return () => clearInterval(timer);
    }, []);

    return (
        <div className="min-h-screen bg-[#F8FAFC] dark:bg-[#090D16] text-slate-900 dark:text-slate-100 relative overflow-hidden">
            {/* Subtle Technical Grid Backdrop + Slow-Drifting Random Color-Morphing Ambient Orbs */}
            <div className="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
                <div
                    className="absolute inset-0 opacity-[0.035] dark:opacity-[0.055]"
                    style={{
                        backgroundImage:
                            'linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px)',
                        backgroundSize: '40px 40px',
                    }}
                />
                <div
                    data-ambient-orb="a"
                    className="landing-ambient-orb-a absolute -top-40 right-1/5 w-[38rem] h-[38rem] rounded-full bg-blue-600/10 dark:bg-blue-500/14 blur-3xl"
                    style={{
                        transform: `translate3d(${orbShift.x1}px, ${orbShift.y1}px, 0)`,
                        filter: `blur(72px) hue-rotate(${orbShift.hue1}deg)`,
                    }}
                />
                <div
                    data-ambient-orb="b"
                    className="landing-ambient-orb-b absolute bottom-0 left-8 w-[32rem] h-[32rem] rounded-full bg-cyan-500/8 dark:bg-sky-500/12 blur-3xl"
                    style={{
                        transform: `translate3d(${orbShift.x2}px, ${orbShift.y2}px, 0)`,
                        filter: `blur(68px) hue-rotate(${orbShift.hue2}deg)`,
                    }}
                />
                <div
                    data-ambient-orb="c"
                    className="landing-ambient-orb-c absolute top-1/3 left-1/2 -translate-x-1/2 w-[30rem] h-[30rem] rounded-full bg-indigo-500/7 dark:bg-violet-500/10 blur-3xl"
                />
            </div>

            <div className="relative z-10 mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
                <motion.div
                    initial={{ opacity: 0, y: 14 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ type: 'spring', stiffness: 320, damping: 28 }}
                    className="rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 sm:p-12 shadow-xl"
                >
                    <span className="inline-flex items-center gap-2 rounded-full bg-blue-50 dark:bg-blue-950/60 px-3.5 py-1 text-xs font-bold text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">
                        <ShieldCheck className="h-3.5 w-3.5" />
                        Pemerintah Kabupaten Garut • Dinas Komunikasi dan Informatika
                    </span>
                    <h1 className="mt-5 text-3xl sm:text-5xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {appName}
                    </h1>
                    <p className="mt-4 max-w-2xl text-sm sm:text-base text-slate-600 dark:text-slate-400 leading-relaxed">
                        Platform terpadu pencatatan aset TIK, pemetaan ruangan kerja, audit mutasi berjenjang,
                        dan verifikasi QR real-time di lingkungan Diskominfo Kabupaten Garut.
                    </p>
                    <div className="mt-8 flex flex-wrap items-center gap-3">
                        <a
                            href={authenticated ? '/dashboard' : '/login'}
                            className="inline-flex items-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition-all"
                        >
                            <span>{authenticated ? 'Buka Dashboard' : 'Masuk Portal Pegawai'}</span>
                            <ArrowRight className="h-4 w-4" />
                        </a>
                        <a
                            href="/products/scan"
                            className="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 px-5 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 hover:border-blue-500/40 transition-all"
                        >
                            <QrCode className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            <span>Pindai QR Aset</span>
                        </a>
                    </div>

                    <div className="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div className="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 p-4">
                            <QrCode className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            <h3 className="mt-2 text-sm font-bold">Identitas QR Terenkripsi</h3>
                            <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Setiap perangkat memiliki kode inventaris dan label QR siap cetak.
                            </p>
                        </div>
                        <div className="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 p-4">
                            <ArrowRightLeft className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                            <h3 className="mt-2 text-sm font-bold">Lacak Mutasi Berjenjang</h3>
                            <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Alur persetujuan perpindahan barang antar ruangan yang transparan.
                            </p>
                        </div>
                        <div className="rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40 p-4">
                            <Building2 className="h-5 w-5 text-amber-600 dark:text-amber-400" />
                            <h3 className="mt-2 text-sm font-bold">Pemetaan Ruangan & Bidang</h3>
                            <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                Pantau distribusi dan kondisi fisik perangkat di seluruh bidang kerja.
                            </p>
                        </div>
                    </div>
                </motion.div>
            </div>
        </div>
    );
}
