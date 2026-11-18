<template>
  <div class="dashboard-page">
    <!-- Top Hero Header Container Card -->
    <section class="hero-card">
      <div class="hero-top-row">
        <div class="hero-badge-group">
          <span class="hero-eyebrow">Estate Command Center</span>
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

        <div class="hero-switcher-box">
          <EstateSwitcher variant="hero" />
        </div>
      </div>

      <div class="hero-main-row">
        <div class="hero-title-group">
          <h1 class="hero-title">Estate Command &amp; Security Overview</h1>
          <p class="hero-copy">Monitor real-time visitor gate access, resident activity, and emergency dispatch across your community.</p>
        </div>

        <div class="hero-actions-group">
          <router-link to="/passes" class="btn btn-hero-action">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <rect x="3" y="4" width="18" height="16" rx="2"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="12" y1="11" x2="12" y2="15"/>
              <line x1="10" y1="13" x2="14" y2="13"/>
            </svg>
            <span>Issue Pass</span>
          </router-link>

          <router-link to="/residents/new" class="btn btn-hero-action">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <line x1="19" y1="8" x2="19" y2="14"/>
              <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
            <span>Add Resident</span>
          </router-link>

          <router-link to="/properties" class="btn btn-hero-action">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Properties</span>
          </router-link>

          <button type="button" class="btn btn-hero-action btn-hero-action--subtle" @click="openBroadcastModal">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <span>Broadcast</span>
          </button>
        </div>
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
      <!-- ── 5-METRIC SPACIOUS KPI STAT CARDS ROW ── -->
      <section class="kpi-grid">
        <router-link to="/passes" class="kpi-card card">
          <div class="kpi-top">
            <div class="kpi-icon-pill kpi-icon--emerald">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="5" width="20" height="14" rx="3"/>
                <line x1="2" y1="10" x2="22" y2="10"/>
                <line x1="7" y1="15" x2="7.01" y2="15"/>
              </svg>
            </div>
            <span class="kpi-badge kpi-badge--green">+14% vs avg</span>
          </div>
          <div class="kpi-value">{{ stats.activePasses }}</div>
          <div class="kpi-label">Active Passes Today</div>
        </router-link>

        <router-link to="/residents" class="kpi-card card">
          <div class="kpi-top">
            <div class="kpi-icon-pill kpi-icon--blue">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
              </svg>
            </div>
            <span class="kpi-badge kpi-badge--blue">Verified</span>
          </div>
          <div class="kpi-value">{{ stats.totalResidents }}</div>
          <div class="kpi-label">Verified Residents</div>
        </router-link>

        <router-link to="/fees" class="kpi-card card">
          <div class="kpi-top">
            <div class="kpi-icon-pill kpi-icon--gold">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
            </div>
            <span class="kpi-badge kpi-badge--gold">{{ stats.collectionRate }}% Collected</span>
          </div>
          <div class="kpi-value">₦{{ fmtNaira(stats.duesCollected) }}</div>
          <div class="kpi-label">Estate Dues Revenue</div>
        </router-link>

        <router-link to="/properties" class="kpi-card card">
          <div class="kpi-top">
            <div class="kpi-icon-pill kpi-icon--teal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/>
              </svg>
            </div>
            <span class="kpi-badge kpi-badge--teal">{{ stats.occupiedUnits }}/{{ stats.totalUnits }} Units</span>
          </div>
          <div class="kpi-value">{{ stats.occupancyRate }}%</div>
          <div class="kpi-label">Unit Occupancy</div>
        </router-link>

        <router-link to="/emergencies" class="kpi-card card" :class="{ 'kpi-card--alert': stats.todayEmergencies > 0 }">
          <div class="kpi-top">
            <div class="kpi-icon-pill" :class="stats.todayEmergencies > 0 ? 'kpi-icon--red' : 'kpi-icon--green'">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              </svg>
            </div>
            <span class="kpi-badge" :class="stats.todayEmergencies > 0 ? 'kpi-badge--red' : 'kpi-badge--green'">
              {{ stats.todayEmergencies > 0 ? `${stats.todayEmergencies} Alert` : 'Secure' }}
            </span>
          </div>
          <div class="kpi-value" :class="{ 'text-danger': stats.todayEmergencies > 0 }">
            {{ stats.todayEmergencies === 0 ? 'All Clear' : `${stats.todayEmergencies} Alert` }}
          </div>
          <div class="kpi-label">Security Dispatch</div>
        </router-link>
      </section>

      <!-- ── 2-COLUMN OPEN & BREATHABLE OPERATIONS GRID ── -->
      <div class="dashboard-grid-2col">
        <!-- ── MAIN LEFT COLUMN (63%) ── -->
        <div class="dash-col-main">
          <!-- 1. Dual-Bar Influx Chart Card -->
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
                <line x1="20" y1="20" x2="500" y2="20" stroke="#F1F5F9" stroke-width="1" />
                <line x1="20" y1="60" x2="500" y2="60" stroke="#F1F5F9" stroke-width="1" />
                <line x1="20" y1="100" x2="500" y2="100" stroke="#F1F5F9" stroke-width="1" />
                <line x1="20" y1="140" x2="500" y2="140" stroke="#E2E8F0" stroke-width="1.2" />

                <g
                  v-for="(b, idx) in influxBarCoords"
                  :key="idx"
                  class="bar-group"
                  @mouseenter="hoveredBar = b"
                  @mouseleave="hoveredBar = null"
                >
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

          <!-- 2. Recent Passes Operational Table Card -->
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
        </div>

        <!-- ── SIDE RIGHT COLUMN (37%) ── -->
        <div class="dash-col-side">
          <!-- 1. Pass Distribution Donut Card -->
          <div class="card donut-card">
            <div class="donut-header">
              <h3 class="donut-title">Pass Distribution</h3>
              <span class="donut-period">30 Days</span>
            </div>

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

          <!-- 2. Resident Notices & Broadcasts Card -->
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
            <router-link to="/notifications?tab=announcements" class="notice-link">Manage announcements &rarr;</router-link>
          </div>

          <!-- 3. Real-Time Activity Feed Card -->
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
              placeholder="e.g. Scheduled Power Outage &amp; Generator Schedule"
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
import BrandSpinner from '@/components/BrandSpinner.vue'

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
      title: `${n.title || 'Notice Published'}`,
      subtitle: `${n.estateName || 'Estate Notice'}`,
      time: n.createdAt ? formatRelativeTime(n.createdAt) : 'Recently',
      icon: '📢',
      iconClass: 'icon-news',
    })
  }

  return items
})

const syncStatusLabel = computed(() => {
  if (isSyncing.value) return 'Syncing live telemetry…'
  if (secondsSinceSync.value === 0) return 'Just synced'
  if (secondsSinceSync.value < 60) return `Synced ${secondsSinceSync.value}s ago`
  const mins = Math.floor(secondsSinceSync.value / 60)
  return `Synced ${mins}m ago`
})

const isSyncing = ref(false)

async function manualSync() {
  await load(false)
  showToast('Live gate telemetry synced.', 'success')
}

async function load(showSpinner = true) {
  if (showSpinner) loading.value = true
  isSyncing.value = true
  try {
    const [summaryRes, estatesRes, newsRes] = await Promise.all([
      dashboardApi.getSummary(),
      estatesApi.getAll().catch(() => ({ data: { estates: [] } })),
      newsApi.getAll().catch(() => ({ data: { news: [] } })),
    ])

    if (summaryRes.data?.stats) {
      stats.value = { ...stats.value, ...summaryRes.data.stats }
    }
    if (summaryRes.data?.analytics) {
      analytics.value = summaryRes.data.analytics
    }
    recentPasses.value = summaryRes.data?.recentPasses || []
    recentEmergencies.value = summaryRes.data?.recentEmergencies || []
    recentNews.value = (newsRes.data?.news || []).slice(0, 3)
    estates.value = estatesRes.data?.estates ?? []

    if (!auth.activeEstateId && estates.value.length > 0) {
      auth.setActiveEstate(estates.value[0])
    }
    secondsSinceSync.value = 0
  } catch (err: any) {
    if (showSpinner) {
      showToast('Failed to load dashboard data.', 'error')
    }
  } finally {
    loading.value = false
    isSyncing.value = false
  }
}

async function handleCreateEstate() {
  estateError.value = ''
  creatingEstate.value = true
  try {
    const res = await onboardingApi.createEstate(newEstate.value)
    showToast('Estate registered successfully!', 'success')
    showAddEstateModal.value = false
    newEstate.value = { name: '', address: '', city: '', state: '' }
    if (res.data?.estate) {
      auth.setActiveEstate(res.data.estate)
    }
    await load()
  } catch (err: any) {
    estateError.value = err.response?.data?.message || 'Failed to create estate.'
  } finally {
    creatingEstate.value = false
  }
}

function passStatusClass(status: string) {
  switch (status) {
    case 'Pending': return 'status-pending'
    case 'Approved': return 'status-approved'
    case 'On-site': return 'status-onsite'
    case 'Used': return 'status-used'
    case 'Expired': return 'status-expired'
    case 'Denied': return 'status-denied'
    default: return 'status-default'
  }
}

function calcDwellTime(arrivedAt?: string): string {
  if (!arrivedAt) return ''
  const diffMs = Date.now() - new Date(arrivedAt).getTime()
  if (diffMs < 0) return ''
  const mins = Math.floor(diffMs / 60000)
  if (mins < 60) return `${mins}m on-site`
  const hours = Math.floor(mins / 60)
  return `${hours}h ${mins % 60}m on-site`
}

function formatRelativeTime(dt: string): string {
  const diffMs = Date.now() - new Date(dt).getTime()
  const mins = Math.floor(diffMs / 60000)
  if (mins < 1) return 'Just now'
  if (mins < 60) return `${mins}m ago`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours}h ago`
  return `${Math.floor(hours / 24)}d ago`
}

function fmtDate(dt?: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  load()
  tickerInterval = setInterval(() => {
    secondsSinceSync.value += 1
  }, 1000)
  syncInterval = setInterval(() => {
    load(false)
  }, 30000)
})

onUnmounted(() => {
  if (syncInterval) clearInterval(syncInterval)
  if (tickerInterval) clearInterval(tickerInterval)
})
</script>

<style scoped>
.dashboard-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-width: 1400px;
  margin: 0 auto;
}

/* ── Top Hero Container Card ── */
.hero-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 24px 28px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.hero-top-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  flex-wrap: wrap;
  gap: 12px;
}

.hero-badge-group {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.hero-eyebrow {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #0A5C38;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  padding: 3px 9px;
  border-radius: 999px;
}

.gate-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  font-weight: 600;
  color: #065F46;
  background: #F0FDF4;
  border: 1px solid #DCFCE7;
  padding: 3px 10px;
  border-radius: 999px;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #10B981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.25);
}

.telemetry-sync-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  color: #6B7280;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  padding: 2px 8px;
  border-radius: 999px;
}

.telemetry-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #9CA3AF;
}

.telemetry-dot--syncing {
  background: #3B82F6;
  animation: pulse 1s infinite;
}

.btn-telemetry-sync {
  background: transparent;
  border: none;
  font-size: 11px;
  font-weight: 700;
  color: #0A5C38;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 0 2px;
}

.btn-telemetry-sync:hover {
  text-decoration: underline;
}

.spin-anim {
  animation: spin 1s linear infinite;
}

.hero-main-row {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
}

.hero-title-group {
  max-width: 680px;
}

.hero-title {
  font-size: 24px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.02em;
  margin: 0 0 6px;
}

.hero-copy {
  font-size: 13.5px;
  color: #4B5563;
  margin: 0;
  line-height: 1.5;
}

.hero-actions-group {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-hero-action {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 14px;
  border-radius: 9px;
  text-decoration: none;
  background: #ffffff;
  color: #374151;
  border: 1px solid #D1D5DB;
  cursor: pointer;
  transition: all 140ms ease;
}

.btn-hero-action:hover {
  background: #F9FAFB;
  border-color: #9CA3AF;
  color: #111827;
  transform: translateY(-1px);
}

.btn-hero-action--subtle {
  background: #ECFDF5;
  color: #0A5C38;
  border-color: #A7F3D0;
}

.btn-hero-action--subtle:hover {
  background: #D1FAE5;
  border-color: #6EE7B7;
}

/* ── KPI Stat Cards ── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
}

.kpi-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  padding: 18px 20px;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  transition: all 160ms cubic-bezier(0.16, 1, 0.3, 1);
}

.kpi-card:hover {
  border-color: #D1D5DB;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
  transform: translateY(-2px);
}

.kpi-card--alert {
  border-color: #FCA5A5;
  background: #FFFBFB;
}

.kpi-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.kpi-icon-pill {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.kpi-icon--emerald { background: #ECFDF5; color: #059669; }
.kpi-icon--blue    { background: #EFF6FF; color: #2563EB; }
.kpi-icon--gold    { background: #FEF3C7; color: #D97706; }
.kpi-icon--teal    { background: #F0FDFA; color: #0D9488; }
.kpi-icon--green   { background: #DCFCE7; color: #16A34A; }
.kpi-icon--red     { background: #FEE2E2; color: #DC2626; }

.kpi-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
}

.kpi-badge--green { background: #ECFDF5; color: #065F46; }
.kpi-badge--blue  { background: #EFF6FF; color: #1E40AF; }
.kpi-badge--gold  { background: #FEF3C7; color: #92400E; }
.kpi-badge--teal  { background: #F0FDFA; color: #115E59; }
.kpi-badge--red   { background: #FEE2E2; color: #991B1B; }

.kpi-value {
  font-size: 24px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.03em;
  line-height: 1.1;
}

.kpi-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #6B7280;
  margin-top: 4px;
}

.text-danger { color: #DC2626 !important; }

/* ── 2-Column Main Operations Grid ── */
.dashboard-grid-2col {
  display: grid;
  grid-template-columns: 1fr 390px;
  gap: 20px;
  align-items: start;
}

.dash-col-main, .dash-col-side {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ── Chart Card ── */
.chart-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 22px 24px;
  position: relative;
}

.chart-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 16px;
  flex-wrap: wrap;
}

.chart-eyebrow {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #0A5C38;
}

.chart-headline-row {
  display: flex;
  align-items: baseline;
  gap: 12px;
  margin-top: 2px;
}

.chart-title {
  font-size: 17px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.chart-big-count {
  font-size: 12px;
  font-weight: 700;
  color: #065F46;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  padding: 2px 8px;
  border-radius: 999px;
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
  color: #4B5563;
  font-weight: 500;
}

.legend-square {
  width: 9px;
  height: 9px;
  border-radius: 2px;
}

.legend-square--forest { background: #0A5C38; }
.legend-square--mint   { background: #10B981; }

.svg-bar-chart-wrap {
  width: 100%;
  position: relative;
}

.svg-bar-chart {
  width: 100%;
  height: 200px;
}

.bar-rect {
  transition: opacity 140ms ease;
  cursor: pointer;
}

.bar-group:hover .bar-rect {
  opacity: 0.82;
}

.chart-tooltip {
  position: absolute;
  top: 15px;
  transform: translateX(-50%);
  background: #111827;
  color: #ffffff;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  pointer-events: none;
  z-index: 10;
  white-space: nowrap;
}

.tooltip-header { font-weight: 700; margin-bottom: 2px; color: #F3F4F6; font-size: 11px; }
.tooltip-row { display: flex; gap: 6px; font-size: 11.5px; }
.text-mint { color: #34D399 !important; }

/* ── Table Card ── */
.table-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 20px 24px;
}

.table-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.table-card-title {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.table-card-sub {
  font-size: 12px;
  color: #6B7280;
}

.card-link {
  font-size: 12.5px;
  font-weight: 600;
  color: #0A5C38;
  text-decoration: none;
}

.card-link:hover { text-decoration: underline; }

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
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6B7280;
  padding: 10px 12px;
  border-bottom: 1px solid #E5E7EB;
}

.modern-table td {
  padding: 12px;
  border-bottom: 1px solid #F3F4F6;
  color: #374151;
}

.modern-table tr:last-child td { border-bottom: none; }

.visitor-col { display: flex; flex-direction: column; gap: 2px; }
.visitor-name { font-weight: 700; color: #111827; text-decoration: none; }
.visitor-name:hover { text-decoration: underline; }
.plate-badge { font-size: 10.5px; color: #4B5563; background: #F3F4F6; padding: 1px 6px; border-radius: 4px; width: fit-content; }

.unit-col { display: flex; flex-direction: column; gap: 2px; }
.res-name { font-weight: 600; color: #1F2937; }
.unit-tag { font-size: 11.5px; color: #6B7280; }

.status-cell { display: flex; align-items: center; gap: 6px; }
.status-pill { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; }
.status-pending { background: #FEF3C7; color: #92400E; }
.status-approved{ background: #EFF6FF; color: #1E40AF; }
.status-onsite  { background: #ECFDF5; color: #065F46; }
.status-used    { background: #F3F4F6; color: #4B5563; }
.status-expired { background: #FEE2E2; color: #991B1B; }
.status-denied  { background: #FEE2E2; color: #991B1B; }

.dwell-tag { font-size: 10.5px; font-weight: 600; color: #047857; }
.time-col { font-size: 12px; color: #6B7280; }

/* ── Donut Card ── */
.donut-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 22px;
}

.donut-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.donut-title { font-size: 15px; font-weight: 700; color: #111827; margin: 0; }
.donut-period { font-size: 11px; font-weight: 600; color: #6B7280; background: #F3F4F6; padding: 2px 7px; border-radius: 6px; }

.donut-svg-wrap {
  display: flex;
  justify-content: center;
  margin: 10px 0;
}

.donut-svg {
  width: 140px;
  height: 140px;
  transform: rotate(-90deg);
}

.donut-segment { transition: stroke-dashoffset 400ms ease; }

.donut-breakdown-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 12px;
}

.donut-list-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12.5px;
}

.row-left { display: flex; align-items: center; gap: 7px; color: #4B5563; }
.color-dot { width: 8px; height: 8px; border-radius: 50%; }
.dot--forest { background: #0A5C38; }
.dot--mint   { background: #10B981; }
.dot--blue   { background: #3B82F6; }
.dot--purple { background: #8B5CF6; }
.row-val { font-weight: 700; color: #111827; }

/* ── Resident Notices Card ── */
.notice-sidebar-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 20px;
}

.notice-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.notice-eyebrow {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  color: #111827;
  letter-spacing: 0.04em;
}

.btn-quick-post {
  background: #ECFDF5;
  color: #0A5C38;
  border: 1px solid #A7F3D0;
  font-size: 11.5px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  cursor: pointer;
}

.btn-quick-post:hover { background: #D1FAE5; }

.notice-preview-box {
  background: #F9FAFB;
  border: 1px solid #F3F4F6;
  border-radius: 10px;
  padding: 12px 14px;
}

.notice-badge {
  font-size: 10.5px;
  font-weight: 700;
  color: #0A5C38;
  margin-bottom: 4px;
}

.notice-title {
  font-size: 14px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 4px;
}

.notice-snippet {
  font-size: 12.5px;
  color: #4B5563;
  margin: 0 0 8px;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.notice-meta {
  font-size: 11px;
  color: #9CA3AF;
}

.notice-empty {
  font-size: 12.5px;
  color: #9CA3AF;
  font-style: italic;
  padding: 8px 0;
}

.notice-link {
  display: inline-block;
  font-size: 12px;
  font-weight: 600;
  color: #0A5C38;
  text-decoration: none;
  margin-top: 10px;
}

.notice-link:hover { text-decoration: underline; }

/* ── Timeline Card ── */
.timeline-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 20px;
}

.timeline-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.timeline-title { font-size: 15px; font-weight: 700; color: #111827; margin: 0; }
.card-link-sm { font-size: 11.5px; font-weight: 600; color: #0A5C38; text-decoration: none; }
.card-link-sm:hover { text-decoration: underline; }

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
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  flex-shrink: 0;
  background: #F3F4F6;
}

.timeline-body { flex: 1; }
.timeline-item-title { font-size: 12.5px; font-weight: 600; color: #111827; }
.timeline-item-sub { font-size: 11.5px; color: #6B7280; }
.timeline-item-time { font-size: 10.5px; color: #9CA3AF; margin-top: 2px; }

.timeline-empty { font-size: 12.5px; color: #9CA3AF; font-style: italic; }

/* ── Generic UI / Modals ── */
.estate-setup-banner {
  background: #FEF3C7;
  border: 1px solid #FDE68A;
  border-radius: 16px;
  padding: 22px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.setup-badge {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #B45309;
}

.estate-setup-banner h2 { font-size: 18px; font-weight: 800; color: #92400E; margin: 4px 0; }
.estate-setup-banner p { font-size: 13.5px; color: #78350F; margin: 0; }

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(3px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-box {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.modal-header {
  padding: 18px 24px;
  border-bottom: 1px solid #F3F4F6;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-title { font-size: 18px; font-weight: 700; color: #111827; margin: 0; }
.modal-sub { font-size: 12.5px; color: #6B7280; margin: 2px 0 0; }

.modal-close {
  background: transparent;
  border: none;
  font-size: 20px;
  color: #9CA3AF;
  cursor: pointer;
}

.modal-form {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-label { font-size: 12.5px; font-weight: 600; color: #374151; }

.form-input, .form-textarea, .form-select {
  width: 100%;
  font-size: 13.5px;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #D1D5DB;
  background: #ffffff;
  color: #111827;
  font-family: inherit;
}

.form-input:focus, .form-textarea:focus, .form-select:focus {
  outline: none;
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.1);
}

.modal-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  border: 1px solid transparent;
}

.btn-primary { background: #0A5C38; color: #ffffff; }
.btn-primary:hover { background: #08482C; }
.btn-secondary { background: #F3F4F6; color: #374151; border-color: #E5E7EB; }
.btn-secondary:hover { background: #E5E7EB; }
.modal-error { color: #DC2626; font-size: 12.5px; }

/* ── Responsive ── */
@media (max-width: 1180px) {
  .kpi-grid { grid-template-columns: repeat(3, 1fr); }
  .dashboard-grid-2col { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .hero-main-row { flex-direction: column; align-items: flex-start; }
  .hero-actions-group { width: 100%; }
}

@media (max-width: 480px) {
  .kpi-grid { grid-template-columns: 1fr; }
}
</style>
