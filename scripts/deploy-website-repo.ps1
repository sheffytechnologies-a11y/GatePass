# CheckPass Website Synchronization Script
# Synchronizes the checkpass-admin project directly to the dedicated Checkpass-Website repo

$ErrorActionPreference = "Stop"

$workspaceRoot = "c:\Users\DELL\Documents\Code Base\Checkpass\CheckPass"
$adminDir = "$workspaceRoot\checkpass-admin"
$deployTemp = "$env:TEMP\checkpass-website-sync"
$websiteRepo = "https://github.com/sheffytechnologies-a11y/Checkpass-Website.git"

Write-Host "==> 1. Building latest checkpass-admin website..." -ForegroundColor Green
Set-Location $adminDir
npm.cmd run build

Write-Host "==> 2. Preparing clean deployment bundle at $deployTemp..." -ForegroundColor Green
if (Test-Path $deployTemp) {
    Remove-Item -Recurse -Force $deployTemp
}
New-Item -ItemType Directory -Path $deployTemp | Out-Null

# Copy all checkpass-admin files excluding node_modules and .git
$items = Get-ChildItem -Path $adminDir -Exclude "node_modules", ".git"
foreach ($item in $items) {
    Copy-Item -Recurse -Force -Path $item.FullName -Destination $deployTemp
}

Write-Host "==> 3. Initializing Git & committing updates..." -ForegroundColor Green
Set-Location $deployTemp
git init
git config user.name "Sheriff"
git config user.email "sheriff@sheffytechnologies.com"
git branch -M main
git remote add origin $websiteRepo

git add .
git commit -m "feat(website): release harmonized website with contact CRM pipeline, zero-mock telemetry, and mobile toasts"

Write-Host "==> 4. Pushing to Checkpass-Website repository..." -ForegroundColor Green
git push origin main --force

Write-Host "==> SUCCESS! Website repository updated at $websiteRepo" -ForegroundColor Green
