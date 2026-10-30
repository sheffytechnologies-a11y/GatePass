<template>
  <div class="dashboard-page">
    <section class="hero-card">
      <div class="hero-header-row">
        <div>
          <div class="hero-eyebrow">Command center</div>
          <h1 class="hero-title">Run estate operations from the same mobile workflow.</h1>
        </div>
        <button class="btn btn-hero-action" @click="showAddEstateModal = true">
          <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M10 4V16M4 10H16" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
          Add Estate
        </button>
      </div>
      <p class="hero-copy">Create residents and security staff, track gate activity, and stay on top of emergencies without leaving the phone shell.</p>
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
      <div class="spinner"></div>
    </div>

    <template v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon stat-icon--blue">👤</div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.totalUsers }}</div>
            <div class="stat-label">Total Users</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon--green">🏠</div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.totalResidents }}</div>
            <div class="stat-label">Residents</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon--teal">🎫</div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.activePasses }}</div>
            <div class="stat-label">Active Passes</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon--teal">📍</div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.onSite }}</div>
            <div class="stat-label">On-Site Now</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon--red">🚨</div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.todayEmergencies }}</div>
            <div class="stat-label">Emergencies Today</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon stat-icon--yellow">🚩</div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.flaggedItems }}</div>
            <div class="stat-label">Flagged Item Passes</div>
          </div>
        </div>
      </div>

      <div class="quick-grid">
        <router-link to="/users" class="quick-card card">
          <span class="quick-icon">👥</span>
          <strong>Manage users</strong>
          <span>Create or update resident and security accounts.</span>
        </router-link>
        <router-link to="/residents" class="quick-card card">
          <span class="quick-icon">🏠</span>
          <strong>Residents</strong>
          <span>Assign estates, units, and resident status.</span>
        </router-link>
        <router-link to="/access" class="quick-card card">
          <span class="quick-icon">🛡️</span>
          <strong>Access feed</strong>
          <span>Review gate traffic and flagged visitor passes.</span>
        </router-link>
        <router-link to="/passes" class="quick-card card">
          <span class="quick-icon">🎟️</span>
          <strong>Passes</strong>
          <span>Create passes and monitor expiry or revocation status.</span>
        </router-link>
        <router-link to="/notifications" class="quick-card card">
          <span class="quick-icon">🔔</span>
          <strong>Messages</strong>
          <span>Check system notifications sent to residents.</span>
        </router-link>
        <router-link to="/billing" class="quick-card card">
          <span class="quick-icon">💳</span>
          <strong>Billing</strong>
          <span>Manage your plan, usage, and payments.</span>
        </router-link>
      </div>

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
                    <router-link :to="`/passes/${pass.ulid}`" class="link">{{ pass.visitorName }}</router-link>
                  </td>
                  <td>{{ pass.resident?.name ?? '—' }}</td>
                  <td>{{ pass.resident?.unit ?? '—' }}</td>
                  <td><span :class="passStatusClass(pass.status)" class="badge">{{ pass.status }}</span></td>
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
              <span v-if="creatingEstate" class="spinner" />
              <span v-else>Save Estate &rarr;</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { dashboardApi, estatesApi, onboardingApi } from '@/api/index'
import { useToast } from '@/composables/useToast'
import StateSelect from '@/components/StateSelect.vue'

const { showToast } = useToast()

const loading = ref(true)
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

async function load() {
  try {
    const [summaryRes, estatesRes] = await Promise.all([
      dashboardApi.getSummary(),
      estatesApi.getAll().catch(() => ({ data: { estates: [] } }))
    ])
    stats.value = summaryRes.data.stats
    recentPasses.value = summaryRes.data.recentPasses
    recentEmergencies.value = summaryRes.data.recentEmergencies
    estates.value = estatesRes.data?.estates ?? []
  } catch {
    showToast('Failed to load dashboard data.', 'error')
  } finally {
    loading.value = false
  }
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
    await load()
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

onMounted(load)
</script>

<style scoped>
.dashboard-page { display: flex; flex-direction: column; gap: 18px; }
.hero-card {
  background: radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 38%), linear-gradient(150deg, #102a21 0%, #0a5c38 60%, #78cca2 100%);
  color: white;
  border-radius: 28px;
  padding: 22px;
  box-shadow: var(--shadow-md);
}
.hero-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: rgba(255,255,255,0.74); }
.hero-title { margin-top: 10px; font-size: 28px; line-height: 1.04; font-family: var(--font-display); }
.hero-copy { margin-top: 8px; color: rgba(255,255,255,0.80); font-size: 14px; }

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
  border-radius: 22px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
}
.stat-icon {
  width: 44px; height: 44px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.stat-icon--blue   { background: #EFF6FF; }
.stat-icon--green  { background: var(--c-success-light); }
.stat-icon--teal   { background: var(--c-primary-light); }
.stat-icon--red    { background: var(--c-danger-light); }
.stat-icon--yellow { background: var(--c-warning-light); }

.stat-value {
  font-size: 28px;
  font-weight: 700;
  line-height: 1;
  color: var(--c-text);
}
.stat-label {
  font-size: 12px;
  color: var(--c-muted);
  margin-top: 4px;
}

.quick-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 10px;
}
.quick-card {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.quick-icon { font-size: 22px; }
.quick-card strong { font-size: 16px; color: var(--c-text); }
.quick-card span:last-child { color: var(--c-muted); font-size: 13px; }

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

@media (max-width: 500px) {
  .stats-grid { grid-template-columns: 1fr; }
}

.hero-header-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.btn-hero-action {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.35);
  backdrop-filter: blur(8px);
  padding: 8px 16px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-hero-action:hover {
  background: #fff;
  color: #0a5c38;
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
