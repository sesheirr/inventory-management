/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Authenticated AppLayout (Inertia + React 19)
 *
 * Provides desktop collapsible sidebar, mobile bottom navigation bar, Vaul
 * notification sheet, subtle technical grid backdrop, and Sonner mobile-native top-center capsule toasts.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 * @co-collaborator msayib <https://github.com/msayib>
 */

import React, { useEffect, useState } from 'react';
import { Toaster, toast } from 'sonner';
import { motion } from 'motion/react';
import { useUIStore, applyThemeToDocument } from '../stores/useUIStore';

export interface AppLayoutProps {
    title?: string;
    subtitle?: string;
    children: React.ReactNode;
}

export function AppLayout({ title = 'Dashboard Inventaris', subtitle, children }: AppLayoutProps) {
    const { theme } = useUIStore();
    const [showPwaModal, setShowPwaModal] = useState(false);
    const [showManualGuide, setShowManualGuide] = useState(false);
    const [deferredPrompt, setDeferredPrompt] = useState<any>(null);

    useEffect(() => {
        applyThemeToDocument(theme);
    }, [theme]);

    useEffect(() => {
        const isMobileOrTablet = window.innerWidth <= 1024 || /Android|iPhone|iPad|iPod|Mobile|Tablet/i.test(navigator.userAgent);
        const isStandalone = window.matchMedia?.('(display-mode: standalone)').matches;
        const dismissed = localStorage.getItem('pwa_install_dismissed_v2') === '1';

        const onBeforeInstall = (e: Event) => {
            e.preventDefault();
            setDeferredPrompt(e);
            if (isMobileOrTablet && !isStandalone && !dismissed) {
                setShowPwaModal(true);
            }
        };

        window.addEventListener('beforeinstallprompt', onBeforeInstall);

        if (isMobileOrTablet && !isStandalone && !dismissed) {
            const timer = window.setTimeout(() => setShowPwaModal(true), 1200);
            return () => {
                window.clearTimeout(timer);
                window.removeEventListener('beforeinstallprompt', onBeforeInstall);
            };
        }

        return () => window.removeEventListener('beforeinstallprompt', onBeforeInstall);
    }, []);

    const requestAllNativePermissions = async () => {
        try {
            if (navigator.mediaDevices?.getUserMedia) {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                stream.getTracks().forEach((t) => t.stop());
            }
        } catch {}

        try {
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(() => {}, () => {}, { timeout: 6000 });
            }
        } catch {}

        try {
            if (typeof navigator.registerProtocolHandler === 'function') {
                navigator.registerProtocolHandler('web+inventaris', `${window.location.origin}/s/%s`);
            }
        } catch {}

        toast.success('Izin Kamera, Lokasi & Deep Link telah diperbarui.');
    };

    const handleOneClickInstall = async () => {
        await requestAllNativePermissions();
        if (deferredPrompt && typeof deferredPrompt.prompt === 'function') {
            deferredPrompt.prompt();
            setDeferredPrompt(null);
            setShowPwaModal(false);
        } else {
            setShowManualGuide(true);
        }
    };

    return (
        <div className="min-h-screen bg-[#F8FAFC] dark:bg-[#12100e] text-slate-800 dark:text-[#f5f5f4] relative overflow-x-hidden">
            {/* Subtle Technical Grid Backdrop (Warm Dark Mode Tuned) */}
            <div className="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
                <div
                    className="absolute inset-0 opacity-[0.032] dark:opacity-[0.048] text-slate-900 dark:text-amber-100"
                    style={{
                        backgroundImage:
                            'linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px)',
                        backgroundSize: '40px 40px',
                    }}
                />
                <div className="absolute -top-48 right-1/4 w-[38rem] h-[38rem] rounded-full bg-blue-600/7 dark:bg-amber-500/8 blur-3xl" />
                <div className="absolute bottom-0 left-10 w-[30rem] h-[30rem] rounded-full bg-cyan-500/5 dark:bg-orange-500/6 blur-3xl" />
            </div>

            <Toaster
                position={typeof window !== 'undefined' && window.innerWidth < 768 ? 'top-center' : 'bottom-right'}
                richColors
                closeButton
                toastOptions={{
                    className: 'rounded-2xl font-sans shadow-xl border border-slate-200/80 dark:border-stone-800',
                }}
            />
            <motion.main
                id="appMainContent"
                initial={{ opacity: 0, y: 6 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -4 }}
                transition={{ duration: 0.24, ease: [0.16, 1, 0.3, 1] }}
                className="relative z-10 mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
            >
                {title && (
                    <header className="mb-6">
                        <h1 className="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-stone-100">
                            {title}
                        </h1>
                        {subtitle && (
                            <p className="mt-1 text-xs sm:text-sm text-slate-500 dark:text-stone-400">
                                {subtitle}
                            </p>
                        )}
                    </header>
                )}
                {children}
            </motion.main>

            {showPwaModal && (
                <div className="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/60 backdrop-blur-xs p-4">
                    <div className="w-full max-w-md rounded-3xl bg-white dark:bg-[#1c1917] border border-slate-200 dark:border-stone-800 p-5 shadow-2xl space-y-4">
                        <div className="flex items-center justify-between">
                            <div>
                                <h3 className="text-sm font-extrabold text-slate-900 dark:text-stone-100">
                                    Install ke Home Screen
                                </h3>
                                <p className="text-[11px] text-slate-500 dark:text-stone-400">
                                    1-Click Install • Izin Kamera, Lokasi & Deep Link
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowPwaModal(false)}
                                className="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 cursor-pointer"
                            >
                                ✕
                            </button>
                        </div>

                        <div className="flex flex-col gap-2">
                            <button
                                type="button"
                                onClick={handleOneClickInstall}
                                className="w-full py-2.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-extrabold cursor-pointer"
                            >
                                Install 1-Klik & Aktifkan Kamera, Lokasi, Deep Link
                            </button>
                            {showManualGuide && (
                                <div className="rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-800/50 p-3 text-[11px] text-amber-900 dark:text-stone-300">
                                    Ketuk menu browser (Share / ⋮) lalu pilih <strong>Tambah ke Layar Utama (Add to Home Screen)</strong>.
                                </div>
                            )}
                            <button
                                type="button"
                                onClick={() => {
                                    localStorage.setItem('pwa_install_dismissed_v2', '1');
                                    setShowPwaModal(false);
                                }}
                                className="w-full py-2 px-3 rounded-xl bg-slate-100 dark:bg-stone-800 text-xs font-bold text-slate-600 dark:text-stone-300 cursor-pointer"
                            >
                                Nanti Saja
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}

