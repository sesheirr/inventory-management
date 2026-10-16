/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Authenticated AppLayout (Inertia + React 19)
 *
 * Provides desktop collapsible sidebar, mobile bottom navigation bar, Vaul
 * notification sheet, subtle technical grid backdrop, and Sonner mobile-native top-center capsule toasts.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 * @co-collaborator msayib <https://github.com/msayib>
 */

import React, { useEffect } from 'react';
import { Toaster } from 'sonner';
import { motion } from 'motion/react';
import { useUIStore, applyThemeToDocument } from '../stores/useUIStore';

export interface AppLayoutProps {
    title?: string;
    subtitle?: string;
    children: React.ReactNode;
}

export function AppLayout({ title = 'Dashboard Inventaris', subtitle, children }: AppLayoutProps) {
    const { theme } = useUIStore();

    useEffect(() => {
        applyThemeToDocument(theme);
    }, [theme]);

    return (
        <div className="min-h-screen bg-[#F8FAFC] dark:bg-[#0b132b] text-slate-800 dark:text-slate-100 relative overflow-x-hidden">
            {/* Subtle Technical Grid Backdrop */}
            <div className="fixed inset-0 pointer-events-none z-0 overflow-hidden" aria-hidden="true">
                <div
                    className="absolute inset-0 opacity-[0.032] dark:opacity-[0.052]"
                    style={{
                        backgroundImage:
                            'linear-gradient(to right, currentColor 1px, transparent 1px), linear-gradient(to bottom, currentColor 1px, transparent 1px)',
                        backgroundSize: '40px 40px',
                    }}
                />
                <div className="absolute -top-48 right-1/4 w-[38rem] h-[38rem] rounded-full bg-blue-600/7 dark:bg-blue-500/9 blur-3xl" />
                <div className="absolute bottom-0 left-10 w-[30rem] h-[30rem] rounded-full bg-cyan-500/5 dark:bg-sky-500/7 blur-3xl" />
            </div>

            <Toaster
                position="top-center"
                richColors
                closeButton
                toastOptions={{
                    className: 'rounded-2xl font-sans shadow-xl border border-slate-200/80 dark:border-slate-800',
                }}
            />
            <motion.main
                initial={{ opacity: 0, y: 6 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.2, ease: 'easeOut' }}
                className="relative z-10 mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
            >
                {title && (
                    <header className="mb-6">
                        <h1 className="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            {title}
                        </h1>
                        {subtitle && (
                            <p className="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                {subtitle}
                            </p>
                        )}
                    </header>
                )}
                {children}
            </motion.main>
        </div>
    );
}
