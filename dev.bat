@echo off
setlocal
title Inventory Management - Cross-Platform Dev Runner

set "ROOT=%~dp0"
set "JS_RT="

if exist "%USERPROFILE%\.bun\bin\bun.exe" set "JS_RT=%USERPROFILE%\.bun\bin\bun.exe"
if "%JS_RT%"=="" (
    for /f "delims=" %%i in ('where bun.exe 2^>NUL') do if "%JS_RT%"=="" set "JS_RT=%%i"
)
if "%JS_RT%"=="" (
    for /f "delims=" %%i in ('where node.exe 2^>NUL') do if "%JS_RT%"=="" set "JS_RT=%%i"
)
if "%JS_RT%"=="" if exist "C:\Program Files\nodejs\node.exe" set "JS_RT=C:\Program Files\nodejs\node.exe"

if "%JS_RT%"=="" (
    echo [INFO] Bun/Node.js belum terdeteksi. Membuka Smart Onboarding...
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%ROOT%onboarding.ps1" %*
    exit /b %ERRORLEVEL%
)

if "%~1"=="" (
    "%JS_RT%" "%ROOT%scripts\dev-cli.mjs" run
    exit /b %ERRORLEVEL%
)

"%JS_RT%" "%ROOT%scripts\dev-cli.mjs" %*
exit /b %ERRORLEVEL%
