<template>
  <div class="mobile-page">
    <section class="hero-card">
      <div class="hero-eyebrow">Access control</div>
      <h1 class="hero-title">Track active and flagged visitor passes.</h1>
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

    <section class="access-section">
      <div class="section-head">
        <div>
          <h2 class="section-title">Access feed</h2>
          <p class="section-copy">Active pass records across the estate.</p>
        </div>
        <button class="btn btn-outline btn-sm" @click="refresh">Refresh</button>
      </div>

      <div v-if="loading" class="empty-stack"><BrandSpinner :size="40" color="var(--c-primary)" /></div>
      <div v-else-if="passes.length === 0" class="empty-stack">No passes found.</div>
      <div v-else class="pass-grid">
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
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { passesApi } from '@/api'
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

const route = useRoute()

onMounted(() => {
  if (route.query.status) {
    const s = String(route.query.status)
    if (s.toLowerCase() === 'on-site' || s === 'On-site') {
      statusFilter.value = 'On-site'
    } else if (s.toLowerCase() === 'pending' || s === 'Pending') {
      statusFilter.value = 'Pending'
    } else if (s.toLowerCase() === 'revoked' || s === 'Revoked') {
      statusFilter.value = 'Revoked'
    }
  }
  refresh()
})
</script>

<style scoped>
.mobile-page { display: flex; flex-direction: column; gap: 16px; }
.hero-card {
  background: linear-gradient(160deg, #0a5c38 0%, #0f7d50 70%, #66c596 100%);
  color: white;
  padding: 22px 28px;
  border-radius: 20px;
  box-shadow: 0 4px 18px rgba(10, 92, 56, 0.18);
}
.hero-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: rgba(255,255,255,0.72); font-weight: 700; }
.hero-title { margin-top: 6px; font-size: 24px; line-height: 1.15; font-family: var(--font-display); font-weight: 800; color: #ffffff; }

.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.stat-card { padding: 18px 20px; border-radius: 16px; }
.stat-value { font-size: 26px; font-weight: 800; color: var(--c-primary); }
.stat-label { margin-top: 4px; font-size: 13px; color: var(--c-muted); font-weight: 600; }

.section-card { padding: 16px 20px; border-radius: 16px; }
.search-card { gap: 12px; display: flex; flex-direction: column; }
.chip-row { display: flex; gap: 8px; overflow-x: auto; }
.chip {
  border: none;
  border-radius: var(--btn-radius-pill, 999px);
  padding: 8px 16px;
  white-space: nowrap;
  background: #edf2ee;
  color: #506157;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
}
.chip.active { background: var(--c-primary); color: white; }

.access-section { margin-top: 4px; }
.section-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 14px; }
.section-title { font-size: 18px; font-weight: 800; color: #111827; }
.section-copy { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.empty-stack { min-height: 140px; display: flex; align-items: center; justify-content: center; color: var(--c-muted); }

/* At most 2 cards per row on desktop, 1 card on mobile */
.pass-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}

.pass-card {
  padding: 18px 20px;
  border-radius: 16px;
  cursor: pointer;
  transition: all 160ms cubic-bezier(0.16, 1, 0.3, 1);
  border: 1px solid #E5E7EB;
  background: #ffffff;
}

.pass-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
  border-color: #D1D5DB;
}

.pass-row { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
.pass-row--sub { margin-top: 10px; color: var(--c-muted); font-size: 12.5px; }
.pass-name { font-size: 15.5px; font-weight: 700; color: #111827; }
.pass-meta { margin-top: 4px; color: #4B5563; font-size: 13px; font-weight: 500; }
.flag-note { margin-top: 10px; color: var(--c-warning, #D97706); font-size: 12px; font-weight: 700; }

@media (max-width: 860px) {
  .pass-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .stats-grid { grid-template-columns: 1fr; }
}
</style>