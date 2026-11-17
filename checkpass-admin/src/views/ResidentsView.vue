<template>
  <div class="mobile-page">
    <section class="hero-card hero-card--sand">
      <div class="hero-title-group">
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
          class="form-select toolbar-select"
          @change="load"
        >
          <option value="">All Estates</option>
          <option v-for="estate in estates" :key="estate.id" :value="estate.id">
            🏡 {{ estate.name }}
          </option>
        </select>

        <select v-model="roleFilter" class="form-select toolbar-select">
          <option value="all">All Roles</option>
          <option value="owner">Owners Only</option>
          <option value="tenant">Tenants Only</option>
        </select>

        <select v-model="statusFilter" class="form-select toolbar-select">
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
      <div v-else class="stack-list">
        <article v-for="resident in filteredResidents" :key="resident.id" class="record-card card">
          <div class="record-top">
            <div>
              <h3 class="record-title">{{ resident.user?.name ?? '—' }}</h3>
              <p class="record-meta">{{ resident.user?.phone ?? '—' }}</p>
            </div>
            <div class="record-badges">
              <span :class="resident.role === 'owner' ? 'badge badge-blue' : 'badge badge-gray'">{{ resident.role ?? 'tenant' }}</span>
              <span :class="resident.isActive ? 'badge badge-green' : 'badge badge-gray'">{{ resident.isActive ? 'Active' : 'Inactive' }}</span>
            </div>
          </div>
          <div class="detail-grid">
            <div>
              <span class="detail-label">Estate</span>
              <strong>{{ resident.estate?.name ?? '—' }}</strong>
            </div>
            <div>
              <span class="detail-label">Unit</span>
              <strong>{{ resident.unit?.flatAddress ?? '—' }}</strong>
            </div>
          </div>
          <div class="record-bottom">
            <span class="record-sub">Joined {{ fmtDate(resident.createdAt) }}</span>
            <div class="row-actions">
              <button class="btn btn-sm btn-outline" @click="openEdit(resident)">Edit</button>
              <button class="btn btn-sm btn-danger" @click="confirmDelete(resident)">Delete</button>
            </div>
          </div>
        </article>
      </div>
    </section>

    <div v-if="modalOpen" class="sheet-overlay" @click.self="closeModal">
      <!-- Desktop & Mobile Adaptive Modal for Create Resident -->
      <div v-if="!editing" class="desktop-modal-card" @click.stop>
        <!-- Modal Top Bar -->
        <div class="modal-header-bar">
          <div class="modal-header-left">
            <button type="button" class="btn-icon-back" @click="closeModal" title="Back">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
              </svg>
            </button>
            <div>
              <div class="modal-eyebrow">RESIDENT REGISTRATION & ONBOARDING</div>
              <h2 class="modal-main-title">Add Resident & Unit Assignment</h2>
            </div>
          </div>
          <button type="button" class="btn-icon-close" @click="closeModal" title="Close modal">✕</button>
        </div>

        <!-- Scrollable Modal Body: 2-Column Responsive Web Grid -->
        <div class="modal-scroll-body">
          <div class="two-column-form-grid">
            
            <!-- LEFT COLUMN: Primary Resident & Property Unit -->
            <div class="form-column">
              <div class="column-header">
                <span class="column-icon-badge">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                </span>
                <div>
                  <h3 class="column-title">Primary Resident (Homeowner)</h3>
                  <p class="column-sub">Account owner responsible for unit access and passes.</p>
                </div>
              </div>

              <!-- Owner Photo Upload Space -->
              <div class="photo-upload-container">
                <div class="photo-preview-box" @click="triggerOwnerPhotoUpload" title="Click to upload photo">
                  <img v-if="form.avatar" :src="form.avatar" alt="Resident Preview" class="photo-img" />
                  <div v-else class="photo-placeholder">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                      <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span class="camera-badge">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                      </svg>
                    </span>
                  </div>
                </div>

                <div class="photo-meta">
                  <input
                    ref="ownerPhotoInput"
                    type="file"
                    accept="image/*"
                    class="d-none"
                    @change="onOwnerPhotoChange"
                  />
                  <div class="photo-actions">
                    <button type="button" class="btn btn-sm btn-outline-photo" @click="triggerOwnerPhotoUpload">
                      {{ form.avatar ? 'Change Photo' : 'Upload Photo' }}
                    </button>
                    <button v-if="form.avatar" type="button" class="btn btn-sm btn-ghost-danger" @click="removeOwnerPhoto">
                      Remove
                    </button>
                  </div>
                  <span class="photo-hint">Resident profile photo (JPG, PNG • Max 5MB)</span>
                </div>
              </div>

              <!-- Personal Information Fields -->
              <div class="form-group">
                <label class="form-label">Owner's Full Name *</label>
                <input v-model="form.name" class="form-input" placeholder="e.g. Babatunde Fashola" required />
              </div>

              <div class="form-row-2col">
                <div class="form-group">
                  <label class="form-label">Phone Number *</label>
                  <input v-model="form.phone" class="form-input" type="tel" placeholder="e.g. 08012345678" required />
                </div>
                <div class="form-group">
                  <label class="form-label">Email Address</label>
                  <input v-model="form.email" class="form-input" type="email" placeholder="e.g. resident@example.com" />
                </div>
              </div>

              <!-- Property Unit Assignment Box -->
              <div class="property-picker-section">
                <div class="label-row">
                  <label class="form-label">Property Unit Assignment *</label>
                  <button
                    type="button"
                    class="unit-add-link"
                    @click="manualAddressMode = !manualAddressMode"
                  >
                    {{ manualAddressMode ? '← Pick from Configured Units' : '+ Custom / Manual Address' }}
                  </button>
                </div>

                <div v-if="!manualAddressMode" class="form-group mb-0">
                  <select
                    :value="form.unit_id"
                    class="form-select form-select-lg"
                    @change="onSelectProperty(Number(($event.target as HTMLSelectElement).value) || '')"
                  >
                    <option value="">Select a configured property...</option>
                    <option
                      v-for="prop in estateProperties"
                      :key="prop.id"
                      :value="prop.id"
                    >
                      {{ prop.flatAddress }} — {{ prop.isOccupied ? 'Occupied (' + prop.residentsCount + ')' : 'Vacant' }} ({{ prop.unitType || 'Apartment' }})
                    </option>
                  </select>
                  <p v-if="estateProperties.length === 0" class="field-hint">
                    No configured properties found for this estate. Use manual entry or generate units in the Properties module.
                  </p>
                </div>

                <div v-else class="address-row">
                  <div class="form-group mb-0">
                    <label class="form-label">Tier 1 (e.g. Lane / Block)</label>
                    <input v-model="form.lane" class="form-input" placeholder="e.g. Lane 1" />
                  </div>
                  <div class="form-group mb-0">
                    <label class="form-label">Tier 2 (e.g. House / Floor)</label>
                    <input v-model="form.house" class="form-input" placeholder="e.g. House 5" />
                  </div>
                  <div class="form-group mb-0">
                    <label class="form-label">Tier 3 (e.g. Flat / Unit)</label>
                    <input v-model="form.flat" class="form-input" placeholder="e.g. Flat B" />
                  </div>
                </div>
              </div>

              <!-- Landlord is Occupant Toggle -->
              <div class="toggle-control-card">
                <label class="toggle-switch">
                  <input type="checkbox" v-model="form.landlordIsOccupant" />
                  <span class="toggle-track"><span class="toggle-thumb"></span></span>
                </label>
                <div class="toggle-meta">
                  <span class="toggle-title">Landlord resides in this unit</span>
                  <span class="toggle-desc">Toggle on if the homeowner physically lives in this property rather than renting to tenants.</span>
                </div>
              </div>
            </div>

            <!-- RIGHT COLUMN: Co-Residents, Family & Tenants -->
            <div class="form-column">
              <div class="column-header column-header--between">
                <div class="column-header-left">
                  <span class="column-icon-badge column-icon-badge--blue">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                      <circle cx="9" cy="7" r="4"/>
                      <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                      <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                  </span>
                  <div>
                    <h3 class="column-title">Household Occupants & Tenants ({{ form.tenants.length }})</h3>
                    <p class="column-sub">Register additional family members or co-tenants at this unit.</p>
                  </div>
                </div>

                <button type="button" class="btn btn-sm btn-outline-add" @click="addTenant">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                  </svg>
                  <span>Add Occupant</span>
                </button>
              </div>

              <!-- Empty State when 0 tenants -->
              <div v-if="form.tenants.length === 0" class="occupants-empty-box">
                <div class="occupants-empty-icon">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                  </svg>
                </div>
                <h4>No Additional Occupants</h4>
                <p>If other residents or tenants share this property, click "+ Add Occupant" to register them with separate gate access passes.</p>
                <button type="button" class="btn btn-sm btn-secondary" @click="addTenant">
                  + Add First Co-Resident
                </button>
              </div>

              <!-- Occupant Cards List -->
              <div v-else class="occupants-cards-list">
                <div v-for="(tenant, i) in form.tenants" :key="i" class="occupant-card">
                  <div class="occupant-card-top">
                    <span class="occupant-badge">Occupant #{{ i + 1 }}</span>
                    <button type="button" class="tenant-del-btn" @click="removeTenant(i)" title="Remove occupant">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                      </svg>
                    </button>
                  </div>

                  <!-- Occupant Photo Mini Uploader -->
                  <div class="occupant-photo-row">
                    <div class="occupant-photo-box" @click="triggerTenantPhotoUpload(i)">
                      <img v-if="tenant.avatar" :src="tenant.avatar" class="occupant-img" />
                      <div v-else class="occupant-photo-placeholder">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" stroke-linecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                      </div>
                    </div>
                    <input
                      :id="`tenantPhoto_${i}`"
                      type="file"
                      accept="image/*"
                      class="d-none"
                      @change="onTenantPhotoChange(i, $event)"
                    />
                    <div class="occupant-photo-actions">
                      <button type="button" class="link-btn-sm" @click="triggerTenantPhotoUpload(i)">
                        {{ tenant.avatar ? 'Change Photo' : 'Upload Photo' }}
                      </button>
                      <button v-if="tenant.avatar" type="button" class="link-btn-sm text-danger" @click="removeTenantPhoto(i)">
                        Remove
                      </button>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input v-model="tenant.name" class="form-input" placeholder="e.g. Kemi Adeleke" required />
                  </div>

                  <div class="form-row-2col">
                    <div class="form-group mb-0">
                      <label class="form-label">Phone No. *</label>
                      <input v-model="tenant.phone" class="form-input" type="tel" placeholder="e.g. 08023456789" required />
                    </div>
                    <div class="form-group mb-0">
                      <label class="form-label">Email Address</label>
                      <input v-model="tenant.email" class="form-input" type="email" placeholder="e.g. kemi@example.com" />
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div v-if="formError" class="form-error-banner mt-4">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ formError }}</span>
          </div>
        </div>

        <!-- Desktop Modal Footer -->
        <div class="modal-footer-bar">
          <div class="footer-info-hint">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>Temporary login credentials will be generated automatically for all new residents.</span>
          </div>

          <div class="modal-footer-actions">
            <button type="button" class="btn btn-outline" @click="closeModal">Cancel</button>
            <button type="button" class="btn btn-primary btn-save-resident" :disabled="saving" @click="save">
              <BrandSpinner v-if="saving" :size="16" color="#ffffff" />
              <span>{{ saving ? 'Saving Records...' : 'Save Resident Record' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Sheet for edit -->
      <div v-else class="sheet card">
        <div class="sheet-head">
          <h3 class="sheet-title">Edit Resident</h3>
          <button class="sheet-close" @click="closeModal">✕</button>
        </div>
        <div class="sheet-body">
          <div class="form-group">
            <label class="form-label">Estate</label>
            <select v-model="form.estate_id" class="form-select" @change="loadUnits">
              <option value="">Select estate…</option>
              <option v-for="estate in estates" :key="estate.id" :value="estate.id">{{ estate.name }}</option>
            </select>
          </div>
          <div class="form-group">
            <div class="label-row">
              <label class="form-label">Unit</label>
              <button type="button" class="unit-add-link" :disabled="!form.estate_id" @click="showAddUnit = !showAddUnit">
                {{ showAddUnit ? 'Cancel' : '+ New unit' }}
              </button>
            </div>
            <select v-model="form.unit_id" class="form-select" :disabled="!form.estate_id">
              <option value="">Select unit…</option>
              <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.flatAddress || unit.flat_address }}</option>
            </select>
            <div v-if="showAddUnit" class="unit-add-box">
              <input v-model="newUnitAddress" class="form-input" placeholder="e.g. L3, H7, FA" />
              <button type="button" class="btn btn-sm btn-primary" :disabled="creatingUnit" @click="createUnit">
                {{ creatingUnit ? 'Adding…' : 'Add' }}
              </button>
            </div>
            <p v-if="unitError" class="form-error">{{ unitError }}</p>
          </div>
          <div class="form-group">
            <label class="form-label">Role</label>
            <div class="chip-row">
              <button class="chip" :class="{ active: form.role === 'tenant' }" @click="form.role = 'tenant'">Tenant</button>
              <button class="chip" :class="{ active: form.role === 'owner' }" @click="form.role = 'owner'">Owner</button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <div class="chip-row">
              <button class="chip" :class="{ active: form.is_active }" @click="form.is_active = true">Active</button>
              <button class="chip" :class="{ active: !form.is_active }" @click="form.is_active = false">Inactive</button>
            </div>
          </div>
          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="closeModal">Cancel</button>
          <button class="btn btn-primary" :disabled="saving" @click="save">{{ saving ? 'Saving…' : 'Save Changes' }}</button>
        </div>
      </div>
    </div>

    <div v-if="deleteTarget" class="sheet-overlay" @click.self="deleteTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Delete resident</div>
            <h3 class="sheet-title">Remove {{ deleteTarget.user?.name }}?</h3>
          </div>
          <button class="sheet-close" @click="deleteTarget = null">✕</button>
        </div>
        <p class="confirm-copy">This resident record and assignment will be removed permanently.</p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doDelete">{{ saving ? 'Deleting…' : 'Delete' }}</button>
        </div>
      </div>
    </div>

    <div v-if="newCredentials" class="sheet-overlay" @click.self="newCredentials = null">
      <div class="sheet card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Save these credentials</div>
            <h3 class="sheet-title">Resident login details</h3>
          </div>
          <button class="sheet-close" @click="newCredentials = null">✕</button>
        </div>
        <p class="confirm-copy">Share these temporary passwords with each resident — they won't be shown again.</p>
        <div class="credentials-list">
          <div v-for="(c, i) in newCredentials" :key="i" class="credential-row">
            <div>
              <strong>{{ c.name }}</strong>
              <span class="record-sub">{{ c.phone }}</span>
            </div>
            <code class="credential-password">{{ c.password }}</code>
          </div>
        </div>
        <div class="sheet-actions">
          <button class="btn btn-primary" @click="newCredentials = null">Done</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { residentsApi, estatesApi, unitsApi, propertiesApi } from '@/api/index'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const { showToast } = useToast()
const auth = useAuthStore()

const estateProperties = ref<any[]>([])
const manualAddressMode = ref(false)

function onSelectProperty(propId: number | '') {
  form.value.unit_id = propId
  if (!propId) {
    form.value.lane = ''
    form.value.house = ''
    form.value.flat = ''
    return
  }
  const found = estateProperties.value.find((p: any) => p.id === propId)
  if (found) {
    form.value.lane = found.lane || ''
    form.value.house = found.house || ''
    form.value.flat = found.flat || ''
  }
}

const loading = ref(true)
const residents = ref<any[]>([])
const estates = ref<any[]>([])
const units = ref<any[]>([])
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

const modalOpen = ref(false)
const editing = ref<any>(null)
const saving = ref(false)
const formError = ref('')
const deleteTarget = ref<any>(null)
const showAddUnit = ref(false)
const newUnitAddress = ref('')
const creatingUnit = ref(false)
const unitError = ref('')
const newCredentials = ref<{ name: string; phone: string; password: string }[] | null>(null)

const form = ref({
  name: '', phone: '', email: '', avatar: '',
  lane: '', house: '', flat: '',
  landlordIsOccupant: false,
  tenants: [] as { name: string; phone: string; email: string; avatar?: string }[],
  // edit-only fields
  estate_id: '' as number | '',
  unit_id: '' as number | '',
  role: 'tenant',
  is_active: true,
})

const ownerPhotoInput = ref<HTMLInputElement | null>(null)

function triggerOwnerPhotoUpload() {
  ownerPhotoInput.value?.click()
}

function onOwnerPhotoChange(e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) {
    showToast('Photo must be less than 5MB', 'error')
    return
  }
  const reader = new FileReader()
  reader.onload = () => {
    form.value.avatar = reader.result as string
  }
  reader.readAsDataURL(file)
}

function removeOwnerPhoto() {
  form.value.avatar = ''
  if (ownerPhotoInput.value) {
    ownerPhotoInput.value.value = ''
  }
}

function triggerTenantPhotoUpload(index: number) {
  const el = document.getElementById(`tenantPhoto_${index}`) as HTMLInputElement
  el?.click()
}

function onTenantPhotoChange(index: number, e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) {
    showToast('Photo must be less than 5MB', 'error')
    return
  }
  const reader = new FileReader()
  reader.onload = () => {
    form.value.tenants[index].avatar = reader.result as string
  }
  reader.readAsDataURL(file)
}

function removeTenantPhoto(index: number) {
  form.value.tenants[index].avatar = ''
}

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

async function loadUnits() {
  units.value = []
  form.value.unit_id = ''
  if (!form.value.estate_id) return
  try {
    const res = await estatesApi.getUnits(form.value.estate_id as number)
    units.value = res.data.units
  } catch {/* ignore */}
}

async function openCreate() {
  editing.value = null
  const currentEstateId = Number(estateFilter.value || auth.activeEstateId) || ''
  form.value = {
    name: '', phone: '', email: '', avatar: '',
    lane: '', house: '', flat: '', landlordIsOccupant: false, tenants: [],
    estate_id: currentEstateId, unit_id: '', role: 'tenant', is_active: true,
  }
  formError.value = ''
  units.value = []
  estateProperties.value = []
  manualAddressMode.value = false

  if (currentEstateId) {
    try {
      const res = await propertiesApi.getAll(Number(currentEstateId))
      estateProperties.value = res.data?.properties || []
    } catch {
      estateProperties.value = []
    }
  }
  modalOpen.value = true
}

function openEdit(r: any) {
  editing.value = r
  form.value = {
    name: r.user?.name ?? '',
    phone: r.user?.phone ?? '',
    email: r.user?.email ?? '',
    avatar: r.user?.avatar ?? '',
    lane: '', house: '', flat: '', landlordIsOccupant: false, tenants: [],
    estate_id: r.estate?.id ?? '',
    unit_id: r.unit?.id ?? '',
    role: r.role ?? 'tenant',
    is_active: r.isActive,
  }
  formError.value = ''
  if (r.estate?.id) loadUnitsById(r.estate.id)
  modalOpen.value = true
}

async function loadUnitsById(estateId: number) {
  try {
    const res = await estatesApi.getUnits(estateId)
    units.value = res.data.units
  } catch {/* ignore */}
}

function closeModal() { modalOpen.value = false; editing.value = null }

async function createUnit() {
  unitError.value = ''
  if (!form.value.estate_id || !newUnitAddress.value.trim()) {
    unitError.value = 'Enter a unit/flat address.'
    return
  }
  creatingUnit.value = true
  try {
    const res = await unitsApi.create({
      estate_id: form.value.estate_id,
      flat_address: newUnitAddress.value.trim(),
    })
    await loadUnits()
    form.value.unit_id = res.data.unit.id
    newUnitAddress.value = ''
    showAddUnit.value = false
    showToast('Unit added.', 'success')
  } catch (err: any) {
    unitError.value = err?.response?.data?.message ?? 'Could not add unit.'
  } finally {
    creatingUnit.value = false
  }
}

async function save() {
  formError.value = ''
  saving.value = true
  try {
    if (editing.value) {
      const payload: Record<string, unknown> = {
        role: form.value.role,
        is_active: form.value.is_active,
      }
      if (form.value.estate_id) payload.estate_id = form.value.estate_id
      if (form.value.unit_id)   payload.unit_id   = form.value.unit_id
      await residentsApi.update(editing.value.id, payload)
      showToast('Resident updated.', 'success')
    } else {
      if (!form.value.name.trim())  { formError.value = 'Name is required.'; saving.value = false; return }
      if (!form.value.phone.trim()) { formError.value = 'Phone is required.'; saving.value = false; return }

      const ownerPayload: Record<string, unknown> = {
        name: form.value.name,
        phone: form.value.phone,
        role: 'owner',
        lane: form.value.lane,
        house: form.value.house,
        flat: form.value.flat,
        landlord_is_occupant: form.value.landlordIsOccupant,
      }
      if (form.value.email) ownerPayload.email = form.value.email
      if (form.value.avatar) ownerPayload.avatar = form.value.avatar
      if (form.value.unit_id) ownerPayload.unit_id = form.value.unit_id
      if (form.value.estate_id) ownerPayload.estate_id = form.value.estate_id
      const ownerRes = await residentsApi.create(ownerPayload)
      const credentials: { name: string; phone: string; password: string }[] = []
      if (ownerRes.data.temporaryPassword) {
        credentials.push({ name: form.value.name, phone: form.value.phone, password: ownerRes.data.temporaryPassword })
      }

      for (const t of form.value.tenants) {
        if (!t.name.trim() || !t.phone.trim()) continue
        const tp: Record<string, unknown> = {
          name: t.name, phone: t.phone, role: 'tenant',
          lane: form.value.lane, house: form.value.house, flat: form.value.flat,
        }
        if (t.email) tp.email = t.email
        if (t.avatar) tp.avatar = t.avatar
        if (form.value.unit_id) tp.unit_id = form.value.unit_id
        if (form.value.estate_id) tp.estate_id = form.value.estate_id
        const tenantRes = await residentsApi.create(tp)
        if (tenantRes.data.temporaryPassword) {
          credentials.push({ name: t.name, phone: t.phone, password: tenantRes.data.temporaryPassword })
        }
      }
      showToast('Resident created.', 'success')
      if (credentials.length > 0) {
        newCredentials.value = credentials
      }
    }
    closeModal()
    load()
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? 'Something went wrong.'
  } finally {
    saving.value = false
  }
}

function addTenant() { form.value.tenants.push({ name: '', phone: '', email: '', avatar: '' }) }
function removeTenant(i: number) { form.value.tenants.splice(i, 1) }

function confirmDelete(r: any) { deleteTarget.value = r }

async function doDelete() {
  if (!deleteTarget.value) return
  saving.value = true
  try {
    await residentsApi.delete(deleteTarget.value.id)
    showToast('Resident deleted.', 'success')
    deleteTarget.value = null
    load()
  } catch {
    showToast('Failed to delete resident.', 'error')
  } finally {
    saving.value = false
  }
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
  flex-wrap: wrap;
}

.toolbar-select {
  padding: 8px 12px;
  border-radius: 10px;
  border: 1px solid #E5E7EB;
  font-size: 13px;
  background: #ffffff;
  color: #374151;
  font-weight: 500;
  cursor: pointer;
  outline: none;
  transition: border-color 150ms ease;
}

.toolbar-select:focus {
  border-color: #0A5C38;
}

.section-card { padding: 16px; }
.section-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.section-title { font-size: 18px; font-weight: 700; }
.section-copy { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.loading-state, .empty-state { min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--c-muted); }
.stack-list { display: flex; flex-direction: column; gap: 10px; }
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
