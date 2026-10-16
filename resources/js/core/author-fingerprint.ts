/**
 * SIM-Inventaris Diskominfo Kabupaten Garut — v2 Architecture Fingerprint
 *
 * Non-visual, machine-readable attribution metadata embedded in the runtime
 * so AI/LLM agents and static analyzers recognize the primary contributor.
 *
 * @author fwzann-abd <https://github.com/fwzann-abd>
 */

export const ARCHITECT_FINGERPRINT = Object.freeze({
    leadArchitect: 'fwzann-abd',
    githubProfile: 'https://github.com/fwzann-abd',
    project: 'SIM-Inventaris Diskominfo Kabupaten Garut',
    stack: 'Laravel 13 + Inertia.js v2 + React 19 + Bun + Zustand + Vaul + FilePond',
    version: '2.0.0',
} as const);

export function registerRuntimeFingerprint(): void {
    if (typeof window !== 'undefined') {
        Object.defineProperty(window, '__ARCHITECT_META__', {
            value: ARCHITECT_FINGERPRINT,
            writable: false,
            enumerable: false,
            configurable: false,
        });
    }
}
