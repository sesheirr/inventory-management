/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Adaptive ResponsiveModal (Vaul + Motion)
 *
 * Renders a spring-animated centered modal on Desktop (>= 640px) and a native
 * velocity-aware drag-to-dismiss bottom sheet on Mobile (< 640px) via Vaul.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React, { useEffect, useState } from 'react';
import { Drawer } from 'vaul';
import { AnimatePresence, motion } from 'motion/react';
import { X } from 'lucide-react';

export interface ResponsiveModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description?: string;
    children: React.ReactNode;
    footer?: React.ReactNode;
    maxWidthClass?: string;
}

export function ResponsiveModal({
    open,
    onOpenChange,
    title,
    description,
    children,
    footer,
    maxWidthClass = 'max-w-lg',
}: ResponsiveModalProps) {
    const [isMobile, setIsMobile] = useState<boolean>(false);

    useEffect(() => {
        if (typeof window === 'undefined') return;
        const mq = window.matchMedia('(max-width: 639px)');
        const update = () => setIsMobile(mq.matches);
        update();
        mq.addEventListener('change', update);
        return () => mq.removeEventListener('change', update);
    }, []);

    if (isMobile) {
        return (
            <Drawer.Root open={open} onOpenChange={onOpenChange} shouldScaleBackground>
                <Drawer.Portal>
                    <Drawer.Overlay className="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs" />
                    <Drawer.Content className="fixed inset-x-0 bottom-0 z-50 mt-24 flex max-h-[92dvh] flex-col rounded-t-3xl border-t border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xl outline-none">
                        <div className="mx-auto mt-3 h-1.5 w-12 shrink-0 rounded-full bg-slate-300 dark:bg-slate-700" />
                        <div className="flex items-start justify-between gap-3 px-5 pt-4 pb-3 border-b border-slate-100 dark:border-slate-800/80">
                            <div>
                                <Drawer.Title className="text-base font-bold text-slate-900 dark:text-slate-100">
                                    {title}
                                </Drawer.Title>
                                {description && (
                                    <Drawer.Description className="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                        {description}
                                    </Drawer.Description>
                                )}
                            </div>
                            <button
                                type="button"
                                onClick={() => onOpenChange(false)}
                                className="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors"
                                aria-label="Tutup"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>
                        <div className="flex-1 overflow-y-auto px-5 py-4">{children}</div>
                        {footer && (
                            <div className="border-t border-slate-100 dark:border-slate-800/80 px-5 py-3.5 bg-slate-50/60 dark:bg-slate-900/80">
                                {footer}
                            </div>
                        )}
                    </Drawer.Content>
                </Drawer.Portal>
            </Drawer.Root>
        );
    }

    return (
        <AnimatePresence>
            {open && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <motion.div
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.18 }}
                        onClick={() => onOpenChange(false)}
                        className="fixed inset-0 bg-slate-950/60 backdrop-blur-xs"
                    />
                    <motion.div
                        initial={{ opacity: 0, scale: 0.95, y: 10 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.96, y: 8 }}
                        transition={{ type: 'spring', stiffness: 380, damping: 30 }}
                        className={`relative z-10 w-full ${maxWidthClass} rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-2xl overflow-hidden`}
                    >
                        <div className="flex items-start justify-between gap-3 px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                            <div>
                                <h3 className="text-base font-bold text-slate-900 dark:text-slate-100">
                                    {title}
                                </h3>
                                {description && (
                                    <p className="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                        {description}
                                    </p>
                                )}
                            </div>
                            <button
                                type="button"
                                onClick={() => onOpenChange(false)}
                                className="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors"
                                aria-label="Tutup"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        </div>
                        <div className="max-h-[75vh] overflow-y-auto px-6 py-5">{children}</div>
                        {footer && (
                            <div className="border-t border-slate-100 dark:border-slate-800 px-6 py-3.5 bg-slate-50/70 dark:bg-slate-900/70">
                                {footer}
                            </div>
                        )}
                    </motion.div>
                </div>
            )}
        </AnimatePresence>
    );
}
