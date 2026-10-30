<template>
  <!-- ── FORM OVERLAY ── -->
  <div v-if="screen !== 'list'" class="form-overlay">
    <!-- Step 1: Select user type -->
    <div v-if="screen === 'type-select'" class="form-page">
      <div class="form-nav">
        <button class="nav-back" @click="screen = 'list'">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="#1A1A1A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <span class="nav-title">Add User</span>
        <div class="nav-spacer"></div>
      </div>
      <div class="form-scroll">
        <h2 class="type-heading">Select User Type</h2>
        <div class="type-list">
          <label v-for="opt in typeOptions" :key="opt.value" class="type-option" :class="{ 'type-option--selected': selectedType === opt.value }">
            <span class="type-label">{{ opt.label }}</span>
            <div class="radio-ring" :class="{ 'radio-ring--filled': selectedType === opt.value }">
              <div v-if="selectedType === opt.value" class="radio-dot"></div>
            </div>
            <input type="radio" :value="opt.value" v-model="selectedType" class="sr-only" />
          </label>
        </div>
      </div>
      <div class="form-footer">
        <button class="btn btn-primary btn-block" @click="screen = 'form'">Continue</button>
      </div>
    </div>

    <!-- Step 2: Fill in details -->
    <div v-else-if="screen === 'form'" class="form-page">
      <div class="form-nav">
        <button class="nav-back" @click="screen = 'type-select'">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="#1A1A1A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <span class="nav-title">{{ formTitle }}</span>
        <button class="nav-close" @click="screen = 'list'">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#7B7E87" stroke-width="1.5"/><path d="M9 9l6 6M15 9l-6 6" stroke="#7B7E87" stroke-width="1.5" stroke-linecap="round"/></svg>
          Close
        </button>
      </div>
      <div class="form-scroll">
        <!-- Resident form -->
        <template v-if="selectedType === 'resident'">
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
        </template>

        <!-- Admin / Security form -->
        <template v-else>
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input v-model="form.name" class="form-input" placeholder="Enter Full Name" />
          </div>
          <div class="form-group">
            <label class="form-label">Phone No.</label>
            <input v-model="form.phone" class="form-input" type="tel" placeholder="Enter Phone No" />
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input v-model="form.email" class="form-input" type="email" placeholder="Enter Email Address" />
          </div>
        </template>

        <p v-if="formError" class="form-error err-top">{{ formError }}</p>
      </div>
      <div class="form-footer">
        <button class="btn btn-primary btn-block" :disabled="saving" @click="save">
          {{ saving ? 'Saving…' : 'Save' }}
        </button>
      </div>
    </div>
  </div>

  <!-- ── LIST VIEW ── -->
  <div class="users-page">
    <!-- Search -->
    <div class="search-bar">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="8" stroke="#AAAAAA" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="#AAAAAA" stroke-width="2" stroke-linecap="round"/></svg>
      <input v-model="search" class="search-input" placeholder="Search" @input="debouncedLoad" />
    </div>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-cell">
        <span class="stat-num">{{ allStats.admins }}</span>
        <span class="stat-lbl">Admins</span>
      </div>
      <div class="stat-sep"></div>
      <div class="stat-cell">
        <span class="stat-num">{{ allStats.residents }}</span>
        <span class="stat-lbl">Residents</span>
      </div>
      <div class="stat-sep"></div>
      <div class="stat-cell">
        <span class="stat-num">{{ allStats.securities }}</span>
        <span class="stat-lbl">Securities</span>
      </div>
    </div>
    <div class="stats-grid stats-grid-2">
      <div class="stat-cell">
        <span class="stat-num">{{ allStats.tenants }}</span>
        <span class="stat-lbl">Tenants</span>
      </div>
      <div class="stat-sep"></div>
      <div class="stat-cell">
        <span class="stat-num">{{ allStats.landlords }}</span>
        <span class="stat-lbl">Landlords</span>
      </div>
    </div>

    <!-- Filters + Add -->
    <div class="filter-bar">
      <select v-model="typeFilter" class="filter-select" @change="load">
        <option value="">User Type</option>
        <option value="admin">Admin</option>
        <option value="resident">Resident</option>
        <option value="security">Security</option>
      </select>
      <select v-model="statusFilter" class="filter-select" @change="load">
        <option value="">Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
      <button class="btn btn-primary btn-add" @click="startAdd">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="7.25" stroke="white" stroke-width="1.5"/><path d="M8 5v6M5 8h6" stroke="white" stroke-width="1.5" stroke-linecap="round"/></svg>
        Add User
      </button>
    </div>

    <!-- User list -->
    <div v-if="loading" class="state-box"><div class="spinner"></div></div>
    <div v-else-if="users.length === 0" class="state-box">No users found.</div>
    <div v-else class="user-list">
      <article v-for="user in users" :key="user.id" class="user-row" @click="openEdit(user)">
        <div class="user-avatar" :style="{ background: avatarBg(user.id) }">{{ initial(user.name) }}</div>
        <div class="user-details">
          <p class="user-name">{{ user.name }}</p>
          <p v-if="addressLine(user)" class="user-meta">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z" fill="#9CA3AF"/></svg>
            {{ addressLine(user) }}
          </p>
          <p class="user-meta">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            {{ capitalize(user.type) }}
          </p>
        </div>
        <span :class="statusBadgeClass(user)">{{ statusLabel(user) }}</span>
      </article>
    </div>

    <!-- Edit sheet -->
    <div v-if="editTarget" class="sheet-overlay" @click.self="editTarget = null">
      <div class="sheet card">
        <div class="sheet-head">
          <h3 class="sheet-title">Edit {{ editTarget.name }}</h3>
          <button class="sheet-close" @click="editTarget = null">✕</button>
        </div>
        <div class="sheet-body">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input v-model="editForm.name" class="form-input" />
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input v-model="editForm.phone" class="form-input" type="tel" />
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input v-model="editForm.email" class="form-input" type="email" />
          </div>
          <div class="form-group">
            <label class="form-label">Status</label>
            <div class="chip-row">
              <button class="chip" :class="{ active: editForm.is_active }" @click="editForm.is_active = true">Active</button>
              <button class="chip" :class="{ active: !editForm.is_active }" @click="editForm.is_active = false">Inactive</button>
            </div>
          </div>
          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>
        <div class="sheet-actions">
          <button class="btn btn-danger btn-sm" @click="confirmDelete(editTarget)">Delete</button>
          <button class="btn btn-primary" style="flex:1;justify-content:center" :disabled="saving" @click="saveEdit">{{ saving ? 'Saving…' : 'Save Changes' }}</button>
        </div>
      </div>
    </div>

    <div v-if="deleteTarget" class="sheet-overlay" @click.self="deleteTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <h3 class="sheet-title">Remove {{ deleteTarget.name }}?</h3>
          <button class="sheet-close" @click="deleteTarget = null">✕</button>
        </div>
        <p class="confirm-copy">This account will be permanently removed.</p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doDelete">{{ saving ? 'Deleting…' : 'Delete' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usersApi, residentsApi } from '@/api/index'
import { useToast } from '@/composables/useToast'

const { showToast } = useToast()
const router = useRouter()

// ── Screen state ──────────────────────────────────────────────────
type Screen = 'list' | 'type-select' | 'form'
const screen = ref<Screen>('list')
const selectedType = ref<'resident' | 'admin' | 'security'>('resident')
const typeOptions = [
  { label: 'Resident', value: 'resident' as const },
  { label: 'Admin',    value: 'admin'    as const },
  { label: 'Security', value: 'security' as const },
]

// ── List state ────────────────────────────────────────────────────
const loading    = ref(true)
const users      = ref<any[]>([])
const allUsers   = ref<any[]>([])
const allResidents = ref<any[]>([])
const search     = ref('')
const typeFilter = ref('')
const statusFilter = ref('')

// ── Add-user form ─────────────────────────────────────────────────
const saving    = ref(false)
const formError = ref('')
const form = ref({
  name: '', phone: '', email: '',
  lane: '', house: '', flat: '',
  landlordIsOccupant: false,
  tenants: [] as { name: string; phone: string; email: string }[],
})

// ── Edit form ─────────────────────────────────────────────────────
const editTarget = ref<any>(null)
const editForm   = ref({ name: '', phone: '', email: '', is_active: true })
const deleteTarget = ref<any>(null)

// ── Stats ─────────────────────────────────────────────────────────
const allStats = computed(() => ({
  admins:    allUsers.value.filter(u => u.type === 'admin').length,
  residents: allUsers.value.filter(u => u.type === 'resident').length,
  securities: allUsers.value.filter(u => u.type === 'security').length,
  tenants:   allResidents.value.filter(r => r.role === 'tenant').length,
  landlords: allResidents.value.filter(r => r.role === 'owner').length,
}))

const formTitle = computed(() => {
  if (selectedType.value === 'admin')    return 'Add Admin'
  if (selectedType.value === 'security') return 'Add Security'
  return 'Add Resident'
})

// ── Avatar helpers ────────────────────────────────────────────────
const COLORS = ['#F97316','#22C55E','#8B5CF6','#EC4899','#06B6D4','#F59E0B','#EF4444','#3B82F6']
function avatarBg(id: number): string { return COLORS[id % COLORS.length] }
function initial(name: string): string { return (name ?? '?').charAt(0).toUpperCase() }
function capitalize(s: string): string { return s ? s.charAt(0).toUpperCase() + s.slice(1) : '' }

function addressLine(user: any): string {
  const r = user.residency ?? user.resident
  if (!r) return ''
  const u = r.unit
  if (!u) return ''
  const parts: string[] = []
  if (u.lane)   parts.push(`L${u.lane}`)
  if (u.house)  parts.push(`H${u.house}`)
  if (u.flat)   parts.push(String(u.flat))
  if (parts.length) return parts.join(',')
  if (u.number) return u.number
  return ''
}

function statusBadgeClass(user: any): string {
  if (user.status === 'pending') return 'badge badge-yellow'
  return user.isActive ? 'badge badge-green' : 'badge badge-red'
}
function statusLabel(user: any): string {
  if (user.status === 'pending') return 'Pending'
  return user.isActive ? 'Active' : 'In-active'
}

// ── Data loading ──────────────────────────────────────────────────
let searchTimer: ReturnType<typeof setTimeout>
function debouncedLoad() { clearTimeout(searchTimer); searchTimer = setTimeout(load, 350) }

async function loadStats() {
  try {
    const [ur, rr] = await Promise.all([usersApi.getAll(), residentsApi.getAll()])
    allUsers.value     = ur.data.users     ?? []
    allResidents.value = rr.data.residents ?? []
  } catch {/* non-critical */}
}

async function load() {
  loading.value = true
  try {
    const p: Record<string, unknown> = {}
    if (search.value)       p.search = search.value
    if (typeFilter.value)   p.type   = typeFilter.value
    if (statusFilter.value) p.status = statusFilter.value
    const res = await usersApi.getAll(p)
    users.value = res.data.users ?? []
  } catch {
    showToast('Failed to load users.', 'error')
  } finally {
    loading.value = false
  }
}

// ── Add user flow ─────────────────────────────────────────────────
function startAdd() {
  router.push('/users/new')
}
function addTenant()         { form.value.tenants.push({ name: '', phone: '', email: '' }) }
function removeTenant(i: number) { form.value.tenants.splice(i, 1) }

async function save() {
  formError.value = ''
  if (!form.value.name.trim())  { formError.value = 'Name is required.'; return }
  if (!form.value.phone.trim()) { formError.value = 'Phone number is required.'; return }
  saving.value = true
  try {
    if (selectedType.value === 'resident') {
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
    } else {
      const payload: Record<string, unknown> = {
        name: form.value.name,
        phone: form.value.phone,
        type: selectedType.value,
      }
      if (form.value.email) payload.email = form.value.email
      await usersApi.create(payload)
    }
    showToast(`${formTitle.value.replace('Add ', '')} created.`, 'success')
    screen.value = 'list'
    await Promise.all([load(), loadStats()])
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? 'Something went wrong.'
  } finally {
    saving.value = false
  }
}

// ── Edit / Delete ─────────────────────────────────────────────────
function openEdit(user: any) {
  editTarget.value = user
  editForm.value   = { name: user.name, phone: user.phone ?? '', email: user.email ?? '', is_active: user.isActive }
  formError.value  = ''
}

async function saveEdit() {
  if (!editTarget.value) return
  formError.value = ''
  saving.value = true
  try {
    await usersApi.update(editTarget.value.id, {
      name: editForm.value.name,
      phone: editForm.value.phone,
      email: editForm.value.email,
      is_active: editForm.value.is_active,
    })
    showToast('User updated.', 'success')
    editTarget.value = null
    await Promise.all([load(), loadStats()])
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? 'Something went wrong.'
  } finally {
    saving.value = false
  }
}

function confirmDelete(user: any) { deleteTarget.value = user; editTarget.value = null }

async function doDelete() {
  if (!deleteTarget.value) return
  saving.value = true
  try {
    await usersApi.delete(deleteTarget.value.id)
    showToast('User deleted.', 'success')
    deleteTarget.value = null
    await Promise.all([load(), loadStats()])
  } catch {
    showToast('Failed to delete user.', 'error')
  } finally {
    saving.value = false
  }
}

onMounted(() => { load(); loadStats() })
</script>

<style scoped>
/* ── List page ──────────────────────────────── */
.users-page {
  margin: -8px -16px -18px;
  background: #fff;
  min-height: calc(100% + 26px);
  display: flex;
  flex-direction: column;
}
.search-bar {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 16px;
  border-bottom: 1px solid #F0F0F0;
}
.search-input {
  flex: 1; border: none; outline: none;
  font-size: 15px; background: transparent; color: #1A1A1A;
}
.search-input::placeholder { color: #C0C0C0; }
.stats-grid {
  display: grid; grid-template-columns: 1fr auto 1fr auto 1fr;
  padding: 14px 16px; border-bottom: 1px solid #F5F5F5; text-align: center;
}
.stats-grid-2 {
  grid-template-columns: 1fr auto 1fr;
  border-bottom: 1.5px solid #EBEBEB;
}
.stat-cell { display: flex; flex-direction: column; gap: 2px; padding: 3px 0; }
.stat-num  { font-size: 20px; font-weight: 700; color: #1A1A1A; }
.stat-lbl  { font-size: 11px; color: #9CA3AF; }
.stat-sep  { width: 1px; background: #E5E5E5; margin: 4px 0; }
.filter-bar {
  display: flex; align-items: center; gap: 8px;
  padding: 10px 12px; border-bottom: 1px solid #F0F0F0;
}
.filter-select {
  flex: 1; padding: 8px 10px; border: 1.5px solid #E5E3DF;
  border-radius: 10px; background: white; color: #1A1A1A;
  font-size: 13px; outline: none; cursor: pointer;
}
.btn-add { display: flex; align-items: center; gap: 5px; padding: 8px 14px; font-size: 13px; border-radius: 10px; }
.user-list { display: flex; flex-direction: column; }
.user-row {
  display: flex; align-items: center; gap: 12px;
  padding: 14px 16px; border-bottom: 1px solid #F5F5F5; cursor: pointer;
}
.user-row:last-child { border-bottom: none; }
.user-avatar {
  width: 46px; height: 46px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 19px; font-weight: 700; color: #fff; flex-shrink: 0;
}
.user-details { flex: 1; min-width: 0; }
.user-name { font-size: 15px; font-weight: 700; color: #1A1A1A; }
.user-meta { font-size: 12px; color: #9CA3AF; margin-top: 2px; display: flex; align-items: center; gap: 4px; }
.state-box { min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--c-muted); font-size: 14px; }
</style>
/* ── Edit / delete sheets ──────────────────── */
.sheet-overlay { position: fixed; inset: 0; z-index: 40; background: rgba(10,18,14,0.5); display: flex; align-items: flex-end; justify-content: center; padding: 16px; }
.sheet { width: min(var(--shell-width), 100%); border-radius: 28px; padding: 20px; }
.sheet-sm { max-width: 420px; }
.sheet-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
.sheet-title { font-size: 18px; font-weight: 700; }
.sheet-close { width: 32px; height: 32px; border: none; border-radius: 50%; background: #edf2ee; color: var(--c-text); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.sheet-body { display: flex; flex-direction: column; gap: 14px; }
.sheet-actions { display: flex; gap: 10px; margin-top: 18px; }
.confirm-copy { color: var(--c-muted); font-size: 14px; margin-bottom: 4px; }
.chip-row { display: flex; gap: 8px; }
.chip { border: none; border-radius: 999px; padding: 8px 14px; background: #edf2ee; color: #4f5f56; font-weight: 600; font-size: 13px; }
.chip.active { background: var(--c-primary); color: white; }

/* ── Form overlay ──────────────────────────── */
.form-overlay {
  position: fixed; inset: 0; background: #fff; z-index: 200;
  display: flex; justify-content: center;
}
.form-page {
  width: 100%; max-width: var(--shell-width);
  height: 100%; display: flex; flex-direction: column; overflow: hidden;
}
.form-nav {
  display: flex; align-items: center; justify-content: space-between;
  padding: 14px 16px; border-bottom: 1px solid #F0F0F0; flex-shrink: 0;
}
.nav-back {
  width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
  background: none; border: none; cursor: pointer;
}
.nav-title { font-size: 16px; font-weight: 700; color: #1A1A1A; }
.nav-spacer { width: 36px; }
.nav-close {
  display: flex; align-items: center; gap: 5px;
  font-size: 13px; color: #7B7E87; background: none; border: none; cursor: pointer;
}
.form-scroll {
  flex: 1; overflow-y: auto; padding: 20px 16px;
  display: flex; flex-direction: column; gap: 16px;
}
/* Type selection */
.type-heading { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
.type-list { display: flex; flex-direction: column; }
.type-option {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 0; border-bottom: 1px solid #F0F0F0; cursor: pointer;
}
.type-option:first-child { border-top: 1px solid #F0F0F0; }
.type-option--selected .type-label { font-weight: 600; color: #1A1A1A; }
.type-label { font-size: 15px; color: #1A1A1A; }
.radio-ring {
  width: 22px; height: 22px; border-radius: 50%; border: 2px solid #D0D0D0;
  display: flex; align-items: center; justify-content: center;
}
.radio-ring--filled { border-color: var(--c-primary); }
.radio-dot { width: 12px; height: 12px; border-radius: 50%; background: var(--c-primary); }
/* Resident form extras */
.form-section-heading { font-size: 18px; font-weight: 700; color: #1A1A1A; }
.address-row { display: grid; grid-template-columns: 1fr 1.4fr 1fr; gap: 8px; }
.toggle-row { display: flex; align-items: center; gap: 12px; }
.toggle-switch { position: relative; display: inline-flex; cursor: pointer; }
.toggle-switch input { position: absolute; opacity: 0; width: 0; height: 0; }
.toggle-track {
  width: 44px; height: 26px; background: #E0E0E0; border-radius: 13px;
  transition: background 0.2s; position: relative; display: block;
}
.toggle-switch input:checked + .toggle-track { background: var(--c-primary); }
.toggle-thumb {
  position: absolute; top: 3px; left: 3px;
  width: 20px; height: 20px; border-radius: 50%;
  background: white; transition: left 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.toggle-switch input:checked + .toggle-track .toggle-thumb { left: 21px; }
.toggle-label { font-size: 14px; color: #1A1A1A; }
.occupants-head { display: flex; align-items: center; justify-content: space-between; }
.btn-add-tenant { display: flex; align-items: center; gap: 6px; border-radius: 999px; padding: 8px 14px; }
.tenant-block { display: flex; flex-direction: column; gap: 12px; padding-top: 12px; border-top: 1px solid #F0F0F0; }
.label-row { display: flex; align-items: center; justify-content: space-between; }
.tenant-del { background: none; border: none; padding: 0; cursor: pointer; display: flex; }
.err-top { margin-top: 4px; }
.form-footer {
  padding: 14px 16px; border-top: 1px solid #F0F0F0; flex-shrink: 0;
}
.btn-block { width: 100%; justify-content: center; border-radius: 14px; padding: 15px; font-size: 16px; }
