@echo off
setlocal
title Inventory Management - Smart Cross-Platform Onboarding

where powershell.exe >NUL 2>&1
if errorlevel 1 (
    echo [ERROR] PowerShell tidak ditemukan di sistem Windows ini.
    echo         Silakan gunakan Node.js / Bun: node scripts/dev-cli.mjs setup
    exit /b 1
)

powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0onboarding.ps1" %*
set "EXIT_CODE=%ERRORLEVEL%"
if not "%EXIT_CODE%"=="0" (
    echo.
    echo [INFO] Onboarding selesai dengan kode keluar %EXIT_CODE%.
)
exit /b %EXIT_CODE%
