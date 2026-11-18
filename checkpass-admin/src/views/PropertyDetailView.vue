<template>
  <div class="property-detail-view">
    <!-- Top Navigation / Breadcrumb -->
    <div class="page-top-nav">
      <router-link to="/properties" class="back-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"></line>
          <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        <span>Back to Properties</span>
      </router-link>
      <div class="breadcrumbs">
        <router-link to="/properties" class="bc-link">Properties</router-link>
        <span class="bc-sep">/</span>
        <span class="bc-current">{{ property?.flatAddress || 'Property Details' }}</span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="detail-loading-box">
      <BrandSpinner :size="36" color="#047857" />
      <span class="loading-label">Loading property details...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error && !property" class="detail-error-card">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="1.8">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <h3>Property Not Found</h3>
      <p>{{ error }}</p>
      <router-link to="/properties" class="btn btn-primary mt-3">Return to Properties</router-link>
    </div>

    <!-- Main Content -->
    <template v-else-if="property">
      <!-- Page Header -->
      <div class="property-detail-header card">
        <div class="header-left">
          <div class="badge-row">
            <span class="type-pill">{{ property.unitType || 'Apartment' }}</span>
            <span class="id-pill">ID: #{{ property.id }}</span>
            <span :class="property.isOccupied ? 'status-pill status-occupied' : 'status-pill status-vacant'">
              {{ property.isOccupied ? `• Occupied (${property.residentsCount || property.residents?.length || 1})` : '• Vacant' }}
            </span>
          </div>
          <h1 class="property-title">{{ property.flatAddress }}</h1>
          <p class="property-subtitle">Property and resident management</p>
        </div>

        <div class="header-actions">
          <button type="button" class="btn btn-outline" @click="openEditModal">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            <span>Edit Property</span>
          </button>
          <button type="button" class="btn btn-danger-outline" @click="showDeleteConfirm = true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
            <span>Delete</span>
          </button>
        </div>
      </div>

      <!-- Detail Grid Layout -->
      <div class="detail-grid">
        <!-- LEFT COLUMN: Hierarchy & Specs -->
        <div class="detail-sidebar">
          <!-- Structure Hierarchy Card -->
          <div class="card sidebar-card">
            <div class="card-header-clean">
              <h3 class="card-title">Structure Hierarchy</h3>
            </div>
            <div class="card-body-clean">
              <div v-if="hierarchyTags.length > 0" class="hierarchy-tags">
                <span v-for="(tag, tIdx) in hierarchyTags" :key="tIdx" class="h-tag">
                  <strong v-if="!tag.value.toLowerCase().startsWith(tag.key.toLowerCase())">{{ tag.key }}: </strong>{{ tag.value }}
                </span>
              </div>
              <p v-else class="text-muted-empty">No explicit tier hierarchy recorded.</p>
            </div>
          </div>

          <!-- Property Overview Card -->
          <div class="card sidebar-card">
            <div class="card-header-clean">
              <h3 class="card-title">Property Details</h3>
            </div>
            <div class="card-body-clean">
              <dl class="meta-list">
                <div class="meta-row">
                  <dt>Unit Type</dt>
                  <dd><span class="type-pill-subtle">{{ property.unitType || 'Apartment' }}</span></dd>
                </div>
                <div class="meta-row">
                  <dt>Occupancy</dt>
                  <dd>
                    <span :class="property.isOccupied ? 'text-emerald font-semibold' : 'text-muted'">
                      {{ property.isOccupied ? 'Occupied' : 'Vacant' }}
                    </span>
                  </dd>
                </div>
                <div class="meta-row">
                  <dt>Address</dt>
                  <dd class="font-medium">{{ property.flatAddress }}</dd>
                </div>
                <div class="meta-row">
                  <dt>Notes</dt>
                  <dd>{{ property.notes || 'No notes specified.' }}</dd>
                </div>
                <div v-if="property.createdAt" class="meta-row">
                  <dt>Registered</dt>
                  <dd>{{ formatDate(property.createdAt) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- RIGHT COLUMN: Residents & Homeowners -->
        <div class="detail-main">
          <div class="card main-card">
            <div class="card-header-clean header-flex">
              <div>
                <h2 class="card-title">
                  Residents & Homeowners
                  <span class="count-badge">({{ property.residents?.length || 0 }})</span>
                </h2>
                <p class="card-subtitle">Occupants registered to this unit</p>
              </div>
              <button
                v-if="!addingResidentMode"
                type="button"
                class="btn btn-sm btn-primary"
                @click="openAddResidentForm"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                  <line x1="12" y1="5" x2="12" y2="19"/>
                  <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Add Resident</span>
              </button>
            </div>

            <div class="card-body-clean">
              <!-- Inline Add Resident Form Box -->
              <div v-if="addingResidentMode" class="add-resident-box mb-4">
                <div class="add-resident-box-header">
                  <div>
                    <h4 class="box-title">Assign Resident to {{ property.flatAddress }}</h4>
                    <p class="box-subtitle">New resident will be linked to this property and receive access credentials.</p>
                  </div>
                  <button type="button" class="btn-text-close" @click="addingResidentMode = false">Cancel</button>
                </div>

                <div class="form-grid-3">
                  <div class="form-group">
                    <label class="form-label">Full Name <span class="req">*</span></label>
                    <input
                      v-model="residentForm.name"
                      class="form-input"
                      placeholder="e.g. Adebayo Ogunlesi"
                    />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Phone Number <span class="req">*</span></label>
                    <input
                      v-model="residentForm.phone"
                      class="form-input"
                      placeholder="e.g. 08012345678"
                    />
                  </div>
                  <div class="form-group">
                    <label class="form-label">Email (Optional)</label>
                    <input
                      v-model="residentForm.email"
                      type="email"
                      class="form-input"
                      placeholder="e.g. adebayo@example.com"
                    />
                  </div>
                </div>

                <div class="form-group mt-3">
                  <label class="form-label">Resident Role</label>
                  <div class="role-radio-group">
                    <label class="role-radio-label" :class="{ 'is-selected': residentForm.role === 'owner' }">
                      <input type="radio" v-model="residentForm.role" value="owner" />
                      <div class="role-info">
                        <strong>Homeowner / Landlord</strong>
                        <span>Primary resident with full property authority</span>
                      </div>
                    </label>
                    <label class="role-radio-label" :class="{ 'is-selected': residentForm.role === 'tenant' }">
                      <input type="radio" v-model="residentForm.role" value="tenant" />
                      <div class="role-info">
                        <strong>Tenant</strong>
                        <span>Lease occupant with visitor pass privileges</span>
                      </div>
                    </label>
                    <label class="role-radio-label" :class="{ 'is-selected': residentForm.role === 'family' }">
                      <input type="radio" v-model="residentForm.role" value="family" />
                      <div class="role-info">
                        <strong>Family Member</strong>
                        <span>Resident dependant / household member</span>
                      </div>
                    </label>
                  </div>
                </div>

                <p v-if="residentError" class="form-error mt-2">{{ residentError }}</p>

                <div class="add-resident-actions mt-3">
                  <button
                    type="button"
                    class="btn btn-outline"
                    @click="addingResidentMode = false"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    class="btn btn-primary btn-with-spinner"
                    :disabled="savingResident"
                    @click="saveResident"
                  >
                    <BrandSpinner v-if="savingResident" :size="15" color="#ffffff" />
                    <span>{{ savingResident ? 'Assigning...' : 'Assign Resident' }}</span>
                  </button>
                </div>
              </div>

              <!-- Residents List -->
              <div v-if="property.residents && property.residents.length > 0" class="residents-list">
                <div v-for="res in property.residents" :key="res.id" class="resident-item">
                  <div class="res-avatar">
                    {{ (res.name || 'R').charAt(0).toUpperCase() }}
                  </div>
                  <div class="res-info">
                    <div class="res-name-row">
                      <span class="res-name">{{ res.name }}</span>
                      <span class="res-role-badge" :class="`role-${(res.role || 'owner').toLowerCase()}`">
                        {{ res.role || 'Resident' }}
                      </span>
                    </div>
                    <div class="res-contact-row">
                      <span v-if="res.phone" class="contact-pill">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        {{ res.phone }}
                      </span>
                      <span v-if="res.email" class="contact-pill">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        {{ res.email }}
                      </span>
                    </div>
                  </div>
                  <button
                    type="button"
                    class="btn-remove-res"
                    title="Remove resident from property"
                    :disabled="deletingResidentId === res.id"
                    @click="removeResident(res.id, res.name)"
                  >
                    <BrandSpinner v-if="deletingResidentId === res.id" :size="13" color="#DC2626" />
                    <span v-else>Remove</span>
                  </button>
                </div>
              </div>

              <!-- Empty State -->
              <div v-else-if="!addingResidentMode" class="empty-occupants-card">
                <div class="empty-avatar-icon">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                </div>
                <h4 class="empty-title">No residents assigned</h4>
                <p class="empty-desc">This unit is currently unoccupied. Add an owner or tenant to register passes and receive visitor arrivals.</p>
                <button
                  type="button"
                  class="btn btn-outline btn-sm mt-3"
                  @click="openAddResidentForm"
                >
                  + Assign Resident
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- MODAL: Edit Property -->
    <div v-if="editModalOpen" class="modal-backdrop" @click.self="editModalOpen = false">
      <div class="modal-box modal-box--md">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">Edit Property #{{ property?.id }}</h2>
            <p class="modal-subtitle">Update property address details and notes</p>
          </div>
          <button class="modal-close" @click="editModalOpen = false">✕</button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Full Address / Code <span class="req">*</span></label>
            <input v-model="editForm.flat_address" class="form-input" placeholder="e.g. VIP 1, VVIP 2, 1% 3" />
          </div>

          <div class="form-group">
            <label class="form-label">Unit Type</label>
            <select v-model="editForm.unit_type" class="form-select">
              <option value="Apartment">Apartment</option>
              <option value="Duplex">Duplex</option>
              <option value="Terrace">Terrace</option>
              <option value="Penthouse">Penthouse</option>
              <option value="Bungalow">Bungalow</option>
              <option value="Plot">Plot</option>
              <option value="Commercial">Commercial / Shop</option>
            </select>
          </div>

          <div class="form-grid-3">
            <div class="form-group">
              <label class="form-label">Tier 1 (Lane/VIP)</label>
              <input v-model="editForm.lane" class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Tier 2 (House/VVIP)</label>
              <input v-model="editForm.house" class="form-input" />
            </div>
            <div class="form-group">
              <label class="form-label">Tier 3 (Flat/1%)</label>
              <input v-model="editForm.flat" class="form-input" />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Notes (Optional)</label>
            <textarea
              v-model="editForm.notes"
              class="form-input"
              rows="2"
              placeholder="e.g. Corner unit, ground floor, fiber line installed"
            />
          </div>

          <p v-if="editError" class="form-error">{{ editError }}</p>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline" @click="editModalOpen = false">Cancel</button>
          <button
            class="btn btn-primary btn-with-spinner"
            :disabled="savingEdit"
            @click="savePropertyEdit"
          >
            <BrandSpinner v-if="savingEdit" :size="16" color="#ffffff" />
            <span>{{ savingEdit ? 'Updating...' : 'Update Property' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: Delete Property Confirmation -->
    <div v-if="showDeleteConfirm" class="modal-backdrop" @click.self="showDeleteConfirm = false">
      <div class="modal-box modal-box--sm">
        <div class="modal-header">
          <h2 class="modal-title">Delete Property</h2>
          <button class="modal-close" @click="showDeleteConfirm = false">✕</button>
        </div>
        <div class="modal-body">
          <p>
            Are you sure you want to remove <strong>{{ property?.flatAddress }}</strong> from the estate inventory?
          </p>
          <p class="text-hint mt-2">
            This action cannot be undone. Properties with assigned active residents cannot be deleted.
          </p>
          <p v-if="deleteError" class="form-error mt-2">{{ deleteError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" :disabled="deleting" @click="showDeleteConfirm = false">Cancel</button>
          <button class="btn btn-danger btn-with-spinner" :disabled="deleting" @click="executeDeleteProperty">
            <BrandSpinner v-if="deleting" :size="15" color="#ffffff" />
            <span>{{ deleting ? 'Deleting...' : 'Confirm Delete' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { propertiesApi, residentsApi } from '@/api'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import BrandSpinner from '@/components/BrandSpinner.vue'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const { showToast } = useToast()

const propertyId = computed(() => Number(route.params.id))
const activeEstateId = computed(() => Number(auth.activeEstateId) || 1)

const property = ref<any>(null)
const loading = ref(true)
const error = ref('')

// Add Resident State
const addingResidentMode = ref(false)
const residentForm = ref({
  name: '',
  phone: '',
  email: '',
  role: 'owner',
})
const savingResident = ref(false)
const residentError = ref('')
const deletingResidentId = ref<number | null>(null)

// Edit Property State
const editModalOpen = ref(false)
const savingEdit = ref(false)
const editError = ref('')
const editForm = ref({
  flat_address: '',
  unit_type: 'Apartment',
  lane: '',
  house: '',
  flat: '',
  notes: '',
})

// Delete Property State
const showDeleteConfirm = ref(false)
const deleting = ref(false)
const deleteError = ref('')

const hierarchyTags = computed(() => {
  if (!property.value) return []
  if (property.value.hierarchyData && typeof property.value.hierarchyData === 'object') {
    return Object.entries(property.value.hierarchyData).map(([k, v]) => ({
      key: k,
      value: String(v || '').trim(),
    })).filter(t => t.value)
  }
  // Fallback to lane, house, flat
  const out: { key: string; value: string }[] = []
  if (property.value.lane) out.push({ key: 'Lane', value: property.value.lane })
  if (property.value.house) out.push({ key: 'House', value: property.value.house })
  if (property.value.flat) out.push({ key: 'Flat', value: property.value.flat })
  return out
})

function formatDate(dateStr?: string): string {
  if (!dateStr) return '—'
  try {
    return new Date(dateStr).toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  } catch {
    return dateStr
  }
}

async function loadProperty() {
  if (!propertyId.value) {
    error.value = 'Invalid property ID.'
    loading.value = false
    return
  }

  loading.value = true
  error.value = ''

  try {
    const res = await propertiesApi.getOne(propertyId.value)
    if (res.data?.property) {
      property.value = res.data.property
    } else {
      // Fallback: search in getAll if getOne gave empty structure
      const allRes = await propertiesApi.getAll(activeEstateId.value)
      const found = (allRes.data?.properties || []).find((p: any) => p.id === propertyId.value)
      if (found) {
        property.value = found
      } else {
        error.value = `Property #${propertyId.value} not found in this estate.`
      }
    }
  } catch (err: any) {
    // If 404, try to look up in estate properties list as fallback
    try {
      const allRes = await propertiesApi.getAll(activeEstateId.value)
      const found = (allRes.data?.properties || []).find((p: any) => p.id === propertyId.value)
      if (found) {
        property.value = found
        return
      }
    } catch {
      // ignore
    }
    error.value = err.response?.data?.message || 'Failed to load property details.'
  } finally {
    loading.value = false
  }
}

function openAddResidentForm() {
  residentForm.value = {
    name: '',
    phone: '',
    email: '',
    role: 'owner',
  }
  residentError.value = ''
  addingResidentMode.value = true
}

async function saveResident() {
  if (!property.value) return
  if (!residentForm.value.name.trim()) {
    residentError.value = 'Resident name is required.'
    return
  }
  if (!residentForm.value.phone.trim()) {
    residentError.value = 'Phone number is required.'
    return
  }

  savingResident.value = true
  residentError.value = ''

  try {
    const payload: Record<string, any> = {
      name: residentForm.value.name.trim(),
      phone: residentForm.value.phone.trim(),
      role: residentForm.value.role,
      unit_id: property.value.id,
      estate_id: activeEstateId.value,
    }
    if (residentForm.value.email.trim()) {
      payload.email = residentForm.value.email.trim()
    }

    await residentsApi.create(payload)
    showToast(`Resident ${residentForm.value.name} assigned successfully!`, 'success')
    addingResidentMode.value = false
    await loadProperty()
  } catch (err: any) {
    const msg = err?.response?.data?.message || 'Failed to assign resident.'
    residentError.value = msg
    showToast(msg, 'error')
  } finally {
    savingResident.value = false
  }
}

async function removeResident(residentId: number, residentName: string) {
  if (!confirm(`Are you sure you want to remove ${residentName} from this property?`)) return
  deletingResidentId.value = residentId

  try {
    await residentsApi.delete(residentId)
    showToast(`Resident ${residentName} removed.`, 'success')
    await loadProperty()
  } catch (err: any) {
    showToast(err?.response?.data?.message || 'Failed to remove resident.', 'error')
  } finally {
    deletingResidentId.value = null
  }
}

function openEditModal() {
  if (!property.value) return
  editForm.value = {
    flat_address: property.value.flatAddress || '',
    unit_type: property.value.unitType || 'Apartment',
    lane: property.value.lane || '',
    house: property.value.house || '',
    flat: property.value.flat || '',
    notes: property.value.notes || '',
  }
  editError.value = ''
  editModalOpen.value = true
}

async function savePropertyEdit() {
  if (!property.value) return
  if (!editForm.value.flat_address.trim()) {
    editError.value = 'Property address is required.'
    return
  }

  savingEdit.value = true
  editError.value = ''

  try {
    await propertiesApi.update(property.value.id, {
      flat_address: editForm.value.flat_address.trim(),
      unit_type: editForm.value.unit_type,
      lane: editForm.value.lane.trim(),
      house: editForm.value.house.trim(),
      flat: editForm.value.flat.trim(),
      notes: editForm.value.notes.trim(),
    })
    showToast('Property updated successfully.', 'success')
    editModalOpen.value = false
    await loadProperty()
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Failed to update property.'
    editError.value = msg
    showToast(msg, 'error')
  } finally {
    savingEdit.value = false
  }
}

async function executeDeleteProperty() {
  if (!property.value) return
  deleting.value = true
  deleteError.value = ''

  try {
    await propertiesApi.delete(property.value.id)
    showToast('Property deleted successfully.', 'success')
    showDeleteConfirm.value = false
    router.push('/properties')
  } catch (err: any) {
    const msg = err.response?.data?.message || 'Failed to delete property.'
    deleteError.value = msg
    showToast(msg, 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(async () => {
  await loadProperty()
  if (route.query.addResident === 'true') {
    openAddResidentForm()
  }
})
</script>

<style scoped>
.property-detail-view {
  padding: 0 0 40px;
  width: 100%;
  max-width: 100%;
  margin: 0;
}

/* Page Top Navigation */
.page-top-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #047857;
  font-weight: 600;
  font-size: 14px;
  text-decoration: none;
  padding: 6px 12px;
  border-radius: 8px;
  background: #F0FDF4;
  border: 1px solid #DCFCE7;
  transition: all 0.15s ease;
}

.back-link:hover {
  background: #DCFCE7;
}

.breadcrumbs {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13.5px;
  color: #6B7280;
}

.bc-link {
  color: #6B7280;
  text-decoration: none;
}

.bc-link:hover {
  color: #111827;
  text-decoration: underline;
}

.bc-sep {
  color: #D1D5DB;
}

.bc-current {
  color: #111827;
  font-weight: 500;
}

/* Header Card */
.property-detail-header {
  padding: 24px 28px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 24px;
  margin-bottom: 24px;
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.badge-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
  flex-wrap: wrap;
}

.type-pill {
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  background: #E8F5E9;
  color: #047857;
  padding: 3px 10px;
  border-radius: 6px;
}

.id-pill {
  font-size: 12px;
  font-weight: 600;
  color: #6B7280;
  background: #F3F4F6;
  padding: 3px 8px;
  border-radius: 6px;
}

.status-pill {
  font-size: 12px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 12px;
}

.status-occupied {
  background: #DCFCE7;
  color: #166534;
}

.status-vacant {
  background: #F3F4F6;
  color: #6B7280;
}

.property-title {
  font-size: 24px;
  font-weight: 800;
  color: #111827;
  margin: 0 0 4px;
  letter-spacing: -0.02em;
}

.property-subtitle {
  font-size: 13.5px;
  color: #6B7280;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

/* Detail Grid */
.detail-grid {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 24px;
  align-items: start;
}

.detail-sidebar {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
  overflow: hidden;
}

.card-header-clean {
  padding: 18px 22px;
  border-bottom: 1px solid #F3F4F6;
}

.card-header-clean.header-flex {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-title {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 8px;
}

.count-badge {
  font-size: 13px;
  font-weight: 600;
  color: #047857;
  background: #F0FDF4;
  padding: 2px 8px;
  border-radius: 10px;
}

.card-subtitle {
  font-size: 12.5px;
  color: #6B7280;
  margin: 3px 0 0;
}

.card-body-clean {
  padding: 20px 22px;
}

/* Hierarchy Tags */
.hierarchy-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.h-tag {
  background: transparent;
  border: 1px solid #E2E8F0;
  padding: 6px 12px;
  border-radius: 8px;
  font-size: 13px;
  color: #334155;
}

.h-tag strong {
  color: #0F172A;
  font-weight: 600;
}

/* Metadata List */
.meta-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin: 0;
}

.meta-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 13.5px;
  padding-bottom: 12px;
  border-bottom: 1px solid #F3F4F6;
}

.meta-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.meta-row dt {
  color: #6B7280;
  font-weight: 500;
}

.meta-row dd {
  margin: 0;
  color: #111827;
  text-align: right;
}

.type-pill-subtle {
  background: #F3F4F6;
  color: #374151;
  font-weight: 600;
  font-size: 12px;
  padding: 2px 8px;
  border-radius: 6px;
}

/* Add Resident Box */
.add-resident-box {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 18px 20px;
}

.add-resident-box-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 14px;
}

.box-title {
  font-size: 14.5px;
  font-weight: 700;
  color: #0F172A;
  margin: 0 0 2px;
}

.box-subtitle {
  font-size: 12px;
  color: #64748B;
  margin: 0;
}

.btn-text-close {
  background: none;
  border: none;
  color: #6B7280;
  font-size: 12.5px;
  font-weight: 500;
  cursor: pointer;
}

.btn-text-close:hover {
  color: #111827;
  text-decoration: underline;
}

.form-grid-3 {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.role-radio-group {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 10px;
  margin-top: 6px;
}

.role-radio-label {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 14px;
  background: #ffffff;
  border: 1px solid #CBD5E1;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.role-radio-label.is-selected {
  border-color: #047857;
  background: #F0FDF4;
}

.role-radio-label input {
  margin-top: 3px;
  accent-color: #047857;
}

.role-info {
  display: flex;
  flex-direction: column;
}

.role-info strong {
  font-size: 13px;
  color: #1E293B;
}

.role-info span {
  font-size: 11.5px;
  color: #64748B;
}

.add-resident-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

/* Residents List */
.residents-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.resident-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 18px;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  background: #ffffff;
  transition: all 0.15s ease;
}

.resident-item:hover {
  border-color: #D1D5DB;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
}

.res-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: #E8F5E9;
  color: #047857;
  font-weight: 700;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.res-info {
  flex: 1;
  min-width: 0;
}

.res-name-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}

.res-name {
  font-size: 14.5px;
  font-weight: 700;
  color: #111827;
}

.res-role-badge {
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  padding: 2px 7px;
  border-radius: 4px;
}

.role-owner {
  background: #E0E7FF;
  color: #3730A3;
}

.role-tenant {
  background: #FEF3C7;
  color: #92400E;
}

.role-family {
  background: #F1F5F9;
  color: #475569;
}

.res-contact-row {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}

.contact-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12.5px;
  color: #4B5563;
}

.btn-remove-res {
  background: transparent;
  border: 1px solid #FCA5A5;
  color: #DC2626;
  font-size: 12px;
  font-weight: 600;
  padding: 5px 12px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-remove-res:hover:not(:disabled) {
  background: #FEF2F2;
  border-color: #EF4444;
}

/* Empty State */
.empty-occupants-card {
  padding: 48px 24px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.empty-avatar-icon {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #F3F4F6;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 14px;
}

.empty-title {
  font-size: 16px;
  font-weight: 700;
  color: #1F2937;
  margin: 0 0 6px;
}

.empty-desc {
  font-size: 13.5px;
  color: #6B7280;
  max-width: 400px;
  margin: 0;
  line-height: 1.5;
}

/* Loading & Error States */
.detail-loading-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 100px 20px;
}

.loading-label {
  font-size: 14px;
  color: #6B7280;
}

.detail-error-card {
  padding: 60px 20px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #E5E7EB;
}

/* Reusable buttons & inputs */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 8px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all 0.15s ease;
  text-decoration: none;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12.5px;
}

.btn-primary {
  background: #047857;
  color: #ffffff;
}

.btn-primary:hover:not(:disabled) {
  background: #065F46;
}

.btn-outline {
  background: #ffffff;
  border: 1px solid #D1D5DB;
  color: #374151;
}

.btn-outline:hover:not(:disabled) {
  background: #F9FAFB;
  border-color: #9CA3AF;
}

.btn-danger-outline {
  background: #ffffff;
  border: 1.5px solid #FCA5A5;
  color: #DC2626;
}

.btn-danger-outline:hover:not(:disabled) {
  background: #FEF2F2;
  border-color: #DC2626;
}

.btn-danger {
  background: #DC2626;
  color: #ffffff;
}

.btn-danger:hover:not(:disabled) {
  background: #B91C1C;
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

.req {
  color: #DC2626;
}

.form-input, .form-select {
  padding: 8px 12px;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  font-size: 13.5px;
  color: #111827;
  background: #ffffff;
  width: 100%;
}

.form-input:focus, .form-select:focus {
  outline: none;
  border-color: #047857;
  box-shadow: 0 0 0 2px rgba(4, 120, 87, 0.15);
}

.form-error {
  color: #DC2626;
  font-size: 12.5px;
  font-weight: 500;
  margin: 4px 0 0;
}

/* Modals */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
  padding: 16px;
}

.modal-box {
  background: #ffffff;
  border-radius: 14px;
  width: 100%;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.modal-box--sm { max-width: 440px; }
.modal-box--md { max-width: 580px; }

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 17px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.modal-subtitle {
  font-size: 12.5px;
  color: #6B7280;
  margin: 2px 0 0;
}

.modal-close {
  background: none;
  border: none;
  font-size: 18px;
  color: #9CA3AF;
  cursor: pointer;
}

.modal-body {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.modal-footer {
  padding: 16px 24px;
  background: #F9FAFB;
  border-top: 1px solid #E5E7EB;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.text-hint {
  font-size: 13px;
  color: #6B7280;
  margin: 0;
}

.text-emerald { color: #047857; }
.text-muted { color: #6B7280; }
.font-semibold { font-weight: 600; }
.font-medium { font-weight: 500; }

@media (max-width: 900px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
  .property-detail-header {
    flex-direction: column;
    align-items: stretch;
  }
  .header-actions {
    justify-content: flex-start;
  }
}
</style>
