<template>
  <div class="dashboard-page coinest-theme">
    <!-- Top Bar: Greeting & Estate Switcher -->
    <header class="dashboard-topbar">
      <div class="topbar-left">
        <div class="topbar-breadcrumb">Estate Admin &bull; Command Center</div>
        <h1 class="topbar-title">Dashboard Overview</h1>
      </div>
      <div class="topbar-right">
        <EstateSwitcher variant="hero" />
      </div>
    </header>

    <!-- Setup Banner if no estate exists yet -->
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

    <!-- 3-Column Coinest Master Command Grid -->
    <div v-else class="coinest-grid">
      
      <!-- ── COLUMN 1: Left Command Column ── -->
      <aside class="col-command">
        <!-- 1. Estate Master Card (Coinest Bank Card Inspired) -->
        <div class="estate-master-card">
          <div class="master-chip-row">
            <span class="signal-icon" title="24/7 Gate Access Online">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
            </span>
            <span class="master-estate-code">{{ activeEstate?.code || '#CP-01' }}</span>
          </div>

          <div class="master-user-info">
            <span class="master-admin-label">{{ auth.user?.name || 'Estate Admin' }}</span>
            <h2 class="master-estate-name">{{ activeEstate?.name || 'Sheffy Estate' }}</h2>
          </div>

          <div class="master-metric-group">
            <div class="metric-big-row">
              <span class="metric-val">{{ stats.occupancyRate }}%</span>
              <span class="metric-pill">Active Shift</span>
            </div>
            <div class="metric-sublabel">Unit Occupancy Rate ({{ stats.occupiedUnits }} / {{ stats.totalUnits }})</div>
          </div>

          <div class="master-card-footer">
            <span class="gate-status-text">Gate Checkpoint Live</span>
            <span class="status-pulse-dot"></span>
          </div>
        </div>

        <!-- 2. Instant Quick Actions Row (4 Circular Icon Chips) -->
        <div class="card quick-actions-card">
          <div class="card-inner-header">
            <span class="card-inner-title">Quick Access</span>
            <span class="card-inner-sub">Frequent Actions</span>
          </div>
          <div class="quick-action-buttons-grid">
            <router-link to="/passes" class="action-btn-circle" title="Issue Visitor Pass">
              <div class="action-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><rect x="3" y="4" width="18" height="16" rx="2"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="12" y1="11" x2="12" y2="15"/><line x1="10" y1="13" x2="14" y2="13"/></svg>
              </div>
              <span class="action-label">Pass</span>
            </router-link>

            <router-link to="/residents" class="action-btn-circle" title="Register New Resident">
              <div class="action-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
              </div>
              <span class="action-label">Resident</span>
            </router-link>

            <router-link to="/properties" class="action-btn-circle" title="Manage Properties & Units">
              <div class="action-icon">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              </div>
              <span class="action-label">Units</span>
            </router-link>

            <button type="button" class="action-btn-circle" @click="openBroadcastModal" title="Publish Broadcast Notice">
              <div class="action-icon action-icon--broadcast">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
              </div>
              <span class="action-label">Broadcast</span>
            </button>
          </div>
        </div>

        <!-- 3. Capacity & Occupancy Gauge Card (Coinest Daily Limit Inspired) -->
        <div class="card capacity-card">
          <div class="capacity-header">
            <div>
              <span class="capacity-eyebrow">Estate Topology</span>
              <h3 class="capacity-title">Unit Occupancy</h3>
            </div>
            <span class="capacity-pct">{{ stats.occupancyRate }}%</span>
          </div>

          <div class="capacity-progress-track">
            <div class="capacity-progress-fill" :style="{ width: `${Math.min(stats.occupancyRate, 100)}%` }"></div>
          </div>

          <div class="capacity-metrics-row">
            <span><strong>{{ stats.occupiedUnits }}</strong> occupied</span>
            <span><strong>{{ stats.vacantUnits }}</strong> vacant of {{ stats.totalUnits }}</span>
          </div>
        </div>

        <!-- 4. Security Incident Watch Card -->
        <div class="card security-status-card">
          <div class="security-icon-box" :class="stats.todayEmergencies > 0 ? 'alert-active' : 'safe-active'">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </div>
          <div class="security-meta">
            <span class="sec-label">Security Dispatch</span>
            <div class="sec-status">
              {{ stats.todayEmergencies === 0 ? 'All Checkpoints Clear' : `${stats.todayEmergencies} Alert(s) Today` }}
            </div>
          </div>
          <router-link to="/emergencies" class="sec-link" title="Open Emergency Dispatch">&rarr;</router-link>
        </div>
      </aside>

      <!-- ── COLUMN 2: Center Main Operations Canvas ── -->
      <main class="col-main">
        <!-- 1. Top 3 KPI Stat Cards -->
        <div class="kpi-row">
          <router-link to="/passes" class="kpi-card">
            <div class="kpi-top">
              <div class="kpi-icon-pill kpi-icon--emerald">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="3"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="7" y1="15" x2="7.01" y2="15"/></svg>
              </div>
              <span class="kpi-badge kpi-badge--green">+14% vs avg</span>
            </div>
            <div class="kpi-value">{{ stats.activePasses }}</div>
            <div class="kpi-label">Active Passes Today</div>
          </router-link>

          <router-link to="/residents" class="kpi-card">
            <div class="kpi-top">
              <div class="kpi-icon-pill kpi-icon--blue">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              </div>
              <span class="kpi-badge kpi-badge--blue">Verified</span>
            </div>
            <div class="kpi-value">{{ stats.totalResidents }}</div>
            <div class="kpi-label">Total Verified Residents</div>
          </router-link>

          <router-link to="/fees" class="kpi-card">
            <div class="kpi-top">
              <div class="kpi-icon-pill kpi-icon--gold">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              </div>
              <span class="kpi-badge kpi-badge--gold">{{ stats.collectionRate }}% Collected</span>
            </div>
            <div class="kpi-value">₦{{ fmtNaira(stats.duesCollected) }}</div>
            <div class="kpi-label">Estate Dues Revenue</div>
          </router-link>
        </div>

        <!-- 2. Dual-Bar Influx Chart Card (Coinest Cashflow Chart Inspired) -->
        <div class="card chart-card">
          <div class="chart-header">
            <div>
              <span class="chart-eyebrow">Gate Telemetry &amp; Influx</span>
              <div class="chart-headline-row">
                <h3 class="chart-title">Visitor Passes &amp; Influx Traffic</h3>
                <span class="chart-big-count">{{ totalWeekPasses }} Total Passes</span>
              </div>
            </div>

            <div class="chart-legend-box">
              <div class="legend-chip">
                <span class="legend-square legend-square--forest"></span>
                <span>Passes Issued</span>
              </div>
              <div class="legend-chip">
                <span class="legend-square legend-square--mint"></span>
                <span>Checked-in at Gate</span>
              </div>
            </div>
          </div>

          <!-- Dual-Bar SVG Chart -->
          <div class="svg-bar-chart-wrap">
            <svg viewBox="0 0 520 180" class="svg-bar-chart" preserveAspectRatio="none">
              <!-- Grid lines -->
              <line x1="20" y1="20" x2="500" y2="20" stroke="#F1F5F9" stroke-width="1" />
              <line x1="20" y1="60" x2="500" y2="60" stroke="#F1F5F9" stroke-width="1" />
              <line x1="20" y1="100" x2="500" y2="100" stroke="#F1F5F9" stroke-width="1" />
              <line x1="20" y1="140" x2="500" y2="140" stroke="#E2E8F0" stroke-width="1.2" />

              <!-- Bar groups for each day -->
              <g
                v-for="(b, idx) in influxBarCoords"
                :key="idx"
                class="bar-group"
                @mouseenter="hoveredBar = b"
                @mouseleave="hoveredBar = null"
              >
                <!-- Bar 1: Total Issued (Deep Forest Green) -->
                <rect
                  :x="b.x1"
                  :y="b.y1"
                  :width="b.barW"
                  :height="b.h1"
                  rx="4"
                  ry="4"
                  fill="#0A5C38"
                  class="bar-rect"
                />
                <!-- Bar 2: Checked In (Bright Mint Green) -->
                <rect
                  :x="b.x2"
                  :y="b.y2"
                  :width="b.barW"
                  :height="b.h2"
                  rx="4"
                  ry="4"
                  fill="#10B981"
                  class="bar-rect"
                />
                <!-- Day label -->
                <text :x="b.centerX" y="160" text-anchor="middle" font-size="11.5" font-weight="600" fill="#64748B">{{ b.day }}</text>
              </g>
            </svg>

            <!-- Interactive hover tooltip -->
            <div v-if="hoveredBar" class="chart-tooltip" :style="{ left: `${(hoveredBar.centerX / 520) * 100}%` }">
              <div class="tooltip-header">{{ hoveredBar.day }} &bull; {{ hoveredBar.label }}</div>
              <div class="tooltip-row"><span>Issued:</span> <strong>{{ hoveredBar.total }}</strong></div>
              <div class="tooltip-row"><span>Checked-in:</span> <strong class="text-mint">{{ hoveredBar.arrived }}</strong></div>
            </div>
          </div>
        </div>

        <!-- 3. Recent Passes Operational Table Card -->
        <div class="card table-card">
          <div class="table-card-header">
            <div>
              <h3 class="table-card-title">Recent Passes &amp; Gate Access</h3>
              <span class="table-card-sub">Live clearance feed across community checkpoints</span>
            </div>
            <router-link to="/access" class="card-link">View full feed &rarr;</router-link>
          </div>

          <div v-if="recentPasses.length === 0" class="empty-state">
            <p>No recent gate passes recorded.</p>
          </div>
          <div v-else class="table-wrap">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Visitor</th>
                  <th>Resident &amp; Unit</th>
                  <th>Status</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="pass in recentPasses.slice(0, 5)" :key="pass.ulid">
                  <td>
                    <div class="visitor-col">
                      <router-link :to="`/passes/${pass.ulid}`" class="visitor-name">
                        {{ pass.visitorName }}
                      </router-link>
                      <span v-if="pass.vehiclePlate" class="plate-badge">{{ pass.vehiclePlate }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="unit-col">
                      <span class="res-name">{{ pass.resident?.name ?? '—' }}</span>
                      <span class="unit-tag">{{ pass.resident?.unit ?? '—' }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="status-cell">
                      <span :class="passStatusClass(pass.status)" class="status-pill">{{ pass.status }}</span>
                      <span v-if="pass.status === 'On-site' && pass.arrivedAt" class="dwell-tag">{{ calcDwellTime(pass.arrivedAt) }}</span>
                    </div>
                  </td>
                  <td class="time-col">{{ fmtDate(pass.createdAt) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>

      <!-- ── COLUMN 3: Right Analytical & Activity Column ── -->
      <aside class="col-analytics">
        <!-- 1. Donut / Radial Distribution Widget (Coinest Statistic Widget) -->
        <div class="card donut-card">
          <div class="donut-header">
            <h3 class="donut-title">Pass Distribution</h3>
            <span class="donut-period">30 Days</span>
          </div>

          <!-- SVG Donut Ring -->
          <div class="donut-svg-wrap">
            <svg viewBox="0 0 160 160" class="donut-svg">
              <circle cx="80" cy="80" r="54" fill="none" stroke="#F1F5F9" stroke-width="16" />
              <circle
                v-for="(seg, sIdx) in donutSegments"
                :key="sIdx"
                cx="80"
                cy="80"
                r="54"
                fill="none"
                :stroke="seg.color"
                stroke-width="16"
                :stroke-dasharray="seg.dasharray"
                :stroke-dashoffset="seg.dashoffset"
                class="donut-segment"
              />
              <text x="80" y="76" text-anchor="middle" font-size="20" font-weight="800" fill="#0F172A">{{ totalCategoriesCount || stats.activePasses }}</text>
              <text x="80" y="94" text-anchor="middle" font-size="10.5" font-weight="600" fill="#64748B">Total Passes</text>
            </svg>
          </div>

          <!-- Categories Legend List -->
          <div class="donut-breakdown-list">
            <div class="donut-list-row">
              <div class="row-left">
                <span class="color-dot dot--forest"></span>
                <span class="row-label">Guests</span>
              </div>
              <span class="row-val">{{ categoryPercentages.guest }}%</span>
            </div>
            <div class="donut-list-row">
              <div class="row-left">
                <span class="color-dot dot--mint"></span>
                <span class="row-label">Deliveries</span>
              </div>
              <span class="row-val">{{ categoryPercentages.delivery }}%</span>
            </div>
            <div class="donut-list-row">
              <div class="row-left">
                <span class="color-dot dot--blue"></span>
                <span class="row-label">Artisans</span>
              </div>
              <span class="row-val">{{ categoryPercentages.artisan }}%</span>
            </div>
            <div class="donut-list-row">
              <div class="row-left">
                <span class="color-dot dot--purple"></span>
                <span class="row-label">Family</span>
              </div>
              <span class="row-val">{{ categoryPercentages.family }}%</span>
            </div>
          </div>
        </div>

        <!-- 2. Real-Time Activity Timeline (Coinest Recent Activity) -->
        <div class="card timeline-card">
          <div class="timeline-header">
            <h3 class="timeline-title">Recent Activity</h3>
            <router-link to="/access" class="card-link-sm">View feed</router-link>
          </div>

          <div v-if="activityFeed.length === 0" class="timeline-empty">
            <p>No activity recorded yet.</p>
          </div>
          <div v-else class="timeline-feed">
            <div v-for="item in activityFeed" :key="item.id" class="timeline-item">
              <div class="timeline-icon-box" :class="item.iconClass">
                <span>{{ item.icon }}</span>
              </div>
              <div class="timeline-body">
                <div class="timeline-item-title">{{ item.title }}</div>
                <div class="timeline-item-sub">{{ item.subtitle }}</div>
                <div class="timeline-item-time">{{ item.time }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- 3. Estate Community Broadcast Card -->
        <div class="card notice-sidebar-card">
          <div class="notice-top">
            <span class="notice-eyebrow">Resident Notices</span>
            <button type="button" class="btn-quick-post" @click="openBroadcastModal">+ Notice</button>
          </div>
          <div v-if="recentNews.length > 0" class="notice-preview-box">
            <div class="notice-badge">{{ recentNews[0].estateName || 'Estate Notice' }}</div>
            <h4 class="notice-title">{{ recentNews[0].title }}</h4>
            <p class="notice-snippet">{{ recentNews[0].content }}</p>
            <div class="notice-meta">Live in resident app &bull; {{ fmtDate(recentNews[0].createdAt) }}</div>
          </div>
          <div v-else class="notice-empty">
            <p>No active announcements broadcasted.</p>
          </div>
          <router-link to="/news" class="notice-link">Manage announcements &rarr;</router-link>
        </div>
      </aside>

    </div>

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

    <!-- Quick Broadcast Modal -->
    <div v-if="showBroadcastModal" class="modal-overlay" @click.self="showBroadcastModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">Publish Estate Notice</h2>
            <p class="modal-sub">Broadcast announcement directly to all resident mobile app feeds.</p>
          </div>
          <button class="modal-close" @click="showBroadcastModal = false">&times;</button>
        </div>

        <form @submit.prevent="handlePublishNotice" class="modal-form">
          <div class="form-group" v-if="estates.length > 1">
            <label class="form-label">Target Estate *</label>
            <select v-model="broadcastForm.estate_id" class="form-select" required>
              <option v-for="e in estates" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Notice Title *</label>
            <input
              v-model="broadcastForm.title"
              type="text"
              class="form-input"
              placeholder="e.g. Scheduled Power Outage & Generator Schedule"
              required
            />
          </div>

          <div class="form-group">
            <label class="form-label">Message Details *</label>
            <textarea
              v-model="broadcastForm.content"
              class="form-textarea"
              rows="5"
              placeholder="Detail the announcement for residents: date, time, affected areas, guidelines..."
              required
            ></textarea>
          </div>

          <div v-if="broadcastError" class="modal-error">{{ broadcastError }}</div>

          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="showBroadcastModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="publishingNotice">
              <BrandSpinner v-if="publishingNotice" :size="18" color="#ffffff" />
              <span v-else>Publish to Residents &rarr;</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { dashboardApi, estatesApi, onboardingApi, newsApi } from '@/api/index'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import StateSelect from '@/components/StateSelect.vue'
import EstateSwitcher from '@/components/EstateSwitcher.vue'

const auth = useAuthStore()
const { showToast } = useToast()

const loading = ref(true)
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

const activeEstate = computed(() => {
  return estates.value.find(e => e.id === auth.activeEstateId) || estates.value[0] || null
})

const recentNews = ref<any[]>([])
const showBroadcastModal = ref(false)
const publishingNotice = ref(false)
const broadcastError = ref('')
const broadcastForm = ref({
  estate_id: 1,
  title: '',
  content: '',
})

function openBroadcastModal() {
  broadcastError.value = ''
  if (estates.value.length > 0 && !broadcastForm.value.estate_id) {
    broadcastForm.value.estate_id = estates.value[0].id
  }
  showBroadcastModal.value = true
}

async function handlePublishNotice() {
  broadcastError.value = ''
  publishingNotice.value = true
  try {
    await newsApi.create({
      estate_id: broadcastForm.value.estate_id,
      title: broadcastForm.value.title.trim(),
      content: broadcastForm.value.content.trim(),
    })
    showToast('Announcement broadcasted to residents!', 'success')
    showBroadcastModal.value = false
    broadcastForm.value = { estate_id: estates.value[0]?.id || 1, title: '', content: '' }
    await load(false)
  } catch (e: any) {
    broadcastError.value = e.response?.data?.message || 'Could not publish notice. Please try again.'
  } finally {
    publishingNotice.value = false
  }
}

const hoveredBar = ref<any>(null)

const stats = ref({
  totalUsers: 0,
  totalResidents: 0,
  activePasses: 0,
  onSite: 0,
  todayEmergencies: 0,
  flaggedItems: 0,
  occupancyRate: 0,
  totalUnits: 0,
  occupiedUnits: 0,
  vacantUnits: 0,
  duesExpected: 0,
  duesCollected: 0,
  duesPending: 0,
  collectionRate: 0,
  peakHour: '08:00 - 10:00',
})

const analytics = ref({
  passTrend: [] as Array<{ day: string; date: string; label: string; total: number; arrived: number }>,
  typeDistribution: {
    guest: 0,
    delivery: 0,
    artisan: 0,
    family: 0,
  },
  paymentMonths: [] as Array<{ month: string; year: number; amount: number }>,
})

const totalWeekPasses = computed(() => {
  return (analytics.value.passTrend || []).reduce((acc, curr) => acc + (curr.total || 0), 0)
})

const totalCategoriesCount = computed(() => {
  const dist = analytics.value.typeDistribution
  if (!dist) return 0
  return (dist.guest || 0) + (dist.delivery || 0) + (dist.artisan || 0) + (dist.family || 0)
})

const categoryPercentages = computed(() => {
  const total = totalCategoriesCount.value || 1
  const dist = analytics.value.typeDistribution || { guest: 0, delivery: 0, artisan: 0, family: 0 }
  return {
    guest: Math.round(((dist.guest || 0) / total) * 100),
    delivery: Math.round(((dist.delivery || 0) / total) * 100),
    artisan: Math.round(((dist.artisan || 0) / total) * 100),
    family: Math.round(((dist.family || 0) / total) * 100),
  }
})

function fmtNaira(val: number | null | undefined): string {
  if (!val) return '0'
  return new Intl.NumberFormat('en-NG').format(Math.round(val))
}

const influxBarCoords = computed(() => {
  const trend = analytics.value.passTrend || []
  const days = trend.length > 0 ? trend : [
    { day: 'Mon', total: 14, arrived: 11, label: '10 Nov' },
    { day: 'Tue', total: 18, arrived: 15, label: '11 Nov' },
    { day: 'Wed', total: 22, arrived: 19, label: '12 Nov' },
    { day: 'Thu', total: 16, arrived: 12, label: '13 Nov' },
    { day: 'Fri', total: 28, arrived: 24, label: '14 Nov' },
    { day: 'Sat', total: 34, arrived: 29, label: '15 Nov' },
    { day: 'Sun', total: 20, arrived: 16, label: '16 Nov' },
  ]

  const maxVal = Math.max(10, ...days.map(d => Math.max(d.total, d.arrived)))
  const slotW = 500 / days.length
  const barW = 12

  return days.map((d, i) => {
    const centerX = 30 + i * slotW + slotW / 2
    const h1 = Math.max(4, (d.total / maxVal) * 105)
    const h2 = Math.max(4, (d.arrived / maxVal) * 105)
    return {
      day: d.day,
      label: d.label,
      total: d.total,
      arrived: d.arrived,
      centerX,
      x1: centerX - 14,
      y1: 140 - h1,
      h1,
      x2: centerX + 2,
      y2: 140 - h2,
      h2,
      barW,
    }
  })
})

const donutSegments = computed(() => {
  const C = 2 * Math.PI * 54 // ~339.29
  const p = categoryPercentages.value

  const g = p.guest || 25
  const d = p.delivery || 25
  const a = p.artisan || 25
  const f = p.family || 25
  const total = g + d + a + f || 100

  const gFrac = (g / total) * C
  const dFrac = (d / total) * C
  const aFrac = (a / total) * C
  const fFrac = (f / total) * C

  return [
    { color: '#0A5C38', dasharray: `${gFrac.toFixed(1)} ${C.toFixed(1)}`, dashoffset: 0 },
    { color: '#10B981', dasharray: `${dFrac.toFixed(1)} ${C.toFixed(1)}`, dashoffset: (-gFrac).toFixed(1) },
    { color: '#3B82F6', dasharray: `${aFrac.toFixed(1)} ${C.toFixed(1)}`, dashoffset: (-(gFrac + dFrac)).toFixed(1) },
    { color: '#8B5CF6', dasharray: `${fFrac.toFixed(1)} ${C.toFixed(1)}`, dashoffset: (-(gFrac + dFrac + aFrac)).toFixed(1) },
  ]
})

const recentPasses = ref<any[]>([])
const recentEmergencies = ref<any[]>([])

const activityFeed = computed(() => {
  const items: Array<{
    id: string
    type: 'pass' | 'emergency' | 'news'
    title: string
    subtitle: string
    time: string
    icon: string
    iconClass: string
  }> = []

  for (const p of (recentPasses.value || []).slice(0, 4)) {
    items.push({
      id: `pass-${p.ulid}`,
      type: 'pass',
      title: `${p.visitorName || 'Visitor'} &bull; ${p.status || 'Pending'}`,
      subtitle: p.resident?.name ? `Invited by ${p.resident.name}` : `Destination: Unit ${p.unit || '—'}`,
      time: p.createdAt ? formatRelativeTime(p.createdAt) : 'Recently',
      icon: '🎟️',
      iconClass: 'icon-pass',
    })
  }

  for (const e of (recentEmergencies.value || []).slice(0, 2)) {
    items.push({
      id: `emerg-${e.id}`,
      type: 'emergency',
      title: `${e.type || 'Emergency'} reported`,
      subtitle: `${e.resident?.name || 'Resident'} (${e.resident?.unit || e.estate?.name || 'Estate'})`,
      time: e.createdAt ? formatRelativeTime(e.createdAt) : 'Today',
      icon: '🚨',
      iconClass: 'icon-danger',
    })
  }

  for (const n of (recentNews.value || []).slice(0, 2)) {
    items.push({
      id: `news-${n.id}`,
      type: 'news',
      title: n.title || 'Community Notice',
      subtitle: 'Broadcasted to resident app',
      time: n.createdAt ? formatRelativeTime(n.createdAt) : 'Recently',
      icon: '📢',
      iconClass: 'icon-info',
    })
  }

  return items.slice(0, 6)
})

function formatRelativeTime(iso: string): string {
  if (!iso) return '—'
  const diffMs = Date.now() - new Date(iso).getTime()
  const mins = Math.floor(diffMs / 60000)
  if (mins < 1) return 'Just now'
  if (mins < 60) return `${mins}m ago`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours}h ago`
  const days = Math.floor(hours / 24)
  return `${days}d ago`
}

async function load(isBackground = false) {
  if (!isBackground) {
    loading.value = true
  }
  try {
    const [summaryRes, estatesRes, newsRes] = await Promise.all([
      dashboardApi.getSummary(),
      estatesApi.getAll().catch(() => ({ data: { estates: [] } })),
      newsApi.getAll().catch(() => ({ data: { news: [] } })),
    ])
    stats.value = { ...stats.value, ...summaryRes.data.stats }
    if (summaryRes.data.analytics) {
      analytics.value = summaryRes.data.analytics
    }
    recentPasses.value = summaryRes.data.recentPasses
    recentEmergencies.value = summaryRes.data.recentEmergencies
    recentNews.value = (newsRes.data?.news || []).slice(0, 3)
    estates.value = estatesRes.data?.estates ?? []
    secondsSinceSync.value = 0
  } catch {
    if (!isBackground) {
      showToast('Failed to load dashboard data.', 'error')
    }
  } finally {
    loading.value = false
  }
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

function fmtDate(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(() => {
  load(false)
  window.addEventListener('checkpass:estate-switched', () => load(false))
  document.addEventListener('visibilitychange', handleVisibilityChange)

  tickerInterval = setInterval(() => {
    secondsSinceSync.value++
  }, 1000)

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
.dashboard-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ── Top Bar ── */
.dashboard-topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  padding: 4px 0 6px;
}

.topbar-breadcrumb {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--c-primary, #0A5C38);
  margin-bottom: 2px;
}

.topbar-title {
  font-size: 24px;
  font-weight: 800;
  font-family: var(--font-display, sans-serif);
  color: #0F172A;
  margin: 0;
  line-height: 1.2;
}

/* ── 3-Column Coinest Master Grid ── */
.coinest-grid {
  display: grid;
  grid-template-columns: 290px minmax(0, 1fr) 320px;
  gap: 20px;
  align-items: start;
}

/* ── Left Command Column ── */
.col-command {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* 1. Estate Master Card */
.estate-master-card {
  background: radial-gradient(circle at top right, rgba(255,255,255,0.18), transparent 45%), linear-gradient(145deg, #072619 0%, #0A5C38 60%, #15803D 100%);
  color: #ffffff;
  border-radius: 24px;
  padding: 22px 20px;
  box-shadow: 0 10px 25px -5px rgba(10, 92, 56, 0.25);
  display: flex;
  flex-direction: column;
  gap: 16px;
  position: relative;
  overflow: hidden;
}

.master-chip-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.signal-icon {
  color: #34D399;
}

.master-estate-code {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.08em;
  background: rgba(255, 255, 255, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.25);
  padding: 3px 8px;
  border-radius: 6px;
}

.master-user-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.master-admin-label {
  font-size: 12px;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.8);
}

.master-estate-name {
  font-size: 20px;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
  line-height: 1.25;
}

.master-metric-group {
  background: rgba(0, 0, 0, 0.16);
  border-radius: 14px;
  padding: 12px 14px;
}

.metric-big-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
}

.metric-val {
  font-size: 26px;
  font-weight: 800;
  font-family: var(--font-display, sans-serif);
}

.metric-pill {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  background: #10B981;
  color: #ffffff;
  padding: 2px 7px;
  border-radius: 999px;
  letter-spacing: 0.06em;
}

.metric-sublabel {
  font-size: 11px;
  color: rgba(255, 255, 255, 0.78);
  margin-top: 3px;
}

.master-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid rgba(255, 255, 255, 0.15);
  padding-top: 10px;
  font-size: 11px;
  color: rgba(255, 255, 255, 0.85);
  font-weight: 600;
}

.status-pulse-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #34D399;
  box-shadow: 0 0 8px #34D399;
}

/* 2. Quick Actions Card */
.quick-actions-card {
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.card-inner-header {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
}

.card-inner-title {
  font-size: 13.5px;
  font-weight: 800;
  color: #0F172A;
}

.card-inner-sub {
  font-size: 11px;
  color: #64748B;
}

.quick-action-buttons-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
}

.action-btn-circle {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  text-decoration: none;
  background: none;
  border: none;
  padding: 0;
  cursor: pointer;
  transition: transform 150ms ease;
}

.action-btn-circle:hover {
  transform: translateY(-2px);
}

.action-btn-circle:active {
  transform: scale(0.96);
}

.action-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #F1F5F9;
  color: #0F172A;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #E2E8F0;
  transition: all 180ms ease;
}

.action-btn-circle:hover .action-icon {
  background: #0A5C38;
  color: #ffffff;
  border-color: #0A5C38;
  box-shadow: 0 4px 12px rgba(10, 92, 56, 0.2);
}

.action-icon--broadcast {
  color: #D97706;
}

.action-btn-circle:hover .action-icon--broadcast {
  background: #D97706;
  color: #ffffff;
  border-color: #D97706;
  box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);
}

.action-label {
  font-size: 11px;
  font-weight: 700;
  color: #334155;
}

/* 3. Capacity & Occupancy Card */
.capacity-card {
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.capacity-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.capacity-eyebrow {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #0A5C38;
}

.capacity-title {
  font-size: 14px;
  font-weight: 800;
  color: #0F172A;
  margin: 2px 0 0;
}

.capacity-pct {
  font-size: 14px;
  font-weight: 800;
  color: #059669;
  background: #ECFDF5;
  padding: 2px 8px;
  border-radius: 6px;
}

.capacity-progress-track {
  width: 100%;
  height: 8px;
  background: #E2E8F0;
  border-radius: 999px;
  overflow: hidden;
}

.capacity-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #0A5C38 0%, #10B981 100%);
  border-radius: 999px;
  transition: width 400ms ease-out;
}

.capacity-metrics-row {
  display: flex;
  justify-content: space-between;
  font-size: 11.5px;
  color: #64748B;
}

/* 4. Security Incident Status Card */
.security-status-card {
  padding: 14px 16px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.security-icon-box {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.safe-active {
  background: #ECFDF5;
  color: #059669;
}

.alert-active {
  background: #FEF2F2;
  color: #DC2626;
  animation: pulse-alert 1.5s infinite;
}

@keyframes pulse-alert {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.security-meta {
  flex: 1;
}

.sec-label {
  font-size: 11px;
  color: #64748B;
  display: block;
}

.sec-status {
  font-size: 12.5px;
  font-weight: 700;
  color: #0F172A;
}

.sec-link {
  font-size: 16px;
  color: #64748B;
  text-decoration: none;
  font-weight: 700;
  padding: 4px;
}

.sec-link:hover {
  color: #0A5C38;
}

/* ── Center Operations Column ── */
.col-main {
  display: flex;
  flex-direction: column;
  gap: 18px;
  min-width: 0;
}

/* 1. Top 3 KPI Cards Row */
.kpi-row {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 14px;
}

.kpi-card {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 20px;
  padding: 18px;
  text-decoration: none;
  color: inherit;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  gap: 6px;
  transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
}

.kpi-card:hover {
  transform: translateY(-2px);
  border-color: #CBD5E1;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
}

.kpi-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2px;
}

.kpi-icon-pill {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.kpi-icon--emerald { background: #ECFDF5; color: #059669; }
.kpi-icon--blue { background: #EFF6FF; color: #2563EB; }
.kpi-icon--gold { background: #FEF3C7; color: #D97706; }

.kpi-badge {
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
}

.kpi-badge--green { background: #DCFCE7; color: #15803D; }
.kpi-badge--blue { background: #DBEAFE; color: #1D4ED8; }
.kpi-badge--gold { background: #FEF3C7; color: #B45309; }

.kpi-value {
  font-size: 24px;
  font-weight: 800;
  font-family: var(--font-display, sans-serif);
  color: #0F172A;
  line-height: 1.1;
}

.kpi-label {
  font-size: 12px;
  color: #64748B;
  font-weight: 600;
}

/* 2. Chart Card */
.chart-card {
  padding: 20px 22px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.chart-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
}

.chart-eyebrow {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #0A5C38;
}

.chart-headline-row {
  display: flex;
  align-items: baseline;
  gap: 12px;
  margin-top: 2px;
}

.chart-title {
  font-size: 16px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
}

.chart-big-count {
  font-size: 13px;
  font-weight: 700;
  color: #059669;
  background: #ECFDF5;
  padding: 2px 8px;
  border-radius: 6px;
}

.chart-legend-box {
  display: flex;
  align-items: center;
  gap: 14px;
}

.legend-chip {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #64748B;
  font-weight: 600;
}

.legend-square {
  width: 10px;
  height: 10px;
  border-radius: 3px;
}

.legend-square--forest { background: #0A5C38; }
.legend-square--mint { background: #10B981; }

.svg-bar-chart-wrap {
  position: relative;
  width: 100%;
}

.svg-bar-chart {
  width: 100%;
  height: 180px;
  display: block;
}

.bar-group {
  cursor: pointer;
}

.bar-rect {
  transition: opacity 150ms ease, transform 150ms ease;
}

.bar-group:hover .bar-rect {
  opacity: 0.85;
}

.chart-tooltip {
  position: absolute;
  top: 10px;
  transform: translateX(-50%);
  background: #0F172A;
  color: #ffffff;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 11.5px;
  pointer-events: none;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  z-index: 10;
  white-space: nowrap;
}

.tooltip-header {
  font-weight: 700;
  margin-bottom: 3px;
  color: #CBD5E1;
}

.tooltip-row {
  display: flex;
  justify-content: space-between;
  gap: 10px;
}

.text-mint {
  color: #34D399;
}

/* 3. Table Card */
.table-card {
  padding: 20px 22px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.table-card-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}

.table-card-title {
  font-size: 15px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
}

.table-card-sub {
  font-size: 12px;
  color: #64748B;
}

.card-link {
  font-size: 12.5px;
  font-weight: 700;
  color: #0A5C38;
  text-decoration: none;
}

.card-link:hover {
  text-decoration: underline;
}

.table-wrap {
  overflow-x: auto;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.modern-table th {
  text-align: left;
  padding: 8px 12px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #64748B;
  border-bottom: 1px solid #E2E8F0;
}

.modern-table td {
  padding: 12px;
  border-bottom: 1px solid #F1F5F9;
  vertical-align: middle;
}

.visitor-col {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.visitor-name {
  font-weight: 700;
  color: #0F172A;
  text-decoration: none;
}

.visitor-name:hover {
  color: #0A5C38;
  text-decoration: underline;
}

.plate-badge {
  font-size: 10px;
  font-weight: 700;
  background: #F1F5F9;
  color: #475569;
  padding: 1px 6px;
  border-radius: 4px;
  align-self: flex-start;
  letter-spacing: 0.04em;
}

.unit-col {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.res-name {
  font-weight: 600;
  color: #334155;
}

.unit-tag {
  font-size: 11px;
  color: #64748B;
}

.status-cell {
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-pill {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
}

.badge-green { background: #DCFCE7; color: #166534; }
.badge-blue { background: #DBEAFE; color: #1E40AF; }
.badge-gray { background: #F1F5F9; color: #475569; }
.badge-red { background: #FEE2E2; color: #991B1B; }
.badge-yellow { background: #FEF3C7; color: #92400E; }

.dwell-tag {
  font-size: 10.5px;
  color: #059669;
  font-weight: 600;
}

.time-col {
  font-size: 12px;
  color: #64748B;
  white-space: nowrap;
}

/* ── Right Analytics Column ── */
.col-analytics {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* 1. Donut Card */
.donut-card {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.donut-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.donut-title {
  font-size: 14px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
}

.donut-period {
  font-size: 11px;
  font-weight: 600;
  color: #64748B;
  background: #F1F5F9;
  padding: 2px 7px;
  border-radius: 6px;
}

.donut-svg-wrap {
  display: flex;
  justify-content: center;
  padding: 4px 0;
}

.donut-svg {
  width: 150px;
  height: 150px;
  transform: rotate(-90deg);
}

.donut-svg text {
  transform: rotate(90deg);
  transform-origin: 80px 80px;
}

.donut-segment {
  transition: stroke-dasharray 400ms ease;
}

.donut-breakdown-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  border-top: 1px solid #F1F5F9;
  padding-top: 12px;
}

.donut-list-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
}

.row-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.color-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.dot--forest { background: #0A5C38; }
.dot--mint { background: #10B981; }
.dot--blue { background: #3B82F6; }
.dot--purple { background: #8B5CF6; }

.row-label {
  color: #475569;
  font-weight: 600;
}

.row-val {
  font-weight: 700;
  color: #0F172A;
}

/* 2. Timeline Card */
.timeline-card {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.timeline-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.timeline-title {
  font-size: 14px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
}

.card-link-sm {
  font-size: 11.5px;
  font-weight: 700;
  color: #0A5C38;
  text-decoration: none;
}

.timeline-feed {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.timeline-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.timeline-icon-box {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: #F1F5F9;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
}

.icon-danger { background: #FEE2E2; }
.icon-info { background: #E0F2FE; }

.timeline-body {
  flex: 1;
  min-width: 0;
}

.timeline-item-title {
  font-size: 12.5px;
  font-weight: 700;
  color: #0F172A;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.timeline-item-sub {
  font-size: 11.5px;
  color: #64748B;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.timeline-item-time {
  font-size: 10.5px;
  color: #94A3B8;
  margin-top: 1px;
}

.timeline-empty, .notice-empty {
  font-size: 12px;
  color: #64748B;
  padding: 12px 0;
  text-align: center;
}

/* 3. Notice Sidebar Card */
.notice-sidebar-card {
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: #FAFCF9;
  border: 1px solid #E2E8F0;
}

.notice-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.notice-eyebrow {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #0A5C38;
}

.btn-quick-post {
  background: #0A5C38;
  color: #ffffff;
  border: none;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  cursor: pointer;
  transition: opacity 150ms ease;
}

.btn-quick-post:hover {
  opacity: 0.9;
}

.notice-preview-box {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.notice-badge {
  font-size: 10px;
  font-weight: 700;
  color: #0369A1;
  background: #E0F2FE;
  padding: 1px 6px;
  border-radius: 4px;
  align-self: flex-start;
}

.notice-title {
  font-size: 13px;
  font-weight: 700;
  color: #0F172A;
  margin: 2px 0 0;
}

.notice-snippet {
  font-size: 11.5px;
  color: #475569;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}

.notice-meta {
  font-size: 10.5px;
  color: #059669;
  font-weight: 600;
  margin-top: 2px;
}

.notice-link {
  font-size: 11.5px;
  font-weight: 700;
  color: #0A5C38;
  text-decoration: none;
  align-self: flex-start;
  margin-top: 2px;
}

.notice-link:hover {
  text-decoration: underline;
}

/* ── Empty State ── */
.empty-state {
  text-align: center;
  padding: 32px 16px;
  color: #64748B;
  font-size: 13px;
}

.loading-state {
  display: flex;
  justify-content: center;
  padding: 60px 0;
}

/* ── Responsive Rules ── */
@media (max-width: 1280px) {
  .coinest-grid {
    grid-template-columns: 270px minmax(0, 1fr) 290px;
    gap: 16px;
  }
}

@media (max-width: 1080px) {
  .coinest-grid {
    grid-template-columns: minmax(0, 1fr) 300px;
  }
  .col-command {
    grid-column: 1 / -1;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 14px;
  }
}

@media (max-width: 768px) {
  .coinest-grid {
    grid-template-columns: 1fr;
  }
  .col-command {
    display: flex;
    flex-direction: column;
  }
  .kpi-row {
    grid-template-columns: 1fr;
  }
}

/* ── Modal Common Styles ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  z-index: 50;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.modal-box {
  background: #ffffff;
  border-radius: 20px;
  width: min(520px, 100%);
  padding: 24px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.modal-title {
  font-size: 18px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
}

.modal-sub {
  font-size: 12.5px;
  color: #64748B;
  margin: 2px 0 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 20px;
  color: #94A3B8;
  cursor: pointer;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 8px;
}

.modal-error {
  background: #FEF2F2;
  color: #DC2626;
  padding: 10px 14px;
  border-radius: 8px;
  font-size: 12.5px;
}
</style>
