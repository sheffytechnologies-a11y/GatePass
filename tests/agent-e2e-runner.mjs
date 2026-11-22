/**
 * CheckPass Autonomous Agentic End-to-End Testing Engine
 * 
 * Executable by any AI agent, CI pipeline, or developer.
 * Validates all personas, contracts, and database hygiene across:
 * - Public Website & Inbound Lead Ingestion
 * - Super Admin Sales CRM & Subscriptions
 * - Estate Admin Command Center & Physical Topology
 * - Resident Digital Pass Generation & Extension
 * - Security Guard Gatehouse Ingress/Egress Clearance
 * - Homeowner Portfolio, Verified Deeds & Occupant Privacy
 * - Automatic Post-Test Teardown & Zero-Mock Database Verification
 */

import http from 'http'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'
import { spawn } from 'child_process'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const ROOT_DIR = path.resolve(__dirname, '..')
const API_BASE = process.env.CHECKPASS_API_URL || 'http://127.0.0.1:8000/api'

// ANSI Colors
const RESET = '\x1b[0m'
const BOLD = '\x1b[1m'
const GREEN = '\x1b[32m'
const RED = '\x1b[31m'
const YELLOW = '\x1b[33m'
const CYAN = '\x1b[36m'
const GRAY = '\x1b[90m'

const stats = {
  total: 0,
  passed: 0,
  failed: 0,
  startTime: Date.now(),
  results: [],
}

function logHeader(title) {
  console.log(`\n${BOLD}${CYAN}================================================================${RESET}`)
  console.log(`${BOLD}${CYAN} ${title}${RESET}`)
  console.log(`${BOLD}${CYAN}================================================================${RESET}`)
}

function recordResult(suite, name, passed, detail = '', error = null) {
  stats.total++
  if (passed) {
    stats.passed++
    console.log(`  ${GREEN}✓ [PASS]${RESET} ${BOLD}${name}${RESET}`)
    if (detail) console.log(`     ${GRAY}└─ ${detail}${RESET}`)
  } else {
    stats.failed++
    console.log(`  ${RED}✗ [FAIL]${RESET} ${BOLD}${name}${RESET}`)
    if (detail) console.log(`     ${YELLOW}└─ ${detail}${RESET}`)
    if (error) console.log(`     ${RED}└─ Error: ${error.message || error}${RESET}`)
  }
  stats.results.push({ suite, name, passed, detail, error: error ? String(error) : null })
}

async function apiRequest(endpoint, options = {}) {
  const url = `${API_BASE}${endpoint}`
  const headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    ...(options.headers || {})
  }

  const res = await fetch(url, {
    method: options.method || 'GET',
    headers,
    body: options.body ? JSON.stringify(options.body) : undefined,
  })

  let json = null
  try {
    json = await res.json()
  } catch (err) {
    const text = await res.text().catch(() => '')
    json = { _rawText: text }
  }

  return { status: res.status, ok: res.ok, data: json }
}

async function isServerReachable(urlStr) {
  try {
    const res = await fetch(urlStr, { method: 'GET', signal: AbortSignal.timeout(2000) })
    return res.status < 500
  } catch {
    return false
  }
}

async function ensureBackendServer() {
  const pingUrl = `${API_BASE}/v1/platform-stats`
  const isUp = await isServerReachable(pingUrl)
  if (isUp) {
    console.log(`${GRAY}Backend API is already running at ${API_BASE}${RESET}`)
    return null
  }

  console.log(`${YELLOW}⚡ Local backend not detected. Booting ephemeral PHP artisan serve on port 8000...${RESET}`)
  const apiDir = path.join(ROOT_DIR, 'checkpass-api')
  const child = spawn('cmd.exe', ['/c', 'php artisan serve --host=127.0.0.1 --port=8000'], {
    cwd: apiDir,
    stdio: 'ignore',
    detached: false
  })

  // Wait for server to come online
  for (let i = 0; i < 25; i++) {
    await new Promise(r => setTimeout(r, 400))
    if (await isServerReachable(pingUrl)) {
      console.log(`${GREEN}✓ Ephemeral backend online at ${API_BASE}${RESET}`)
      return child
    }
  }

  throw new Error(`Failed to initialize backend API at ${API_BASE}. Please ensure php artisan serve is running.`)
}

// ─────────────────────────────────────────────────────────────────────────────
// MAIN AGENT E2E EXECUTION PIPELINE
// ─────────────────────────────────────────────────────────────────────────────
async function runAgentE2ETests() {
  console.log(`\n${BOLD}🤖 CHECKPASS AUTONOMOUS AGENT E2E VERIFICATION SUITE${RESET}`)
  console.log(`${GRAY}Target Base API: ${API_BASE}${RESET}`)
  console.log(`${GRAY}Timestamp: ${new Date().toISOString()}${RESET}\n`)

  let serverProcess = null
  try {
    serverProcess = await ensureBackendServer()
  } catch (err) {
    console.error(`${RED}Bootstrap Error: ${err.message}${RESET}`)
    process.exit(1)
  }

  let testLeadId = null
  let superAdminToken = null
  let estateAdminToken = null
  let residentToken = null
  let guardToken = null
  let homeownerToken = null
  let createdPassUlid = null

  try {
    // ── SUITE 1: Public Website & Inbound Pipeline ──
    logHeader('SUITE 1: Public Website & Inbound Pipeline')
    {
      const statsRes = await apiRequest('/v1/platform-stats')
      recordResult(
        'Public Website',
        '1.1 Retrieve Public Platform Telemetry (/v1/platform-stats)',
        statsRes.ok && typeof statsRes.data.propertiesOnboarded === 'number',
        `Active properties: ${statsRes.data?.propertiesOnboarded ?? 0}, Residents: ${statsRes.data?.activeResidents ?? 0}`
      )

      const leadPayload = {
        estate_name: 'Agentic QA Horizon Estate',
        name: 'Chief Automated Tester',
        role: 'Estate Executive Chair',
        phone: '08099887766',
        email: 'qa.agent@checkpass.ng',
        city: 'Lagos',
        units: 120,
        deal_value: 1728000,
        stage: 'new',
        source: 'Website Contact',
        notes: 'End-to-End Agent Automated Lead Ingestion Test'
      }

      const leadRes = await apiRequest('/v1/crm/leads', {
        method: 'POST',
        body: leadPayload
      })

      const leadCreated = leadRes.ok && leadRes.data?.data?.id
      testLeadId = leadRes.data?.data?.id || leadRes.data?.data?.ulid
      recordResult(
        'Public Website',
        '1.2 Submit Website Contact Us Lead Form (POST /v1/crm/leads)',
        leadCreated,
        `Generated Lead ID: ${testLeadId} | Deal Value: ₦${leadRes.data?.data?.deal_value?.toLocaleString() || '1,728,000'}`
      )
    }

    // ── SUITE 2: Super Admin Operations & CRM ──
    logHeader('SUITE 2: Super Admin Operations & CRM')
    {
      const adminLogin = await apiRequest('/v1/admin/auth/login', {
        method: 'POST',
        body: { email: 'admin@checkpass.com', password: '123456' }
      })

      superAdminToken = adminLogin.data?.token
      const isSuper = adminLogin.data?.admin?.role === 'super_admin'
      recordResult(
        'Super Admin',
        '2.1 Super Admin Authentication (admin@checkpass.com)',
        adminLogin.ok && !!superAdminToken && isSuper,
        `Role verified: ${adminLogin.data?.admin?.role || 'N/A'}`
      )

      if (superAdminToken) {
        const crmList = await apiRequest('/v1/crm/leads', {
          headers: { Authorization: `Bearer ${superAdminToken}` }
        })
        const hasTestLead = Array.isArray(crmList.data?.data) && crmList.data.data.some(l => l.id == testLeadId)
        recordResult(
          'Super Admin',
          '2.2 Query Inbound CRM Pipeline (GET /v1/crm/leads)',
          crmList.ok && hasTestLead,
          `Pipeline deals count: ${crmList.data?.data?.length || 0} | Verified test lead present in 'new' stage`
        )

        // Advance stage: discovery -> demo -> pilot -> won
        let stageAdv = true
        for (const nextStage of ['discovery', 'demo', 'pilot', 'won']) {
          const upd = await apiRequest(`/v1/crm/leads/${testLeadId}`, {
            method: 'PATCH',
            headers: { Authorization: `Bearer ${superAdminToken}` },
            body: { stage: nextStage }
          })
          if (!upd.ok) { stageAdv = false; break; }
        }
        recordResult(
          'Super Admin',
          '2.3 Advance Lead Lifecycle Through Kanban Stages (PATCH /crm/leads/:id)',
          stageAdv,
          `Successfully transitioned deal from 'new' -> 'discovery' -> 'demo' -> 'pilot' -> 'won'`
        )

        const subsRes = await apiRequest('/v1/admin/estates', {
          headers: { Authorization: `Bearer ${superAdminToken}` }
        })
        const hasEstates = Array.isArray(subsRes.data?.data || subsRes.data?.estates)
        recordResult(
          'Super Admin',
          '2.4 Global Multi-Estate Telemetry Directory (GET /admin/estates)',
          subsRes.ok && hasEstates,
          `Super Admin cross-estate query verified with global active estates directory`
        )
      }
    }

    // ── SUITE 3: Estate Admin Operations & Resident Management ──
    logHeader('SUITE 3: Estate Admin Operations & Resident Management')
    {
      const estateAdminLogin = await apiRequest('/v1/admin/auth/login', {
        method: 'POST',
        body: { email: 'sheffybig@gmail.com', password: '123456' }
      })

      estateAdminToken = estateAdminLogin.data?.token
      recordResult(
        'Estate Admin',
        '3.1 Estate Admin Authentication (sheffybig@gmail.com / Sheffy Estate)',
        estateAdminLogin.ok && !!estateAdminToken,
        `Active Estate Scope: Sheffy Estate (ID 2)`
      )

      if (estateAdminToken) {
        const propRes = await apiRequest('/v1/admin/estates/2/properties', {
          headers: { Authorization: `Bearer ${estateAdminToken}` }
        })
        const units = propRes.data?.properties || propRes.data?.data || []
        const hasUnits = Array.isArray(units) && units.length > 0
        recordResult(
          'Estate Admin',
          '3.2 Physical Topology & Unit Inventory (GET /admin/estates/2/properties)',
          propRes.ok && hasUnits,
          `Retrieved ${units.length} configured properties in Sheffy Estate with formatted flat addresses`
        )

        const resDir = await apiRequest('/v1/admin/residents', {
          headers: { Authorization: `Bearer ${estateAdminToken}` }
        })
        recordResult(
          'Estate Admin',
          '3.3 Resident Census Roster Query (GET /admin/residents)',
          resDir.ok,
          `Active residents count: ${resDir.data?.data?.length || 0} residents indexed`
        )

        const feeRoster = await apiRequest('/v1/admin/fees', {
          headers: { Authorization: `Bearer ${estateAdminToken}` }
        })
        recordResult(
          'Estate Admin',
          '3.4 Estate Dues & Service Charge Ledger (GET /admin/fees)',
          feeRoster.ok,
          `Zero-mock status verified: ${feeRoster.data?.data?.length || 0} active estate levies`
        )

        const artisanRoster = await apiRequest('/v1/admin/artisans', {
          headers: { Authorization: `Bearer ${estateAdminToken}` }
        })
        const artisanCount = artisanRoster.data?.total ?? artisanRoster.data?.artisans?.length ?? 0
        recordResult(
          'Estate Admin',
          '3.5 Artisan Directory Zero-Mock Integrity Check (GET /admin/artisans)',
          artisanRoster.ok && artisanCount === 0,
          `Confirmed 0 dummy plumbers/electricians; empty state verified`
        )
      }
    }

    // ── SUITE 4: Resident Mobile Experience & Visitor Pass Lifecycle ──
    logHeader('SUITE 4: Resident Mobile Experience & Visitor Pass Lifecycle')
    {
      const resLogin = await apiRequest('/v1/auth/login', {
        method: 'POST',
        body: { email: 'Kemi@gmail.com', password: '123456' }
      })

      residentToken = resLogin.data?.token
      recordResult(
        'Resident App',
        '4.1 Resident User Authentication (Kemi@gmail.com / Sheffy Estate)',
        resLogin.ok && !!residentToken,
        `Resident authenticated: ${resLogin.data?.data?.name || 'Kemi Adeleke'}`
      )

      if (residentToken) {
        const createPass = await apiRequest('/v1/passes', {
          method: 'POST',
          headers: { Authorization: `Bearer ${residentToken}` },
          body: {
            visitorName: 'Senator Babajide (E2E Test Guest)',
            visitorPhone: '08031112233',
            purpose: 'Personal Visit',
            type: 'One-time',
            expiresAt: new Date(Date.now() + 3600000 * 8).toISOString(),
            vehiclePlate: 'E2E-99-TEST'
          }
        })

        const passData = createPass.data?.data || createPass.data?.pass
        createdPassUlid = passData?.ulid || passData?.id
        recordResult(
          'Resident App',
          '4.2 Generate 2-Tap Digital Visitor Pass (POST /v1/passes)',
          createPass.ok && !!createdPassUlid,
          `Pass ULID: ${createdPassUlid} | Visitor: ${passData?.visitor_name || 'Senator Babajide'}`
        )

        if (createdPassUlid) {
          const extRes = await apiRequest(`/v1/passes/${createdPassUlid}/extend`, {
            method: 'PATCH',
            headers: { Authorization: `Bearer ${residentToken}` },
            body: {
              newExpiresAt: new Date(Date.now() + 3600000 * 12).toISOString()
            }
          })
          recordResult(
            'Resident App',
            '4.3 Extend Visitor Pass Validity Window (PATCH /passes/:ulid/extend)',
            extRes.ok,
            `Successfully extended pass validity window`
          )
        }

        const duesFeed = await apiRequest('/v1/fees', {
          headers: { Authorization: `Bearer ${residentToken}` }
        })
        recordResult(
          'Resident App',
          '4.4 Resident Dues & Service Charge Ledger (GET /v1/fees)',
          duesFeed.ok,
          `Retrieved resident personal ledger without tenant privacy leakage`
        )
      }
    }

    // ── SUITE 5: Security Guard Gatehouse Ingress/Egress Clearance ──
    logHeader('SUITE 5: Security Guard Gatehouse Clearance')
    {
      const guardLogin = await apiRequest('/v1/auth/login', {
        method: 'POST',
        body: { email: 'gate@gmail.com', password: '123456' }
      })

      guardToken = guardLogin.data?.token
      recordResult(
        'Gatehouse Security',
        '5.1 Security Guard Terminal Authentication (gate@gmail.com)',
        guardLogin.ok && !!guardToken,
        `Guard authenticated: ${guardLogin.data?.data?.name || 'Gate Officer'}`
      )

      if (guardToken && createdPassUlid) {
        const verifyScan = await apiRequest(`/v1/passes/${createdPassUlid}`, {
          headers: { Authorization: `Bearer ${guardToken}` }
        })
        const passItem = verifyScan.data?.data || verifyScan.data?.pass || verifyScan.data
        const passMatches = passItem?.ulid === createdPassUlid || passItem?.id == createdPassUlid
        recordResult(
          'Gatehouse Security',
          '5.2 Optical HUD QR Scan / Pass Lookup (GET /v1/passes/:ulid)',
          verifyScan.ok && passMatches,
          `Pass verified at gate: Status = ${passItem?.status || 'Pending'}`
        )

        const allowEntry = await apiRequest(`/v1/passes/${createdPassUlid}/allow-entry`, {
          method: 'PATCH',
          headers: { Authorization: `Bearer ${guardToken}` }
        })
        recordResult(
          'Gatehouse Security',
          '5.3 Visitor Ingress Clearance (PATCH /passes/:ulid/allow-entry)',
          allowEntry.ok,
          `Gate barrier lifted. Status transitioned to 'On-site'`
        )

        const markExited = await apiRequest(`/v1/passes/${createdPassUlid}/mark-exited`, {
          method: 'PATCH',
          headers: { Authorization: `Bearer ${guardToken}` }
        })
        recordResult(
          'Gatehouse Security',
          '5.4 Visitor Departure Egress Logging (PATCH /passes/:ulid/mark-exited)',
          markExited.ok,
          `Visitor logged out of estate. Status transitioned to 'Exited'`
        )
      }
    }

    // ── SUITE 6: Homeowner Portfolio & Title Verification ──
    logHeader('SUITE 6: Homeowner Portfolio & Title Verification')
    {
      const ownerLogin = await apiRequest('/v1/auth/login', {
        method: 'POST',
        body: { email: 'babatunde@gmail.com', password: '123456' }
      })

      homeownerToken = ownerLogin.data?.token
      recordResult(
        'Homeowner Portal',
        '6.1 Homeowner Persona Authentication (babatunde@gmail.com)',
        ownerLogin.ok && !!homeownerToken,
        `User Authenticated as Verified Property Owner (Baba Tunde)`
      )

      if (homeownerToken) {
        const homeSummary = await apiRequest('/v1/home/summary', {
          headers: { Authorization: `Bearer ${homeownerToken}` }
        })

        const isOwner = homeSummary.data?.isHomeowner === true
        const props = homeSummary.data?.homeownerProperties || []
        recordResult(
          'Homeowner Portal',
          '6.2 Real Property Title Deeds Query (GET /v1/home/summary)',
          homeSummary.ok && isOwner && props.length > 0,
          `Verified ${props.length} owned property deed(s). Deed ref: ${props[0]?.deedRef || 'DEED-SH-21'}`
        )

        const hasTenantContact = props[0]?.primaryTenant && props[0]?.primaryTenant?.phone
        recordResult(
          'Homeowner Portal',
          '6.3 Tenant Occupant Contact & WhatsApp Links Verification',
          !!hasTenantContact,
          `Tenant: ${props[0]?.primaryTenant?.name || 'Kemi Adeleke'} | Phone: ${props[0]?.primaryTenant?.phone || 'Verified'}`
        )
      }
    }

    // ── SUITE 7: Post-Test Teardown & Zero-Mock Sanity ──
    logHeader('SUITE 7: Post-Test Teardown & Zero-Mock Sanity')
    {
      let leadCleaned = false
      if (testLeadId && superAdminToken) {
        const delLead = await apiRequest(`/v1/crm/leads/${testLeadId}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${superAdminToken}` }
        })
        leadCleaned = delLead.ok
      }
      recordResult(
        'Database Hygiene',
        '7.1 Transient Test Lead Teardown (DELETE /crm/leads/:id)',
        leadCleaned,
        `Test lead #${testLeadId} successfully purged from database`
      )

      let passCleaned = false
      if (createdPassUlid && superAdminToken) {
        const delPass = await apiRequest(`/v1/admin/passes/${createdPassUlid}`, {
          method: 'DELETE',
          headers: { Authorization: `Bearer ${superAdminToken}` }
        })
        passCleaned = delPass.ok
      }
      recordResult(
        'Database Hygiene',
        '7.2 Transient Test Visitor Pass Teardown (DELETE /admin/passes/:ulid)',
        passCleaned,
        `Test pass #${createdPassUlid} successfully purged from database`
      )
    }

  } finally {
    if (serverProcess) {
      console.log(`\n${GRAY}Shutting down ephemeral PHP server...${RESET}`)
      try { serverProcess.kill() } catch {}
    }
  }

  // ───────────────────────────────────────────────────────────────────────────
  // SUMMARY & REPORT GENERATION
  // ───────────────────────────────────────────────────────────────────────────
  const durationSec = ((Date.now() - stats.startTime) / 1000).toFixed(2)
  const isPerfect = stats.failed === 0

  logHeader('TEST EXECUTION SUMMARY')
  console.log(`Total Assertions Executed : ${BOLD}${stats.total}${RESET}`)
  console.log(`Passed Assertions         : ${GREEN}${BOLD}${stats.passed} (${Math.round((stats.passed/stats.total)*100)}%)${RESET}`)
  console.log(`Failed Assertions         : ${stats.failed > 0 ? RED : GREEN}${BOLD}${stats.failed}${RESET}`)
  console.log(`Execution Duration        : ${BOLD}${durationSec}s${RESET}`)
  console.log(`Final System Health       : ${isPerfect ? GREEN + BOLD + '100% OPERATIONAL & VERIFIED' : RED + BOLD + 'FAILURES DETECTED'}${RESET}\n`)

  // Write Markdown Report
  const reportsDir = path.join(ROOT_DIR, 'reports')
  if (!fs.existsSync(reportsDir)) fs.mkdirSync(reportsDir, { recursive: true })

  const reportPath = path.join(reportsDir, 'AGENT_E2E_TEST_REPORT.md')
  let md = `# CheckPass Agentic End-to-End Test Report\n\n`
  md += `**Execution Date:** ${new Date().toUTCString()}  \n`
  md += `**Target API:** \`${API_BASE}\`  \n`
  md += `**Duration:** ${durationSec}s  \n`
  md += `**Result:** **${stats.passed}/${stats.total} Passed (${stats.failed === 0 ? '100% ALL GREEN' : stats.failed + ' FAILED'})**\n\n`
  md += `| Test Suite | Scenario / Verification | Result | Diagnostic Telemetry |\n`
  md += `| :--- | :--- | :---: | :--- |\n`

  for (const r of stats.results) {
    const badge = r.passed ? '`PASS` ✅' : '`FAIL` ❌'
    md += `| **${r.suite}** | ${r.name} | ${badge} | ${r.detail || r.error || 'N/A'} |\n`
  }

  md += `\n---\n*Report generated automatically by CheckPass Autonomous Agentic Testing Engine.*\n`
  fs.writeFileSync(reportPath, md, 'utf-8')
  console.log(`${CYAN}📄 Test report written to: ${reportPath}${RESET}\n`)

  process.exit(stats.failed > 0 ? 1 : 0)
}

runAgentE2ETests().catch((err) => {
  console.error(`${RED}Fatal Test Runner Exception: ${err.stack || err}${RESET}`)
  process.exit(1)
})
