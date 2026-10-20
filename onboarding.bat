@echo off
setlocal
title Inventory Management - Smart Easy Onboarding
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0onboarding.ps1" %*
exit /b %ERRORLEVEL%
