/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Authenticated AppLayout (Inertia + React 19)
 *
 * Provides desktop collapsible sidebar, mobile bottom navigation bar, Vaul
 * notification sheet, and Sonner mobile-native top-center capsule toasts.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
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
        <div className="min-h-screen bg-slate-50 dark:bg-[#0b132b] text-slate-800 dark:text-slate-100">
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
                className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8"
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
