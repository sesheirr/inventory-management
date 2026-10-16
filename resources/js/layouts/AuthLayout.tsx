/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Guest AuthLayout (Inertia + React 19)
 *
 * Includes pinned top-right collapsible theme switcher (Terang / Gelap / Sistem)
 * and adaptive split-screen civic branding for Diskominfo Kabupaten Garut.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React, { useEffect } from 'react';
import { motion } from 'motion/react';
import { Sun, Moon, Monitor } from 'lucide-react';
import { useUIStore, applyThemeToDocument, ThemeMode } from '../stores/useUIStore';

export interface AuthLayoutProps {
    title?: string;
    children: React.ReactNode;
}

export function AuthLayout({ title = 'Masuk Sistem Inventaris', children }: AuthLayoutProps) {
    const { theme, setTheme } = useUIStore();

    useEffect(() => {
        applyThemeToDocument(theme);
    }, [theme]);

    const modes: { id: ThemeMode; label: string; Icon: React.ComponentType<{ className?: string }> }[] = [
        { id: 'light', label: 'Terang', Icon: Sun },
        { id: 'dark', label: 'Gelap', Icon: Moon },
        { id: 'system', label: 'Sistem', Icon: Monitor },
    ];

    return (
        <div className="relative min-h-[100dvh] bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8">
            <div className="fixed top-4 right-4 z-50 inline-flex items-center gap-1 rounded-full bg-white/90 dark:bg-slate-900/90 border border-slate-200/80 dark:border-slate-800 p-1 shadow-lg backdrop-blur-xl">
                {modes.map(({ id, label, Icon }) => (
                    <button
                        key={id}
                        type="button"
                        onClick={() => setTheme(id)}
                        className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold transition-all cursor-pointer ${
                            theme === id
                                ? 'bg-blue-600 text-white shadow-xs'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        }`}
                    >
                        <Icon className="h-3.5 w-3.5" />
                        <span>{label}</span>
                    </button>
                ))}
            </div>

            <motion.div
                initial={{ opacity: 0, y: 12 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ type: 'spring', stiffness: 320, damping: 28 }}
                className="mx-auto w-full max-w-md"
            >
                <div className="mb-6 text-center">
                    <h1 className="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {title}
                    </h1>
                </div>
                {children}
            </motion.div>
        </div>
    );
}
