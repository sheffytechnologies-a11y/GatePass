<#
.SYNOPSIS
    CheckPass Autonomous Agent E2E Testing Runner

.DESCRIPTION
    Executes the comprehensive 7-suite autonomous agentic test runner for CheckPass.
    Tests Public Landing Page, Super Admin CRM, Estate Admin topology, Resident pass generation,
    Security Gate clearance, Homeowner deed verification, and automated teardown.

.EXAMPLE
    .\scripts\run-agent-tests.ps1
    .\scripts\run-agent-tests.ps1 -BaseUrl "http://127.0.0.1:8000/api"
#>

[CmdletBinding()]
param (
    [string]$BaseUrl = "http://127.0.0.1:8000/api"
)

$ErrorActionPreference = "Stop"
$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$CheckPassRoot = Split-Path -Parent $ScriptDir
Set-Location $CheckPassRoot

Write-Host "`n========================================================" -ForegroundColor DarkGreen
Write-Host "   CHECKPASS AUTONOMOUS AGENT E2E TEST RUNNER" -ForegroundColor Green
Write-Host "========================================================`n" -ForegroundColor DarkGreen

# Check Node.js
try {
    $nodeVer = node -v
    Write-Host "[✓] Node.js runtime detected: $nodeVer" -ForegroundColor Cyan
} catch {
    Write-Host "[!] Node.js not found in PATH. Please install Node.js." -ForegroundColor Red
    exit 1
}

# Check PHP
try {
    $phpVer = php -r "echo PHP_VERSION;"
    Write-Host "[✓] PHP runtime detected: $phpVer" -ForegroundColor Cyan
} catch {
    Write-Host "[!] PHP not found in PATH." -ForegroundColor Yellow
}

Write-Host "`nLaunching Autonomous Agent Test Engine (24 E2E Assertions)..." -ForegroundColor Yellow

$env:CHECKPASS_API_BASE = $BaseUrl
node tests/agent-e2e-runner.mjs

$testExitCode = $LASTEXITCODE

if ($testExitCode -eq 0) {
    Write-Host "`n========================================================" -ForegroundColor DarkGreen
    Write-Host "   ALL 24 E2E ASSERTIONS PASSED (100% GREEN)" -ForegroundColor Green
    Write-Host "   Report: CheckPass/reports/AGENT_E2E_TEST_REPORT.md" -ForegroundColor DarkCyan
    Write-Host "========================================================`n" -ForegroundColor DarkGreen
} else {
    Write-Host "`n========================================================" -ForegroundColor DarkRed
    Write-Host "   AGENT E2E TEST RUN FAILED (Exit Code: $testExitCode)" -ForegroundColor Red
    Write-Host "   Check CheckPass/reports/AGENT_E2E_TEST_REPORT.md for diagnostics" -ForegroundColor Yellow
    Write-Host "========================================================`n" -ForegroundColor DarkRed
}

exit $testExitCode
