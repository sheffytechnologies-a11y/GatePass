<template>
  <div class="mobile-page">
    <section class="hero-card hero-card--sand">
      <div class="hero-title-group">
        <div class="hero-eyebrow">Resident Registry</div>
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

    <!-- Filter & Search Toolbar (consistent with Properties tab design) -->
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

    <section class="card section-card">
      <div class="section-head">
        <div>
          <h2 class="section-title">Residents</h2>
          <p class="section-copy">{{ filteredResidents.length }} resident{{ filteredResidents.length === 1 ? '' : 's' }} loaded.</p>
        </div>
      </div>

      <div v-if="loading" class="loading-state"><BrandSpinner :size="40" color="var(--c-primary)" /></div>
      <div v-else-if="filteredResidents.length === 0" class="empty-state">No residents found.</div>
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
import { ref, computed, onMounted } from 'vue'
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

function goToDetail(id: number) {
  router.push(`/residents/${id}`)
}

function initials(name?: string) {
  if (!name) return '?'
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

function fmtDate(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-NG', { dateStyle: 'medium' })
}

onMounted(() => { load(); loadEstates() })
</script>

<style scoped>
.mobile-page { display: flex; flex-direction: column; gap: 16px; }
.hero-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 24px 28px;
  border-radius: 24px;
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
  font-size: 24px;
  line-height: 1.15;
  font-family: var(--font-display);
  font-weight: 800;
  color: #ffffff;
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
.hero-button:active {
  transform: translateY(0);
}

.toolbar-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 12px 16px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  padding: 8px 14px;
  flex: 1;
  min-width: 220px;
}

.search-input {
  border: none;
  background: transparent;
  outline: none;
  width: 100%;
  font-size: 13.5px;
  color: #111827;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: nowrap;
  flex-shrink: 0;
}

.toolbar-select {
  padding: 8px 14px;
  font-size: 13px;
  border-radius: 10px;
  border: 1px solid #E5E7EB;
  background-color: #F9FAFB;
  color: #374151;
  font-weight: 500;
  cursor: pointer;
  outline: none;
  white-space: nowrap;
  width: auto;
  min-width: 130px;
  flex: 0 0 auto;
  transition: border-color 150ms ease, background-color 150ms ease;
}

.toolbar-select:focus {
  border-color: #0A5C38;
  background-color: #ffffff;
}

.section-card { padding: 16px; }
.section-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.section-title { font-size: 18px; font-weight: 700; }
.section-copy { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.loading-state, .empty-state { min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--c-muted); }
.stack-list { display: flex; flex-direction: column; gap: 10px; }
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

.resident-card:focus-visible {
  outline: none;
  border-color: #0A5C38;
  box-shadow: 0 0 0 2px rgba(10, 92, 56, 0.2);
}

.card-left {
  display: flex;
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.avatar-box {
  position: relative;
  width: 42px;
  height: 42px;
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
  font-size: 13.5px;
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

.badge-role {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  padding: 2px 7px;
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

@media (max-width: 580px) {
  .join-date {
    display: none;
  }
  .resident-card {
    padding: 12px 14px;
  }
}

.record-card { padding: 16px; border-radius: 20px; }
.record-top, .record-bottom { display: flex; justify-content: space-between; gap: 12px; }
.record-bottom { margin-top: 14px; align-items: center; }
.record-title { font-size: 17px; font-weight: 700; color: var(--c-text); }
.record-meta { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.record-badges { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 14px; padding: 14px; background: #f7f4ee; border-radius: 18px; }
.detail-label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--c-muted); margin-bottom: 4px; }
.record-sub { color: var(--c-muted); font-size: 12px; }
.row-actions { display: flex; gap: 8px; }
.chip-row { display: flex; gap: 8px; overflow-x: auto; }
.unit-add-link { background: none; border: none; color: var(--c-primary); font-size: 12px; font-weight: 700; }
.unit-add-link:disabled { color: var(--c-muted); cursor: not-allowed; }
.unit-add-box { display: flex; gap: 8px; margin-top: 8px; }
.unit-add-box .form-input { flex: 1; }
.chip { border: none; border-radius: 999px; padding: 10px 14px; background: #edf2ee; color: #4f5f56; white-space: nowrap; font-weight: 700; }
.chip.active { background: var(--c-primary); color: white; }
/* Edit/delete sheets */
.sheet-overlay { position: fixed; inset: 0; z-index: 40; background: rgba(10,18,14,0.65); backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; padding: 16px; }
.sheet { width: min(var(--shell-width), 100%); border-radius: 28px; padding: 18px; }
.sheet-sm { max-width: 420px; }
.sheet-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.sheet-title { font-size: 18px; font-weight: 700; }
.sheet-close { width: 32px; height: 32px; border: none; border-radius: 50%; background: #edf2ee; color: var(--c-text); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.sheet-body { display: flex; flex-direction: column; gap: 14px; }
.sheet-actions { display: flex; gap: 10px; margin-top: 18px; }
.sheet-actions .btn { flex: 1; justify-content: center; }
.confirm-copy { color: var(--c-muted); font-size: 14px; margin-bottom: 4px; }
.credentials-list { display: flex; flex-direction: column; gap: 8px; margin-top: 12px; }
.credential-row {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  padding: 12px 14px; background: var(--c-bg); border-radius: var(--radius);
}
.credential-row .record-sub { display: block; margin-top: 2px; }
.credential-password {
  font-family: monospace; font-size: 14px; font-weight: 700;
  background: var(--c-surface); border: 1px solid var(--c-border);
  padding: 4px 10px; border-radius: 8px; color: var(--c-primary);
}

/* Desktop-First Responsive Modal */
.desktop-modal-card {
  background: #ffffff;
  width: 100%;
  max-width: 1060px;
  max-height: 92vh;
  border-radius: 24px;
  box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.35);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: modalScaleIn 0.22s ease-out;
}

@keyframes modalScaleIn {
  from { opacity: 0; transform: scale(0.97) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px;
  border-bottom: 1px solid #E5E7EB;
  background: #FAFAFA;
}

.modal-header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.btn-icon-back {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 1px solid #E2E8F0;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #334155;
  cursor: pointer;
  transition: all 0.15s ease;
}
.btn-icon-back:hover {
  background: #F1F5F9;
  color: #0F172A;
}

.modal-eyebrow {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.1em;
  color: #0A5C38;
}

.modal-main-title {
  font-size: 18px;
  font-weight: 800;
  color: #0F172A;
  margin-top: 2px;
  font-family: var(--font-display, inherit);
}

.btn-icon-close {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid #E2E8F0;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  color: #64748B;
  cursor: pointer;
  transition: all 0.15s ease;
}
.btn-icon-close:hover {
  background: #FEE2E2;
  color: #DC2626;
  border-color: #FCA5A5;
}

.modal-scroll-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
  background: #F8FAFC;
}

.two-column-form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  align-items: start;
}

.form-column {
  background: #ffffff;
  border: 1px solid #E2E8F0;
  border-radius: 18px;
  padding: 22px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.column-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding-bottom: 14px;
  border-bottom: 1px solid #F1F5F9;
}
.column-header--between {
  justify-content: space-between;
}
.column-header-left {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.column-icon-badge {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #ECFDF5;
  color: #059669;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.column-icon-badge--blue {
  background: #EFF6FF;
  color: #2563EB;
}

.column-title {
  font-size: 15px;
  font-weight: 700;
  color: #0F172A;
}

.column-sub {
  font-size: 12px;
  color: #64748B;
  margin-top: 2px;
}

/* Photo Upload Space */
.photo-upload-container {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 16px;
  background: #F8FAFC;
  border: 1px dashed #CBD5E1;
  border-radius: 14px;
}

.photo-preview-box {
  width: 72px;
  height: 72px;
  border-radius: 16px;
  background: #ffffff;
  border: 2px solid #E2E8F0;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  flex-shrink: 0;
  transition: border-color 0.15s ease;
}
.photo-preview-box:hover {
  border-color: #0A5C38;
}

.photo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.photo-placeholder {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.camera-badge {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 20px;
  height: 20px;
  background: #0A5C38;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.25);
}

.photo-meta {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.photo-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-outline-photo {
  background: #ffffff;
  border: 1px solid #CBD5E1;
  color: #334155;
  font-weight: 600;
  font-size: 12px;
  padding: 5px 12px;
  border-radius: 8px;
  cursor: pointer;
}
.btn-outline-photo:hover {
  border-color: #0A5C38;
  color: #0A5C38;
}

.btn-ghost-danger {
  background: none;
  border: none;
  color: #DC2626;
  font-size: 12px;
  font-weight: 600;
  padding: 5px 8px;
  cursor: pointer;
}
.btn-ghost-danger:hover {
  text-decoration: underline;
}

.photo-hint {
  font-size: 11px;
  color: #64748B;
}

.form-row-2col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.address-row {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 8px;
}

.toggle-control-card {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 14px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
}
.toggle-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.toggle-title {
  font-size: 13px;
  font-weight: 700;
  color: #1E293B;
}
.toggle-desc {
  font-size: 11.5px;
  color: #64748B;
  line-height: 1.4;
}

.btn-outline-add {
  background: #F0FDF4;
  border: 1px solid #BBF7D0;
  color: #047857;
  font-weight: 700;
  font-size: 12px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 10px;
  cursor: pointer;
  white-space: nowrap;
}
.btn-outline-add:hover {
  background: #DCFCE7;
  border-color: #86EFAC;
}

/* Occupants Empty Box */
.occupants-empty-box {
  padding: 32px 20px;
  text-align: center;
  background: #F8FAFC;
  border: 1.5px dashed #CBD5E1;
  border-radius: 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}
.occupants-empty-icon {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #E2E8F0;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
}
.occupants-empty-box h4 {
  font-size: 14px;
  font-weight: 700;
  color: #334155;
  margin: 0;
}
.occupants-empty-box p {
  font-size: 12px;
  color: #64748B;
  max-width: 320px;
  line-height: 1.45;
  margin: 0;
}

/* Occupants Cards List */
.occupants-cards-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
  max-height: 460px;
  overflow-y: auto;
  padding-right: 4px;
}

.occupant-card {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  position: relative;
}

.occupant-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.occupant-badge {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  background: #E2E8F0;
  color: #334155;
  padding: 2px 8px;
  border-radius: 6px;
}

.tenant-del-btn {
  background: none;
  border: none;
  color: #94A3B8;
  padding: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.15s ease;
}
.tenant-del-btn:hover {
  background: #FEE2E2;
  color: #DC2626;
}

.occupant-photo-row {
  display: flex;
  align-items: center;
  gap: 12px;
}

.occupant-photo-box {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  background: #ffffff;
  border: 1.5px solid #CBD5E1;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  cursor: pointer;
  flex-shrink: 0;
}
.occupant-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.occupant-photo-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
}

.occupant-photo-actions {
  display: flex;
  align-items: center;
  gap: 8px;
}
.link-btn-sm {
  background: none;
  border: none;
  padding: 0;
  font-size: 11.5px;
  font-weight: 600;
  color: #047857;
  cursor: pointer;
}
.link-btn-sm:hover {
  text-decoration: underline;
}

.form-error-banner {
  background: #FEF2F2;
  border: 1px solid #FCA5A5;
  color: #991B1B;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Modal Footer Bar */
.modal-footer-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 24px;
  border-top: 1px solid #E5E7EB;
  background: #ffffff;
  flex-wrap: wrap;
  gap: 12px;
}

.footer-info-hint {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #047857;
  font-weight: 500;
}

.modal-footer-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-save-resident {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  font-size: 13.5px;
  font-weight: 700;
}

.d-none { display: none !important; }

.toggle-switch { position: relative; display: inline-flex; cursor: pointer; flex-shrink: 0; }
.toggle-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
.toggle-track { width: 44px; height: 26px; background: #E0E0E0; border-radius: 13px; transition: background 0.2s; position: relative; display: block; }
.toggle-switch input:checked + .toggle-track { background: var(--c-primary); }
.toggle-thumb { position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: white; transition: left 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.toggle-switch input:checked + .toggle-track .toggle-thumb { left: 21px; }

@media (max-width: 860px) {
  .two-column-form-grid {
    grid-template-columns: 1fr;
  }
  .desktop-modal-card {
    max-height: 98vh;
  }
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
    flex: 1 1 calc(50% - 10px);
    min-width: 0;
  }
}
@media (max-width: 600px) {
  .form-row-2col {
    grid-template-columns: 1fr;
  }
  .address-row {
    grid-template-columns: 1fr;
  }
  .desktop-modal-card {
    border-radius: 0;
    max-height: 100vh;
    height: 100%;
  }
}
@media (max-width: 420px) {
  .record-top, .record-bottom { flex-direction: column; align-items: flex-start; }
  .detail-grid { grid-template-columns: 1fr; }
  .record-badges { justify-content: flex-start; }
}

.property-picker-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  padding: 12px;
}
.field-hint {
  font-size: 11.5px;
  color: #6B7280;
  margin: 4px 0 0;
}

</style>
