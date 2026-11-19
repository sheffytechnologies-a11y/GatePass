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

    <!-- Filter & Search Toolbar (Single Line Pattern) -->
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
        <select v-model="targetFilter" class="toolbar-select" @change="loadFees">
          <option value="">All Payers</option>
          <option value="both">Both (All)</option>
          <option value="occupant">Occupants Only</option>
          <option value="homeowner">Homeowners Only</option>
        </select>
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

    <!-- Fee List Section -->
    <section class="fees-section">
      <div class="section-head">
        <div>
          <h2 class="section-title">Fee list</h2>
          <p class="section-copy">Latest configured dues and billing schedules across estates.</p>
        </div>
        <button class="btn btn-outline btn-sm" @click="loadFees">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
          </svg>
          <span>Refresh</span>
        </button>
      </div>

      <div v-if="loading" class="empty-stack">
        <BrandSpinner :size="40" color="var(--c-primary)" />
      </div>

      <div v-else-if="filteredFees.length === 0" class="empty-card card">
        <div class="empty-icon">💳</div>
        <div class="empty-title">No fees found</div>
        <p class="empty-desc">No dues match the search query or active filter settings.</p>
        <button v-if="search || targetFilter || typeFilter || estateFilter" class="btn btn-outline btn-sm mt-3" @click="resetFilters">
          Reset Filters
        </button>
      </div>

      <div v-else class="fee-stack">
        <article v-for="fee in filteredFees" :key="fee.id" class="fee-card card">
          <div class="fee-row">
            <div class="fee-main-info">
              <div class="fee-title-row">
                <span class="fee-title">{{ fee.name }}</span>
                <span class="audience-badge" :class="audienceBadgeClass(fee.targetAudience)">
                  {{ formatAudience(fee.targetAudience) }}
                </span>
              </div>
              <div class="fee-meta">
                <span class="fee-code-pill">{{ fee.code }}</span>
                <span>&bull;</span>
                <span class="estate-name">🏡 {{ estateName(fee.estateId) }}</span>
              </div>
            </div>

            <div class="fee-amount-box">
              <span class="fee-amount">{{ formatAmount(fee.amount) }}</span>
              <span class="badge" :class="fee.type === 'recurring' ? 'badge-blue' : 'badge-gray'">
                {{ fee.type === 'recurring' ? `🔁 ${capitalize(fee.frequency || 'Monthly')} Recurring` : '⚡ One-Time' }}
              </span>
            </div>
          </div>

          <div class="fee-details-strip">
            <div v-if="fee.effectiveDate" class="detail-pill">
              <span class="pill-label">Takes Effect:</span>
              <span class="pill-value">📅 {{ formatDate(fee.effectiveDate) }}</span>
            </div>
            <div class="detail-pill">
              <span class="pill-label">Created:</span>
              <span class="pill-value">{{ formatTime(fee.createdAt) }}</span>
            </div>
            <div v-if="fee.type === 'recurring'" class="detail-pill">
              <span class="pill-label">Billing Cycle:</span>
              <span class="pill-value">Every {{ capitalize(fee.frequency || 'month') }}</span>
            </div>
          </div>

          <p v-if="fee.description" class="fee-description">{{ fee.description }}</p>
        </article>
      </div>
    </section>

    <!-- Create Fee Modal -->
    <div v-if="showCreate" class="sheet-overlay" @click.self="closeCreate">
      <div class="sheet card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">BILLING CONFIGURATION</div>
            <h3 class="sheet-title">Create Estate Fee / Due</h3>
          </div>
          <button class="sheet-close" @click="closeCreate">✕</button>
        </div>

        <div class="sheet-body">
          <!-- Estate Selection -->
          <div class="form-group">
            <label class="form-label">Estate <span class="text-danger">*</span></label>
            <select v-model="createForm.estate_id" class="form-input">
              <option value="">Select estate</option>
              <option v-for="estate in estates" :key="estate.id" :value="String(estate.id)">{{ estate.name }}</option>
            </select>
          </div>

          <!-- Target Payer / Audience (Occupant, Homeowner, Both) -->
          <div class="form-group">
            <label class="form-label">
              <span>Target Payer / Audience</span>
              <span class="text-danger">*</span>
            </label>
            <div class="audience-options-grid">
              <button
                type="button"
                class="audience-card-btn"
                :class="{ active: createForm.target_audience === 'both' }"
                @click="createForm.target_audience = 'both'"
              >
                <div class="opt-head">
                  <span class="opt-icon">👥</span>
                  <span class="opt-title">Both (All)</span>
                </div>
                <span class="opt-desc">All occupants, residents and homeowners</span>
              </button>

              <button
                type="button"
                class="audience-card-btn"
                :class="{ active: createForm.target_audience === 'occupant' }"
                @click="createForm.target_audience = 'occupant'"
              >
                <div class="opt-head">
                  <span class="opt-icon">👤</span>
                  <span class="opt-title">Occupants</span>
                </div>
                <span class="opt-desc">Residents & tenants living in estate units</span>
              </button>

              <button
                type="button"
                class="audience-card-btn"
                :class="{ active: createForm.target_audience === 'homeowner' }"
                @click="createForm.target_audience = 'homeowner'"
              >
                <div class="opt-head">
                  <span class="opt-icon">🏡</span>
                  <span class="opt-title">Homeowners</span>
                </div>
                <span class="opt-desc">Property owners & landlords only</span>
              </button>
            </div>
          </div>

          <!-- Fee Code and Name -->
          <div class="double-grid">
            <div class="form-group">
              <label class="form-label">Fee Code <span class="text-danger">*</span></label>
              <input v-model="createForm.code" class="form-input" placeholder="e.g. JAN-2026-SERVICE" />
            </div>

            <div class="form-group">
              <label class="form-label">Fee Name <span class="text-danger">*</span></label>
              <input v-model="createForm.name" class="form-input" placeholder="e.g. January Service Charge" />
            </div>
          </div>

          <!-- Amount and Type -->
          <div class="double-grid">
            <div class="form-group">
              <label class="form-label">Amount (₦) <span class="text-danger">*</span></label>
              <input v-model="createForm.amount" class="form-input" type="number" min="0" step="0.01" placeholder="0.00" />
            </div>

            <div class="form-group">
              <label class="form-label">Payment Type <span class="text-danger">*</span></label>
              <select v-model="createForm.type" class="form-input">
                <option value="one-time">⚡ One-time</option>
                <option value="recurring">🔁 Recurring</option>
              </select>
            </div>
          </div>

          <!-- Recurring Frequency (Daily, Weekly, Monthly, Yearly) -->
          <div v-if="createForm.type === 'recurring'" class="recurring-box">
            <label class="form-label">
              <span>Recurrence Frequency</span>
              <span class="text-danger">*</span>
            </label>
            <div class="freq-chips-row">
              <button
                type="button"
                class="freq-chip-btn"
                :class="{ active: createForm.frequency === 'daily' }"
                @click="createForm.frequency = 'daily'"
              >
                Daily
              </button>
              <button
                type="button"
                class="freq-chip-btn"
                :class="{ active: createForm.frequency === 'weekly' }"
                @click="createForm.frequency = 'weekly'"
              >
                Weekly
              </button>
              <button
                type="button"
                class="freq-chip-btn"
                :class="{ active: createForm.frequency === 'monthly' }"
                @click="createForm.frequency = 'monthly'"
              >
                Monthly
              </button>
              <button
                type="button"
                class="freq-chip-btn"
                :class="{ active: createForm.frequency === 'yearly' }"
                @click="createForm.frequency = 'yearly'"
              >
                Yearly
              </button>
            </div>
            <p class="form-help-text">
              This due will automatically bill on a <strong>{{ createForm.frequency }}</strong> cycle for matching payers.
            </p>
          </div>

          <!-- Effective Date (Takes Effect on) -->
          <div class="form-group">
            <label class="form-label">
              <span>Effective Date (Takes effect on)</span>
            </label>
            <input
              v-model="createForm.effective_date"
              type="date"
              class="form-input"
            />
            <p class="form-help-text">
              The date when this due takes effect and is posted to resident accounts.
            </p>
          </div>

          <!-- Description -->
          <div class="form-group">
            <label class="form-label">Description / Purpose</label>
            <textarea v-model="createForm.description" class="form-input" rows="2" placeholder="Optional details or instructions for residents"></textarea>
          </div>

          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>

        <div class="sheet-actions">
          <button class="btn btn-outline" :disabled="saving" @click="closeCreate">Cancel</button>
          <button class="btn btn-primary" :disabled="saving" @click="createFee">
            {{ saving ? 'Saving…' : 'Create Fee' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue'
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
  targetAudience?: 'occupant' | 'homeowner' | 'both'
  frequency?: 'daily' | 'weekly' | 'monthly' | 'yearly'
  effectiveDate?: string | null
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
const targetFilter = ref('')
const typeFilter = ref('')
const estateFilter = ref('')

const createForm = ref({
  estate_id: '',
  code: '',
  name: '',
  amount: '',
  type: 'one-time' as 'one-time' | 'recurring',
  target_audience: 'both' as 'occupant' | 'homeowner' | 'both',
  frequency: 'monthly' as 'daily' | 'weekly' | 'monthly' | 'yearly',
  effective_date: '',
  description: '',
})

const filteredFees = computed(() => {
  return fees.value.filter((fee) => {
    if (targetFilter.value && (fee.targetAudience || 'both') !== targetFilter.value) {
      return false
    }
    return true
  })
})

function resetCreateForm() {
  createForm.value = {
    estate_id: estates.value.length > 0 ? String(estates.value[0].id) : '',
    code: '',
    name: '',
    amount: '',
    type: 'one-time',
    target_audience: 'both',
    frequency: 'monthly',
    effective_date: new Date().toISOString().slice(0, 10),
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

function resetFilters() {
  search.value = ''
  targetFilter.value = ''
  typeFilter.value = ''
  estateFilter.value = ''
  loadFees()
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

function formatDate(value: string) {
  return new Date(value).toLocaleDateString('en-NG', { dateStyle: 'medium' })
}

function capitalize(text?: string | null) {
  if (!text) return ''
  return text.charAt(0).toUpperCase() + text.slice(1)
}

function formatAudience(audience?: string | null) {
  if (audience === 'occupant') return '👤 Occupants Only'
  if (audience === 'homeowner') return '🏡 Homeowners Only'
  return '👥 Both (All Payers)'
}

function audienceBadgeClass(audience?: string | null) {
  if (audience === 'occupant') return 'audience--occupant'
  if (audience === 'homeowner') return 'audience--homeowner'
  return 'audience--both'
}

async function loadEstates() {
  try {
    const res = await estatesApi.getAll()
    estates.value = res.data.estates ?? []
    if (estates.value.length > 0 && !createForm.value.estate_id) {
      createForm.value.estate_id = String(estates.value[0].id)
    }
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
      target_audience: createForm.value.target_audience,
    }

    if (createForm.value.type === 'recurring') {
      payload.frequency = createForm.value.frequency
    }

    if (createForm.value.effective_date) {
      payload.effective_date = createForm.value.effective_date
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
  border-radius: var(--btn-radius-lg, 12px);
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
.section-title { font-size: 18px; font-weight: 800; color: #111827; }
.section-copy { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.empty-stack { min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--c-muted); }

.empty-card {
  padding: 48px 24px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  background: #ffffff;
  border-radius: 16px;
  border: 1px dashed #CBD5E1;
}

.empty-icon { font-size: 38px; margin-bottom: 6px; }
.empty-title { font-size: 16px; font-weight: 700; color: #111827; }
.empty-desc { font-size: 13px; color: #64748B; margin-top: 4px; }

.fee-stack { display: flex; flex-direction: column; gap: 12px; }
.fee-card {
  padding: 18px 20px;
  border-radius: 16px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  transition: all 160ms cubic-bezier(0.16, 1, 0.3, 1);
}

.fee-card:hover {
  transform: translateY(-1px);
  border-color: #D1D5DB;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
}

.fee-row { display: flex; justify-content: space-between; gap: 14px; align-items: flex-start; }
.fee-main-info { flex: 1; min-width: 0; }

.fee-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.fee-title {
  font-size: 16px;
  font-weight: 800;
  color: #111827;
}

.audience-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
}

.audience--both {
  background: #F1F5F9;
  color: #475569;
}

.audience--occupant {
  background: #EFF6FF;
  color: #1D4ED8;
}

.audience--homeowner {
  background: #F3E8FF;
  color: #7E22CE;
}

.fee-meta {
  margin-top: 5px;
  color: var(--c-muted);
  font-size: 12.5px;
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.fee-code-pill {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-weight: 700;
  font-size: 11.5px;
  color: #0F172A;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  padding: 1px 6px;
  border-radius: 4px;
}

.fee-amount-box {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.fee-amount {
  font-size: 18px;
  font-weight: 800;
  color: #0A5C38;
  font-family: var(--font-display, inherit);
}

.fee-details-strip {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px solid #F1F5F9;
  flex-wrap: wrap;
}

.detail-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
}

.pill-label {
  color: #94A3B8;
  font-weight: 600;
}

.pill-value {
  color: #334155;
  font-weight: 700;
}

.fee-description {
  margin-top: 8px;
  color: #64748B;
  font-size: 13px;
  line-height: 1.45;
}

/* ── Create Fee Modal / Sheet ──────────────────────── */
.sheet-overlay {
  position: fixed;
  inset: 0;
  z-index: 1000;
  background: rgba(10, 18, 14, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}

.sheet {
  width: 100%;
  max-width: 520px;
  border-radius: 20px;
  padding: 24px;
  background: #ffffff;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.18);
  max-height: 90vh;
  overflow-y: auto;
}

.sheet-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.sheet-eyebrow {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: var(--c-muted);
  font-weight: 700;
}

.sheet-title {
  margin-top: 4px;
  font-size: 20px;
  font-weight: 800;
  font-family: var(--font-display, inherit);
  color: #0F172A;
}

.sheet-close {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 8px;
  background: #F1F5F9;
  color: #64748B;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 140ms ease;
}

.sheet-close:hover {
  background: #E2E8F0;
  color: #0F172A;
}

.sheet-body {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

/* Audience Options Grid */
.audience-options-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}

.audience-card-btn {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 10px;
  background: #F8FAFC;
  border: 1.5px solid #E2E8F0;
  border-radius: 10px;
  cursor: pointer;
  text-align: left;
  transition: all 140ms ease;
}

.audience-card-btn:hover {
  background: #F1F5F9;
  border-color: #CBD5E1;
}

.audience-card-btn.active {
  background: #ECFDF5;
  border-color: #0A5C38;
  box-shadow: 0 0 0 1px #0A5C38;
}

.opt-head {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-bottom: 2px;
}

.opt-icon {
  font-size: 14px;
}

.opt-title {
  font-size: 12.5px;
  font-weight: 700;
  color: #0F172A;
}

.audience-card-btn.active .opt-title {
  color: #0A5C38;
}

.opt-desc {
  font-size: 10px;
  color: #64748B;
  line-height: 1.3;
}

.double-grid {
  display: grid;
  gap: 10px;
  grid-template-columns: 1fr 1fr;
}

/* Recurring Box */
.recurring-box {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.freq-chips-row {
  display: flex;
  gap: 6px;
}

.freq-chip-btn {
  flex: 1;
  padding: 7px 10px;
  border: 1px solid #CBD5E1;
  border-radius: var(--btn-radius-sm, 8px);
  background: #ffffff;
  font-size: 12.5px;
  font-weight: 600;
  color: #475569;
  cursor: pointer;
  transition: all 140ms ease;
  text-align: center;
}

.freq-chip-btn:hover {
  background: #F1F5F9;
  color: #0F172A;
}

.freq-chip-btn.active {
  background: #0A5C38;
  color: #ffffff;
  border-color: #0A5C38;
}

.form-help-text {
  font-size: 11.5px;
  color: #64748B;
  margin-top: 2px;
}

.sheet-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 18px;
}

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
  .audience-options-grid { grid-template-columns: 1fr; }
}
</style>
