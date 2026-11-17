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

    <!-- Sheet for edit -->
    <div v-if="modalOpen && editing" class="sheet-overlay" @click.self="closeModal">
      <div class="sheet card">
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
import { useRouter } from 'vue-router'
import { residentsApi, estatesApi, unitsApi } from '@/api/index'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const { showToast } = useToast()
const auth = useAuthStore()

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

function openCreate() {
  const currentEstateId = estateFilter.value || auth.activeEstateId || (estates.value.length ? estates.value[0].id : '')
  if (currentEstateId) {
    router.push(`/residents/new?estate_id=${currentEstateId}`)
  } else {
    router.push('/residents/new')
  }
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
      showToast('Resident updated successfully.', 'success')
    } else {
      if (!form.value.name.trim()) {
        formError.value = 'Owner name is required.'
        showToast(formError.value, 'error')
        saving.value = false
        return
      }
      if (!form.value.phone.trim()) {
        formError.value = 'Phone number is required.'
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
        credentials.push({ name: form.value.name, phone: form.value.phone, password: ownerRes.data.temporaryPassword })
      }

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
          credentials.push({ name: t.name, phone: t.phone, password: tenantRes.data.temporaryPassword })
        }
      }

      showToast('Resident registered successfully!', 'success')
      closeModal()
      if (credentials.length > 0) {
        newCredentials.value = credentials
      }
      await load()
      return
    }
    closeModal()
    await load()
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? 'Something went wrong.'
    showToast(formError.value, 'error')
  } finally {
    saving.value = false
  }
}

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
