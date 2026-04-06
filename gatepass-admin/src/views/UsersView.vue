<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Users</h1>
        <p class="page-sub">Manage resident and security accounts</p>
      </div>
      <button class="btn btn-primary" @click="openCreate">+ New User</button>
    </div>

    <!-- Filters -->
    <div class="filters card">
      <input v-model="search" class="form-input" placeholder="Search name or phone…" @input="load" />
      <select v-model="typeFilter" class="form-select" @change="load">
        <option value="">All Types</option>
        <option value="resident">Resident</option>
        <option value="security">Security</option>
      </select>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner"></div></div>

    <div v-else class="card table-card">
      <div v-if="users.length === 0" class="empty-state">
        <p>No users found.</p>
      </div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Phone</th>
              <th>Email</th>
              <th>Type</th>
              <th>Status</th>
              <th>Created</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td class="muted">{{ user.id }}</td>
              <td class="fw">{{ user.name }}</td>
              <td>{{ user.phone }}</td>
              <td class="muted">{{ user.email ?? '—' }}</td>
              <td>
                <span :class="user.type === 'security' ? 'badge badge-blue' : 'badge badge-green'">
                  {{ user.type }}
                </span>
              </td>
              <td>
                <span :class="user.isActive ? 'badge badge-green' : 'badge badge-gray'">
                  {{ user.isActive ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="muted">{{ fmtDate(user.createdAt) }}</td>
              <td>
                <div class="row-actions">
                  <button class="btn btn-sm btn-outline" @click="openEdit(user)">Edit</button>
                  <button class="btn btn-sm btn-danger" @click="confirmDelete(user)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- User Modal -->
    <div v-if="modalOpen" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ editing ? 'Edit User' : 'Create User' }}</h3>
          <button class="modal-close" @click="closeModal">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Full Name *</label>
            <input v-model="form.name" class="form-input" placeholder="e.g. John Doe" />
          </div>
          <div class="form-group">
            <label class="form-label">Phone *</label>
            <input v-model="form.phone" class="form-input" placeholder="+234…" />
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input v-model="form.email" class="form-input" type="email" placeholder="optional" />
          </div>
          <div class="form-group">
            <label class="form-label">{{ editing ? 'New Password (leave blank to keep)' : 'Password *' }}</label>
            <input v-model="form.password" class="form-input" type="password" placeholder="••••••" />
          </div>
          <div class="form-group">
            <label class="form-label">Type *</label>
            <select v-model="form.type" class="form-select">
              <option value="resident">Resident</option>
              <option value="security">Security</option>
            </select>
          </div>
          <div v-if="editing" class="form-group">
            <label class="form-label">Status</label>
            <select v-model="form.is_active" class="form-select">
              <option :value="true">Active</option>
              <option :value="false">Inactive</option>
            </select>
          </div>
          <p v-if="formError" class="form-error">{{ formError }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="closeModal">Cancel</button>
          <button class="btn btn-primary" :disabled="saving" @click="save">
            {{ saving ? 'Saving…' : (editing ? 'Save Changes' : 'Create User') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete -->
    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal modal--sm">
        <div class="modal-header">
          <h3>Delete User</h3>
          <button class="modal-close" @click="deleteTarget = null">✕</button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong>{{ deleteTarget.name }}</strong>? This cannot be undone.</p>
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
import { usersApi } from '@/api/index'
import { useToast } from '@/composables/useToast'

const { showToast } = useToast()

const loading = ref(true)
const users = ref<any[]>([])
const search = ref('')
const typeFilter = ref('')

const modalOpen = ref(false)
const editing = ref<any>(null)
const saving = ref(false)
const formError = ref('')
const deleteTarget = ref<any>(null)

const form = ref({
  name: '',
  phone: '',
  email: '',
  password: '',
  type: 'resident' as 'resident' | 'security',
  is_active: true,
})

async function load() {
  loading.value = true
  try {
    const params: Record<string, string> = {}
    if (search.value) params.search = search.value
    if (typeFilter.value) params.type = typeFilter.value
    const res = await usersApi.getAll(params)
    users.value = res.data.users
  } catch {
    showToast('Failed to load users.', 'error')
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value = null
  form.value = { name: '', phone: '', email: '', password: '', type: 'resident', is_active: true }
  formError.value = ''
  modalOpen.value = true
}

function openEdit(user: any) {
  editing.value = user
  form.value = { name: user.name, phone: user.phone, email: user.email ?? '', password: '', type: user.type, is_active: user.isActive }
  formError.value = ''
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
  editing.value = null
}

async function save() {
  formError.value = ''
  if (!form.value.name.trim()) { formError.value = 'Name is required.'; return }
  if (!form.value.phone.trim()) { formError.value = 'Phone is required.'; return }
  if (!editing.value && !form.value.password) { formError.value = 'Password is required.'; return }

  saving.value = true
  try {
    const payload: Record<string, unknown> = {
      name: form.value.name,
      phone: form.value.phone,
      type: form.value.type,
    }
    if (form.value.email) payload.email = form.value.email
    if (form.value.password) payload.password = form.value.password
    if (editing.value) payload.is_active = form.value.is_active

    if (editing.value) {
      await usersApi.update(editing.value.id, payload)
      showToast('User updated.', 'success')
    } else {
      await usersApi.create(payload)
      showToast('User created.', 'success')
    }
    closeModal()
    load()
  } catch (err: any) {
    formError.value = err?.response?.data?.message ?? 'Something went wrong.'
  } finally {
    saving.value = false
  }
}

function confirmDelete(user: any) {
  deleteTarget.value = user
}

async function doDelete() {
  if (!deleteTarget.value) return
  saving.value = true
  try {
    await usersApi.delete(deleteTarget.value.id)
    showToast('User deleted.', 'success')
    deleteTarget.value = null
    load()
  } catch {
    showToast('Failed to delete user.', 'error')
  } finally {
    saving.value = false
  }
}

function fmtDate(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-NG', { dateStyle: 'medium' })
}

onMounted(load)
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 16px;
  flex-wrap: wrap;
}
.page-title { font-size: 22px; font-weight: 700; }
.page-sub   { font-size: 13px; color: var(--c-muted); margin-top: 2px; }

.filters {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
  padding: 12px 16px;
  flex-wrap: wrap;
}
.filters .form-input  { flex: 1; min-width: 200px; }
.filters .form-select { width: 160px; }

.card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-lg); box-shadow: var(--shadow); }
.table-card { overflow: hidden; }

.loading-state { display: flex; justify-content: center; padding: 80px; }

.fw   { font-weight: 500; }
.muted{ color: var(--c-muted); }

.row-actions { display: flex; gap: 6px; }

.form-label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 6px; color: var(--c-text); }
.form-error { color: var(--c-danger); font-size: 13px; margin-top: 8px; }

.modal--sm { max-width: 400px; }
</style>
