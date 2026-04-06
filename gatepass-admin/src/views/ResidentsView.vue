<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Residents</h1>
        <p class="page-sub">Manage residents, units and estate assignments</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ New Resident</button>
    </div>

    <!-- Filters -->
    <div class="filters card">
      <input v-model="search" class="form-input" placeholder="Search name or phone…" @input="debouncedLoad" />
      <select v-model="estateFilter" class="form-select" @change="load">
        <option value="">All Estates</option>
        <option v-for="estate in estates" :key="estate.id" :value="estate.id">{{ estate.name }}</option>
      </select>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner"></div></div>

    <div v-else class="card table-card">
      <div v-if="residents.length === 0" class="empty-state"><p>No residents found.</p></div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Phone</th>
              <th>Unit</th>
              <th>Estate</th>
              <th>Role</th>
              <th>Status</th>
              <th>Joined</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in residents" :key="r.id">
              <td class="muted">{{ r.id }}</td>
              <td class="fw">{{ r.user?.name ?? '—' }}</td>
              <td class="muted">{{ r.user?.phone ?? '—' }}</td>
              <td>{{ r.unit?.number ?? '—' }}</td>
              <td class="muted">{{ r.estate?.name ?? '—' }}</td>
              <td>
                <span :class="r.role === 'owner' ? 'badge badge-blue' : 'badge badge-gray'">{{ r.role ?? '—' }}</span>
              </td>
              <td>
                <span :class="r.isActive ? 'badge badge-green' : 'badge badge-gray'">
                  {{ r.isActive ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="muted">{{ fmtDate(r.createdAt) }}</td>
              <td>
                <div class="row-actions">
                  <button class="btn btn-sm btn-outline" @click="openEdit(r)">Edit</button>
                  <button class="btn btn-sm btn-danger" @click="confirmDelete(r)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Resident Modal -->
    <div v-if="modalOpen" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ editing ? 'Edit Resident' : 'Add Resident' }}</h3>
          <button class="modal-close" @click="closeModal">✕</button>
        </div>
        <div class="modal-body">
          <template v-if="!editing">
            <p class="section-label">Account Details</p>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input v-model="form.name" class="form-input" placeholder="e.g. Jane Smith" />
              </div>
              <div class="form-group">
                <label class="form-label">Phone *</label>
                <input v-model="form.phone" class="form-input" placeholder="+234…" />
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Email</label>
                <input v-model="form.email" class="form-input" type="email" placeholder="optional" />
              </div>
              <div class="form-group">
                <label class="form-label">Password *</label>
                <input v-model="form.password" class="form-input" type="password" placeholder="••••••" />
              </div>
            </div>
            <p class="section-label" style="margin-top: 12px;">Residency Details</p>
          </template>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Estate *</label>
              <select v-model="form.estate_id" class="form-select" @change="loadUnits">
                <option value="">Select estate…</option>
                <option v-for="e in estates" :key="e.id" :value="e.id">{{ e.name }}</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Unit *</label>
              <select v-model="form.unit_id" class="form-select" :disabled="!form.estate_id">
                <option value="">Select unit…</option>
                <option v-for="u in units" :key="u.id" :value="u.id">Unit {{ u.number }}</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Role</label>
              <select v-model="form.role" class="form-select">
                <option value="tenant">Tenant</option>
                <option value="owner">Owner</option>
              </select>
            </div>
            <div v-if="editing" class="form-group">
              <label class="form-label">Status</label>
              <select v-model="form.is_active" class="form-select">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
          </div>
          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="closeModal">Cancel</button>
          <button class="btn btn-primary" :disabled="saving" @click="save">
            {{ saving ? 'Saving…' : (editing ? 'Save Changes' : 'Add Resident') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete -->
    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal modal--sm">
        <div class="modal-header">
          <h3>Delete Resident</h3>
          <button class="modal-close" @click="deleteTarget = null">✕</button>
        </div>
        <div class="modal-body">
          <p>Delete resident <strong>{{ deleteTarget.user?.name }}</strong>? This cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doDelete">
            {{ saving ? 'Deleting…' : 'Delete' }}
          </button>
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
  name: '', phone: '', email: '', password: '',
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
  form.value = { name: '', phone: '', email: '', password: '', estate_id: '', unit_id: '', role: 'tenant', is_active: true }
  formError.value = ''
  units.value = []
  modalOpen.value = true
}

function openEdit(r: any) {
  editing.value = r
  form.value = {
    name: '', phone: '', email: '', password: '',
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
      if (!form.value.password)     { formError.value = 'Password is required.'; saving.value = false; return }
      if (!form.value.estate_id)    { formError.value = 'Please select an estate.'; saving.value = false; return }
      if (!form.value.unit_id)      { formError.value = 'Please select a unit.'; saving.value = false; return }

      const payload: Record<string, unknown> = {
        name: form.value.name,
        phone: form.value.phone,
        password: form.value.password,
        estate_id: form.value.estate_id,
        unit_id: form.value.unit_id,
        role: form.value.role,
      }
      if (form.value.email) payload.email = form.value.email
      await residentsApi.create(payload)
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
.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: 20px; gap: 16px; flex-wrap: wrap;
}
.page-title { font-size: 22px; font-weight: 700; }
.page-sub   { font-size: 13px; color: var(--c-muted); margin-top: 2px; }

.filters { display: flex; gap: 12px; margin-bottom: 16px; padding: 12px 16px; flex-wrap: wrap; }
.filters .form-input  { flex: 1; min-width: 200px; }
.filters .form-select { width: 200px; }

.card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-lg); box-shadow: var(--shadow); }
.table-card { overflow: hidden; }
.loading-state { display: flex; justify-content: center; padding: 80px; }

.fw    { font-weight: 500; }
.muted { color: var(--c-muted); }
.row-actions { display: flex; gap: 6px; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
.form-error { color: var(--c-danger); font-size: 13px; margin-top: 8px; }
.section-label { font-size: 12px; font-weight: 600; color: var(--c-muted); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 10px; }

.modal--sm { max-width: 420px; }
</style>
