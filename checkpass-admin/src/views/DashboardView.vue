<template>
  <div class="dashboard-page">
    <section class="hero-card">
      <div class="hero-top-row">
        <div class="hero-title-group">
          <div class="hero-badge-row">
            <span class="hero-eyebrow">Estate Operational Center</span>
            <span class="gate-status-pill">
              <span class="status-dot"></span>
              Gate Active &bull; Shift Live
            </span>
            <div class="telemetry-sync-pill" :title="`Auto-refreshes every 30s. ${syncStatusLabel}`">
              <span class="telemetry-dot" :class="{ 'telemetry-dot--syncing': isSyncing }"></span>
              <span class="telemetry-label">{{ syncStatusLabel }}</span>
              <button
                type="button"
                class="btn-telemetry-sync"
                :disabled="isSyncing"
                @click="manualSync"
                title="Sync live gate telemetry now"
              >
                <svg :class="{ 'spin-anim': isSyncing }" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                </svg>
                <span>Sync</span>
              </button>
            </div>
          </div>
          <h1 class="hero-title">Estate Command & Security Overview</h1>
          <p class="hero-copy">Monitor real-time visitor gate access, resident activity, and emergency dispatch across your community.</p>
        </div>
        <div class="hero-actions-group">
          <router-link to="/passes" class="btn btn-hero-action">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="16" rx="2"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="12" y1="11" x2="12" y2="15"/>
              <line x1="10" y1="13" x2="14" y2="13"/>
            </svg>
            Issue Pass
          </router-link>
          <router-link to="/properties" class="btn btn-hero-action">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Properties
          </router-link>
          <router-link to="/news" class="btn btn-hero-action btn-hero-action--subtle">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            Broadcast
          </router-link>
        </div>
      </div>
      <div class="hero-switcher-row">
        <EstateSwitcher variant="hero" />
      </div>
    </section>

    <!-- Prominent Setup Banner if no estate yet -->
    <div v-if="!loading && estates.length === 0" class="estate-setup-banner">
      <div class="setup-banner-left">
        <span class="setup-badge">⚡ Action required</span>
        <h2>Add your estate to get started</h2>
        <p>You haven't registered an estate yet. Add your community now to start issuing visitor passes, onboarding residents, and managing gate access.</p>
      </div>
      <button class="btn btn-primary btn-add-estate" @click="showAddEstateModal = true">
        + Add Estate Now
      </button>
    </div>

    <div v-if="loading" class="loading-state">
      <BrandSpinner :size="40" color="var(--c-primary)" />
    </div>

    <template v-else>
      <!-- Operational KPI Stat Cards with SVG Icons -->
      <div class="stats-grid">
        <router-link to="/users" class="stat-card">
          <div class="stat-icon stat-icon--blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.totalUsers }}</div>
            <div class="stat-label">Total Users</div>
          </div>
        </router-link>

        <router-link to="/residents" class="stat-card">
          <div class="stat-icon stat-icon--green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.totalResidents }}</div>
            <div class="stat-label">Residents</div>
          </div>
        </router-link>

        <router-link to="/passes?status=Pending" class="stat-card">
          <div class="stat-icon stat-icon--teal">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="5" width="20" height="14" rx="3"/>
              <line x1="2" y1="10" x2="22" y2="10"/>
              <line x1="7" y1="15" x2="7.01" y2="15"/>
              <line x1="11" y1="15" x2="13" y2="15"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.activePasses }}</div>
            <div class="stat-label">Active Passes</div>
          </div>
        </router-link>

        <router-link to="/access?status=On-site" class="stat-card">
          <div class="stat-icon stat-icon--teal">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.onSite }}</div>
            <div class="stat-label">On-Site Now</div>
          </div>
        </router-link>

        <router-link to="/emergencies" class="stat-card" :class="{ 'stat-card--alert': stats.todayEmergencies > 0 }">
          <div class="stat-icon stat-icon--red">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value" :class="{ 'text-danger': stats.todayEmergencies > 0 }">{{ stats.todayEmergencies }}</div>
            <div class="stat-label">Emergencies Today</div>
          </div>
        </router-link>

        <router-link to="/access?flagged=1" class="stat-card" :class="{ 'stat-card--warning': stats.flaggedItems > 0 }">
          <div class="stat-icon stat-icon--yellow">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
              <line x1="4" y1="22" x2="4" y2="15"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.flaggedItems }}</div>
            <div class="stat-label">Flagged Item Passes</div>
          </div>
        </router-link>
      </div>

      <!-- Real-time Operational Panels (Recent Passes & Emergencies) directly visible without redundant quick-grid -->
      <div class="dashboard-panels">
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Recent Passes</h2>
            <router-link to="/access" class="card-link">Open access →</router-link>
          </div>
          <div v-if="recentPasses.length === 0" class="empty-state">
            <p>No passes yet.</p>
          </div>
          <div v-else class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Visitor</th>
                  <th>Resident</th>
                  <th>Unit</th>
                  <th>Status</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="pass in recentPasses" :key="pass.ulid">
                  <td>
                    <div class="visitor-meta">
                      <router-link :to="`/passes/${pass.ulid}`" class="link visitor-name">
                        {{ pass.visitorName }}
                      </router-link>
                      <!-- Subordinate vehicle & category line: never prioritized over visitor identity -->
                      <div v-if="pass.vehiclePlate || (pass.type && pass.type.toLowerCase() !== 'standard' && pass.type.toLowerCase() !== 'one-time')" class="visitor-subline">
                        <span v-if="pass.vehiclePlate" class="vehicle-subtext" title="Vehicle plate number">
                          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                            <line x1="2" y1="10" x2="22" y2="10"/>
                          </svg>
                          {{ pass.vehiclePlate }}
                        </span>
                        <span v-if="pass.type && pass.type.toLowerCase() !== 'standard' && pass.type.toLowerCase() !== 'one-time'" class="visitor-type-tag">
                          {{ pass.type }}
                        </span>
                      </div>
                    </div>
                  </td>
                  <td>{{ pass.resident?.name ?? '—' }}</td>
                  <td>{{ pass.resident?.unit ?? '—' }}</td>
                  <td>
                    <div class="status-cell">
                      <span :class="passStatusClass(pass.status)" class="badge">{{ pass.status }}</span>
                      <span v-if="pass.status === 'On-site' && pass.arrivedAt" class="dwell-time-hint" title="Time spent on estate">
                        {{ calcDwellTime(pass.arrivedAt) }}
                      </span>
                    </div>
                  </td>
                  <td>{{ fmtDate(pass.createdAt) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Recent Emergencies</h2>
            <router-link to="/emergencies" class="card-link">View all →</router-link>
          </div>
          <div v-if="recentEmergencies.length === 0" class="empty-state">
            <p>No emergencies recorded.</p>
          </div>
          <div v-else class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Resident</th>
                  <th>Estate</th>
                  <th>Status</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="e in recentEmergencies" :key="e.id">
                  <td>{{ e.type }}</td>
                  <td>{{ e.resident?.name ?? '—' }}</td>
                  <td>{{ e.estate?.name ?? '—' }}</td>
                  <td>
                    <span :class="emergencyStatusClass(e.status)" class="badge">{{ e.status }}</span>
                  </td>
                  <td>{{ fmtDate(e.createdAt) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>

    <!-- Add Estate Modal -->
    <div v-if="showAddEstateModal" class="modal-overlay" @click.self="showAddEstateModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">Add Estate</h2>
            <p class="modal-sub">Register your residential community or facility.</p>
          </div>
          <button class="modal-close" @click="showAddEstateModal = false">&times;</button>
        </div>

        <form @submit.prevent="handleCreateEstate" class="modal-form">
          <div class="form-group">
            <label class="form-label">Estate Name *</label>
            <input v-model="newEstate.name" type="text" class="form-input" placeholder="e.g. Lekki Gardens Phase 4" required />
          </div>

          <div class="form-group">
            <label class="form-label">Street Address</label>
            <input v-model="newEstate.address" type="text" class="form-input" placeholder="e.g. 15 Admiralty Way" />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">City</label>
              <input v-model="newEstate.city" type="text" class="form-input" placeholder="e.g. Lekki / Ikeja" />
            </div>
            <div class="form-group">
              <label class="form-label">State (Nigeria)</label>
              <StateSelect v-model="newEstate.state" placeholder="Select or type state" />
            </div>
          </div>

          <div v-if="estateError" class="modal-error">{{ estateError }}</div>

          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="showAddEstateModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="creatingEstate">
              <BrandSpinner v-if="creatingEstate" :size="18" color="#ffffff" />
              <span v-else>Save Estate &rarr;</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { dashboardApi, estatesApi, onboardingApi } from '@/api/index'
import { useToast } from '@/composables/useToast'
import StateSelect from '@/components/StateSelect.vue'
import EstateSwitcher from '@/components/EstateSwitcher.vue'

const { showToast } = useToast()

const loading = ref(true)
const isSyncing = ref(false)
const secondsSinceSync = ref(0)
let syncInterval: ReturnType<typeof setInterval> | null = null
let tickerInterval: ReturnType<typeof setInterval> | null = null

const estates = ref<any[]>([])
const showAddEstateModal = ref(false)
const creatingEstate = ref(false)
const estateError = ref('')
const newEstate = ref({
  name: '',
  address: '',
  city: '',
  state: '',
})

const stats = ref({
  totalUsers: 0,
  totalResidents: 0,
  activePasses: 0,
  onSite: 0,
  todayEmergencies: 0,
  flaggedItems: 0,
})
const recentPasses = ref<any[]>([])
const recentEmergencies = ref<any[]>([])

const syncStatusLabel = computed(() => {
  if (isSyncing.value) return 'Syncing…'
  if (secondsSinceSync.value < 5) return 'Live • Just now'
  if (secondsSinceSync.value < 60) return `Live • ${secondsSinceSync.value}s ago`
  const mins = Math.floor(secondsSinceSync.value / 60)
  return `Synced ${mins}m ago`
})

async function load(isBackground = false) {
  if (isBackground) {
    isSyncing.value = true
  } else {
    loading.value = true
  }
  try {
    const [summaryRes, estatesRes] = await Promise.all([
      dashboardApi.getSummary(),
      estatesApi.getAll().catch(() => ({ data: { estates: [] } }))
    ])
    stats.value = summaryRes.data.stats
    recentPasses.value = summaryRes.data.recentPasses
    recentEmergencies.value = summaryRes.data.recentEmergencies
    estates.value = estatesRes.data?.estates ?? []
    secondsSinceSync.value = 0
  } catch {
    if (!isBackground) {
      showToast('Failed to load dashboard data.', 'error')
    }
  } finally {
    loading.value = false
    isSyncing.value = false
  }
}

async function manualSync() {
  await load(true)
  showToast('Live telemetry synchronized', 'info')
}

function handleVisibilityChange() {
  if (!document.hidden && secondsSinceSync.value >= 30) {
    load(true)
  }
}

function calcDwellTime(iso: string): string {
  const diffMs = Date.now() - new Date(iso).getTime()
  if (diffMs < 0) return 'Just entered'
  const mins = Math.floor(diffMs / 60000)
  if (mins < 1) return '< 1m'
  if (mins < 60) return `${mins}m`
  const hrs = Math.floor(mins / 60)
  const remMins = mins % 60
  return `${hrs}h ${remMins}m`
}

async function handleCreateEstate() {
  estateError.value = ''
  creatingEstate.value = true
  try {
    await onboardingApi.createEstate({
      name: newEstate.value.name,
      address: newEstate.value.address || undefined,
      city: newEstate.value.city || undefined,
      state: newEstate.value.state || undefined,
    })
    showToast(`Estate "${newEstate.value.name}" added successfully!`, 'success')
    showAddEstateModal.value = false
    newEstate.value = { name: '', address: '', city: '', state: '' }
    await load(false)
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    estateError.value = err.response?.data?.message ?? 'Could not create estate. Please try again.'
  } finally {
    creatingEstate.value = false
  }
}

function passStatusClass(status: string) {
  return {
    'badge-green':  status === 'On-site',
    'badge-blue':   status === 'Pending',
    'badge-gray':   status === 'Expired' || status === 'Exited',
    'badge-red':    status === 'Revoked',
    'badge-yellow': status === 'Item Flagged',
  }
}

function emergencyStatusClass(status: string) {
  return {
    'badge-red':    status === 'sent',
    'badge-yellow': status === 'acknowledged',
    'badge-green':  status === 'resolved',
  }
}

function fmtDate(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(() => {
  load(false)
  window.addEventListener('checkpass:estate-switched', () => load(false))
  document.addEventListener('visibilitychange', handleVisibilityChange)

  // 1-second ticker for relative time counter
  tickerInterval = setInterval(() => {
    secondsSinceSync.value++
  }, 1000)

  // 30-second background auto-refresh
  syncInterval = setInterval(() => {
    if (!document.hidden) {
      load(true)
    }
  }, 30000)
})

onUnmounted(() => {
  window.removeEventListener('checkpass:estate-switched', () => load(false))
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  if (tickerInterval) clearInterval(tickerInterval)
  if (syncInterval) clearInterval(syncInterval)
})
</script>

<style scoped>
.dashboard-page { display: flex; flex-direction: column; gap: 18px; }
.hero-card {
  background: radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 38%), linear-gradient(150deg, #102a21 0%, #0a5c38 60%, #78cca2 100%);
  color: white;
  border-radius: 28px;
  padding: 26px 28px;
  box-shadow: var(--shadow-md);
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-top-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
}

.hero-title-group {
  flex: 1;
  min-width: 280px;
}

.hero-badge-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 8px;
}

.hero-eyebrow {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: rgba(255, 255, 255, 0.85);
  font-weight: 700;
}

.gate-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(8px);
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  color: #ffffff;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #34d399;
  box-shadow: 0 0 8px #34d399;
  display: inline-block;
}

.telemetry-sync-pill {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: rgba(255, 255, 255, 0.16);
  border: 1px solid rgba(255, 255, 255, 0.28);
  backdrop-filter: blur(8px);
  padding: 3px 6px 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  color: #ffffff;
}

.telemetry-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #34d399;
  box-shadow: 0 0 6px #34d399;
  display: inline-block;
  transition: background-color 200ms ease, box-shadow 200ms ease;
}

.telemetry-dot--syncing {
  background: #60a5fa;
  box-shadow: 0 0 6px #60a5fa;
  animation: pulse-dot 1s infinite alternate;
}

@keyframes pulse-dot {
  from { opacity: 0.4; }
  to { opacity: 1; }
}

.telemetry-label {
  letter-spacing: 0.02em;
}

.btn-telemetry-sync {
  background: rgba(255, 255, 255, 0.22);
  border: 1px solid rgba(255, 255, 255, 0.4);
  color: #ffffff;
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: background-color 150ms ease, transform 150ms ease;
}

.btn-telemetry-sync:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.35);
  transform: translateY(-0.5px);
}

.btn-telemetry-sync:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spin-anim {
  animation: spin 900ms linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.hero-title {
  font-size: 26px;
  line-height: 1.15;
  font-family: var(--font-display);
  font-weight: 800;
  color: #ffffff;
}

.hero-copy {
  margin-top: 6px;
  color: rgba(255, 255, 255, 0.88);
  font-size: 14px;
  line-height: 1.5;
  max-width: 620px;
}

.hero-actions-group {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-hero-action {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(8px);
  padding: 9px 16px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  text-decoration: none;
  transition: transform 180ms ease, background 180ms ease, color 180ms ease;
}

.btn-hero-action:hover {
  background: #ffffff;
  color: #0a5c38;
  transform: translateY(-1px);
}

.btn-hero-action:active {
  transform: translateY(0);
}

.btn-hero-action--subtle {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(255, 255, 255, 0.24);
}

.hero-switcher-row {
  display: flex;
  align-items: center;
  padding-top: 4px;
  border-top: 1px solid rgba(255, 255, 255, 0.14);
}

.loading-state {
  display: flex;
  justify-content: center;
  padding: 80px 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.stat-card {
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: 20px;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  text-decoration: none;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  transition: transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
  cursor: pointer;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
  border-color: #CBD5E1;
}

.stat-card--alert {
  border-color: rgba(239, 68, 68, 0.3);
  background: linear-gradient(180deg, #FFFFFF 0%, #FEF2F2 100%);
}

.stat-card--warning {
  border-color: rgba(245, 158, 11, 0.3);
}

.text-danger {
  color: #DC2626 !important;
}

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 200ms ease;
}

.stat-card:hover .stat-icon {
  transform: scale(1.04);
}

.stat-icon--blue   { background: #EFF6FF; color: #2563EB; }
.stat-icon--green  { background: #ECFDF5; color: #059669; }
.stat-icon--teal   { background: #F0FDF4; color: #0A5C38; }
.stat-icon--red    { background: #FEF2F2; color: #DC2626; }
.stat-icon--yellow { background: #FFFBEB; color: #D97706; }

.stat-value {
  font-size: 26px;
  font-weight: 800;
  line-height: 1;
  color: var(--c-text);
  font-family: var(--font-display, inherit);
}

.stat-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--c-muted);
  margin-top: 4px;
}

.dashboard-panels {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
  gap: 16px;
}

.card {
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
  overflow: hidden;
}
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--c-border);
}
.card-title {
  font-size: 15px;
  font-weight: 600;
}
.card-link {
  font-size: 13px;
  color: var(--c-primary);
  font-weight: 500;
}
.card-link:hover { text-decoration: underline; }

.link { color: var(--c-primary); font-weight: 500; }
.link:hover { text-decoration: underline; }

.visitor-meta {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.visitor-name {
  font-weight: 600;
  color: var(--c-text);
  text-decoration: none;
}

.visitor-name:hover {
  color: var(--c-primary);
}

.visitor-subline {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.vehicle-subtext {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  color: #64748B;
  background: #F1F5F9;
  border: 1px solid #E2E8F0;
  padding: 1px 6px;
  border-radius: 4px;
  letter-spacing: 0.03em;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.visitor-type-tag {
  font-size: 10.5px;
  font-weight: 600;
  color: #065F46;
  background: #ECFDF5;
  padding: 1px 6px;
  border-radius: 4px;
  text-transform: capitalize;
}

.status-cell {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 3px;
}

.dwell-time-hint {
  font-size: 10.5px;
  color: #059669;
  font-weight: 600;
}

@media (max-width: 500px) {
  .stats-grid { grid-template-columns: 1fr; }
}

.estate-setup-banner {
  background: linear-gradient(135deg, rgba(10, 92, 56, 0.08) 0%, rgba(0, 201, 122, 0.08) 100%);
  border: 1.5px dashed var(--c-primary, #0a5c38);
  border-radius: 20px;
  padding: 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
}

.setup-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--c-primary, #0a5c38);
  background: rgba(10, 92, 56, 0.12);
  padding: 4px 10px;
  border-radius: 8px;
  margin-bottom: 8px;
}

.setup-banner-left h2 {
  font-size: 20px;
  font-weight: 800;
  color: var(--c-text);
  margin-bottom: 4px;
  font-family: var(--font-display);
}

.setup-banner-left p {
  font-size: 14px;
  color: var(--c-muted);
  max-width: 600px;
}

.btn-add-estate {
  padding: 12px 22px;
  font-size: 14px;
  font-weight: 700;
  border-radius: 12px;
  white-space: nowrap;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-box {
  background: var(--c-surface, #fff);
  border-radius: 24px;
  padding: 28px 24px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.2);
  animation: modalIn 0.25s ease-out;
}

@keyframes modalIn {
  from { opacity: 0; transform: scale(0.96) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 20px;
}

.modal-title {
  font-size: 22px;
  font-weight: 800;
  font-family: var(--font-display);
}

.modal-sub {
  font-size: 13px;
  color: var(--c-muted);
  margin-top: 4px;
}

.modal-close {
  background: none;
  border: none;
  font-size: 26px;
  line-height: 1;
  color: var(--c-muted);
  cursor: pointer;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.modal-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.modal-error {
  background: #fee2e2;
  color: #991b1b;
  font-size: 13px;
  padding: 10px 14px;
  border-radius: 10px;
}
</style>
