<template>
  <div class="pass-detail-page">
    <!-- Top Navigation Bar with Standardized Back Button & Breadcrumbs -->
    <div class="page-top-nav">
      <div class="nav-left">
        <router-link to="/passes" class="btn-back">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
          </svg>
          <span>Back to Passes</span>
        </router-link>

        <div class="breadcrumbs">
          <router-link to="/passes" class="bc-link">Passes</router-link>
          <span class="bc-sep">/</span>
          <span class="bc-current">{{ pass?.visitorName || 'Pass Detail' }}</span>
        </div>
      </div>

      <!-- Action Header CTAs -->
      <div v-if="pass" class="header-actions">
        <button
          v-if="pass.status === 'Pending'"
          type="button"
          class="btn btn-primary"
          :disabled="saving"
          @click="allowEntry"
        >
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <span>{{ saving ? 'Allowing…' : 'Allow Entry' }}</span>
        </button>

        <button
          v-if="pass.status === 'On-site'"
          type="button"
          class="btn btn-outline"
          :disabled="saving"
          @click="markExited"
        >
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>{{ saving ? 'Updating…' : 'Mark Exited' }}</span>
        </button>

        <button
          v-if="pass.status !== 'Revoked' && pass.status !== 'Expired'"
          type="button"
          class="btn btn-danger"
          :disabled="saving"
          @click="confirmRevoke"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
          </svg>
          <span>Revoke Pass</span>
        </button>

        <button
          type="button"
          class="btn btn-outline btn-danger-outline"
          :disabled="saving"
          @click="showDeleteConfirm = true"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
          </svg>
          <span>Delete</span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="detail-loading-box">
      <BrandSpinner :size="40" color="#0A5C38" />
      <span class="loading-label">Loading pass record...</span>
    </div>

    <!-- Error / Not Found State -->
    <div v-else-if="!pass" class="empty-state card">
      <div class="empty-icon">🎫</div>
      <div class="empty-title">Pass Not Found</div>
      <p class="empty-body">The pass record could not be found or has been permanently removed.</p>
      <router-link to="/passes" class="btn btn-primary mt-3">Return to Passes</router-link>
    </div>

    <!-- Pass Content (2-Column Layout) -->
    <div v-else class="detail-layout">
      <!-- ── LEFT MAIN COLUMN (65%) ── -->
      <div class="detail-col-main">
        <!-- 1. Visitor Profile Card -->
        <div class="card visitor-hero-card">
          <div class="visitor-hero-head">
            <div class="visitor-avatar-wrap">
              <div class="visitor-avatar">
                {{ getInitials(pass.visitorName) }}
              </div>
            </div>

            <div class="visitor-identity-info">
              <div class="visitor-title-row">
                <h1 class="visitor-name">{{ pass.visitorName }}</h1>
                <span class="status-pill" :class="statusPillClass(pass.status)">
                  <span class="status-dot"></span>
                  <span>{{ pass.status }}</span>
                </span>
              </div>

              <div class="visitor-host-meta">
                <span class="meta-label">Issued for:</span>
                <span class="host-name">{{ pass.resident?.name || 'Estate General Access' }}</span>
                <span v-if="pass.resident?.unit" class="unit-badge">
                  🏡 Unit {{ pass.resident.unit }}
                </span>
              </div>
            </div>
          </div>

          <!-- Quick Stats / Core Meta Grid -->
          <div class="visitor-meta-grid">
            <!-- Phone -->
            <div class="meta-cell">
              <span class="cell-label">Phone Number</span>
              <div class="cell-val-row">
                <strong class="cell-val">{{ pass.visitorPhone || '—' }}</strong>
                <button
                  v-if="pass.visitorPhone"
                  type="button"
                  class="cell-action-btn"
                  title="Copy Phone Number"
                  @click="copyText(pass.visitorPhone, 'Phone number')"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Vehicle Plate -->
            <div class="meta-cell">
              <span class="cell-label">Vehicle Registration</span>
              <div v-if="pass.vehiclePlate" class="vehicle-plate-pill">
                <span class="plate-flag">🇳🇬</span>
                <span class="plate-code">{{ pass.vehiclePlate }}</span>
              </div>
              <span v-else class="cell-val text-muted">Pedestrian / No Vehicle</span>
            </div>

            <!-- Pass Type -->
            <div class="meta-cell">
              <span class="cell-label">Pass Type</span>
              <span class="type-badge" :class="pass.type === 'recurring' ? 'type-recurring' : 'type-onetime'">
                {{ pass.type === 'recurring' ? '🔁 Recurring Access' : '⚡ One-Time Pass' }}
              </span>
            </div>

            <!-- Security Flagged Status -->
            <div class="meta-cell">
              <span class="cell-label">Security Screening</span>
              <span v-if="pass.itemsFlagged" class="security-flag-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                  <line x1="4" y1="22" x2="4" y2="15"/>
                </svg>
                <span>Items Flagged for Gate Check</span>
              </span>
              <span v-else class="security-clear-badge">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>Clear &bull; No Items Declared</span>
              </span>
            </div>

            <!-- Purpose -->
            <div class="meta-cell meta-cell--wide">
              <span class="cell-label">Purpose of Visit</span>
              <p class="purpose-text">{{ pass.purpose || 'General social or residential visit' }}</p>
            </div>
          </div>
        </div>

        <!-- 2. Host Resident & Household Card -->
        <div class="card host-resident-card">
          <div class="card-section-head">
            <div>
              <h2 class="card-section-title">Host Resident & Household</h2>
              <p class="card-section-desc">The estate occupant responsible for authorizing this guest pass.</p>
            </div>
            <router-link
              v-if="pass.resident?.id"
              :to="`/residents/${pass.resident.id}`"
              class="host-profile-link"
            >
              <span>View Profile</span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <line x1="5" y1="12" x2="19" y2="12"/>
                <polyline points="12 5 19 12 12 19"/>
              </svg>
            </router-link>
          </div>

          <div v-if="pass.resident" class="resident-summary-row">
            <div class="resident-avatar-circle">
              {{ getInitials(pass.resident.name) }}
            </div>
            <div class="resident-summary-info">
              <div class="resident-name-title">{{ pass.resident.name || 'Verified Occupant' }}</div>
              <div class="resident-unit-meta">
                <span class="unit-code-chip">🏡 Unit {{ pass.resident.unit || '—' }}</span>
                <span class="host-role-badge">Host Resident</span>
              </div>
            </div>
          </div>

          <div v-else class="empty-host-state">
            <div class="empty-host-icon">🏢</div>
            <div>
              <div class="empty-host-title">Estate Administration Pass</div>
              <p class="empty-host-desc">This pass was authorized directly by the estate management or facilities team.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- ── RIGHT SIDEBAR COLUMN (35%) ── -->
      <div class="detail-col-side">
        <!-- 1. Pass Security Code & Reference Card -->
        <div class="card security-ref-card">
          <div class="ref-card-head">
            <span class="ref-eyebrow">PASS IDENTIFIER</span>
            <span class="ref-type-tag">{{ pass.type }}</span>
          </div>

          <div class="code-box">
            <span class="code-label">ULID Reference</span>
            <div class="code-value-row">
              <span class="code-text">{{ pass.ulid }}</span>
              <button
                type="button"
                class="code-copy-btn"
                title="Copy Pass Reference"
                @click="copyText(pass.ulid, 'Pass ULID')"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                  <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="gate-security-notice">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0A5C38" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Verified against Gate Security Terminal</span>
          </div>
        </div>

        <!-- 2. Interactive Stepper Timeline Card -->
        <div class="card timeline-card">
          <div class="timeline-head">
            <h3 class="timeline-title">Audit Trail & Timeline</h3>
            <span class="timeline-pulse-tag" :class="{ 'pulse-active': pass.status === 'On-site' }">
              {{ pass.status === 'On-site' ? 'Live on premise' : pass.status }}
            </span>
          </div>

          <div class="stepper-timeline">
            <!-- Step 1: Created -->
            <div class="step-item step--completed">
              <div class="step-marker">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
              </div>
              <div class="step-content">
                <div class="step-title">Pass Issued</div>
                <div class="step-timestamp">{{ fmtDatetime(pass.createdAt) }}</div>
                <p class="step-desc">Generated and dispatched to visitor</p>
              </div>
            </div>

            <!-- Step 2: Arrived -->
            <div class="step-item" :class="pass.arrivedAt ? 'step--completed' : 'step--pending'">
              <div class="step-marker">
                <svg v-if="pass.arrivedAt" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <div v-else class="step-dot-inner"></div>
              </div>
              <div class="step-content">
                <div class="step-title">Gate Arrival & Check-In</div>
                <div class="step-timestamp">{{ pass.arrivedAt ? fmtDatetime(pass.arrivedAt) : 'Awaiting arrival at gate' }}</div>
                <p class="step-desc">{{ pass.arrivedAt ? 'Scanned & admitted by security' : 'Visitor has not checked in yet' }}</p>
              </div>
            </div>

            <!-- Step 3: Exited -->
            <div class="step-item" :class="pass.exitedAt ? 'step--completed' : (pass.status === 'On-site' ? 'step--active' : 'step--pending')">
              <div class="step-marker">
                <svg v-if="pass.exitedAt" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                <div v-else-if="pass.status === 'On-site'" class="step-ping-dot"></div>
                <div v-else class="step-dot-inner"></div>
              </div>
              <div class="step-content">
                <div class="step-title">Gate Exit & Checkout</div>
                <div class="step-timestamp">{{ pass.exitedAt ? fmtDatetime(pass.exitedAt) : (pass.status === 'On-site' ? 'Currently on premises' : 'Pending departure') }}</div>
                <p class="step-desc">{{ pass.exitedAt ? 'Exited gate and cleared out' : 'Awaiting checkout scan' }}</p>
              </div>
            </div>

            <!-- Step 4: Revocation (if applicable) -->
            <div v-if="pass.revokedAt || pass.status === 'Revoked'" class="step-item step--revoked">
              <div class="step-marker step-marker--danger">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="3">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </div>
              <div class="step-content">
                <div class="step-title text-danger">Access Revoked</div>
                <div class="step-timestamp">{{ fmtDatetime(pass.revokedAt) }}</div>
                <p class="step-desc">Pass canceled by administrator or security</p>
              </div>
            </div>

            <!-- Step 5: Expiration -->
            <div class="step-item step--last">
              <div class="step-marker step-marker--amber">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5">
                  <circle cx="12" cy="12" r="10"/>
                  <polyline points="12 6 12 12 16 14"/>
                </svg>
              </div>
              <div class="step-content">
                <div class="step-title">Pass Expiration</div>
                <div class="step-timestamp">{{ fmtDatetime(pass.expiresAt) }}</div>
                <p class="step-desc">{{ isExpired(pass.expiresAt) ? 'Pass expired' : 'Valid until scheduled expiry' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Revoke Modal -->
    <div v-if="showRevokeConfirm" class="modal-overlay" @click.self="showRevokeConfirm = false">
      <div class="modal modal--sm">
        <div class="modal-header">
          <h3 class="modal-title text-danger">Revoke Visitor Access</h3>
          <button class="modal-close" @click="showRevokeConfirm = false">✕</button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to revoke pass for <strong>{{ pass?.visitorName }}</strong>?</p>
          <p class="modal-hint">The gate guard scanner will immediately flag and decline entry for this code.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showRevokeConfirm = false">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doRevoke">
            {{ saving ? 'Revoking…' : 'Revoke Pass' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <div v-if="showDeleteConfirm" class="modal-overlay" @click.self="showDeleteConfirm = false">
      <div class="modal modal--sm">
        <div class="modal-header">
          <h3 class="modal-title text-danger">Delete Pass Record</h3>
          <button class="modal-close" @click="showDeleteConfirm = false">✕</button>
        </div>
        <div class="modal-body">
          <p>Permanently delete this pass for <strong>{{ pass?.visitorName }}</strong>?</p>
          <p class="modal-hint">This operation cannot be reversed. Access history will be permanently cleared from records.</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="showDeleteConfirm = false">Cancel</button>
          <button class="btn btn-danger" :disabled="saving" @click="doDelete">
            {{ saving ? 'Deleting…' : 'Delete Record' }}
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
import client from '@/api/client'

const route = useRoute()
const router = useRouter()
const { showToast } = useToast()

const loading = ref(true)
const pass = ref<any>(null)
const saving = ref(false)
const showRevokeConfirm = ref(false)
const showDeleteConfirm = ref(false)

async function loadPass() {
  try {
    const res = await passesApi.getOne(route.params.id as string)
    pass.value = res.data.pass
  } catch {
    showToast('Pass record not found.', 'error')
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
    showToast('Visitor admitted through gate.', 'success')
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

function confirmRevoke() {
  showRevokeConfirm.value = true
}

async function doRevoke() {
  if (!pass.value) return
  saving.value = true
  try {
    await passesApi.revoke(pass.value.ulid)
    showToast('Pass revoked successfully.', 'success')
    showRevokeConfirm.value = false
    await loadPass()
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
    showToast('Pass record deleted.', 'success')
    router.push('/passes')
  } catch {
    showToast('Failed to delete pass.', 'error')
  } finally {
    saving.value = false
  }
}

function getInitials(name?: string | null): string {
  if (!name) return 'GP'
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

function statusPillClass(status: string) {
  return {
    'status--onsite':   status === 'On-site',
    'status--pending':  status === 'Pending',
    'status--exited':   ['Expired', 'Exited'].includes(status),
    'status--revoked':  ['Revoked', 'Declined'].includes(status),
    'status--flagged':  status === 'Item Flagged',
  }
}

function isExpired(expiresAt?: string | null): boolean {
  if (!expiresAt) return false
  return new Date(expiresAt).getTime() < Date.now()
}

function fmtDatetime(iso: string | null | undefined): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

function copyText(text: string, label: string) {
  navigator.clipboard.writeText(text).then(() => {
    showToast(`${label} copied to clipboard`, 'success')
  }).catch(() => {
    showToast('Failed to copy text', 'error')
  })
}

onMounted(loadPass)
</script>

<style scoped>
.pass-detail-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ── Top Navigation Bar ────────────────────────────── */
.page-top-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 16px;
  flex-wrap: wrap;
}

.breadcrumbs {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13.5px;
  color: #6B7280;
}

.bc-link {
  color: #6B7280;
  text-decoration: none;
  font-weight: 500;
  transition: color 140ms ease;
}

.bc-link:hover {
  color: #0A5C38;
}

.bc-sep {
  color: #D1D5DB;
}

.bc-current {
  color: #111827;
  font-weight: 700;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-danger-outline {
  border: 1.5px solid #FCA5A5;
  color: #DC2626;
  background: transparent;
}

.btn-danger-outline:hover {
  background: #FEE2E2;
  border-color: #DC2626;
}

/* ── Loading State ─────────────────────────────────── */
.detail-loading-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 80px 20px;
}

.loading-label {
  font-size: 14px;
  font-weight: 600;
  color: #6B7280;
}

/* ── Detail 2-Column Grid Layout ───────────────────── */
.detail-layout {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 20px;
  align-items: start;
}

.detail-col-main,
.detail-col-side {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ── Visitor Hero Profile Card ─────────────────────── */
.visitor-hero-card {
  padding: 24px;
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

.visitor-hero-head {
  display: flex;
  align-items: flex-start;
  gap: 18px;
  padding-bottom: 20px;
  border-bottom: 1px solid #F1F5F9;
}

.visitor-avatar-wrap {
  flex-shrink: 0;
}

.visitor-avatar {
  width: 58px;
  height: 58px;
  border-radius: 16px;
  background: linear-gradient(135deg, #0A5C38 0%, #16A34A 100%);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  font-weight: 800;
  letter-spacing: -0.02em;
  box-shadow: 0 4px 12px rgba(10, 92, 56, 0.16);
}

.visitor-identity-info {
  flex: 1;
  min-width: 0;
}

.visitor-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.visitor-name {
  font-size: 22px;
  font-weight: 800;
  color: #111827;
  font-family: var(--font-display, inherit);
  line-height: 1.2;
}

/* Status Pill */
.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentColor;
}

.status--onsite {
  background: #DCFCE7;
  color: #15803D;
}

.status--pending {
  background: #EFF6FF;
  color: #1D4ED8;
}

.status--exited {
  background: #F1F5F9;
  color: #475569;
}

.status--revoked {
  background: #FEE2E2;
  color: #B91C1C;
}

.status--flagged {
  background: #FEF3C7;
  color: #B45309;
}

.visitor-host-meta {
  margin-top: 6px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13.5px;
  color: #6B7280;
  flex-wrap: wrap;
}

.host-name {
  font-weight: 700;
  color: #374151;
}

.unit-badge {
  background: #ECFDF5;
  color: #0A5C38;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 12px;
}

/* Visitor Meta Grid */
.visitor-meta-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
  margin-top: 20px;
}

.meta-cell {
  background: #F8FAFC;
  border: 1px solid #F1F5F9;
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.meta-cell--wide {
  grid-column: 1 / -1;
}

.cell-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #64748B;
}

.cell-val-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.cell-val {
  font-size: 14px;
  font-weight: 700;
  color: #0F172A;
}

.cell-action-btn {
  background: transparent;
  border: none;
  color: #94A3B8;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  border-radius: 6px;
  transition: all 140ms ease;
}

.cell-action-btn:hover {
  background: #E2E8F0;
  color: #0F172A;
}

/* Vehicle Plate Pill */
.vehicle-plate-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #FFFFFF;
  border: 1.5px solid #1E293B;
  border-radius: 6px;
  padding: 3px 8px;
  width: fit-content;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.plate-flag {
  font-size: 12px;
}

.plate-code {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-weight: 800;
  font-size: 13px;
  letter-spacing: 0.08em;
  color: #0F172A;
}

/* Type badge */
.type-badge {
  display: inline-block;
  font-size: 12px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 8px;
  width: fit-content;
}

.type-onetime {
  background: #EFF6FF;
  color: #1D4ED8;
}

.type-recurring {
  background: #F3E8FF;
  color: #7E22CE;
}

/* Security Flagged / Clear */
.security-clear-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 700;
  color: #15803D;
}

.security-flag-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 700;
  color: #D97706;
}

.purpose-text {
  font-size: 13.5px;
  color: #334155;
  margin: 0;
  line-height: 1.45;
}

/* ── Host Resident Card ────────────────────────────── */
.host-resident-card {
  padding: 24px;
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
}

.card-section-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 16px;
}

.card-section-title {
  font-size: 16px;
  font-weight: 800;
  color: #0F172A;
}

.card-section-desc {
  margin-top: 3px;
  font-size: 12.5px;
  color: #64748B;
}

.host-profile-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  font-weight: 700;
  color: #0A5C38;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  padding: 5px 12px;
  border-radius: var(--btn-radius-sm, 8px);
  text-decoration: none;
  transition: all 140ms ease;
  white-space: nowrap;
}

.host-profile-link:hover {
  background: #D1FAE5;
  transform: translateX(1px);
}

.resident-summary-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 14px;
}

.resident-avatar-circle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #E2E8F0;
  color: #334155;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
}

.resident-name-title {
  font-size: 15px;
  font-weight: 700;
  color: #0F172A;
}

.resident-unit-meta {
  margin-top: 4px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.unit-code-chip {
  font-size: 12px;
  font-weight: 700;
  color: #0A5C38;
}

.host-role-badge {
  font-size: 11px;
  font-weight: 600;
  color: #64748B;
  background: #E2E8F0;
  padding: 2px 6px;
  border-radius: 4px;
}

.empty-host-state {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px;
  background: #F8FAFC;
  border-radius: 12px;
  border: 1px dashed #CBD5E1;
}

.empty-host-icon {
  font-size: 26px;
}

.empty-host-title {
  font-size: 14px;
  font-weight: 700;
  color: #1E293B;
}

.empty-host-desc {
  font-size: 12.5px;
  color: #64748B;
  margin: 2px 0 0;
}

/* ── Right Column: Pass Security Reference ─────────── */
.security-ref-card {
  padding: 20px;
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
}

.ref-card-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.ref-eyebrow {
  font-size: 11px;
  font-weight: 700;
  color: #64748B;
  letter-spacing: 0.08em;
}

.ref-type-tag {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #0A5C38;
  background: #ECFDF5;
  padding: 2px 8px;
  border-radius: 6px;
}

.code-box {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  padding: 10px 12px;
}

.code-label {
  font-size: 10.5px;
  font-weight: 700;
  color: #94A3B8;
  display: block;
  margin-bottom: 2px;
}

.code-value-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.code-text {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 12.5px;
  font-weight: 700;
  color: #0F172A;
  word-break: break-all;
}

.code-copy-btn {
  background: transparent;
  border: none;
  color: #94A3B8;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  border-radius: 6px;
  transition: all 140ms ease;
  flex-shrink: 0;
}

.code-copy-btn:hover {
  background: #E2E8F0;
  color: #0A5C38;
}

.gate-security-notice {
  margin-top: 14px;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  font-weight: 600;
  color: #0A5C38;
}

/* ── Right Column: Timeline Stepper ─────────────────── */
.timeline-card {
  padding: 20px;
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
}

.timeline-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
}

.timeline-title {
  font-size: 15px;
  font-weight: 800;
  color: #0F172A;
}

.timeline-pulse-tag {
  font-size: 11px;
  font-weight: 700;
  color: #64748B;
  background: #F1F5F9;
  padding: 3px 8px;
  border-radius: 6px;
}

.pulse-active {
  background: #DCFCE7;
  color: #15803D;
}

.stepper-timeline {
  display: flex;
  flex-direction: column;
  position: relative;
}

.step-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  position: relative;
  padding-bottom: 22px;
}

/* Connecting vertical line */
.step-item::before {
  content: '';
  position: absolute;
  top: 22px;
  left: 11px;
  bottom: 0;
  width: 2px;
  background: #E2E8F0;
}

.step-item.step--last::before {
  display: none;
}

.step-marker {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #94A3B8;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  z-index: 2;
  box-shadow: 0 0 0 3px #ffffff;
}

.step--completed .step-marker {
  background: #0A5C38;
}

.step--pending .step-marker {
  background: #F1F5F9;
  border: 1.5px solid #CBD5E1;
}

.step-dot-inner {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #94A3B8;
}

.step--active .step-marker {
  background: #10B981;
}

.step-ping-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #ffffff;
}

.step-marker--danger {
  background: #DC2626 !important;
}

.step-marker--amber {
  background: #D97706 !important;
}

.step-content {
  flex: 1;
}

.step-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #0F172A;
  line-height: 1.2;
}

.step-timestamp {
  font-size: 12px;
  color: #475569;
  margin-top: 2px;
  font-weight: 600;
}

.step-desc {
  font-size: 11.5px;
  color: #94A3B8;
  margin: 2px 0 0;
}

/* ── Modals ────────────────────────────────────────── */
.modal-hint {
  font-size: 12.5px;
  color: #64748B;
  margin-top: 6px;
}

@media (max-width: 960px) {
  .detail-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .visitor-meta-grid {
    grid-template-columns: 1fr;
  }
  .page-top-nav {
    flex-direction: column;
    align-items: stretch;
  }
  .header-actions {
    justify-content: flex-start;
  }
}
</style>
