<template>
  <div class="mobile-page">
    <section class="hero-card">
      <div class="hero-eyebrow">Access control</div>
      <h1 class="hero-title">Track active and flagged visitor passes.</h1>
      <p class="hero-copy">
        Monitor the gate feed, inspect flagged activity, and jump into pass records fast.
      </p>
      <div class="guard-actions-row">
        <button class="btn btn-primary hero-button" @click="scannerOpen = true">Scan QR Pass</button>
        <button type="button" class="guard-compliance-pill" @click="guardCodeModalOpen = true">
          <span class="pill-dot"></span>
          {{ guardCodeAcknowledged ? "Gate Protocol: Verified (NDPA/GAID 2025)" : "Gate Protocol: Pending Acknowledgment" }}
        </button>
      </div>
    </section>

    <section class="stats-grid">
      <article class="stat-card card">
        <div class="stat-value">{{ stats.pending }}</div>
        <div class="stat-label">Pending</div>
      </article>
      <article class="stat-card card">
        <div class="stat-value">{{ stats.onSite }}</div>
        <div class="stat-label">On-site</div>
      </article>
      <article class="stat-card card">
        <div class="stat-value">{{ stats.flagged }}</div>
        <div class="stat-label">Flagged</div>
      </article>
    </section>

    <section class="card section-card search-card">
      <input v-model="search" class="form-input" type="search" placeholder="Search visitor name" @keyup.enter="loadAdminPasses" />
      <div class="chip-row">
        <button v-for="filter in filters" :key="filter.value" class="chip" :class="{ active: statusFilter === filter.value }" @click="setFilter(filter.value)">
          {{ filter.label }}
        </button>
      </div>
    </section>

    <section class="section-card card">
      <div class="section-head">
        <div>
          <h2 class="section-title">Access feed</h2>
          <p class="section-copy">Active pass records across the estate.</p>
        </div>
        <button class="btn btn-outline btn-sm" @click="refresh">Refresh</button>
      </div>

      <div v-if="loading" class="empty-stack"><BrandSpinner :size="40" color="var(--c-primary)" /></div>
      <div v-else-if="passes.length === 0" class="empty-stack">No passes found.</div>
      <div v-else class="pass-stack">
        <article v-for="pass in passes" :key="pass.id" class="pass-card card" @click="openPass(pass.id)">
          <div class="pass-row">
            <div>
              <div class="pass-name">{{ pass.visitorName }}</div>
              <div class="pass-meta">{{ pass.hostName }} • {{ pass.hostUnit }}</div>
            </div>
            <span class="badge" :class="statusBadge(pass.status)">{{ pass.status }}</span>
          </div>
          <div class="pass-row pass-row--sub">
            <span>{{ pass.purpose || 'General access' }}</span>
            <span>{{ formatTime(pass.createdAt) }}</span>
          </div>
          <div v-if="pass.itemsFlagged" class="flag-note">Flagged items declared on this pass.</div>
        </article>
      </div>
    </section>

    <QrScannerSheet :open="scannerOpen" @close="scannerOpen = false" @scanned="handleScanned" />
  </div>

    <!-- Guard Code of Conduct & Confidentiality Modal -->
    <div v-if="guardCodeModalOpen" class="guard-modal-backdrop" @click.self="guardCodeAcknowledged ? guardCodeModalOpen = false : null">
      <div class="guard-modal-card">
        <div class="guard-modal-header">
          <div class="guard-brand-lockup">
            <span class="guard-shield-icon">🛡️</span>
            <div>
              <h3 class="guard-modal-title">Gate Security Code of Conduct &amp; Privacy Agreement</h3>
              <p class="guard-modal-sub">Compliant with Nigeria Data Protection Act 2023 &amp; NDPC GAID 2025</p>
            </div>
          </div>
          <button v-if="guardCodeAcknowledged" type="button" class="guard-modal-close" @click="guardCodeModalOpen = false">&times;</button>
        </div>

        <div class="guard-modal-body">
          <div class="guard-notice-box">
            <strong>Official Notice to Duty Security Officers:</strong>
            As a gate security operator on CheckPass, you handle personal and vehicle data of residents and visitors. You are legally required to uphold strict confidentiality and operational discipline under Nigerian data protection laws.
          </div>

          <div class="guard-rules-stack">
            <div class="guard-rule-item">
              <span class="rule-num">1</span>
              <div>
                <strong>Zero Personal Device Recording:</strong>
                <p>Never take photos, video, or screenshots of resident names, phone numbers, vehicle license plates, or gate records using your personal mobile phone. All on-screen data is strictly confidential.</p>
              </div>
            </div>

            <div class="guard-rule-item">
              <span class="rule-num">2</span>
              <div>
                <strong>Zero Cash Bribery &amp; Illegal Gate Tolls:</strong>
                <p>Soliciting, collecting, or accepting cash tips, gate entry bribes, or unauthorized tolls from visitors, delivery couriers, or artisans is strictly prohibited and subject to immediate dismissal and legal action.</p>
              </div>
            </div>

            <div class="guard-rule-item">
              <span class="rule-num">3</span>
              <div>
                <strong>100% Digital Pass Verification:</strong>
                <p>Every vehicle, pedestrian, or artisan must present a verified 6-character pass code or digital QR pass. Never wave unknown persons in based on verbal claims without scanning or validating in the system.</p>
              </div>
            </div>

            <div class="guard-rule-item">
              <span class="rule-num">4</span>
              <div>
                <strong>Move-Out Asset Clearance:</strong>
                <p>For moving trucks, vans, or departing occupants, verify all declared appliances, furniture, and machinery item-by-item against the digital move-out manifest before lifting the exit barrier.</p>
              </div>
            </div>

            <div class="guard-rule-item">
              <span class="rule-num">5</span>
              <div>
                <strong>Emergency Calmness &amp; SOS Dispatch:</strong>
                <p>In case of resident panic SOS alerts or perimeter disputes, maintain calm, press the emergency dispatch button on the terminal, and notify the Chief Security Officer immediately.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="guard-modal-footer">
          <button type="button" class="btn-accept-guard-code" @click="acceptGuardCode">
            I Acknowledge &amp; Accept Gate Security Protocol
          </button>
        </div>
      </div>
    </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { passesApi } from '@/api'
import QrScannerSheet from '@/components/QrScannerSheet.vue'
import { useToast } from '@/composables/useToast'

type FeedPass = {
  id: string
  visitorName: string
  visitorPhone: string | null
  purpose: string
  status: string
  hostName: string
  hostUnit: string
  createdAt: string | null
  itemsFlagged: boolean
}

const router = useRouter()
const { showToast } = useToast()

const scannerOpen = ref(false)
const guardCodeModalOpen = ref(false)
const guardCodeAcknowledged = ref(false)

function checkGuardCompliance() {
  if (typeof window !== "undefined") {
    const ack = localStorage.getItem("checkpass_guard_code_acknowledged_at")
    guardCodeAcknowledged.value = !!ack
    if (!ack) {
      guardCodeModalOpen.value = true
    }
  }
}

function acceptGuardCode() {
  if (typeof window !== "undefined") {
    localStorage.setItem("checkpass_guard_code_acknowledged_at", new Date().toISOString())
    guardCodeAcknowledged.value = true
    guardCodeModalOpen.value = false
  }
}
const loading = ref(true)
const search = ref('')
const statusFilter = ref('all')
const passes = ref<FeedPass[]>([])

const filters = [
  { label: 'All', value: 'all' },
  { label: 'Pending', value: 'Pending' },
  { label: 'On-site', value: 'On-site' },
  { label: 'Revoked', value: 'Revoked' },
]

const stats = computed(() => ({
  pending: passes.value.filter((pass) => pass.status === 'Pending').length,
  onSite: passes.value.filter((pass) => pass.status === 'On-site').length,
  flagged: passes.value.filter((pass) => pass.itemsFlagged).length,
}))

function mapAdminPass(pass: any): FeedPass {
  return {
    id: pass.ulid,
    visitorName: pass.visitorName,
    visitorPhone: pass.visitorPhone || null,
    purpose: pass.purpose || 'General access',
    status: pass.status,
    hostName: pass.resident?.name || 'Unknown resident',
    hostUnit: pass.resident?.unit || 'No unit',
    createdAt: pass.createdAt,
    itemsFlagged: !!pass.itemsFlagged,
  }
}

async function loadAdminPasses() {
  loading.value = true
  try {
    const res = await passesApi.getAll({
      search: search.value || undefined,
      status: statusFilter.value === 'all' ? undefined : statusFilter.value,
    })
    passes.value = (res.data.passes || []).map(mapAdminPass)
  } catch {
    showToast('Could not load access feed.', 'error')
  } finally {
    loading.value = false
  }
}

async function refresh() {
  await loadAdminPasses()
}

async function setFilter(value: string) {
  statusFilter.value = value
  await loadAdminPasses()
}

function openPass(id: string) {
  router.push(`/passes/${id}`)
}

function isLikelyPassId(value: string) {
  return /^[0-9A-HJKMNP-TV-Z]{26}$/i.test(value)
}

function normalizePhone(value: string) {
  return value.replace(/\D+/g, '')
}

function phoneTail(value: string) {
  const normalized = normalizePhone(value)
  return normalized.length > 10 ? normalized.slice(-10) : normalized
}

async function handleScanned(value: string) {
  const trimmed = value.trim()
  if (!trimmed) return

  if (isLikelyPassId(trimmed)) {
    openPass(trimmed)
    return
  }

  try {
    const res = await passesApi.findByPhone(trimmed)
    const passId = String(res?.data?.pass?.id || '').trim()
    if (passId) {
      openPass(passId)
      return
    }
  } catch {
    // Fallback to local list matching if API lookup fails.
  }

  const scannedPhoneTail = phoneTail(trimmed)
  const passByPhone = scannedPhoneTail
    ? passes.value.find((pass) => {
      if (!pass.visitorPhone) return false
      return phoneTail(pass.visitorPhone) === scannedPhoneTail
    })
    : null

  if (passByPhone) {
    openPass(passByPhone.id)
    return
  }

  showToast('No pass found for that phone number.', 'error')
}

function statusBadge(status: string) {
  if (status === 'On-site') return 'badge-green'
  if (status === 'Pending') return 'badge-blue'
  if (status === 'Revoked') return 'badge-red'
  return 'badge-gray'
}

function formatTime(value: string | null) {
  if (!value) return '—'
  return new Date(value).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(() => {
  refresh()
  checkGuardCompliance()
})
</script>

<style scoped>
.mobile-page { display: flex; flex-direction: column; gap: 16px; }
.hero-card {
  background: linear-gradient(160deg, #0a5c38 0%, #0f7d50 70%, #66c596 100%);
  color: white;
  padding: 20px;
  border-radius: 28px;
  box-shadow: var(--shadow-md);
}
.hero-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: rgba(255,255,255,0.72); }
.hero-title { margin-top: 10px; font-size: 26px; line-height: 1.05; font-family: var(--font-display); }
.hero-copy { margin-top: 8px; color: rgba(255,255,255,0.78); font-size: 14px; }
.hero-button { margin-top: 16px; background: white; color: var(--c-primary); justify-content: center; }
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.stat-card { padding: 16px 14px; border-radius: 20px; }
.stat-value { font-size: 24px; font-weight: 800; color: var(--c-primary); }
.stat-label { margin-top: 4px; font-size: 12px; color: var(--c-muted); }
.section-card { padding: 16px; }
.search-card { gap: 12px; display: flex; flex-direction: column; }
.chip-row { display: flex; gap: 8px; overflow-x: auto; }
.chip {
  border: none;
  border-radius: 999px;
  padding: 10px 14px;
  white-space: nowrap;
  background: #edf2ee;
  color: #506157;
  font-weight: 700;
}
.chip.active { background: var(--c-primary); color: white; }
.section-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 14px; }
.section-title { font-size: 18px; font-weight: 700; }
.section-copy { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.empty-stack { min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--c-muted); }
.pass-stack { display: flex; flex-direction: column; gap: 10px; }
.pass-card { padding: 16px; border-radius: 20px; cursor: pointer; }
.pass-row { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
.pass-row--sub { margin-top: 10px; color: var(--c-muted); font-size: 13px; }
.pass-name { font-size: 16px; font-weight: 700; color: var(--c-text); }
.pass-meta { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.flag-note { margin-top: 12px; color: var(--c-warning); font-size: 12px; font-weight: 700; }
@media (max-width: 420px) {
  .stats-grid { grid-template-columns: 1fr; }
}

.guard-actions-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  margin-top: 14px;
}
.guard-compliance-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(10, 92, 56, 0.08);
  border: 1px solid rgba(10, 92, 56, 0.2);
  color: #0A5C38;
  font-size: 11.5px;
  font-weight: 700;
  padding: 6px 14px;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.guard-compliance-pill:hover {
  background: rgba(10, 92, 56, 0.15);
}
.pill-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #22C55E;
  display: inline-block;
}
.guard-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(7, 31, 20, 0.82);
  backdrop-filter: blur(8px);
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.guard-modal-card {
  background: #FFFFFF;
  border-radius: 18px;
  width: 100%;
  max-width: 660px;
  max-height: 88vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 24px 60px rgba(0,0,0,0.35);
}
.guard-modal-header {
  padding: 18px 22px;
  background: #F8FAFC;
  border-bottom: 1px solid #E2E8F0;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.guard-brand-lockup {
  display: flex;
  align-items: center;
  gap: 12px;
}
.guard-shield-icon {
  font-size: 24px;
}
.guard-modal-title {
  font-size: 14.5px;
  font-weight: 800;
  color: #071F14;
  margin: 0;
}
.guard-modal-sub {
  font-size: 11px;
  color: #0A5C38;
  font-weight: 600;
  margin: 2px 0 0;
}
.guard-modal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #64748B;
  cursor: pointer;
}
.guard-modal-body {
  padding: 22px;
  overflow-y: auto;
  font-size: 13px;
  line-height: 1.6;
  color: #334155;
}
.guard-notice-box {
  background: #EFF6FF;
  border-left: 4px solid #2563EB;
  padding: 10px 14px;
  border-radius: 6px;
  font-size: 12px;
  color: #1E40AF;
  margin-bottom: 18px;
}
.guard-rules-stack {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.guard-rule-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}
.rule-num {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #0A5C38;
  color: #fff;
  font-size: 12px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}
.guard-rule-item strong {
  color: #071F14;
  font-size: 13px;
}
.guard-rule-item p {
  margin: 2px 0 0;
  color: #475569;
  font-size: 12px;
  line-height: 1.5;
}
.guard-modal-footer {
  padding: 14px 22px;
  background: #F8FAFC;
  border-top: 1px solid #E2E8F0;
  display: flex;
  justify-content: flex-end;
}
.btn-accept-guard-code {
  background: #0A5C38;
  color: #FFFFFF;
  border: none;
  padding: 10px 22px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  transition: background 0.15s ease;
}
.btn-accept-guard-code:hover {
  background: #07472A;
}
</style>