/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Global UI State Store (Zustand v5)
 *
 * Manages theme preference (light / dark / system), sidebar state, mobile
 * bottom-sheet drawers, and notification drawer state with zero re-render overhead.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export type ThemeMode = 'light' | 'dark' | 'system';

interface UIState {
    theme: ThemeMode;
    sidebarCollapsed: boolean;
    mobileNavOpen: boolean;
    notificationSheetOpen: boolean;
    activeBottomSheetId: string | null;
    setTheme: (theme: ThemeMode) => void;
    toggleSidebar: () => void;
    setMobileNavOpen: (open: boolean) => void;
    setNotificationSheetOpen: (open: boolean) => void;
    openBottomSheet: (id: string) => void;
    closeBottomSheet: () => void;
}

export function applyThemeToDocument(theme: ThemeMode): void {
    if (typeof window === 'undefined') return;
    const prefersDark =
        window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = theme === 'dark' || (theme === 'system' && prefersDark);
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    document.documentElement.dataset.themeMode = theme;
}

export const useUIStore = create<UIState>()(
    persist(
        (set) => ({
            theme: 'system',
            sidebarCollapsed: false,
            mobileNavOpen: false,
            notificationSheetOpen: false,
            activeBottomSheetId: null,
            setTheme: (theme) => {
                applyThemeToDocument(theme);
                set({ theme });
            },
            toggleSidebar: () =>
                set((state) => ({ sidebarCollapsed: !state.sidebarCollapsed })),
            setMobileNavOpen: (open) => set({ mobileNavOpen: open }),
            setNotificationSheetOpen: (open) => set({ notificationSheetOpen: open }),
            openBottomSheet: (id) => set({ activeBottomSheetId: id }),
            closeBottomSheet: () => set({ activeBottomSheetId: null }),
        }),
        {
            name: 'sim-inventaris-ui-v2',
            partialize: (state) => ({
                theme: state.theme,
                sidebarCollapsed: state.sidebarCollapsed,
            }),
        },
    ),
);
