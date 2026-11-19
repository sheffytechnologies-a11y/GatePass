<template>
  <div class="mobile-page passes-page">
    <!-- Top Hero Header Container Card (Emerald Gradient) -->
    <section class="hero-card hero-card--emerald">
      <div class="hero-title-group">
        <div class="hero-eyebrow">VISITOR PASSES</div>
        <h1 class="hero-title">Track, issue and manage visitor gate passes.</h1>
      </div>
      <button class="hero-button" @click="openCreate">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        <span>New Pass</span>
      </button>
    </section>

    <!-- Filter & Search Toolbar (Single Line Pattern) -->
    <div class="toolbar-card">
      <div class="search-box">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          v-model="search"
          type="search"
          placeholder="Search by visitor name or phone..."
          class="search-input"
          @input="debouncedLoad"
          @keyup.enter="load"
        />
      </div>

      <div class="filter-group">
        <div class="status-chips-wrap">
          <button
            v-for="item in statusOptions"
            :key="item.value"
            type="button"
            class="filter-chip"
            :class="{ active: statusFilter === item.value }"
            @click="setStatus(item.value)"
          >
            {{ item.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- Passes Feed Section -->
    <section class="passes-section">
      <div class="section-head">
        <div>
          <h2 class="section-title">Pass Feed</h2>
          <p class="section-copy">Page {{ page }} of {{ totalPages || 1 }} &bull; {{ total }} total registered passes</p>
        </div>
        <button class="btn btn-outline btn-sm" @click="load">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
          </svg>
          <span>Refresh</span>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="empty-stack">
        <BrandSpinner :size="40" color="var(--c-primary)" />
      </div>

      <!-- Empty State -->
      <div v-else-if="passes.length === 0" class="empty-card card">
        <div class="empty-icon">🎫</div>
        <div class="empty-title">No passes found</div>
        <p class="empty-desc">No visitor passes match your active filter criteria.</p>
        <button v-if="statusFilter || search" class="btn btn-outline btn-sm mt-3" @click="resetFilters">
          Reset Filters
        </button>
      </div>

      <!-- Pass Cards Grid (2-Column Desktop Grid) -->
      <div v-else class="pass-cards-grid">
        <article
          v-for="pass in passes"
          :key="pass.ulid"
          class="pass-card"
          tabindex="0"
          role="button"
          :aria-label="'View ' + pass.visitorName + ' pass details'"
          @click="goToDetail(pass.ulid)"
          @keydown.enter="goToDetail(pass.ulid)"
        >
          <!-- Card Head: Avatar, Identity, Status & 3-Dots Menu -->
          <div class="card-head">
            <div class="visitor-avatar">
              {{ getInitials(pass.visitorName) }}
            </div>

            <div class="visitor-info-box">
              <div class="visitor-name-title">{{ pass.visitorName }}</div>
              <div class="host-meta-sub">
                <span class="host-text">{{ pass.resident?.name || 'Estate General' }}</span>
                <span v-if="pass.resident?.unit" class="unit-pill">
                  🏡 Unit {{ pass.resident.unit }}
                </span>
              </div>
            </div>

            <div class="card-head-right">
              <span class="status-badge" :class="statusBadgeClass(pass.status)">
                <span class="status-dot"></span>
                <span>{{ pass.status }}</span>
              </span>

              <!-- 3-Dots Action Menu Trigger -->
              <div class="card-more-wrapper" @click.stop>
                <button
                  type="button"
                  class="card-more-btn"
                  :class="{ 'is-active': activeCardMenuId === pass.ulid }"
                  title="Pass options"
                  aria-label="Pass options"
                  @click.stop="toggleCardMenu(pass.ulid)"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="5" r="2.2"/>
                    <circle cx="12" cy="12" r="2.2"/>
                    <circle cx="12" cy="19" r="2.2"/>
                  </svg>
                </button>

                <!-- 3-Dots Dropdown Popover Menu -->
                <div v-if="activeCardMenuId === pass.ulid" class="card-dropdown-popover" @click.stop>
                  <button type="button" class="dropdown-item" @click="goToDetail(pass.ulid)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                      <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <span>View Details</span>
                  </button>

                  <button
                    v-if="pass.status === 'Pending'"
                    type="button"
                    class="dropdown-item dropdown-item--primary"
                    @click="allowEntryQuick(pass)"
                  >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>Allow Entry</span>
                  </button>

                  <button
                    v-if="pass.status === 'On-site'"
                    type="button"
                    class="dropdown-item"
                    @click="markExitedQuick(pass)"
                  >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                      <polyline points="16 17 21 12 16 7"/>
                      <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Mark Exited</span>
                  </button>

                  <button
                    type="button"
                    class="dropdown-item"
                    @click="copyPassCode(pass.ulid)"
                  >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                    <span>Copy Code</span>
                  </button>

                  <div class="dropdown-divider"></div>

                  <button
                    v-if="pass.status !== 'Revoked' && pass.status !== 'Expired'"
                    type="button"
                    class="dropdown-item dropdown-item--danger"
                    @click="confirmRevoke(pass)"
                  >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <circle cx="12" cy="12" r="10"/>
                      <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                    </svg>
                    <span>Revoke Pass</span>
                  </button>

                  <button
                    type="button"
                    class="dropdown-item dropdown-item--danger"
                    @click="confirmDelete(pass)"
                  >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <polyline points="3 6 5 6 21 6"/>
                      <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                    <span>Delete Pass</span>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Card Body: Metadata Strip -->
          <div class="card-meta-strip">
            <!-- Phone -->
            <div class="meta-pill">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
              <span>{{ pass.visitorPhone || 'No Phone' }}</span>
            </div>

            <!-- Vehicle Plate -->
            <div v-if="pass.vehiclePlate" class="meta-plate-pill">
              <span class="plate-flag">🇳🇬</span>
              <span class="plate-num">{{ pass.vehiclePlate }}</span>
            </div>

            <!-- Pass Type -->
            <div class="meta-pill">
              <span>{{ pass.type === 'recurring' ? '🔁 Recurring' : '⚡ One-Time' }}</span>
            </div>

            <!-- Flagged Tag if any -->
            <div v-if="pass.itemsFlagged" class="meta-pill meta-pill--flagged">
              <span>🚩 Flagged Items</span>
            </div>
          </div>

          <!-- Card Bottom: Purpose & Expiry -->
          <div class="card-foot">
            <div class="purpose-snippet">
              <span class="foot-label">Purpose:</span>
              <span class="foot-val">{{ pass.purpose || 'General social visit' }}</span>
            </div>

            <div class="foot-right">
              <span class="expiry-snippet">Exp: {{ fmtDate(pass.expiresAt) }}</span>
              <div class="foot-arrow">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
              </div>
            </div>
          </div>
        </article>
      </div>

      <!-- Pagination Footer -->
      <div v-if="totalPages > 1" class="pager-row">
        <button class="btn btn-outline" :disabled="page === 1" @click="changePage(page - 1)">Previous</button>
        <span class="page-count-text">Page {{ page }} of {{ totalPages }}</span>
        <button class="btn btn-outline" :disabled="page === totalPages" @click="changePage(page + 1)">Next</button>
      </div>
    </section>

    <!-- Create Pass Sheet Modal -->
    <div v-if="modalOpen" class="sheet-overlay" @click.self="closeModal">
      <div class="sheet card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">CREATE PASS</div>
            <h3 class="sheet-title">New Visitor Pass</h3>
          </div>
          <button class="sheet-close" @click="closeModal">✕</button>
        </div>
        <div class="sheet-body">
          <div class="form-group">
            <label class="form-label">Visitor Name</label>
            <input v-model="form.visitor_name" class="form-input" placeholder="e.g. John Doe" />
          </div>
          <div class="form-group">
            <label class="form-label">Visitor Phone</label>
            <input v-model="form.visitor_phone" class="form-input" placeholder="08012345678" />
          </div>
          <div class="form-group">
            <label class="form-label">Vehicle Registration Plate</label>
            <input v-model="form.vehicle_plate" class="form-input" placeholder="e.g. KRD-551XQ" />
          </div>
          <div class="form-group">
            <label class="form-label">Pass Type</label>
            <div class="type-toggle-row">
              <button
                type="button"
                class="type-pill-btn"
                :class="{ active: form.type === 'one-time' }"
                @click="form.type = 'one-time'"
              >
                One-Time
              </button>
              <button
                type="button"
                class="type-pill-btn"
                :class="{ active: form.type === 'recurring' }"
                @click="form.type = 'recurring'"
              >
                Recurring
              </button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Host Resident</label>
            <select v-model="form.resident_id" class="form-select">
              <option value="">Select resident…</option>
              <option v-for="resident in residents" :key="resident.id" :value="resident.id">
                {{ resident.user?.name }} — Unit {{ resident.unit?.number || resident.unit?.flat_address || '—' }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Purpose of Visit</label>
            <textarea v-model="form.purpose" class="form-textarea" rows="2" placeholder="Reason for access"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Expiration Date & Time</label>
            <input v-model="form.expires_at" class="form-input" type="datetime-local" />
          </div>
          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="closeModal">Cancel</button>
          <button class="btn btn-primary" :disabled="saving" @click="save">
            {{ saving ? 'Creating…' : 'Issue Pass' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Revoke Modal -->
    <div v-if="revokeTarget" class="sheet-overlay" @click.self="revokeTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow text-danger">REVOKE ACCESS</div>
            <h3 class="sheet-title">Revoke {{ revokeTarget.visitorName }}?</h3>
          </div>
          <button class="sheet-close" @click="revokeTarget = null">✕</button>
        </div>
        <p class="confirm-copy">The security gate terminal will immediately decline this visitor pass on arrival.</p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="revokeTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doRevoke">
            {{ saving ? 'Revoking…' : 'Revoke Pass' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div v-if="deleteTarget" class="sheet-overlay" @click.self="deleteTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow text-danger">DELETE RECORD</div>
            <h3 class="sheet-title">Delete {{ deleteTarget.visitorName }}?</h3>
          </div>
          <button class="sheet-close" @click="deleteTarget = null">✕</button>
        </div>
        <p class="confirm-copy">This pass record will be permanently deleted from the database.</p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doDelete">
            {{ saving ? 'Deleting…' : 'Delete Pass' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { passesApi, residentsApi } from '@/api/index'
import { useToast } from '@/composables/useToast'
import client from '@/api/client'

const route = useRoute()
const router = useRouter()
const { showToast } = useToast()

const loading = ref(true)
const passes = ref<any[]>([])
const search = ref('')
const statusFilter = ref('')
const page = ref(1)
const total = ref(0)
const totalPages = ref(1)

const activeCardMenuId = ref<string | null>(null)
const modalOpen = ref(false)
const saving = ref(false)
const formError = ref('')
const revokeTarget = ref<any>(null)
const deleteTarget = ref<any>(null)
const residents = ref<any[]>([])

const statusOptions = [
  { label: 'All', value: '' },
  { label: 'Pending', value: 'Pending' },
  { label: 'On-site', value: 'On-site' },
  { label: 'Exited', value: 'Exited' },
  { label: 'Expired', value: 'Expired' },
  { label: 'Revoked', value: 'Revoked' },
]

const form = ref({
  visitor_name: '',
  visitor_phone: '',
  vehicle_plate: '',
  type: 'one-time',
  resident_id: '' as number | '',
  purpose: '',
  expires_at: '',
})

let searchTimer: ReturnType<typeof setTimeout>
function debouncedLoad() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(load, 300)
}

async function load() {
  loading.value = true
  closeMenus()
  try {
    const params: Record<string, unknown> = { page: page.value }
    if (search.value.trim()) params.search = search.value.trim()
    if (statusFilter.value) params.status = statusFilter.value
    const res = await passesApi.getAll(params)
    passes.value = res.data.passes ?? []
    total.value = res.data.total ?? 0
    totalPages.value = res.data.pages ?? 1
  } catch {
    showToast('Failed to load passes.', 'error')
  } finally {
    loading.value = false
  }
}

function setStatus(value: string) {
  statusFilter.value = value
  page.value = 1
  load()
}

function resetFilters() {
  statusFilter.value = ''
  search.value = ''
  page.value = 1
  load()
}

function changePage(nextPage: number) {
  page.value = nextPage
  load()
}

function goToDetail(ulid: string) {
  router.push(`/passes/${ulid}`)
}

function toggleCardMenu(ulid: string) {
  activeCardMenuId.value = activeCardMenuId.value === ulid ? null : ulid
}

function closeMenus() {
  activeCardMenuId.value = null
}

async function allowEntryQuick(pass: any) {
  closeMenus()
  try {
    await client.patch(`/v1/passes/${pass.ulid}/allow-entry`)
    showToast(`${pass.visitorName} admitted through gate.`, 'success')
    load()
  } catch (error: any) {
    showToast(error?.response?.data?.message ?? 'Failed to allow entry.', 'error')
  }
}

async function markExitedQuick(pass: any) {
  closeMenus()
  try {
    await client.patch(`/v1/passes/${pass.ulid}/mark-exited`)
    showToast(`${pass.visitorName} marked as exited.`, 'success')
    load()
  } catch (error: any) {
    showToast(error?.response?.data?.message ?? 'Failed to update exit.', 'error')
  }
}

function copyPassCode(ulid: string) {
  closeMenus()
  navigator.clipboard.writeText(ulid).then(() => {
    showToast('Pass code copied to clipboard', 'success')
  }).catch(() => {
    showToast('Failed to copy code', 'error')
  })
}

async function openCreate() {
  formError.value = ''
  form.value = {
    visitor_name: '',
    visitor_phone: '',
    vehicle_plate: '',
    type: 'one-time',
    resident_id: '',
    purpose: '',
    expires_at: '',
  }
  if (residents.value.length === 0) {
    try {
      const res = await residentsApi.getAll()
      residents.value = res.data.residents ?? []
    } catch {}
  }
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
}

async function save() {
  formError.value = ''
  if (!form.value.visitor_name.trim()) {
    formError.value = 'Visitor name is required.'
    return
  }
  if (!form.value.resident_id) {
    formError.value = 'Please select a host resident.'
    return
  }

  saving.value = true
  try {
    const payload: Record<string, unknown> = {
      visitor_name: form.value.visitor_name.trim(),
      resident_id: form.value.resident_id,
      type: form.value.type,
    }
    if (form.value.visitor_phone.trim()) payload.visitor_phone = form.value.visitor_phone.trim()
    if (form.value.vehicle_plate.trim()) payload.vehicle_plate = form.value.vehicle_plate.trim()
    if (form.value.purpose.trim()) payload.purpose = form.value.purpose.trim()
    if (form.value.expires_at) payload.expires_at = form.value.expires_at

    await passesApi.create(payload)
    showToast('Pass issued successfully.', 'success')
    closeModal()
    load()
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? 'Something went wrong.'
  } finally {
    saving.value = false
  }
}

function confirmRevoke(pass: any) {
  closeMenus()
  revokeTarget.value = pass
}

function confirmDelete(pass: any) {
  closeMenus()
  deleteTarget.value = pass
}

async function doRevoke() {
  if (!revokeTarget.value) return
  saving.value = true
  try {
    await passesApi.revoke(revokeTarget.value.ulid)
    showToast('Pass revoked successfully.', 'success')
    revokeTarget.value = null
    load()
  } catch {
    showToast('Failed to revoke pass.', 'error')
  } finally {
    saving.value = false
  }
}

async function doDelete() {
  if (!deleteTarget.value) return
  saving.value = true
  try {
    await passesApi.delete(deleteTarget.value.ulid)
    showToast('Pass deleted.', 'success')
    deleteTarget.value = null
    load()
  } catch {
    showToast('Failed to delete pass.', 'error')
  } finally {
    saving.value = false
  }
}

function getInitials(name?: string | null): string {
  if (!name) return 'GP'
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

function statusBadgeClass(status: string) {
  return {
    'status-badge--onsite':   status === 'On-site',
    'status-badge--pending':  status === 'Pending',
    'status-badge--exited':   ['Expired', 'Exited'].includes(status),
    'status-badge--revoked':  ['Revoked', 'Declined'].includes(status),
    'status-badge--flagged':  status === 'Item Flagged',
  }
}

function fmtDate(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(() => {
  window.addEventListener('click', closeMenus)
  if (route.query.status) {
    statusFilter.value = String(route.query.status)
  }
  load()
})

onUnmounted(() => {
  window.removeEventListener('click', closeMenus)
  if (searchTimer) clearTimeout(searchTimer)
})
</script>

<style scoped>
.mobile-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

/* ── Hero Header Container ─────────────────────────── */
.hero-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  padding: 24px 28px;
  border-radius: 20px;
  color: white;
  background: linear-gradient(145deg, #0d3824 0%, #165637 55%, #2a8a5b 100%);
  box-shadow: 0 4px 18px rgba(10, 92, 56, 0.18);
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
}

.hero-title {
  margin-top: 6px;
  font-size: 24px;
  line-height: 1.15;
  font-weight: 800;
  font-family: var(--font-display, inherit);
  color: #ffffff;
  margin-bottom: 0;
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
  border-radius: var(--btn-radius-lg, 12px);
  border: 1px solid rgba(255, 255, 255, 0.4);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
  white-space: nowrap;
  cursor: pointer;
  transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease;
  flex-shrink: 0;
}

.hero-button:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
  background: #F0FDF4;
}

/* ── Filter & Search Toolbar (Single Line Pattern) ──── */
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
  min-width: 200px;
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

.status-chips-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  overflow-x: auto;
}

.filter-chip {
  border: 1px solid #E5E7EB;
  border-radius: var(--btn-radius-pill, 999px);
  padding: 6px 14px;
  font-size: 12px;
  font-weight: 600;
  background: #F9FAFB;
  color: #4B5563;
  cursor: pointer;
  white-space: nowrap;
  transition: all 140ms ease;
}

.filter-chip:hover {
  background: #F3F4F6;
  border-color: #D1D5DB;
  color: #111827;
}

.filter-chip.active {
  background: #0A5C38;
  color: #ffffff;
  border-color: #0A5C38;
  box-shadow: 0 2px 6px rgba(10, 92, 56, 0.2);
}

/* ── Feed Section ──────────────────────────────────── */
.passes-section {
  margin-top: 4px;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 14px;
}

.section-title {
  font-size: 18px;
  font-weight: 800;
  color: #111827;
}

.section-copy {
  margin-top: 4px;
  color: var(--c-muted);
  font-size: 13px;
}

.empty-stack {
  min-height: 140px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-card {
  padding: 48px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  background: #ffffff;
  border-radius: 16px;
  border: 1px dashed #CBD5E1;
}

.empty-icon {
  font-size: 38px;
  margin-bottom: 6px;
}

.empty-title {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.empty-desc {
  font-size: 13px;
  color: #64748B;
  margin-top: 4px;
  max-width: 320px;
}

/* ── Pass Cards 2-Column Grid ──────────────────────── */
.pass-cards-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 14px;
}

.pass-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 18px 20px;
  cursor: pointer;
  transition: all 180ms cubic-bezier(0.16, 1, 0.3, 1);
  display: flex;
  flex-direction: column;
  gap: 12px;
  position: relative;
  user-select: none;
}

.pass-card:hover {
  transform: translateY(-2px);
  border-color: #D1D5DB;
  box-shadow: 0 8px 22px rgba(0, 0, 0, 0.06);
}

/* Card Head */
.card-head {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  position: relative;
}

.visitor-avatar {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0A5C38 0%, #16A34A 100%);
  color: #ffffff;
  font-weight: 800;
  font-size: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 2px 6px rgba(10, 92, 56, 0.16);
}

.visitor-info-box {
  flex: 1;
  min-width: 0;
}

.visitor-name-title {
  font-size: 15.5px;
  font-weight: 800;
  color: #111827;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.host-meta-sub {
  margin-top: 3px;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  color: #64748B;
  flex-wrap: wrap;
}

.host-text {
  font-weight: 600;
  color: #475569;
}

.unit-pill {
  font-size: 11px;
  font-weight: 700;
  color: #0A5C38;
  background: #ECFDF5;
  padding: 1px 6px;
  border-radius: 4px;
}

.card-head-right {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}

/* Status Badge */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  border-radius: 999px;
  font-size: 11.5px;
  font-weight: 700;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.status-badge--onsite {
  background: #DCFCE7;
  color: #15803D;
}

.status-badge--pending {
  background: #EFF6FF;
  color: #1D4ED8;
}

.status-badge--exited {
  background: #F1F5F9;
  color: #475569;
}

.status-badge--revoked {
  background: #FEE2E2;
  color: #B91C1C;
}

.status-badge--flagged {
  background: #FEF3C7;
  color: #B45309;
}

/* 3-Dots Action Menu */
.card-more-wrapper {
  position: relative;
  display: inline-flex;
}

.card-more-btn {
  background: transparent;
  border: none;
  width: 30px;
  height: 30px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6B7280;
  cursor: pointer;
  transition: all 140ms ease;
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
  width: 170px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.12);
  padding: 6px;
  display: flex;
  flex-direction: column;
  gap: 2px;
  z-index: 50;
  animation: fadeInPop 140ms ease;
}

@keyframes fadeInPop {
  from { opacity: 0; transform: translateY(-4px); }
  to { opacity: 1; transform: translateY(0); }
}

.dropdown-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 7px 10px;
  border: none;
  background: transparent;
  border-radius: 6px;
  font-size: 12.5px;
  font-weight: 600;
  color: #374151;
  text-align: left;
  cursor: pointer;
  transition: all 120ms ease;
}

.dropdown-item:hover {
  background: #F3F4F6;
  color: #111827;
}

.dropdown-item--primary {
  color: #0A5C38;
}

.dropdown-item--primary:hover {
  background: #ECFDF5;
  color: #064E3B;
}

.dropdown-item--danger {
  color: #DC2626;
}

.dropdown-item--danger:hover {
  background: #FEE2E2;
  color: #991B1B;
}

.dropdown-divider {
  height: 1px;
  background: #F1F5F9;
  margin: 4px 0;
}

/* Card Body Meta Strip */
.card-meta-strip {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.meta-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  padding: 4px 9px;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 600;
  color: #475569;
}

.meta-plate-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #FFFFFF;
  border: 1px solid #0F172A;
  padding: 3px 7px;
  border-radius: 4px;
}

.plate-flag {
  font-size: 10px;
}

.plate-num {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 11px;
  font-weight: 800;
  color: #0F172A;
  letter-spacing: 0.05em;
}

.meta-pill--flagged {
  background: #FEF3C7;
  color: #B45309;
  border-color: #FCD34D;
}

/* Card Bottom */
.card-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-top: 10px;
  border-top: 1px solid #F1F5F9;
  margin-top: 2px;
}

.purpose-snippet {
  display: flex;
  align-items: baseline;
  gap: 6px;
  font-size: 12px;
  color: #64748B;
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.foot-label {
  font-weight: 700;
  text-transform: uppercase;
  font-size: 10px;
  letter-spacing: 0.04em;
  color: #94A3B8;
  flex-shrink: 0;
}

.foot-val {
  color: #334155;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.foot-right {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.expiry-snippet {
  font-size: 11px;
  color: #94A3B8;
  font-weight: 500;
}

.foot-arrow {
  color: #94A3B8;
  transition: transform 160ms ease, color 160ms ease;
  display: flex;
  align-items: center;
}

.pass-card:hover .foot-arrow {
  transform: translateX(3px);
  color: #0A5C38;
}

/* ── Pagination ────────────────────────────────────── */
.pager-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 18px;
}

.page-count-text {
  font-size: 13px;
  font-weight: 600;
  color: #64748B;
}

/* ── Modals & Sheets ───────────────────────────────── */
.sheet-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(10, 18, 14, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.sheet {
  width: 100%;
  max-width: 480px;
  border-radius: 20px;
  padding: 24px;
  background: #ffffff;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18);
  max-height: 90vh;
  overflow-y: auto;
}

.sheet-sm {
  max-width: 400px;
}

.sheet-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
  margin-bottom: 16px;
}

.sheet-eyebrow {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--c-muted);
  font-weight: 700;
}

.sheet-title {
  margin-top: 4px;
  font-size: 20px;
  font-weight: 800;
  font-family: var(--font-display, inherit);
  color: #0F172A;
}

.sheet-close {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  background: #F1F5F9;
  color: #64748B;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 140ms ease;
}

.sheet-close:hover {
  background: #E2E8F0;
  color: #0F172A;
}

.sheet-body {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.type-toggle-row {
  display: flex;
  gap: 8px;
}

.type-pill-btn {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid #E2E8F0;
  border-radius: var(--btn-radius-sm, 8px);
  background: #F8FAFC;
  font-size: 13px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 140ms ease;
}

.type-pill-btn.active {
  background: #0A5C38;
  color: #ffffff;
  border-color: #0A5C38;
}

.sheet-actions {
  display: flex;
  gap: 10px;
  margin-top: 20px;
}

.sheet-actions .btn {
  flex: 1;
  justify-content: center;
}

.confirm-copy {
  color: #475569;
  font-size: 13.5px;
  line-height: 1.45;
  margin: 12px 0 0;
}

@media (max-width: 860px) {
  .pass-cards-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 680px) {
  .toolbar-card {
    flex-direction: column;
    align-items: stretch;
  }
  .hero-card {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
