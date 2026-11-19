#Requires -Version 5.1
<#
.SYNOPSIS
    Smart Cross-Platform Onboarding for Windows (Local & Docker Mode)
.DESCRIPTION
    Works on any Windows PC state without requiring Make or Git Bash:
      - Detects Docker, WSL2, PHP (Laragon/Herd/XAMPP/Scoop/Winget), Composer, Bun, Node.js, OpenSSL
      - Offers guided provisioning with explicit confirmation before UAC/WSL2/Winget actions
      - Delegates Local orchestration to scripts/dev-cli.mjs (Bun or Node.js)
      - Supports pure-Docker execution even if host has no PHP, Composer, Bun, or Node.js
#>

$ErrorActionPreference = "Continue"
$ProjectRoot = if ($PSScriptRoot) { $PSScriptRoot } else { (Get-Location).Path }
Set-Location $ProjectRoot

# ---------------------------------------------------------------------------
# Parse CLI Arguments
# ---------------------------------------------------------------------------
$Mode = ""
$DbTarget = ""
$NoBrowser = $false
$CheckOnly = $false
$ShowHelp = $false
$YesAll = $false

foreach ($arg in $args) {
    $normalized = $arg.ToString().Trim().ToLower()
    switch -Regex ($normalized) {
        "^--?local$"           { $Mode = "local" }
        "^--?docker$"          { $Mode = "docker" }
        "^--?db[-:=]sqlite$"   { $DbTarget = "sqlite" }
        "^--?db[-:=]local$"    { $DbTarget = "local" }
        "^--?db[-:=]live$"     { $DbTarget = "live" }
        "^--?db[-:=]docker$"   { $DbTarget = "docker" }
        "^--?no-browser$"      { $NoBrowser = $true }
        "^--?check-only$"      { $CheckOnly = $true }
        "^--?yes$|^-y$"        { $YesAll = $true }
        "^--?help$|^-h$|^\?$"  { $ShowHelp = $true }
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

function Confirm-Action([string]$Prompt, [bool]$DefaultYes = $true) {
    if ($YesAll) { return $true }
    $suffix = if ($DefaultYes) { "[Y/n]" } else { "[y/N]" }
    $ans = Read-Host "   $Prompt $suffix"
    if ([string]::IsNullOrWhiteSpace($ans)) { return $DefaultYes }
    return ($ans.Trim().ToLower() -match "^(y|ya|yes)$")
}

if ($ShowHelp) {
    Write-Header "Inventory Management - Smart Windows Onboarding"
    Write-Host "Pemakaian:"
    Write-Host "  .\onboarding.bat                      # Menu interaktif + deteksi PC otomatis"
    Write-Host "  .\onboarding.bat --local              # Mode Local (PHP + Bun atau Node.js)"
    Write-Host "  .\onboarding.bat --docker             # Mode Docker (WSL2 + Docker Desktop)"
    Write-Host "  .\onboarding.bat --local --db=sqlite  # Mode Local dengan database SQLite zero-config"
    Write-Host "  .\onboarding.bat --local --db=local   # Mode Local dengan MySQL Laragon/XAMPP"
    Write-Host "  .\onboarding.bat --check-only         # Cek diagnostik lingkungan lalu keluar"
    Write-Host ""
    exit 0
}

# ---------------------------------------------------------------------------
# Auto-Discover Local Toolchains (Laragon, Herd, XAMPP, Scoop, Winget, Bun, Node)
# ---------------------------------------------------------------------------
function Initialize-LocalToolchainPaths {
    # Refresh Registry PATH so tools installed by winget in this session are immediately visible
    try {
        $machinePath = [Environment]::GetEnvironmentVariable("Path", "Machine")
        $userPath = [Environment]::GetEnvironmentVariable("Path", "User")
        foreach ($seg in (($machinePath + ";" + $userPath) -split ";")) {
            $trimmed = $seg.Trim()
            if ($trimmed -and ($env:PATH -notlike "*$trimmed*")) {
                $env:PATH = "$env:PATH;$trimmed"
            }
        }
    } catch {}

    $extraPaths = @()

    foreach ($laragonRoot in @("C:\laragon\bin", "D:\laragon\bin")) {
        $phpRoot = Join-Path $laragonRoot "php"
        if (Test-Path $phpRoot) {
            $phpDirs = Get-ChildItem -Path $phpRoot -Directory -ErrorAction SilentlyContinue | Sort-Object Name -Descending
            foreach ($d in $phpDirs) {
                if (Test-Path (Join-Path $d.FullName "php.exe")) {
                    $extraPaths += $d.FullName
                    break
                }
            }
        }
        $mysqlRoot = Join-Path $laragonRoot "mysql"
        if (Test-Path $mysqlRoot) {
            $mysqlDirs = Get-ChildItem -Path $mysqlRoot -Directory -ErrorAction SilentlyContinue | Sort-Object Name -Descending
            foreach ($d in $mysqlDirs) {
                $binPath = Join-Path $d.FullName "bin"
                if (Test-Path (Join-Path $binPath "mysql.exe")) {
                    $extraPaths += $binPath
                    break
                }
            }
        }
        $compDir = Join-Path $laragonRoot "composer"
        if (Test-Path $compDir) { $extraPaths += $compDir }
    }

    $wingetPkgs = "$env:LOCALAPPDATA\Microsoft\WinGet\Packages"
    if (Test-Path $wingetPkgs) {
        Get-ChildItem -Path $wingetPkgs -Directory -Filter "PHP.PHP*" -ErrorAction SilentlyContinue | Sort-Object Name -Descending | ForEach-Object {
            if (Test-Path (Join-Path $_.FullName "php.exe")) {
                $extraPaths += $_.FullName
            }
        }
    }

    $candidates = @(
        (Join-Path $ProjectRoot ".bin"),
        "$env:USERPROFILE\.composer\bin",
        "$env:USERPROFILE\.config\herd\bin",
        "$env:USERPROFILE\.bun\bin",
        "$env:USERPROFILE\scoop\shims",
        "$env:LOCALAPPDATA\Programs\Php",
        "$env:LOCALAPPDATA\Programs\Composer",
        "$env:LOCALAPPDATA\Microsoft\WinGet\Links",
        "C:\xampp\php",
        "C:\php",
        "C:\ProgramData\ComposerSetup\bin",
        "C:\Program Files\nodejs",
        "C:\Program Files\Git\cmd",
        "C:\Program Files\Git\usr\bin",
        "C:\Program Files\Docker\Docker\resources\bin",
        "C:\Program Files\OpenSSL-Win64\bin"
    )

    foreach ($c in $candidates) {
        if ($c -and (Test-Path $c)) { $extraPaths += $c }
    }

    foreach ($p in $extraPaths) {
        if ($env:PATH -notlike "*$p*") {
            $env:PATH = "$p;$env:PATH"
        }
    }
}

Initialize-LocalToolchainPaths

function Get-ToolSnapshot {
    $phpCandidates = @(Get-Command php.exe, php85.exe, php84.exe, php83.exe -All -ErrorAction SilentlyContinue)
    $phpCmd = $null
    $phpVer = ""
    $phpOk = $false
    foreach ($cand in $phpCandidates) {
        $v = (& $cand.Source -r "echo PHP_VERSION;" 2>$null | Out-String).Trim()
        if (-not $v) { continue }
        $ok = ((& $cand.Source -r "echo version_compare(PHP_VERSION, '8.3.0', '>=') ? '1' : '0';" 2>$null | Out-String).Trim() -eq "1")
        if (-not $phpCmd -or $ok) {
            $phpCmd = $cand
            $phpVer = $v
            $phpOk = $ok
            if ($ok) { break }
        }
    }

    $composerCmd = Get-Command composer -ErrorAction SilentlyContinue
    $bunCmd = Get-Command bun.exe -ErrorAction SilentlyContinue
    $nodeCmd = Get-Command node.exe -ErrorAction SilentlyContinue
    $npmCmd = Get-Command npm.cmd -ErrorAction SilentlyContinue
    $dockerCmd = Get-Command docker.exe -ErrorAction SilentlyContinue
    $wingetCmd = Get-Command winget.exe -ErrorAction SilentlyContinue
    $gitBashExists = (Test-Path "C:\Program Files\Git\bin\bash.exe")

    $dockerRunning = $false
    if ($dockerCmd) {
        & docker info 2>&1 | Out-Null
        $dockerRunning = ($LASTEXITCODE -eq 0)
    }

    return [PSCustomObject]@{
        PhpCmd        = $phpCmd
        PhpVersion    = $phpVer
        PhpOk         = $phpOk
        ComposerCmd   = $composerCmd
        BunCmd        = $bunCmd
        NodeCmd       = $nodeCmd
        NpmCmd        = $npmCmd
        DockerCmd     = $dockerCmd
        DockerRunning = $dockerRunning
        WingetCmd     = $wingetCmd
        GitBashExists = $gitBashExists
    }
}

function Show-PcDiagnostics($snap) {
    Write-Step "Status Tool di PC Windows Anda:"
    if ($snap.PhpCmd) {
        $tag = if ($snap.PhpOk) { "[OK]" } else { "[LAMA <8.3]" }
        Write-Host "     • PHP        : $tag v$($snap.PhpVersion) ($($snap.PhpCmd.Source))" -ForegroundColor $(if ($snap.PhpOk) { "Green" } else { "Yellow" })
    } else {
        Write-Host "     • PHP        : [BELUM ADA]" -ForegroundColor Yellow
    }
    Write-Host "     • Composer   : $(if ($snap.ComposerCmd) { "[OK] $($snap.ComposerCmd.Source)" } else { '[BELUM ADA]' })" -ForegroundColor $(if ($snap.ComposerCmd) { "Green" } else { "Yellow" })
    if ($snap.BunCmd) {
        Write-Host "     • JS Runtime : [OK] Bun ($(& bun.exe -v 2>$null))" -ForegroundColor Green
    } elseif ($snap.NodeCmd) {
        Write-Host "     • JS Runtime : [OK] Node.js ($(& node.exe -v 2>$null)) + npm fallback" -ForegroundColor Green
    } else {
        Write-Host "     • JS Runtime : [BELUM ADA] (Butuh Bun atau Node.js untuk Local Mode)" -ForegroundColor Yellow
    }
    $dockerLabel = if ($snap.DockerRunning) { "[OK] Aktif" } elseif ($snap.DockerCmd) { "[TERINSTAL, DAEMON MATI]" } else { "[BELUM ADA]" }
    Write-Host "     • Docker     : $dockerLabel" -ForegroundColor $(if ($snap.DockerRunning) { "Green" } else { "Gray" })
    Write-Host "     • Git Bash   : $(if ($snap.GitBashExists) { '[OK] Tersedia (opsional)' } else { 'Tidak ada (tidak wajib)' })" -ForegroundColor Gray
}

function Ensure-JsRuntimeForLocal($snap) {
    if ($snap.BunCmd -or $snap.NodeCmd) { return $snap }

    Write-Warn "Bun maupun Node.js belum terdeteksi untuk Local Mode."
    if (-not (Confirm-Action "Install Bun (cepat & ringan, tanpa perlu restart) sekarang?" $true)) {
        if ($snap.WingetCmd -and (Confirm-Action "Install Node.js LTS via winget sebagai gantinya?" $true)) {
            & winget install -e --id OpenJS.NodeJS.LTS --accept-package-agreements --accept-source-agreements
            Initialize-LocalToolchainPaths
            return (Get-ToolSnapshot)
        }
        Write-Err "Local Mode membutuhkan Bun atau Node.js. Anda bisa memilih Docker Mode atau install Bun/Node.js manual."
        exit 1
    }

    Write-Step "Menginstall Bun via installer resmi (bun.sh/install.ps1)..."
    Invoke-RestMethod https://bun.sh/install.ps1 | Invoke-Expression
    Initialize-LocalToolchainPaths
    $snap = Get-ToolSnapshot
    if (-not $snap.BunCmd) {
        Write-Err "Bun selesai diinstall tetapi belum terdeteksi. Tutup jendela ini, buka terminal baru, lalu jalankan .\onboarding.bat lagi."
        exit 1
    }
    Write-Ok "Bun v$(& bun.exe -v) berhasil diinstall!"
    return $snap
}

function Ensure-WindowsPhpExtensions([string]$PhpExePath) {
    if (-not $PhpExePath) { return }

    $desired = @("mbstring", "openssl", "fileinfo", "curl", "gd", "zip", "intl", "exif", "pdo_sqlite", "pdo_mysql", "mysqli", "bcmath", "sodium")
    $loadedRaw = (& $PhpExePath -m 2>$null | Out-String)
    $loadedSet = @{}
    foreach ($line in ($loadedRaw -split "\r?\n")) {
        $t = $line.Trim().ToLower()
        if ($t) { $loadedSet[$t] = $true }
    }

    $coreNeeded = @("mbstring", "openssl", "fileinfo", "curl", "gd", "zip", "pdo_sqlite")
    $missing = @($coreNeeded | Where-Object { -not $loadedSet.ContainsKey($_) })
    if ($missing.Count -eq 0) { return }

    Write-Step "Mengaktifkan ekstensi PHP wajib secara otomatis di php.ini ($($missing -join ', '))..."

    $realPhp = (& $PhpExePath -n -r "echo PHP_BINARY;" 2>$null | Out-String).Trim()
    if (-not $realPhp) { $realPhp = $PhpExePath }
    $phpDir = Split-Path -Parent $realPhp
    $extDir = Join-Path $phpDir "ext"

    $loadedIni = (& $PhpExePath -r "echo php_ini_loaded_file() ?: '';" 2>$null | Out-String).Trim()
    $iniPath = if ($loadedIni -and (Test-Path $loadedIni)) { $loadedIni } else { Join-Path $phpDir "php.ini" }

    $applyIniScript = {
        param([string]$TargetIni, [string]$PhpFolder, [string]$ExtFolder, [string[]]$ExtNames)
        $ErrorActionPreference = "Stop"
        if (-not (Test-Path $TargetIni)) {
            $devIni = Join-Path $PhpFolder "php.ini-development"
            $prodIni = Join-Path $PhpFolder "php.ini-production"
            if (Test-Path $devIni) {
                Copy-Item $devIni $TargetIni -Force
            } elseif (Test-Path $prodIni) {
                Copy-Item $prodIni $TargetIni -Force
            } else {
                Set-Content -Path $TargetIni -Value "; Auto-generated by SIM-Inventaris onboarding" -Encoding ASCII
            }
        }

        $lines = [System.Collections.Generic.List[string]]::new([string[]](Get-Content -Path $TargetIni))
        $hasActiveExtDir = $false
        foreach ($l in $lines) {
            if ($l -match "^\s*extension_dir\s*=") { $hasActiveExtDir = $true; break }
        }
        if (-not $hasActiveExtDir) {
            $uncommented = $false
            for ($i = 0; $i -lt $lines.Count; $i++) {
                if ($lines[$i] -match '^\s*;\s*extension_dir\s*=\s*"?ext"?\s*$') {
                    $lines[$i] = 'extension_dir = "ext"'
                    $uncommented = $true
                    break
                }
            }
            if (-not $uncommented) {
                $lines.Add('extension_dir = "ext"')
            }
        }

        foreach ($ext in $ExtNames) {
            $dllPath = Join-Path $ExtFolder "php_$ext.dll"
            if (-not (Test-Path $dllPath)) { continue }
            $activeRe = "^\s*extension\s*=\s*`"?(?:php_)?$([Regex]::Escape($ext))(?:\.dll)?`"?"
            $commentedRe = "^\s*;\s*extension\s*=\s*`"?(?:php_)?$([Regex]::Escape($ext))(?:\.dll)?`"?\s*(?:;.*)?$"
            $alreadyActive = $false
            foreach ($l in $lines) {
                if ($l -match $activeRe) { $alreadyActive = $true; break }
            }
            if ($alreadyActive) { continue }

            $foundIdx = -1
            for ($i = 0; $i -lt $lines.Count; $i++) {
                if ($lines[$i] -match $commentedRe) { $foundIdx = $i; break }
            }
            if ($foundIdx -ge 0) {
                $lines[$foundIdx] = "extension=$ext"
            } else {
                $lines.Add("extension=$ext")
            }
        }
        Set-Content -Path $TargetIni -Value $lines -Encoding ASCII
    }

    try {
        & $applyIniScript -TargetIni $iniPath -PhpFolder $phpDir -ExtFolder $extDir -ExtNames $desired
        Write-Ok "php.ini siap dan ekstensi PHP diaktifkan ($iniPath)."
    } catch {
        Write-Warn "Folder $phpDir memerlukan hak Administrator untuk mengubah php.ini."
        if (Confirm-Action "Izinkan UAC Administrator untuk mengaktifkan ekstensi PHP di $iniPath secara otomatis?" $true) {
            $extArrayLiteral = (($desired | ForEach-Object { "'$_'" }) -join ",")
            $safeIni = $iniPath -replace "'", "''"
            $safePhp = $phpDir -replace "'", "''"
            $safeExt = $extDir -replace "'", "''"
            $uacScript = "& { $($applyIniScript.ToString()) } -TargetIni '$safeIni' -PhpFolder '$safePhp' -ExtFolder '$safeExt' -ExtNames @($extArrayLiteral)"
            $encoded = [Convert]::ToBase64String([System.Text.Encoding]::Unicode.GetBytes($uacScript))
            Start-Process powershell.exe -Verb RunAs -ArgumentList "-NoProfile -ExecutionPolicy Bypass -EncodedCommand $encoded" -Wait
            Write-Ok "Ekstensi PHP selesai diperbarui lewat Administrator."
        }
    }
}

function Ensure-PhpAndComposerForLocal($snap) {
    if (-not $snap.PhpCmd -or -not $snap.PhpOk) {
        Write-Warn "PHP 8.3+ belum tersedia di sistem Anda."
        if ($snap.WingetCmd) {
            Write-Host "     Pilihan instalasi PHP di Windows:" -ForegroundColor White
            Write-Host "       [1] Install PHP 8.3 otomatis via winget (PHP.PHP.8.3)" -ForegroundColor Cyan
            Write-Host "       [2] Install Laravel Herd via winget (Herd.Herd - PHP + Composer lengkap)" -ForegroundColor Cyan
            Write-Host "       [3] Batal (install Laragon/PHP manual atau pakai Docker Mode)" -ForegroundColor Gray
            $pick = if ($YesAll) { "1" } else { (Read-Host "     Pilihan [default: 1]") }
            if ([string]::IsNullOrWhiteSpace($pick)) { $pick = "1" }
            if ($pick -eq "1") {
                & winget install -e --id PHP.PHP.8.3 --accept-package-agreements --accept-source-agreements
                Initialize-LocalToolchainPaths
            } elseif ($pick -eq "2") {
                & winget install -e --id Laravel.Herd --accept-package-agreements --accept-source-agreements
                Initialize-LocalToolchainPaths
            } else {
                exit 1
            }
            $snap = Get-ToolSnapshot
        }
        if (-not $snap.PhpCmd -or -not $snap.PhpOk) {
            Write-Err "PHP 8.3+ belum siap. Jika baru diinstall, buka terminal baru atau jalankan Laragon lalu ulangi .\onboarding.bat."
            exit 1
        }
    }

    Ensure-WindowsPhpExtensions -PhpExePath $snap.PhpCmd.Source

    if (-not $snap.ComposerCmd) {
        Write-Step "Composer belum ditemukan. Menginstall Composer secara otomatis..."
        $composerBinDir = "$env:USERPROFILE\.composer\bin"
        $composerPhar = Join-Path $composerBinDir "composer.phar"
        $composerBat = Join-Path $composerBinDir "composer.bat"
        try {
            New-Item -ItemType Directory -Path $composerBinDir -Force | Out-Null
            if (-not (Test-Path $composerPhar)) {
                [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
                Invoke-WebRequest -Uri "https://getcomposer.org/composer-stable.phar" -OutFile $composerPhar -UseBasicParsing
            }
            $batContent = "@echo off`r`n`"$($snap.PhpCmd.Source)`" `"%~dp0composer.phar`" %*`r`n"
            Set-Content -Path $composerBat -Value $batContent -Encoding ASCII

            # Persist ~/.composer/bin in User PATH so Composer works in future terminals too
            $userPath = [Environment]::GetEnvironmentVariable("Path", "User")
            if ($userPath -notlike "*$composerBinDir*") {
                $newUserPath = if ([string]::IsNullOrWhiteSpace($userPath)) { $composerBinDir } else { "$userPath;$composerBinDir" }
                [Environment]::SetEnvironmentVariable("Path", $newUserPath, "User")
            }
            Initialize-LocalToolchainPaths
            $snap = Get-ToolSnapshot
        } catch {
            Write-Warn "Unduhan langsung composer.phar gagal ($($_.Exception.Message))."
            if ($snap.WingetCmd) {
                Write-Step "Mencoba instalasi Composer via winget (Composer.Composer)..."
                & winget install -e --id Composer.Composer --accept-package-agreements --accept-source-agreements
                Initialize-LocalToolchainPaths
                $snap = Get-ToolSnapshot
            }
        }

        if ($snap.ComposerCmd) {
            $compVer = (& $snap.ComposerCmd.Source --version 2>$null | Select-Object -First 1)
            Write-Ok "Composer otomatis terpasang: $compVer"
        } else {
            Write-Err "Gagal menginstall Composer otomatis. Install manual dari https://getcomposer.org/download/ lalu jalankan .\onboarding.bat lagi."
            exit 1
        }
    }
    return $snap
}

function Ensure-DockerAndWslReady {
    Write-Header "Mode Docker: Memeriksa WSL2, Ubuntu & Docker Desktop"

    $wslCmd = Get-Command wsl.exe -ErrorAction SilentlyContinue
    $wslOk = $false
    if ($wslCmd) {
        & wsl.exe --status 2>&1 | Out-Null
        $wslOk = ($LASTEXITCODE -eq 0)
    }

    if (-not $wslOk) {
        Write-Warn "WSL2 / VirtualMachinePlatform belum aktif (membutuhkan hak Administrator dan mungkin restart Windows)."
        if (-not (Confirm-Action "Aktifkan WSL2 & VirtualMachinePlatform sekarang (akan muncul prompt UAC)?" $true)) {
            Write-Err "Mode Docker membutuhkan WSL2. Gunakan Mode Local (.\onboarding.bat --local) jika tidak ingin mengaktifkan WSL2."
            exit 1
        }
        $enableScript = "dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart; dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart; wsl.exe --set-default-version 2"
        Start-Process powershell.exe -Verb RunAs -ArgumentList "-NoProfile -ExecutionPolicy Bypass -Command `"$enableScript`"" -Wait
        Write-Warn "Jika aktivasi WSL2 baru pertama kali dilakukan, Windows mungkin memerlukan RESTART sebelum Docker dapat menyala."
    } else {
        Write-Ok "WSL2 aktif."
    }

    $dockerExe = Get-Command docker.exe -ErrorAction SilentlyContinue
    $dockerDesktopPath = "C:\Program Files\Docker\Docker\Docker Desktop.exe"
    if (-not $dockerExe -and -not (Test-Path $dockerDesktopPath)) {
        Write-Warn "Docker Desktop belum terinstall."
        if (-not (Confirm-Action "Install Docker Desktop otomatis via winget?" $true)) {
            exit 1
        }
        & winget install -e --id Docker.DockerDesktop --accept-package-agreements --accept-source-agreements
        Initialize-LocalToolchainPaths
    }

    & docker info 2>&1 | Out-Null
    if ($LASTEXITCODE -ne 0) {
        if (Test-Path $dockerDesktopPath) {
            Write-Step "Menyalakan Docker Desktop..."
            Start-Process -FilePath $dockerDesktopPath
        } else {
            Write-Err "Docker Desktop tidak ditemukan di '$dockerDesktopPath'."
            exit 1
        }

        Write-Host "   [...] Menunggu Docker Engine siap..." -NoNewline -ForegroundColor Yellow
        $engineReady = $false
        for ($i = 0; $i -lt 40; $i++) {
            Start-Sleep -Seconds 3
            Write-Host "." -NoNewline -ForegroundColor Yellow
            & docker info 2>&1 | Out-Null
            if ($LASTEXITCODE -eq 0) {
                $engineReady = $true
                break
            }
        }
        Write-Host ""
        if (-not $engineReady) {
            Write-Err "Docker Engine belum merespons. Jika baru mengaktifkan WSL2/install Docker, silakan RESTART Windows lalu jalankan .\onboarding.bat --docker."
            exit 1
        }
    }
    Write-Ok "Docker Engine aktif dan siap!"
}

# ---------------------------------------------------------------------------
# Main Flow
# ---------------------------------------------------------------------------
Write-Header "Inventory Management — Smart Windows Onboarding"
$snap = Get-ToolSnapshot
Show-PcDiagnostics $snap

if ($CheckOnly -and [string]::IsNullOrWhiteSpace($Mode)) {
    $Mode = if ($snap.PhpOk -and ($snap.BunCmd -or $snap.NodeCmd)) { "local" } elseif ($snap.DockerRunning) { "docker" } else { "local" }
}

if ([string]::IsNullOrWhiteSpace($Mode)) {
    $recommended = if ($snap.DockerRunning -and -not $snap.PhpOk) { "2" } else { "1" }
    Write-Host ""
    Write-Host "  Pilih Mode Onboarding:" -ForegroundColor White
    Write-Host "    [1] Local Mode  (PHP + Bun/Node.js + SQLite/MySQL — paling ringan, tanpa Docker)" -ForegroundColor Green
    Write-Host "    [2] Docker Mode (Container PHP 8.3 + Bun + MySQL — terisolasi, butuh Docker Desktop)" -ForegroundColor Cyan
    Write-Host "    [Q] Keluar" -ForegroundColor Gray
    $pick = Read-Host "`n  Pilihan Anda [rekomendasi: $recommended]"
    if ([string]::IsNullOrWhiteSpace($pick)) { $pick = $recommended }
    switch ($pick.Trim().ToUpper()) {
        "2" { $Mode = "docker" }
        "Q" { exit 0 }
        default { $Mode = "local" }
    }
}

$cliArgs = @("setup", "--$Mode")
if ($DbTarget) { $cliArgs += "--db=$DbTarget" }
if ($NoBrowser) { $cliArgs += "--no-browser" }
if ($CheckOnly) { $cliArgs += "--check-only" } else { $cliArgs += "--start" }
if ($YesAll) { $cliArgs += "--yes" }

if ($Mode -eq "docker") {
    Ensure-DockerAndWslReady
    $snap = Get-ToolSnapshot

    if ($snap.BunCmd) {
        & $snap.BunCmd.Source "scripts\dev-cli.mjs" @cliArgs
        exit $LASTEXITCODE
    }
    if ($snap.NodeCmd) {
        & $snap.NodeCmd.Source "scripts\dev-cli.mjs" @cliArgs
        exit $LASTEXITCODE
    }

    # Pure Docker execution when host has neither Bun nor Node.js
    Write-Step "Menjalankan setup & dev langsung lewat Docker Compose (tanpa butuh Node/Bun/PHP di host)..."
    if (-not (Test-Path (Join-Path $ProjectRoot ".env"))) {
        Copy-Item (Join-Path $ProjectRoot ".env.example") (Join-Path $ProjectRoot ".env") -Force
        Write-Ok "Created .env from .env.example"
    }
    if ($CheckOnly) {
        Write-Ok "Check-only Docker mode lulus!"
        exit 0
    }
    & docker compose build
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    & docker compose up -d db
    & docker compose run --rm app composer install --no-interaction --prefer-dist
    & docker compose run --rm app bun install
    & docker compose run --rm app php artisan key:generate --ansi
    & docker compose run --rm app php artisan inventory:smart-db-sync --ansi
    & docker compose run --rm app bun run build

    if (-not $NoBrowser) {
        Start-Job -ScriptBlock {
            param($Url)
            for ($i = 0; $i -lt 90; $i++) {
                try {
                    $r = Invoke-WebRequest -Uri $Url -UseBasicParsing -TimeoutSec 2 -ErrorAction Stop
                    if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 400) { Start-Process $Url; break }
                } catch {}
                Start-Sleep -Seconds 1
            }
        } -ArgumentList "http://localhost:8000" | Out-Null
    }

    & docker compose run --rm --service-ports app bash ./docker/run-dev.sh
    exit $LASTEXITCODE
}

# Local Mode: ensure PHP, Composer, and Bun or Node.js are ready, then run scripts/dev-cli.mjs
$snap = Ensure-PhpAndComposerForLocal $snap
$snap = Ensure-JsRuntimeForLocal $snap

$jsExe = if ($snap.BunCmd) { $snap.BunCmd.Source } else { $snap.NodeCmd.Source }
Write-Step "Menjalankan cross-platform runner ($jsExe scripts\dev-cli.mjs $($cliArgs -join ' '))..."
& $jsExe "scripts\dev-cli.mjs" @cliArgs
exit $LASTEXITCODE
