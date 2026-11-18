<template>
  <div class="mobile-page users-management-page">
    <!-- Hero Header Card -->
    <section class="hero-card hero-card--emerald">
      <div class="hero-title-group">
        <div class="hero-eyebrow">STAFF & SECURITY MANAGEMENT</div>
        <h1 class="hero-title">Staff & Security Directory</h1>
        <p class="hero-subtext">Manage estate administrators, officers, and gate security personnel credentials.</p>
      </div>
      <button class="btn btn-primary hero-button" @click="startAdd">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="8.5" cy="7" r="4"/>
          <line x1="20" y1="8" x2="20" y2="14"/>
          <line x1="23" y1="11" x2="17" y2="11"/>
        </svg>
        <span>Add Staff Member</span>
      </button>
    </section>

    <!-- Unified Stats Metric Row -->
    <div class="stats-metric-strip">
      <div class="stat-card">
        <div class="stat-icon-wrap bg-blue-subtle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-number">{{ allStats.total }}</span>
          <span class="stat-title">Total Staff</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrap bg-purple-subtle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#7C3AED" stroke-width="2.2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-number">{{ allStats.admins }}</span>
          <span class="stat-title">Estate Admins</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrap bg-amber-subtle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2.2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-number">{{ allStats.securities }}</span>
          <span class="stat-title">Gate Security</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-wrap bg-emerald-subtle">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-number">{{ allStats.active }}</span>
          <span class="stat-title">Active on Duty</span>
        </div>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="toolbar-card">
      <div class="search-box">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          v-model="search"
          type="search"
          placeholder="Search staff by name, phone, or email..."
          class="search-input"
          @input="debouncedLoad"
        />
      </div>

      <div class="filter-group">
        <select v-model="typeFilter" class="toolbar-select" @change="load">
          <option value="">All Staff Roles</option>
          <option value="admin">Estate Administrators</option>
          <option value="security">Gate Security Personnel</option>
        </select>

        <select v-model="statusFilter" class="toolbar-select" @change="load">
          <option value="">All Status</option>
          <option value="active">Active Staff</option>
          <option value="inactive">Disabled / Inactive</option>
        </select>
      </div>
    </div>

    <!-- Main List Section -->
    <section class="card section-card">
      <div class="section-head">
        <div>
          <h2 class="section-title">Staff Directory</h2>
          <p class="section-copy">{{ filteredUsers.length }} staff member{{ filteredUsers.length === 1 ? '' : 's' }} loaded.</p>
        </div>

        <!-- View Mode Switcher: Cards Grid vs Compact List -->
        <div class="view-mode-toggle">
          <button
            type="button"
            class="btn-view-toggle"
            :class="{ active: viewMode === 'grid' }"
            title="Card Grid View"
            @click="viewMode = 'grid'"
          >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="3" width="7" height="7"/>
              <rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/>
              <rect x="3" y="14" width="7" height="7"/>
            </svg>
            <span>Cards</span>
          </button>
          <button
            type="button"
            class="btn-view-toggle"
            :class="{ active: viewMode === 'list' }"
            title="Compact List View"
            @click="viewMode = 'list'"
          >
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="8" y1="6" x2="21" y2="6"/>
              <line x1="8" y1="12" x2="21" y2="12"/>
              <line x1="8" y1="18" x2="21" y2="18"/>
              <line x1="3" y1="6" x2="3.01" y2="6"/>
              <line x1="3" y1="12" x2="3.01" y2="12"/>
              <line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            <span>List</span>
          </button>
        </div>
      </div>

      <div v-if="loading" class="loading-state">
        <BrandSpinner :size="40" color="var(--c-primary)" />
      </div>

      <div v-else-if="filteredUsers.length === 0" class="empty-state">
        No accounts found matching your filters.
      </div>

      <!-- CARDS GRID VIEW (Default, 3-column side-by-side) -->
      <div v-else-if="viewMode === 'grid'" class="users-grid">
        <article
          v-for="user in filteredUsers"
          :key="user.id"
          class="user-grid-card"
          tabindex="0"
          role="button"
          :aria-label="'View ' + user.name + ' profile'"
          @click="handleUserClick(user)"
          @keydown.enter="handleUserClick(user)"
        >
          <!-- Top Row: Avatar & 3-Dots Action Menu -->
          <div class="grid-card-head">
            <div class="avatar-box">
              <div
                v-if="user.avatar"
                class="avatar-photo"
                :style="{ backgroundImage: `url(${user.avatar})` }"
              />
              <div v-else class="avatar-initials" :style="{ background: avatarBg(user.id) }">
                {{ initials(user.name) }}
              </div>
              <span
                class="status-dot"
                :class="user.isActive ? 'dot-active' : 'dot-inactive'"
                :title="user.isActive ? 'Active' : 'Disabled'"
              />
            </div>

            <!-- 3-Dots Action Menu Button (matching Property Cards) -->
            <div class="card-more-wrapper" @click.stop>
              <button
                type="button"
                class="card-more-btn"
                :class="{ 'is-active': activeCardMenuId === user.id }"
                title="User options"
                aria-label="User options"
                @click.stop="toggleCardMenu(user.id)"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <circle cx="12" cy="5" r="2.2"/>
                  <circle cx="12" cy="12" r="2.2"/>
                  <circle cx="12" cy="19" r="2.2"/>
                </svg>
              </button>

              <!-- Dropdown Popover -->
              <div v-if="activeCardMenuId === user.id" class="card-dropdown-popover" @click.stop>
                <button type="button" class="dropdown-item" @click="handleMenuAction('view', user)">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <span>View Details</span>
                </button>
                <button type="button" class="dropdown-item" @click="handleMenuAction('edit', user)">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                  <span>Edit Account</span>
                </button>
                <div class="dropdown-divider"></div>
                <button
                  type="button"
                  class="dropdown-item"
                  :class="user.isActive ? 'dropdown-item--warning' : 'dropdown-item--success'"
                  @click="handleMenuAction('toggle-status', user)"
                >
                  <svg v-if="user.isActive" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                  </svg>
                  <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <span>{{ user.isActive ? 'Disable Account' : 'Activate Account' }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Mid Section: Name & Context Box (Role for Staff) -->
          <div class="grid-card-body">
            <h3 class="grid-card-name" :title="user.name">{{ user.name }}</h3>

            <div class="grid-unit-box">
              <!-- Admin Role -->
              <div v-if="user.type === 'admin'" class="grid-unit-val staff-val staff-val--admin">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span class="grid-unit-text">Estate Administrator</span>
              </div>

              <!-- Security Role -->
              <div v-else class="grid-unit-val staff-val staff-val--security">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                <span class="grid-unit-text">Gate Security Personnel</span>
              </div>
            </div>
          </div>

          <!-- Bottom Row: Badges -->
          <div class="grid-card-foot">
            <div class="grid-card-badges">
              <span
                class="badge-type"
                :class="'badge-type--' + (user.type || 'security')"
              >
                {{ formatTypeLabel(user.type) }}
              </span>
            </div>

            <span class="status-pill" :class="user.isActive ? 'status-active' : 'status-disabled'">
              <span class="pill-dot"></span>
              {{ user.isActive ? 'Active' : 'Disabled' }}
            </span>
          </div>
        </article>
      </div>

      <!-- COMPACT LIST VIEW -->
      <div v-else class="user-cards-list">
        <article
          v-for="user in filteredUsers"
          :key="user.id"
          class="user-card"
          tabindex="0"
          role="button"
          :aria-label="'View ' + user.name + ' profile'"
          @click="handleUserClick(user)"
          @keydown.enter="handleUserClick(user)"
        >
          <!-- Left: Avatar, Name, Role Pills, Unit -->
          <div class="card-left">
            <div class="avatar-box">
              <div
                v-if="user.avatar"
                class="avatar-photo"
                :style="{ backgroundImage: `url(${user.avatar})` }"
              />
              <div v-else class="avatar-initials" :style="{ background: avatarBg(user.id) }">
                {{ initials(user.name) }}
              </div>
              <span
                class="status-dot"
                :class="user.isActive ? 'dot-active' : 'dot-inactive'"
                :title="user.isActive ? 'Active' : 'Disabled'"
              />
            </div>

            <div class="user-primary-info">
              <div class="user-name-row">
                <h3 class="user-name">{{ user.name }}</h3>

                <div class="badge-row">
                  <span
                    class="badge-type"
                    :class="'badge-type--' + (user.type || 'security')"
                  >
                    {{ formatTypeLabel(user.type) }}
                  </span>
                </div>
              </div>

              <div class="staff-contact-row">
                <span v-if="user.email" class="staff-contact-item">{{ user.email }}</span>
                <span v-if="user.email && user.phone" class="staff-contact-sep">•</span>
                <span v-if="user.phone" class="staff-contact-item">{{ user.phone }}</span>
              </div>
            </div>
          </div>

          <!-- Right: Status, Join Date & Interactive Chevron -->
          <div class="card-right">
            <span class="status-pill" :class="user.isActive ? 'status-active' : 'status-disabled'">
              <span class="pill-dot"></span>
              {{ user.isActive ? 'Active' : 'Disabled' }}
            </span>

            <span v-if="user.createdAt" class="join-date">Joined {{ fmtDate(user.createdAt) }}</span>

            <div class="arrow-wrap">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </div>
          </div>
        </article>
      </div>
    </section>

    <!-- EDIT / MANAGE MODAL (for Staff accounts) -->
    <div v-if="editTarget" class="modal-overlay" @click.self="editTarget = null">
      <div class="modal-card">
        <header class="modal-header">
          <div>
            <div class="modal-eyebrow">{{ editTarget.type === 'admin' ? 'ESTATE ADMINISTRATOR' : 'GATE SECURITY' }}</div>
            <h3 class="modal-title">Edit {{ editTarget.name }}</h3>
          </div>
          <button type="button" class="btn-close" @click="editTarget = null">✕</button>
        </header>

        <form class="modal-body" @submit.prevent="saveEdit">
          <div class="form-group">
            <label class="form-label">Full Name <span class="req">*</span></label>
            <input v-model="editForm.name" class="form-input" required placeholder="e.g. Samuel Okon" />
          </div>

          <div class="form-group">
            <label class="form-label">
              Phone Number <span v-if="editTarget.type === 'security'" class="req">*</span>
            </label>
            <input v-model="editForm.phone" class="form-input" type="tel" :required="editTarget.type === 'security'" placeholder="e.g. 08012345678" />
          </div>

          <div class="form-group">
            <label class="form-label">
              Email Address <span v-if="editTarget.type === 'admin'" class="req">*</span>
            </label>
            <input v-model="editForm.email" class="form-input" type="email" :required="editTarget.type === 'admin'" placeholder="e.g. admin@checkpass.com" />
          </div>

          <div class="form-group">
            <label class="form-label">Account Access Status</label>
            <div class="status-choice-group">
              <label class="status-radio-card" :class="{ active: editForm.is_active }">
                <input v-model="editForm.is_active" type="radio" :value="true" class="sr-only" />
                <div class="radio-indicator active-indicator"></div>
                <div>
                  <strong>Active Account</strong>
                  <p>Allowed to log in and perform duty operations.</p>
                </div>
              </label>

              <label class="status-radio-card" :class="{ active: !editForm.is_active }">
                <input v-model="editForm.is_active" type="radio" :value="false" class="sr-only" />
                <div class="radio-indicator disabled-indicator"></div>
                <div>
                  <strong>Disabled Account</strong>
                  <p>Access suspended from estate operations.</p>
                </div>
              </label>
            </div>
          </div>

          <p v-if="formError" class="modal-error">{{ formError }}</p>

          <footer class="modal-footer">
            <button type="button" class="btn btn-outline btn-danger-outline" @click="confirmDelete(editTarget)">Remove Staff</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <BrandSpinner v-if="saving" :size="16" color="#ffffff" />
              <span>{{ saving ? 'Saving...' : 'Save Changes' }}</span>
            </button>
          </footer>
        </form>
      </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal-card modal-card-sm">
        <header class="modal-header">
          <div>
            <div class="modal-eyebrow text-red">REMOVE STAFF</div>
            <h3 class="modal-title">Remove {{ deleteTarget.name }}?</h3>
          </div>
          <button type="button" class="btn-close" @click="deleteTarget = null">✕</button>
        </header>
        <div class="modal-body">
          <p class="confirm-copy">This staff account and associated login credentials will be permanently removed.</p>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
            <button type="button" class="btn btn-danger" :disabled="saving" @click="doDelete">
              {{ saving ? 'Deleting...' : 'Remove Staff Member' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { usersApi } from '@/api/index'
import { useToast } from '@/composables/useToast'
import BrandSpinner from '@/components/BrandSpinner.vue'

const { showToast } = useToast()
const router = useRouter()

// ── State ─────────────────────────────────────────────────────────
const loading = ref(true)
const users = ref<any[]>([])
const allUsers = ref<any[]>([])

const search = ref('')
const typeFilter = ref('')
const statusFilter = ref('')
const viewMode = ref<'grid' | 'list'>('grid')

const activeCardMenuId = ref<any>(null)

function toggleCardMenu(id: any) {
  activeCardMenuId.value = activeCardMenuId.value === id ? null : id
}

function closeCardMenu() {
  activeCardMenuId.value = null
}

async function handleMenuAction(action: 'view' | 'edit' | 'toggle-status', user: any) {
  closeCardMenu()
  if (action === 'view' || action === 'edit') {
    openEdit(user)
    return
  }
  if (action === 'toggle-status') {
    await toggleUserStatus(user)
  }
}

async function toggleUserStatus(user: any) {
  const nextState = !user.isActive
  try {
    await usersApi.update(user.id, { is_active: nextState })
    user.isActive = nextState
    showToast(
      `${user.name} is now ${nextState ? 'active' : 'disabled'}.`,
      nextState ? 'success' : 'info'
    )
    loadStats()
  } catch (err: any) {
    showToast(err?.response?.data?.message || 'Failed to update user status.', 'error')
  }
}

const saving = ref(false)
const formError = ref('')
const editTarget = ref<any>(null)
const editForm = ref({ name: '', phone: '', email: '', is_active: true })
const deleteTarget = ref<any>(null)

// ── Computed Stats ────────────────────────────────────────────────
const allStats = computed(() => {
  const staff = allUsers.value.filter(u => u.type === 'admin' || u.type === 'security')
  return {
    total: staff.length,
    admins: staff.filter(u => u.type === 'admin').length,
    securities: staff.filter(u => u.type === 'security').length,
    active: staff.filter(u => u.isActive).length,
  }
})

// ── Filtered List ─────────────────────────────────────────────────
const filteredUsers = computed(() => {
  return users.value.filter(u => u.type === 'admin' || u.type === 'security')
})

// ── Avatar & Color helpers ────────────────────────────────────────
const COLORS = [
  '#0A5C38', '#1D4ED8', '#B45309', '#7C3AED',
  '#BE185D', '#047857', '#C2410C', '#4338CA'
]

function avatarBg(id: any): string {
  const num = typeof id === 'number' ? id : String(id).charCodeAt(String(id).length - 1)
  return COLORS[num % COLORS.length]
}

function initials(name?: string): string {
  if (!name) return '?'
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

function formatTypeLabel(type?: string): string {
  return (type || '').toLowerCase() === 'admin' ? 'Administrator' : 'Gate Security'
}

function fmtDate(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

// ── Interactive Navigation on Card Click ──────────────────────────
function handleUserClick(user: any) {
  openEdit(user)
}

function startAdd() {
  router.push('/users/new')
}

// ── Data loading ──────────────────────────────────────────────────
let searchTimer: ReturnType<typeof setTimeout>
function debouncedLoad() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(load, 350)
}

async function loadStats() {
  try {
    const ur = await usersApi.getAll({ type: 'staff' })
    allUsers.value = ur.data.users ?? []
  } catch (e) {
    console.error('Failed loading stats:', e)
  }
}

async function load() {
  loading.value = true
  try {
    const p: Record<string, unknown> = {}
    if (search.value) p.search = search.value
    p.type = typeFilter.value || 'staff'
    if (statusFilter.value) p.status = statusFilter.value
    const res = await usersApi.getAll(p)
    users.value = res.data.users ?? []
  } catch {
    showToast('Failed to load staff accounts.', 'error')
  } finally {
    loading.value = false
  }
}

// ── Edit & Delete for Staff accounts ──────────────────────────────
function openEdit(user: any) {
  editTarget.value = user
  editForm.value = {
    name: user.name,
    phone: user.phone ?? '',
    email: user.email ?? '',
    is_active: !!user.isActive,
  }
  formError.value = ''
}

async function saveEdit() {
  if (!editTarget.value) return
  formError.value = ''
  saving.value = true
  try {
    const payload: Record<string, any> = {
      name: editForm.value.name.trim(),
      email: editForm.value.email.trim() || null,
      is_active: editForm.value.is_active,
    }
    if (editForm.value.phone.trim()) {
      payload.phone = editForm.value.phone.trim()
    }
    await usersApi.update(editTarget.value.id, payload)
    showToast('Staff account updated successfully.', 'success')
    editTarget.value = null
    await Promise.all([load(), loadStats()])
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? 'Something went wrong.'
  } finally {
    saving.value = false
  }
}

function confirmDelete(user: any) {
  deleteTarget.value = user
  editTarget.value = null
}

async function doDelete() {
  if (!deleteTarget.value) return
  saving.value = true
  try {
    await usersApi.delete(deleteTarget.value.id)
    showToast('Staff member removed successfully.', 'success')
    deleteTarget.value = null
    await Promise.all([load(), loadStats()])
  } catch {
    showToast('Failed to remove staff member.', 'error')
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  load()
  loadStats()
  window.addEventListener('click', closeCardMenu)
})

onUnmounted(() => {
  window.removeEventListener('click', closeCardMenu)
})
</script>

<style scoped>
.users-management-page {
  display: flex;
  flex-direction: column;
  gap: 16px;
  max-width: 1200px;
  margin: 0 auto;
}

/* ── Hero Card ─────────────────────────────────────── */
.hero-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 24px 28px;
  border-radius: 20px;
  color: white;
}

.hero-card--emerald {
  background: linear-gradient(145deg, #063923 0%, #0A5C38 60%, #168051 100%);
  box-shadow: 0 4px 18px rgba(10, 92, 56, 0.16);
}

.hero-title-group {
  flex: 1;
  min-width: 240px;
}

.hero-eyebrow {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: rgba(255, 255, 255, 0.78);
  font-weight: 700;
  margin-bottom: 6px;
}

.hero-title {
  font-size: 22px;
  line-height: 1.2;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
}

.hero-subtext {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.85);
  margin: 6px 0 0;
  max-width: 580px;
}

.hero-button {
  background: #ffffff;
  color: #0A5C38;
  font-weight: 700;
  font-size: 13.5px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
  white-space: nowrap;
  cursor: pointer;
  transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease;
}

.hero-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
  background: #F0FDF4;
}

/* ── Unified Stats Metric Strip ────────────────────── */
.stats-metric-strip {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px 18px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

.stat-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.bg-blue-subtle { background: #EFF6FF; }
.bg-emerald-subtle { background: #ECFDF5; }
.bg-purple-subtle { background: #F5F3FF; }
.bg-amber-subtle { background: #FEF3C7; }

.stat-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.stat-num-row {
  display: flex;
  align-items: baseline;
  gap: 6px;
  flex-wrap: wrap;
}

.stat-number {
  font-size: 20px;
  font-weight: 800;
  color: #111827;
  line-height: 1;
}

.stat-pill-hint {
  font-size: 11px;
  color: #6B7280;
  font-weight: 500;
}

.stat-title {
  font-size: 12px;
  font-weight: 600;
  color: #6B7280;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

/* ── Filter & Search Toolbar ───────────────────────── */
.toolbar-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  padding: 10px 14px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  flex: 1;
  min-width: 220px;
}

.search-input {
  border: none;
  outline: none;
  font-size: 13.5px;
  color: #111827;
  width: 100%;
  background: transparent;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.toolbar-select {
  padding: 7px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  background: #F9FAFB;
  font-size: 12.5px;
  font-weight: 500;
  color: #374151;
  outline: none;
  cursor: pointer;
  transition: all 140ms ease;
}

.toolbar-select:hover {
  border-color: #D1D5DB;
  background: #ffffff;
}

.toolbar-select:focus {
  border-color: #0A5C38;
}

/* ── Section Card & View Switcher ──────────────────── */
.section-card {
  padding: 18px 20px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
  gap: 12px;
}

.section-title {
  font-size: 17px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.section-copy {
  margin-top: 2px;
  color: #6B7280;
  font-size: 12.5px;
}

.view-mode-toggle {
  display: inline-flex;
  align-items: center;
  background: #F3F4F6;
  border-radius: 10px;
  padding: 3px;
  gap: 2px;
}

.btn-view-toggle {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 10px;
  font-size: 12px;
  font-weight: 600;
  color: #6B7280;
  background: transparent;
  border: none;
  border-radius: 7px;
  cursor: pointer;
  transition: all 140ms ease;
}

.btn-view-toggle:hover {
  color: #111827;
}

.btn-view-toggle.active {
  background: #ffffff;
  color: #111827;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.loading-state, .empty-state {
  min-height: 140px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6B7280;
  font-size: 13.5px;
}

/* ── 3-COLUMN USER CARDS GRID (User's Drawing) ─────── */
.users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
  gap: 16px;
}

.user-grid-card {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 18px 20px;
  cursor: pointer;
  user-select: none;
  transition: all 180ms cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
  min-height: 200px;
}

.user-grid-card:hover {
  transform: translateY(-3px);
  border-color: #D1D5DB;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
}

.user-grid-card:focus-visible {
  outline: none;
  border-color: #0A5C38;
  box-shadow: 0 0 0 2px rgba(10, 92, 56, 0.2);
}

.grid-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.grid-card-badges {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.grid-card-body {
  margin: 14px 0 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.grid-card-name {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  letter-spacing: -0.2px;
}

.grid-unit-box {
  background: #F9FAFB;
  border: 1px solid #F3F4F6;
  border-radius: 10px;
  padding: 10px 12px;
  display: flex;
  align-items: center;
  transition: background 160ms ease;
  min-height: 42px;
  box-sizing: border-box;
}

.user-grid-card:hover .grid-unit-box {
  background: #F3F4F6;
}

.grid-unit-val {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #111827;
  width: 100%;
  min-width: 0;
}

.grid-unit-val svg {
  color: #6B7280;
  flex-shrink: 0;
}

.grid-unit-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.text-muted-italic {
  color: #9CA3AF;
  font-weight: 500;
  font-style: italic;
}

.staff-val--admin {
  color: #065F46;
}

.staff-val--admin svg {
  color: #059669;
}

.staff-val--security {
  color: #92400E;
}

.staff-val--security svg {
  color: #D97706;
}

.grid-card-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 12px;
  border-top: 1px solid #F3F4F6;
  gap: 8px;
}

/* ── 3-Dots Action Menu (matched with Property Cards) ── */
.card-more-wrapper {
  position: relative;
  display: inline-flex;
}

.card-more-btn {
  background: transparent;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6B7280;
  cursor: pointer;
  transition: all 0.15s ease;
}

.card-more-btn:hover,
.card-more-btn.is-active {
  background: #F3F4F6;
  color: #111827;
}

.card-dropdown-popover {
  position: absolute;
  top: calc(100% + 4px);
  right: 0;
  width: 165px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12), 0 2px 6px rgba(0, 0, 0, 0.05);
  padding: 6px;
  z-index: 50;
  display: flex;
  flex-direction: column;
  gap: 2px;
  animation: popoverFade 0.15s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes popoverFade {
  from {
    opacity: 0;
    transform: translateY(-4px) scale(0.97);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 8px 10px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 500;
  color: #374151;
  border: none;
  background: transparent;
  text-align: left;
  cursor: pointer;
  transition: all 0.15s ease;
}

.dropdown-item:hover {
  background: #F3F4F6;
  color: #111827;
}

.dropdown-item--warning {
  color: #D97706;
}

.dropdown-item--warning:hover {
  background: #FEF3C7;
  color: #B45309;
}

.dropdown-item--success {
  color: #047857;
}

.dropdown-item--success:hover {
  background: #ECFDF5;
  color: #065F46;
}

.dropdown-divider {
  height: 1px;
  background: #F3F4F6;
  margin: 3px 0;
}

/* ── Avatar Elements ───────────────────────────────── */
.avatar-box {
  position: relative;
  width: 44px;
  height: 44px;
  flex-shrink: 0;
}

.avatar-photo {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  border: 1px solid #E5E7EB;
}

.avatar-initials {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  color: #ffffff;
  font-size: 15px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255,255,255,0.4);
}

.status-dot {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 11px;
  height: 11px;
  border-radius: 50%;
  border: 2px solid #ffffff;
}

.dot-active {
  background: #10B981;
}

.dot-inactive {
  background: #9CA3AF;
}

/* ── Badges ────────────────────────────────────────── */
.badge-type {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  padding: 2.5px 7px;
  border-radius: 5px;
}

.badge-type--resident {
  background: #F3F4F6;
  color: #4B5563;
  border: 1px solid #E5E7EB;
}

.badge-type--admin {
  background: #F5F3FF;
  color: #7C3AED;
  border: 1px solid #DDD6FE;
}

.badge-type--security {
  background: #FEF3C7;
  color: #B45309;
  border: 1px solid #FDE68A;
}

.badge-role {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  padding: 2.5px 7px;
  border-radius: 5px;
}

.badge-role--owner {
  background: #EFF6FF;
  color: #1D4ED8;
  border: 1px solid #DBEAFE;
}

.badge-role--tenant {
  background: #F3F4F6;
  color: #4B5563;
  border: 1px solid #E5E7EB;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 600;
  padding: 2.5px 7px;
  border-radius: 6px;
}

.status-active {
  background: #ECFDF5;
  color: #065F46;
  border: 1px solid #A7F3D0;
}

.status-disabled {
  background: #FEF2F2;
  color: #991B1B;
  border: 1px solid #FECACA;
}

.pill-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

/* ── COMPACT LIST VIEW STYLES ──────────────────────── */
.user-cards-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.user-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 14px 18px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  cursor: pointer;
  user-select: none;
  transition: all 180ms cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}

.user-card:hover {
  transform: translateY(-2px);
  border-color: #D1D5DB;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
}

.card-left {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.user-primary-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.user-name-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.user-name {
  font-size: 14.5px;
  font-weight: 600;
  color: #111827;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.badge-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.unit-location-row,
.staff-contact-row {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  color: #4B5563;
}

.staff-contact-item {
  color: #4B5563;
  font-weight: 500;
}

.staff-contact-sep {
  color: #9CA3AF;
  font-size: 11px;
}

.unit-name {
  color: #374151;
  font-weight: 500;
}

.email-subtext {
  font-size: 12px;
  color: #6B7280;
}

.card-right {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-shrink: 0;
}

.join-date {
  font-size: 12px;
  color: #6B7280;
  white-space: nowrap;
}

.arrow-wrap {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9CA3AF;
  background: #F9FAFB;
  transition: all 160ms ease;
}

.user-card:hover .arrow-wrap {
  transform: translateX(3px);
  color: #0A5C38;
  background: #ECFDF5;
}

/* ── Modal Architecture ────────────────────────────── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-card {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-card-sm {
  max-width: 420px;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 18px 22px;
  border-bottom: 1px solid #E5E7EB;
}

.modal-eyebrow {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.6px;
  color: #0A5C38;
}

.text-red {
  color: #DC2626 !important;
}

.modal-title {
  font-size: 17px;
  font-weight: 700;
  color: #111827;
  margin: 2px 0 0;
}

.btn-close {
  background: transparent;
  border: none;
  font-size: 16px;
  color: #9CA3AF;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
}

.btn-close:hover {
  color: #111827;
  background: #F3F4F6;
}

.modal-body {
  padding: 20px 22px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #374151;
}

.req {
  color: #DC2626;
}

.form-input {
  padding: 9px 12px;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13.5px;
  color: #111827;
  background: #ffffff;
}

.form-input:focus {
  outline: none;
  border-color: #0A5C38;
  box-shadow: 0 0 0 2px rgba(10, 92, 56, 0.15);
}

.status-choice-group {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.status-radio-card {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  cursor: pointer;
  transition: all 140ms ease;
  background: #ffffff;
}

.status-radio-card:hover {
  border-color: #D1D5DB;
}

.status-radio-card.active {
  border-color: #0A5C38;
  background: #F0FDF4;
}

.radio-indicator {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  margin-top: 2px;
  flex-shrink: 0;
}

.active-indicator {
  background: #10B981;
}

.disabled-indicator {
  background: #EF4444;
}

.status-radio-card strong {
  font-size: 13px;
  color: #111827;
}

.status-radio-card p {
  font-size: 11.5px;
  color: #6B7280;
  margin: 2px 0 0;
  line-height: 1.35;
}

.modal-error {
  color: #DC2626;
  font-size: 12.5px;
  margin: 0;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 8px;
  border-top: 1px solid #F3F4F6;
}

.btn-danger-outline {
  color: #DC2626;
  border-color: #FCA5A5;
}

.btn-danger-outline:hover {
  background: #FEF2F2;
}

.confirm-copy {
  font-size: 13.5px;
  color: #4B5563;
  margin: 0;
  line-height: 1.5;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}

@media (max-width: 900px) {
  .stats-metric-strip {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .hero-card {
    flex-direction: column;
    align-items: flex-start;
  }
  .toolbar-card {
    flex-direction: column;
    align-items: stretch;
  }
  .filter-group {
    flex-wrap: wrap;
    width: 100%;
  }
  .toolbar-select {
    flex: 1 1 calc(50% - 6px);
  }
  .users-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 580px) {
  .stats-metric-strip {
    grid-template-columns: 1fr;
  }
  .join-date {
    display: none;
  }
  .status-choice-group {
    grid-template-columns: 1fr;
  }
}
</style>
