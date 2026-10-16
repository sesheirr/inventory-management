/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Fullscreen Banking-Style Barcode/QR Scanner HUD (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 * @collaborator msayib <https://github.com/msayib> — Ideation, System Design, Architecture, Performance, Refactor, Tune-Up, UI/UX & Mobile-First High-Level Engineering
 */

import React, { useState } from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { ArrowLeft, Maximize2, Minimize2, QrCode, Search, Sparkles } from 'lucide-react';

export default function ProductScan() {
    const [code, setCode] = useState<string>('');
    const [fullscreenMode, setFullscreenMode] = useState<boolean>(true);
    const [feedback, setFeedback] = useState<{ type: 'info' | 'error' | 'success'; text: string } | null>(null);

    const handleSearch = async (e: React.FormEvent) => {
        e.preventDefault();
        const trimmed = code.trim().toUpperCase();
        if (!trimmed) return;
        setFeedback({ type: 'info', text: `Mencari kode aset ${trimmed}...` });

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
            subtitle="Mode pemindai layar penuh bergaya sistem perbankan untuk identifikasi aset inventaris."
        >
            <div
                className={
                    fullscreenMode
                        ? 'fixed inset-0 z-[120] bg-[#0b0a09] text-white flex flex-col justify-between overflow-y-auto select-none'
                        : 'mx-auto max-w-2xl rounded-3xl border border-slate-800 bg-[#0b0a09] text-white flex flex-col justify-between shadow-2xl overflow-hidden'
                }
            >
                {/* Top Banking HUD Bar */}
                <div className="flex items-center justify-between gap-3 px-4 sm:px-6 py-4 border-b border-white/10 bg-black/60 backdrop-blur-xl">
                    <div className="flex items-center gap-3 min-w-0">
                        <a
                            href="/products"
                            className="h-10 w-10 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/15 flex items-center justify-center text-white transition-colors shrink-0"
                        >
                            <ArrowLeft className="h-4 w-4" />
                        </a>
                        <div className="min-w-0">
                            <div className="flex items-center gap-2">
                                <h2 className="text-sm sm:text-base font-extrabold uppercase tracking-wide truncate">
                                    Inventory Barcode HUD
                                </h2>
                                <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                    <span className="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                                    LIVE SCANNER
                                </span>
                            </div>
                            <p className="text-[11px] text-white/60 truncate">
                                Diskominfo Kab. Garut • Optik Kamera &amp; USB/Wireless HID
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        onClick={() => setFullscreenMode((v) => !v)}
                        className="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 border border-white/15 text-white transition-colors"
                    >
                        {fullscreenMode ? <Minimize2 className="h-3.5 w-3.5" /> : <Maximize2 className="h-3.5 w-3.5" />}
                        <span>{fullscreenMode ? 'Mode Ringkas' : 'Fullscreen'}</span>
                    </button>
                </div>

                {/* Center Reticle Frame */}
                <div className="relative flex-1 flex flex-col items-center justify-center p-6">
                    <div className="relative w-full max-w-md aspect-[4/3] rounded-[2rem] bg-black/85 border-2 border-white/15 shadow-[0_0_70px_rgba(37,99,235,0.28)] flex flex-col items-center justify-center p-6 text-center overflow-hidden">
                        <div className="pointer-events-none absolute inset-6">
                            <span className="absolute top-0 left-0 w-9 h-9 border-t-4 border-l-4 border-sky-400 rounded-tl-2xl" />
                            <span className="absolute top-0 right-0 w-9 h-9 border-t-4 border-r-4 border-sky-400 rounded-tr-2xl" />
                            <span className="absolute bottom-0 left-0 w-9 h-9 border-b-4 border-l-4 border-sky-400 rounded-bl-2xl" />
                            <span className="absolute bottom-0 right-0 w-9 h-9 border-b-4 border-r-4 border-sky-400 rounded-br-2xl" />
                            <div className="scanner-laser-line" />
                        </div>

                        <div className="h-14 w-14 rounded-2xl bg-sky-500/15 border border-sky-400/30 text-sky-400 flex items-center justify-center mb-3">
                            <QrCode className="h-7 w-7" />
                        </div>
                        <p className="text-sm font-extrabold text-white">Siap Memindai Barcode / QR Aset</p>
                        <p className="text-xs text-white/65 max-w-xs mt-1">
                            Arahkan kamera atau tembakkan laser scanner fisik langsung pada label inventaris.
                        </p>
                    </div>

                    <div className="mt-4 inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/5 border border-white/10 text-[11px] font-semibold text-sky-300">
                        <Sparkles className="h-3.5 w-3.5" />
                        <span>Mendukung Code128, QR Code &amp; Input Cepat HID</span>
                    </div>
                </div>

                {/* Bottom Banking Glass Search Bar */}
                <div className="px-4 sm:px-6 py-4 border-t border-white/10 bg-[#141210]/95 backdrop-blur-xl">
                    <form onSubmit={handleSearch} className="max-w-xl mx-auto space-y-2.5">
                        <div className="flex gap-2.5">
                            <input
                                type="text"
                                name="barcode"
                                value={code}
                                onChange={(e) => setCode(e.target.value.toUpperCase())}
                                placeholder="Contoh: BRG-00001"
                                className="flex-1 rounded-2xl border border-white/20 bg-white/10 px-4 py-3 font-mono text-sm font-extrabold uppercase text-white placeholder-white/40 outline-none focus:border-sky-400"
                            />
                            <button
                                type="submit"
                                className="inline-flex items-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 px-5 py-3 text-xs sm:text-sm font-extrabold text-white shadow-lg shadow-blue-600/30"
                            >
                                <Search className="h-4 w-4" />
                                <span>Cari Aset</span>
                            </button>
                        </div>

                        {feedback && (
                            <p
                                className={`text-xs font-bold px-3 py-2 rounded-xl border ${
                                    feedback.type === 'error'
                                        ? 'bg-rose-500/20 border-rose-400/40 text-rose-200'
                                        : feedback.type === 'success'
                                          ? 'bg-emerald-500/20 border-emerald-400/40 text-emerald-200'
                                          : 'bg-sky-500/20 border-sky-400/40 text-sky-200'
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
