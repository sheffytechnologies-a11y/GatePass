<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Passes</h1>
        <p class="page-sub">All visitor passes across the estate</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ New Pass</button>
    </div>

    <!-- Filters -->
    <div class="filters card">
      <input v-model="search" class="form-input" placeholder="Search visitor name…" @input="debouncedLoad" />
      <select v-model="statusFilter" class="form-select" @change="load">
        <option value="">All Statuses</option>
        <option value="Pending">Pending</option>
        <option value="On-site">On-site</option>
        <option value="Exited">Exited</option>
        <option value="Expired">Expired</option>
        <option value="Revoked">Revoked</option>
      </select>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner"></div></div>

    <div v-else class="card table-card">
      <div v-if="passes.length === 0" class="empty-state"><p>No passes found.</p></div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Visitor</th>
              <th>Phone</th>
              <th>Resident</th>
              <th>Unit</th>
              <th>Purpose</th>
              <th>Type</th>
              <th>Status</th>
              <th>Expires</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="pass in passes" :key="pass.ulid" :class="{ 'row-flagged': pass.itemsFlagged }">
              <td>
                <router-link :to="`/passes/${pass.ulid}`" class="link fw">{{ pass.visitorName }}</router-link>
                <span v-if="pass.itemsFlagged" class="badge badge-yellow flag-badge">🚩 Flagged</span>
              </td>
              <td class="muted">{{ pass.visitorPhone ?? '—' }}</td>
              <td>{{ pass.resident?.name ?? '—' }}</td>
              <td class="muted">{{ pass.resident?.unit ?? '—' }}</td>
              <td class="muted truncate">{{ pass.purpose ?? '—' }}</td>
              <td><span class="badge badge-gray">{{ pass.type }}</span></td>
              <td><span :class="passStatusClass(pass.status)" class="badge">{{ pass.status }}</span></td>
              <td class="muted">{{ fmtDate(pass.expiresAt) }}</td>
              <td>
                <div class="row-actions">
                  <router-link :to="`/passes/${pass.ulid}`" class="btn btn-sm btn-outline">View</router-link>
                  <button
                    v-if="pass.status !== 'Revoked' && pass.status !== 'Expired'"
                    class="btn btn-sm btn-danger"
                    @click="confirmRevoke(pass)"
                  >Revoke</button>
                  <button class="btn btn-sm btn-danger" @click="confirmDelete(pass)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="pagination">
        <button class="btn btn-sm btn-outline" :disabled="page === 1" @click="changePage(page - 1)">← Prev</button>
        <span class="page-info">Page {{ page }} of {{ totalPages }} ({{ total }} total)</span>
        <button class="btn btn-sm btn-outline" :disabled="page === totalPages" @click="changePage(page + 1)">Next →</button>
      </div>
    </div>

    <!-- Create Pass Modal -->
    <div v-if="modalOpen" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3>Create Pass</h3>
          <button class="modal-close" @click="closeModal">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Visitor Name *</label>
              <input v-model="form.visitor_name" class="form-input" placeholder="e.g. John Doe" />
            </div>
            <div class="form-group">
              <label class="form-label">Visitor Phone</label>
              <input v-model="form.visitor_phone" class="form-input" placeholder="+234…" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Vehicle Plate</label>
              <input v-model="form.vehicle_plate" class="form-input" placeholder="e.g. ABC-123-DE" />
            </div>
            <div class="form-group">
              <label class="form-label">Type *</label>
              <select v-model="form.type" class="form-select">
                <option value="one-time">One-time</option>
                <option value="recurring">Recurring</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Resident *</label>
            <select v-model="form.resident_id" class="form-select">
              <option value="">Select resident…</option>
              <option v-for="r in residents" :key="r.id" :value="r.id">
                {{ r.user?.name }} — Unit {{ r.unit?.number }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Purpose</label>
            <textarea v-model="form.purpose" class="form-textarea" rows="2" placeholder="Reason for visit…"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Expires At</label>
            <input v-model="form.expires_at" class="form-input" type="datetime-local" />
          </div>
          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="closeModal">Cancel</button>
          <button class="btn btn-primary" :disabled="saving" @click="save">
            {{ saving ? 'Creating…' : 'Create Pass' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Revoke -->
    <div v-if="revokeTarget" class="modal-overlay" @click.self="revokeTarget = null">
      <div class="modal modal--sm">
        <div class="modal-header">
          <h3>Revoke Pass</h3>
          <button class="modal-close" @click="revokeTarget = null">✕</button>
        </div>
        <div class="modal-body">
          <p>Revoke pass for <strong>{{ revokeTarget.visitorName }}</strong>? They will no longer be allowed entry.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="revokeTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doRevoke">
            {{ saving ? 'Revoking…' : 'Revoke' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete -->
    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal modal--sm">
        <div class="modal-header">
          <h3>Delete Pass</h3>
          <button class="modal-close" @click="deleteTarget = null">✕</button>
        </div>
        <div class="modal-body">
          <p>Permanently delete the pass for <strong>{{ deleteTarget.visitorName }}</strong>? This cannot be undone.</p>
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
import { passesApi, residentsApi } from '@/api/index'
import { useToast } from '@/composables/useToast'

const { showToast } = useToast()

const loading = ref(true)
const passes = ref<any[]>([])
const search = ref('')
const statusFilter = ref('')
const page = ref(1)
const total = ref(0)
const totalPages = ref(1)

const modalOpen = ref(false)
const saving = ref(false)
const formError = ref('')
const revokeTarget = ref<any>(null)
const deleteTarget = ref<any>(null)
const residents = ref<any[]>([])

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
  searchTimer = setTimeout(load, 350)
}

async function load() {
  loading.value = true
  try {
    const params: Record<string, unknown> = { page: page.value }
    if (search.value) params.search = search.value
    if (statusFilter.value) params.status = statusFilter.value
    const res = await passesApi.getAll(params)
    passes.value = res.data.passes
    total.value = res.data.total
    totalPages.value = res.data.pages
  } catch {
    showToast('Failed to load passes.', 'error')
  } finally {
    loading.value = false
  }
}

function changePage(p: number) {
  page.value = p
  load()
}

async function openCreate() {
  formError.value = ''
  form.value = { visitor_name: '', visitor_phone: '', vehicle_plate: '', type: 'one-time', resident_id: '', purpose: '', expires_at: '' }
  // Load residents list
  if (residents.value.length === 0) {
    try {
      const res = await residentsApi.getAll()
      residents.value = res.data.residents
    } catch {/* ignore */}
  }
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
}

async function save() {
  formError.value = ''
  if (!form.value.visitor_name.trim()) { formError.value = 'Visitor name is required.'; return }
  if (!form.value.resident_id) { formError.value = 'Please select a resident.'; return }

  saving.value = true
  try {
    const payload: Record<string, unknown> = {
      visitor_name: form.value.visitor_name,
      resident_id: form.value.resident_id,
      type: form.value.type,
    }
    if (form.value.visitor_phone) payload.visitor_phone = form.value.visitor_phone
    if (form.value.vehicle_plate) payload.vehicle_plate = form.value.vehicle_plate
    if (form.value.purpose) payload.purpose = form.value.purpose
    if (form.value.expires_at) payload.expires_at = form.value.expires_at

    await passesApi.create(payload)
    showToast('Pass created.', 'success')
    closeModal()
    load()
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? 'Something went wrong.'
  } finally {
    saving.value = false
  }
}

function confirmRevoke(pass: any) { revokeTarget.value = pass }
function confirmDelete(pass: any) { deleteTarget.value = pass }

async function doRevoke() {
  if (!revokeTarget.value) return
  saving.value = true
  try {
    await passesApi.revoke(revokeTarget.value.ulid)
    showToast('Pass revoked.', 'success')
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

function passStatusClass(status: string) {
  return {
    'badge-green':  status === 'On-site',
    'badge-blue':   status === 'Pending',
    'badge-gray':   ['Expired', 'Exited'].includes(status),
    'badge-red':    status === 'Revoked',
    'badge-yellow': status === 'Item Flagged',
  }
}

function fmtDate(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(load)
</script>

<style scoped>
.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: 20px; gap: 16px; flex-wrap: wrap;
}
.page-title { font-size: 22px; font-weight: 700; }
.page-sub   { font-size: 13px; color: var(--c-muted); margin-top: 2px; }

.filters {
  display: flex; gap: 12px; margin-bottom: 16px; padding: 12px 16px; flex-wrap: wrap;
}
.filters .form-input  { flex: 1; min-width: 200px; }
.filters .form-select { width: 180px; }

.card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-lg); box-shadow: var(--shadow); }
.table-card { overflow: hidden; }
.loading-state { display: flex; justify-content: center; padding: 80px; }

.fw   { font-weight: 500; }
.muted{ color: var(--c-muted); }
.truncate { max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.row-flagged { background: #FFFBEB; }
.flag-badge  { margin-left: 6px; font-size: 11px; }
.row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
.link { color: var(--c-primary); font-weight: 500; }
.link:hover { text-decoration: underline; }

.pagination {
  display: flex; align-items: center; justify-content: center; gap: 16px;
  padding: 16px; border-top: 1px solid var(--c-border);
}
.page-info { font-size: 13px; color: var(--c-muted); }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; }
.form-error { color: var(--c-danger); font-size: 13px; margin-top: 8px; }

.modal--sm { max-width: 420px; }
</style>
