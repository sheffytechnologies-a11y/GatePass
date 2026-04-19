<template>
  <div>
    <div class="page-header">
      <div class="back-row">
        <router-link :to="backLink" class="back-btn">← Back</router-link>
        <h1 class="page-title">Pass Detail</h1>
      </div>
      <div class="header-actions">
        <button
          v-if="auth.isAdmin && pass && pass.status !== 'Revoked' && pass.status !== 'Expired'"
          class="btn btn-danger"
          @click="confirmRevoke"
        >Revoke Pass</button>
        <button v-if="auth.isAdmin && pass" class="btn btn-outline btn-danger-outline" @click="showDeleteConfirm = true">Delete</button>
        <button v-if="auth.isSecurity && pass && pass.status === 'Pending'" class="btn btn-primary" :disabled="saving" @click="allowEntry">{{ saving ? 'Allowing…' : 'Allow Entry' }}</button>
        <button v-if="auth.isSecurity && pass && pass.status === 'On-site'" class="btn btn-outline" :disabled="saving" @click="markExited">{{ saving ? 'Updating…' : 'Exited' }}</button>
      </div>
    </div>

    <div v-if="loading" class="loading-state"><div class="spinner"></div></div>

    <template v-else-if="pass">
      <div class="detail-grid">
        <!-- Main info card -->
        <div class="card info-card">
          <div class="card-header">
            <h2 class="card-title">Visitor Information</h2>
            <span :class="passStatusClass(pass.status)" class="badge badge-lg">{{ pass.status }}</span>
          </div>
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">Visitor Name</span>
              <span class="info-value">{{ pass.visitorName }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Phone</span>
              <span class="info-value">{{ pass.visitorPhone ?? '—' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Vehicle Plate</span>
              <span class="info-value">{{ pass.vehiclePlate ?? '—' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Purpose</span>
              <span class="info-value">{{ pass.purpose ?? '—' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Pass Type</span>
              <span class="info-value">{{ pass.type }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Items Flagged</span>
              <span class="info-value">
                <span v-if="pass.itemsFlagged" class="badge badge-yellow">🚩 Yes</span>
                <span v-else class="badge badge-green">Clear</span>
              </span>
            </div>
          </div>
        </div>

        <!-- Timeline card -->
        <div class="card timeline-card">
          <div class="card-header">
            <h2 class="card-title">Timeline</h2>
          </div>
          <div class="timeline">
            <div class="timeline-item" :class="{ active: true }">
              <span class="tl-dot tl-dot--green"></span>
              <div>
                <div class="tl-label">Created</div>
                <div class="tl-time">{{ fmtDatetime(pass.createdAt) }}</div>
              </div>
            </div>
            <div class="timeline-item" :class="{ active: !!pass.arrivedAt }">
              <span class="tl-dot" :class="pass.arrivedAt ? 'tl-dot--green' : 'tl-dot--gray'"></span>
              <div>
                <div class="tl-label">Arrived</div>
                <div class="tl-time">{{ pass.arrivedAt ? fmtDatetime(pass.arrivedAt) : 'Not yet' }}</div>
              </div>
            </div>
            <div class="timeline-item" :class="{ active: !!pass.exitedAt }">
              <span class="tl-dot" :class="pass.exitedAt ? 'tl-dot--green' : 'tl-dot--gray'"></span>
              <div>
                <div class="tl-label">Exited</div>
                <div class="tl-time">{{ pass.exitedAt ? fmtDatetime(pass.exitedAt) : 'Not yet' }}</div>
              </div>
            </div>
            <div v-if="pass.revokedAt" class="timeline-item active">
              <span class="tl-dot tl-dot--red"></span>
              <div>
                <div class="tl-label">Revoked</div>
                <div class="tl-time">{{ fmtDatetime(pass.revokedAt) }}</div>
              </div>
            </div>
            <div v-if="pass.expiresAt" class="timeline-item">
              <span class="tl-dot tl-dot--yellow"></span>
              <div>
                <div class="tl-label">Expires</div>
                <div class="tl-time">{{ fmtDatetime(pass.expiresAt) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Resident card -->
        <div class="card resident-card">
          <div class="card-header">
            <h2 class="card-title">Resident</h2>
          </div>
          <div class="info-grid" v-if="pass.resident">
            <div class="info-item">
              <span class="info-label">Name</span>
              <span class="info-value">{{ pass.resident.user?.name ?? '—' }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Unit</span>
              <span class="info-value">Unit {{ pass.resident.unit?.flat_address ?? '—' }}</span>
            </div>
          </div>
          <div v-else class="empty-state"><p>No resident linked.</p></div>
        </div>
      </div>
    </template>

    <div v-else class="empty-state"><p>Pass not found.</p></div>

    <!-- Confirm Revoke -->
    <div v-if="showRevokeConfirm" class="modal-overlay" @click.self="showRevokeConfirm = false">
      <div class="modal modal--sm">
        <div class="modal-header">
          <h3>Revoke Pass</h3>
          <button class="modal-close" @click="showRevokeConfirm = false">✕</button>
        </div>
        <div class="modal-body">
          <p>Revoke pass for <strong>{{ pass?.visitorName }}</strong>? They will no longer be allowed entry.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showRevokeConfirm = false">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doRevoke">
            {{ saving ? 'Revoking…' : 'Revoke' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete -->
    <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
      <div class="modal modal--sm">
        <div class="modal-header">
          <h3>Delete Pass</h3>
          <button class="modal-close" @click="showDeleteConfirm = false">✕</button>
        </div>
        <div class="modal-body">
          <p>Permanently delete this pass? This cannot be undone.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDeleteConfirm = false">Cancel</button>
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
import { useRoute, useRouter } from 'vue-router'
import { passesApi } from '@/api/index'
import { useToast } from '@/composables/useToast'
import { useAuthStore } from '@/stores/auth'
import client from '@/api/client'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const { showToast } = useToast()

const loading = ref(true)
const pass = ref<any>(null)
const saving = ref(false)
const showRevokeConfirm = ref(false)
const showDeleteConfirm = ref(false)
const backLink = auth.isSecurity ? '/access' : '/passes'

async function loadPass() {
  try {
    const res = auth.isSecurity
      ? await client.get(`/v1/passes/${route.params.id as string}`)
      : await passesApi.getOne(route.params.id as string)
    pass.value = res.data.pass
  } catch {
    showToast('Pass not found.', 'error')
  } finally {
    loading.value = false
  }
}

async function allowEntry() {
  if (!pass.value || saving.value) return
  saving.value = true
  try {
    const res = await client.patch(`/v1/passes/${route.params.id as string}/allow-entry`)
    pass.value = res.data.pass
    showToast('Visitor allowed in.', 'success')
  } catch (error: any) {
    showToast(error?.response?.data?.message ?? 'Failed to allow entry.', 'error')
  } finally {
    saving.value = false
  }
}

async function markExited() {
  if (!pass.value || saving.value) return
  saving.value = true
  try {
    const res = await client.patch(`/v1/passes/${route.params.id as string}/mark-exited`)
    pass.value = res.data.pass
    showToast('Visitor marked as exited.', 'success')
  } catch (error: any) {
    showToast(error?.response?.data?.message ?? 'Failed to update exit.', 'error')
  } finally {
    saving.value = false
  }
}

function confirmRevoke() { showRevokeConfirm.value = true }

async function doRevoke() {
  if (!pass.value) return
  saving.value = true
  try {
    await passesApi.revoke(pass.value.ulid)
    showToast('Pass revoked.', 'success')
    showRevokeConfirm.value = false
    loadPass()
  } catch {
    showToast('Failed to revoke pass.', 'error')
  } finally {
    saving.value = false
  }
}

async function doDelete() {
  if (!pass.value) return
  saving.value = true
  try {
    await passesApi.delete(pass.value.ulid)
    showToast('Pass deleted.', 'success')
    router.push('/passes')
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

function fmtDatetime(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(loadPass)
</script>

<style scoped>
.page-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: 24px; gap: 16px; flex-wrap: wrap;
}
.back-row { display: flex; flex-direction: column; gap: 4px; }
.back-btn { font-size: 13px; color: var(--c-primary); font-weight: 500; }
.back-btn:hover { text-decoration: underline; }
.page-title { font-size: 22px; font-weight: 700; }
.header-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }

.loading-state { display: flex; justify-content: center; padding: 80px; }

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  grid-template-rows: auto auto;
  gap: 20px;
}
.info-card     { grid-column: 1; grid-row: 1; }
.timeline-card { grid-column: 2; grid-row: 1 / 3; }
.resident-card { grid-column: 1; grid-row: 2; }

.card { background: var(--c-surface); border: 1px solid var(--c-border); border-radius: var(--radius-lg); box-shadow: var(--shadow); }
.card-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--c-border); }
.card-title  { font-size: 15px; font-weight: 600; }
.badge-lg    { font-size: 13px; padding: 4px 12px; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; padding: 0; }
.info-item { padding: 14px 20px; border-bottom: 1px solid var(--c-border); }
.info-item:nth-child(odd)  { border-right: 1px solid var(--c-border); }
.info-item:nth-last-child(-n+2) { border-bottom: none; }
.info-label { display: block; font-size: 12px; color: var(--c-muted); margin-bottom: 4px; }
.info-value { font-weight: 500; font-size: 14px; }

/* Timeline */
.timeline { padding: 20px; display: flex; flex-direction: column; gap: 20px; }
.timeline-item { display: flex; align-items: flex-start; gap: 12px; opacity: 0.5; }
.timeline-item.active { opacity: 1; }
.tl-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; background: var(--c-border); }
.tl-dot--green  { background: var(--c-success); }
.tl-dot--gray   { background: var(--c-border); }
.tl-dot--red    { background: var(--c-danger); }
.tl-dot--yellow { background: var(--c-warning); }
.tl-label { font-size: 13px; font-weight: 600; color: var(--c-text); }
.tl-time  { font-size: 12px; color: var(--c-muted); margin-top: 2px; }

.btn-danger-outline { border: 1.5px solid var(--c-danger); color: var(--c-danger); background: transparent; }
.btn-danger-outline:hover { background: var(--c-danger-light); }

.modal--sm { max-width: 420px; }

@media (max-width: 900px) {
  .detail-grid { grid-template-columns: 1fr; }
  .timeline-card { grid-column: 1; grid-row: auto; }
  .resident-card { grid-column: 1; grid-row: auto; }
}
@media (max-width: 600px) {
  .info-grid { grid-template-columns: 1fr; }
  .info-item:nth-child(odd) { border-right: none; }
  .info-item:nth-last-child(-n+2) { border-bottom: 1px solid var(--c-border); }
  .info-item:last-child { border-bottom: none; }
}
</style>
