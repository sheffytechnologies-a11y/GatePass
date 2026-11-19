<template>
  <div class="resident-create-page">
    <!-- Header with Back & Title -->
    <header class="page-header">
      <div class="header-content">
        <button type="button" class="btn-back" @click="goBack" title="Back to Residents">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
          </svg>
          <span>Back to Residents</span>
        </button>
        <div class="header-titles">
          <h1 class="page-title">Add Resident & Unit Assignment</h1>
        </div>
      </div>
    </header>

    <!-- Error Alert if any -->
    <div v-if="formError" class="alert-error">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span>{{ formError }}</span>
    </div>

    <!-- 2-Column Responsive Form Layout -->
    <main class="page-grid">
      <!-- COLUMN 1: Primary Resident (Homeowner) -->
      <section class="form-card">
        <div class="card-head">
          <h2 class="card-title">Primary Resident (Homeowner)</h2>
        </div>

        <div class="card-body">
          <!-- Profile Photo Upload -->
          <div class="photo-upload-box">
            <div class="photo-avatar-preview" :style="form.avatar ? { backgroundImage: `url(${form.avatar})` } : {}">
              <div v-if="!form.avatar" class="avatar-placeholder">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                <div class="camera-badge">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                  </svg>
                </div>
              </div>
            </div>

            <div class="photo-meta">
              <input
                ref="ownerPhotoInput"
                type="file"
                accept="image/*"
                class="sr-only"
                @change="onOwnerPhotoFilePicked"
              />
              <div class="photo-actions">
                <button type="button" class="btn-photo-action" @click="triggerOwnerPhoto">
                  {{ form.avatar ? 'Change Photo' : 'Upload Photo' }}
                </button>
                <button v-if="form.avatar" type="button" class="btn-photo-remove" @click="removeOwnerPhoto">
                  Remove
                </button>
              </div>
              <span class="photo-tip">Resident profile photo (JPG, PNG • Crop available on upload)</span>
            </div>
          </div>

          <!-- Multi-Estate Selector if Applicable -->
          <div v-if="estates.length > 1" class="form-group">
            <label class="form-label">Estate Community <span class="req">*</span></label>
            <select v-model="form.estate_id" class="form-select" @change="onEstateChange">
              <option value="">Select estate...</option>
              <option v-for="e in estates" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
          </div>

          <!-- Full Name -->
          <div class="form-group">
            <label class="form-label">Owner's Full Name <span class="req">*</span></label>
            <input
              v-model="form.name"
              class="form-input"
              placeholder="e.g. Babatunde Fashola"
              required
            />
          </div>

          <!-- Phone & Email 2-Col Grid -->
          <div class="form-grid-2">
            <div class="form-group">
              <label class="form-label">Phone Number <span class="req">*</span></label>
              <input
                v-model="form.phone"
                class="form-input"
                type="tel"
                placeholder="e.g. 08012345678"
                required
              />
            </div>
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input
                v-model="form.email"
                class="form-input"
                type="email"
                placeholder="e.g. resident@example.com"
              />
            </div>
          </div>

          <!-- Property Unit Selection & Manual Address -->
          <div class="form-group">
            <div class="field-header">
              <label class="form-label mb-0">Property Unit Assignment <span class="req">*</span></label>
              <button
                type="button"
                class="link-toggle"
                @click="manualAddressMode = !manualAddressMode"
              >
                {{ manualAddressMode ? '← Pick from configured units' : '+ Custom / Manual Address' }}
              </button>
            </div>

            <!-- Configured Unit Dropdown -->
            <div v-if="!manualAddressMode" class="unit-select-wrap">
              <select v-model="form.unit_id" class="form-select" :disabled="loadingProperties">
                <option value="">
                  {{ loadingProperties ? 'Loading property units...' : 'Select a configured property...' }}
                </option>
                <option v-for="p in estateProperties" :key="p.id" :value="p.id">
                  {{ p.address }} · {{ p.unitType || 'Unit' }} {{ p.residentsCount ? `(${p.residentsCount} resident${p.residentsCount > 1 ? 's' : ''})` : '(Vacant)' }}
                </option>
              </select>
            </div>

            <!-- Manual Address Fields -->
            <div v-else class="manual-address-card">
              <div class="form-grid-3">
                <div class="form-group mb-0">
                  <label class="form-label-xs">Lane / Street</label>
                  <input v-model="form.lane" class="form-input-sm" placeholder="e.g. Maple St" />
                </div>
                <div class="form-group mb-0">
                  <label class="form-label-xs">House / Building</label>
                  <input v-model="form.house" class="form-input-sm" placeholder="e.g. House 4B" />
                </div>
                <div class="form-group mb-0">
                  <label class="form-label-xs">Flat / Suite</label>
                  <input v-model="form.flat" class="form-input-sm" placeholder="e.g. Suite 2" />
                </div>
              </div>
            </div>
          </div>

          <!-- Landlord Resides in Unit Toggle -->
          <div class="toggle-card">
            <label class="switch-control">
              <input type="checkbox" v-model="form.landlordIsOccupant" />
              <span class="switch-slider"></span>
            </label>
            <div class="toggle-copy">
              <div class="toggle-title">Landlord resides in this unit</div>
              <p class="toggle-desc">Toggle on if the homeowner physically lives in this property rather than renting to tenants.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- COLUMN 2: Household Occupants & Tenants -->
      <section class="form-card">
        <div class="card-head head-between">
          <h2 class="card-title">Household Occupants & Tenants ({{ form.tenants.length }})</h2>
          <button type="button" class="btn btn-outline-emerald btn-sm" @click="addTenant">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Add Occupant</span>
          </button>
        </div>

        <div class="card-body">
          <!-- Empty State when no occupants -->
          <div v-if="form.tenants.length === 0" class="empty-occupants-box">
            <div class="empty-icon-circle">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </div>
            <h3 class="empty-title">No Additional Occupants</h3>
            <p class="empty-desc">
              If other residents or tenants share this property, click "+ Add Occupant" to register them with separate gate access passes.
            </p>
            <button type="button" class="btn btn-outline-emerald" @click="addTenant">
              + Add First Co-Resident
            </button>
          </div>

          <!-- Occupant Cards List -->
          <div v-else class="occupants-stack">
            <article
              v-for="(tenant, idx) in form.tenants"
              :key="idx"
              class="occupant-item"
            >
              <div class="occupant-head">
                <span class="occupant-badge">Occupant #{{ idx + 1 }} · Co-Resident</span>
                <button
                  type="button"
                  class="btn-del-occupant"
                  @click="removeTenant(idx)"
                  title="Remove occupant"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>

              <!-- Occupant Photo & Fields -->
              <div class="occupant-body">
                <div class="occupant-photo-row">
                  <div class="occupant-avatar" :style="tenant.avatar ? { backgroundImage: `url(${tenant.avatar})` } : {}">
                    <svg v-if="!tenant.avatar" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2">
                      <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                      <circle cx="12" cy="7" r="4"/>
                    </svg>
                  </div>
                  <div class="occupant-photo-ctrls">
                    <input
                      :id="`occupantPhotoInput_${idx}`"
                      type="file"
                      accept="image/*"
                      class="sr-only"
                      @change="onTenantPhotoFilePicked(idx, $event)"
                    />
                    <button type="button" class="link-btn" @click="triggerTenantPhoto(idx)">
                      {{ tenant.avatar ? 'Change Photo' : 'Upload Photo' }}
                    </button>
                    <button v-if="tenant.avatar" type="button" class="link-btn text-danger" @click="removeTenantPhoto(idx)">
                      Remove
                    </button>
                  </div>
                </div>

                <div class="form-group mb-2">
                  <label class="form-label-xs">Full Name <span class="req">*</span></label>
                  <input
                    v-model="tenant.name"
                    class="form-input-sm"
                    placeholder="e.g. Kemi Adeleke"
                    required
                  />
                </div>

                <div class="form-grid-2">
                  <div class="form-group mb-0">
                    <label class="form-label-xs">Phone No. <span class="req">*</span></label>
                    <input
                      v-model="tenant.phone"
                      class="form-input-sm"
                      type="tel"
                      placeholder="e.g. 08023456789"
                      required
                    />
                  </div>
                  <div class="form-group mb-0">
                    <label class="form-label-xs">Email Address</label>
                    <input
                      v-model="tenant.email"
                      class="form-input-sm"
                      type="email"
                      placeholder="e.g. kemi@example.com"
                    />
                  </div>
                </div>
              </div>
            </article>
          </div>
        </div>
      </section>
    </main>

    <!-- Bottom Sticky Save Bar -->
    <footer class="bottom-action-bar">
      <div class="footer-hint">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#047857" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="16" x2="12" y2="12"/>
          <line x1="12" y1="8" x2="12.01" y2="8"/>
        </svg>
        <span>Temporary login credentials will be generated automatically for all registered residents.</span>
      </div>
      <div class="footer-actions">
        <button type="button" class="btn btn-outline" @click="goBack">Cancel</button>
        <button type="button" class="btn btn-primary btn-save" :disabled="saving" @click="save">
          <BrandSpinner v-if="saving" :size="16" color="#ffffff" />
          <span>{{ saving ? 'Saving Records...' : 'Save Resident Record' }}</span>
        </button>
      </div>
    </footer>

    <!-- Image Cropper Modal for Avatar Uploads -->
    <ImageCropperModal
      v-model="cropperOpen"
      :image-src="cropperImageSrc"
      :title="cropperTitle"
      @crop="onCroppedPhoto"
      @cancel="onCropCancel"
    />

    <!-- Credentials Modal on Success -->
    <div v-if="newCredentials" class="creds-backdrop">
      <div class="creds-modal card">
        <div class="creds-header">
          <div class="creds-icon">✓</div>
          <h3 class="creds-title">Resident Registered Successfully!</h3>
          <p class="creds-sub">
            Share these temporary login credentials with the resident(s). They will be prompted to change their password on first sign in.
          </p>
        </div>

        <div class="creds-list">
          <div v-for="c in newCredentials" :key="c.phone" class="creds-item">
            <div class="creds-item-info">
              <strong class="creds-name">{{ c.name }}</strong>
              <span class="creds-phone">{{ c.phone }}</span>
            </div>
            <div class="creds-pass-wrap">
              <span class="creds-pass">{{ c.password }}</span>
              <button type="button" class="btn-copy" @click="copyText(c.password)" title="Copy password">
                Copy
              </button>
            </div>
          </div>
        </div>

        <div class="creds-actions">
          <button type="button" class="btn btn-primary" @click="goBack">
            Return to Residents
          </button>
          <button type="button" class="btn btn-outline" @click="resetForAnother">
            + Register Another Resident
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { residentsApi, estatesApi, propertiesApi } from '@/api/index'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import ImageCropperModal from '@/components/ImageCropperModal.vue'
import BrandSpinner from '@/components/BrandSpinner.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const { showToast } = useToast()

const estates = ref<any[]>([])
const estateProperties = ref<any[]>([])
const loadingProperties = ref(false)
const manualAddressMode = ref(false)
const saving = ref(false)
const formError = ref('')
const newCredentials = ref<{ name: string; phone: string; password: string }[] | null>(null)

// Owner photo input ref
const ownerPhotoInput = ref<HTMLInputElement | null>(null)

// Image Cropper State
const cropperOpen = ref(false)
const cropperImageSrc = ref('')
const cropperTitle = ref('Crop Profile Photo')
const activeCropTarget = ref<'owner' | { tenantIndex: number }>('owner')

const form = ref({
  name: '',
  phone: '',
  email: '',
  avatar: '',
  estate_id: '' as number | '',
  unit_id: '' as number | '',
  lane: '',
  house: '',
  flat: '',
  landlordIsOccupant: false,
  tenants: [] as { name: string; phone: string; email: string; avatar: string }[],
})

function goBack() {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/residents')
  }
}

// ── Photo Upload & Cropper Logic ──
function triggerOwnerPhoto() {
  ownerPhotoInput.value?.click()
}

function removeOwnerPhoto() {
  form.value.avatar = ''
  if (ownerPhotoInput.value) ownerPhotoInput.value.value = ''
}

function onOwnerPhotoFilePicked(e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  if (file.size > 8 * 1024 * 1024) {
    showToast('Photo must be less than 8MB', 'error')
    return
  }
  const reader = new FileReader()
  reader.onload = () => {
    activeCropTarget.value = 'owner'
    cropperTitle.value = "Crop Owner's Profile Photo"
    cropperImageSrc.value = reader.result as string
    cropperOpen.value = true
  }
  reader.readAsDataURL(file)
}

function triggerTenantPhoto(idx: number) {
  const el = document.getElementById(`occupantPhotoInput_${idx}`) as HTMLInputElement
  el?.click()
}

function removeTenantPhoto(idx: number) {
  if (form.value.tenants[idx]) {
    form.value.tenants[idx].avatar = ''
  }
}

function onTenantPhotoFilePicked(idx: number, e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (!file) return
  if (file.size > 8 * 1024 * 1024) {
    showToast('Photo must be less than 8MB', 'error')
    return
  }
  const reader = new FileReader()
  reader.onload = () => {
    activeCropTarget.value = { tenantIndex: idx }
    cropperTitle.value = `Crop Occupant #${idx + 1} Photo`
    cropperImageSrc.value = reader.result as string
    cropperOpen.value = true
  }
  reader.readAsDataURL(file)
}

function onCroppedPhoto(croppedBase64: string) {
  if (activeCropTarget.value === 'owner') {
    form.value.avatar = croppedBase64
    showToast('Profile photo cropped & ready.', 'success')
  } else if (typeof activeCropTarget.value === 'object') {
    const idx = activeCropTarget.value.tenantIndex
    if (form.value.tenants[idx]) {
      form.value.tenants[idx].avatar = croppedBase64
      showToast(`Occupant #${idx + 1} photo cropped.`, 'success')
    }
  }
  cropperOpen.value = false
}

function onCropCancel() {
  cropperOpen.value = false
}

// ── Occupant List Management ──
function addTenant() {
  form.value.tenants.push({
    name: '',
    phone: '',
    email: '',
    avatar: '',
  })
}

function removeTenant(i: number) {
  form.value.tenants.splice(i, 1)
}

// ── Estates & Units Loading ──
async function loadEstates() {
  try {
    const res = await estatesApi.getAll()
    estates.value = res.data.estates
    if (estates.value.length && !form.value.estate_id) {
      const targetId = Number(route.query.estate_id || auth.activeEstateId || estates.value[0].id)
      form.value.estate_id = targetId
      await loadProperties(targetId)
    }
  } catch {
    /* ignore */
  }
}

async function onEstateChange() {
  form.value.unit_id = ''
  if (form.value.estate_id) {
    await loadProperties(Number(form.value.estate_id))
  }
}

async function loadProperties(estateId: number) {
  loadingProperties.value = true
  try {
    const res = await propertiesApi.getAll(estateId)
    estateProperties.value = res.data?.properties || []

    // If query has unit_id or property_id, pre-select it
    const preselectedUnit = route.query.unit_id || route.query.property_id
    if (preselectedUnit) {
      const match = estateProperties.value.find((p: any) => String(p.id) === String(preselectedUnit))
      if (match) {
        form.value.unit_id = match.id
      }
    }
  } catch {
    estateProperties.value = []
  } finally {
    loadingProperties.value = false
  }
}

// ── Save Function ──
async function save() {
  formError.value = ''
  saving.value = true

  try {
    if (!form.value.name.trim()) {
      formError.value = "Owner's Full Name is required."
      showToast(formError.value, 'error')
      saving.value = false
      return
    }
    if (!form.value.phone.trim()) {
      formError.value = "Owner's Phone Number is required."
      showToast(formError.value, 'error')
      saving.value = false
      return
    }
    if (!form.value.unit_id && !form.value.lane?.trim() && !form.value.house?.trim() && !form.value.flat?.trim()) {
      formError.value = 'Please select a configured unit or enter an address.'
      showToast(formError.value, 'error')
      saving.value = false
      return
    }

    const ownerPayload: Record<string, unknown> = {
      name: form.value.name.trim(),
      phone: form.value.phone.trim(),
      role: 'owner',
      lane: form.value.lane,
      house: form.value.house,
      flat: form.value.flat,
      landlord_is_occupant: form.value.landlordIsOccupant,
    }
    if (form.value.email?.trim()) ownerPayload.email = form.value.email.trim()
    if (form.value.avatar) ownerPayload.avatar = form.value.avatar
    if (form.value.unit_id) ownerPayload.unit_id = form.value.unit_id
    if (form.value.estate_id) ownerPayload.estate_id = form.value.estate_id

    const ownerRes = await residentsApi.create(ownerPayload)
    const credentials: { name: string; phone: string; password: string }[] = []
    if (ownerRes.data?.temporaryPassword) {
      credentials.push({
        name: form.value.name,
        phone: form.value.phone,
        password: ownerRes.data.temporaryPassword,
      })
    }

    // Save occupants
    for (const t of form.value.tenants) {
      if (!t.name.trim() || !t.phone.trim()) continue
      const tp: Record<string, unknown> = {
        name: t.name.trim(),
        phone: t.phone.trim(),
        role: 'tenant',
        lane: form.value.lane,
        house: form.value.house,
        flat: form.value.flat,
      }
      if (t.email?.trim()) tp.email = t.email.trim()
      if (t.avatar) tp.avatar = t.avatar
      if (form.value.unit_id) tp.unit_id = form.value.unit_id
      if (form.value.estate_id) tp.estate_id = form.value.estate_id

      const tenantRes = await residentsApi.create(tp)
      if (tenantRes.data?.temporaryPassword) {
        credentials.push({
          name: t.name,
          phone: t.phone,
          password: tenantRes.data.temporaryPassword,
        })
      }
    }

    showToast('Resident record registered successfully!', 'success')
    if (credentials.length > 0) {
      newCredentials.value = credentials
    } else {
      goBack()
    }
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? 'Failed to save resident record.'
    showToast(formError.value, 'error')
  } finally {
    saving.value = false
  }
}

function resetForAnother() {
  newCredentials.value = null
  form.value.name = ''
  form.value.phone = ''
  form.value.email = ''
  form.value.avatar = ''
  form.value.tenants = []
  form.value.landlordIsOccupant = false
}

function copyText(txt: string) {
  navigator.clipboard.writeText(txt)
  showToast('Password copied to clipboard', 'success')
}

onMounted(() => {
  loadEstates()
})
</script>

<style scoped>
.resident-create-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
  padding-bottom: 90px;
  max-width: 1280px;
  margin: 0 auto;
  width: 100%;
}

/* Page Header */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  background: #ffffff;
  border-radius: 20px;
  padding: 22px 28px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.header-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #F3F4F6;
  border: none;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  width: fit-content;
  transition: all 0.15s ease;
}

.btn-back:hover {
  background: #E5E7EB;
  color: #111827;
}

.page-eyebrow {
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.14em;
  color: #0A5C38;
  text-transform: uppercase;
  margin-bottom: 4px;
}

.page-title {
  margin: 0;
  font-size: 26px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.02em;
}

.page-subtitle {
  margin: 4px 0 0;
  font-size: 14px;
  color: #6B7280;
  max-width: 680px;
  line-height: 1.45;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-ghost {
  background: transparent;
  border: 1px solid #D1D5DB;
  border-radius: 10px;
  padding: 10px 18px;
  font-size: 13.5px;
  font-weight: 600;
  color: #4B5563;
  cursor: pointer;
}

.btn-ghost:hover {
  background: #F9FAFB;
}

.btn-save-header {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
}

/* Alert */
.alert-error {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #FEF2F2;
  border: 1px solid #FCA5A5;
  color: #B91C1C;
  padding: 12px 16px;
  border-radius: 12px;
  font-size: 13.5px;
  font-weight: 500;
}

/* 2-Column Grid */
.page-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  align-items: start;
}

.form-card {
  background: #ffffff;
  border-radius: 20px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.card-head {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 20px 24px;
  border-bottom: 1px solid #F3F4F6;
  background: #FAFAFA;
}

.card-icon-wrap {
  width: 42px;
  height: 42px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.bg-emerald {
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
}

.bg-blue {
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
}

.card-title {
  margin: 0;
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.card-desc {
  margin: 2px 0 0;
  font-size: 12.5px;
  color: #6B7280;
}

.card-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

/* Photo Upload Box */
.photo-upload-box {
  display: flex;
  align-items: center;
  gap: 16px;
  background: #F9FAFB;
  border: 1px dashed #D1D5DB;
  border-radius: 16px;
  padding: 16px 20px;
}

.photo-avatar-preview {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  background: #E5E7EB;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  border: 2px solid #ffffff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  flex-shrink: 0;
}

.avatar-placeholder {
  display: flex;
  align-items: center;
  justify-content: center;
}

.camera-badge {
  position: absolute;
  bottom: -4px;
  right: -4px;
  background: #0A5C38;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 2px solid #ffffff;
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

.btn-photo-action {
  background: #ffffff;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  padding: 6px 14px;
  font-size: 12.5px;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-photo-action:hover {
  border-color: #0A5C38;
  color: #0A5C38;
}

.btn-photo-remove {
  background: transparent;
  border: none;
  font-size: 12px;
  font-weight: 600;
  color: #DC2626;
  cursor: pointer;
  padding: 4px 8px;
}

.photo-tip {
  font-size: 11.5px;
  color: #6B7280;
}

/* Form inputs */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.form-label-xs {
  font-size: 11.5px;
  font-weight: 600;
  color: #4B5563;
  margin-bottom: 2px;
}

.req {
  color: #DC2626;
}

.form-input, .form-select {
  border: 1px solid #D1D5DB;
  border-radius: 10px;
  padding: 10px 14px;
  font-size: 14px;
  color: #111827;
  outline: none;
  background: #ffffff;
  transition: all 0.15s ease;
}

.form-input:focus, .form-select:focus {
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.12);
}

.form-input-sm {
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  padding: 7px 10px;
  font-size: 13px;
  color: #111827;
  width: 100%;
  outline: none;
}

.form-input-sm:focus {
  border-color: #0A5C38;
  box-shadow: 0 0 0 2px rgba(10, 92, 56, 0.12);
}

.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.form-grid-3 {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 10px;
}

.field-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.link-toggle {
  background: none;
  border: none;
  color: #0A5C38;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  padding: 0;
}

.link-toggle:hover {
  text-decoration: underline;
}

.manual-address-card {
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 12px;
  margin-top: 4px;
}

/* Toggle Switch */
.toggle-card {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  background: #F9FAFB;
  border-radius: 14px;
  padding: 14px 16px;
  border: 1px solid #E5E7EB;
}

.switch-control {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  flex-shrink: 0;
  margin-top: 2px;
}

.switch-control input {
  opacity: 0;
  width: 0;
  height: 0;
}

.switch-slider {
  position: absolute;
  cursor: pointer;
  inset: 0;
  background-color: #D1D5DB;
  border-radius: 24px;
  transition: 0.2s;
}

.switch-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  border-radius: 50%;
  transition: 0.2s;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.switch-control input:checked + .switch-slider {
  background-color: #0A5C38;
}

.switch-control input:checked + .switch-slider:before {
  transform: translateX(20px);
}

.toggle-copy {
  display: flex;
  flex-direction: column;
}

.toggle-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #111827;
}

.toggle-desc {
  margin: 2px 0 0;
  font-size: 12px;
  color: #6B7280;
  line-height: 1.4;
}

/* Empty Occupants */
.empty-occupants-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 40px 24px;
  background: #FAFAFA;
  border: 1.5px dashed #E5E7EB;
  border-radius: 16px;
}

.empty-icon-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: #F3F4F6;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
}

.empty-title {
  margin: 0;
  font-size: 15px;
  font-weight: 700;
  color: #111827;
}

.empty-desc {
  margin: 6px 0 16px;
  font-size: 13px;
  color: #6B7280;
  max-width: 380px;
  line-height: 1.45;
}

/* Occupant Stack & Item */
.occupants-stack {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.occupant-item {
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  padding: 14px 16px;
  background: #FAFAFA;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.occupant-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.occupant-badge {
  font-size: 11px;
  font-weight: 700;
  color: #1E40AF;
  background: #DBEAFE;
  padding: 3px 8px;
  border-radius: 6px;
}

.btn-del-occupant {
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  border-radius: var(--btn-radius-sm, 8px);
  transition: all 0.15s ease;
}

.btn-del-occupant:hover {
  background: #FEE2E2;
}

.occupant-body {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.occupant-photo-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 4px;
}

.occupant-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #E5E7EB;
  background-size: cover;
  background-position: center;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #D1D5DB;
  flex-shrink: 0;
}

.occupant-photo-ctrls {
  display: flex;
  align-items: center;
  gap: 8px;
}

.link-btn {
  background: none;
  border: none;
  font-size: 12px;
  font-weight: 600;
  color: #0A5C38;
  cursor: pointer;
  padding: 0;
}

.link-btn:hover {
  text-decoration: underline;
}

.link-btn.text-danger {
  color: #DC2626;
}

/* Head Between */
.head-between {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

.flex-1 {
  flex: 1;
}

.mb-0 { margin-bottom: 0; }
.mb-2 { margin-bottom: 8px; }

/* Buttons */
.btn {
  padding: 9px 18px;
  border-radius: 10px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s ease;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.btn-primary {
  background: #0A5C38;
  color: white;
}

.btn-primary:hover {
  background: #08492c;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-outline {
  background: white;
  border-color: #D1D5DB;
  color: #374151;
}

.btn-outline:hover {
  background: #F3F4F6;
}

.btn-outline-emerald {
  background: white;
  border-color: #A7F3D0;
  color: #047857;
  font-weight: 700;
}

.btn-outline-emerald:hover {
  background: #ECFDF5;
  border-color: #047857;
}

.btn-sm {
  padding: 6px 12px;
  font-size: 12px;
}

/* Bottom Action Bar */
.bottom-action-bar {
  position: fixed;
  bottom: 0;
  left: 252px;
  right: 0;
  background: #ffffff;
  border-top: 1px solid #E5E7EB;
  padding: 16px 36px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.06);
  z-index: 100;
}

@media (max-width: 900px) {
  .bottom-action-bar {
    left: 0;
    padding: 14px 20px;
  }
}

.footer-hint {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  color: #374151;
  font-weight: 500;
}

.footer-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-save {
  padding: 11px 26px;
  font-size: 14px;
  font-weight: 700;
  box-shadow: 0 2px 6px rgba(10, 92, 56, 0.25);
}

/* Credentials Modal */
.creds-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(10, 17, 13, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.creds-modal {
  background: #ffffff;
  border-radius: 20px;
  padding: 28px;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.3);
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.creds-header {
  text-align: center;
}

.creds-icon {
  width: 48px;
  height: 48px;
  background: #ECFDF5;
  color: #047857;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  font-weight: 800;
  margin: 0 auto 12px;
  border: 1px solid #A7F3D0;
}

.creds-title {
  margin: 0;
  font-size: 18px;
  font-weight: 800;
  color: #111827;
}

.creds-sub {
  margin: 6px 0 0;
  font-size: 13px;
  color: #6B7280;
  line-height: 1.45;
}

.creds-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  padding: 12px;
  max-height: 240px;
  overflow-y: auto;
}

.creds-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 10px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
}

.creds-item-info {
  display: flex;
  flex-direction: column;
}

.creds-name {
  font-size: 13.5px;
  color: #111827;
}

.creds-phone {
  font-size: 12px;
  color: #6B7280;
}

.creds-pass-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.creds-pass {
  font-family: monospace;
  font-size: 13px;
  font-weight: 700;
  background: #F3F4F6;
  padding: 3px 8px;
  border-radius: 6px;
  color: #0A5C38;
}

.btn-copy {
  background: #0A5C38;
  color: white;
  border: none;
  border-radius: 6px;
  padding: 4px 8px;
  font-size: 11px;
  font-weight: 700;
  cursor: pointer;
}

.creds-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.creds-actions .btn {
  justify-content: center;
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
  .page-grid {
    grid-template-columns: 1fr;
  }
  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }
  .header-actions {
    width: 100%;
    justify-content: space-between;
  }
  .bottom-action-bar {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }
  .footer-actions {
    justify-content: flex-end;
  }
}
</style>
