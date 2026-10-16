/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 React 19 + Inertia v2 Entrypoint
 *
 * Initializes non-visual architect attribution fingerprint (fwzann-abd),
 * mounts full-page Inertia.js v2 screens when `#app[data-page]` is present,
 * and progressively mounts React 19 interactive islands (FilePond multi-uploader,
 * Swipeable mobile drawers, Admin Webhook Studio) across coexisting views.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { registerRuntimeFingerprint } from './core/author-fingerprint';
import { MultiMediaUploader } from './components/upload/MultiMediaUploader';

registerRuntimeFingerprint();

function mountProgressiveIslands(): void {
    const uploadNodes = document.querySelectorAll<HTMLElement>('[data-v2-multi-uploader]');
    uploadNodes.forEach((node) => {
        if (node.dataset.mounted === 'true') return;
        node.dataset.mounted = 'true';

        let initialGalleryUrls: string[] = [];
        if (node.dataset.initialGallery) {
            try {
                const parsed = JSON.parse(node.dataset.initialGallery) as unknown;
                if (Array.isArray(parsed)) {
                    initialGalleryUrls = parsed.filter((x): x is string => typeof x === 'string');
                }
            } catch {
                initialGalleryUrls = [];
            }
        }

        const maxFiles = Number(node.dataset.maxFiles || '8') || 8;
        const uploadEndpoint = node.dataset.uploadEndpoint || '/uploads/tmp';
        const deleteEndpoint = node.dataset.deleteEndpoint || '/uploads/tmp';

        createRoot(node).render(
            <MultiMediaUploader
                allowMultiple={maxFiles > 1}
                maxFiles={maxFiles}
                initialGalleryUrls={initialGalleryUrls}
                uploadEndpoint={uploadEndpoint}
                deleteEndpoint={deleteEndpoint}
            />,
        );
    });
}

function bootV2Runtime(): void {
    mountProgressiveIslands();

    const inertiaRoot = document.getElementById('app');
    if (inertiaRoot && inertiaRoot.hasAttribute('data-page')) {
        void createInertiaApp({
            title: (title) =>
                title ? `${title} — SIM-Inventaris Diskominfo Garut` : 'SIM-Inventaris Diskominfo Garut',
            resolve: (name) => {
                const pages = import.meta.glob('./pages/**/*.tsx', { eager: true }) as Record<
                    string,
                    { default: React.ComponentType }
                >;
                const target = pages[`./pages/${name}.tsx`];
                if (!target) {
                    throw new Error(`Inertia page not found: ${name}`);
                }
                return target.default;
            },
            setup({ el, App, props }) {
                createRoot(el).render(<App {...props} />);
            },
            progress: {
                color: '#2563eb',
                showSpinner: false,
            },
        });
    }
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootV2Runtime, { once: true });
    } else {
        bootV2Runtime();
    }
}
