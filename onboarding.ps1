#Requires -Version 5.1
<#
.SYNOPSIS
    Smart Easy Onboarding Script for Inventory Management (Local & Docker Mode)
.DESCRIPTION
    Interactive or CLI-driven onboarding script that sets up, validates, and runs
    the Inventory Management system end-to-end until the browser opens with HTTP 200 OK.
    Supports:
      --local       : Run in Local Mode (PHP + MySQL/Laragon + Bun + LAN HTTPS PWA)
      --docker      : Run in Docker Mode (Auto-enables WSL2 + Ubuntu + Docker Desktop + make install + make dev)
      --db-local    : Switch active .env database to .env.db.local before starting
      --db-live     : Switch active .env database to .env.db.live before starting
      --no-browser  : Do not automatically launch the web browser
      --check-only  : Validate environment, dependencies, DB connection, and LAN SSL cert then exit 0
      --help / -h   : Show usage help
#>

$ErrorActionPreference = "Continue"
$ProjectRoot = if ($PSScriptRoot) { $PSScriptRoot } else { (Get-Location).Path }
Set-Location $ProjectRoot

# ---------------------------------------------------------------------------
# Parse CLI Arguments (supports both --flag and -flag styles)
# ---------------------------------------------------------------------------
$Mode = ""
$DbTarget = ""
$NoBrowser = $false
$CheckOnly = $false
$ShowHelp = $false

foreach ($arg in $args) {
    $normalized = $arg.ToString().Trim().ToLower()
    switch -Regex ($normalized) {
        "^--?local$"          { $Mode = "local" }
        "^--?docker$"         { $Mode = "docker" }
        "^--?db[-:]local$"    { $DbTarget = "local" }
        "^--?db[-:]live$"     { $DbTarget = "live" }
        "^--?no-browser$"     { $NoBrowser = $true }
        "^--?check-only$"     { $CheckOnly = $true }
        "^--?help$|^-h$|^\?$" { $ShowHelp = $true }
    }
}

function Write-Header([string]$Title) {
    Write-Host ""
    Write-Host "======================================================================" -ForegroundColor Cyan
    Write-Host "  $Title" -ForegroundColor White
    Write-Host "======================================================================" -ForegroundColor Cyan
}

function Write-Step([string]$Message) {
    Write-Host "`n>> $Message" -ForegroundColor Cyan
}

function Write-Ok([string]$Message) {
    Write-Host "   [OK] $Message" -ForegroundColor Green
}

function Write-Warn([string]$Message) {
    Write-Host "   [WARN] $Message" -ForegroundColor Yellow
}

function Write-Err([string]$Message) {
    Write-Host "   [ERROR] $Message" -ForegroundColor Red
}

if ($ShowHelp) {
    Write-Header "Inventory Management - Smart Easy Onboarding"
    Write-Host "Usage:"
    Write-Host "  .\onboarding.bat                   # Interactive mode selection (Local or Docker)"
    Write-Host "  .\onboarding.bat --local           # Automatic Local mode (PHP + MySQL + Bun + LAN HTTPS)"
    Write-Host "  .\onboarding.bat --docker          # Automatic Docker mode (WSL2 + Ubuntu + Docker + make install/dev)"
    Write-Host "  .\onboarding.bat --local --db-live # Local mode using live cloud DB profile"
    Write-Host "  .\onboarding.bat --check-only      # Validate environment, DB connection & LAN HTTPS certs"
    Write-Host ""
    exit 0
}

# ---------------------------------------------------------------------------
# Auto-Discover Local Toolchains (Laragon, Git OpenSSL, Bun, Composer)
# ---------------------------------------------------------------------------
function Initialize-LocalToolchainPaths {
    $extraPaths = @()

    # Laragon PHP
    if (Test-Path "C:\laragon\bin\php") {
        $phpDirs = Get-ChildItem -Path "C:\laragon\bin\php" -Directory -ErrorAction SilentlyContinue | Sort-Object Name -Descending
        foreach ($d in $phpDirs) {
            if (Test-Path (Join-Path $d.FullName "php.exe")) {
                $extraPaths += $d.FullName
                break
            }
        }
    }

    # Laragon MySQL
    if (Test-Path "C:\laragon\bin\mysql") {
        $mysqlDirs = Get-ChildItem -Path "C:\laragon\bin\mysql" -Directory -ErrorAction SilentlyContinue | Sort-Object Name -Descending
        foreach ($d in $mysqlDirs) {
            $binPath = Join-Path $d.FullName "bin"
            if (Test-Path (Join-Path $binPath "mysql.exe")) {
                $extraPaths += $binPath
                break
            }
        }
    }

    # Laragon Composer & Node/Bun
    if (Test-Path "C:\laragon\bin\composer") { $extraPaths += "C:\laragon\bin\composer" }
    if (Test-Path "$env:USERPROFILE\.bun\bin") { $extraPaths += "$env:USERPROFILE\.bun\bin" }
    if (Test-Path "C:\Program Files\Git\usr\bin") { $extraPaths += "C:\Program Files\Git\usr\bin" }
    if (Test-Path "C:\Program Files\Docker\Docker\resources\bin") { $extraPaths += "C:\Program Files\Docker\Docker\resources\bin" }

    foreach ($p in $extraPaths) {
        if ($env:PATH -notlike "*$p*") {
            $env:PATH = "$p;$env:PATH"
        }
    }
}

Initialize-LocalToolchainPaths

# ---------------------------------------------------------------------------
# Helper: Read & Update .env values
# ---------------------------------------------------------------------------
function Ensure-EnvFile {
    $envPath = Join-Path $ProjectRoot ".env"
    $examplePath = Join-Path $ProjectRoot ".env.example"
    if (-not (Test-Path $envPath)) {
        if (Test-Path $examplePath) {
            Copy-Item $examplePath $envPath -Force
            Write-Ok "Created .env from .env.example"
        } else {
            Write-Err ".env and .env.example not found in $ProjectRoot"
            exit 1
        }
    }
}

function Get-EnvValue([string]$Key, [string]$Default = "") {
    $envPath = Join-Path $ProjectRoot ".env"
    if (-not (Test-Path $envPath)) { return $Default }
    $lines = Get-Content $envPath -ErrorAction SilentlyContinue
    foreach ($line in $lines) {
        if ($line -match "^\s*#" -or $line -notmatch "=") { continue }
        $parts = $line -split "=", 2
        if ($parts[0].Trim() -eq $Key) {
            $val = $parts[1].Trim()
            if (($val.StartsWith('"') -and $val.EndsWith('"')) -or ($val.StartsWith("'") -and $val.EndsWith("'"))) {
                $val = $val.Substring(1, $val.Length - 2)
            }
            return $val
        }
    }
    return $Default
}

function Set-EnvValue([string]$Key, [string]$Value) {
    $envPath = Join-Path $ProjectRoot ".env"
    $lines = @()
    if (Test-Path $envPath) {
        $lines = @(Get-Content $envPath)
    }
    $formattedValue = $Value
    if ($Value -match "\s" -and -not ($Value.StartsWith('"') -and $Value.EndsWith('"'))) {
        $formattedValue = "`"$Value`""
    }

    $found = $false
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match "^\s*$([Regex]::Escape($Key))\s*=") {
            $lines[$i] = "$Key=$formattedValue"
            $found = $true
            break
        }
    }
    if (-not $found) {
        $lines += "$Key=$formattedValue"
    }
    Set-Content -Path $envPath -Value $lines -Encoding UTF8
}

function Get-LanIpAddress {
    try {
        $ips = Get-NetIPAddress -AddressFamily IPv4 -ErrorAction SilentlyContinue |
            Where-Object { $_.IPAddress -notlike "127.*" -and $_.IPAddress -notlike "169.254.*" -and $_.InterfaceAlias -notmatch "WSL|vEthernet|Docker|Loopback" }
        foreach ($ip in $ips) {
            if ($ip.IPAddress -match "^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)") {
                return $ip.IPAddress
            }
        }
        if ($ips -and $ips.Count -gt 0) {
            return $ips[0].IPAddress
        }
    } catch {}
    return "127.0.0.1"
}

# ---------------------------------------------------------------------------
# Helper: Wait for HTTP 200 and Open Browser
# ---------------------------------------------------------------------------
function Wait-ForServerAndOpenBrowser([string]$Url, [string]$LanHttpsUrl, [int]$TimeoutSeconds = 90) {
    Write-Step "Menunggu web server siap menerima request ($Url)..."
    $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
    $ready = $false
    while ($stopwatch.Elapsed.TotalSeconds -lt $TimeoutSeconds) {
        try {
            $resp = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 3 -MaximumRedirection 5 -ErrorAction Stop
            if ($resp.StatusCode -ge 200 -and $resp.StatusCode -lt 400) {
                $ready = $true
                break
            }
        } catch {
            if ($_.Exception.Response -and [int]$_.Exception.Response.StatusCode -ge 200 -and [int]$_.Exception.Response.StatusCode -lt 400) {
                $ready = $true
                break
            }
        }
        Start-Sleep -Milliseconds 1000
    }

    if ($ready) {
        Write-Ok "Web server berjalan normal (HTTP 200 OK) di $Url"
        if ($LanHttpsUrl) {
            Write-Ok "LAN HTTPS (Mobile Chrome PWA) siap di $LanHttpsUrl"
            Write-Host "      [INFO] Buka $LanHttpsUrl/lan-ca.crt di HP untuk install sertifikat Root CA agar PWA bisa diinstall." -ForegroundColor Gray
        }
        if (-not $NoBrowser -and -not $CheckOnly) {
            Write-Step "Membuka browser secara otomatis ke $Url ..."
            Start-Process $Url
        }
        return $true
    } else {
        Write-Warn "Server belum merespons HTTP 200 dalam batas waktu $TimeoutSeconds detik. Silakan cek log di bawah."
        return $false
    }
}

# ---------------------------------------------------------------------------
# Helper: Ensure Local MySQL is Running (Laragon or Windows Service)
# ---------------------------------------------------------------------------
function Ensure-LocalMySqlRunning([int]$Port = 3306) {
    $tcpCheck = Test-NetConnection -ComputerName "127.0.0.1" -Port $Port -WarningAction SilentlyContinue -ErrorAction SilentlyContinue
    if ($tcpCheck.TcpTestSucceeded) {
        return $true
    }

    Write-Warn "Port MySQL 127.0.0.1:$Port belum aktif. Mencoba menyalakan MySQL / Laragon secara otomatis..."

    # 1. Try Windows Services named MySQL* or MariaDB*
    $services = Get-Service -ErrorAction SilentlyContinue | Where-Object { $_.Name -match "^(MySQL|MariaDB)" -or $_.DisplayName -match "^(MySQL|MariaDB)" }
    foreach ($svc in $services) {
        if ($svc.Status -ne "Running") {
            Write-Host "   [...] Menjalankan Windows Service $($svc.Name)..." -ForegroundColor Yellow
            Start-Service -Name $svc.Name -ErrorAction SilentlyContinue
            Start-Sleep -Seconds 3
        }
    }

    # 2. Check if port is now open
    $tcpCheck = Test-NetConnection -ComputerName "127.0.0.1" -Port $Port -WarningAction SilentlyContinue -ErrorAction SilentlyContinue
    if ($tcpCheck.TcpTestSucceeded) {
        Write-Ok "MySQL service berhasil dinyalakan di port $Port."
        return $true
    }

    # 3. Try Laragon mysqld.exe directly in background
    $mysqldCmd = Get-Command mysqld.exe -ErrorAction SilentlyContinue
    if ($mysqldCmd) {
        Write-Host "   [...] Menjalankan Laragon mysqld.exe di background..." -ForegroundColor Yellow
        Start-Process -FilePath $mysqldCmd.Source -WindowStyle Hidden -ErrorAction SilentlyContinue
        for ($i = 0; $i -lt 8; $i++) {
            Start-Sleep -Seconds 1
            $tcpCheck = Test-NetConnection -ComputerName "127.0.0.1" -Port $Port -WarningAction SilentlyContinue -ErrorAction SilentlyContinue
            if ($tcpCheck.TcpTestSucceeded) {
                Write-Ok "Laragon MySQL (mysqld) berhasil berjalan di port $Port."
                return $true
            }
        }
    }

    return $false
}

# ---------------------------------------------------------------------------
# Helper: Validate & Interactively Repair Database Connection in .env
# ---------------------------------------------------------------------------
function Validate-And-RepairDatabaseConnection([bool]$IsDockerMode = $false) {
    Ensure-EnvFile

    if ($DbTarget -in @("local", "live")) {
        Write-Step "Mengaktifkan profil database '$DbTarget' (php artisan db:use $DbTarget)..."
        & php artisan db:use $DbTarget
    }

    while ($true) {
        $dbConn = Get-EnvValue "DB_CONNECTION" "mysql"
        $dbHost = Get-EnvValue "DB_HOST" "127.0.0.1"
        $dbPort = Get-EnvValue "DB_PORT" "3306"
        $dbName = Get-EnvValue "DB_DATABASE" "inventory"
        $dbUser = Get-EnvValue "DB_USERNAME" "root"
        $dbPass = Get-EnvValue "DB_PASSWORD" ""

        Write-Step "Memeriksa koneksi database .env ($dbConn -> ${dbUser}@${dbHost}:${dbPort}/${dbName})..."

        if ($IsDockerMode -and ($dbHost -eq "db")) {
            Write-Ok "Konfigurasi DB mengarah ke container MySQL Docker (db:3306/$dbName)."
            return $true
        }

        if ($dbConn -eq "mysql" -and ($dbHost -eq "127.0.0.1" -or $dbHost -eq "localhost")) {
            Ensure-LocalMySqlRunning ([int]$dbPort) | Out-Null
        }

        $phpTestScript = @'
<?php
$env = @parse_ini_file('.env');
$conn = $env['DB_CONNECTION'] ?? 'mysql';
if ($conn === 'sqlite') {
    echo 'OK_SQLITE';
    exit(0);
}
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$db   = $env['DB_DATABASE'] ?? 'inventory';
$user = $env['DB_USERNAME'] ?? 'root';
$pass = $env['DB_PASSWORD'] ?? '';
$opts = [
    PDO::ATTR_TIMEOUT => 5,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
];
if (defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
    $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}
try {
    $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, $opts);
    $stmt = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?');
    $stmt->execute([$db]);
    $exists = (bool) $stmt->fetchColumn();
    if (!$exists) {
        if (in_array($host, ['127.0.0.1', 'localhost'], true)) {
            $safeDb = str_replace('`', '``', $db);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo 'CREATED_DB';
            exit(0);
        } else {
            echo "ERR:Database '{$db}' tidak ditemukan di server {$host}:{$port}.";
            exit(2);
        }
    }
    $pdoDb = new PDO("mysql:host={$host};port={$port};dbname={$db}", $user, $pass, $opts);
    $ver = $pdoDb->query('SELECT VERSION()')->fetchColumn();
    echo "OK:{$ver}";
    exit(0);
} catch (Throwable $e) {
    echo 'ERR:' . $e->getMessage();
    exit(1);
}
'@

        $testOutput = ($phpTestScript | & php 2>&1 | Out-String).Trim()

        if ($LASTEXITCODE -eq 0 -and ($testOutput -like "OK*" -or $testOutput -like "CREATED_DB*")) {
            if ($testOutput -like "CREATED_DB*") {
                Write-Ok "Database '$dbName' berhasil dibuat otomatis di ${dbHost}:${dbPort}!"
            } else {
                $verInfo = $testOutput -replace "^OK:?", ""
                Write-Ok "Koneksi database BERHASIL! (${dbHost}:${dbPort}/${dbName} - MySQL $verInfo)"
            }
            return $true
        }

        $errMsg = $testOutput -replace "^ERR:", ""
        Write-Err "Koneksi database ke ${dbHost}:${dbPort}/${dbName} GAGAL!"
        Write-Host "      Detail Error: $errMsg" -ForegroundColor Red

        if ($CheckOnly) {
            exit 1
        }

        Write-Host ""
        Write-Host "   Pilih tindakan perbaikan koneksi Database (.env):" -ForegroundColor Yellow
        Write-Host "     [1] Isi / Perbaiki kredensial Database (.env) secara interaktif" -ForegroundColor White
        if (Test-Path (Join-Path $ProjectRoot ".env.db.local")) {
            Write-Host "     [2] Gunakan profil Database Local (.env.db.local - Laragon 127.0.0.1:3306)" -ForegroundColor White
        }
        if (Test-Path (Join-Path $ProjectRoot ".env.db.live")) {
            Write-Host "     [3] Gunakan profil Database Live (.env.db.live - Cloud MySQL)" -ForegroundColor White
        }
        if ($IsDockerMode) {
            Write-Host "     [4] Gunakan container MySQL bawaan Docker (db:3306 / user: inventory / pass: secret)" -ForegroundColor White
        }
        Write-Host "     [R] Coba tes ulang koneksi sekarang" -ForegroundColor White
        Write-Host "     [Q] Keluar / Batalkan onboarding" -ForegroundColor Gray

        $choice = Read-Host "`n   Pilihan Anda [default: 1]"
        if ([string]::IsNullOrWhiteSpace($choice)) { $choice = "1" }

        switch ($choice.Trim().ToUpper()) {
            "2" {
                & php artisan db:use local
                continue
            }
            "3" {
                & php artisan db:use live
                continue
            }
            "4" {
                if ($IsDockerMode) {
                    Set-EnvValue "DB_CONNECTION" "mysql"
                    Set-EnvValue "DB_HOST" "db"
                    Set-EnvValue "DB_PORT" "3306"
                    Set-EnvValue "DB_DATABASE" "inventory"
                    Set-EnvValue "DB_USERNAME" "inventory"
                    Set-EnvValue "DB_PASSWORD" "secret"
                    Write-Ok "Berhasil mengatur .env ke container MySQL Docker (db:3306)."
                    return $true
                }
            }
            "R" {
                continue
            }
            "Q" {
                Write-Warn "Onboarding dibatalkan oleh pengguna."
                exit 1
            }
            default {
                Write-Host "`n   --- Masukkan Kredensial Database MySQL (Tekan Enter untuk mempertahankan nilai saat ini) ---" -ForegroundColor Cyan
                $newHost = Read-Host "   DB_HOST [$dbHost]"
                if (-not [string]::IsNullOrWhiteSpace($newHost)) { $dbHost = $newHost.Trim() }

                $newPort = Read-Host "   DB_PORT [$dbPort]"
                if (-not [string]::IsNullOrWhiteSpace($newPort)) { $dbPort = $newPort.Trim() }

                $newName = Read-Host "   DB_DATABASE [$dbName]"
                if (-not [string]::IsNullOrWhiteSpace($newName)) { $dbName = $newName.Trim() }

                $newUser = Read-Host "   DB_USERNAME [$dbUser]"
                if (-not [string]::IsNullOrWhiteSpace($newUser)) { $dbUser = $newUser.Trim() }

                $maskedPass = if ([string]::IsNullOrEmpty($dbPass)) { "<kosong>" } else { "********" }
                $newPass = Read-Host "   DB_PASSWORD [$maskedPass] (ketik '-' untuk mengosongkan password)"
                if ($newPass -eq "-") {
                    $dbPass = ""
                } elseif (-not [string]::IsNullOrEmpty($newPass)) {
                    $dbPass = $newPass
                }

                Set-EnvValue "DB_CONNECTION" "mysql"
                Set-EnvValue "DB_HOST" $dbHost
                Set-EnvValue "DB_PORT" $dbPort
                Set-EnvValue "DB_DATABASE" $dbName
                Set-EnvValue "DB_USERNAME" $dbUser
                Set-EnvValue "DB_PASSWORD" $dbPass

                & php artisan config:clear 2>&1 | Out-Null
                Write-Ok "Kredensial .env diperbarui. Menguji ulang koneksi..."
            }
        }
    }
}

# ---------------------------------------------------------------------------
# Helper: Ensure WSL2, Windows Features, Ubuntu, and Docker Desktop (Docker Mode)
# ---------------------------------------------------------------------------
function Ensure-DockerAndWslReady {
    Write-Header "Mode Docker: Memeriksa WSL2, Fitur Windows, Ubuntu & Docker Desktop"

    # 1. Check WSL installation and Windows Features
    Write-Step "Memeriksa status WSL (Windows Subsystem for Linux) & VirtualMachinePlatform..."
    $wslCmd = Get-Command wsl.exe -ErrorAction SilentlyContinue
    $wslStatusOk = $false
    if ($wslCmd) {
        $wslStatusOut = & wsl.exe --status 2>&1 | Out-String
        if ($LASTEXITCODE -eq 0) {
            $wslStatusOk = $true
            Write-Ok "Fitur Windows WSL2 aktif dan siap digunakan."
        }
    }

    if (-not $wslStatusOk) {
        Write-Warn "WSL2 atau VirtualMachinePlatform belum aktif. Mengaktifkan fitur Windows secara otomatis (membutuhkan Administrator)..."
        $enableScript = "dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart; dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart; wsl.exe --set-default-version 2"
        Start-Process powershell.exe -Verb RunAs -ArgumentList "-NoProfile -ExecutionPolicy Bypass -Command `"$enableScript`"" -Wait
        Write-Ok "Perintah aktivasi fitur WSL & VirtualMachinePlatform selesai dijalankan."
    }

    # 2. Check if Ubuntu (or any WSL distro) is installed
    Write-Step "Memeriksa distribusi Linux Ubuntu di WSL..."
    $distroRaw = (& wsl.exe -l -q 2>&1 | Out-String) -replace "\0", ""
    $hasUbuntu = $distroRaw -match "(?i)Ubuntu"
    if (-not $hasUbuntu) {
        Write-Warn "Distribusi Ubuntu belum ditemukan di WSL. Menginstall Ubuntu secara otomatis..."
        & wsl.exe --install -d Ubuntu --no-launch
        Write-Ok "Instalasi Ubuntu WSL selesai."
    } else {
        Write-Ok "Distribusi Ubuntu WSL terdeteksi."
    }

    # 3. Start / Wake up WSL background VM
    Write-Step "Menyalakan instance WSL Ubuntu di background..."
    & wsl.exe -d Ubuntu -- exec true 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Ok "WSL Ubuntu aktif dan berjalan."
    } else {
        Write-Warn "WSL Ubuntu sedang diinisialisasi pertama kali..."
    }

    # 4. Check Docker CLI & Docker Desktop
    Write-Step "Memeriksa Docker Desktop & Docker Engine..."
    $dockerExe = Get-Command docker.exe -ErrorAction SilentlyContinue
    $dockerDesktopPath = "C:\Program Files\Docker\Docker\Docker Desktop.exe"

    if (-not $dockerExe -and -not (Test-Path $dockerDesktopPath)) {
        Write-Warn "Docker Desktop belum terinstall. Mencoba menginstall Docker Desktop via winget..."
        & winget install -e --id Docker.DockerDesktop --accept-package-agreements --accept-source-agreements
        Initialize-LocalToolchainPaths
    }

    # Check if Docker daemon is responding
    & docker info 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        if (Test-Path $dockerDesktopPath) {
            Write-Warn "Docker Engine belum menyala. Menjalankan Docker Desktop di background..."
            Start-Process -FilePath $dockerDesktopPath
        } else {
            Write-Err "Docker Desktop tidak ditemukan di '$dockerDesktopPath'. Pastikan Docker Desktop sudah terinstall."
            exit 1
        }

        Write-Host "   [...] Menunggu Docker Engine siap (docker info)..." -NoNewline -ForegroundColor Yellow
        $maxWait = 120
        $elapsed = 0
        $engineReady = $false
        while ($elapsed -lt $maxWait) {
            Start-Sleep -Seconds 3
            $elapsed += 3
            Write-Host "." -NoNewline -ForegroundColor Yellow
            & docker info 2>&1 | Out-Null
            if ($LASTEXITCODE -eq 0) {
                $engineReady = $true
                break
            }
        }
        Write-Host ""
        if (-not $engineReady) {
            Write-Err "Docker Engine belum siap setelah menunggu $maxWait detik. Pastikan Docker Desktop terbuka dan WSL2 tidak membutuhkan restart Windows."
            exit 1
        }
    }

    Write-Ok "Docker Engine aktif dan siap digunakan!"
}

# ---------------------------------------------------------------------------
# Interactive Mode Selection (when neither --local nor --docker was passed)
# ---------------------------------------------------------------------------
Write-Header "Inventory Management - Smart Easy Onboarding"

if ([string]::IsNullOrWhiteSpace($Mode)) {
    if ($CheckOnly) {
        $Mode = "local"
    } else {
        Write-Host ""
        Write-Host "  Pilih Mode Onboarding yang ingin dijalankan:" -ForegroundColor White
        Write-Host "    [1] Local Mode  (Tanpa Docker -- PHP + MySQL/Laragon + Bun + LAN HTTPS PWA)" -ForegroundColor Green
        Write-Host "    [2] Docker Mode (Otomatis nyalakan WSL2 + Ubuntu + Docker Desktop + make install & dev)" -ForegroundColor Cyan
        Write-Host "    [Q] Keluar" -ForegroundColor Gray
        Write-Host ""
        $sel = Read-Host "  Masukkan pilihan Anda [1/2, default: 1]"
        if ([string]::IsNullOrWhiteSpace($sel) -or $sel.Trim() -eq "1") {
            $Mode = "local"
        } elseif ($sel.Trim() -eq "2") {
            $Mode = "docker"
        } else {
            Write-Warn "Onboarding dibatalkan."
            exit 0
        }
    }
}

$LanIp = Get-LanIpAddress
$LocalHttpUrl = "http://localhost:8000"
$LanHttpsUrl = "https://${LanIp}:8443"

# ===========================================================================
# MODE 1: DOCKER ONBOARDING
# ===========================================================================
if ($Mode -eq "docker") {
    Ensure-DockerAndWslReady
    Validate-And-RepairDatabaseConnection -IsDockerMode $true | Out-Null

    if ($CheckOnly) {
        Write-Ok "Check-only selesai (Docker mode siap)."
        exit 0
    }

    $makeCmd = Get-Command make.exe -ErrorAction SilentlyContinue

    Write-Step "Menjalankan instalasi container & sinkronisasi project (make install)..."
    if ($makeCmd) {
        & make install
    } else {
        & docker compose build
        & docker compose up -d db
        & docker compose run --rm app composer install --no-interaction --prefer-dist
        & docker compose run --rm app bun install
        & docker compose run --rm app php artisan key:generate --ansi
        & docker compose run --rm app bun run build
    }

    if ($LASTEXITCODE -ne 0) {
        Write-Err "Proses 'make install' mengalami kendala. Periksa pesan error di atas."
        exit 1
    }
    Write-Ok "Instalasi Docker selesai!"

    if (-not $NoBrowser) {
        Start-Job -ScriptBlock {
            param($Url)
            for ($i = 0; $i -lt 90; $i++) {
                try {
                    $r = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 2 -MaximumRedirection 5 -ErrorAction Stop
                    if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 400) {
                        Start-Process $Url
                        break
                    }
                } catch {
                    if ($_.Exception.Response -and [int]$_.Exception.Response.StatusCode -ge 200 -and [int]$_.Exception.Response.StatusCode -lt 400) {
                        Start-Process $Url
                        break
                    }
                }
                Start-Sleep -Seconds 1
            }
        } -ArgumentList $LocalHttpUrl | Out-Null
    }

    Write-Step "Menjalankan stack development Docker (make dev)..."
    Write-Host "   [WEB] Web App (Desktop) : $LocalHttpUrl" -ForegroundColor Green
    Write-Host "   [PWA] LAN HTTPS (PWA)   : $LanHttpsUrl" -ForegroundColor Cyan
    Write-Host "   [SSL] Root CA Download  : $LanHttpsUrl/lan-ca.crt" -ForegroundColor Yellow
    Write-Host "   [STOP] Tekan Ctrl+C untuk menghentikan semua container." -ForegroundColor Gray

    if ($makeCmd) {
        & make dev
    } else {
        & docker compose up -d db
        & docker compose run --rm --service-ports app bash ./docker/run-dev.sh
    }
    exit 0
}

# ===========================================================================
# MODE 2: LOCAL ONBOARDING (NO DOCKER)
# ===========================================================================
Write-Header "Mode Local: Memeriksa PHP, Composer, Bun, MySQL & LAN HTTPS"

# 1. Verify PHP, Composer, Bun
$phpCmd = Get-Command php.exe -ErrorAction SilentlyContinue
if (-not $phpCmd) {
    Write-Err "PHP tidak ditemukan di PATH maupun C:\laragon\bin\php. Silakan install Laragon atau PHP 8.2+."
    exit 1
}
$phpVersion = (& php -v | Select-Object -First 1)
Write-Ok "PHP terdeteksi: $phpVersion"

$composerCmd = Get-Command composer -ErrorAction SilentlyContinue
if (-not $composerCmd) {
    Write-Err "Composer tidak ditemukan di PATH. Pastikan Composer sudah terinstall."
    exit 1
}
Write-Ok "Composer terdeteksi: $($composerCmd.Source)"

$bunCmd = Get-Command bun.exe -ErrorAction SilentlyContinue
if (-not $bunCmd) {
    Write-Warn "Bun belum ditemukan. Menginstall Bun secara otomatis..."
    Invoke-RestMethod https://bun.sh/install.ps1 | Invoke-Expression
    Initialize-LocalToolchainPaths
    $bunCmd = Get-Command bun.exe -ErrorAction SilentlyContinue
}
if ($bunCmd) {
    Write-Ok "Bun terdeteksi: v$(& bun -v)"
} else {
    Write-Err "Gagal menemukan Bun. Silakan jalankan: powershell -c `"irm bun.sh/install.ps1 | iex`""
    exit 1
}

# 2. Ensure .env & Validate Database Connection (with interactive repair loop if needed)
Validate-And-RepairDatabaseConnection -IsDockerMode $false | Out-Null

# 3. Install Composer & Bun dependencies if needed
if (-not (Test-Path (Join-Path $ProjectRoot "vendor\autoload.php"))) {
    Write-Step "Menginstall dependensi PHP (composer install)..."
    & composer install --no-interaction --prefer-dist
    if ($LASTEXITCODE -ne 0) {
        Write-Err "composer install gagal."
        exit 1
    }
}
Write-Ok "Dependensi Composer siap."

if (-not (Test-Path (Join-Path $ProjectRoot "node_modules"))) {
    Write-Step "Menginstall dependensi frontend (bun install)..."
    & bun install
    if ($LASTEXITCODE -ne 0) {
        Write-Err "bun install gagal."
        exit 1
    }
}
Write-Ok "Dependensi Bun siap."

# 4. Ensure APP_KEY is set
$appKey = Get-EnvValue "APP_KEY" ""
if (-not $appKey.StartsWith("base64:")) {
    Write-Step "Men-generate APP_KEY Laravel..."
    & php artisan key:generate --ansi
}

# 5. Clear stale caches & Run Smart Database Sync (Migrations + Official Diskominfo Seeders)
Write-Step "Menjalankan migrasi & sinkronisasi pintar database (inventory:smart-db-sync)..."
& php artisan optimize:clear | Out-Null
& php artisan inventory:smart-db-sync --ansi
if ($LASTEXITCODE -ne 0) {
    Write-Err "Sinkronisasi database gagal. Membuka kembali prompt perbaikan database..."
    Validate-And-RepairDatabaseConnection -IsDockerMode $false | Out-Null
    & php artisan inventory:smart-db-sync --ansi
}
Write-Ok "Struktur tabel dan data database tersinkronisasi!"

# 6. Build frontend production assets if manifest is missing
if (-not (Test-Path (Join-Path $ProjectRoot "public\build\manifest.json"))) {
    Write-Step "Mem-build aset frontend (bun run build)..."
    & bun run build
}
Write-Ok "Aset frontend (Vite build) siap."

Write-Step "Menyiapkan sertifikat SSL Root CA & SAN untuk LAN HTTPS (Mobile Chrome PWA)..."
$certGenOut = (& bun scripts/lan-https-proxy.mjs --generate-only 2>&1 | Out-String).Trim()
if ($LASTEXITCODE -eq 0) {
    Write-Ok "Sertifikat SSL LAN HTTPS siap ($certGenOut)"
} else {
    Write-Warn "Peringatan sertifikat SSL LAN: $certGenOut"
}

if ($CheckOnly) {
    Write-Ok "Semua pemeriksaan onboarding Local Mode LULUS 100%!"
    exit 0
}

# 7. Start Local Services (Laravel :8000, LAN HTTPS Proxy :8443, Vite :5173, Webhook :8085)
Remove-Item (Join-Path $ProjectRoot "public\hot") -Force -ErrorAction SilentlyContinue

Write-Step "Menyalakan seluruh layanan Local Mode di background..."
$processes = @()

try {
    $pLaravel = Start-Process -FilePath $phpCmd.Source -ArgumentList "artisan serve --host=0.0.0.0 --port=8000" -WorkingDirectory $ProjectRoot -PassThru -NoNewWindow
    $processes += $pLaravel

    $pVite = Start-Process -FilePath $bunCmd.Source -ArgumentList "run dev" -WorkingDirectory $ProjectRoot -PassThru -NoNewWindow
    $processes += $pVite

    $pWebhook = Start-Process -FilePath $phpCmd.Source -ArgumentList "artisan inventory:webhook-server --host=0.0.0.0 --port=8085" -WorkingDirectory $ProjectRoot -PassThru -NoNewWindow
    $processes += $pWebhook

    $pLanHttps = Start-Process -FilePath $bunCmd.Source -ArgumentList "scripts/lan-https-proxy.mjs" -WorkingDirectory $ProjectRoot -PassThru -NoNewWindow
    $processes += $pLanHttps

    Wait-ForServerAndOpenBrowser -Url $LocalHttpUrl -LanHttpsUrl $LanHttpsUrl -TimeoutSeconds 45 | Out-Null

    Write-Host ""
    Write-Host "======================================================================" -ForegroundColor Green
    Write-Host "  SEMUA LAYANAN BERJALAN DENGAN LANCAR (LOCAL MODE)" -ForegroundColor White
    Write-Host "  [WEB] Desktop Browser   : $LocalHttpUrl" -ForegroundColor Green
    Write-Host "  [LAN] LAN HTTP          : http://${LanIp}:8000" -ForegroundColor Green
    Write-Host "  [PWA] LAN HTTPS (PWA)   : $LanHttpsUrl" -ForegroundColor Cyan
    Write-Host "  [SSL] Sertifikat CA LAN : $LanHttpsUrl/lan-ca.crt" -ForegroundColor Yellow
    Write-Host "  [HMR] Vite HMR Server   : http://localhost:5173" -ForegroundColor Gray
    Write-Host "  [WHK] Webhook Broker    : http://localhost:8085/webhook" -ForegroundColor Gray
    Write-Host "  [STOP] Tekan Ctrl+C untuk menghentikan semua server dengan bersih." -ForegroundColor Yellow
    Write-Host "======================================================================" -ForegroundColor Green

    while ($true) {
        Start-Sleep -Seconds 2
        if ($pLaravel.HasExited) {
            Write-Warn "Proses Laravel (:8000) berhenti."
            break
        }
    }
} finally {
    Write-Host "`n[STOP] Menghentikan semua server Local Mode..." -ForegroundColor Yellow
    Remove-Item (Join-Path $ProjectRoot "public\hot") -Force -ErrorAction SilentlyContinue
    foreach ($proc in $processes) {
        if ($proc -and -not $proc.HasExited) {
            Stop-Process -Id $proc.Id -Force -ErrorAction SilentlyContinue
        }
    }
    Write-Ok "Semua server Local Mode telah dihentikan."
}
