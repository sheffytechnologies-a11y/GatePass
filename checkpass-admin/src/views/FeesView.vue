<template>
  <div class="mobile-page fees-page">
    <section class="hero-card hero-card--emerald">
      <div class="hero-title-group">
        <div class="hero-eyebrow">Finance</div>
        <h1 class="hero-title">Manage estate fees and billing records.</h1>
      </div>
      <button class="hero-button" @click="openCreate">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        <span>Create New Fee</span>
      </button>
    </section>

    <!-- Filter & Search Toolbar (Single Line, exactly like Residents and Properties) -->
    <div class="toolbar-card">
      <div class="search-box">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          v-model="search"
          type="search"
          placeholder="Search fee name or code..."
          class="search-input"
          @input="debouncedLoadFees"
          @keyup.enter="loadFees"
        />
      </div>

      <div class="filter-group">
        <select v-model="typeFilter" class="toolbar-select" @change="loadFees">
          <option value="">All Types</option>
          <option value="one-time">One-time</option>
          <option value="recurring">Recurring</option>
        </select>
        <select v-model="estateFilter" class="toolbar-select" @change="loadFees">
          <option value="">All Estates</option>
          <option v-for="estate in estates" :key="estate.id" :value="String(estate.id)">
            🏡 {{ estate.name }}
          </option>
        </select>
      </div>
    </div>

    <section class="fees-section">
      <div class="section-head">
        <div>
          <h2 class="section-title">Fee list</h2>
          <p class="section-copy">Latest fees across estates.</p>
        </div>
        <button class="btn btn-outline btn-sm" @click="loadFees">Refresh</button>
      </div>

      <div v-if="loading" class="empty-stack"><BrandSpinner :size="40" color="var(--c-primary)" /></div>
      <div v-else-if="fees.length === 0" class="empty-stack">No fees found.</div>
      <div v-else class="fee-stack">
        <article v-for="fee in fees" :key="fee.id" class="fee-card card">
          <div class="fee-row">
            <div>
              <div class="fee-title">{{ fee.name }}</div>
              <div class="fee-meta">{{ fee.code }} • {{ estateName(fee.estateId) }}</div>
            </div>
            <span class="badge" :class="fee.type === 'recurring' ? 'badge-blue' : 'badge-gray'">{{ fee.type }}</span>
          </div>
          <div class="fee-row fee-row--sub">
            <span>{{ formatAmount(fee.amount) }}</span>
            <span>{{ formatTime(fee.createdAt) }}</span>
          </div>
          <p v-if="fee.description" class="fee-description">{{ fee.description }}</p>
        </article>
      </div>
    </section>

    <div v-if="showCreate" class="sheet-overlay" @click.self="closeCreate">
      <div class="sheet card">
        <div class="sheet-head">
          <h3 class="sheet-title">Create fee</h3>
          <button class="sheet-close" @click="closeCreate">✕</button>
        </div>

        <div class="sheet-body">
          <div class="form-group">
            <label class="form-label">Estate</label>
            <select v-model="createForm.estate_id" class="form-input">
              <option value="">Select estate</option>
              <option v-for="estate in estates" :key="estate.id" :value="String(estate.id)">{{ estate.name }}</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Fee code</label>
            <input v-model="createForm.code" class="form-input" placeholder="e.g. JAN-2026-SERVICE" />
          </div>

          <div class="form-group">
            <label class="form-label">Fee name</label>
            <input v-model="createForm.name" class="form-input" placeholder="e.g. January service charge" />
          </div>

          <div class="double-grid">
            <div class="form-group">
              <label class="form-label">Amount</label>
              <input v-model="createForm.amount" class="form-input" type="number" min="0" step="0.01" placeholder="0.00" />
            </div>

            <div class="form-group">
              <label class="form-label">Type</label>
              <select v-model="createForm.type" class="form-input">
                <option value="one-time">One-time</option>
                <option value="recurring">Recurring</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea v-model="createForm.description" class="form-input" rows="3" placeholder="Optional details"></textarea>
          </div>

          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>

        <div class="sheet-actions">
          <button class="btn btn-outline" :disabled="saving" @click="closeCreate">Cancel</button>
          <button class="btn btn-primary" :disabled="saving" @click="createFee">{{ saving ? 'Saving…' : 'Create fee' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { estatesApi, feesApi } from '@/api'
import { useToast } from '@/composables/useToast'

type Estate = { id: number; name: string }
type Fee = {
  id: number
  estateId: number
  code: string
  name: string
  amount: number
  type: 'one-time' | 'recurring'
  description: string | null
  createdAt: string
}

const { showToast } = useToast()

const loading = ref(true)
const saving = ref(false)
const showCreate = ref(false)
const formError = ref('')

const fees = ref<Fee[]>([])
const estates = ref<Estate[]>([])

const search = ref('')
const typeFilter = ref('')
const estateFilter = ref('')

const createForm = ref({
  estate_id: '',
  code: '',
  name: '',
  amount: '',
  type: 'one-time' as 'one-time' | 'recurring',
  description: '',
})

function resetCreateForm() {
  createForm.value = {
    estate_id: '',
    code: '',
    name: '',
    amount: '',
    type: 'one-time',
    description: '',
  }
  formError.value = ''
}

function openCreate() {
  resetCreateForm()
  showCreate.value = true
}

function closeCreate() {
  showCreate.value = false
  formError.value = ''
}

function estateName(estateId: number) {
  return estates.value.find((estate) => estate.id === estateId)?.name ?? 'Unknown estate'
}

function formatAmount(amount: number) {
  return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN', maximumFractionDigits: 2 }).format(amount)
}

function formatTime(value: string) {
  return new Date(value).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

async function loadEstates() {
  try {
    const res = await estatesApi.getAll()
    estates.value = res.data.estates ?? []
  } catch {
    showToast('Failed to load estates.', 'error')
  }
}

let debounceTimer: ReturnType<typeof setTimeout> | null = null
function debouncedLoadFees() {
  if (debounceTimer) clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    loadFees()
  }, 250)
}

onUnmounted(() => {
  if (debounceTimer) clearTimeout(debounceTimer)
})

async function loadFees() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (search.value.trim()) params.search = search.value.trim()
    if (typeFilter.value) params.type = typeFilter.value
    if (estateFilter.value) params.estate_id = Number(estateFilter.value)

    const res = await feesApi.getAll(params)
    fees.value = res.data.fees ?? []
  } catch {
    showToast('Failed to load fees.', 'error')
  } finally {
    loading.value = false
  }
}

async function createFee() {
  formError.value = ''

  if (!createForm.value.estate_id) {
    formError.value = 'Estate is required.'
    return
  }
  if (!createForm.value.code.trim()) {
    formError.value = 'Fee code is required.'
    return
  }
  if (!createForm.value.name.trim()) {
    formError.value = 'Fee name is required.'
    return
  }
  if (!createForm.value.amount || Number(createForm.value.amount) < 0) {
    formError.value = 'Amount must be zero or more.'
    return
  }

  saving.value = true
  try {
    const payload: Record<string, unknown> = {
      estate_id: Number(createForm.value.estate_id),
      code: createForm.value.code.trim(),
      name: createForm.value.name.trim(),
      amount: Number(createForm.value.amount),
      type: createForm.value.type,
    }

    if (createForm.value.description.trim()) {
      payload.description = createForm.value.description.trim()
    }

    await feesApi.create(payload)
    showToast('Fee created successfully.', 'success')
    closeCreate()
    await loadFees()
  } catch (error: any) {
    formError.value = error?.response?.data?.message ?? 'Failed to create fee.'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await Promise.all([loadEstates(), loadFees()])
})
</script>

<style scoped>
.mobile-page { display: flex; flex-direction: column; gap: 16px; }

/* ── Hero Header ───────────────────────────────────── */
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
  border-radius: 12px;
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

/* ── Filter & Search Toolbar (Single Line) ─────────── */
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
  min-width: 220px;
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

.toolbar-select {
  padding: 7px 12px;
  border: 1px solid #E5E7EB;
  border-radius: 8px;
  background: #F9FAFB;
  font-size: 12.5px;
  font-weight: 500;
  color: #374151;
  outline: none;
  cursor: pointer;
  transition: all 140ms ease;
}

.toolbar-select:hover {
  border-color: #D1D5DB;
  background: #ffffff;
}

/* ── Fee List Section ──────────────────────────────── */
.fees-section { margin-top: 4px; }
.section-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 14px; }
.section-title { font-size: 18px; font-weight: 700; }
.section-copy { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.empty-stack { min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--c-muted); }

.fee-stack { display: flex; flex-direction: column; gap: 10px; }
.fee-card { padding: 16px; border-radius: 20px; }
.fee-row { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
.fee-row--sub { margin-top: 10px; color: var(--c-muted); font-size: 13px; }
.fee-title { font-size: 16px; font-weight: 700; color: var(--c-text); }
.fee-meta { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.fee-description { margin-top: 10px; color: var(--c-muted); font-size: 13px; }

.sheet-overlay { position: fixed; inset: 0; z-index: 40; background: rgba(10,18,14,0.5); display: flex; align-items: flex-end; justify-content: center; padding: 16px; }
.sheet { width: min(var(--shell-width), 100%); border-radius: 28px; padding: 20px; }
.sheet-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.sheet-title { font-size: 18px; font-weight: 700; }
.sheet-close { width: 32px; height: 32px; border: none; border-radius: 50%; background: #edf2ee; color: var(--c-text); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.sheet-body { display: flex; flex-direction: column; gap: 12px; }
.double-grid { display: grid; gap: 10px; grid-template-columns: 1fr 1fr; }
.sheet-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 16px; }

@media (max-width: 768px) {
  .hero-card {
    flex-direction: column;
    align-items: flex-start;
    padding: 20px;
  }
  .hero-button {
    width: 100%;
    justify-content: center;
  }
  .toolbar-card {
    flex-direction: column;
    align-items: stretch;
  }
  .filter-group {
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
  }
  .toolbar-select {
    width: 100%;
  }
}

@media (max-width: 520px) {
  .filter-group { grid-template-columns: 1fr; }
  .double-grid { grid-template-columns: 1fr; }
}
</style>
