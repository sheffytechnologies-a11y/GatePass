<template>
  <div class="mobile-page notifications-page">
    <!-- Top Hero Header Container -->
    <section class="hero-card hero-card--mint">
      <div class="hero-header-flex">
        <div class="hero-titles">
          <div class="hero-eyebrow">Estate Communications &amp; Dispatch</div>
          <h1 class="hero-title">Notifications &amp; Announcements</h1>
          <p class="hero-copy">
            Monitor real-time system alerts and broadcast official announcements, security advisories, and maintenance schedules directly to resident mobile feeds.
          </p>
        </div>

        <div v-if="activeTab === 'announcements'" class="hero-actions">
          <button type="button" class="btn btn-gold hero-cta-btn" @click="openCreateAnnouncementModal">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
            </svg>
            <span>New Announcement</span>
          </button>
        </div>
      </div>

      <!-- Tab Switcher Bar -->
      <div class="tab-pill-bar">
        <button
          type="button"
          class="nav-tab-pill"
          :class="{ active: activeTab === 'notifications' }"
          @click="switchTab('notifications')"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 8a6 6 0 0 1 12 0c0 6.5 2.5 8.5 2.5 8.5h-17S6 14.5 6 8z"/>
            <path d="M10 20.5a2 2 0 0 0 4 0"/>
          </svg>
          <span>System Notifications</span>
          <span class="tab-counter">{{ notifications.length }}</span>
        </button>

        <button
          type="button"
          class="nav-tab-pill"
          :class="{ active: activeTab === 'announcements' }"
          @click="switchTab('announcements')"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
          </svg>
          <span>Announcements &amp; Broadcasts</span>
          <span class="tab-counter">{{ newsList.length }}</span>
        </button>

        <button
          type="button"
          class="nav-tab-pill nav-tab-pill--emergency"
          :class="{ active: activeTab === 'emergencies', 'has-alert': activeEmergencyCount > 0 }"
          @click="switchTab('emergencies')"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 3l10 18H2L12 3z" stroke-linejoin="round"/>
            <path d="M12 9.5v4.5M12 17h.01" stroke-linecap="round"/>
          </svg>
          <span>Emergency Alerts</span>
          <span class="tab-counter" :class="{ 'tab-counter--danger': activeEmergencyCount > 0 }">
            {{ activeEmergencyCount > 0 ? `${activeEmergencyCount} Active` : emergenciesList.length }}
          </span>
        </button>
      </div>
    </section>

    <!-- ── TAB 1: SYSTEM NOTIFICATIONS ── -->
    <div v-if="activeTab === 'notifications'" class="tab-content">
      <div class="section-head">
        <div>
          <h2 class="section-title">Automated System &amp; Gate Alerts</h2>
          <p class="section-copy">{{ notifications.length }} message{{ notifications.length === 1 ? '' : 's' }} recorded across gate and resident channels.</p>
        </div>
        <div class="section-controls">
          <button
            type="button"
            class="btn btn-sm btn-outline"
            :class="{ active: notifFilter === 'all' }"
            @click="notifFilter = 'all'"
          >
            All ({{ notifications.length }})
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline"
            :class="{ active: notifFilter === 'unread' }"
            @click="notifFilter = 'unread'"
          >
            Unread ({{ unreadCount }})
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline refresh-btn"
            :disabled="loadingNotifications"
            @click="loadNotifications"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="{ spinning: loadingNotifications }">
              <path d="M23 4v6h-6M1 20v-6h6"/>
              <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/>
            </svg>
            Refresh
          </button>
        </div>
      </div>

      <div v-if="loadingNotifications" class="loading-state">
        <BrandSpinner :size="40" color="#0A5C38" />
        <p>Loading notifications...</p>
      </div>

      <div v-else-if="filteredNotifications.length === 0" class="empty-state card">
        <div class="empty-icon">🔔</div>
        <h3>No Notifications Found</h3>
        <p>{{ notifFilter === 'unread' ? 'You have read all notifications!' : 'No system notifications recorded yet.' }}</p>
      </div>

      <div v-else class="stack-list">
        <article
          v-for="notification in filteredNotifications"
          :key="notification.id"
          class="record-card card"
          :class="{ unread: !notification.read }"
        >
          <div class="record-top">
            <div class="record-badges">
              <span :class="typeClass(notification.type)" class="badge">{{ notification.type || 'Notice' }}</span>
              <span :class="notification.read ? 'badge badge-green' : 'badge badge-yellow'">
                {{ notification.read ? 'Read' : 'Unread' }}
              </span>
            </div>
            <button
              class="btn btn-sm btn-danger-subtle"
              :disabled="deletingNotifId === notification.id"
              @click="confirmDeleteNotification(notification)"
            >
              Delete
            </button>
          </div>
          <p class="message-body">{{ notification.message }}</p>
          <div class="record-bottom">
            <span class="record-sub">
              <strong>Resident:</strong> {{ notification.resident?.name ?? 'Community Broadcast' }}
            </span>
            <span class="record-sub">{{ fmtDatetime(notification.createdAt) }}</span>
          </div>
        </article>
      </div>
    </div>

    <!-- ── TAB 2: ANNOUNCEMENTS & BROADCASTS ── -->
    <div v-else-if="activeTab === 'announcements'" class="tab-content">
      <!-- Search & Estate Filter Toolbar -->
      <div class="filters-toolbar card">
        <div class="search-input-wrap">
          <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <path d="M21 21l-4.35-4.35"/>
          </svg>
          <input
            v-model="announcementSearch"
            type="search"
            placeholder="Search announcements by title or content..."
            class="search-input"
            @input="handleAnnouncementSearch"
          />
        </div>

        <div v-if="estates.length > 1" class="estate-filter-wrap">
          <select v-model="selectedEstateId" class="estate-select" @change="loadNews">
            <option :value="null">All Estates ({{ estates.length }})</option>
            <option v-for="e in estates" :key="e.id" :value="e.id">{{ e.name }}</option>
          </select>
        </div>

        <div class="audience-filter-wrap">
          <select v-model="selectedAudience" class="estate-select" @change="loadNews">
            <option value="all">All Audiences (Everyone)</option>
            <option value="residents">🏠 Residents &amp; Tenants</option>
            <option value="homeowners">🔑 Homeowners</option>
            <option value="security">🛡️ Security Officers</option>
            <option value="staff">👔 Estate Staff</option>
          </select>
        </div>
      </div>

      <div class="section-head">
        <div>
          <h2 class="section-title">Published Resident Announcements</h2>
          <p class="section-copy">{{ newsList.length }} announcement{{ newsList.length === 1 ? '' : 's' }} active in resident mobile app feeds.</p>
        </div>
        <button type="button" class="btn btn-primary hero-cta-btn" @click="openCreateAnnouncementModal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
          </svg>
          <span>New Announcement</span>
        </button>
      </div>

      <div v-if="loadingNews" class="loading-state">
        <BrandSpinner :size="40" color="#0A5C38" />
        <p>Loading estate announcements...</p>
      </div>

      <div v-else-if="newsList.length === 0" class="empty-state card">
        <div class="empty-icon">📢</div>
        <h3>No Announcements Published Yet</h3>
        <p>Keep your community informed about facility maintenance, security updates, estate dues, or general meetings.</p>
        <button class="btn btn-primary" @click="openCreateAnnouncementModal">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M12 5v14M5 12h14" stroke-linecap="round"/>
          </svg>
          <span>Create First Announcement</span>
        </button>
      </div>

      <div v-else class="news-grid">
        <article v-for="item in newsList" :key="item.id" class="news-card card">
          <div class="news-card-header">
            <div class="news-card-meta">
              <span class="estate-badge">{{ item.estateName || 'Estate Announcement' }}</span>
              <span class="aud-pill" :class="`aud-pill--${item.targetAudience || 'all'}`">
                {{ audienceLabel(item.targetAudience) }}
              </span>
              <span class="news-date">{{ fmtDate(item.createdAt) }}</span>
            </div>
            <div class="news-card-actions">
              <button class="icon-btn edit-btn" title="Edit Announcement" @click="openEditAnnouncementModal(item)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </button>
              <button class="icon-btn delete-btn" title="Delete Announcement" @click="confirmDeleteAnnouncement(item)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                </svg>
              </button>
            </div>
          </div>

          <h3 class="news-item-title">{{ item.title }}</h3>
          <p class="news-item-preview">{{ item.content }}</p>

          <div class="news-card-footer">
            <span class="author-tag">Posted by {{ item.adminName || 'Estate Management' }}</span>
            <span class="status-live-pill">
              <span class="pulse-dot"></span>
              Live in App
            </span>
          </div>
        </article>
      </div>
    </div>

    <!-- ── TAB 3: RESIDENT EMERGENCY ALERTS ── -->
    <div v-else-if="activeTab === 'emergencies'" class="tab-content">
      <div class="section-head">
        <div>
          <h2 class="section-title">Resident Emergency SOS Incidents</h2>
          <p class="section-copy">
            {{ emergenciesList.length }} incident report{{ emergenciesList.length === 1 ? '' : 's' }} logged from resident mobile panic triggers.
          </p>
        </div>
        <div class="section-controls">
          <button
            type="button"
            class="btn btn-sm btn-outline"
            :class="{ active: emergencyFilter === '' }"
            @click="setEmergencyFilter('')"
          >
            All ({{ emergenciesList.length }})
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline"
            :class="{ active: emergencyFilter === 'sent' }"
            @click="setEmergencyFilter('sent')"
          >
            Active SOS ({{ activeEmergencyCount }})
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline"
            :class="{ active: emergencyFilter === 'acknowledged' }"
            @click="setEmergencyFilter('acknowledged')"
          >
            Acknowledged
          </button>
          <button
            type="button"
            class="btn btn-sm btn-outline"
            :class="{ active: emergencyFilter === 'resolved' }"
            @click="setEmergencyFilter('resolved')"
          >
            Resolved
          </button>
        </div>
      </div>

      <div v-if="loadingEmergencies" class="loading-state">
        <BrandSpinner :size="40" color="#DC2626" />
        <p>Checking active emergency broadcasts...</p>
      </div>

      <div v-else-if="filteredEmergencies.length === 0" class="empty-state card">
        <div class="empty-icon">🛡️</div>
        <h3>No Emergency Incidents</h3>
        <p>{{ emergencyFilter ? 'No incidents match this status.' : 'All resident sectors are secure. Any panic alerts triggered by residents will appear here in real-time.' }}</p>
      </div>

      <div v-else class="stack-list">
        <article
          v-for="incident in filteredEmergencies"
          :key="incident.id"
          class="record-card card emergency-card"
          :class="{ urgent: incident.status === 'sent' }"
        >
          <div class="record-top">
            <div class="record-badges">
              <span class="badge" :class="emergencyTypeBadge(incident.type)">
                {{ emergencyTypeIcon(incident.type) }} {{ incident.type }}
              </span>
              <span class="badge" :class="emergencyStatusBadge(incident.status)">
                <span v-if="incident.status === 'sent'" class="pulse-dot-red"></span>
                {{ incident.status === 'sent' ? 'Pending Dispatch' : incident.status }}
              </span>
            </div>
            <div class="emergency-actions-row">
              <button
                v-if="incident.status === 'sent'"
                class="btn btn-sm btn-outline emergency-btn-ack"
                :disabled="savingEmergencyId === incident.id"
                @click="acknowledgeEmergency(incident)"
              >
                Acknowledge
              </button>
              <button
                v-if="incident.status !== 'resolved'"
                class="btn btn-sm btn-primary emergency-btn-resolve"
                :disabled="savingEmergencyId === incident.id"
                @click="resolveEmergency(incident)"
              >
                Resolve
              </button>
              <button
                class="btn btn-sm btn-danger-subtle"
                :disabled="savingEmergencyId === incident.id"
                @click="confirmDeleteEmergency(incident)"
              >
                Delete
              </button>
            </div>
          </div>

          <div class="emergency-resident-block">
            <div class="resident-detail-chip">
              <span class="chip-lbl">Resident:</span>
              <strong class="chip-val">{{ incident.resident?.name || 'Estate Resident' }}</strong>
            </div>
            <div v-if="incident.resident?.flatAddress" class="resident-detail-chip">
              <span class="chip-lbl">Unit / Flat:</span>
              <strong class="chip-val">{{ incident.resident.flatAddress }}</strong>
            </div>
            <div v-if="incident.resident?.phone" class="resident-detail-chip">
              <span class="chip-lbl">Phone:</span>
              <a :href="`tel:${incident.resident.phone}`" class="chip-link">{{ incident.resident.phone }}</a>
            </div>
          </div>

          <p class="incident-notes">{{ incident.notes || 'Emergency distress trigger dispatched from resident app.' }}</p>

          <div class="record-bottom">
            <span class="record-sub">
              <strong>Reported:</strong> {{ fmtDatetime(incident.createdAt) }}
            </span>
            <span v-if="incident.estate?.name" class="record-sub">
              {{ incident.estate.name }}
            </span>
          </div>
        </article>
      </div>
    </div>

    <!-- ── MODAL: DELETE NOTIFICATION ── -->
    <div v-if="deleteNotifTarget" class="sheet-overlay" @click.self="deleteNotifTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Delete Notification</div>
            <h3 class="sheet-title">Remove this message?</h3>
          </div>
          <button class="sheet-close" @click="deleteNotifTarget = null">&times;</button>
        </div>
        <p class="confirm-copy">{{ deleteNotifTarget.message }}</p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="deleteNotifTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="deletingNotifId !== null" @click="doDeleteNotification">
            {{ deletingNotifId !== null ? 'Deleting...' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── MODAL: CREATE / EDIT ANNOUNCEMENT ── -->
    <div v-if="showAnnouncementModal" class="sheet-overlay" @click.self="showAnnouncementModal = false">
      <div class="sheet sheet-md card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">{{ isEditingAnnouncement ? 'Update Announcement' : 'New Broadcast Notice' }}</div>
            <h3 class="sheet-title">{{ isEditingAnnouncement ? 'Edit Announcement' : 'Publish Community Notice' }}</h3>
          </div>
          <button class="sheet-close" @click="showAnnouncementModal = false">&times;</button>
        </div>

        <form @submit.prevent="saveAnnouncement" class="sheet-form">
          <div v-if="estates.length > 1" class="form-group">
            <label class="form-label">Target Estate Community *</label>
            <select v-model="announcementForm.estate_id" class="form-input" required>
              <option v-for="e in estates" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
          </div>

          <!-- Target Audience -->
          <div class="form-group">
            <label class="form-label">Target Audience *</label>
            <select v-model="announcementForm.target_audience" class="form-input" required>
              <option v-for="aud in audienceChoices" :key="aud.key" :value="aud.key">
                {{ aud.icon }} {{ aud.label }} &mdash; {{ aud.desc }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Announcement Title *</label>
            <input
              v-model="announcementForm.title"
              type="text"
              class="form-input"
              placeholder="e.g. Scheduled Generator Maintenance & Power Switch"
              required
            />
          </div>

          <div class="form-group">
            <label class="form-label">Notice Message &amp; Content *</label>
            <textarea
              v-model="announcementForm.content"
              rows="5"
              class="form-textarea"
              placeholder="Provide detailed instructions, time windows, or guidelines..."
              required
            ></textarea>
          </div>

          <div class="sheet-actions">
            <button type="button" class="btn btn-outline" @click="showAnnouncementModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="savingAnnouncement">
              {{ savingAnnouncement ? 'Publishing...' : (isEditingAnnouncement ? 'Save Changes' : 'Publish') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── MODAL: DELETE ANNOUNCEMENT ── -->
    <div v-if="deleteAnnouncementTarget" class="sheet-overlay" @click.self="deleteAnnouncementTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Delete Announcement</div>
            <h3 class="sheet-title">Remove this broadcast?</h3>
          </div>
          <button class="sheet-close" @click="deleteAnnouncementTarget = null">&times;</button>
        </div>
        <p class="confirm-copy">
          Are you sure you want to delete <strong>&ldquo;{{ deleteAnnouncementTarget.title }}&rdquo;</strong>? It will immediately disappear from resident feeds.
        </p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="deleteAnnouncementTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="deletingAnnouncement" @click="doDeleteAnnouncement">
            {{ deletingAnnouncement ? 'Deleting...' : 'Delete Broadcast' }}
          </button>
        </div>
      </div>
    </div>

    <!-- ── MODAL: DELETE EMERGENCY ── -->
    <div v-if="deleteEmergencyTarget" class="sheet-overlay" @click.self="deleteEmergencyTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Delete Emergency Record</div>
            <h3 class="sheet-title">Remove {{ deleteEmergencyTarget.type }} alert?</h3>
          </div>
          <button class="sheet-close" @click="deleteEmergencyTarget = null">&times;</button>
        </div>
        <p class="confirm-copy">
          This incident record from <strong>{{ deleteEmergencyTarget.resident?.name || 'Resident' }}</strong> will be permanently removed.
        </p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="deleteEmergencyTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="deletingEmergency" @click="doDeleteEmergency">
            {{ deletingEmergency ? 'Deleting...' : 'Delete Incident' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { notificationsApi, newsApi, estatesApi, emergenciesApi } from '@/api/index'
import { useToast } from '@/composables/useToast'
import BrandSpinner from '@/components/BrandSpinner.vue'

const route = useRoute()
const router = useRouter()
const { showToast } = useToast()

const activeTab = ref<'notifications' | 'announcements' | 'emergencies'>('notifications')

// Watch route query to switch tabs cleanly
watch(
  () => [route.query.tab, route.path],
  () => {
    if (route.query.tab === 'emergencies' || route.path.startsWith('/emergencies')) {
      activeTab.value = 'emergencies'
      loadEmergencies()
    } else if (route.query.tab === 'announcements' || route.path.startsWith('/news')) {
      activeTab.value = 'announcements'
    } else {
      activeTab.value = 'notifications'
    }
  },
  { immediate: true }
)

function switchTab(tab: 'notifications' | 'announcements' | 'emergencies') {
  activeTab.value = tab
  if (tab === 'emergencies') {
    loadEmergencies()
  } else if (tab === 'announcements') {
    loadNews()
  } else {
    loadNotifications()
  }
  router.replace({ path: '/notifications', query: tab !== 'notifications' ? { tab } : {} })
}

// ── NOTIFICATIONS STATE ──
const loadingNotifications = ref(true)
const notifications = ref<any[]>([])
const notifFilter = ref<'all' | 'unread'>('all')
const deletingNotifId = ref<number | null>(null)
const deleteNotifTarget = ref<any>(null)

const unreadCount = computed(() => notifications.value.filter(n => !n.read).length)

const filteredNotifications = computed(() => {
  if (notifFilter.value === 'unread') {
    return notifications.value.filter(n => !n.read)
  }
  return notifications.value
})

async function loadNotifications() {
  loadingNotifications.value = true
  try {
    const res = await notificationsApi.getAll()
    notifications.value = res.data?.notifications || []
  } catch {
    showToast('Failed to load notifications.', 'error')
  } finally {
    loadingNotifications.value = false
  }
}

function confirmDeleteNotification(n: any) {
  deleteNotifTarget.value = n
}

async function doDeleteNotification() {
  if (!deleteNotifTarget.value) return
  deletingNotifId.value = deleteNotifTarget.value.id
  try {
    await notificationsApi.delete(deleteNotifTarget.value.id)
    showToast('Notification deleted.', 'success')
    deleteNotifTarget.value = null
    loadNotifications()
  } catch {
    showToast('Failed to delete notification.', 'error')
  } finally {
    deletingNotifId.value = null
  }
}

function typeClass(type: string) {
  const map: Record<string, string> = {
    emergency: 'badge-red',
    pass: 'badge-blue',
    billing: 'badge-gold',
    general: 'badge-green',
  }
  return map[type?.toLowerCase()] ?? 'badge-gray'
}

function fmtDatetime(dt?: string | null) {
  if (!dt) return '—'
  return new Date(dt).toLocaleString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// ── ANNOUNCEMENTS / NEWS STATE ──
interface NewsItem {
  id: number
  estateId: number
  estateName?: string
  adminId: number
  adminName?: string
  title: string
  content: string
  targetAudience?: string
  image?: string | null
  createdAt?: string
  updatedAt?: string
}

const audienceChoices = [
  { key: 'all', label: 'All Community', icon: '🌐', desc: 'Broadcast to everyone across estate' },
  { key: 'residents', label: 'Residents & Tenants', icon: '🏠', desc: 'Active household occupants & tenants' },
  { key: 'homeowners', label: 'Homeowners', icon: '🔑', desc: 'Property landlords & deed holders' },
  { key: 'security', label: 'Security Officers', icon: '🛡️', desc: 'Gate guards & security dispatch' },
  { key: 'staff', label: 'Estate Staff', icon: '👔', desc: 'Estate management & office personnel' },
]

function audienceLabel(aud?: string): string {
  switch (aud) {
    case 'security': return 'Security'
    case 'staff': return 'Staff'
    case 'residents': return 'Residents & Tenants'
    case 'homeowners': return 'Homeowners'
    default: return 'All Community'
  }
}

const loadingNews = ref(true)
const savingAnnouncement = ref(false)
const deletingAnnouncement = ref(false)
const newsList = ref<NewsItem[]>([])
const estates = ref<any[]>([])
const selectedEstateId = ref<number | null>(null)
const selectedAudience = ref('all')
const announcementSearch = ref('')
let searchTimeout: any = null

const showAnnouncementModal = ref(false)
const isEditingAnnouncement = ref(false)
const editingAnnouncementId = ref<number | null>(null)
const deleteAnnouncementTarget = ref<NewsItem | null>(null)

const announcementForm = ref({
  estate_id: 1,
  title: '',
  content: '',
  target_audience: 'all',
})

async function loadEstates() {
  try {
    const res = await estatesApi.getAll()
    estates.value = res.data?.estates || []
    if (estates.value.length > 0 && !announcementForm.value.estate_id) {
      announcementForm.value.estate_id = estates.value[0].id
    }
  } catch {
    // fallback
  }
}

async function loadNews() {
  loadingNews.value = true
  try {
    const params: Record<string, unknown> = {}
    if (selectedEstateId.value) params.estate_id = selectedEstateId.value
    if (selectedAudience.value && selectedAudience.value !== 'all') params.target_audience = selectedAudience.value
    if (announcementSearch.value.trim()) params.search = announcementSearch.value.trim()
    const res = await newsApi.getAll(params)
    newsList.value = res.data?.news || []
  } catch {
    showToast('Failed to load announcements.', 'error')
  } finally {
    loadingNews.value = false
  }
}

function handleAnnouncementSearch() {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadNews()
  }, 300)
}

function openCreateAnnouncementModal() {
  isEditingAnnouncement.value = false
  editingAnnouncementId.value = null
  const defaultEstate = selectedEstateId.value || (estates.value[0]?.id ?? 1)
  announcementForm.value = {
    estate_id: defaultEstate,
    title: '',
    content: '',
    target_audience: 'all',
  }
  showAnnouncementModal.value = true
}

function openEditAnnouncementModal(item: NewsItem) {
  isEditingAnnouncement.value = true
  editingAnnouncementId.value = item.id
  announcementForm.value = {
    estate_id: item.estateId,
    title: item.title,
    content: item.content,
    target_audience: item.targetAudience || 'all',
  }
  showAnnouncementModal.value = true
}

async function saveAnnouncement() {
  savingAnnouncement.value = true
  try {
    if (isEditingAnnouncement.value && editingAnnouncementId.value) {
      await newsApi.update(editingAnnouncementId.value, announcementForm.value)
      showToast('Announcement updated successfully.', 'success')
    } else {
      await newsApi.create(announcementForm.value)
      showToast('Announcement published successfully!', 'success')
    }
    showAnnouncementModal.value = false
    loadNews()
  } catch {
    showToast('Failed to publish announcement.', 'error')
  } finally {
    savingAnnouncement.value = false
  }
}

function confirmDeleteAnnouncement(item: NewsItem) {
  deleteAnnouncementTarget.value = item
}

async function doDeleteAnnouncement() {
  if (!deleteAnnouncementTarget.value) return
  deletingAnnouncement.value = true
  try {
    await newsApi.delete(deleteAnnouncementTarget.value.id)
    showToast('Announcement removed.', 'success')
    deleteAnnouncementTarget.value = null
    loadNews()
  } catch {
    showToast('Failed to delete announcement.', 'error')
  } finally {
    deletingAnnouncement.value = false
  }
}

function fmtDate(dt?: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

// ── EMERGENCY SOS STATE & ACTIONS ──
interface EmergencyItem {
  id: number
  type: string
  notes?: string | null
  status: 'sent' | 'acknowledged' | 'resolved' | string
  acknowledgedAt?: string | null
  resolvedAt?: string | null
  createdAt?: string | null
  resident?: {
    id: number
    name?: string
    phone?: string
    flatAddress?: string
  } | null
  estate?: {
    id: number
    name?: string
  } | null
}

const loadingEmergencies = ref(false)
const emergenciesList = ref<EmergencyItem[]>([])
const emergencyFilter = ref<string>('')
const savingEmergencyId = ref<number | null>(null)
const deleteEmergencyTarget = ref<EmergencyItem | null>(null)
const deletingEmergency = ref(false)

const activeEmergencyCount = computed(() => {
  return emergenciesList.value.filter(e => e.status === 'sent').length
})

const filteredEmergencies = computed(() => {
  if (!emergencyFilter.value) return emergenciesList.value
  return emergenciesList.value.filter(e => e.status === emergencyFilter.value)
})

function setEmergencyFilter(val: string) {
  emergencyFilter.value = val
  loadEmergencies()
}

async function loadEmergencies(silent = false) {
  if (!silent) loadingEmergencies.value = true
  try {
    const params: Record<string, unknown> = {}
    if (emergencyFilter.value) params.status = emergencyFilter.value
    const res = await emergenciesApi.getAll(params)
    emergenciesList.value = res.data?.emergencies || []
  } catch {
    if (!silent) showToast('Failed to load emergencies.', 'error')
  } finally {
    if (!silent) loadingEmergencies.value = false
  }
}

async function acknowledgeEmergency(incident: EmergencyItem) {
  savingEmergencyId.value = incident.id
  try {
    await emergenciesApi.acknowledge(incident.id)
    incident.status = 'acknowledged'
    showToast('Emergency incident acknowledged & dispatched.', 'success')
    loadEmergencies(true)
  } catch {
    showToast('Failed to acknowledge emergency.', 'error')
  } finally {
    savingEmergencyId.value = null
  }
}

async function resolveEmergency(incident: EmergencyItem) {
  savingEmergencyId.value = incident.id
  try {
    await emergenciesApi.resolve(incident.id)
    incident.status = 'resolved'
    showToast('Emergency incident marked as resolved.', 'success')
    loadEmergencies(true)
  } catch {
    showToast('Failed to resolve emergency.', 'error')
  } finally {
    savingEmergencyId.value = null
  }
}

function confirmDeleteEmergency(incident: EmergencyItem) {
  deleteEmergencyTarget.value = incident
}

async function doDeleteEmergency() {
  if (!deleteEmergencyTarget.value) return
  deletingEmergency.value = true
  try {
    await emergenciesApi.delete(deleteEmergencyTarget.value.id)
    showToast('Emergency record deleted.', 'success')
    deleteEmergencyTarget.value = null
    loadEmergencies(true)
  } catch {
    showToast('Failed to delete emergency record.', 'error')
  } finally {
    deletingEmergency.value = false
  }
}

function emergencyTypeIcon(type?: string): string {
  switch (type?.toLowerCase()) {
    case 'fire': return '🔥'
    case 'medical': return '🚑'
    case 'intruder': return '🚨'
    case 'security incident': return '🛡️'
    default: return '⚠️'
  }
}

function emergencyTypeBadge(type?: string): string {
  switch (type?.toLowerCase()) {
    case 'fire': return 'badge-red'
    case 'medical': return 'badge-yellow'
    case 'intruder': return 'badge-red'
    case 'security incident': return 'badge-blue'
    default: return 'badge-gray'
  }
}

function emergencyStatusBadge(status?: string): string {
  switch (status) {
    case 'sent': return 'badge-red'
    case 'acknowledged': return 'badge-yellow'
    case 'resolved': return 'badge-green'
    default: return 'badge-gray'
  }
}

// ── BACKGROUND AUTO-REFRESH ──
let autoRefreshTimer: any = null

async function silentRefresh() {
  try {
    if (activeTab.value === 'notifications') {
      const res = await notificationsApi.getAll()
      notifications.value = res.data?.notifications || []
    } else if (activeTab.value === 'announcements') {
      const params: Record<string, unknown> = {}
      if (selectedEstateId.value) params.estate_id = selectedEstateId.value
      if (selectedAudience.value && selectedAudience.value !== 'all') params.target_audience = selectedAudience.value
      if (announcementSearch.value.trim()) params.search = announcementSearch.value.trim()
      const res = await newsApi.getAll(params)
      newsList.value = res.data?.news || []
    } else if (activeTab.value === 'emergencies') {
      const params: Record<string, unknown> = {}
      if (emergencyFilter.value) params.status = emergencyFilter.value
      const res = await emergenciesApi.getAll(params)
      emergenciesList.value = res.data?.emergencies || []
    }

    // Keep active emergency counter updated at all times across all tabs
    if (activeTab.value !== 'emergencies') {
      const emRes = await emergenciesApi.getAll()
      emergenciesList.value = emRes.data?.emergencies || []
    }
  } catch {
    // silent catch to avoid interrupting user activity
  }
}

onMounted(() => {
  loadNotifications()
  loadEstates()
  loadNews()
  loadEmergencies(true)

  // Start auto-refresh interval every 8 seconds
  autoRefreshTimer = setInterval(silentRefresh, 8000)
})

onUnmounted(() => {
  if (autoRefreshTimer) {
    clearInterval(autoRefreshTimer)
  }
})
</script>

<style scoped>
.notifications-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* ── Hero Card & Tab Switcher ── */
.hero-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 24px 28px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.hero-header-flex {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
  margin-bottom: 20px;
}

.hero-titles {
  max-width: 720px;
}

.hero-eyebrow {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #0A5C38;
  margin-bottom: 4px;
}

.hero-title {
  font-size: 24px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.02em;
  margin: 0 0 6px;
}

.hero-copy {
  font-size: 14px;
  color: #4B5563;
  line-height: 1.5;
  margin: 0;
}

.hero-cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #0A5C38;
  color: #ffffff;
  font-size: 13.5px;
  font-weight: 600;
  padding: 9px 18px;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  transition: all 160ms ease;
  box-shadow: 0 2px 6px rgba(10, 92, 56, 0.2);
}

.hero-cta-btn:hover {
  background: #08482C;
  transform: translateY(-1px);
}

.tab-pill-bar {
  display: flex;
  gap: 8px;
  border-top: 1px solid #F3F4F6;
  padding-top: 16px;
  flex-wrap: wrap;
}

.nav-tab-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 9px;
  font-size: 13.5px;
  font-weight: 600;
  color: #4B5563;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  cursor: pointer;
  transition: all 140ms ease;
}

.nav-tab-pill:hover {
  background: #F3F4F6;
  color: #111827;
}

.nav-tab-pill.active {
  background: #0A5C38;
  color: #ffffff;
  border-color: #0A5C38;
  box-shadow: 0 2px 6px rgba(10, 92, 56, 0.2);
}

.tab-counter {
  font-size: 11.5px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.08);
  color: inherit;
}

.nav-tab-pill.active .tab-counter {
  background: rgba(255, 255, 255, 0.22);
  color: #ffffff;
}

/* ── Section Head & Controls ── */
.section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.section-title {
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.section-copy {
  font-size: 13px;
  color: #6B7280;
  margin: 2px 0 0;
}

.section-controls {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* ── Stack List (Notifications) ── */
.stack-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.record-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 16px 20px;
  transition: all 140ms ease;
}

.record-card:hover {
  border-color: #D1D5DB;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.record-card.unread {
  border-left: 4px solid #0A5C38;
  background: #FDFEFE;
}

.record-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.record-badges {
  display: flex;
  align-items: center;
  gap: 6px;
}

.badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.badge-green { background: #ECFDF5; color: #065F46; }
.badge-yellow{ background: #FEF3C7; color: #92400E; }
.badge-red   { background: #FEE2E2; color: #991B1B; }
.badge-blue  { background: #EFF6FF; color: #1E40AF; }
.badge-gold  { background: #FEF3C7; color: #B45309; }
.badge-gray  { background: #F3F4F6; color: #4B5563; }

.message-body {
  font-size: 14px;
  color: #1F2937;
  line-height: 1.5;
  margin: 0 0 10px;
}

.record-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12px;
  color: #6B7280;
}

.btn-danger-subtle {
  background: transparent;
  color: #DC2626;
  border: none;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  padding: 3px 8px;
  border-radius: 6px;
  transition: background 140ms ease;
}

.btn-danger-subtle:hover {
  background: #FEE2E2;
}

/* ── Filter Toolbar ── */
.filters-toolbar {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}

.search-input-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  padding: 6px 12px;
  flex: 1;
  min-width: 260px;
}

.search-icon {
  color: #9CA3AF;
  flex-shrink: 0;
}

.search-input {
  border: none;
  background: transparent;
  width: 100%;
  font-size: 13.5px;
  color: #111827;
  outline: none;
  font-family: inherit;
}

.estate-select {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  background: #ffffff;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  padding: 7px 12px;
  outline: none;
}

/* ── Announcements Grid ── */
.news-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.news-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  transition: all 160ms ease;
}

.news-card:hover {
  border-color: #D1D5DB;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
}

.news-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}

.news-card-meta {
  display: flex;
  align-items: center;
  gap: 8px;
}

.estate-badge {
  font-size: 11px;
  font-weight: 700;
  color: #0A5C38;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  padding: 2px 7px;
  border-radius: 6px;
}

.aud-pill {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
}

.aud-pill--all        { background: #EFF6FF; color: #1D4ED8; border: 1px solid #BFDBFE; }
.aud-pill--residents  { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
.aud-pill--homeowners { background: #FEF3C7; color: #B45309; border: 1px solid #FDE68A; }
.aud-pill--security   { background: #FEE2E2; color: #B91C1C; border: 1px solid #FECACA; }
.aud-pill--staff      { background: #F3E8FF; color: #6D28D9; border: 1px solid #DDD6FE; }

.news-date {
  font-size: 12px;
  color: #9CA3AF;
}

.news-card-actions {
  display: flex;
  align-items: center;
  gap: 4px;
}

.icon-btn {
  background: transparent;
  border: none;
  padding: 5px;
  border-radius: 6px;
  color: #6B7280;
  cursor: pointer;
  transition: all 140ms ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-btn:hover {
  background: #F3F4F6;
  color: #111827;
}

.delete-btn:hover {
  background: #FEE2E2;
  color: #DC2626;
}

.news-item-title {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 8px;
  line-height: 1.35;
}

.news-item-preview {
  font-size: 13.5px;
  color: #4B5563;
  line-height: 1.5;
  margin: 0 0 16px;
  flex: 1;
}

.news-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 12px;
  border-top: 1px solid #F3F4F6;
  font-size: 11.5px;
}

.author-tag {
  color: #6B7280;
  font-weight: 500;
}

.status-live-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  color: #059669;
  font-weight: 700;
}

.pulse-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10B981;
}

/* ── Generic UI ── */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13.5px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 9px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 140ms ease;
}

.btn-primary { background: #0A5C38; color: #ffffff; }
.btn-primary:hover { background: #08482C; }

.btn-outline { background: #ffffff; color: #374151; border-color: #D1D5DB; }
.btn-outline:hover { background: #F9FAFB; border-color: #9CA3AF; }
.btn-outline.active { background: #F3F4F6; border-color: #0A5C38; color: #0A5C38; font-weight: 700; }

.btn-danger { background: #DC2626; color: #ffffff; }
.btn-danger:hover { background: #B91C1C; }

.btn-sm { font-size: 12px; padding: 5px 11px; border-radius: 7px; }

.empty-state {
  text-align: center;
  padding: 48px 24px;
  color: #6B7280;
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #E5E7EB;
}

.empty-icon { font-size: 32px; margin-bottom: 8px; }
.empty-state h3 { font-size: 16px; font-weight: 700; color: #111827; margin: 0 0 6px; }
.empty-state p { font-size: 13.5px; margin: 0 0 16px; }

.loading-state {
  text-align: center;
  padding: 40px;
  color: #6B7280;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
}

.spinning { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* ── Sheet / Modal Overlays ── */
.sheet-overlay {
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

.sheet {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  animation: modalScale 160ms ease-out;
}

.sheet-sm { max-width: 440px; }
.sheet-md { max-width: 580px; }

@keyframes modalScale {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}

.sheet-head {
  padding: 18px 24px;
  border-bottom: 1px solid #F3F4F6;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sheet-eyebrow {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #0A5C38;
}

.sheet-title {
  font-size: 17px;
  font-weight: 700;
  color: #111827;
  margin: 2px 0 0;
}

.sheet-close {
  background: transparent;
  border: none;
  font-size: 20px;
  color: #9CA3AF;
  cursor: pointer;
}

.confirm-copy {
  padding: 20px 24px;
  font-size: 14px;
  color: #374151;
  line-height: 1.5;
  margin: 0;
}

.sheet-form {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.form-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #374151;
}

.form-input, .form-textarea {
  width: 100%;
  font-size: 13.5px;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #D1D5DB;
  background: #ffffff;
  color: #111827;
  font-family: inherit;
  transition: all 140ms ease;
}

.form-input:focus, .form-textarea:focus {
  outline: none;
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.1);
}

.form-textarea { resize: vertical; }

.sheet-actions {
  padding: 16px 24px;
  border-top: 1px solid #F3F4F6;
  background: #F9FAFB;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
}

/* ── Audience Grid & Cards in Modal ── */
.audience-options-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}

.audience-radio-card {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  padding: 10px 12px;
  cursor: pointer;
  transition: all 140ms ease;
}

.audience-radio-card:hover {
  background: #F1F5F9;
  border-color: #CBD5E1;
}

.audience-radio-card.is-selected {
  background: #ECFDF5;
  border-color: #10B981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.aud-icon {
  font-size: 18px;
  flex-shrink: 0;
}

.aud-info {
  display: flex;
  flex-direction: column;
}

.aud-info strong {
  font-size: 12.5px;
  color: #0F172A;
  font-weight: 700;
}

.aud-info small {
  font-size: 11px;
  color: #64748B;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}

/* ── Emergency Tab & Incident Alerts ── */
.nav-tab-pill--emergency.has-alert {
  border-color: #FCA5A5;
  background: #FEF2F2;
  color: #DC2626;
  animation: pulse-border 2s infinite;
}

.nav-tab-pill--emergency.active {
  background: #DC2626 !important;
  border-color: #DC2626 !important;
  color: #ffffff !important;
  box-shadow: 0 2px 6px rgba(220, 38, 38, 0.3);
}

.tab-counter--danger {
  background: #DC2626 !important;
  color: #ffffff !important;
}

.nav-tab-pill.active .tab-counter--danger {
  background: rgba(255, 255, 255, 0.3) !important;
  color: #ffffff !important;
}

@keyframes pulse-border {
  0% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
  70% { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); }
  100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0); }
}

.emergency-card.urgent {
  background: #FFF5F5;
  border-color: #FECACA;
  border-left: 4px solid #DC2626;
}

.emergency-actions-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.emergency-btn-ack {
  color: #B45309;
  border-color: #FCD34D;
  background: #FFFBEB;
}

.emergency-btn-ack:hover {
  background: #FEF3C7;
  color: #92400E;
}

.emergency-btn-resolve {
  background: #0A5C38;
  border-color: #0A5C38;
  color: #FFFFFF;
}

.emergency-btn-resolve:hover {
  background: #08482C;
}

.emergency-resident-block {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  margin: 12px 0 10px;
  padding: 10px 14px;
  background: rgba(0, 0, 0, 0.02);
  border-radius: 10px;
  border: 1px solid #E5E7EB;
}

.resident-detail-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
}

.chip-lbl {
  color: #6B7280;
  font-size: 11.5px;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.chip-val {
  color: #111827;
  font-weight: 700;
}

.chip-link {
  color: #0A5C38;
  font-weight: 700;
  text-decoration: none;
}

.chip-link:hover {
  text-decoration: underline;
}

.incident-notes {
  font-size: 13.5px;
  color: #374151;
  line-height: 1.5;
  margin: 0 0 12px;
  padding: 10px 14px;
  background: #FFFFFF;
  border-radius: 8px;
  border: 1px dashed #D1D5DB;
}

.pulse-dot-red {
  display: inline-block;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #DC2626;
  margin-right: 4px;
  animation: pulse-red 1.5s infinite;
}

@keyframes pulse-red {
  0% { transform: scale(0.9); opacity: 0.8; }
  50% { transform: scale(1.3); opacity: 1; }
  100% { transform: scale(0.9); opacity: 0.8; }
}
</style>
