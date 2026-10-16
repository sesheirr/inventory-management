/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — 60fps Mobile SwipeableRow (Motion)
 *
 * Provides native-app horizontal drag/swipe physics for mobile list items
 * (Notifications mark-as-read/dismiss, Mutation quick approve/reject).
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { motion, useMotionValue, useTransform, PanInfo } from 'motion/react';

export interface SwipeableRowProps {
    children: React.ReactNode;
    onSwipeLeft?: () => void;
    onSwipeRight?: () => void;
    leftLabel?: string;
    rightLabel?: string;
    threshold?: number;
    className?: string;
}

export function SwipeableRow({
    children,
    onSwipeLeft,
    onSwipeRight,
    leftLabel = 'Tandai Dibaca',
    rightLabel = 'Selesai',
    threshold = 88,
    className = '',
}: SwipeableRowProps) {
    const x = useMotionValue(0);
    const backgroundOpacity = useTransform(x, [-threshold, 0, threshold], [1, 0, 1]);

    const handleDragEnd = (_event: MouseEvent | TouchEvent | PointerEvent, info: PanInfo) => {
        if (info.offset.x < -threshold && onSwipeLeft) {
            onSwipeLeft();
        } else if (info.offset.x > threshold && onSwipeRight) {
            onSwipeRight();
        }
    };

    return (
        <div className={`relative overflow-hidden rounded-2xl select-none touch-pan-y ${className}`}>
            <motion.div
                style={{ opacity: backgroundOpacity }}
                className="pointer-events-none absolute inset-0 flex items-center justify-between px-5 text-xs font-bold text-white bg-gradient-to-r from-emerald-600 via-blue-600 to-rose-600"
            >
                <span>{rightLabel}</span>
                <span>{leftLabel}</span>
            </motion.div>
            <motion.div
                drag="x"
                dragConstraints={{ left: 0, right: 0 }}
                dragElastic={0.45}
                style={{ x }}
                onDragEnd={handleDragEnd}
                whileTap={{ scale: 0.992 }}
                className="relative z-10 bg-white dark:bg-slate-900"
            >
                {children}
            </motion.div>
        </div>
    );
}
