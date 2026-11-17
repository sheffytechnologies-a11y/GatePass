<template>
  <div class="resident-detail-page">
    <!-- Top Bar with Back Link & Creative Sibling Navigator -->
    <div class="top-nav-bar">
      <router-link to="/residents" class="btn-back">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"/>
          <polyline points="12 19 5 12 12 5"/>
        </svg>
        <span>Back to Residents</span>
      </router-link>

      <!-- Sibling Switcher: Quick Next / Prev among all estate residents -->
      <div v-if="navIds.length > 1" class="sibling-nav">
        <button
          type="button"
          class="btn-sibling"
          :disabled="!hasPrev"
          title="Previous Resident (Left Arrow)"
          @click="navigatePrev"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 18 9 12 15 6"/>
          </svg>
          <span class="sibling-text">Prev</span>
        </button>

        <span class="sibling-counter">
          {{ currentIndex + 1 }} <span class="counter-divider">/</span> {{ navIds.length }}
        </span>

        <button
          type="button"
          class="btn-sibling"
          :disabled="!hasNext"
          title="Next Resident (Right Arrow)"
          @click="navigateNext"
        >
          <span class="sibling-text">Next</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 18 15 12 9 6"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <BrandSpinner :size="40" color="var(--c-primary)" />
      <p class="loading-label">Loading resident profile...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>{{ error }}</p>
      <router-link to="/residents" class="btn btn-primary">Return to Registry</router-link>
    </div>

    <!-- Main Profile Content -->
    <div v-else-if="resident" class="content-wrap">
      <!-- Hero Profile Header Card -->
      <section class="card hero-profile-card">
        <div class="profile-main">
          <!-- Avatar with status ring -->
          <div class="avatar-wrap">
            <div
              v-if="resident.user?.avatar"
              class="avatar-img"
              :style="{ backgroundImage: `url(${resident.user.avatar})` }"
            />
            <div v-else class="avatar-monogram">
              {{ initials(resident.user?.name) }}
            </div>
            <span
              class="status-pip"
              :class="resident.isActive ? 'pip-active' : 'pip-inactive'"
              :title="resident.isActive ? 'Active resident account' : 'Account disabled'"
            />
          </div>

          <!-- Name & Core Meta -->
          <div class="profile-info">
            <div class="profile-title-row">
              <h1 class="profile-name">{{ resident.user?.name || 'Unnamed Resident' }}</h1>
              <div class="pill-group">
                <span class="role-pill" :class="resident.role === 'owner' ? 'role-owner' : 'role-tenant'">
                  {{ resident.role === 'owner' ? 'Homeowner' : 'Tenant' }}
                </span>
                <span class="status-pill" :class="resident.isActive ? 'status-active' : 'status-disabled'">
                  <span class="pill-dot"></span>
                  {{ resident.isActive ? 'Active' : 'Disabled' }}
                </span>
              </div>
            </div>

            <div class="unit-banner">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              <span class="unit-address">{{ resident.unit?.flatAddress || 'Unassigned Unit' }}</span>
              <span v-if="resident.estate?.name" class="estate-bullet">• {{ resident.estate.name }}</span>
            </div>

            <p class="joined-meta">
              Onboarded on {{ fmtDate(resident.createdAt) }}
            </p>
          </div>
        </div>

        <!-- Quick Profile Actions -->
        <div class="profile-actions">
          <button
            type="button"
            class="btn btn-outline btn-edit"
            @click="openEditModal"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 20h9"/>
              <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
            </svg>
            <span>Edit Resident</span>
          </button>

          <!-- Toggle Disable / Enable Account CTA -->
          <button
            type="button"
            class="btn"
            :class="resident.isActive ? 'btn-disable' : 'btn-enable'"
            :disabled="togglingStatus"
            @click="toggleAccountStatus"
          >
            <BrandSpinner v-if="togglingStatus" :size="14" color="currentColor" />
            <svg v-else-if="resident.isActive" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
            <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span>{{ resident.isActive ? 'Disable Account' : 'Activate Account' }}</span>
          </button>
        </div>
      </section>

      <!-- 2-Column Grid: Details & Household on Left, Activities on Right -->
      <div class="profile-grid">
        <!-- LEFT COLUMN: Contact & Household -->
        <div class="grid-sidebar">
          <!-- Contact Card -->
          <div class="card detail-card">
            <h2 class="card-heading">Contact Information</h2>
            <div class="contact-rows">
              <div class="contact-item">
                <span class="contact-label">Phone Number</span>
                <div class="contact-val-row">
                  <span class="contact-val">{{ resident.user?.phone || '—' }}</span>
                  <button
                    v-if="resident.user?.phone"
                    type="button"
                    class="btn-icon-copy"
                    title="Copy phone number"
                    @click="copyText(resident.user.phone, 'Phone number copied!')"
                  >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                  </button>
                </div>
              </div>

              <div class="contact-item">
                <span class="contact-label">Email Address</span>
                <div class="contact-val-row">
                  <span class="contact-val">{{ resident.user?.email || 'No email attached' }}</span>
                  <button
                    v-if="resident.user?.email"
                    type="button"
                    class="btn-icon-copy"
                    title="Copy email address"
                    @click="copyText(resident.user.email, 'Email copied!')"
                  >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                  </button>
                </div>
              </div>

              <div class="contact-item">
                <span class="contact-label">Account System ID</span>
                <span class="contact-val font-mono">#{{ resident.id }}</span>
              </div>
            </div>
          </div>

          <!-- Household & Co-Residents in Same Unit -->
          <div class="card detail-card">
            <div class="card-heading-row">
              <h2 class="card-heading">Unit Household</h2>
              <span class="count-badge">{{ coResidents.length }} co-resident{{ coResidents.length === 1 ? '' : 's' }}</span>
            </div>

            <div v-if="coResidents.length === 0" class="empty-household">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              <p>Sole registered occupant of this unit.</p>
            </div>

            <div v-else class="coresidents-list">
              <router-link
                v-for="cr in coResidents"
                :key="cr.id"
                :to="'/residents/' + cr.id"
                class="coresident-chip"
                :title="'View ' + cr.user?.name"
              >
                <div class="chip-avatar">
                  <div
                    v-if="cr.user?.avatar"
                    class="chip-avatar-img"
                    :style="{ backgroundImage: `url(${cr.user.avatar})` }"
                  />
                  <span v-else class="chip-avatar-monogram">{{ initials(cr.user?.name) }}</span>
                </div>
                <div class="chip-info">
                  <div class="chip-name">{{ cr.user?.name || 'Co-Resident' }}</div>
                  <div class="chip-role">{{ cr.role === 'owner' ? 'Owner' : 'Tenant' }}</div>
                </div>
                <svg class="chip-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                  <polyline points="9 18 15 12 9 6"/>
                </svg>
              </router-link>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Interactive Activity Tabs (Passes & Dues) -->
        <div class="grid-main">
          <div class="card tabs-card">
            <!-- Tab Headers -->
            <div class="tab-bar">
              <button
                type="button"
                class="tab-btn"
                :class="{ active: activeTab === 'passes' }"
                @click="activeTab = 'passes'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span>Visitor Passes</span>
                <span class="tab-pill">{{ passes.length }}</span>
              </button>

              <button
                type="button"
                class="tab-btn"
                :class="{ active: activeTab === 'dues' }"
                @click="activeTab = 'dues'"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="1" x2="12" y2="23"/>
                  <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                <span>Estate Dues & Payments</span>
                <span class="tab-pill">{{ dues.length }}</span>
              </button>
            </div>

            <!-- TAB 1: Passes Activity -->
            <div v-if="activeTab === 'passes'" class="tab-content">
              <div v-if="passes.length === 0" class="empty-state-tab">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.6">
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                  <line x1="16" y1="2" x2="16" y2="6"/>
                  <line x1="8" y1="2" x2="8" y2="6"/>
                  <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <p class="empty-title">No Passes Created</p>
                <p class="empty-desc">This resident has not generated any gate passes yet.</p>
              </div>

              <div v-else class="passes-table-wrap">
                <table class="activity-table">
                  <thead>
                    <tr>
                      <th>Visitor</th>
                      <th>Access Code</th>
                      <th>Purpose</th>
                      <th>Status</th>
                      <th>Issued Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="pass in passes" :key="pass.id || pass.code">
                      <td>
                        <div class="visitor-cell">
                          <span class="visitor-name">{{ pass.visitorName || pass.visitor_name }}</span>
                          <span v-if="pass.visitorPhone || pass.visitor_phone" class="visitor-phone">
                            {{ pass.visitorPhone || pass.visitor_phone }}
                          </span>
                        </div>
                      </td>
                      <td>
                        <div class="code-badge-wrap">
                          <code class="code-badge">{{ pass.code || pass.ulid?.slice(-6) }}</code>
                          <button
                            type="button"
                            class="btn-icon-mini"
                            title="Copy code"
                            @click="copyText(pass.code, 'Pass code copied!')"
                          >
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                          </button>
                        </div>
                      </td>
                      <td>
                        <span class="purpose-cell">{{ pass.purpose || 'General Visit' }}</span>
                      </td>
                      <td>
                        <span class="badge" :class="passStatusBadgeClass(pass.status)">
                          {{ pass.status || 'Pending' }}
                        </span>
                      </td>
                      <td class="date-cell">
                        {{ fmtDateTime(pass.createdAt || pass.created_at) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- TAB 2: Dues & Payments -->
            <div v-if="activeTab === 'dues'" class="tab-content">
              <div v-if="dues.length === 0" class="empty-state-tab">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.6">
                  <line x1="12" y1="1" x2="12" y2="23"/>
                  <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                <p class="empty-title">No Dues Recorded</p>
                <p class="empty-desc">No dues or fee assessments are currently assigned to this resident.</p>
              </div>

              <div v-else class="passes-table-wrap">
                <table class="activity-table">
                  <thead>
                    <tr>
                      <th>Dues / Fee Name</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Paid Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="due in dues" :key="due.id">
                      <td>
                        <strong>{{ due.fee_name || due.feeName || 'Estate Levy' }}</strong>
                      </td>
                      <td>
                        <span class="amount-val">₦{{ formatAmount(due.amount) }}</span>
                      </td>
                      <td>
                        <span class="badge" :class="due.status === 'paid' ? 'badge-green' : 'badge-yellow'">
                          {{ due.status === 'paid' ? 'Paid' : 'Pending' }}
                        </span>
                      </td>
                      <td class="date-cell">
                        {{ due.paid_at ? fmtDate(due.paid_at) : '—' }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- FULL EDIT RESIDENT MODAL / DRAWER -->
    <div v-if="editModalOpen" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal-card">
        <header class="modal-header">
          <div>
            <div class="modal-eyebrow">UPDATE RESIDENT RECORD</div>
            <h3 class="modal-title">Edit Resident Profile</h3>
          </div>
          <button type="button" class="btn-close" @click="closeEditModal">✕</button>
        </header>

        <form class="modal-body" @submit.prevent="saveEdit">
          <!-- Profile Photo with Crop -->
          <div class="photo-section">
            <div class="photo-preview" :style="editForm.avatar ? { backgroundImage: `url(${editForm.avatar})` } : {}">
              <span v-if="!editForm.avatar" class="photo-preview-monogram">{{ initials(editForm.name) }}</span>
            </div>
            <div class="photo-meta">
              <input
                ref="photoInput"
                type="file"
                accept="image/*"
                class="sr-only"
                @change="onPhotoPicked"
              />
              <div class="photo-btn-row">
                <button type="button" class="btn btn-sm btn-outline" @click="triggerPhotoPicker">
                  {{ editForm.avatar ? 'Change Photo' : 'Upload Photo' }}
                </button>
                <button v-if="editForm.avatar" type="button" class="btn btn-sm btn-ghost-danger" @click="removePhoto">
                  Remove
                </button>
              </div>
              <span class="photo-hint">Crop available upon file selection (JPG, PNG)</span>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Full Name <span class="req">*</span></label>
              <input
                v-model="editForm.name"
                type="text"
                class="form-input"
                required
                placeholder="e.g. Babatunde Fashola"
              />
            </div>

            <div class="form-group">
              <label class="form-label">Phone Number <span class="req">*</span></label>
              <input
                v-model="editForm.phone"
                type="tel"
                class="form-input"
                required
                placeholder="e.g. 08012345678"
              />
            </div>

            <div class="form-group full-width">
              <label class="form-label">Email Address</label>
              <input
                v-model="editForm.email"
                type="email"
                class="form-input"
                placeholder="e.g. resident@example.com"
              />
            </div>

            <div class="form-group">
              <label class="form-label">Occupancy Role</label>
              <select v-model="editForm.role" class="form-select">
                <option value="tenant">Tenant</option>
                <option value="owner">Homeowner</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Assigned Property Unit</label>
              <select v-model="editForm.unit_id" class="form-select">
                <option :value="null">Unassigned</option>
                <option v-for="unit in availableUnits" :key="unit.id" :value="unit.id">
                  {{ unit.flatAddress || unit.flat_address || ('Unit #' + unit.id) }}
                </option>
              </select>
            </div>

            <div class="form-group full-width">
              <label class="form-label">Account Gate Access Status</label>
              <div class="status-choice-group">
                <label
                  class="status-radio-card"
                  :class="{ active: editForm.is_active }"
                >
                  <input
                    v-model="editForm.is_active"
                    type="radio"
                    :value="true"
                    class="sr-only"
                  />
                  <div class="radio-indicator active-indicator"></div>
                  <div>
                    <strong>Active Account</strong>
                    <p>Resident can issue passes, enter gate, and access services.</p>
                  </div>
                </label>

                <label
                  class="status-radio-card"
                  :class="{ active: !editForm.is_active }"
                >
                  <input
                    v-model="editForm.is_active"
                    type="radio"
                    :value="false"
                    class="sr-only"
                  />
                  <div class="radio-indicator disabled-indicator"></div>
                  <div>
                    <strong>Disabled Account</strong>
                    <p>Suspends gate pass issuing and triggers security gate flag.</p>
                  </div>
                </label>
              </div>
            </div>
          </div>

          <p v-if="editError" class="modal-error">{{ editError }}</p>

          <footer class="modal-footer">
            <button type="button" class="btn btn-outline" @click="closeEditModal">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="savingEdit">
              <BrandSpinner v-if="savingEdit" :size="16" color="#ffffff" />
              <span>{{ savingEdit ? 'Saving...' : 'Save Changes' }}</span>
            </button>
          </footer>
        </form>
      </div>
    </div>

    <!-- Image Cropper Modal -->
    <ImageCropperModal
      v-model="cropperVisible"
      :image-src="cropperSrc"
      title="Crop Resident Profile Photo"
      @crop="handleCroppedImage"
      @cancel="cancelCrop"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { residentsApi, estatesApi } from '@/api/index'
import { useToast } from '@/composables/useToast'
import BrandSpinner from '@/components/BrandSpinner.vue'
import ImageCropperModal from '@/components/ImageCropperModal.vue'

const route = useRoute()
const router = useRouter()
const { showToast } = useToast()

const loading = ref(true)
const error = ref('')
const resident = ref<any>(null)
const coResidents = ref<any[]>([])
const passes = ref<any[]>([])
const dues = ref<any[]>([])
const navIds = ref<number[]>([])

const activeTab = ref<'passes' | 'dues'>('passes')
const togglingStatus = ref(false)

// Edit Modal state
const editModalOpen = ref(false)
const savingEdit = ref(false)
const editError = ref('')
const availableUnits = ref<any[]>([])
const photoInput = ref<HTMLInputElement | null>(null)

const editForm = ref({
  name: '',
  phone: '',
  email: '',
  avatar: '',
  role: 'tenant',
  unit_id: null as number | null,
  is_active: true,
})

// Cropper state
const cropperVisible = ref(false)
const cropperSrc = ref('')

// Sibling Navigation Computed
const currentIndex = computed(() => {
  if (!resident.value || !navIds.value.length) return -1
  return navIds.value.indexOf(Number(resident.value.id))
})

const hasPrev = computed(() => currentIndex.value > 0)
const hasNext = computed(() => currentIndex.value >= 0 && currentIndex.value < navIds.value.length - 1)

function navigatePrev() {
  if (hasPrev.value) {
    const prevId = navIds.value[currentIndex.value - 1]
    router.push(`/residents/${prevId}`)
  }
}

function navigateNext() {
  if (hasNext.value) {
    const nextId = navIds.value[currentIndex.value + 1]
    router.push(`/residents/${nextId}`)
  }
}

// Fetch Resident Profile & Association Data
async function loadResident(id: number) {
  loading.value = true
  error.value = ''
  try {
    const res = await residentsApi.getOne(id)
    resident.value = res.data.resident
    coResidents.value = res.data.coResidents || []
    passes.value = res.data.passes || []
    dues.value = res.data.dues || []
    navIds.value = res.data.navIds || []

    // Populate edit form
    editForm.value = {
      name: resident.value.user?.name || '',
      phone: resident.value.user?.phone || '',
      email: resident.value.user?.email || '',
      avatar: resident.value.user?.avatar || '',
      role: resident.value.role || 'tenant',
      unit_id: resident.value.unit_id || null,
      is_active: !!resident.value.isActive,
    }

    // Load available estate units
    if (resident.value.estate_id) {
      loadEstateUnits(resident.value.estate_id)
    }
  } catch (err: any) {
    console.error('Failed to load resident:', err)
    error.value = err.response?.data?.message || 'Resident not found or access denied.'
  } finally {
    loading.value = false
  }
}

async function loadEstateUnits(estateId: number) {
  try {
    const res = await estatesApi.getUnits(estateId)
    availableUnits.value = res.data.units || []
  } catch (e) {
    console.error('Failed loading units for edit:', e)
  }
}

// Toggle Account Status CTA
async function toggleAccountStatus() {
  if (!resident.value || togglingStatus.value) return
  togglingStatus.value = true
  const nextState = !resident.value.isActive

  try {
    await residentsApi.update(resident.value.id, {
      is_active: nextState,
    })
    resident.value.isActive = nextState
    editForm.value.is_active = nextState
    showToast(
      nextState ? 'Resident account activated successfully.' : 'Resident account disabled.',
      nextState ? 'success' : 'info'
    )
  } catch (err: any) {
    console.error('Failed toggling status:', err)
    showToast(err.response?.data?.message || 'Failed to update account status.', 'error')
  } finally {
    togglingStatus.value = false
  }
}

// Full Edit Modal Handlers
function openEditModal() {
  if (!resident.value) return
  editForm.value = {
    name: resident.value.user?.name || '',
    phone: resident.value.user?.phone || '',
    email: resident.value.user?.email || '',
    avatar: resident.value.user?.avatar || '',
    role: resident.value.role || 'tenant',
    unit_id: resident.value.unit_id || null,
    is_active: !!resident.value.isActive,
  }
  editError.value = ''
  editModalOpen.value = true
}

function closeEditModal() {
  editModalOpen.value = false
  editError.value = ''
}

function triggerPhotoPicker() {
  photoInput.value?.click()
}

function onPhotoPicked(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return

  const reader = new FileReader()
  reader.onload = (e) => {
    cropperSrc.value = e.target?.result as string
    cropperVisible.value = true
    if (target) target.value = ''
  }
  reader.readAsDataURL(file)
}

function handleCroppedImage(dataUrl: string) {
  editForm.value.avatar = dataUrl
  cropperVisible.value = false
}

function cancelCrop() {
  cropperVisible.value = false
  cropperSrc.value = ''
}

function removePhoto() {
  editForm.value.avatar = ''
}

async function saveEdit() {
  savingEdit.value = true
  editError.value = ''

  try {
    const payload: Record<string, unknown> = {
      name: editForm.value.name.trim(),
      phone: editForm.value.phone.trim(),
      email: editForm.value.email.trim() || null,
      avatar: editForm.value.avatar || null,
      role: editForm.value.role,
      unit_id: editForm.value.unit_id,
      is_active: editForm.value.is_active,
    }

    const res = await residentsApi.update(resident.value.id, payload)
    resident.value = res.data.resident
    showToast('Resident profile updated successfully!', 'success')
    closeEditModal()
    // Reload full details (including unit and co-residents)
    loadResident(resident.value.id)
  } catch (err: any) {
    console.error('Failed saving resident edit:', err)
    editError.value = err.response?.data?.message || 'Could not update resident information.'
  } finally {
    savingEdit.value = false
  }
}

// Copy to Clipboard Utility
async function copyText(text: string, msg = 'Copied to clipboard!') {
  if (!text) return
  try {
    await navigator.clipboard.writeText(text)
    showToast(msg, 'success')
  } catch {
    showToast('Failed to copy', 'error')
  }
}

// Formatters
function initials(name?: string) {
  if (!name) return '?'
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

function fmtDate(iso?: string) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function fmtDateTime(iso?: string) {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-GB', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

function formatAmount(val: any) {
  if (!val) return '0.00'
  return Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function passStatusBadgeClass(status?: string) {
  switch ((status || '').toLowerCase()) {
    case 'pending': return 'badge-yellow'
    case 'on-site': return 'badge-green'
    case 'used': return 'badge-gray'
    case 'revoked': return 'badge-red'
    default: return 'badge-gray'
  }
}

// Watch Route ID changes (e.g. Prev/Next navigation)
watch(() => route.params.id, (newId) => {
  if (newId) {
    loadResident(Number(newId))
  }
})

onMounted(() => {
  const id = Number(route.params.id)
  if (id) {
    loadResident(id)
  }
})
</script>

<style scoped>
.resident-detail-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-width: 1200px;
  margin: 0 auto;
  padding-bottom: 60px;
}

/* Top Navigation Bar */
.top-nav-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 0;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  text-decoration: none;
  padding: 8px 14px;
  border-radius: 10px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  transition: all 160ms cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-back:hover {
  background: #F9FAFB;
  border-color: #D1D5DB;
  color: #111827;
  transform: translateX(-2px);
}

/* Sibling Navigator */
.sibling-nav {
  display: flex;
  align-items: center;
  gap: 4px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  padding: 4px 6px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

.btn-sibling {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: transparent;
  border: none;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  padding: 6px 10px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 140ms ease;
}

.btn-sibling:hover:not(:disabled) {
  background: #F3F4F6;
  color: #111827;
}

.btn-sibling:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.sibling-counter {
  font-size: 12px;
  font-weight: 600;
  color: #6B7280;
  padding: 0 8px;
  letter-spacing: 0.5px;
}

.counter-divider {
  color: #D1D5DB;
}

/* Loading & Error */
.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 280px;
  gap: 12px;
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #E5E7EB;
  padding: 40px;
}

.loading-label {
  font-size: 14px;
  color: #6B7280;
}

/* Hero Profile Card */
.hero-profile-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 24px 28px;
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

.profile-main {
  display: flex;
  align-items: center;
  gap: 20px;
}

.avatar-wrap {
  position: relative;
  width: 76px;
  height: 76px;
  flex-shrink: 0;
}

.avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  border: 2px solid #ffffff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.avatar-monogram {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: linear-gradient(135deg, #0A5C38, #053B23);
  color: #ffffff;
  font-size: 24px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 8px rgba(10, 92, 56, 0.2);
}

.status-pip {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2.5px solid #ffffff;
}

.pip-active {
  background: #10B981;
}

.pip-inactive {
  background: #9CA3AF;
}

.profile-info {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.profile-title-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.profile-name {
  font-size: 22px;
  font-weight: 700;
  color: #111827;
  letter-spacing: -0.3px;
  margin: 0;
}

.pill-group {
  display: flex;
  align-items: center;
  gap: 6px;
}

.role-pill {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 3px 8px;
  border-radius: 6px;
}

.role-owner {
  background: #EFF6FF;
  color: #1D4ED8;
  border: 1px solid #DBEAFE;
}

.role-tenant {
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
  padding: 3px 8px;
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

.unit-banner {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13.5px;
  color: #374151;
  font-weight: 500;
}

.unit-address {
  font-weight: 600;
  color: #111827;
}

.estate-bullet {
  color: #6B7280;
  font-size: 12.5px;
}

.joined-meta {
  font-size: 12px;
  color: #6B7280;
  margin: 0;
}

/* Actions in Header */
.profile-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.btn-edit {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  font-size: 13.5px;
  font-weight: 600;
  border-radius: 10px;
}

.btn-disable {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  font-size: 13.5px;
  font-weight: 600;
  background: #FEF2F2;
  color: #DC2626;
  border: 1px solid #FCA5A5;
  border-radius: 10px;
  cursor: pointer;
  transition: all 160ms ease;
}

.btn-disable:hover:not(:disabled) {
  background: #FEE2E2;
}

.btn-enable {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  font-size: 13.5px;
  font-weight: 600;
  background: #0A5C38;
  color: #ffffff;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: all 160ms ease;
}

.btn-enable:hover:not(:disabled) {
  background: #07472B;
}

/* 2-Column Grid Layout */
.profile-grid {
  display: grid;
  grid-template-columns: 340px 1fr;
  gap: 20px;
  align-items: start;
}

/* Sidebar Cards */
.grid-sidebar {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.detail-card {
  padding: 20px 22px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
}

.card-heading {
  font-size: 14.5px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 14px;
}

.card-heading-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.count-badge {
  font-size: 11.5px;
  font-weight: 600;
  background: #F3F4F6;
  color: #4B5563;
  padding: 2px 8px;
  border-radius: 12px;
}

.contact-rows {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.contact-item {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.contact-label {
  font-size: 11.5px;
  font-weight: 600;
  color: #6B7280;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.contact-val-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.contact-val {
  font-size: 14px;
  font-weight: 600;
  color: #111827;
}

.font-mono {
  font-family: monospace;
}

.btn-icon-copy {
  background: transparent;
  border: none;
  color: #9CA3AF;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  transition: all 140ms ease;
}

.btn-icon-copy:hover {
  color: #0A5C38;
  background: #F0FDF4;
}

/* Co-Residents */
.empty-household {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 18px 8px;
  gap: 8px;
  color: #6B7280;
  font-size: 13px;
}

.coresidents-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.coresident-chip {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: 10px;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  text-decoration: none;
  transition: all 160ms ease;
}

.coresident-chip:hover {
  background: #F3F4F6;
  border-color: #D1D5DB;
  transform: translateX(2px);
}

.chip-avatar {
  width: 32px;
  height: 32px;
  flex-shrink: 0;
}

.chip-avatar-img {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
}

.chip-avatar-monogram {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: #E5E7EB;
  color: #374151;
  font-size: 11px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chip-info {
  flex: 1;
  min-width: 0;
}

.chip-name {
  font-size: 13px;
  font-weight: 600;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.chip-role {
  font-size: 11px;
  color: #6B7280;
}

.chip-arrow {
  color: #9CA3AF;
  flex-shrink: 0;
}

/* Main Activities Tabs */
.tabs-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  overflow: hidden;
}

.tab-bar {
  display: flex;
  border-bottom: 1px solid #E5E7EB;
  background: #F9FAFB;
  padding: 4px 6px 0;
}

.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 18px;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  font-size: 13.5px;
  font-weight: 600;
  color: #6B7280;
  cursor: pointer;
  transition: all 140ms ease;
}

.tab-btn:hover {
  color: #111827;
}

.tab-btn.active {
  color: #0A5C38;
  border-bottom-color: #0A5C38;
  background: #ffffff;
  border-top-left-radius: 8px;
  border-top-right-radius: 8px;
}

.tab-pill {
  font-size: 11px;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 10px;
  background: #E5E7EB;
  color: #374151;
}

.tab-btn.active .tab-pill {
  background: #ECFDF5;
  color: #065F46;
}

.tab-content {
  padding: 20px;
}

.empty-state-tab {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 16px;
  text-align: center;
}

.empty-title {
  font-size: 14.5px;
  font-weight: 600;
  color: #111827;
  margin: 10px 0 4px;
}

.empty-desc {
  font-size: 13px;
  color: #6B7280;
  max-width: 300px;
  margin: 0;
}

/* Activities Table */
.passes-table-wrap {
  overflow-x: auto;
}

.activity-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.activity-table th {
  text-align: left;
  padding: 10px 14px;
  font-size: 11.5px;
  font-weight: 600;
  color: #6B7280;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  border-bottom: 1px solid #E5E7EB;
  background: #F9FAFB;
}

.activity-table td {
  padding: 12px 14px;
  border-bottom: 1px solid #F3F4F6;
  vertical-align: middle;
}

.activity-table tr:last-child td {
  border-bottom: none;
}

.visitor-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.visitor-name {
  font-weight: 600;
  color: #111827;
}

.visitor-phone {
  font-size: 11.5px;
  color: #6B7280;
}

.code-badge-wrap {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.code-badge {
  font-family: monospace;
  font-size: 12.5px;
  font-weight: 700;
  background: #F3F4F6;
  color: #0A5C38;
  padding: 2px 7px;
  border-radius: 6px;
  border: 1px solid #E5E7EB;
}

.btn-icon-mini {
  background: transparent;
  border: none;
  color: #9CA3AF;
  cursor: pointer;
  padding: 2px;
  display: flex;
  align-items: center;
}

.btn-icon-mini:hover {
  color: #0A5C38;
}

.purpose-cell {
  color: #4B5563;
}

.amount-val {
  font-weight: 700;
  color: #111827;
}

.date-cell {
  color: #6B7280;
  font-size: 12px;
  white-space: nowrap;
}

/* Edit Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
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
  max-width: 580px;
  box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 20px 24px;
  border-bottom: 1px solid #E5E7EB;
}

.modal-eyebrow {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.6px;
  color: #0A5C38;
}

.modal-title {
  font-size: 18px;
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
  padding: 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

/* Photo Section in Modal */
.photo-section {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 14px;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
}

.photo-preview {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background-size: cover;
  background-position: center;
  background-color: #E5E7EB;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.photo-preview-monogram {
  font-size: 18px;
  font-weight: 700;
  color: #4B5563;
}

.photo-meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.photo-btn-row {
  display: flex;
  gap: 8px;
}

.btn-ghost-danger {
  background: transparent;
  border: none;
  color: #DC2626;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
}

.photo-hint {
  font-size: 11px;
  color: #6B7280;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.full-width {
  grid-column: 1 / -1;
}

.form-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #374151;
}

.req {
  color: #DC2626;
}

.form-input, .form-select {
  padding: 9px 12px;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13.5px;
  color: #111827;
  background: #ffffff;
}

.form-input:focus, .form-select:focus {
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

@media (max-width: 860px) {
  .hero-profile-card {
    flex-direction: column;
    align-items: flex-start;
  }
  .profile-actions {
    width: 100%;
    justify-content: flex-start;
  }
  .profile-grid {
    grid-template-columns: 1fr;
  }
  .status-choice-group {
    grid-template-columns: 1fr;
  }
}
</style>
