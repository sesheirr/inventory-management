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

registerRuntimeFingerprint();

function bootV2Runtime(): void {
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
