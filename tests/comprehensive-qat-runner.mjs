/**
 * CheckPass Exhaustive End-to-End QAT, Security & Edge-Case Suite
 * ("No Stone Unturned" — Covers every module, persona, Support, AI Chatbot & Play Store flows)
 *
 * Run: node tests/comprehensive-qat-runner.mjs
 */

import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const ROOT_DIR = path.resolve(__dirname, '..')
const API_BASE = process.env.CHECKPASS_API_URL || 'http://127.0.0.1:8000/api'

let passed = 0
let failed = 0
const reportRows = []

function check(module, name, ok, detail = '') {
  if (ok) {
    passed++
    console.log(`  [PASS] [${module}] ${name}${detail ? ' — ' + detail : ''}`)
    reportRows.push({ module, name, status: 'PASS', detail })
  } else {
    failed++
    console.log(`  [FAIL] [${module}] ${name}${detail ? ' — ' + detail : ''}`)
    reportRows.push({ module, name, status: 'FAIL', detail })
  }
}

async function req(endpoint, { method = 'GET', token = null, body = null, headers = {} } = {}) {
  const h = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
    ...headers,
  }
  if (token) h['Authorization'] = `Bearer ${token}`
  const res = await fetch(`${API_BASE}${endpoint}`, {
    method,
    headers: h,
    body: body ? JSON.stringify(body) : undefined,
  })
  let data = null
  try {
    data = await res.json()
  } catch {
    data = null
  }
  return { status: res.status, ok: res.ok, headers: res.headers, data }
}

async function main() {
  console.log('======================================================================')
  console.log(' CHECKPASS EXHAUSTIVE QAT, SECURITY & EDGE-CASE SUITE')
  console.log('======================================================================\n')

  const cleanup = {
    leads: [],
    passes: [],
    emergencies: [],
    artisans: [],
    news: [],
    fees: [],
    properties: [],
    users: [],
  }

  // ── 1. Auth, Validation & Session Edge Cases ───────────────────────────
  console.log('--- 1. Auth, Validation & Session Edge Cases ---')
  const badPwd = await req('/v1/auth/login', { method: 'POST', body: { email: 'Kemi@gmail.com', password: 'wrong_password' } })
  check('Auth', 'Reject invalid password (401/422)', badPwd.status === 401 || badPwd.status === 422, `status=${badPwd.status}`)

  const emptyAuth = await req('/v1/auth/login', { method: 'POST', body: {} })
  check('Auth', 'Reject empty login payload (422)', emptyAuth.status === 422, `status=${emptyAuth.status}`)

  const superLogin = await req('/v1/admin/auth/login', { method: 'POST', body: { email: 'admin@checkpass.com', password: '123456' } })
  const superToken = superLogin.data?.token
  check('Auth', 'Super Admin login & role check', superLogin.ok && superLogin.data?.admin?.role === 'super_admin')

  const estLogin = await req('/v1/admin/auth/login', { method: 'POST', body: { email: 'sheffybig@gmail.com', password: '123456' } })
  const estToken = estLogin.data?.token
  check('Auth', 'Estate Admin login', estLogin.ok && !!estToken)

  const resLogin = await req('/v1/auth/login', { method: 'POST', body: { email: 'Kemi@gmail.com', password: '123456' } })
  const resToken = resLogin.data?.token
  check('Auth', 'Resident login', resLogin.ok && !!resToken)

  const ownerLogin = await req('/v1/auth/login', { method: 'POST', body: { email: 'babatunde@gmail.com', password: '123456' } })
  const ownerToken = ownerLogin.data?.token
  check('Auth', 'Homeowner login', ownerLogin.ok && !!ownerToken)

  const secLogin = await req('/v1/auth/login', { method: 'POST', body: { email: 'gate@gmail.com', password: '123456' } })
  const secToken = secLogin.data?.token
  check('Auth', 'Security Guard login', secLogin.ok && !!secToken)

  const meRes = await req('/v1/auth/me', { token: resToken })
  check('Auth', 'GET /v1/auth/me returns authenticated resident', meRes.ok)

  const forgotRes = await req('/v1/auth/forgot-password', { method: 'POST', body: { email: 'nonexistent_qat@checkpass.ng' } })
  check('Auth', 'Forgot password handles unknown email gracefully (no 500)', forgotRes.status < 500, `status=${forgotRes.status}`)

  // Logout & token revocation check
  const resLoginTemp = await req('/v1/auth/login', { method: 'POST', body: { email: 'Kemi@gmail.com', password: '123456' } })
  if (resLoginTemp.data?.token) {
    const out = await req('/v1/auth/logout', { method: 'POST', token: resLoginTemp.data.token })
    check('Auth', 'Logout succeeds (200)', out.ok, `status=${out.status}`)
    const reuse = await req('/v1/auth/me', { token: resLoginTemp.data.token })
    check('Auth', 'Revoked token rejected after logout (401)', reuse.status === 401, `status=${reuse.status}`)
  }

  // ── 2. Security, RBAC & Multi-Tenant Isolation ─────────────────────────
  console.log('\n--- 2. Security, RBAC & Multi-Tenant Isolation ---')
  const unauthCrm = await req('/v1/crm/leads')
  check('Security', 'Unauthenticated GET /v1/crm/leads blocked (401)', unauthCrm.status === 401, `status=${unauthCrm.status}`)

  const unauthCrmDel = await req('/v1/crm/leads/1', { method: 'DELETE' })
  check('Security', 'Unauthenticated DELETE /v1/crm/leads/1 blocked (401)', unauthCrmDel.status === 401, `status=${unauthCrmDel.status}`)

  const resAdmin = await req('/v1/admin/dashboard', { token: resToken })
  check('Security', 'Resident blocked from /v1/admin/dashboard (403)', resAdmin.status === 403, `status=${resAdmin.status}`)

  const estSubs = await req('/v1/admin/billing/subscribers', { token: estToken })
  check('Security', 'Estate Admin blocked from Super-Admin subscribers (403)', estSubs.status === 403, `status=${estSubs.status}`)

  const est1Props = await req('/v1/admin/estates/1/properties', { token: estToken })
  check('Security', 'Estate 2 Admin blocked from Estate 1 properties (403)', est1Props.status === 403, `status=${est1Props.status}`)

  const secCreatePass = await req('/v1/passes', { method: 'POST', token: secToken, body: { visitorName: 'Illegal', purpose: 'Delivery', type: 'One-time', expiresAt: new Date(Date.now() + 3600000).toISOString() } })
  check('Security', 'Security Guard cannot create resident visitor pass (403/404)', secCreatePass.status >= 400 && secCreatePass.status < 500, `status=${secCreatePass.status}`)

  // CORS check
  const corsRes = await req('/v1/platform-stats', { headers: { Origin: 'https://evil-attacker.example.com' } })
  const allowOrigin = corsRes.headers.get('access-control-allow-origin')
  check('Security', 'CORS does not echo wildcard * to untrusted origin', allowOrigin !== '*', `ACAO=${allowOrigin}`)

  // ── 3. Website AI Chatbot, Concierge & Inbound CRM Pipeline ────────────
  console.log('\n--- 3. Website AI Chatbot, Concierge & Inbound CRM ---')
  const landingPath = path.join(ROOT_DIR, 'checkpass-admin', 'src', 'views', 'LandingView.vue')
  const landingSrc = fs.readFileSync(landingPath, 'utf8')
  check('Website AI', 'LandingView has Quick Topics anchored above chat stream', landingSrc.includes('cp-concierge-chips-inline') && landingSrc.includes('conciergeMessages.slice(1)'))
  check('Website AI', 'LandingView handles natural CheckPass intro queries', landingSrc.toLowerCase().includes('tell me about checkpass') || landingSrc.toLowerCase().includes('what is checkpass'))

  const statsRes = await req('/v1/platform-stats')
  check('Website AI', 'Public /v1/platform-stats returns live telemetry', statsRes.ok && typeof statsRes.data?.propertiesOnboarded === 'number')

  const badLead = await req('/v1/crm/leads', { method: 'POST', body: { estate_name: 'Bad Email Estate', name: 'Test', email: 'not-an-email' } })
  check('Website AI', 'Lead ingestion rejects invalid email (422)', badLead.status === 422, `status=${badLead.status}`)

  const aiLead = await req('/v1/crm/leads', {
    method: 'POST',
    body: {
      estate_name: 'QAT Concierge Estate',
      name: 'AI Chatbot Lead',
      phone: '08055544433',
      email: 'concierge.qat@checkpass.ng',
      city: 'Abuja',
      units: 80,
      source: 'AI Concierge',
      chat_transcript: 'User: tell me about checkpass\nAI: CheckPass provides 4-second QR clearance...',
    },
  })
  const aiLeadId = aiLead.data?.data?.id
  if (aiLeadId) cleanup.leads.push(aiLeadId)
  check('Website AI', 'AI Concierge lead ingestion creates CRM deal with transcript (201)', aiLead.ok && !!aiLeadId, `id=${aiLeadId}`)

  if (aiLeadId) {
    const crmList = await req('/v1/crm/leads', { token: superToken })
    check('Website AI', 'Super Admin sees AI Concierge lead in CRM pipeline', crmList.ok && crmList.data?.data?.some(l => l.id === aiLeadId))
  }

  // ── 4. Help & Support Module (Web & Mobile) ────────────────────────────
  console.log('\n--- 4. Help & Support Module ---')
  const profileSrc = fs.readFileSync(path.join(ROOT_DIR, 'checkpass-app', 'src', 'views', 'ProfileView.vue'), 'utf8')
  const supportSrc = fs.readFileSync(path.join(ROOT_DIR, 'checkpass-admin', 'src', 'views', 'SupportView.vue'), 'utf8')
  check('Help & Support', 'Mobile ProfileView exposes FAQ, Privacy Policy (NDPA 2023), Terms & Delete Account',
    profileSrc.includes('showFaq') && profileSrc.includes('showPrivacy') && profileSrc.includes('showTerms') && profileSrc.includes('handleDeleteAccount'))
  check('Help & Support', 'Mobile ProfileView includes Message Estate Office & Support Ticket composer',
    profileSrc.includes('Message Estate Office') && profileSrc.includes('submittingTicket'))
  check('Help & Support', 'Admin SupportView includes Tickets & Resident Messages triage tabs',
    supportSrc.includes('Support Tickets') && supportSrc.includes('Resident Messages'))

  // ── 5. Passes, Declared Items, Flagging & Gatehouse Edge Cases ─────────
  console.log('\n--- 5. Passes, Declared Items, Flagging & Gatehouse ---')
  const notFoundPass = await req('/v1/passes/NON_EXISTENT_ULID_000', { token: resToken })
  check('Passes', 'Missing pass ULID returns 404', notFoundPass.status === 404, `status=${notFoundPass.status}`)

  const pastPass = await req('/v1/passes', { method: 'POST', token: resToken, body: { visitorName: 'Past', purpose: 'Delivery', type: 'One-time', expiresAt: '2020-01-01T00:00:00Z' } })
  check('Passes', 'Past expiresAt rejected (422)', pastPass.status === 422, `status=${pastPass.status}`)

  const recNoDays = await req('/v1/passes', { method: 'POST', token: resToken, body: { visitorName: 'Rec', purpose: 'Service', type: 'Recurring', expiresAt: new Date(Date.now() + 86400000).toISOString() } })
  check('Passes', 'Recurring pass without recurringDays rejected (422)', recNoDays.status === 422, `status=${recNoDays.status}`)

  // Create a transient Estate 2 Security Guard so we test BOTH cross-estate isolation (Estate 1 guard blocked) AND same-estate gatehouse flows!
  const est2SecEmail = `qat.sec2.${Date.now()}@checkpass.local`
  const createEst2Sec = await req('/v1/admin/users', {
    method: 'POST',
    token: estToken,
    body: {
      name: 'QAT Estate 2 Guard',
      email: est2SecEmail,
      phone: `0808${String(Date.now()).slice(-7)}`,
      password: 'Password123!',
      type: 'security',
      estate_id: 2,
    },
  })
  const est2SecId = createEst2Sec.data?.user?.id || createEst2Sec.data?.data?.id || createEst2Sec.data?.id
  if (est2SecId) cleanup.users.push(est2SecId)
  const est2SecLogin = est2SecId
    ? await req('/v1/auth/login', { method: 'POST', body: { email: est2SecEmail, password: 'Password123!' } })
    : null
  const est2SecToken = est2SecLogin?.data?.token || secToken

  // Pass 1: Add item -> cross-estate guard blocked -> same-estate find-by-phone (verifies 500 crash is FIXED!) -> allow-entry -> mark-exited
  const p1 = await req('/v1/passes', {
    method: 'POST',
    token: resToken,
    body: { visitorName: 'QAT Item Visitor', visitorPhone: '08077766655', purpose: 'Service', type: 'One-time', expiresAt: new Date(Date.now() + 7200000).toISOString(), vehiclePlate: 'LAG-999-QA' },
  })
  const ulid1 = p1.data?.pass?.id
  const passCode1 = p1.data?.pass?.passCode
  if (ulid1) cleanup.passes.push(ulid1)
  check('Passes', 'Create One-time pass with vehicle plate & 6-digit passCode (201)', p1.status === 201 && !!ulid1 && /^\d{6}$/.test(String(passCode1 || '')), `ulid=${ulid1}, passCode=${passCode1}`)

  if (ulid1) {
    const addItem = await req(`/v1/passes/${ulid1}/items`, { method: 'POST', token: resToken, body: { name: 'Laptop & Toolkit', quantity: 2 } })
    check('Passes', 'Declare item on pass (201)', addItem.ok, `status=${addItem.status}`)

    // Cross-estate guard (Estate 1) must NOT see Estate 2 pass (by ULID or 6-digit code)
    const crossGuardLookup = await req(`/v1/passes/${ulid1}`, { token: secToken })
    check('Security', 'Estate 1 Security Guard blocked from viewing Estate 2 pass (404)', crossGuardLookup.status === 404, `status=${crossGuardLookup.status}`)

    const crossCodeLookup = await req(`/v1/passes/lookup?query=${encodeURIComponent(passCode1)}`, { token: secToken })
    check('Security', 'Estate 1 Security Guard blocked from Estate 2 6-digit code lookup (404)', crossCodeLookup.status === 404, `status=${crossCodeLookup.status}`)

    // Unified lookup by 6-digit passCode, phone number, and ULID
    const lookupByCode = await req(`/v1/passes/lookup?query=${encodeURIComponent(passCode1)}`, { token: est2SecToken })
    check('Passes', 'Security lookup by 6-digit passCode returns 200', lookupByCode.status === 200 && lookupByCode.data?.pass?.id === ulid1, `passCode=${passCode1}`)

    const lookupByPhone = await req('/v1/passes/lookup?query=08077766655', { token: est2SecToken })
    check('Passes', 'Security lookup by visitor phone returns 200', lookupByPhone.status === 200 && lookupByPhone.data?.pass?.id === ulid1)

    // Edge case: Multiple active passes for the same visitor phone number (Multi-Pass Picker)
    const p2 = await req('/v1/passes', {
      method: 'POST',
      token: resToken,
      body: {
        visitorName: 'Musa delivery 2',
        visitorPhone: '08077766655',
        purpose: 'Delivery',
        type: 'One-time',
        expiresAt: new Date(Date.now() + 86400000).toISOString(),
      },
    })
    const ulid2 = p2.data?.pass?.id
    if (ulid2) cleanup.passes.push(ulid2)
    check('Passes', 'Create second pass with duplicate phone number in same estate (201)', p2.status === 201 && !!ulid2, `status=${p2.status}`)

    const multiLookup = await req('/v1/passes/lookup?query=08077766655', { token: est2SecToken })
    check(
      'Passes',
      'Phone lookup returns multiple active passes array for picker (200)',
      multiLookup.status === 200 &&
      Array.isArray(multiLookup.data?.passes) &&
      multiLookup.data.passes.length >= 2 &&
      multiLookup.data.passes.some(p => p.id === ulid1) &&
      multiLookup.data.passes.some(p => p.id === ulid2),
      `count=${multiLookup.data?.passes?.length}`
    )

    const lookupByUlid = await req(`/v1/passes/lookup?query=${encodeURIComponent(ulid1)}`, { token: est2SecToken })
    check('Passes', 'Security lookup by ULID returns 200', lookupByUlid.status === 200 && lookupByUlid.data?.pass?.id === ulid1)

    const lookupInvalid = await req('/v1/passes/lookup?query=000000', { token: est2SecToken })
    check('Passes', 'Security lookup with invalid 6-digit code returns 404', lookupInvalid.status === 404, `status=${lookupInvalid.status}`)

    const findPhone = await req('/v1/passes/find-by-phone?phone=08077766655', { token: est2SecToken })
    check('Passes', 'Security find-by-phone with unflagged declared item returns 200 (no 500 crash)', findPhone.status === 200 && (findPhone.data?.pass?.id === ulid1 || findPhone.data?.pass?.id === ulid2), `status=${findPhone.status}`)

    const resAllow = await req(`/v1/passes/${ulid1}/allow-entry`, { method: 'PATCH', token: resToken })
    check('Passes', 'Resident blocked from self-approving entry (403)', resAllow.status === 403, `status=${resAllow.status}`)

    const exitBeforeEntry = await req(`/v1/passes/${ulid1}/mark-exited`, { method: 'PATCH', token: est2SecToken })
    check('Passes', 'Cannot mark Pending visitor exited before arrival (422)', exitBeforeEntry.status === 422, `status=${exitBeforeEntry.status}`)

    const allow = await req(`/v1/passes/${ulid1}/allow-entry`, { method: 'PATCH', token: est2SecToken })
    check('Passes', 'Security allows entry -> On-site (200)', allow.ok && allow.data?.pass?.status === 'On-site')

    const exit = await req(`/v1/passes/${ulid1}/mark-exited`, { method: 'PATCH', token: est2SecToken })
    check('Passes', 'Security marks exit -> Exited (200)', exit.ok && exit.data?.pass?.status === 'Exited')

    const declineExited = await req(`/v1/passes/${ulid1}/decline-entry`, { method: 'PATCH', token: est2SecToken })
    check('Passes', 'Cannot decline an already Exited pass (422)', declineExited.status === 422, `status=${declineExited.status}`)
  }

  // Pass 2: Revoke & Decline edge cases
  const p2 = await req('/v1/passes', {
    method: 'POST',
    token: resToken,
    body: { visitorName: 'QAT Revoke Visitor', purpose: 'Personal Visit', type: 'One-time', expiresAt: new Date(Date.now() + 3600000).toISOString() },
  })
  const ulid2 = p2.data?.pass?.id
  if (ulid2) cleanup.passes.push(ulid2)
  if (ulid2) {
    const rev = await req(`/v1/passes/${ulid2}/revoke`, { method: 'PATCH', token: resToken })
    check('Passes', 'Resident revokes pass -> Revoked', rev.ok && rev.data?.pass?.status === 'Revoked')
    const allowRev = await req(`/v1/passes/${ulid2}/allow-entry`, { method: 'PATCH', token: est2SecToken })
    check('Passes', 'Blocked from allowing entry on Revoked pass (422)', allowRev.status === 422)
    const extRev = await req(`/v1/passes/${ulid2}/extend`, { method: 'PATCH', token: resToken, body: { newExpiresAt: new Date(Date.now() + 7200000).toISOString() } })
    check('Passes', 'Blocked from extending Revoked pass (422)', extRev.status === 422)
  }

  // ── 6. Access Phrase Module ────────────────────────────────────────────
  console.log('\n--- 6. Access Phrase Module ---')
  const phraseGet = await req('/v1/access-phrase', { token: resToken })
  check('AccessPhrase', 'Resident retrieves daily access phrase (200)', phraseGet.ok && !!phraseGet.data?.accessPhrase?.phrase)

  const phraseRegen = await req('/v1/access-phrase/regenerate', { method: 'POST', token: resToken })
  const phraseObj = phraseRegen.data?.accessPhrase
  check('AccessPhrase', 'Resident regenerates daily access phrase (200)', phraseRegen.ok && !!phraseObj?.phrase)

  const unitsRes = await req('/v1/access-phrase/units', { token: secToken })
  check('AccessPhrase', 'Security retrieves units for access phrase lookup (200)', unitsRes.ok)

  if (phraseObj?.phrase && phraseObj?.unitId && est2SecToken) {
    const badPhrase = await req('/v1/access-phrase/verify', {
      method: 'POST',
      token: est2SecToken,
      body: { unitId: Number(phraseObj.unitId), phrase: 'WRONG-PHRASE-999', visitorName: 'QAT Guest' },
    })
    check('AccessPhrase', 'Wrong access phrase rejected (422)', badPhrase.status === 422, `status=${badPhrase.status}`)

    const goodPhrase = await req('/v1/access-phrase/verify', {
      method: 'POST',
      token: est2SecToken,
      body: { unitId: Number(phraseObj.unitId), phrase: phraseObj.phrase, visitorName: 'QAT Phrase Guest' },
    })
    const phrasePassId = goodPhrase.data?.pass?.id
    if (phrasePassId) cleanup.passes.push(phrasePassId)
    check('AccessPhrase', 'Valid access phrase admits visitor at gate', goodPhrase.ok, `status=${goodPhrase.status}`)
  }

  // ── 7. Homeowner Portfolio & Privacy ───────────────────────────────────
  console.log('\n--- 7. Homeowner Portfolio & Privacy ---')
  const ownerSummary = await req('/v1/home/summary', { token: ownerToken })
  check('Homeowner', 'Homeowner fetches verified deeds & tenant occupancy', ownerSummary.ok && Array.isArray(ownerSummary.data?.homeownerProperties))

  // ── 8. Emergency SOS Module ────────────────────────────────────────────
  console.log('\n--- 8. Emergency SOS Module ---')
  const sos = await req('/v1/emergencies', { method: 'POST', token: resToken, body: { type: 'Security Incident', notes: 'QAT automated SOS test' } })
  const sosId = sos.data?.incident?.id || sos.data?.emergency?.id || sos.data?.data?.id || sos.data?.id
  if (sosId) cleanup.emergencies.push(sosId)
  check('Emergencies', 'Resident triggers SOS (201)', sos.status === 201 && !!sosId, `id=${sosId}`)
  if (sosId) {
    const ack = await req(`/v1/admin/emergencies/${sosId}/acknowledge`, { method: 'PATCH', token: estToken })
    check('Emergencies', 'Admin acknowledges SOS (200)', ack.ok)
    const resolve = await req(`/v1/admin/emergencies/${sosId}/resolve`, { method: 'PATCH', token: estToken })
    check('Emergencies', 'Admin resolves SOS (200)', resolve.ok)
  }

  // ── 9. Artisans, News, Properties, Notifications, Billing ──────────────
  console.log('\n--- 9. Artisans, News, Properties, Notifications & Billing ---')
  const art = await req('/v1/admin/artisans', { method: 'POST', token: estToken, body: { name: 'QAT Plumber', trade: 'Plumber', category: 'Plumbing', phone: '08022233344', estate_id: 2 } })
  const artId = art.data?.artisan?.id || art.data?.data?.id || art.data?.id
  if (artId) cleanup.artisans.push(artId)
  check('Artisans', 'Admin creates Artisan (201)', art.status === 201 && !!artId)
  if (artId) {
    const rev = await req(`/v1/artisans/${artId}/reviews`, { method: 'POST', token: resToken, body: { rating: 5, comment: 'Fast QAT test' } })
    check('Artisans', 'Resident submits Artisan review (201)', rev.status === 201)
  }

  const news = await req('/v1/admin/news', { method: 'POST', token: estToken, body: { title: 'QAT Notice', content: 'Automated QAT notice', target_audience: 'all', estate_id: 2 } })
  const newsId = news.data?.news?.id || news.data?.data?.id || news.data?.id
  if (newsId) cleanup.news.push(newsId)
  check('News', 'Admin publishes announcement (201)', news.status === 201 && !!newsId)
  const newsFeed = await req('/v1/news', { token: resToken })
  check('News', 'Resident fetches estate announcements (200)', newsFeed.ok)

  const propStats = await req('/v1/admin/estates/2/properties/stats', { token: estToken })
  check('Properties', 'Estate property stats (200)', propStats.ok)
  const topo = await req('/v1/admin/estates/2/properties/topology-tree', { token: estToken })
  check('Properties', 'Estate topology tree (200)', topo.ok)

  const notifs = await req('/v1/notifications', { token: resToken })
  check('Notifications', 'Fetch resident notifications (200)', notifs.ok)

  const plans = await req('/v1/admin/billing/plans', { token: estToken })
  check('Billing', 'Fetch subscription plans (200)', plans.ok)
  const sub = await req('/v1/admin/billing/subscription', { token: estToken })
  check('Billing', 'Fetch estate subscription status (200)', sub.ok)

  // ── 10. Play Store Readiness Config & In-App Account Deletion Check ────
  console.log('\n--- 10. Google Play Store Config & Account Deletion Check ---')
  const envProdPath = path.join(ROOT_DIR, 'checkpass-app', '.env.production')
  const envProd = fs.existsSync(envProdPath) ? fs.readFileSync(envProdPath, 'utf8') : ''
  check('PlayStore', '.env.production uses live https://api.checkpass.ng/api URL', envProd.includes('https://api.checkpass.ng/api'))

  const homeSrc = fs.readFileSync(path.join(ROOT_DIR, 'checkpass-app', 'src', 'views', 'HomeView.vue'), 'utf8')
  check('PlayStore', 'HomeView.vue has no hardcoded http://localhost:3100', !homeSrc.includes('http://localhost:3100'))

  const manifestSrc = fs.readFileSync(path.join(ROOT_DIR, 'checkpass-app', 'android', 'app', 'src', 'main', 'AndroidManifest.xml'), 'utf8')
  check('PlayStore', 'AndroidManifest declares CAMERA and POST_NOTIFICATIONS', manifestSrc.includes('android.permission.CAMERA') && manifestSrc.includes('android.permission.POST_NOTIFICATIONS'))

  // Test DELETE /v1/profile on a newly created transient resident user
  const tempEmail = `qat.delete.${Date.now()}@checkpass.local`
  const createRes = await req('/v1/admin/residents', {
    method: 'POST',
    token: estToken,
    body: {
      estate_id: 2,
      unit_id: 21,
      name: 'QAT Delete User',
      email: tempEmail,
      phone: `0809${String(Date.now()).slice(-7)}`,
      password: 'Password123!',
      role: 'tenant',
    },
  })
  const createdResidentId = createRes.data?.resident?.id || createRes.data?.data?.id
  if (createdResidentId) {
    const tempLogin = await req('/v1/auth/login', { method: 'POST', body: { email: tempEmail, password: 'Password123!' } })
    const tempToken = tempLogin.data?.token
    if (tempToken) {
      const delAcc = await req('/v1/profile', { method: 'DELETE', token: tempToken })
      check('PlayStore', 'In-app DELETE /v1/profile deletes account & revokes token (200)', delAcc.ok)
      const afterDel = await req('/v1/auth/me', { token: tempToken })
      check('PlayStore', 'Deleted user token rejected with 401', afterDel.status === 401, `status=${afterDel.status}`)
    } else {
      check('PlayStore', 'Transient delete-test user login', false, `login status=${tempLogin.status}`)
    }
    await req(`/v1/admin/residents/${createdResidentId}`, { method: 'DELETE', token: estToken })
  } else {
    check('PlayStore', 'Create transient user for DELETE /v1/profile test', false, `status=${createRes.status}, body=${JSON.stringify(createRes.data)}`)
  }

  // ── Post-Test Cleanup ──────────────────────────────────────────────────
  console.log('\n--- Post-Test Cleanup ---')
  for (const id of cleanup.leads) await req(`/v1/crm/leads/${id}`, { method: 'DELETE', token: superToken })
  for (const id of cleanup.passes) await req(`/v1/admin/passes/${id}`, { method: 'DELETE', token: estToken })
  for (const id of cleanup.emergencies) await req(`/v1/admin/emergencies/${id}`, { method: 'DELETE', token: estToken })
  for (const id of cleanup.artisans) await req(`/v1/admin/artisans/${id}`, { method: 'DELETE', token: estToken })
  for (const id of cleanup.news) await req(`/v1/admin/news/${id}`, { method: 'DELETE', token: estToken })
  for (const id of cleanup.users) await req(`/v1/admin/users/${id}`, { method: 'DELETE', token: estToken })

  console.log(`\n======================================================================`)
  console.log(` FINAL QAT RESULT: ${passed} PASSED | ${failed} FAILED (Total ${passed + failed})`)
  console.log(`======================================================================`)
  process.exit(failed > 0 ? 1 : 0)
}

main().catch(err => {
  console.error('Fatal QAT runner error:', err)
  process.exit(1)
})
