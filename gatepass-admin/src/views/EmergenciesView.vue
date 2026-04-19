<template>
  <div class="mobile-page">
    <section class="hero-card hero-card--alert">
      <div>
        <div class="hero-eyebrow">Emergency feed</div>
        <h1 class="hero-title">Respond to active incidents without leaving the mobile admin shell.</h1>
      </div>
    </section>

    <section class="card section-card filter-card">
      <div class="chip-row">
        <button v-for="item in statusOptions" :key="item.value" class="chip" :class="{ active: statusFilter === item.value }" @click="setStatus(item.value)">
          {{ item.label }}
        </button>
      </div>
    </section>

    <section class="card section-card">
      <div class="section-head">
        <div>
          <h2 class="section-title">Reported incidents</h2>
          <p class="section-copy">{{ emergencies.length }} incident{{ emergencies.length === 1 ? '' : 's' }} in view.</p>
        </div>
      </div>

      <div v-if="loading" class="loading-state"><div class="spinner"></div></div>
      <div v-else-if="emergencies.length === 0" class="empty-state">No emergencies found.</div>
      <div v-else class="stack-list">
        <article v-for="incident in emergencies" :key="incident.id" class="record-card card" :class="{ urgent: incident.status === 'sent' }">
          <div class="record-top">
            <div>
              <h3 class="record-title">{{ typeIcon(incident.type) }} {{ incident.type }}</h3>
              <p class="record-meta">{{ incident.resident?.name ?? '—' }} • {{ incident.estate?.name ?? '—' }}</p>
            </div>
            <span :class="statusClass(incident.status)" class="badge">{{ incident.status }}</span>
          </div>
          <p class="incident-notes">{{ incident.notes ?? 'No notes provided.' }}</p>
          <div class="record-bottom">
            <span class="record-sub">Reported {{ fmtDatetime(incident.createdAt) }}</span>
            <div class="row-actions">
              <button v-if="incident.status === 'sent'" class="btn btn-sm btn-outline" :disabled="saving === incident.id" @click="acknowledge(incident)">Acknowledge</button>
              <button v-if="incident.status !== 'resolved'" class="btn btn-sm btn-primary" :disabled="saving === incident.id" @click="resolve(incident)">Resolve</button>
              <button class="btn btn-sm btn-danger" :disabled="saving === incident.id" @click="confirmDelete(incident)">Delete</button>
            </div>
          </div>
        </article>
      </div>
    </section>

    <div v-if="deleteTarget" class="sheet-overlay" @click.self="deleteTarget = null">
      <div class="sheet sheet-sm card">
        <div class="sheet-head">
          <div>
            <div class="sheet-eyebrow">Delete emergency</div>
            <h3 class="sheet-title">Remove {{ deleteTarget.type }}?</h3>
          </div>
          <button class="sheet-close" @click="deleteTarget = null">✕</button>
        </div>
        <p class="confirm-copy">This emergency record will be permanently removed.</p>
        <div class="sheet-actions">
          <button class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="saving !== null" @click="doDelete">{{ saving !== null ? 'Deleting…' : 'Delete' }}</button>
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

const statusOptions = [
  { label: 'All', value: '' },
  { label: 'Sent', value: 'sent' },
  { label: 'Acknowledged', value: 'acknowledged' },
  { label: 'Resolved', value: 'resolved' },
]

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

function setStatus(value: string) {
  statusFilter.value = value
  load()
}

async function acknowledge(incident: any) {
  saving.value = incident.id
  try {
    await emergenciesApi.acknowledge(incident.id)
    showToast('Emergency acknowledged.', 'success')
    load()
  } catch {
    showToast('Failed to acknowledge.', 'error')
  } finally {
    saving.value = null
  }
}

async function resolve(incident: any) {
  saving.value = incident.id
  try {
    await emergenciesApi.resolve(incident.id)
    showToast('Emergency resolved.', 'success')
    load()
  } catch {
    showToast('Failed to resolve.', 'error')
  } finally {
    saving.value = null
  }
}

function confirmDelete(incident: any) { deleteTarget.value = incident }

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
    Fire: '🔥',
    Medical: '🏥',
    Intruder: '⚠️',
  }
  return icons[type] ?? '🚨'
}

function statusClass(status: string) {
  return {
    'badge-red': status === 'sent',
    'badge-yellow': status === 'acknowledged',
    'badge-green': status === 'resolved',
  }
}

function fmtDatetime(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(load)
</script>

<style scoped>
.mobile-page { display: flex; flex-direction: column; gap: 16px; }
.hero-card { padding: 20px; border-radius: 28px; color: white; }
.hero-card--alert { background: linear-gradient(145deg, #6d1118 0%, #c1272d 56%, #f3836f 100%); }
.hero-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: rgba(255,255,255,0.74); }
.hero-title { margin-top: 10px; font-size: 26px; line-height: 1.05; font-family: var(--font-display); }
.section-card { padding: 16px; }
.filter-card { display: flex; flex-direction: column; gap: 12px; }
.chip-row { display: flex; gap: 8px; overflow-x: auto; }
.chip { border: none; border-radius: 999px; padding: 10px 14px; background: #f2e7e7; color: #725252; white-space: nowrap; font-weight: 700; }
.chip.active { background: #b91c1c; color: white; }
.section-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; }
.section-title { font-size: 18px; font-weight: 700; }
.section-copy { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.loading-state, .empty-state { min-height: 120px; display: flex; align-items: center; justify-content: center; color: var(--c-muted); }
.stack-list { display: flex; flex-direction: column; gap: 10px; }
.record-card { padding: 16px; border-radius: 20px; }
.record-card.urgent { background: #fff3f2; }
.record-top, .record-bottom { display: flex; justify-content: space-between; gap: 12px; }
.record-bottom { margin-top: 14px; align-items: center; }
.record-title { font-size: 17px; font-weight: 700; color: var(--c-text); }
.record-meta { margin-top: 4px; color: var(--c-muted); font-size: 13px; }
.incident-notes { margin-top: 14px; padding: 14px; border-radius: 18px; background: #faf4f4; color: var(--c-text); }
.record-sub { color: var(--c-muted); font-size: 12px; }
.row-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.sheet-overlay { position: fixed; inset: 0; z-index: 40; background: rgba(10,18,14,0.6); display: flex; align-items: flex-end; justify-content: center; padding: 16px; }
.sheet { width: min(var(--shell-width), 100%); border-radius: 28px; padding: 18px; }
.sheet-sm { max-width: 420px; }
.sheet-head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
.sheet-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: var(--c-muted); }
.sheet-title { margin-top: 6px; font-size: 22px; font-family: var(--font-display); }
.sheet-close { width: 36px; height: 36px; border: none; border-radius: 50%; background: #edf2ee; color: var(--c-text); }
.sheet-actions { display: flex; gap: 10px; margin-top: 18px; }
.sheet-actions .btn { flex: 1; justify-content: center; }
.confirm-copy { margin-top: 16px; color: var(--c-muted); }
@media (max-width: 420px) {
  .record-top, .record-bottom { flex-direction: column; align-items: flex-start; }
}
</style>
