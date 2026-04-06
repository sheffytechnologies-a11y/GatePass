<template>
  <div>
    <div class="page-header">
      <h1 class="page-title">Dashboard</h1>
    </div>

    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
    </div>

    <template v-else>
      <!-- Stats grid -->
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

      <!-- Bottom panels -->
      <div class="dashboard-panels">
        <!-- Recent Passes -->
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Recent Passes</h2>
            <router-link to="/passes" class="card-link">View all →</router-link>
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

        <!-- Recent Emergencies -->
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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { dashboardApi } from '@/api/index'
import { useToast } from '@/composables/useToast'

const { showToast } = useToast()

const loading = ref(true)
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
    const res = await dashboardApi.getSummary()
    stats.value = res.data.stats
    recentPasses.value = res.data.recentPasses
    recentEmergencies.value = res.data.recentEmergencies
  } catch {
    showToast('Failed to load dashboard data.', 'error')
  } finally {
    loading.value = false
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
.page-header {
  margin-bottom: 24px;
}
.page-title {
  font-size: 22px;
  font-weight: 700;
  color: var(--c-text);
}

.loading-state {
  display: flex;
  justify-content: center;
  padding: 80px 0;
}

/* Stats */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 16px;
  margin-bottom: 28px;
}
.stat-card {
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: var(--radius-lg);
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: var(--shadow);
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

/* Panels */
.dashboard-panels {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
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

@media (max-width: 900px) {
  .dashboard-panels { grid-template-columns: 1fr; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 500px) {
  .stats-grid { grid-template-columns: 1fr; }
}
</style>
