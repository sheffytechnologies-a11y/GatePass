<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-sub">System notifications sent to residents</p>
      </div>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner"></div></div>

    <div v-else class="card table-card">
      <div v-if="notifications.length === 0" class="empty-state"><p>No notifications found.</p></div>
      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Type</th>
              <th>Message</th>
              <th>Resident</th>
              <th>Read</th>
              <th>Sent</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="n in notifications" :key="n.id" :class="{ 'row-unread': !n.read }">
              <td class="muted">{{ n.id }}</td>
              <td>
                <span :class="typeClass(n.type)" class="badge">{{ n.type }}</span>
              </td>
              <td class="message-cell">{{ n.message }}</td>
              <td>{{ n.resident?.name ?? '—' }}</td>
              <td>
                <span :class="n.read ? 'badge badge-green' : 'badge badge-yellow'">
                  {{ n.read ? 'Read' : 'Unread' }}
                </span>
              </td>
              <td class="muted">{{ fmtDatetime(n.createdAt) }}</td>
              <td>
                <button class="btn btn-sm btn-danger" :disabled="deleting === n.id" @click="confirmDelete(n)">Delete</button>
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
          <h3>Delete Notification</h3>
          <button class="modal-close" @click="deleteTarget = null">✕</button>
        </div>
        <div class="modal-body">
          <p>Delete this notification? This cannot be undone.</p>
          <p class="preview">{{ deleteTarget.message }}</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="deleting !== null" @click="doDelete">
            {{ deleting !== null ? 'Deleting…' : 'Delete' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { notificationsApi } from '@/api/index'
import { useToast } from '@/composables/useToast'

const { showToast } = useToast()

const loading = ref(true)
const notifications = ref<any[]>([])
const deleting = ref<number | null>(null)
const deleteTarget = ref<any>(null)

async function load() {
  loading.value = true
  try {
    const res = await notificationsApi.getAll()
    notifications.value = res.data.notifications
  } catch {
    showToast('Failed to load notifications.', 'error')
  } finally {
    loading.value = false
  }
}

function confirmDelete(n: any) { deleteTarget.value = n }

async function doDelete() {
  if (!deleteTarget.value) return
  deleting.value = deleteTarget.value.id
  try {
    await notificationsApi.delete(deleteTarget.value.id)
    showToast('Notification deleted.', 'success')
    deleteTarget.value = null
    load()
  } catch {
    showToast('Failed to delete notification.', 'error')
  } finally {
    deleting.value = null
  }
}

function typeClass(type: string) {
  const map: Record<string, string> = {
    emergency:   'badge-red',
    arrival:     'badge-green',
    expiry:      'badge-yellow',
    pass:        'badge-blue',
  }
  return map[type] ?? 'badge-gray'
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

.card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-lg); box-shadow: var(--shadow); }
.table-card { overflow: hidden; }
.loading-state { display: flex; justify-content: center; padding: 80px; }

.muted { color: var(--c-muted); }
.row-unread { background: #F0FDF4; }
.message-cell { max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

.preview {
  margin-top: 10px; padding: 10px 12px;
  background: var(--c-bg); border-radius: var(--radius);
  font-size: 13px; color: var(--c-muted);
}
.modal--sm { max-width: 420px; }
</style>
