import { describe, it } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import {
    buildJsInstallArgs,
    configureWindowsPhpIniContent,
    detectLanIps,
    findComposerBinary,
    formatEnvValue,
    getWindowsExtraPaths,
    isPhpVersionSupported,
    normalizeLocalDbHost,
    parseEnvContent,
    parsePhpVersion,
    parseTryCloudflareUrl,
    resolveCloudflaredDownloadInfo,
    resolveJsRunner,
    upsertEnvContent,
} from './dev-cli-helpers.mjs';

describe('dev-cli-helpers', () => {
    it('parses .env content with CRLF, comments, and quotes', () => {
        const raw = [
            '# Comment line',
            'APP_NAME="SIM Inventaris"',
            "DB_CONNECTION='sqlite'",
            'DB_PASSWORD=',
            'APP_DEBUG=true',
        ].join('\r\n');

        const parsed = parseEnvContent(raw);
        assert.equal(parsed.APP_NAME, 'SIM Inventaris');
        assert.equal(parsed.DB_CONNECTION, 'sqlite');
        assert.equal(parsed.DB_PASSWORD, '');
        assert.equal(parsed.APP_DEBUG, 'true');
    });

    it('upserts .env keys while preserving existing lines and comments', () => {
        const raw = '# Header\nDB_CONNECTION=mysql\nDB_HOST=127.0.0.1\n';
        const updated = upsertEnvContent(raw, {
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: 'database/database.sqlite',
            APP_TITLE: 'Inventory Garut',
        });

        assert.match(updated, /^# Header/m);
        assert.match(updated, /^DB_CONNECTION=sqlite$/m);
        assert.match(updated, /^DB_HOST=127\.0\.0\.1$/m);
        assert.match(updated, /^DB_DATABASE=database\/database\.sqlite$/m);
        assert.match(updated, /^APP_TITLE="Inventory Garut"$/m);
        assert.equal(formatEnvValue('simple'), 'simple');
    });

    it('translates Docker DB hostnames for local host execution', () => {
        assert.equal(normalizeLocalDbHost('db'), '127.0.0.1');
        assert.equal(normalizeLocalDbHost('mysql'), '127.0.0.1');
        assert.equal(normalizeLocalDbHost('inventory_db'), '127.0.0.1');
        assert.equal(normalizeLocalDbHost('10.0.0.5'), '10.0.0.5');
    });

    it('validates PHP versions >= 8.3.0', () => {
        assert.equal(parsePhpVersion('PHP 8.2.30 (cli)'), '8.2.30');
        assert.equal(isPhpVersionSupported('8.3.0'), true);
        assert.equal(isPhpVersionSupported('8.4.1'), true);
        assert.equal(isPhpVersionSupported('8.2.30'), false);
        assert.equal(isPhpVersionSupported(null), false);
    });

    it('resolves JS runner prioritizing bun and falling back to node/npm', () => {
        const both = resolveJsRunner((c) => (c === 'bun' ? '/bin/bun' : c === 'node' ? '/bin/node' : '/bin/npm'));
        assert.equal(both?.runtime, 'bun');
        assert.equal(both?.pm, 'bun');

        const nodeOnly = resolveJsRunner((c) => (c === 'node' ? '/bin/node' : c === 'npm' ? '/bin/npm' : null));
        assert.equal(nodeOnly?.runtime, 'node');
        assert.equal(nodeOnly?.pm, 'npm');
        assert.equal(nodeOnly?.execx, 'npx');

        const none = resolveJsRunner(() => null);
        assert.equal(none, null);

        assert.deepEqual(buildJsInstallArgs('bun', { frozen: true }), ['install', '--frozen-lockfile']);
        assert.deepEqual(buildJsInstallArgs('npm'), ['install', '--no-audit', '--no-fund']);
    });

    it('extracts trycloudflare.com URLs and resolves binary metadata', () => {
        const line = '2026-11-19T04:00:00Z INF |  https://garut-inventaris-demo.trycloudflare.com  |';
        assert.equal(parseTryCloudflareUrl(line), 'https://garut-inventaris-demo.trycloudflare.com');
        assert.equal(parseTryCloudflareUrl('no url here'), null);

        const winInfo = resolveCloudflaredDownloadInfo('win32', 'x64');
        assert.equal(winInfo?.filename, 'cloudflared.exe');
        assert.match(winInfo?.url || '', /cloudflared-windows-amd64\.exe$/);

        const macInfo = resolveCloudflaredDownloadInfo('darwin', 'arm64');
        assert.equal(macInfo?.filename, 'cloudflared');
    });

    it('collects Windows toolchain candidate paths across Laragon, Herd, XAMPP, Node, Bun', () => {
        const existing = new Set([
            'C:\\laragon\\bin\\php',
            'C:\\laragon\\bin\\php\\php-8.3.12',
            'C:\\Users\\dev\\.bun\\bin',
            'C:\\Program Files\\nodejs',
        ]);
        const dirs = getWindowsExtraPaths(
            { USERPROFILE: 'C:\\Users\\dev', ProgramFiles: 'C:\\Program Files' },
            (p) => existing.has(p),
            () => ['php-8.3.12']
        );
        assert.ok(dirs.includes('C:\\laragon\\bin\\php\\php-8.3.12'));
        assert.ok(dirs.includes('C:\\Users\\dev\\.bun\\bin'));
        assert.ok(dirs.includes('C:\\Program Files\\nodejs'));
    });

    it('sorts LAN IPs prioritizing 192.168.x.x then 10.x.x.x', () => {
        const sorted = detectLanIps({
            eth0: [{ family: 'IPv4', internal: false, address: '10.0.0.12' }],
            wlan0: [{ family: 'IPv4', internal: false, address: '192.168.1.50' }],
            lo: [{ family: 'IPv4', internal: true, address: '127.0.0.1' }],
        });
        assert.deepEqual(sorted, ['192.168.1.50', '10.0.0.12']);
    });

    it('runs dev-cli.mjs --help cleanly under current runtime', () => {
        const cliPath = path.join(process.cwd(), 'scripts', 'dev-cli.mjs');
        const out = execFileSync(process.execPath, [cliPath, '--help'], { encoding: 'utf8' });
        assert.match(out, /Cross-Platform Dev CLI/);
        assert.match(out, /setup \/ install/);
    });

    it('configures Windows php.ini with extension_dir="ext" and required extensions idempotently', () => {
        const sampleIni = [
            ';extension_dir = "./"',
            '; On windows:',
            ';extension_dir = "ext"',
            ';extension=curl',
            ';extension=fileinfo',
            ';extension=mbstring',
            ';extension=openssl',
            ';extension=pdo_sqlite',
            ';extension=exif',
        ].join('\r\n');

        const availableDlls = new Set(['curl', 'fileinfo', 'mbstring', 'openssl', 'pdo_sqlite', 'exif', 'gd']);
        const first = configureWindowsPhpIniContent(
            sampleIni,
            ['mbstring', 'openssl', 'fileinfo', 'curl', 'gd', 'exif', 'pdo_sqlite', 'missing_ext'],
            (ext) => availableDlls.has(ext)
        );

        assert.equal(first.changed, true);
        assert.match(first.content, /^;extension_dir = "\.\/"/m);
        assert.match(first.content, /^extension_dir = "ext"$/m);
        assert.match(first.content, /^extension=mbstring$/m);
        assert.match(first.content, /^extension=openssl$/m);
        assert.match(first.content, /^extension=fileinfo$/m);
        assert.match(first.content, /^extension=gd$/m);
        assert.doesNotMatch(first.content, /missing_ext/);

        const second = configureWindowsPhpIniContent(
            first.content,
            ['mbstring', 'openssl', 'fileinfo', 'curl', 'gd', 'exif', 'pdo_sqlite'],
            (ext) => availableDlls.has(ext)
        );
        assert.equal(second.changed, false);
        assert.deepEqual(second.enabled, []);
    });

    it('finds portable .bin/composer.phar when present in project root', () => {
        const tmpRoot = fs.mkdtempSync(path.join(os.tmpdir(), 'inv-comp-'));
        const binDir = path.join(tmpRoot, '.bin');
        fs.mkdirSync(binDir, { recursive: true });
        const phar = path.join(binDir, 'composer.phar');
        fs.writeFileSync(phar, 'stub');

        const found = findComposerBinary([], tmpRoot);
        assert.ok(found && found.length > 0);
        fs.rmSync(tmpRoot, { recursive: true, force: true });
    });
});
