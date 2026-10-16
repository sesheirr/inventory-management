/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Fullscreen 1D Barcode Sticker Scanner HUD (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 * @collaborator msayib <https://github.com/msayib> — Ideation, System Design, Architecture, Performance, Refactor, Tune-Up, UI/UX & Mobile-First High-Level Engineering
 */

import React, { useEffect, useState } from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { ArrowLeft, Barcode, Search, Sparkles } from 'lucide-react';

export default function ProductScan() {
    const [code, setCode] = useState<string>('');
    const [feedback, setFeedback] = useState<{ type: 'info' | 'error' | 'success'; text: string } | null>(null);

    useEffect(() => {
        document.body.classList.add('scanner-fullscreen-active');
        return () => {
            document.body.classList.remove('scanner-fullscreen-active');
        };
    }, []);

    const handleSearch = async (e: React.FormEvent) => {
        e.preventDefault();
        const trimmed = code.trim().toUpperCase();
        if (!trimmed) return;
        setFeedback({ type: 'info', text: `Mencari kode barang ${trimmed}...` });

        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const res = await fetch('/scan-barcode/cari', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ barcode: trimmed }),
            });
            const data = await res.json();
            if (!res.ok || !data.found) {
                setFeedback({
                    type: 'error',
                    text: data.message || `Barang dengan barcode ${trimmed} tidak ditemukan.`,
                });
                return;
            }
            setFeedback({ type: 'success', text: 'Barang ditemukan! Membuka detail...' });
            window.location.href = data.redirect_url;
        } catch {
            setFeedback({ type: 'error', text: 'Gagal menghubungi server pemindai.' });
        }
    };

    return (
        <AppLayout
            title="Scan Barcode Fullscreen"
            subtitle="Mode pemindai stiker barcode layar penuh (Code-128) untuk identifikasi barang inventaris."
        >
            <div
                data-barcode-mode="linear-1d"
                className="fixed inset-0 z-[120] bg-[#F8FAFC] dark:bg-[#12100e] text-slate-900 dark:text-[#f5f5f4] flex flex-col justify-between overflow-y-auto select-none transition-colors duration-300"
            >
                {/* Full-Only Scanner Header */}
                <div className="flex items-center justify-between gap-3 px-4 sm:px-6 py-4 border-b border-slate-200/80 dark:border-stone-800 bg-white/90 dark:bg-[#1c1917]/90 backdrop-blur-xl">
                    <div className="flex items-center gap-3 min-w-0">
                        <a
                            href="/products"
                            className="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-slate-100 hover:bg-slate-200/80 dark:bg-stone-800 dark:hover:bg-stone-700 border border-slate-200/80 dark:border-stone-700 text-slate-800 dark:text-stone-100 text-xs font-extrabold transition-colors shrink-0"
                        >
                            <ArrowLeft className="h-4 w-4" />
                            <span>Keluar Mode Scan</span>
                        </a>
                        <div className="min-w-0">
                            <div className="flex items-center gap-2">
                                <h2 className="text-sm sm:text-base font-extrabold uppercase tracking-wide truncate">
                                    1D Barcode Sticker Scanner
                                </h2>
                                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-400/30">
                                    <span className="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse" />
                                    CODE-128 LIVE
                                </span>
                            </div>
                            <p className="text-[11px] text-slate-500 dark:text-stone-400 truncate">
                                Pemindai Stiker Barcode Barang • Fullscreen Only Mode
                            </p>
                        </div>
                    </div>
                </div>

                {/* Center Wide 1D Linear Barcode Sticker Reticle */}
                <div className="relative flex-1 flex flex-col items-center justify-center p-6">
                    <div className="relative w-full max-w-xl aspect-[16/8] rounded-[2rem] bg-white dark:bg-[#1c1917] border-2 border-slate-300 dark:border-stone-700 shadow-2xl flex flex-col items-center justify-center p-6 text-center overflow-hidden">
                        <div className="pointer-events-none absolute inset-6">
                            <span className="absolute top-0 left-0 w-9 h-7 border-t-4 border-l-4 border-blue-600 dark:border-amber-400 rounded-tl-xl" />
                            <span className="absolute top-0 right-0 w-9 h-7 border-t-4 border-r-4 border-blue-600 dark:border-amber-400 rounded-tr-xl" />
                            <span className="absolute bottom-0 left-0 w-9 h-7 border-b-4 border-l-4 border-blue-600 dark:border-amber-400 rounded-bl-xl" />
                            <span className="absolute bottom-0 right-0 w-9 h-7 border-b-4 border-r-4 border-blue-600 dark:border-amber-400 rounded-br-xl" />
                            <div className="scanner-laser-line" />
                        </div>

                        <div className="h-12 w-12 rounded-2xl bg-blue-500/10 dark:bg-amber-500/15 border border-blue-500/20 dark:border-amber-400/30 text-blue-600 dark:text-amber-300 flex items-center justify-center mb-2.5">
                            <Barcode className="h-6 w-6" />
                        </div>
                        <p className="text-sm font-extrabold text-slate-900 dark:text-stone-100">
                            Siap Memindai Stiker Barcode Barang (Code-128)
                        </p>
                        <p className="text-xs text-slate-500 dark:text-stone-400 max-w-xs mt-1">
                            Sejajarkan garis stiker barcode barang dengan garis laser pemindai atau gunakan scanner fisik USB.
                        </p>
                    </div>

                    <div className="mt-4 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white dark:bg-[#1c1917] border border-slate-200 dark:border-stone-800 text-[11px] font-semibold text-slate-700 dark:text-stone-300">
                        <Sparkles className="h-3.5 w-3.5 text-blue-600 dark:text-amber-400" />
                        <span>Mendukung Stiker Barcode Code-128 &amp; Input Cepat HID</span>
                    </div>
                </div>

                {/* Bottom Search Bar */}
                <div className="px-4 sm:px-6 py-4 border-t border-slate-200/80 dark:border-stone-800 bg-white/95 dark:bg-[#1c1917]/95 backdrop-blur-xl">
                    <form onSubmit={handleSearch} className="max-w-xl mx-auto space-y-2.5">
                        <div className="flex gap-2.5">
                            <input
                                type="text"
                                name="barcode"
                                value={code}
                                onChange={(e) => setCode(e.target.value.toUpperCase())}
                                placeholder="Contoh: BRG-00001"
                                className="flex-1 rounded-2xl border border-slate-300 dark:border-stone-700 bg-slate-50 dark:bg-[#12100e] px-4 py-3 font-mono text-sm font-extrabold uppercase text-slate-900 dark:text-stone-100 placeholder-slate-400 dark:placeholder-stone-500 outline-none focus:border-blue-500"
                            />
                            <button
                                type="submit"
                                className="inline-flex items-center gap-2 rounded-2xl bg-blue-600 hover:bg-blue-700 px-5 py-3 text-xs sm:text-sm font-extrabold text-white shadow-md shadow-blue-600/20"
                            >
                                <Search className="h-4 w-4" />
                                <span>Cari Barang</span>
                            </button>
                        </div>

                        {feedback && (
                            <p
                                className={`text-xs font-bold px-3 py-2 rounded-xl border ${
                                    feedback.type === 'error'
                                        ? 'bg-rose-500/15 border-rose-400/40 text-rose-700 dark:text-rose-200'
                                        : feedback.type === 'success'
                                          ? 'bg-emerald-500/15 border-emerald-400/40 text-emerald-700 dark:text-emerald-200'
                                          : 'bg-blue-500/15 border-blue-400/40 text-blue-700 dark:text-blue-200'
                                }`}
                            >
                                {feedback.text}
                            </p>
                        )}
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}

