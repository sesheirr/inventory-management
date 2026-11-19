/**
 * Cross-platform helpers for SIM-Inventaris dev-cli & LAN HTTPS proxy.
 * Uses only Node/Bun built-in modules so it runs before npm/bun install.
 */

import fs from 'node:fs';
import net from 'node:net';
import os from 'node:os';
import path from 'node:path';
import { execFileSync, spawnSync } from 'node:child_process';

/**
 * Parse `.env` text into a key-value map (handles CRLF, quotes, comments).
 * @param {string} content
 * @returns {Record<string, string>}
 */
export function parseEnvContent(content) {
    /** @type {Record<string, string>} */
    const result = {};
    const lines = content.split(/\r?\n/);

    for (const rawLine of lines) {
        const trimmed = rawLine.trim();
        if (!trimmed || trimmed.startsWith('#')) {
            continue;
        }
        const eqIdx = rawLine.indexOf('=');
        if (eqIdx <= 0) {
            continue;
        }
        const key = rawLine.slice(0, eqIdx).trim();
        if (!key || key.startsWith('#')) {
            continue;
        }
        let val = rawLine.slice(eqIdx + 1).trim();
        if (
            (val.startsWith('"') && val.endsWith('"') && val.length >= 2) ||
            (val.startsWith("'") && val.endsWith("'") && val.length >= 2)
        ) {
            val = val.slice(1, -1);
        }
        result[key] = val;
    }

    return result;
}

/**
 * Format a `.env` value, quoting when whitespace or `#` is present.
 * @param {string} value
 * @returns {string}
 */
export function formatEnvValue(value) {
    const str = String(value ?? '');
    if (str === '') {
        return '';
    }
    if (/[\s#]/.test(str) && !(str.startsWith('"') && str.endsWith('"'))) {
        return `"${str.replace(/"/g, '\\"')}"`;
    }
    return str;
}

/**
 * Update or append keys in `.env` content while preserving comments and order.
 * @param {string} content
 * @param {Record<string, string>} updates
 * @returns {string}
 */
export function upsertEnvContent(content, updates) {
    const eol = content.includes('\r\n') ? '\r\n' : '\n';
    const lines = content === '' ? [] : content.split(/\r?\n/);
    const remaining = new Map(Object.entries(updates));

    const updatedLines = lines.map((line) => {
        const trimmed = line.trim();
        if (!trimmed || trimmed.startsWith('#')) {
            return line;
        }
        const eqIdx = line.indexOf('=');
        if (eqIdx <= 0) {
            return line;
        }
        const key = line.slice(0, eqIdx).trim();
        if (!remaining.has(key)) {
            return line;
        }
        const nextVal = formatEnvValue(remaining.get(key) ?? '');
        remaining.delete(key);
        return `${key}=${nextVal}`;
    });

    for (const [key, val] of remaining.entries()) {
        updatedLines.push(`${key}=${formatEnvValue(val)}`);
    }

    return updatedLines.join(eol);
}

/**
 * Translate Docker-internal DB hostnames (`db`, `mysql`, `inventory_db`) to `127.0.0.1` for host runs.
 * @param {string} host
 * @returns {string}
 */
export function normalizeLocalDbHost(host) {
    const normalized = String(host || '').trim().toLowerCase();
    if (normalized === 'db' || normalized === 'mysql' || normalized === 'inventory_db') {
        return '127.0.0.1';
    }
    return host || '127.0.0.1';
}

/**
 * Parse `php -v` output into major.minor.patch string.
 * @param {string} output
 * @returns {string | null}
 */
export function parsePhpVersion(output) {
    const match = String(output || '').match(/PHP\s+(\d+\.\d+\.\d+)/i);
    return match ? match[1] : null;
}

/**
 * Compare two semver strings (`a > b` -> 1, `a < b` -> -1, equal -> 0).
 * @param {string} aStr
 * @param {string} bStr
 * @returns {number}
 */
export function compareSemver(aStr, bStr) {
    const a = String(aStr || '0.0.0').split('.').map((n) => Number.parseInt(n, 10) || 0);
    const b = String(bStr || '0.0.0').split('.').map((n) => Number.parseInt(n, 10) || 0);
    for (let i = 0; i < 3; i++) {
        if ((a[i] || 0) > (b[i] || 0)) return 1;
        if ((a[i] || 0) < (b[i] || 0)) return -1;
    }
    return 0;
}

/**
 * Check if a PHP semver string satisfies minimum version (default 8.3.0 per composer.json).
 * @param {string | null} versionStr
 * @param {string} minVersion
 * @returns {boolean}
 */
export function isPhpVersionSupported(versionStr, minVersion = '8.3.0') {
    if (!versionStr) {
        return false;
    }
    return compareSemver(versionStr, minVersion) >= 0;
}

/**
 * Resolve the best available PHP CLI binary (preferring highest version >= 8.3.0 across PATH, Herd, Laragon, Homebrew).
 * @param {string[]} [extraDirs]
 * @returns {string | null}
 */
export function resolveBestPhpBinary(extraDirs = []) {
    const isWin = process.platform === 'win32';
    const home = os.homedir();
    const searchDirs = [...extraDirs];

    if (!isWin) {
        for (const dir of [
            path.join(home, 'Library', 'Application Support', 'Herd', 'bin'),
            '/opt/homebrew/opt/php@8.4/bin',
            '/opt/homebrew/opt/php@8.3/bin',
            '/opt/homebrew/bin',
            '/usr/local/bin',
        ]) {
            if (!searchDirs.includes(dir) && fs.existsSync(dir)) {
                searchDirs.push(dir);
            }
        }
    }
    for (const dir of (process.env.PATH || '').split(path.delimiter).filter(Boolean)) {
        if (!searchDirs.includes(dir)) {
            searchDirs.push(dir);
        }
    }

    const binNames = isWin
        ? ['php.exe', 'php85.exe', 'php84.exe', 'php83.exe']
        : ['php', 'php85', 'php84', 'php83', 'php8.5', 'php8.4', 'php8.3'];

    const seen = new Set();
    /** @type {{ bin: string, version: string }[]} */
    const candidates = [];

    for (const dir of searchDirs) {
        for (const name of binNames) {
            const full = path.join(dir, name);
            if (seen.has(full) || !fs.existsSync(full)) {
                continue;
            }
            seen.add(full);
            try {
                if (!fs.statSync(full).isFile()) continue;
                const probe = spawnSync(full, ['-v'], { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] });
                if (probe.status !== 0) continue;
                const ver = parsePhpVersion(probe.stdout || '');
                if (ver) {
                    candidates.push({ bin: full, version: ver });
                }
            } catch {
                // Ignore broken candidate
            }
        }
    }

    if (candidates.length === 0) {
        return findExecutable('php', extraDirs);
    }

    candidates.sort((a, b) => compareSemver(b.version, a.version));
    return candidates[0].bin;
}

/**
 * Resolve preferred JS runtime & package manager (`bun` priority -> `node`/`npm` fallback).
 * @param {(cmd: string) => string | null} findCmd
 * @param {{ preferred?: 'bun' | 'node' }} [options]
 * @returns {{ runtime: 'bun' | 'node', runtimeBin: string, pm: 'bun' | 'npm', pmBin: string, execx: 'bunx' | 'npx' } | null}
 */
export function resolveJsRunner(findCmd, options = {}) {
    const bunBin = findCmd('bun');
    const nodeBin = findCmd('node');
    const npmBin = findCmd('npm');

    if (options.preferred === 'node' && nodeBin && npmBin) {
        return { runtime: 'node', runtimeBin: nodeBin, pm: 'npm', pmBin: npmBin, execx: 'npx' };
    }

    if (bunBin) {
        return { runtime: 'bun', runtimeBin: bunBin, pm: 'bun', pmBin: bunBin, execx: 'bunx' };
    }

    if (nodeBin && npmBin) {
        return { runtime: 'node', runtimeBin: nodeBin, pm: 'npm', pmBin: npmBin, execx: 'npx' };
    }

    if (nodeBin) {
        return { runtime: 'node', runtimeBin: nodeBin, pm: 'npm', pmBin: 'npm', execx: 'npx' };
    }

    return null;
}

/**
 * Build install arguments for Bun or npm (handles `bun.lock` + `.npmrc` gracefully).
 * @param {'bun' | 'npm'} pm
 * @param {{ frozen?: boolean }} [opts]
 * @returns {string[]}
 */
export function buildJsInstallArgs(pm, opts = {}) {
    if (pm === 'bun') {
        return opts.frozen ? ['install', '--frozen-lockfile'] : ['install'];
    }
    return ['install', '--no-audit', '--no-fund'];
}

/**
 * Extract `https://*.trycloudflare.com` URL from a cloudflared output line.
 * @param {string} line
 * @returns {string | null}
 */
export function parseTryCloudflareUrl(line) {
    const match = String(line || '').match(/https:\/\/[a-zA-Z0-9-]+\.trycloudflare\.com/);
    return match ? match[0] : null;
}

/**
 * Resolve official `cloudflared` portable download URL and local `.bin/` path for current platform/arch.
 * @param {NodeJS.Platform} platform
 * @param {string} arch
 * @returns {{ url: string, filename: string } | null}
 */
export function resolveCloudflaredDownloadInfo(platform = process.platform, arch = process.arch) {
    const base = 'https://github.com/cloudflare/cloudflared/releases/latest/download';
    if (platform === 'win32') {
        const asset = arch === 'arm64' ? 'cloudflared-windows-arm64.exe' : 'cloudflared-windows-amd64.exe';
        return { url: `${base}/${asset}`, filename: 'cloudflared.exe' };
    }
    if (platform === 'linux') {
        const asset = arch === 'arm64' ? 'cloudflared-linux-arm64' : 'cloudflared-linux-amd64';
        return { url: `${base}/${asset}`, filename: 'cloudflared' };
    }
    if (platform === 'darwin') {
        const asset = arch === 'arm64' ? 'cloudflared-darwin-arm64.tgz' : 'cloudflared-darwin-amd64.tgz';
        return { url: `${base}/${asset}`, filename: 'cloudflared' };
    }
    return null;
}

/**
 * Collect extra Windows toolchain directories (Laragon, Herd, XAMPP, Scoop, Bun, Node, Git, Docker, OpenSSL).
 * @param {NodeJS.ProcessEnv} [env]
 * @param {(p: string) => boolean} [existsFn]
 * @param {(p: string) => string[]} [readDirsFn]
 * @returns {string[]}
 */
export function getWindowsExtraPaths(
    env = process.env,
    existsFn = fs.existsSync,
    readDirsFn = (dir) =>
        fs
            .readdirSync(dir, { withFileTypes: true })
            .filter((d) => d.isDirectory())
            .map((d) => d.name)
            .sort()
            .reverse()
) {
    const userProfile = env.USERPROFILE || env.HOME || '';
    const programFiles = env.ProgramFiles || 'C:\\Program Files';
    const localAppData = env.LOCALAPPDATA || (userProfile ? path.win32.join(userProfile, 'AppData', 'Local') : '');
    const candidates = [];

    // Laragon PHP & MySQL (highest version first)
    for (const root of ['C:\\laragon\\bin', 'D:\\laragon\\bin']) {
        const phpRoot = path.win32.join(root, 'php');
        if (existsFn(phpRoot)) {
            for (const dir of readDirsFn(phpRoot)) {
                candidates.push(path.win32.join(phpRoot, dir));
            }
        }
        const mysqlRoot = path.win32.join(root, 'mysql');
        if (existsFn(mysqlRoot)) {
            for (const dir of readDirsFn(mysqlRoot)) {
                candidates.push(path.win32.join(mysqlRoot, dir, 'bin'));
            }
        }
        candidates.push(path.win32.join(root, 'composer'));
    }

    // Laravel Herd, XAMPP, Scoop, Winget PHP, Composer
    if (userProfile) {
        candidates.push(path.win32.join(userProfile, '.config', 'herd', 'bin'));
        candidates.push(path.win32.join(userProfile, '.composer', 'bin'));
        candidates.push(path.win32.join(userProfile, '.bun', 'bin'));
        candidates.push(path.win32.join(userProfile, 'scoop', 'shims'));
    }
    if (localAppData) {
        candidates.push(path.win32.join(localAppData, 'Programs', 'Php'));
        candidates.push(path.win32.join(localAppData, 'Programs', 'Composer'));
        candidates.push(path.win32.join(localAppData, 'Microsoft', 'WinGet', 'Links'));
        const wingetPkgs = path.win32.join(localAppData, 'Microsoft', 'WinGet', 'Packages');
        if (existsFn(wingetPkgs)) {
            for (const dir of readDirsFn(wingetPkgs)) {
                if (/^PHP\.PHP/i.test(dir)) {
                    candidates.push(path.win32.join(wingetPkgs, dir));
                }
            }
        }
    }

    candidates.push(
        'C:\\xampp\\php',
        'C:\\php',
        'C:\\ProgramData\\ComposerSetup\\bin',
        path.win32.join(programFiles, 'nodejs'),
        path.win32.join(programFiles, 'Git', 'cmd'),
        path.win32.join(programFiles, 'Git', 'usr', 'bin'),
        path.win32.join(programFiles, 'Docker', 'Docker', 'resources', 'bin'),
        path.win32.join(programFiles, 'OpenSSL-Win64', 'bin')
    );

    return candidates.filter((dir) => dir && existsFn(dir));
}

/**
 * Find an executable on PATH or extra platform directories.
 * @param {string} name
 * @param {string[]} [extraDirs]
 * @returns {string | null}
 */
export function findExecutable(name, extraDirs = []) {
    const isWin = process.platform === 'win32';
    const exts = isWin ? ['.exe', '.cmd', '.bat', ''] : [''];
    const pathDirs = (process.env.PATH || '').split(path.delimiter).filter(Boolean);

    // On macOS, Herd/Homebrew binaries may not always be in non-login shells
    if (!isWin) {
        const home = os.homedir();
        for (const fallbackDir of [
            path.join(home, '.bun', 'bin'),
            path.join(home, 'Library', 'Application Support', 'Herd', 'bin'),
            '/opt/homebrew/bin',
            '/usr/local/bin',
        ]) {
            if (!pathDirs.includes(fallbackDir) && fs.existsSync(fallbackDir)) {
                pathDirs.push(fallbackDir);
            }
        }
    }

    const searchDirs = [...extraDirs, ...pathDirs];
    for (const dir of searchDirs) {
        for (const ext of exts) {
            const fullPath = path.join(dir, name.endsWith(ext) ? name : `${name}${ext}`);
            if (fs.existsSync(fullPath)) {
                try {
                    const stat = fs.statSync(fullPath);
                    if (stat.isFile()) {
                        return fullPath;
                    }
                } catch {
                    // Ignore inaccessible paths
                }
            }
        }
    }

    // Fallback to OS `where` / `which`
    try {
        const probe = spawnSync(isWin ? 'where.exe' : 'which', [name], { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] });
        if (probe.status === 0 && probe.stdout) {
            const first = probe.stdout.split(/\r?\n/).map((s) => s.trim()).find(Boolean);
            if (first && fs.existsSync(first)) {
                return first;
            }
        }
    } catch {
        // Ignore probe failures
    }

    return null;
}

/**
 * Detect non-internal IPv4 LAN addresses sorted by private-subnet priority.
 * @param {NodeJS.Dict<os.NetworkInterfaceInfo[]>} [networkInterfaces]
 * @returns {string[]}
 */
export function detectLanIps(networkInterfaces = os.networkInterfaces()) {
    const ips = [];
    for (const name of Object.keys(networkInterfaces)) {
        for (const netInfo of networkInterfaces[name] || []) {
            if (netInfo.family === 'IPv4' && !netInfo.internal) {
                ips.push(netInfo.address);
            }
        }
    }
    ips.sort((a, b) => {
        const score = (ip) => (ip.startsWith('192.168.') ? 0 : ip.startsWith('10.') ? 1 : 2);
        return score(a) - score(b);
    });
    return ips.length > 0 ? ips : ['127.0.0.1'];
}

/**
 * Probe whether a local TCP port is already in use.
 * @param {number} port
 * @param {string} [host]
 * @returns {Promise<boolean>} true if a service is already listening
 */
export function isPortInUse(port, host = '127.0.0.1') {
    return new Promise((resolve) => {
        const socket = net.createConnection({ port, host });
        socket.setTimeout(600);
        socket.once('connect', () => {
            socket.destroy();
            resolve(true);
        });
        socket.once('timeout', () => {
            socket.destroy();
            resolve(false);
        });
        socket.once('error', () => {
            socket.destroy();
            resolve(false);
        });
    });
}

/**
 * Cross-platform process tree termination (prevents orphaned php/vite children on Windows).
 * @param {import('node:child_process').ChildProcess | null | undefined} child
 */
export function killProcessTree(child) {
    if (!child || !child.pid || child.killed) {
        return;
    }
    try {
        if (process.platform === 'win32') {
            execFileSync('taskkill', ['/F', '/T', '/PID', String(child.pid)], { stdio: 'ignore' });
            return;
        }
    } catch {
        // Fallback to standard signal below
    }
    try {
        child.kill('SIGTERM');
    } catch {
        // Process already exited
    }
}

/**
 * Extensions required/recommended by Laravel, Composer, Intervention Image, MediaLibrary, Excel, SQLite & MySQL on Windows.
 * Note: `mbstring` MUST precede `exif` on Windows.
 */
export const WINDOWS_DESIRED_PHP_EXTENSIONS = [
    'mbstring',
    'openssl',
    'fileinfo',
    'curl',
    'gd',
    'zip',
    'intl',
    'exif',
    'pdo_sqlite',
    'pdo_mysql',
    'mysqli',
    'bcmath',
    'sodium',
];

/**
 * Enable `extension_dir = "ext"` and desired extensions in a Windows `php.ini` string.
 * @param {string} iniContent
 * @param {string[]} extensions
 * @param {(ext: string) => boolean} extExistsFn
 * @returns {{ content: string, changed: boolean, enabled: string[] }}
 */
export function configureWindowsPhpIniContent(iniContent, extensions, extExistsFn) {
    const eol = iniContent.includes('\r\n') ? '\r\n' : '\n';
    const lines = iniContent === '' ? [] : iniContent.split(/\r?\n/);
    let changed = false;
    const enabled = [];

    // 1. Ensure Windows `extension_dir = "ext"` is active (never uncomment `./`)
    const hasActiveExtDir = lines.some((l) => /^\s*extension_dir\s*=/i.test(l));
    if (!hasActiveExtDir) {
        let uncommented = false;
        for (let i = 0; i < lines.length; i++) {
            if (/^\s*;\s*extension_dir\s*=\s*"?ext"?\s*$/i.test(lines[i])) {
                lines[i] = 'extension_dir = "ext"';
                uncommented = true;
                changed = true;
                break;
            }
        }
        if (!uncommented) {
            lines.push('extension_dir = "ext"');
            changed = true;
        }
    }

    // 2. Enable each extension whose DLL exists in `ext/`
    for (const ext of extensions) {
        if (!extExistsFn(ext)) {
            continue;
        }
        const activeRe = new RegExp(`^\\s*extension\\s*=\\s*"?(?:php_)?${ext}(?:\\.dll)?"?\\s*(?:;.*)?$`, 'i');
        if (lines.some((l) => activeRe.test(l))) {
            continue;
        }

        const commentedRe = new RegExp(`^\\s*;\\s*extension\\s*=\\s*"?(?:php_)?${ext}(?:\\.dll)?"?\\s*(?:;.*)?$`, 'i');
        const idx = lines.findIndex((l) => commentedRe.test(l));
        if (idx !== -1) {
            lines[idx] = `extension=${ext}`;
        } else {
            lines.push(`extension=${ext}`);
        }
        enabled.push(ext);
        changed = true;
    }

    return {
        content: lines.join(eol) + (iniContent.endsWith('\n') || lines.length === 0 ? '' : eol),
        changed,
        enabled,
    };
}

/**
 * Automatically create/update Windows `php.ini` to enable required PHP extensions.
 * Resolves `PHP_BINARY` to handle WinGet/Scoop shims pointing to the real PHP directory.
 * @param {string} phpBin
 * @param {string[]} [desiredExtensions]
 * @returns {{ ok: boolean, iniPath: string | null, createdIni: boolean, enabled: string[], error?: string }}
 */
export function autoEnableWindowsPhpExtensions(phpBin, desiredExtensions = WINDOWS_DESIRED_PHP_EXTENSIONS) {
    if (process.platform !== 'win32' || !phpBin) {
        return { ok: true, iniPath: null, createdIni: false, enabled: [] };
    }

    const probe = spawnSync(
        phpBin,
        ['-n', '-r', "echo json_encode(['bin' => PHP_BINARY]);"],
        { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] }
    );
    const iniProbe = spawnSync(
        phpBin,
        ['-r', "echo json_encode(['ini' => php_ini_loaded_file() ?: '']);"],
        { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] }
    );

    let realBin = phpBin;
    try {
        const parsed = JSON.parse((probe.stdout || '').trim());
        if (parsed.bin) realBin = parsed.bin;
    } catch {
        // Keep original phpBin
    }

    let loadedIni = '';
    try {
        const parsedIni = JSON.parse((iniProbe.stdout || '').trim());
        if (parsedIni.ini) loadedIni = parsedIni.ini;
    } catch {
        // Ignore
    }

    const phpDir = path.dirname(realBin);
    const extDir = path.join(phpDir, 'ext');
    let iniPath = loadedIni && fs.existsSync(loadedIni) ? loadedIni : path.join(phpDir, 'php.ini');
    let createdIni = false;

    try {
        if (!fs.existsSync(iniPath)) {
            const devTemplate = path.join(phpDir, 'php.ini-development');
            const prodTemplate = path.join(phpDir, 'php.ini-production');
            if (fs.existsSync(devTemplate)) {
                fs.copyFileSync(devTemplate, iniPath);
                createdIni = true;
            } else if (fs.existsSync(prodTemplate)) {
                fs.copyFileSync(prodTemplate, iniPath);
                createdIni = true;
            } else {
                fs.writeFileSync(iniPath, '; Auto-generated by SIM-Inventaris dev-cli\r\n', 'utf8');
                createdIni = true;
            }
        }

        const raw = fs.readFileSync(iniPath, 'utf8');
        const res = configureWindowsPhpIniContent(raw, desiredExtensions, (ext) =>
            fs.existsSync(path.join(extDir, `php_${ext}.dll`))
        );

        if (res.changed) {
            fs.writeFileSync(iniPath, res.content, 'utf8');
        }

        return { ok: true, iniPath, createdIni, enabled: res.enabled };
    } catch (err) {
        return {
            ok: false,
            iniPath,
            createdIni,
            enabled: [],
            error: err?.message || String(err),
        };
    }
}

/**
 * Find Composer executable or portable `composer.phar` across PATH, extra dirs, `.bin/`, and `~/.composer/bin`.
 * @param {string[]} [extraDirs]
 * @param {string} [rootDir]
 * @returns {string | null}
 */
export function findComposerBinary(extraDirs = [], rootDir = process.cwd()) {
    const found = findExecutable('composer', extraDirs);
    if (found) {
        return found;
    }

    const candidates = [
        path.join(rootDir, '.bin', 'composer.phar'),
        path.join(os.homedir(), '.composer', 'bin', 'composer.phar'),
    ];
    for (const c of candidates) {
        if (fs.existsSync(c)) {
            return c;
        }
    }
    return null;
}

/**
 * Download official `composer-stable.phar` into `targetDir` and create platform wrapper (`composer.bat` / `composer`).
 * @param {string} phpBin
 * @param {string} targetDir
 * @returns {Promise<{ ok: boolean, bin: string | null, error?: string }>}
 */
export async function ensurePortableComposer(phpBin, targetDir) {
    try {
        fs.mkdirSync(targetDir, { recursive: true });
        const pharPath = path.join(targetDir, 'composer.phar');

        if (!fs.existsSync(pharPath)) {
            const resp = await fetch('https://getcomposer.org/composer-stable.phar');
            if (!resp.ok) {
                return { ok: false, bin: null, error: `HTTP ${resp.status} saat mengunduh composer-stable.phar` };
            }
            const buf = Buffer.from(await resp.arrayBuffer());
            fs.writeFileSync(pharPath, buf);
        }

        if (process.platform === 'win32') {
            const batPath = path.join(targetDir, 'composer.bat');
            fs.writeFileSync(batPath, `@echo off\r\n"${phpBin}" "%~dp0composer.phar" %*\r\n`, 'utf8');
        } else {
            const shPath = path.join(targetDir, 'composer');
            fs.writeFileSync(shPath, `#!/usr/bin/env sh\nexec "${phpBin}" "$(dirname "$0")/composer.phar" "$@"\n`, 'utf8');
            fs.chmodSync(shPath, 0o755);
        }

        const verify = spawnSync(phpBin, [pharPath, '--version'], {
            encoding: 'utf8',
            stdio: ['ignore', 'pipe', 'pipe'],
        });
        if (verify.status !== 0) {
            return { ok: false, bin: null, error: (verify.stderr || verify.stdout || 'Verifikasi composer.phar gagal').trim() };
        }

        return { ok: true, bin: pharPath };
    } catch (err) {
        return { ok: false, bin: null, error: err?.message || String(err) };
    }
}


