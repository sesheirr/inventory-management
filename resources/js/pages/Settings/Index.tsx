/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Settings Page (React 19 + Inertia v2)
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { AppLayout } from '../../layouts/AppLayout';
import { useUIStore, ThemeMode } from '../../stores/useUIStore';
import { Sun, Moon, Monitor } from 'lucide-react';

export default function SettingsIndex() {
    const { theme, setTheme } = useUIStore();

    const options: { id: ThemeMode; label: string; Icon: React.ComponentType<{ className?: string }> }[] = [
        { id: 'light', label: 'Mode Terang', Icon: Sun },
        { id: 'dark', label: 'Mode Gelap', Icon: Moon },
        { id: 'system', label: 'Ikuti Sistem', Icon: Monitor },
    ];

    return (
        <AppLayout
            title="Pengaturan Aplikasi"
            subtitle="Sesuaikan preferensi tema tampilan dan notifikasi perangkat."
        >
            <div className="max-w-xl rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-6 shadow-xs space-y-4">
                <h2 className="text-sm font-bold text-slate-900 dark:text-white">
                    Preferensi Tema Tampilan
                </h2>
                <div className="grid grid-cols-3 gap-3">
                    {options.map(({ id, label, Icon }) => (
                        <button
                            key={id}
                            type="button"
                            onClick={() => setTheme(id)}
                            className={`flex flex-col items-center justify-center gap-2 rounded-xl border p-4 text-xs font-bold transition-all cursor-pointer ${
                                theme === id
                                    ? 'border-blue-600 bg-blue-50/70 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400'
                                    : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400'
                            }`}
                        >
                            <Icon className="h-5 w-5" />
                            <span>{label}</span>
                        </button>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
