<?php

/**
 * Cross-platform JS runner bridge for composer.json scripts (Windows CMD/PS + POSIX).
 * Prioritizes Bun if available; falls back to Node.js / npm.
 */
$action = $argv[1] ?? 'dev';
$hasBun = false;
$hasNode = false;

$probeCmd = PHP_OS_FAMILY === 'Windows' ? 'where %s >NUL 2>&1' : 'command -v %s >/dev/null 2>&1';
exec(sprintf($probeCmd, 'bun'), $outBun, $codeBun);
$hasBun = ($codeBun === 0);

exec(sprintf($probeCmd, 'node'), $outNode, $codeNode);
$hasNode = ($codeNode === 0);

$pm = $hasBun ? 'bun' : 'npm';
$runtime = $hasBun ? 'bun' : ($hasNode ? 'node' : null);

$cmd = match ($action) {
    'install' => $pm.' install',
    'build' => $pm.' run build',
    'dev' => $runtime ? $runtime.' scripts/dev-cli.mjs run' : null,
    default => null,
};

if ($cmd === null) {
    fwrite(STDERR, "Error: Neither Bun nor Node.js was found in PATH.\n");
    exit(1);
}

passthru($cmd, $exitCode);
exit($exitCode);
