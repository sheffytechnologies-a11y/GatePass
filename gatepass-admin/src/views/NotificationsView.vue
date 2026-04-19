<template>
  <div class="mobile-page">
    <section class="hero-card hero-card--mint">
      <div>
        <div class="hero-eyebrow">Resident messages</div>
        <h1 class="hero-title">Review system notifications in the same mobile workflow.</h1>
      </div>
    </section>

    <section class="card section-card">
      <div class="section-head">
        <div>
          <h2 class="section-title">Notifications</h2>
          <p class="section-copy">{{ notifications.length }} message{{ notifications.length === 1 ? '' : 's' }} loaded.</p>
        </div>
      </div>

      <div v-if="loading" class="loading-state"><div class="spinner"></div></div>
      <div v-else-if="notifications.length === 0" class="empty-state">No notifications found.</div>
      <div v-else class="stack-list">
        <article v-for="notification in notifications" :key="notification.id" class="record-card card" :class="{ unread: !notification.read }">
          <div class="record-top">
            <div class="record-badges">
              <span :class="typeClass(notification.type)" class="badge">{{ notification.type }}</span>
              <span :class="notification.read ? 'badge badge-green' : 'badge badge-yellow'">{{ notification.read ? 'Read' : 'Unread' }}</span>
            </div>
            <button class="btn btn-sm btn-danger" :disabled="deleting === notification.id" @click="confirmDelete(notification)">Delete</button>
          </div>
          <p class="message-body">{{ notification.message }}</p>
          <div class="record-bottom">
            <span class="record-sub">{{ notification.resident?.name ?? '—' }}</span>
            <span class="record-sub">{{ fmtDatetime(notification.createdAt) }}</span>
          </div>
        </article>
      </div>
    </section>

    <div v-if="deleteTarget" class="sheet-overlay" @click.self="deleteTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Delete notification</div>
            <h3 class="sheet-title">Remove this message?</h3>
          </div>
          <button class="sheet-close" @click="deleteTarget = null">✕</button>
        </div>
        <p class="confirm-copy">{{ deleteTarget.message }}</p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="deleting !== null" @click="doDelete">{{ deleting !== null ? 'Deleting…' : 'Delete' }}</button>
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

function confirmDelete(notification: any) { deleteTarget.value = notification }

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
    emergency: 'badge-red',
    arrival: 'badge-green',
    expiry: 'badge-yellow',
    pass: 'badge-blue',
  }
  return map[type] ?? 'badge-gray'
}

function fmtDatetime(iso: string | null) {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(load)
</script>

<style scoped>
.mobile-page { display: flex; flex-direction: column; gap: 16px; }
.hero-card { padding: 20px; border-radius: 28px; color: white; }
.hero-card--mint { background: linear-gradient(145deg, #0b5f56 0%, #0d8b73 58%, #72d9c6 100%); }
.hero-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: rgba(255,255,255,0.74); }
.hero-title { margin-top: 10px; font-size: 26px; line-height: 1.05; font-family: var(--font-display); }
.section-card { padding: 16px; }
.section-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.section-title { font-size: 18px; font-weight: 700; }
.section-copy { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.loading-state, .empty-state { min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--c-muted); }
.stack-list { display: flex; flex-direction: column; gap: 10px; }
.record-card { padding: 16px; border-radius: 20px; }
.record-card.unread { background: #f3fcf8; }
.record-top, .record-bottom { display: flex; justify-content: space-between; gap: 12px; }
.record-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.message-body { margin-top: 14px; color: var(--c-text); line-height: 1.5; }
.record-bottom { margin-top: 14px; }
.record-sub { color: var(--c-muted); font-size: 12px; }
.sheet-overlay { position: fixed; inset: 0; z-index: 40; background: rgba(10,18,14,0.6); display: flex; align-items: flex-end; justify-content: center; padding: 16px; }
.sheet { width: min(var(--shell-width), 100%); border-radius: 28px; padding: 18px; }
.sheet-sm { max-width: 420px; }
.sheet-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
.sheet-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: var(--c-muted); }
.sheet-title { margin-top: 6px; font-size: 22px; font-family: var(--font-display); }
.sheet-close { width: 36px; height: 36px; border: none; border-radius: 50%; background: #edf2ee; color: var(--c-text); }
.sheet-actions { display: flex; gap: 10px; margin-top: 18px; }
.sheet-actions .btn { flex: 1; justify-content: center; }
.confirm-copy { margin-top: 16px; color: var(--c-muted); line-height: 1.5; }
@media (max-width: 420px) {
  .record-top, .record-bottom { flex-direction: column; align-items: flex-start; }
}
</style>
