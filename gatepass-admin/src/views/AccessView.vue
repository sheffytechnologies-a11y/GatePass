<template>
  <div class="mobile-page">
    <section class="hero-card" :class="auth.isSecurity ? 'hero-card--security' : ''">
      <div class="hero-eyebrow">{{ auth.isSecurity ? 'Gate mode' : 'Access control' }}</div>
      <h1 class="hero-title">{{ auth.isSecurity ? 'Scan passes and clear visitors for entry.' : 'Track active and flagged visitor passes.' }}</h1>
      <p class="hero-copy">
        {{ auth.isSecurity
          ? 'Security users can only inspect passes and approve visitors at the gate.'
          : 'Admins can monitor the gate feed, inspect flagged activity, and jump into pass records fast.' }}
      </p>
      <button class="btn btn-primary hero-button" @click="scannerOpen = true">Scan QR Pass</button>
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

    <section v-if="auth.isAdmin" class="card section-card search-card">
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
          <h2 class="section-title">{{ auth.isSecurity ? "Today's passes" : 'Access feed' }}</h2>
          <p class="section-copy">{{ auth.isSecurity ? 'Recent visitor passes from the security summary.' : 'Active pass records across the estate.' }}</p>
        </div>
        <button class="btn btn-outline btn-sm" @click="refresh">Refresh</button>
      </div>

      <div v-if="loading" class="empty-stack"><div class="spinner"></div></div>
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
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { homeApi, passesApi } from '@/api'
import QrScannerSheet from '@/components/QrScannerSheet.vue'
import { useAuthStore } from '@/stores/auth'
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

const auth = useAuthStore()
const router = useRouter()
const { showToast } = useToast()

const scannerOpen = ref(false)
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

function mapSecurityPass(pass: any): FeedPass {
  return {
    id: pass.id,
    visitorName: pass.visitorName,
    visitorPhone: pass.visitorPhone || null,
    purpose: pass.purpose || 'General access',
    status: pass.status,
    hostName: pass.hostName || 'Unknown resident',
    hostUnit: pass.hostUnit || 'No unit',
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

async function loadSecurityFeed() {
  loading.value = true
  try {
    const res = await homeApi.getSummary()
    passes.value = (res.data.recentPasses || []).map(mapSecurityPass)
  } catch {
    showToast('Could not load gate summary.', 'error')
  } finally {
    loading.value = false
  }
}

async function refresh() {
  if (auth.isSecurity) await loadSecurityFeed()
  else await loadAdminPasses()
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

onMounted(refresh)
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
.hero-card--security { background: linear-gradient(160deg, #17302e 0%, #0b5f56 60%, #5fc3ab 100%); }
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
</style>