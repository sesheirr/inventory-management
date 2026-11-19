#!/usr/bin/env node
/**
 * SIM-Inventaris Diskominfo Garut — Unified Cross-Platform Dev CLI
 * Works seamlessly on Windows (CMD/PowerShell/Git Bash), macOS, and Linux
 * under either Bun or Node.js (>=18) with zero third-party dependencies.
 */

import fs from 'node:fs';
import http from 'node:http';
import path from 'node:path';
import readline from 'node:readline';
import { execFileSync, spawn, spawnSync } from 'node:child_process';
import {
    buildJsInstallArgs,
    detectLanIps,
    findExecutable,
    getWindowsExtraPaths,
    isPhpVersionSupported,
    isPortInUse,
    killProcessTree,
    normalizeLocalDbHost,
    parseEnvContent,
    parsePhpVersion,
    parseTryCloudflareUrl,
    resolveBestPhpBinary,
    resolveCloudflaredDownloadInfo,
    resolveJsRunner,
    upsertEnvContent,
} from './dev-cli-helpers.mjs';

const ROOT_DIR = process.cwd();
const ENV_PATH = path.join(ROOT_DIR, '.env');
const ENV_EXAMPLE_PATH = path.join(ROOT_DIR, '.env.example');
const EXTRA_DIRS = process.platform === 'win32' ? getWindowsExtraPaths() : [];
const USE_COLOR = Boolean(process.stdout.isTTY) && !process.env.NO_COLOR;

const C = {
    reset: USE_COLOR ? '\x1b[0m' : '',
    bold: USE_COLOR ? '\x1b[1m' : '',
    cyan: USE_COLOR ? '\x1b[36m' : '',
    green: USE_COLOR ? '\x1b[32m' : '',
    yellow: USE_COLOR ? '\x1b[33m' : '',
    red: USE_COLOR ? '\x1b[31m' : '',
    gray: USE_COLOR ? '\x1b[90m' : '',
    magenta: USE_COLOR ? '\x1b[35m' : '',
};

const REQUIRED_PHP_EXTENSIONS = ['mbstring', 'xml', 'ctype', 'json', 'openssl', 'pdo', 'fileinfo'];

function logHeader(title) {
    console.log('');
    console.log(`${C.cyan}${C.bold}======================================================================${C.reset}`);
    console.log(`${C.cyan}${C.bold}  ${title}${C.reset}`);
    console.log(`${C.cyan}${C.bold}======================================================================${C.reset}`);
}

function logStep(msg) {
    console.log(`\n${C.cyan}>> ${msg}${C.reset}`);
}

function logOk(msg) {
    console.log(`${C.green}   [OK]${C.reset} ${msg}`);
}

function logWarn(msg) {
    console.log(`${C.yellow}   [WARN]${C.reset} ${msg}`);
}

function logErr(msg) {
    console.error(`${C.red}   [ERROR]${C.reset} ${msg}`);
}

function ask(promptText) {
    return new Promise((resolve) => {
        const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
        rl.question(promptText, (answer) => {
            rl.close();
            resolve(answer.trim());
        });
    });
}

function parseCliArgs(argv) {
    const rawArgs = argv.slice(2);
    const flags = new Set();
    const values = new Map();
    const positional = [];

    for (const item of rawArgs) {
        if (item.startsWith('--')) {
            const eq = item.indexOf('=');
            if (eq !== -1) {
                values.set(item.slice(2, eq).toLowerCase(), item.slice(eq + 1));
            } else {
                flags.add(item.slice(2).toLowerCase());
            }
        } else if (item === '-h') {
            flags.add('help');
        } else if (item === '-y') {
            flags.add('yes');
        } else {
            positional.push(item);
        }
    }

    const command = (positional[0] || (flags.has('help') ? 'help' : 'help')).toLowerCase();
    return {
        command,
        extraArgs: positional.slice(1),
        flags,
        values,
        isLocal: flags.has('local'),
        isDocker: flags.has('docker'),
        checkOnly: flags.has('check-only'),
        noBrowser: flags.has('no-browser'),
        yes: flags.has('yes'),
        autoStart: flags.has('start'),
        dbTarget: values.get('db') || (flags.has('db-local') ? 'local' : flags.has('db-live') ? 'live' : ''),
        port: Number(values.get('port') || process.env.PORT || 8000),
        vitePort: Number(values.get('vite-port') || process.env.VITE_PORT || 5173),
        webhookPort: Number(values.get('webhook-port') || process.env.WEBHOOK_PORT || 8085),
        httpsPort: Number(values.get('https-port') || process.env.HTTPS_PORT || 8443),
    };
}

function findCmd(name) {
    return findExecutable(name, EXTRA_DIRS);
}

function runSync(bin, args, opts = {}) {
    const useShell = process.platform === 'win32' && /\.(cmd|bat)$/i.test(bin);
    const res = spawnSync(bin, args, {
        cwd: ROOT_DIR,
        stdio: opts.capture ? ['ignore', 'pipe', 'pipe'] : 'inherit',
        encoding: opts.capture ? 'utf8' : undefined,
        shell: useShell,
        env: opts.env ? { ...process.env, ...opts.env } : process.env,
    });
    return res;
}

function runSyncOrFail(bin, args, label, opts = {}) {
    const res = runSync(bin, args, opts);
    if (res.status !== 0) {
        logErr(`${label} gagal (exit code ${res.status ?? 'unknown'}).`);
        process.exit(res.status || 1);
    }
}

function isDockerDaemonRunning(dockerBin) {
    if (!dockerBin) {
        return false;
    }
    const res = runSync(dockerBin, ['info'], { capture: true });
    return res.status === 0;
}

function detectEnvironment() {
    const phpBin = resolveBestPhpBinary(EXTRA_DIRS);
    let phpVersion = null;
    let phpOk = false;
    let phpMissingExts = [];

    if (phpBin) {
        const verRes = runSync(phpBin, ['-v'], { capture: true });
        phpVersion = parsePhpVersion(verRes.stdout || '');
        phpOk = isPhpVersionSupported(phpVersion, '8.3.0');

        const modRes = runSync(phpBin, ['-m'], { capture: true });
        const loadedMods = new Set(
            String(modRes.stdout || '')
                .split(/\r?\n/)
                .map((s) => s.trim().toLowerCase())
                .filter(Boolean)
        );
        phpMissingExts = REQUIRED_PHP_EXTENSIONS.filter((ext) => !loadedMods.has(ext));
    }

    const composerBin = findCmd('composer');
    const jsRunner = resolveJsRunner(findCmd);
    const dockerBin = findCmd('docker');
    const dockerRunning = isDockerDaemonRunning(dockerBin);
    const opensslBin = findCmd('openssl');
    const cloudflaredBin =
        findCmd('cloudflared') ||
        (fs.existsSync(path.join(ROOT_DIR, '.bin', 'cloudflared.exe'))
            ? path.join(ROOT_DIR, '.bin', 'cloudflared.exe')
            : fs.existsSync(path.join(ROOT_DIR, '.bin', 'cloudflared'))
              ? path.join(ROOT_DIR, '.bin', 'cloudflared')
              : null);

    return {
        platform: process.platform,
        arch: process.arch,
        phpBin,
        phpVersion,
        phpOk,
        phpMissingExts,
        composerBin,
        jsRunner,
        dockerBin,
        dockerRunning,
        opensslBin,
        cloudflaredBin,
    };
}

function ensureEnvFile() {
    if (!fs.existsSync(ENV_PATH)) {
        if (!fs.existsSync(ENV_EXAMPLE_PATH)) {
            logErr('.env maupun .env.example tidak ditemukan.');
            process.exit(1);
        }
        fs.copyFileSync(ENV_EXAMPLE_PATH, ENV_PATH);
        logOk('File .env dibuat otomatis dari .env.example.');
    }
    return parseEnvContent(fs.readFileSync(ENV_PATH, 'utf8'));
}

function updateEnvKeys(updates) {
    const existing = fs.existsSync(ENV_PATH) ? fs.readFileSync(ENV_PATH, 'utf8') : '';
    fs.writeFileSync(ENV_PATH, upsertEnvContent(existing, updates), 'utf8');
}

function testPhpDatabase(phpBin, envMap, isDockerMode) {
    const conn = (envMap.DB_CONNECTION || 'sqlite').toLowerCase();
    if (conn === 'sqlite') {
        const modRes = runSync(phpBin, ['-m'], { capture: true });
        const hasSqlite = String(modRes.stdout || '').toLowerCase().includes('pdo_sqlite');
        if (!hasSqlite) {
            return { ok: false, error: 'Ekstensi PHP pdo_sqlite belum aktif.' };
        }
        let sqlitePath = envMap.DB_DATABASE || 'database/database.sqlite';
        if (!sqlitePath.includes('/') && !sqlitePath.includes('\\') && !sqlitePath.endsWith('.sqlite')) {
            sqlitePath = 'database/database.sqlite';
        }
        const absSqlite = path.isAbsolute(sqlitePath) ? sqlitePath : path.join(ROOT_DIR, sqlitePath);
        fs.mkdirSync(path.dirname(absSqlite), { recursive: true });
        if (!fs.existsSync(absSqlite)) {
            fs.writeFileSync(absSqlite, '');
        }
        return { ok: true, info: `SQLite (${sqlitePath})` };
    }

    if (conn === 'mysql' || conn === 'mariadb') {
        if (isDockerMode && envMap.DB_HOST === 'db') {
            return { ok: true, info: `Docker MySQL (db:${envMap.DB_PORT || 3306}/${envMap.DB_DATABASE || 'inventory'})` };
        }
        const host = isDockerMode ? envMap.DB_HOST || '127.0.0.1' : normalizeLocalDbHost(envMap.DB_HOST || '127.0.0.1');
        const port = envMap.DB_PORT || '3306';
        const db = envMap.DB_DATABASE || 'inventory';
        const user = envMap.DB_USERNAME || 'root';
        const pass = envMap.DB_PASSWORD || '';

        const probeCode = `
$host = $argv[1]; $port = $argv[2]; $db = $argv[3]; $user = $argv[4]; $pass = $argv[5];
if (!extension_loaded('pdo_mysql')) { echo "ERR:Ekstensi PHP pdo_mysql belum aktif."; exit(1); }
$opts = [PDO::ATTR_TIMEOUT => 4, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) { $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false; }
try {
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, $opts);
    $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
    $stmt->execute([$db]);
    if (!$stmt->fetchColumn()) {
        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            $safe = str_replace('\`', '\`\`', $db);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS \`{$safe}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "CREATED";
            exit(0);
        }
        echo "ERR:Database '{$db}' tidak ditemukan di {$host}:{$port}.";
        exit(2);
    }
    $pdoDb = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, $opts);
    echo "OK:" . $pdoDb->query('SELECT VERSION()')->fetchColumn();
} catch (Throwable $e) {
    echo "ERR:" . $e->getMessage();
    exit(1);
}
`;
        const res = runSync(phpBin, ['-r', probeCode, '--', host, port, db, user, pass], { capture: true });
        const out = String(res.stdout || res.stderr || '').trim();
        if (res.status === 0 && (out.startsWith('OK:') || out === 'CREATED')) {
            const ver = out === 'CREATED' ? 'database dibuat otomatis' : `MySQL ${out.slice(3)}`;
            return { ok: true, info: `${host}:${port}/${db} (${ver})`, effectiveHost: host };
        }
        return { ok: false, error: out.replace(/^ERR:/, '') || 'Gagal terhubung ke MySQL.' };
    }

    return { ok: true, info: conn };
}

async function ensureDatabaseReady(env, opts, isDockerMode = false) {
    ensureEnvFile();

    if (opts.dbTarget === 'sqlite') {
        await switchToSqlite();
    } else if (opts.dbTarget === 'local' || opts.dbTarget === 'live') {
        if (env.phpBin) {
            runSyncOrFail(env.phpBin, ['artisan', 'db:use', opts.dbTarget], `db:use ${opts.dbTarget}`);
        }
    } else if (opts.dbTarget === 'docker') {
        updateEnvKeys({
            DB_CONNECTION: 'mysql',
            DB_HOST: 'db',
            DB_PORT: '3306',
            DB_DATABASE: 'inventory',
            DB_USERNAME: 'inventory',
            DB_PASSWORD: 'secret',
        });
    }

    while (true) {
        const envMap = parseEnvContent(fs.readFileSync(ENV_PATH, 'utf8'));
        if (!env.phpBin) {
            return envMap;
        }
        const check = testPhpDatabase(env.phpBin, envMap, isDockerMode);
        if (check.ok) {
            logOk(`Database siap: ${check.info}`);
            return envMap;
        }

        logErr(`Koneksi database gagal: ${check.error}`);
        if (opts.checkOnly || opts.yes || !process.stdin.isTTY) {
            process.exit(1);
        }

        console.log('');
        console.log(`${C.yellow}   Pilih aksi perbaikan Database:${C.reset}`);
        console.log('     [1] Gunakan SQLite zero-config (paling mudah, tanpa server MySQL)');
        console.log('     [2] Isi kredensial MySQL (.env) secara interaktif');
        if (fs.existsSync(path.join(ROOT_DIR, '.env.db.local.example')) || fs.existsSync(path.join(ROOT_DIR, '.env.db.local'))) {
            console.log('     [3] Pakai profil MySQL Lokal (.env.db.local -> 127.0.0.1:3306)');
        }
        if (fs.existsSync(path.join(ROOT_DIR, '.env.db.live.example')) || fs.existsSync(path.join(ROOT_DIR, '.env.db.live'))) {
            console.log('     [4] Pakai profil MySQL Live Cloud (.env.db.live)');
        }
        if (isDockerMode) {
            console.log('     [5] Pakai container MySQL Docker (db:3306)');
        }
        console.log('     [R] Tes ulang koneksi');
        console.log('     [Q] Keluar');

        const choice = (await ask('\n   Pilihan Anda [default: 1]: ')).toUpperCase() || '1';
        if (choice === '1') {
            await switchToSqlite();
        } else if (choice === '3') {
            runSync(env.phpBin, ['artisan', 'db:use', 'local']);
        } else if (choice === '4') {
            runSync(env.phpBin, ['artisan', 'db:use', 'live']);
        } else if (choice === '5' && isDockerMode) {
            updateEnvKeys({
                DB_CONNECTION: 'mysql',
                DB_HOST: 'db',
                DB_PORT: '3306',
                DB_DATABASE: 'inventory',
                DB_USERNAME: 'inventory',
                DB_PASSWORD: 'secret',
            });
        } else if (choice === 'Q') {
            process.exit(1);
        } else if (choice === '2') {
            const curHost = envMap.DB_HOST || '127.0.0.1';
            const curPort = envMap.DB_PORT || '3306';
            const curDb = envMap.DB_DATABASE || 'inventory';
            const curUser = envMap.DB_USERNAME || 'root';
            const nextHost = (await ask(`   DB_HOST [${curHost}]: `)) || curHost;
            const nextPort = (await ask(`   DB_PORT [${curPort}]: `)) || curPort;
            const nextDb = (await ask(`   DB_DATABASE [${curDb}]: `)) || curDb;
            const nextUser = (await ask(`   DB_USERNAME [${curUser}]: `)) || curUser;
            const nextPassRaw = await ask('   DB_PASSWORD [Enter=tetap, "-"=kosongkan]: ');
            const updates = {
                DB_CONNECTION: 'mysql',
                DB_HOST: nextHost,
                DB_PORT: nextPort,
                DB_DATABASE: nextDb,
                DB_USERNAME: nextUser,
            };
            if (nextPassRaw === '-') {
                updates.DB_PASSWORD = '';
            } else if (nextPassRaw !== '') {
                updates.DB_PASSWORD = nextPassRaw;
            }
            updateEnvKeys(updates);
            runSync(env.phpBin, ['artisan', 'config:clear'], { capture: true });
        }
    }
}

async function switchToSqlite() {
    ensureEnvFile();
    const sqliteRel = 'database/database.sqlite';
    const sqliteAbs = path.join(ROOT_DIR, sqliteRel);
    fs.mkdirSync(path.dirname(sqliteAbs), { recursive: true });
    if (!fs.existsSync(sqliteAbs)) {
        fs.writeFileSync(sqliteAbs, '');
    }
    updateEnvKeys({ DB_CONNECTION: 'sqlite', DB_DATABASE: sqliteRel });
    const phpBin = resolveBestPhpBinary(EXTRA_DIRS);
    if (phpBin && fs.existsSync(path.join(ROOT_DIR, 'vendor/autoload.php'))) {
        runSync(phpBin, ['artisan', 'config:clear'], { capture: true });
    }
    logOk(`Beralih ke SQLite zero-config (${sqliteRel}).`);
}

function runComposer(env, args, label) {
    if (/\.(bat|cmd)$/i.test(env.composerBin)) {
        runSyncOrFail(env.composerBin, args, label, { env: { PATH: `${path.dirname(env.phpBin)}${path.delimiter}${process.env.PATH || ''}` } });
    } else {
        runSyncOrFail(env.phpBin, [env.composerBin, ...args], label);
    }
}

function ensureLocalPrerequisites(env) {
    if (!env.phpBin) {
        logErr('PHP CLI tidak ditemukan.');
        console.log('   • Windows : Jalankan .\\onboarding.bat (panduan install Laragon / Herd / winget PHP)');
        console.log('   • macOS   : brew install php composer');
        console.log('   • Linux   : sudo apt install php-cli php-mbstring php-xml php-curl php-gd php-zip php-sqlite3 php-mysql composer');
        process.exit(1);
    }
    if (!env.phpOk) {
        logErr(`Versi PHP terdeteksi ${env.phpVersion || 'unknown'}, aplikasi butuh PHP >= 8.3 (sesuai composer.json).`);
        process.exit(1);
    }
    if (env.phpMissingExts.length > 0) {
        logErr(`Ekstensi PHP wajib belum aktif: ${env.phpMissingExts.join(', ')}`);
        process.exit(1);
    }
    if (!env.jsRunner) {
        logErr('Tidak ditemukan Bun maupun Node.js/npm. Jalankan .\\onboarding.bat (Windows) atau ./setup.sh (macOS/Linux).');
        process.exit(1);
    }

    fs.mkdirSync(path.join(ROOT_DIR, 'storage/framework/sessions'), { recursive: true });
    fs.mkdirSync(path.join(ROOT_DIR, 'storage/framework/views'), { recursive: true });
    fs.mkdirSync(path.join(ROOT_DIR, 'storage/framework/cache'), { recursive: true });
    fs.mkdirSync(path.join(ROOT_DIR, 'storage/logs'), { recursive: true });
    fs.mkdirSync(path.join(ROOT_DIR, 'bootstrap/cache'), { recursive: true });

    if (!fs.existsSync(path.join(ROOT_DIR, 'vendor/autoload.php'))) {
        if (!env.composerBin) {
            logErr('Folder vendor/ belum ada dan Composer tidak ditemukan.');
            process.exit(1);
        }
        logStep('Menginstall dependensi PHP (composer install)...');
        runComposer(env, ['install', '--no-interaction', '--prefer-dist'], 'composer install');
    }

    if (!fs.existsSync(path.join(ROOT_DIR, 'node_modules'))) {
        logStep(`Menginstall dependensi frontend (${env.jsRunner.pm} install)...`);
        runSyncOrFail(env.jsRunner.pmBin, buildJsInstallArgs(env.jsRunner.pm), `${env.jsRunner.pm} install`);
    }

    const envMap = ensureEnvFile();
    if (!envMap.APP_KEY || !envMap.APP_KEY.startsWith('base64:')) {
        logStep('Membuat APP_KEY Laravel...');
        runSyncOrFail(env.phpBin, ['artisan', 'key:generate', '--ansi'], 'artisan key:generate');
    }

    const storageLink = path.join(ROOT_DIR, 'public/storage');
    if (!fs.existsSync(storageLink)) {
        runSync(env.phpBin, ['artisan', 'storage:link', '--ansi'], { capture: true });
    }
}

function spawnPrefixed(label, color, bin, args, extraEnv = {}) {
    const useShell = process.platform === 'win32' && /\.(cmd|bat)$/i.test(bin);
    const child = spawn(bin, args, {
        cwd: ROOT_DIR,
        env: { ...process.env, ...extraEnv },
        stdio: ['ignore', 'pipe', 'pipe'],
        shell: useShell,
    });

    const writeLines = (chunk, isErr) => {
        const text = chunk.toString('utf8');
        for (const line of text.split(/\r?\n/)) {
            if (!line) continue;
            const out = `${color}[${label}]${C.reset} ${line}`;
            if (isErr) {
                console.error(out);
            } else {
                console.log(out);
            }
        }
    };

    child.stdout?.on('data', (d) => writeLines(d, false));
    child.stderr?.on('data', (d) => writeLines(d, true));
    return child;
}

function openBrowserUrl(url) {
    try {
        if (process.platform === 'win32') {
            spawn('cmd.exe', ['/c', 'start', '', url], { detached: true, stdio: 'ignore' }).unref();
        } else if (process.platform === 'darwin') {
            spawn('open', [url], { detached: true, stdio: 'ignore' }).unref();
        } else {
            spawn('xdg-open', [url], { detached: true, stdio: 'ignore' }).unref();
        }
    } catch {
        // Non-fatal if running in headless session
    }
}

function checkHttpReady(url) {
    return new Promise((resolve) => {
        const req = http.get(url, { timeout: 2000 }, (res) => {
            res.resume();
            resolve((res.statusCode || 500) < 500);
        });
        req.on('timeout', () => {
            req.destroy();
            resolve(false);
        });
        req.on('error', () => resolve(false));
    });
}

async function waitAndOpenBrowser(url, timeoutSec = 60) {
    const deadline = Date.now() + timeoutSec * 1000;
    while (Date.now() < deadline) {
        if (await checkHttpReady(url)) {
            openBrowserUrl(url);
            return true;
        }
        await new Promise((r) => setTimeout(r, 1000));
    }
    return false;
}

async function runSetup(opts) {
    logHeader('SIM-Inventaris — Cross-Platform Setup');
    const env = detectEnvironment();

    let mode = opts.isDocker ? 'docker' : opts.isLocal ? 'local' : '';
    if (!mode) {
        if (opts.checkOnly) {
            mode = 'local';
        } else if (env.phpBin && env.phpOk && env.jsRunner) {
            mode = 'local';
        } else if (env.dockerRunning) {
            mode = 'docker';
        } else if (!opts.yes && process.stdin.isTTY) {
            console.log('\n   Pilih mode setup:');
            console.log('     [1] Local Mode (PHP + Bun/Node.js, tanpa Docker)');
            console.log('     [2] Docker Mode (Container PHP 8.3 + Bun + MySQL)');
            const pick = (await ask('   Pilihan [default: 1]: ')) || '1';
            mode = pick === '2' ? 'docker' : 'local';
        } else {
            mode = 'local';
        }
    }

    if (mode === 'docker') {
        if (!env.dockerBin || !env.dockerRunning) {
            logErr('Docker CLI atau Docker Daemon tidak aktif. Pastikan Docker Desktop sudah menyala.');
            process.exit(1);
        }
        ensureEnvFile();
        if (opts.dbTarget) {
            await ensureDatabaseReady(env, opts, true);
        }
        if (opts.checkOnly) {
            logOk('Pemeriksaan Docker setup lulus.');
            return;
        }
        logStep('Membangun image Docker & menyiapkan container...');
        runSyncOrFail(env.dockerBin, ['compose', 'build'], 'docker compose build');
        runSyncOrFail(env.dockerBin, ['compose', 'up', '-d', 'db'], 'docker compose up -d db');
        runSyncOrFail(env.dockerBin, ['compose', 'run', '--rm', 'app', 'composer', 'install', '--no-interaction', '--prefer-dist'], 'composer install (Docker)');
        runSyncOrFail(env.dockerBin, ['compose', 'run', '--rm', 'app', 'bun', 'install'], 'bun install (Docker)');
        runSyncOrFail(env.dockerBin, ['compose', 'run', '--rm', 'app', 'php', 'artisan', 'key:generate', '--ansi'], 'key:generate (Docker)');
        runSyncOrFail(env.dockerBin, ['compose', 'run', '--rm', 'app', 'php', 'artisan', 'inventory:smart-db-sync', '--ansi'], 'smart-db-sync (Docker)');
        runSyncOrFail(env.dockerBin, ['compose', 'run', '--rm', 'app', 'bun', 'run', 'build'], 'vite build (Docker)');
        logOk('Setup Docker selesai!');

        if (opts.autoStart) {
            await runDocker(opts);
        }
        return;
    }

    // Local mode setup
    ensureLocalPrerequisites(env);
    await ensureDatabaseReady(env, opts, false);

    if (!opts.checkOnly) {
        logStep('Menjalankan Smart Database Sync (migrasi & seed)...');
        const envMap = parseEnvContent(fs.readFileSync(ENV_PATH, 'utf8'));
        const localDbEnv = envMap.DB_CONNECTION === 'mysql' ? { DB_HOST: normalizeLocalDbHost(envMap.DB_HOST) } : {};
        runSyncOrFail(env.phpBin, ['artisan', 'optimize:clear'], 'artisan optimize:clear', { env: localDbEnv });
        runSyncOrFail(env.phpBin, ['artisan', 'inventory:smart-db-sync', '--ansi'], 'artisan inventory:smart-db-sync', {
            env: localDbEnv,
        });

        if (!fs.existsSync(path.join(ROOT_DIR, 'public/build/manifest.json'))) {
            logStep(`Membangun aset frontend (${env.jsRunner.pm} run build)...`);
            runSyncOrFail(env.jsRunner.pmBin, ['run', 'build'], 'vite build');
        }
    }

    logStep('Menyiapkan sertifikat LAN HTTPS (opsional)...');
    runSync(env.jsRunner.runtimeBin, ['scripts/lan-https-proxy.mjs', '--generate-only', '--optional']);

    logOk('Setup Local Mode selesai & siap digunakan!');
    if (opts.autoStart && !opts.checkOnly) {
        await runLocal(opts);
    }
}

async function runLocal(opts) {
    logHeader('SIM-Inventaris — Local Dev Runner (Cross-Platform)');
    const env = detectEnvironment();
    ensureLocalPrerequisites(env);
    const envMap = await ensureDatabaseReady(env, opts, false);

    const effectiveDbHost = normalizeLocalDbHost(envMap.DB_HOST || '127.0.0.1');
    const localChildEnv = envMap.DB_CONNECTION === 'mysql' ? { DB_HOST: effectiveDbHost } : {};

    logStep('Sinkronisasi database (inventory:smart-db-sync)...');
    runSyncOrFail(env.phpBin, ['artisan', 'inventory:smart-db-sync', '--ansi'], 'inventory:smart-db-sync', {
        env: localChildEnv,
    });

    if (!fs.existsSync(path.join(ROOT_DIR, 'public/build/manifest.json'))) {
        logStep(`Membangun aset produksi awal (${env.jsRunner.pm} run build)...`);
        runSyncOrFail(env.jsRunner.pmBin, ['run', 'build'], 'vite build');
    }

    fs.rmSync(path.join(ROOT_DIR, 'public/hot'), { force: true });

    const portsToCheck = [
        { port: opts.port, name: 'Laravel Web' },
        { port: opts.vitePort, name: 'Vite HMR' },
        { port: opts.webhookPort, name: 'Webhook Server' },
    ];
    for (const p of portsToCheck) {
        if (await isPortInUse(p.port)) {
            logErr(`Port ${p.port} (${p.name}) sudah dipakai proses lain. Hentikan proses tersebut atau ubah PORT.`);
            process.exit(1);
        }
    }

    const lanIps = detectLanIps();
    const lanIp = lanIps[0] || '127.0.0.1';
    const localUrl = `http://127.0.0.1:${opts.port}`;

    console.log('');
    console.log(`${C.green}${C.bold}======================================================================${C.reset}`);
    console.log(`${C.green}${C.bold}  Menjalankan Laravel + Vite HMR (${env.jsRunner.runtime}) + Webhook + LAN HTTPS${C.reset}`);
    console.log(`  [WEB]   Desktop Browser : ${localUrl}`);
    console.log(`  [LAN]   LAN HTTP        : http://${lanIp}:${opts.port}`);
    console.log(`  [PWA]   LAN HTTPS (PWA) : https://${lanIp}:${opts.httpsPort}`);
    console.log(`  [VITE]  Vite HMR        : http://127.0.0.1:${opts.vitePort}`);
    console.log(`  [HOOK]  Webhook Broker  : http://127.0.0.1:${opts.webhookPort}/webhook`);
    console.log(`  [STOP]  Tekan Ctrl+C untuk menghentikan seluruh server dengan bersih.`);
    console.log(`${C.green}${C.bold}======================================================================${C.reset}\n`);

    const children = [];
    let shuttingDown = false;

    const cleanup = () => {
        if (shuttingDown) return;
        shuttingDown = true;
        console.log(`\n${C.yellow}🛑 Menghentikan seluruh server lokal...${C.reset}`);
        fs.rmSync(path.join(ROOT_DIR, 'public/hot'), { force: true });
        for (const ch of children) {
            killProcessTree(ch);
        }
        logOk('Semua server lokal telah dihentikan.');
        process.exit(0);
    };

    process.on('SIGINT', cleanup);
    process.on('SIGTERM', cleanup);

    const pLaravel = spawnPrefixed('WEB', C.green, env.phpBin, ['artisan', 'serve', '--host=0.0.0.0', `--port=${opts.port}`], localChildEnv);
    const pVite = spawnPrefixed('VITE', C.cyan, env.jsRunner.pmBin, ['run', 'dev']);
    const pWebhook = spawnPrefixed(
        'HOOK',
        C.magenta,
        env.phpBin,
        ['artisan', 'inventory:webhook-server', '--host=0.0.0.0', `--port=${opts.webhookPort}`],
        localChildEnv
    );
    const pLanHttps = spawnPrefixed(
        'HTTPS',
        C.yellow,
        env.jsRunner.runtimeBin,
        ['scripts/lan-https-proxy.mjs', '--optional'],
        { PORT: String(opts.port), HTTPS_PORT: String(opts.httpsPort) }
    );

    children.push(pLaravel, pVite, pWebhook, pLanHttps);

    for (const primary of [pLaravel, pVite]) {
        primary.on('exit', (code) => {
            if (!shuttingDown) {
                logWarn(`Proses utama berhenti (exit code ${code ?? 0}). Menutup layanan lain...`);
                cleanup();
            }
        });
    }

    if (!opts.noBrowser) {
        waitAndOpenBrowser(localUrl, 60).then((opened) => {
            if (opened) {
                logOk(`Browser dibuka ke ${localUrl}`);
            }
        });
    }

    // Keep event loop alive until signal or child exit
    await new Promise(() => {});
}

async function runDocker(opts) {
    logHeader('SIM-Inventaris — Docker Dev Runner');
    const env = detectEnvironment();
    if (!env.dockerBin || !env.dockerRunning) {
        logErr('Docker CLI atau Docker Daemon tidak aktif. Nyalakan Docker Desktop terlebih dahulu.');
        process.exit(1);
    }
    ensureEnvFile();

    logStep('Menyalakan service MySQL container (db)...');
    runSyncOrFail(env.dockerBin, ['compose', 'up', '-d', 'db'], 'docker compose up -d db');

    const localUrl = `http://localhost:${opts.port}`;
    logStep('Menjalankan Laravel + Vite HMR + LAN HTTPS + Webhook di container app...');
    const dockerProc = spawn(
        env.dockerBin,
        ['compose', 'run', '--rm', '--service-ports', 'app', 'bash', './docker/run-dev.sh'],
        { cwd: ROOT_DIR, stdio: 'inherit' }
    );

    if (!opts.noBrowser) {
        waitAndOpenBrowser(localUrl, 90);
    }

    const exitCode = await new Promise((resolve) => {
        dockerProc.on('close', (code) => resolve(code ?? 0));
        dockerProc.on('error', () => resolve(1));
    });
    process.exit(exitCode);
}

async function ensureCloudflaredBinary(env) {
    if (env.cloudflaredBin) {
        return { mode: 'binary', bin: env.cloudflaredBin };
    }
    if (env.dockerRunning && env.dockerBin) {
        return { mode: 'docker', bin: env.dockerBin };
    }

    const info = resolveCloudflaredDownloadInfo(process.platform, process.arch);
    if (!info) {
        logErr('cloudflared tidak tersedia dan arsitektur tidak didukung untuk auto-download.');
        process.exit(1);
    }

    const binDir = path.join(ROOT_DIR, '.bin');
    fs.mkdirSync(binDir, { recursive: true });
    const targetBin = path.join(binDir, info.filename);

    logStep(`Mengunduh portable cloudflared (${info.url})...`);
    const resp = await fetch(info.url);
    if (!resp.ok) {
        logErr(`Gagal mengunduh cloudflared: HTTP ${resp.status}`);
        process.exit(1);
    }
    const buf = Buffer.from(await resp.arrayBuffer());

    if (info.url.endsWith('.tgz')) {
        const tgzPath = path.join(binDir, 'cloudflared.tgz');
        fs.writeFileSync(tgzPath, buf);
        execFileSync('tar', ['-xzf', tgzPath, '-C', binDir], { stdio: 'ignore' });
        fs.rmSync(tgzPath, { force: true });
    } else {
        fs.writeFileSync(targetBin, buf);
    }

    if (process.platform !== 'win32' && fs.existsSync(targetBin)) {
        fs.chmodSync(targetBin, 0o755);
    }
    logOk(`Binary cloudflared siap di ${targetBin}`);
    return { mode: 'binary', bin: targetBin };
}

async function runTunnel(opts) {
    logHeader('SIM-Inventaris — Cross-Platform Cloudflare HTTPS Tunnel');
    const env = detectEnvironment();
    const targetUrl = `http://127.0.0.1:${opts.port}`;

    if (!fs.existsSync(path.join(ROOT_DIR, 'public/build/manifest.json'))) {
        if (env.jsRunner && fs.existsSync(path.join(ROOT_DIR, 'node_modules'))) {
            logStep('Membangun aset produksi (public/build) untuk akses HTTPS...');
            runSync(env.jsRunner.pmBin, ['run', 'build']);
        } else if (env.dockerRunning && env.dockerBin) {
            runSync(env.dockerBin, ['compose', 'run', '--rm', 'app', 'bun', 'run', 'build']);
        }
    }

    let bgServer = null;
    let startedDockerApp = false;
    const listening = await checkHttpReady(targetUrl);

    if (!listening) {
        const useDockerBackend = opts.isDocker || (!opts.isLocal && (!env.phpBin || !fs.existsSync(path.join(ROOT_DIR, 'vendor/autoload.php'))) && env.dockerRunning);
        if (useDockerBackend && env.dockerRunning && env.dockerBin) {
            logStep(`Menyalakan container app di background untuk port ${opts.port}...`);
            runSyncOrFail(env.dockerBin, ['compose', 'up', '-d', 'app'], 'docker compose up -d app');
            startedDockerApp = true;
        } else if (env.phpBin && fs.existsSync(path.join(ROOT_DIR, 'vendor/autoload.php'))) {
            logStep(`Menyalakan server Laravel lokal di port ${opts.port}...`);
            bgServer = spawn(env.phpBin, ['artisan', 'serve', '--host=127.0.0.1', `--port=${opts.port}`], {
                cwd: ROOT_DIR,
                stdio: 'ignore',
            });
        } else {
            logErr(`Server belum aktif di ${targetUrl} dan PHP/Docker tidak tersedia.`);
            process.exit(1);
        }

        let ready = false;
        for (let i = 0; i < 25; i++) {
            if (await checkHttpReady(targetUrl)) {
                ready = true;
                break;
            }
            await new Promise((r) => setTimeout(r, 1000));
        }
        if (!ready) {
            logErr(`Server tidak merespons di ${targetUrl}.`);
            killProcessTree(bgServer);
            process.exit(1);
        }
        logOk(`Server siap di ${targetUrl}`);
    } else {
        logOk(`Server terdeteksi aktif di ${targetUrl}`);
    }

    const cfRunner = await ensureCloudflaredBinary(env);
    const cfContainerName = `inventory_cf_tunnel_${opts.port}`;
    const tunnelBin = cfRunner.bin;
    const tunnelArgs =
        cfRunner.mode === 'docker'
            ? [
                  'run',
                  '--rm',
                  '--name',
                  cfContainerName,
                  '--add-host=host.docker.internal:host-gateway',
                  'cloudflare/cloudflared:latest',
                  'tunnel',
                  '--no-autoupdate',
                  '--protocol',
                  'http2',
                  '--url',
                  `http://host.docker.internal:${opts.port}`,
              ]
            : ['tunnel', '--no-autoupdate', '--protocol', 'http2', '--url', targetUrl];

    logStep('Membuka Cloudflare Free HTTPS Quick Tunnel (*.trycloudflare.com)...');
    const tunnelProc = spawn(tunnelBin, tunnelArgs, { cwd: ROOT_DIR, stdio: ['ignore', 'pipe', 'pipe'] });
    let announced = false;
    let cleaned = false;

    const cleanup = () => {
        if (cleaned) return;
        cleaned = true;
        console.log(`\n${C.yellow}🛑 Menutup Cloudflare HTTPS Tunnel...${C.reset}`);
        killProcessTree(tunnelProc);
        if (cfRunner.mode === 'docker' && env.dockerBin) {
            runSync(env.dockerBin, ['rm', '-f', cfContainerName], { capture: true });
        }
        if (bgServer) {
            killProcessTree(bgServer);
        }
        if (startedDockerApp && env.dockerBin) {
            runSync(env.dockerBin, ['compose', 'stop', 'app'], { capture: true });
        }
        logOk('Tunnel dihentikan.');
        process.exit(0);
    };

    process.on('SIGINT', cleanup);
    process.on('SIGTERM', cleanup);

    const handleLine = (chunk) => {
        for (const line of chunk.toString('utf8').split(/\r?\n/)) {
            if (!line) continue;
            const publicUrl = parseTryCloudflareUrl(line);
            if (publicUrl && !announced) {
                announced = true;
                console.log('');
                console.log(`${C.green}${C.bold}======================================================================${C.reset}`);
                console.log(`${C.green}${C.bold}  CLOUDFLARE FREE HTTPS TUNNEL AKTIF${C.reset}`);
                console.log(`  URL Publik HTTPS : ${publicUrl}`);
                console.log(`  Scan Barcode HP  : ${publicUrl}/scan-barcode`);
                console.log(`  Tekan Ctrl+C untuk menutup tunnel & melepas domain.`);
                console.log(`${C.green}${C.bold}======================================================================${C.reset}\n`);
            } else if (!announced) {
                console.log(`${C.gray}   ${line}${C.reset}`);
            }
        }
    };

    tunnelProc.stdout?.on('data', handleLine);
    tunnelProc.stderr?.on('data', handleLine);
    tunnelProc.on('exit', () => cleanup());

    await new Promise(() => {});
}

function collectPhpFiles(dirs) {
    const files = [];
    const stack = dirs.map((d) => path.join(ROOT_DIR, d)).filter((d) => fs.existsSync(d));
    while (stack.length > 0) {
        const cur = stack.pop();
        for (const entry of fs.readdirSync(cur, { withFileTypes: true })) {
            const full = path.join(cur, entry.name);
            if (entry.isDirectory()) {
                stack.push(full);
            } else if (entry.isFile() && entry.name.endsWith('.php')) {
                files.push(full);
            }
        }
    }
    return files;
}

function runLint(opts) {
    logHeader('SIM-Inventaris — Cross-Platform Lint & Build Check');
    const env = detectEnvironment();
    const useDocker = opts.isDocker || (!env.phpBin && env.dockerRunning);

    if (useDocker) {
        if (!env.dockerRunning || !env.dockerBin) {
            logErr('Docker daemon tidak aktif.');
            process.exit(1);
        }
        runSyncOrFail(
            env.dockerBin,
            [
                'compose',
                'run',
                '--rm',
                'app',
                'sh',
                '-c',
                "find app bootstrap config database routes tests -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null && ./vendor/bin/pint --test && bun run typecheck && bun run build",
            ],
            'lint (Docker)'
        );
        logOk('Semua pemeriksaan lint & build lulus!');
        return;
    }

    ensureLocalPrerequisites(env);
    logStep('1/4 Memeriksa sintaks PHP (`php -l`)...');
    const phpFiles = collectPhpFiles(['app', 'bootstrap', 'config', 'database', 'routes', 'tests']);
    for (const file of phpFiles) {
        const res = runSync(env.phpBin, ['-l', file], { capture: true });
        if (res.status !== 0) {
            console.error(res.stdout || res.stderr);
            process.exit(res.status || 1);
        }
    }
    logOk(`Sintaks ${phpFiles.length} file PHP valid.`);

    logStep('2/4 Memeriksa format Laravel Pint...');
    const pintScript = path.join(ROOT_DIR, 'vendor', 'bin', 'pint');
    runSyncOrFail(env.phpBin, [pintScript, '--test'], 'Laravel Pint');

    logStep(`3/4 TypeScript typecheck (${env.jsRunner.pm} run typecheck)...`);
    runSyncOrFail(env.jsRunner.pmBin, ['run', 'typecheck'], 'typecheck');

    logStep(`4/4 Frontend production build (${env.jsRunner.pm} run build)...`);
    runSyncOrFail(env.jsRunner.pmBin, ['run', 'build'], 'vite build');

    logOk('Semua lint & production build lulus!');
}

function runTest(opts) {
    const env = detectEnvironment();
    const useDocker = opts.isDocker || (!env.phpBin && env.dockerRunning);
    if (useDocker) {
        if (!env.dockerRunning || !env.dockerBin) {
            logErr('Docker daemon tidak aktif.');
            process.exit(1);
        }
        runSyncOrFail(
            env.dockerBin,
            ['compose', 'run', '--rm', '-e', 'APP_ENV=testing', 'app', 'php', 'artisan', 'test', ...opts.extraArgs],
            'artisan test (Docker)'
        );
        return;
    }

    ensureLocalPrerequisites(env);
    runSyncOrFail(env.phpBin, ['artisan', 'test', ...opts.extraArgs], 'artisan test');
}

function runClean() {
    const env = detectEnvironment();
    fs.rmSync(path.join(ROOT_DIR, 'public/hot'), { force: true });
    if (env.phpBin && fs.existsSync(path.join(ROOT_DIR, 'vendor/autoload.php'))) {
        runSyncOrFail(env.phpBin, ['artisan', 'optimize:clear'], 'artisan optimize:clear');
    } else if (env.dockerRunning && env.dockerBin) {
        runSyncOrFail(env.dockerBin, ['compose', 'run', '--rm', 'app', 'php', 'artisan', 'optimize:clear'], 'artisan optimize:clear (Docker)');
    }
    logOk('Cache Laravel & public/hot dibersihkan.');
}

function runDbSubcommand(sub) {
    const env = detectEnvironment();
    if (sub === 'db:sqlite') {
        return switchToSqlite();
    }
    if (!env.phpBin || !fs.existsSync(path.join(ROOT_DIR, 'vendor/autoload.php'))) {
        if (env.dockerRunning && env.dockerBin) {
            const artisanArgs = sub === 'db:status' ? ['db:show'] : sub === 'db:local' ? ['db:use', 'local'] : ['db:use', 'live'];
            runSyncOrFail(env.dockerBin, ['compose', 'run', '--rm', 'app', 'php', 'artisan', ...artisanArgs], sub);
            return;
        }
        logErr('PHP + vendor/ belum siap. Jalankan setup terlebih dahulu.');
        process.exit(1);
    }
    if (sub === 'db:status') {
        runSyncOrFail(env.phpBin, ['artisan', 'db:show'], 'artisan db:show');
    } else if (sub === 'db:local') {
        runSyncOrFail(env.phpBin, ['artisan', 'db:use', 'local'], 'artisan db:use local');
    } else if (sub === 'db:live') {
        runSyncOrFail(env.phpBin, ['artisan', 'db:use', 'live'], 'artisan db:use live');
    }
}

function runDoctor() {
    logHeader('SIM-Inventaris — Environment Diagnostics');
    const env = detectEnvironment();
    const envMap = fs.existsSync(ENV_PATH) ? parseEnvContent(fs.readFileSync(ENV_PATH, 'utf8')) : null;

    console.log(`  OS / Arch       : ${env.platform} (${env.arch})`);
    console.log(`  JS Runner       : ${env.jsRunner ? `${env.jsRunner.runtime} (${env.jsRunner.runtimeBin})` : 'TIDAK ADA (butuh Bun atau Node.js)'}`);
    console.log(`  PHP CLI         : ${env.phpBin ? `v${env.phpVersion} (${env.phpBin})` : 'TIDAK ADA'}`);
    if (env.phpBin) {
        console.log(`  PHP Extensions  : ${env.phpMissingExts.length === 0 ? 'Semua wajib aktif' : `Kurang: ${env.phpMissingExts.join(', ')}`}`);
    }
    console.log(`  Composer        : ${env.composerBin || 'TIDAK ADA'}`);
    console.log(`  Docker          : ${env.dockerBin ? `${env.dockerRunning ? 'Aktif' : 'Terinstal tapi Daemon mati'} (${env.dockerBin})` : 'TIDAK ADA'}`);
    console.log(`  OpenSSL CLI     : ${env.opensslBin || 'TIDAK ADA (akan pakai fallback PHP ext-openssl)'}`);
    console.log(`  Cloudflared     : ${env.cloudflaredBin || 'Belum ada (auto-download saat tunnel)'}`);
    const dbSummary = !envMap
        ? '.env belum dibuat'
        : (envMap.DB_CONNECTION || 'sqlite').toLowerCase() === 'sqlite'
          ? `sqlite -> ${envMap.DB_DATABASE || 'database/database.sqlite'}`
          : `${envMap.DB_CONNECTION} -> ${envMap.DB_HOST || '127.0.0.1'}:${envMap.DB_PORT || '3306'}/${envMap.DB_DATABASE || 'inventory'}`;
    console.log(`  .env Database   : ${dbSummary}`);
    console.log('');
}

function printHelp() {
    logHeader('SIM-Inventaris — Cross-Platform Dev CLI');
    console.log('Pemakaian: bun scripts/dev-cli.mjs <perintah> [opsi]');
    console.log('      atau node scripts/dev-cli.mjs <perintah> [opsi]');
    console.log('      atau bun run <script> / npm run <script>\n');
    console.log('Perintah:');
    console.log('  setup / install   Siapkan .env, vendor, node_modules, DB & aset awal (--local|--docker|--db=sqlite|local|live)');
    console.log('  run               Jalankan Local Mode (Laravel :8000 + Vite :5173 + Webhook :8085 + LAN HTTPS :8443)');
    console.log('  dev               Jalankan Docker Mode (container db + app)');
    console.log('  tunnel            Buka Cloudflare Free HTTPS Quick Tunnel (--local|--docker)');
    console.log('  lint              Cek PHP syntax, Pint, TypeScript typecheck & Vite build');
    console.log('  test              Jalankan PHPUnit / Pest test suite');
    console.log('  clean             Bersihkan cache Laravel & public/hot');
    console.log('  db:status         Lihat status koneksi database saat ini');
    console.log('  db:sqlite         Beralih ke database SQLite zero-config');
    console.log('  db:local          Beralih ke profil MySQL Lokal (.env.db.local)');
    console.log('  db:live           Beralih ke profil MySQL Live Cloud (.env.db.live)');
    console.log('  doctor            Diagnostik lengkap tool & runtime di PC ini\n');
}

async function main() {
    const opts = parseCliArgs(process.argv);
    switch (opts.command) {
        case 'help':
            printHelp();
            break;
        case 'doctor':
        case 'check':
            runDoctor();
            break;
        case 'setup':
        case 'install':
            await runSetup(opts);
            break;
        case 'run':
            await runLocal(opts);
            break;
        case 'dev':
            await runDocker(opts);
            break;
        case 'tunnel':
            await runTunnel(opts);
            break;
        case 'lint':
            runLint(opts);
            break;
        case 'test':
            runTest(opts);
            break;
        case 'clean':
            runClean();
            break;
        case 'db:status':
        case 'db:local':
        case 'db:live':
        case 'db:sqlite':
            await runDbSubcommand(opts.command);
            break;
        default:
            logErr(`Perintah tidak dikenal: '${opts.command}'`);
            printHelp();
            process.exit(1);
    }
}

main().catch((err) => {
    logErr(err?.message || String(err));
    process.exit(1);
});
