/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — Catalog & Mutation Store (Zustand v5)
 *
 * Manages product view mode ('list' | 'grid'), search/filter criteria, and
 * active quick-preview drawer items across desktop and mobile views.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export type CatalogViewMode = 'list' | 'grid';

interface CatalogState {
    viewMode: CatalogViewMode;
    searchQuery: string;
    selectedCategoryId: number | null;
    selectedRoomId: number | null;
    selectedStatus: string | null;
    selectedProductIds: number[];
    setViewMode: (mode: CatalogViewMode) => void;
    setSearchQuery: (query: string) => void;
    setSelectedCategoryId: (id: number | null) => void;
    setSelectedRoomId: (id: number | null) => void;
    setSelectedStatus: (status: string | null) => void;
    toggleProductSelection: (id: number) => void;
    clearFilters: () => void;
}

export const useCatalogStore = create<CatalogState>()(
    persist(
        (set) => ({
            viewMode: 'list',
            searchQuery: '',
            selectedCategoryId: null,
            selectedRoomId: null,
            selectedStatus: null,
            selectedProductIds: [],
            setViewMode: (viewMode) => set({ viewMode }),
            setSearchQuery: (searchQuery) => set({ searchQuery }),
            setSelectedCategoryId: (selectedCategoryId) => set({ selectedCategoryId }),
            setSelectedRoomId: (selectedRoomId) => set({ selectedRoomId }),
            setSelectedStatus: (selectedStatus) => set({ selectedStatus }),
            toggleProductSelection: (id) =>
                set((state) => ({
                    selectedProductIds: state.selectedProductIds.includes(id)
                        ? state.selectedProductIds.filter((item) => item !== id)
                        : [...state.selectedProductIds, id],
                })),
            clearFilters: () =>
                set({
                    searchQuery: '',
                    selectedCategoryId: null,
                    selectedRoomId: null,
                    selectedStatus: null,
                }),
        }),
        {
            name: 'sim-inventaris-catalog-v2',
            partialize: (state) => ({
                viewMode: state.viewMode,
            }),
        },
    ),
);
