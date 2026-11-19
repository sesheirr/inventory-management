#!/usr/bin/env node
/**
 * SIM-Inventaris Diskominfo Kab. Garut — Smart Local LAN HTTPS Proxy & Root CA Server
 *
 * Enables Mobile Chrome / iOS Safari over LAN (e.g. 192.168.x.x) to access the local
 * or Docker server over a Secure Context (HTTPS) so ServiceWorker, PWA 1-Click Install
 * (beforeinstallprompt), Camera Barcode Scanner, and Geolocation work seamlessly on LAN!
 */

import https from 'node:https';
import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import { execFileSync, spawnSync } from 'node:child_process';
import { detectLanIps, findExecutable, getWindowsExtraPaths, resolveBestPhpBinary } from './dev-cli-helpers.mjs';

const ROOT_DIR = process.cwd();
const CERT_DIR = path.join(ROOT_DIR, 'storage', 'certs');
const TARGET_HOST = process.env.LAN_TARGET_HOST || '127.0.0.1';
const TARGET_PORT = Number(process.env.PORT || process.env.LAN_TARGET_PORT || 8000);
const HTTPS_PORT = Number(process.env.HTTPS_PORT || 8443);

function findOpenSslBinary() {
    const extraDirs = process.platform === 'win32' ? getWindowsExtraPaths() : [];
    const found = findExecutable('openssl', extraDirs);
    if (found) {
        try {
            execFileSync(found, ['version'], { stdio: 'ignore' });
            return found;
        } catch {
            // Try fallback list below
        }
    }
    const candidates = [
        'C:\\Program Files\\Git\\usr\\bin\\openssl.exe',
        'C:\\Program Files\\OpenSSL-Win64\\bin\\openssl.exe',
        'C:\\laragon\\bin\\git\\usr\\bin\\openssl.exe',
        'C:\\laragon\\bin\\apache\\httpd-2.4.62-240904-win64-VS17\\bin\\openssl.exe',
    ];
    for (const bin of candidates) {
        if (fs.existsSync(bin)) {
            try {
                execFileSync(bin, ['version'], { stdio: 'ignore' });
                return bin;
            } catch {
                // Try next
            }
        }
    }
    return null;
}

/**
 * Fallback certificate generator using PHP's built-in ext-openssl when `openssl` CLI is absent
 * (e.g. fresh Windows without Git Bash or Laragon).
 */
function generateCertificatesWithPhp({ caKeyPath, caCertPath, serverKeyPath, serverCertPath, extFilePath, cnfContent }) {
    const extraDirs = process.platform === 'win32' ? getWindowsExtraPaths() : [];
    const phpBin = resolveBestPhpBinary(extraDirs);
    if (!phpBin) {
        return false;
    }

    fs.writeFileSync(extFilePath, cnfContent, 'utf8');
    const phpScript = `
$caKeyPath = $argv[1];
$caCertPath = $argv[2];
$serverKeyPath = $argv[3];
$serverCertPath = $argv[4];
$cnfPath = $argv[5];

if (!function_exists('openssl_pkey_new')) {
    fwrite(STDERR, "ext-openssl unavailable\\n");
    exit(2);
}

$baseCfg = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA, 'digest_alg' => 'sha256'];
if (file_exists($cnfPath)) {
    $baseCfg['config'] = $cnfPath;
}

if (!file_exists($caKeyPath) || !file_exists($caCertPath)) {
    $caKey = openssl_pkey_new($baseCfg);
    if (!$caKey) { exit(3); }
    $caDn = [
        'countryName' => 'ID',
        'stateOrProvinceName' => 'JawaBarat',
        'localityName' => 'Garut',
        'organizationName' => 'Diskominfo Garut Local CA',
        'commonName' => 'Inventaris Diskominfo LAN Root CA',
    ];
    $caCfg = array_merge($baseCfg, ['x509_extensions' => 'v3_ca']);
    $caCsr = openssl_csr_new($caDn, $caKey, $caCfg);
    $caCert = openssl_csr_sign($caCsr, null, $caKey, 3650, $caCfg, time());
    if (!$caCsr || !$caCert) { exit(4); }
    openssl_pkey_export_to_file($caKey, $caKeyPath, null, $caCfg);
    openssl_x509_export_to_file($caCert, $caCertPath);
}

$caCertPem = file_get_contents($caCertPath);
$caKeyPem = file_get_contents($caKeyPath);
$srvCfg = array_merge($baseCfg, ['x509_extensions' => 'v3_ext']);
$srvKey = openssl_pkey_new($srvCfg);
if (!$srvKey) { exit(5); }
$srvDn = [
    'countryName' => 'ID',
    'stateOrProvinceName' => 'JawaBarat',
    'localityName' => 'Garut',
    'organizationName' => 'Diskominfo Kabupaten Garut',
    'commonName' => 'inventaris.local',
];
$srvCsr = openssl_csr_new($srvDn, $srvKey, $srvCfg);
$srvCert = openssl_csr_sign($srvCsr, $caCertPem, $caKeyPem, 825, $srvCfg, time() + 1);
if (!$srvCsr || !$srvCert) { exit(6); }
openssl_pkey_export_to_file($srvKey, $serverKeyPath, null, $srvCfg);
openssl_x509_export_to_file($srvCert, $serverCertPath);
echo "PHP_OPENSSL_OK";
`;
    const caSection = [
        '',
        '[v3_ca]',
        'subjectKeyIdentifier = hash',
        'authorityKeyIdentifier = keyid:always,issuer',
        'basicConstraints = critical, CA:true',
        'keyUsage = critical, digitalSignature, cRLSign, keyCertSign',
        '',
    ].join('\n');
    fs.writeFileSync(extFilePath, `${cnfContent}\n${caSection}`, 'utf8');

    const res = spawnSync(
        phpBin,
        ['-r', phpScript, '--', caKeyPath, caCertPath, serverKeyPath, serverCertPath, extFilePath],
        { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] }
    );
    return res.status === 0 && (res.stdout || '').includes('PHP_OPENSSL_OK');
}

function ensureLanCertificates(lanIps) {
    fs.mkdirSync(CERT_DIR, { recursive: true });

    const caKeyPath = path.join(CERT_DIR, 'lan-ca.key');
    const caCertPath = path.join(CERT_DIR, 'lan-ca.crt');
    const serverKeyPath = path.join(CERT_DIR, 'lan-server.key');
    const serverCsrPath = path.join(CERT_DIR, 'lan-server.csr');
    const serverCertPath = path.join(CERT_DIR, 'lan-server.crt');
    const extFilePath = path.join(CERT_DIR, 'lan-san.cnf');
    const metaFilePath = path.join(CERT_DIR, 'lan-meta.json');

    const ipEntries = Array.from(new Set(['127.0.0.1', ...lanIps]));
    const currentSignature = ipEntries.join(',');

    let needsServerRegen = !fs.existsSync(serverKeyPath) || !fs.existsSync(serverCertPath);
    if (!needsServerRegen && fs.existsSync(metaFilePath)) {
        try {
            const meta = JSON.parse(fs.readFileSync(metaFilePath, 'utf8'));
            if (meta.ips !== currentSignature) {
                needsServerRegen = true;
            }
        } catch {
            needsServerRegen = true;
        }
    }

    const needsCa = !fs.existsSync(caKeyPath) || !fs.existsSync(caCertPath);
    if (!needsCa && !needsServerRegen) {
        return {
            caCertPath,
            key: fs.readFileSync(serverKeyPath),
            cert: fs.readFileSync(serverCertPath),
            ca: fs.readFileSync(caCertPath),
        };
    }

    const sanList = [
        'DNS.1 = localhost',
        'DNS.2 = *.local',
        'DNS.3 = inventaris.local',
        ...ipEntries.map((ip, idx) => `IP.${idx + 1} = ${ip}`),
    ].join('\n');

    const cnfContent = [
        '[req]',
        'default_bits = 2048',
        'prompt = no',
        'default_md = sha256',
        'distinguished_name = dn',
        '',
        '[dn]',
        'C = ID',
        'ST = JawaBarat',
        'L = Garut',
        'O = Diskominfo Kabupaten Garut',
        `CN = ${ipEntries[1] || ipEntries[0]}`,
        '',
        '[v3_ext]',
        'authorityKeyIdentifier=keyid,issuer',
        'basicConstraints=CA:FALSE',
        'keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment',
        'extendedKeyUsage = serverAuth',
        'subjectAltName = @alt_names',
        '',
        '[alt_names]',
        sanList,
        '',
    ].join('\n');

    const openssl = findOpenSslBinary();
    let updatedCerts = false;

    if (!openssl) {
        const phpOk = generateCertificatesWithPhp({
            caKeyPath,
            caCertPath,
            serverKeyPath,
            serverCertPath,
            extFilePath,
            cnfContent,
        });
        if (!phpOk) {
            throw new Error(
                'OpenSSL CLI maupun ekstensi PHP openssl tidak ditemukan untuk membuat sertifikat HTTPS LAN.'
            );
        }
        updatedCerts = true;
    } else {
        // 1. Generate Root CA if not exists (valid 10 years so user only installs once)
        if (needsCa) {
            execFileSync(openssl, ['genrsa', '-out', caKeyPath, '2048'], { stdio: 'ignore' });
            execFileSync(
                openssl,
                [
                    'req',
                    '-x509',
                    '-new',
                    '-nodes',
                    '-key',
                    caKeyPath,
                    '-sha256',
                    '-days',
                    '3650',
                    '-out',
                    caCertPath,
                    '-subj',
                    '/C=ID/ST=JawaBarat/L=Garut/O=Diskominfo Garut Local CA/CN=Inventaris Diskominfo LAN Root CA',
                    '-addext',
                    'basicConstraints=critical,CA:TRUE',
                    '-addext',
                    'keyUsage=critical,keyCertSign,cRLSign,digitalSignature',
                ],
                { stdio: 'ignore' }
            );
        }

        // 2. Generate Server Certificate signed by Root CA with SAN for all LAN IPs + localhost
        if (needsServerRegen) {
            fs.writeFileSync(extFilePath, cnfContent, 'utf8');

            execFileSync(openssl, ['genrsa', '-out', serverKeyPath, '2048'], { stdio: 'ignore' });
            execFileSync(
                openssl,
                ['req', '-new', '-key', serverKeyPath, '-out', serverCsrPath, '-config', extFilePath],
                { stdio: 'ignore' }
            );
            execFileSync(
                openssl,
                [
                    'x509',
                    '-req',
                    '-in',
                    serverCsrPath,
                    '-CA',
                    caCertPath,
                    '-CAkey',
                    caKeyPath,
                    '-CAcreateserial',
                    '-out',
                    serverCertPath,
                    '-days',
                    '825',
                    '-sha256',
                    '-extfile',
                    extFilePath,
                    '-extensions',
                    'v3_ext',
                ],
                { stdio: 'ignore' }
            );
            updatedCerts = true;
        }
        updatedCerts = true;
    }

    if (updatedCerts) {
        fs.writeFileSync(
            metaFilePath,
            JSON.stringify({
                ips: currentSignature,
                primary_ip: lanIps[0] || '127.0.0.1',
                https_port: HTTPS_PORT,
                http_port: TARGET_PORT,
                updated_at: new Date().toISOString(),
            }, null, 2),
            'utf8'
        );

        // Also copy CA cert to public/lan-ca.crt so HTTP port :8000 can serve it directly!
        try {
            fs.copyFileSync(caCertPath, path.join(ROOT_DIR, 'public', 'lan-ca.crt'));
        } catch {
            // Ignore if read-only
        }
    }

    return {
        caCertPath,
        key: fs.readFileSync(serverKeyPath),
        cert: fs.readFileSync(serverCertPath),
        ca: fs.readFileSync(caCertPath),
    };
}

const lanIps = detectLanIps();
const primaryLanIp = lanIps[0] || '127.0.0.1';
const isOptional = process.argv.includes('--optional');

let certs;
try {
    certs = ensureLanCertificates(lanIps);
} catch (err) {
    if (isOptional) {
        console.warn(`⚠️  LAN HTTPS Proxy dilewati: ${err.message}`);
        console.warn(`   Server HTTP utama tetap berjalan normal di http://127.0.0.1:${TARGET_PORT}`);
        process.exit(0);
    }
    console.error(`❌ ${err.message}`);
    process.exit(1);
}

if (process.argv.includes('--generate-only')) {
    console.log(`LAN_CERT_READY:${primaryLanIp}:${HTTPS_PORT}:${certs.caCertPath}`);
    process.exit(0);
}

const server = https.createServer(
    {
        key: certs.key,
        cert: certs.cert,
        ca: certs.ca,
    },
    (clientReq, clientRes) => {
        const reqUrl = clientReq.url || '/';

        // Direct Root CA download endpoint for 1-tap mobile installation
        if (reqUrl === '/lan-ca.crt' || reqUrl === '/lan-root-ca.crt') {
            clientRes.writeHead(200, {
                'Content-Type': 'application/x-x509-ca-cert',
                'Content-Disposition': 'attachment; filename="Inventaris-Diskominfo-LAN-CA.crt"',
                'Cache-Control': 'no-store',
            });
            clientRes.end(certs.ca);
            return;
        }

        const forwardedHost = clientReq.headers.host || `${primaryLanIp}:${HTTPS_PORT}`;
        const proxyHeaders = {
            ...clientReq.headers,
            host: forwardedHost,
            'x-forwarded-proto': 'https',
            'x-forwarded-host': forwardedHost,
            'x-forwarded-port': String(HTTPS_PORT),
            'x-forwarded-for': clientReq.socket.remoteAddress || '127.0.0.1',
        };

        const proxyReq = http.request(
            {
                hostname: TARGET_HOST,
                port: TARGET_PORT,
                path: reqUrl,
                method: clientReq.method,
                headers: proxyHeaders,
            },
            (proxyRes) => {
                const resHeaders = { ...proxyRes.headers };
                // Rewrite any hardcoded http://<LAN_IP>:8000 redirect Location header to https://<LAN_IP>:8443
                if (typeof resHeaders.location === 'string') {
                    resHeaders.location = resHeaders.location
                        .replace(`http://${primaryLanIp}:${TARGET_PORT}`, `https://${primaryLanIp}:${HTTPS_PORT}`)
                        .replace(`http://localhost:${TARGET_PORT}`, `https://${ forwardedHost }`)
                        .replace(`http://127.0.0.1:${TARGET_PORT}`, `https://${ forwardedHost }`);
                }
                clientRes.writeHead(proxyRes.statusCode || 200, resHeaders);
                proxyRes.pipe(clientRes, { end: true });
            }
        );

        proxyReq.on('error', (err) => {
            clientRes.writeHead(502, { 'Content-Type': 'text/html; charset=utf-8' });
            clientRes.end(`
                <!DOCTYPE html>
                <html lang="id">
                <head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Server Belum Aktif</title></head>
                <body style="font-family:sans-serif;background:#0f172a;color:#f8fafc;padding:2rem;text-align:center;">
                    <h2>⚡ Menunggu Server Laravel di Port ${TARGET_PORT}...</h2>
                    <p>Pastikan server utama sedang berjalan (<code>make run</code> atau <code>make dev</code>).</p>
                    <p style="color:#94a3b8;font-size:12px;">Detail: ${err.message}</p>
                </body>
                </html>
            `);
        });

        clientReq.pipe(proxyReq, { end: true });
    }
);

server.on('error', (err) => {
    if (err.code === 'EADDRINUSE') {
        console.log(`ℹ️  LAN HTTPS Proxy sudah berjalan di port ${HTTPS_PORT}.`);
        process.exit(0);
    }
    console.error('❌ LAN HTTPS Proxy Error:', err.message);
    process.exit(1);
});

server.listen(HTTPS_PORT, '0.0.0.0', () => {
    console.log('======================================================================');
    console.log(' 🔒 LAN HTTPS Proxy & Root CA Aktif (Mobile Chrome PWA Ready)');
    console.log(` 📱 URL HTTPS Mobile LAN : https://${primaryLanIp}:${HTTPS_PORT}`);
    console.log(` 📜 Sertifikat Root CA   : http://${primaryLanIp}:${TARGET_PORT}/lan-ca.crt`);
    console.log('======================================================================');
});

const shutdown = () => {
    server.close(() => process.exit(0));
    setTimeout(() => process.exit(0), 500);
};
process.on('SIGINT', shutdown);
process.on('SIGTERM', shutdown);
