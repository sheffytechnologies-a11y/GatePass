<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Emergencies</h1>
        <p class="page-sub">All reported incidents across the estate</p>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters card">
      <select v-model="statusFilter" class="form-select" @change="load">
        <option value="">All Statuses</option>
        <option value="sent">Sent</option>
        <option value="acknowledged">Acknowledged</option>
        <option value="resolved">Resolved</option>
      </select>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner"></div></div>

    <div v-else class="card table-card">
      <div v-if="emergencies.length === 0" class="empty-state"><p>No emergencies found.</p></div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Type</th>
              <th>Resident</th>
              <th>Estate</th>
              <th>Notes</th>
              <th>Status</th>
              <th>Reported</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="e in emergencies" :key="e.id" :class="{ 'row-urgent': e.status === 'sent' }">
              <td class="muted">{{ e.id }}</td>
              <td class="fw">
                <span class="type-icon">{{ typeIcon(e.type) }}</span> {{ e.type }}
              </td>
              <td>{{ e.resident?.name ?? '—' }}</td>
              <td class="muted">{{ e.estate?.name ?? '—' }}</td>
              <td class="muted truncate">{{ e.notes ?? '—' }}</td>
              <td>
                <span :class="statusClass(e.status)" class="badge">{{ e.status }}</span>
              </td>
              <td class="muted">{{ fmtDatetime(e.createdAt) }}</td>
              <td>
                <div class="row-actions">
                  <button
                    v-if="e.status === 'sent'"
                    class="btn btn-sm btn-outline"
                    :disabled="saving === e.id"
                    @click="acknowledge(e)"
                  >Acknowledge</button>
                  <button
                    v-if="e.status !== 'resolved'"
                    class="btn btn-sm btn-primary"
                    :disabled="saving === e.id"
                    @click="resolve(e)"
                  >Resolve</button>
                  <button class="btn btn-sm btn-danger" :disabled="saving === e.id" @click="confirmDelete(e)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Confirm Delete -->
    <div v-if="deleteTarget" class="modal-overlay" @click.self="deleteTarget = null">
      <div class="modal modal--sm">
        <div class="modal-header">
          <h3>Delete Emergency</h3>
          <button class="modal-close" @click="deleteTarget = null">✕</button>
        </div>
        <div class="modal-body">
          <p>Permanently delete the <strong>{{ deleteTarget.type }}</strong> emergency record? This cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="saving !== null" @click="doDelete">
            {{ saving !== null ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { emergenciesApi } from '@/api/index'
import { useToast } from '@/composables/useToast'

const { showToast } = useToast()

const loading = ref(true)
const emergencies = ref<any[]>([])
const statusFilter = ref('')
const saving = ref<number | null>(null)
const deleteTarget = ref<any>(null)

async function load() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (statusFilter.value) params.status = statusFilter.value
    const res = await emergenciesApi.getAll(params)
    emergencies.value = res.data.emergencies
  } catch {
    showToast('Failed to load emergencies.', 'error')
  } finally {
    loading.value = false
  }
}

async function acknowledge(e: any) {
  saving.value = e.id
  try {
    await emergenciesApi.acknowledge(e.id)
    showToast('Emergency acknowledged.', 'success')
    load()
  } catch {
    showToast('Failed to acknowledge.', 'error')
  } finally {
    saving.value = null
  }
}

async function resolve(e: any) {
  saving.value = e.id
  try {
    await emergenciesApi.resolve(e.id)
    showToast('Emergency resolved.', 'success')
    load()
  } catch {
    showToast('Failed to resolve.', 'error')
  } finally {
    saving.value = null
  }
}

function confirmDelete(e: any) { deleteTarget.value = e }

async function doDelete() {
  if (!deleteTarget.value) return
  saving.value = deleteTarget.value.id
  try {
    await emergenciesApi.delete(deleteTarget.value.id)
    showToast('Emergency deleted.', 'success')
    deleteTarget.value = null
    load()
  } catch {
    showToast('Failed to delete emergency.', 'error')
  } finally {
    saving.value = null
  }
}

function typeIcon(type: string): string {
  const icons: Record<string, string> = {
    'Security Incident': '🔒',
    'Fire': '🔥',
    'Medical': '🏥',
    'Intruder': '⚠️',
  }
  return icons[type] ?? '🚨'
}

function statusClass(status: string) {
  return {
    'badge-red':    status === 'sent',
    'badge-yellow': status === 'acknowledged',
    'badge-green':  status === 'resolved',
  }
}

function fmtDatetime(iso: string | null): string {
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

.filters { display: flex; gap: 12px; margin-bottom: 16px; padding: 12px 16px; flex-wrap: wrap; }
.filters .form-select { width: 200px; }

.card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-lg); box-shadow: var(--shadow); }
.table-card { overflow: hidden; }
.loading-state { display: flex; justify-content: center; padding: 80px; }

.fw    { font-weight: 500; }
.muted { color: var(--c-muted); }
.truncate { max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.type-icon { margin-right: 4px; }
.row-urgent { background: #FFF5F5; }
.row-actions { display: flex; gap: 6px; flex-wrap: wrap; }

.modal--sm { max-width: 420px; }
</style>
