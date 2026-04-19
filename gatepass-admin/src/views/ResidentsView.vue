<template>
  <div class="mobile-page">
    <section class="hero-card hero-card--sand">
      <div>
        <div class="hero-eyebrow">Resident registry</div>
        <h1 class="hero-title">Assign residents to estates and units from a mobile-first flow.</h1>
      </div>
      <button class="btn btn-primary hero-button" @click="openCreate">New Resident</button>
    </section>

    <section class="card section-card filter-card">
      <input v-model="search" class="form-input" placeholder="Search by name or phone" @input="debouncedLoad" />
      <select v-model="estateFilter" class="form-select" @change="load">
        <option value="">All Estates</option>
        <option v-for="estate in estates" :key="estate.id" :value="estate.id">{{ estate.name }}</option>
      </select>
    </section>

    <section class="card section-card">
      <div class="section-head">
        <div>
          <h2 class="section-title">Residents</h2>
          <p class="section-copy">{{ residents.length }} resident{{ residents.length === 1 ? '' : 's' }} loaded.</p>
        </div>
      </div>

      <div v-if="loading" class="loading-state"><div class="spinner"></div></div>
      <div v-else-if="residents.length === 0" class="empty-state">No residents found.</div>
      <div v-else class="stack-list">
        <article v-for="resident in residents" :key="resident.id" class="record-card card">
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
              <strong>{{ resident.unit?.number ?? '—' }}</strong>
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
      <!-- Full-screen form for create -->
      <div v-if="!editing" class="form-overlay" @click.self="closeModal">
        <div class="form-page">
          <div class="form-nav">
            <button class="nav-back" @click="closeModal">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="#1A1A1A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <span class="nav-title">Add Resident</span>
            <button class="nav-close" @click="closeModal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#7B7E87" stroke-width="1.5"/><path d="M9 9l6 6M15 9l-6 6" stroke="#7B7E87" stroke-width="1.5" stroke-linecap="round"/></svg>
              Close
            </button>
          </div>
          <div class="form-scroll">
            <h3 class="form-section-heading">Residency Details</h3>
            <div class="form-group">
              <label class="form-label">Owner's Full Name</label>
              <input v-model="form.name" class="form-input" placeholder="Enter Full Name" />
            </div>
            <div class="form-group">
              <label class="form-label">Owner's Phone No.</label>
              <input v-model="form.phone" class="form-input" type="tel" placeholder="Enter Phone No" />
            </div>
            <div class="form-group">
              <label class="form-label">Owner's Email Address</label>
              <input v-model="form.email" class="form-input" type="email" placeholder="Enter Email Address" />
            </div>
            <div class="address-row">
              <div class="form-group">
                <label class="form-label">Lane</label>
                <input v-model="form.lane" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">House</label>
                <input v-model="form.house" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Flat</label>
                <input v-model="form.flat" class="form-input" />
              </div>
            </div>
            <div class="toggle-row">
              <label class="toggle-switch">
                <input type="checkbox" v-model="form.landlordIsOccupant" />
                <span class="toggle-track"><span class="toggle-thumb"></span></span>
              </label>
              <span class="toggle-label">Landlord is occupant</span>
            </div>
            <div class="occupants-head">
              <h3 class="form-section-heading">Occupants</h3>
              <button type="button" class="btn btn-sm btn-primary btn-add-tenant" @click="addTenant">
                <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7.25" stroke="white" stroke-width="1.5"/><path d="M8 5v6M5 8h6" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>
                Add Tenant
              </button>
            </div>
            <div v-for="(tenant, i) in form.tenants" :key="i" class="tenant-block">
              <div class="form-group">
                <div class="label-row">
                  <label class="form-label">Full Name</label>
                  <button type="button" class="tenant-del" @click="removeTenant(i)">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6" stroke="#BBBBBB" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  </button>
                </div>
                <input v-model="tenant.name" class="form-input" placeholder="Enter Full Name" />
              </div>
              <div class="form-group">
                <label class="form-label">Phone No.</label>
                <input v-model="tenant.phone" class="form-input" type="tel" placeholder="Enter Phone No" />
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <input v-model="tenant.email" class="form-input" type="email" placeholder="Enter Email Address" />
              </div>
            </div>
            <p v-if="formError" class="form-error">{{ formError }}</p>
          </div>
          <div class="form-footer">
            <button class="btn btn-primary btn-block" :disabled="saving" @click="save">{{ saving ? 'Saving…' : 'Save' }}</button>
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
            <label class="form-label">Unit</label>
            <select v-model="form.unit_id" class="form-select" :disabled="!form.estate_id">
              <option value="">Select unit…</option>
              <option v-for="unit in units" :key="unit.id" :value="unit.id">Unit {{ unit.number }}</option>
            </select>
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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { residentsApi, estatesApi } from '@/api/index'
import { useToast } from '@/composables/useToast'

const { showToast } = useToast()

const loading = ref(true)
const residents = ref<any[]>([])
const estates = ref<any[]>([])
const units = ref<any[]>([])
const search = ref('')
const estateFilter = ref('')

const modalOpen = ref(false)
const editing = ref<any>(null)
const saving = ref(false)
const formError = ref('')
const deleteTarget = ref<any>(null)

const form = ref({
  name: '', phone: '', email: '',
  lane: '', house: '', flat: '',
  landlordIsOccupant: false,
  tenants: [] as { name: string; phone: string; email: string }[],
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
  editing.value = null
  form.value = {
    name: '', phone: '', email: '',
    lane: '', house: '', flat: '', landlordIsOccupant: false, tenants: [],
    estate_id: '', unit_id: '', role: 'tenant', is_active: true,
  }
  formError.value = ''
  units.value = []
  modalOpen.value = true
}

function openEdit(r: any) {
  editing.value = r
  form.value = {
    name: '', phone: '', email: '',
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
      await residentsApi.create(ownerPayload)

      for (const t of form.value.tenants) {
        if (!t.name.trim() || !t.phone.trim()) continue
        const tp: Record<string, unknown> = {
          name: t.name, phone: t.phone, role: 'tenant',
          lane: form.value.lane, house: form.value.house, flat: form.value.flat,
        }
        if (t.email) tp.email = t.email
        await residentsApi.create(tp)
      }
      showToast('Resident created.', 'success')
    }
    closeModal()
    load()
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? 'Something went wrong.'
  } finally {
    saving.value = false
  }
}

function addTenant() { form.value.tenants.push({ name: '', phone: '', email: '' }) }
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
.hero-card { display: flex; justify-content: space-between; align-items: flex-end; gap: 14px; padding: 20px; border-radius: 28px; color: white; }
.hero-card--sand { background: linear-gradient(145deg, #5c3b12 0%, #9e6523 55%, #dca966 100%); }
.hero-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: rgba(255,255,255,0.74); }
.hero-title { margin-top: 10px; font-size: 26px; line-height: 1.05; font-family: var(--font-display); }
.hero-button { background: white; color: #7d4b11; }
.section-card { padding: 16px; }
.filter-card { display: flex; flex-direction: column; gap: 12px; }
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
.chip { border: none; border-radius: 999px; padding: 10px 14px; background: #edf2ee; color: #4f5f56; white-space: nowrap; font-weight: 700; }
.chip.active { background: var(--c-primary); color: white; }
/* Edit/delete sheets */
.sheet-overlay { position: fixed; inset: 0; z-index: 40; background: rgba(10,18,14,0.5); display: flex; align-items: flex-end; justify-content: center; padding: 16px; }
.sheet { width: min(var(--shell-width), 100%); border-radius: 28px; padding: 18px; }
.sheet-sm { max-width: 420px; }
.sheet-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.sheet-title { font-size: 18px; font-weight: 700; }
.sheet-close { width: 32px; height: 32px; border: none; border-radius: 50%; background: #edf2ee; color: var(--c-text); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.sheet-body { display: flex; flex-direction: column; gap: 14px; }
.sheet-actions { display: flex; gap: 10px; margin-top: 18px; }
.sheet-actions .btn { flex: 1; justify-content: center; }
.confirm-copy { color: var(--c-muted); font-size: 14px; margin-bottom: 4px; }
/* Full-screen add form */
.form-overlay { position: fixed; inset: 0; background: #fff; z-index: 200; display: flex; justify-content: center; }
.form-page { width: 100%; max-width: var(--shell-width); height: 100%; display: flex; flex-direction: column; overflow: hidden; }
.form-nav { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid #F0F0F0; flex-shrink: 0; }
.nav-back { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; background: none; border: none; cursor: pointer; }
.nav-title { font-size: 16px; font-weight: 700; color: #1A1A1A; }
.nav-close { display: flex; align-items: center; gap: 5px; font-size: 13px; color: #7B7E87; background: none; border: none; cursor: pointer; }
.form-scroll { flex: 1; overflow-y: auto; padding: 20px 16px; display: flex; flex-direction: column; gap: 16px; }
.form-section-heading { font-size: 18px; font-weight: 700; color: #1A1A1A; }
.address-row { display: grid; grid-template-columns: 1fr 1.4fr 1fr; gap: 8px; }
.toggle-row { display: flex; align-items: center; gap: 12px; }
.toggle-switch { position: relative; display: inline-flex; cursor: pointer; }
.toggle-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
.toggle-track { width: 44px; height: 26px; background: #E0E0E0; border-radius: 13px; transition: background 0.2s; position: relative; display: block; }
.toggle-switch input:checked + .toggle-track { background: var(--c-primary); }
.toggle-thumb { position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: white; transition: left 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.toggle-switch input:checked + .toggle-track .toggle-thumb { left: 21px; }
.toggle-label { font-size: 14px; color: #1A1A1A; }
.occupants-head { display: flex; align-items: center; justify-content: space-between; }
.btn-add-tenant { display: flex; align-items: center; gap: 6px; border-radius: 999px; padding: 8px 14px; }
.tenant-block { display: flex; flex-direction: column; gap: 12px; padding-top: 12px; border-top: 1px solid #F0F0F0; }
.label-row { display: flex; align-items: center; justify-content: space-between; }
.tenant-del { background: none; border: none; padding: 0; cursor: pointer; display: flex; }
.form-footer { padding: 14px 16px; border-top: 1px solid #F0F0F0; flex-shrink: 0; }
.btn-block { width: 100%; justify-content: center; border-radius: 14px; padding: 15px; font-size: 16px; }
@media (max-width: 420px) {
  .record-top, .record-bottom { flex-direction: column; align-items: flex-start; }
  .detail-grid { grid-template-columns: 1fr; }
  .record-badges { justify-content: flex-start; }
}
</style>
