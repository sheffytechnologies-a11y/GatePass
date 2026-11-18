<template>
  <div class="mobile-page residents-page">
    <section class="hero-card hero-card--sand">
      <div class="hero-title-group">
        <div class="hero-eyebrow">RESIDENT REGISTRY</div>
        <h1 class="hero-title">Assign residents to estates and units from a mobile-first flow.</h1>
      </div>
      <button class="btn btn-primary hero-button" @click="openCreate">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
          <circle cx="8.5" cy="7" r="4"/>
          <line x1="20" y1="8" x2="20" y2="14"/>
          <line x1="23" y1="11" x2="17" y2="11"/>
        </svg>
        <span>Add Resident</span>
      </button>
    </section>

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
          placeholder="Search by name or phone..."
          class="search-input"
          @input="debouncedLoad"
        />
      </div>

      <div class="filter-group">
        <select
          v-if="estates.length > 1"
          v-model="estateFilter"
          class="toolbar-select"
          @change="load"
        >
          <option value="">All Estates</option>
          <option v-for="estate in estates" :key="estate.id" :value="estate.id">
            🏡 {{ estate.name }}
          </option>
        </select>

        <select v-model="roleFilter" class="toolbar-select">
          <option value="all">All Roles</option>
          <option value="owner">Owners Only</option>
          <option value="tenant">Tenants Only</option>
        </select>

        <select v-model="statusFilter" class="toolbar-select">
          <option value="all">All Status</option>
          <option value="active">Active Only</option>
          <option value="inactive">Inactive Only</option>
        </select>
      </div>
    </div>

    <!-- Main Section Card -->
    <section class="card section-card">
      <div class="section-head">
        <div>
          <h2 class="section-title">Residents</h2>
          <p class="section-copy">{{ filteredResidents.length }} resident{{ filteredResidents.length === 1 ? '' : 's' }} loaded.</p>
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

      <div v-if="loading" class="loading-state"><BrandSpinner :size="40" color="var(--c-primary)" /></div>
      <div v-else-if="filteredResidents.length === 0" class="empty-state">No residents found.</div>

      <!-- CARDS GRID VIEW (Default, 3-column side-by-side) -->
      <div v-else-if="viewMode === 'grid'" class="residents-grid">
        <article
          v-for="resident in filteredResidents"
          :key="resident.id"
          class="resident-grid-card"
          tabindex="0"
          role="button"
          :aria-label="'View ' + (resident.user?.name ?? 'resident') + ' details'"
          @click="goToDetail(resident.id)"
          @keydown.enter="goToDetail(resident.id)"
        >
          <!-- Card Top: Avatar & 3-Dots Action Menu -->
          <div class="grid-card-head">
            <div class="avatar-box">
              <div
                v-if="resident.user?.avatar"
                class="avatar-photo"
                :style="{ backgroundImage: `url(${resident.user.avatar})` }"
              />
              <div v-else class="avatar-initials">
                {{ initials(resident.user?.name) }}
              </div>
              <span
                class="status-dot"
                :class="resident.isActive ? 'dot-active' : 'dot-inactive'"
                :title="resident.isActive ? 'Active' : 'Inactive'"
              />
            </div>

            <!-- 3-Dots Action Menu Button (matching Property Cards) -->
            <div class="card-more-wrapper" @click.stop>
              <button
                type="button"
                class="card-more-btn"
                :class="{ 'is-active': activeCardMenuId === resident.id }"
                title="Resident options"
                aria-label="Resident options"
                @click.stop="toggleCardMenu(resident.id)"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <circle cx="12" cy="5" r="2.2"/>
                  <circle cx="12" cy="12" r="2.2"/>
                  <circle cx="12" cy="19" r="2.2"/>
                </svg>
              </button>

              <!-- Dropdown Popover -->
              <div v-if="activeCardMenuId === resident.id" class="card-dropdown-popover" @click.stop>
                <button type="button" class="dropdown-item" @click="handleMenuAction('view', resident)">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <span>View Details</span>
                </button>
                <button type="button" class="dropdown-item" @click="handleMenuAction('edit', resident)">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                  <span>Edit Resident</span>
                </button>
                <div class="dropdown-divider"></div>
                <button
                  type="button"
                  class="dropdown-item"
                  :class="resident.isActive ? 'dropdown-item--warning' : 'dropdown-item--success'"
                  @click="handleMenuAction('toggle-status', resident)"
                >
                  <svg v-if="resident.isActive" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                  </svg>
                  <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  <span>{{ resident.isActive ? 'Disable Account' : 'Activate Account' }}</span>
                </button>
              </div>
            </div>
          </div>

          <!-- Card Middle: Resident Name & Assigned Property Unit Box -->
          <div class="grid-card-body">
            <h3 class="grid-card-name" :title="resident.user?.name">{{ resident.user?.name ?? '—' }}</h3>

            <div class="grid-unit-box">
              <span class="grid-unit-label">ASSIGNED PROPERTY</span>
              <div class="grid-unit-val">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                  <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span class="grid-unit-text">{{ resident.unit?.flatAddress || 'No unit assigned' }}</span>
              </div>
            </div>
          </div>

          <!-- Card Bottom / Footer: Badges (Where green was) -->
          <div class="grid-card-foot">
            <span :class="resident.role === 'owner' ? 'badge-role badge-role--owner' : 'badge-role badge-role--tenant'">
              {{ resident.role === 'owner' ? 'Homeowner' : 'Tenant' }}
            </span>
            <span class="status-pill" :class="resident.isActive ? 'status-active' : 'status-disabled'">
              <span class="pill-dot"></span>
              {{ resident.isActive ? 'Active' : 'Disabled' }}
            </span>
          </div>
        </article>
      </div>

      <!-- COMPACT LIST VIEW -->
      <div v-else class="resident-cards-list">
        <article
          v-for="resident in filteredResidents"
          :key="resident.id"
          class="resident-card"
          tabindex="0"
          role="button"
          :aria-label="'View ' + (resident.user?.name ?? 'resident') + ' details'"
          @click="goToDetail(resident.id)"
          @keydown.enter="goToDetail(resident.id)"
        >
          <div class="card-left">
            <div class="avatar-box">
              <div
                v-if="resident.user?.avatar"
                class="avatar-photo"
                :style="{ backgroundImage: `url(${resident.user.avatar})` }"
              />
              <div v-else class="avatar-initials">
                {{ initials(resident.user?.name) }}
              </div>
              <span
                class="status-dot"
                :class="resident.isActive ? 'dot-active' : 'dot-inactive'"
                :title="resident.isActive ? 'Active' : 'Inactive'"
              />
            </div>

            <div class="resident-primary-info">
              <div class="resident-name-row">
                <h3 class="resident-name">{{ resident.user?.name ?? '—' }}</h3>
                <span :class="resident.role === 'owner' ? 'badge-role badge-role--owner' : 'badge-role badge-role--tenant'">
                  {{ resident.role === 'owner' ? 'Owner' : 'Tenant' }}
                </span>
              </div>

              <div class="unit-location-row">
                <svg width="13.5" height="13.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                  <polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span class="unit-name">{{ resident.unit?.flatAddress || 'No unit assigned' }}</span>
              </div>
            </div>
          </div>

          <div class="card-right">
            <span class="join-date">Joined {{ fmtDate(resident.createdAt) }}</span>
            <div class="arrow-wrap">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"/>
              </svg>
            </div>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { residentsApi, estatesApi } from '@/api/index'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import BrandSpinner from '@/components/BrandSpinner.vue'

const router = useRouter()
const { showToast } = useToast()
const auth = useAuthStore()

const loading = ref(true)
const residents = ref<any[]>([])
const estates = ref<any[]>([])
const search = ref('')
const estateFilter = ref('')
const roleFilter = ref('all')
const statusFilter = ref('all')
const viewMode = ref<'grid' | 'list'>('grid')

const activeCardMenuId = ref<number | null>(null)

function toggleCardMenu(id: number) {
  activeCardMenuId.value = activeCardMenuId.value === id ? null : id
}

function closeCardMenu() {
  activeCardMenuId.value = null
}

async function handleMenuAction(action: 'view' | 'edit' | 'toggle-status', resident: any) {
  closeCardMenu()
  if (action === 'view') {
    goToDetail(resident.id)
    return
  }
  if (action === 'edit') {
    goToDetail(resident.id, true)
    return
  }
  if (action === 'toggle-status') {
    await toggleResidentStatus(resident)
  }
}

async function toggleResidentStatus(resident: any) {
  const nextState = !resident.isActive
  try {
    await residentsApi.update(resident.id, { is_active: nextState })
    resident.isActive = nextState
    showToast(
      `${resident.user?.name || 'Resident'} is now ${nextState ? 'active' : 'disabled'}.`,
      nextState ? 'success' : 'info'
    )
  } catch (err: any) {
    showToast(err?.response?.data?.message || 'Failed to update resident status.', 'error')
  }
}

const filteredResidents = computed(() => {
  return residents.value.filter(r => {
    if (roleFilter.value !== 'all' && (r.role ?? 'tenant').toLowerCase() !== roleFilter.value) {
      return false
    }
    if (statusFilter.value === 'active' && !r.isActive) {
      return false
    }
    if (statusFilter.value === 'inactive' && r.isActive) {
      return false
    }
    return true
  })
})

let searchTimer: ReturnType<typeof setTimeout>
function debouncedLoad() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(load, 350)
}

async function load() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (search.value) params.search = search.value
    if (estateFilter.value) params.estate_id = estateFilter.value
    const res = await residentsApi.getAll(params)
    residents.value = res.data.residents
  } catch {
    showToast('Failed to load residents.', 'error')
  } finally {
    loading.value = false
  }
}

async function loadEstates() {
  try {
    const res = await estatesApi.getAll()
    estates.value = res.data.estates
  } catch {/* ignore */}
}

function openCreate() {
  const currentEstateId = estateFilter.value || auth.activeEstateId || (estates.value.length ? estates.value[0].id : '')
  if (currentEstateId) {
    router.push(`/residents/new?estate_id=${currentEstateId}`)
  } else {
    router.push('/residents/new')
  }
}

function goToDetail(id: number, edit = false) {
  if (edit) {
    router.push({ path: `/residents/${id}`, query: { edit: '1' } })
  } else {
    router.push(`/residents/${id}`)
  }
}

function initials(name?: string) {
  if (!name) return '?'
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

function fmtDate(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(() => {
  load()
  loadEstates()
  window.addEventListener('click', closeCardMenu)
})

onUnmounted(() => {
  window.removeEventListener('click', closeCardMenu)
})
</script>

<style scoped>
.residents-page {
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

.hero-card--sand {
  background: linear-gradient(145deg, #5c3b12 0%, #9e6523 55%, #dca966 100%);
  box-shadow: 0 4px 18px rgba(92, 59, 18, 0.18);
}

.hero-title-group {
  flex: 1;
  min-width: 240px;
}

.hero-eyebrow {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  color: rgba(255, 255, 255, 0.74);
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

.hero-button {
  background: #ffffff;
  color: #7d4b11;
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
  background: #FFFDF9;
}

/* ── Toolbar Card ──────────────────────────────────── */
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

/* ── 3-COLUMN CARDS GRID (User's Drawing) ───────────── */
.residents-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
  gap: 16px;
}

.resident-grid-card {
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

.resident-grid-card:hover {
  transform: translateY(-3px);
  border-color: #D1D5DB;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06);
}

.resident-grid-card:focus-visible {
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
  padding: 9px 12px;
  display: flex;
  flex-direction: column;
  gap: 3px;
  transition: background 160ms ease;
}

.resident-grid-card:hover .grid-unit-box {
  background: #F3F4F6;
}

.grid-unit-label {
  font-size: 10px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #6B7280;
}

.grid-unit-val {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #111827;
}

.grid-unit-text {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
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
  background: #F3F4F6;
  color: #374151;
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #E5E7EB;
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
.resident-cards-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.resident-card {
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

.resident-card:hover {
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

.resident-primary-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.resident-name-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.resident-name {
  font-size: 14.5px;
  font-weight: 600;
  color: #111827;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.unit-location-row {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12.5px;
  color: #4B5563;
}

.unit-name {
  color: #374151;
  font-weight: 500;
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

.resident-card:hover .arrow-wrap {
  transform: translateX(3px);
  color: #0A5C38;
  background: #ECFDF5;
}

@media (max-width: 768px) {
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
  .residents-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 580px) {
  .join-date {
    display: none;
  }
}
</style>
