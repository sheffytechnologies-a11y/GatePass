@echo off
title CheckPass GitHub Push
cd /d "c:\Users\DELL\Documents\Code Base\Checkpass\CheckPass"
echo ===================================================
echo  CHECKPASS GITHUB PUSH
echo ===================================================
echo.
echo [1/3] Pushing Sheriff branch to origin...
git push origin Sheriff
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [!] Push failed or authorization was cancelled.
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo [2/3] Pushing main branch to origin...
git push origin main
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo [!] Push failed for main branch.
    pause
    exit /b %ERRORLEVEL%
)

echo.
echo [3/3] Syncing Checkpass-Website dedicated repository...
powershell -ExecutionPolicy Bypass -File scripts\deploy-website-repo.ps1

echo.
echo ===================================================
echo  SUCCESS! ALL REPOSITORIES UPDATED ON GITHUB!
echo ===================================================
echo.
pause
