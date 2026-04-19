<template>
  <div class="mobile-page">
    <section class="hero-card hero-card--blue">
      <div>
        <div class="hero-eyebrow">Visitor passes</div>
        <h1 class="hero-title">Create, review and revoke visitor access from the phone shell.</h1>
      </div>
      <button class="btn btn-primary hero-button" @click="openCreate">New Pass</button>
    </section>

    <section class="card section-card filter-card">
      <input v-model="search" class="form-input" placeholder="Search visitor name" @input="debouncedLoad" />
      <div class="chip-row">
        <button v-for="item in statusOptions" :key="item.value" class="chip" :class="{ active: statusFilter === item.value }" @click="setStatus(item.value)">
          {{ item.label }}
        </button>
      </div>
    </section>

    <section class="card section-card">
      <div class="section-head">
        <div>
          <h2 class="section-title">Pass feed</h2>
          <p class="section-copy">Page {{ page }} of {{ totalPages }} • {{ total }} total</p>
        </div>
      </div>

      <div v-if="loading" class="loading-state"><div class="spinner"></div></div>
      <div v-else-if="passes.length === 0" class="empty-state">No passes found.</div>
      <div v-else class="stack-list">
        <article v-for="pass in passes" :key="pass.ulid" class="record-card card" :class="{ flagged: pass.itemsFlagged }">
          <div class="record-top">
            <div>
              <router-link :to="`/passes/${pass.ulid}`" class="record-title link">{{ pass.visitorName }}</router-link>
              <p class="record-meta">{{ pass.resident?.name ?? '—' }} • Unit {{ pass.resident?.unit ?? '—' }}</p>
            </div>
            <div class="record-badges">
              <span class="badge badge-gray">{{ pass.type }}</span>
              <span :class="passStatusClass(pass.status)" class="badge">{{ pass.status }}</span>
              <span v-if="pass.itemsFlagged" class="badge badge-yellow">Flagged</span>
            </div>
          </div>
          <div class="detail-grid">
            <div>
              <span class="detail-label">Phone</span>
              <strong>{{ pass.visitorPhone ?? '—' }}</strong>
            </div>
            <div>
              <span class="detail-label">Expires</span>
              <strong>{{ fmtDate(pass.expiresAt) }}</strong>
            </div>
            <div class="detail-wide">
              <span class="detail-label">Purpose</span>
              <strong>{{ pass.purpose ?? '—' }}</strong>
            </div>
          </div>
          <div class="record-bottom">
            <router-link :to="`/passes/${pass.ulid}`" class="btn btn-sm btn-outline">View</router-link>
            <div class="row-actions">
              <button v-if="pass.status !== 'Revoked' && pass.status !== 'Expired'" class="btn btn-sm btn-danger" @click="confirmRevoke(pass)">Revoke</button>
              <button class="btn btn-sm btn-danger" @click="confirmDelete(pass)">Delete</button>
            </div>
          </div>
        </article>
      </div>

      <div v-if="totalPages > 1" class="pager-row">
        <button class="btn btn-outline" :disabled="page === 1" @click="changePage(page - 1)">Prev</button>
        <button class="btn btn-outline" :disabled="page === totalPages" @click="changePage(page + 1)">Next</button>
      </div>
    </section>

    <div v-if="modalOpen" class="sheet-overlay" @click.self="closeModal">
      <div class="sheet card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Create pass</div>
            <h3 class="sheet-title">New visitor pass</h3>
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
            <input v-model="form.visitor_phone" class="form-input" placeholder="+234..." />
          </div>
          <div class="form-group">
            <label class="form-label">Vehicle Plate</label>
            <input v-model="form.vehicle_plate" class="form-input" placeholder="ABC-123-DE" />
          </div>
          <div class="form-group">
            <label class="form-label">Pass Type</label>
            <div class="chip-row">
              <button class="chip" :class="{ active: form.type === 'one-time' }" @click="form.type = 'one-time'">One-time</button>
              <button class="chip" :class="{ active: form.type === 'recurring' }" @click="form.type = 'recurring'">Recurring</button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Resident</label>
            <select v-model="form.resident_id" class="form-select">
              <option value="">Select resident…</option>
              <option v-for="resident in residents" :key="resident.id" :value="resident.id">{{ resident.user?.name }} — Unit {{ resident.unit?.number }}</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Purpose</label>
            <textarea v-model="form.purpose" class="form-textarea" rows="2" placeholder="Reason for visit"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Expires At</label>
            <input v-model="form.expires_at" class="form-input" type="datetime-local" />
          </div>
          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="closeModal">Cancel</button>
          <button class="btn btn-primary" :disabled="saving" @click="save">{{ saving ? 'Creating…' : 'Create Pass' }}</button>
        </div>
      </div>
    </div>

    <div v-if="revokeTarget" class="sheet-overlay" @click.self="revokeTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Revoke pass</div>
            <h3 class="sheet-title">Revoke {{ revokeTarget.visitorName }}?</h3>
          </div>
          <button class="sheet-close" @click="revokeTarget = null">✕</button>
        </div>
        <p class="confirm-copy">The visitor will no longer be allowed entry with this pass.</p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="revokeTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doRevoke">{{ saving ? 'Revoking…' : 'Revoke' }}</button>
        </div>
      </div>
    </div>

    <div v-if="deleteTarget" class="sheet-overlay" @click.self="deleteTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Delete pass</div>
            <h3 class="sheet-title">Delete {{ deleteTarget.visitorName }}?</h3>
          </div>
          <button class="sheet-close" @click="deleteTarget = null">✕</button>
        </div>
        <p class="confirm-copy">This pass will be permanently removed.</p>
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

function setStatus(value: string) {
  statusFilter.value = value
  page.value = 1
  load()
}

function changePage(nextPage: number) {
  page.value = nextPage
  load()
}

async function openCreate() {
  formError.value = ''
  form.value = { visitor_name: '', visitor_phone: '', vehicle_plate: '', type: 'one-time', resident_id: '', purpose: '', expires_at: '' }
  if (residents.value.length === 0) {
    try {
      const res = await residentsApi.getAll()
      residents.value = res.data.residents
    } catch {}
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
    'badge-green': status === 'On-site',
    'badge-blue': status === 'Pending',
    'badge-gray': ['Expired', 'Exited'].includes(status),
    'badge-red': status === 'Revoked',
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
.mobile-page { display: flex; flex-direction: column; gap: 16px; }
.hero-card { display: flex; justify-content: space-between; align-items: flex-end; gap: 14px; padding: 20px; border-radius: 28px; color: white; }
.hero-card--blue { background: linear-gradient(145deg, #1a3f8f 0%, #2a68d2 58%, #77b2ff 100%); }
.hero-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: rgba(255,255,255,0.74); }
.hero-title { margin-top: 10px; font-size: 26px; line-height: 1.05; font-family: var(--font-display); }
.hero-button { background: white; color: #1a3f8f; }
.section-card { padding: 16px; }
.filter-card { display: flex; flex-direction: column; gap: 12px; }
.chip-row { display: flex; gap: 8px; overflow-x: auto; }
.chip { border: none; border-radius: 999px; padding: 10px 14px; background: #edf2ee; color: #4f5f56; white-space: nowrap; font-weight: 700; }
.chip.active { background: var(--c-primary); color: white; }
.section-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.section-title { font-size: 18px; font-weight: 700; }
.section-copy { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.loading-state, .empty-state { min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--c-muted); }
.stack-list { display: flex; flex-direction: column; gap: 10px; }
.record-card { padding: 16px; border-radius: 20px; }
.record-card.flagged { background: #fffaf0; }
.record-top, .record-bottom { display: flex; justify-content: space-between; gap: 12px; }
.record-bottom { margin-top: 14px; align-items: center; }
.record-title { font-size: 17px; font-weight: 700; color: var(--c-text); }
.record-meta { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.record-badges { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
.detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 14px; padding: 14px; background: #f3f6fd; border-radius: 18px; }
.detail-wide { grid-column: 1 / -1; }
.detail-label { display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--c-muted); margin-bottom: 4px; }
.row-actions { display: flex; gap: 8px; }
.pager-row { display: flex; justify-content: space-between; gap: 12px; margin-top: 16px; }
.link { color: var(--c-text); text-decoration: none; }
.sheet-overlay { position: fixed; inset: 0; z-index: 40; background: rgba(10,18,14,0.6); display: flex; align-items: flex-end; justify-content: center; padding: 16px; }
.sheet { width: min(var(--shell-width), 100%); border-radius: 28px; padding: 18px; }
.sheet-sm { max-width: 420px; }
.sheet-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
.sheet-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: var(--c-muted); }
.sheet-title { margin-top: 6px; font-size: 22px; font-family: var(--font-display); }
.sheet-close { width: 36px; height: 36px; border: none; border-radius: 50%; background: #edf2ee; color: var(--c-text); }
.sheet-body { display: flex; flex-direction: column; gap: 14px; margin-top: 18px; }
.sheet-actions { display: flex; gap: 10px; margin-top: 18px; }
.sheet-actions .btn { flex: 1; justify-content: center; }
.confirm-copy { margin-top: 16px; color: var(--c-muted); }
@media (max-width: 420px) {
  .hero-card, .record-top, .record-bottom { flex-direction: column; align-items: flex-start; }
  .detail-grid { grid-template-columns: 1fr; }
  .detail-wide { grid-column: auto; }
  .record-badges { justify-content: flex-start; }
}
</style>
