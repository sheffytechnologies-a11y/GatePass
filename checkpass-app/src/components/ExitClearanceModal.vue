<template>
  <ion-modal
    :is-open="isOpen"
    @didDismiss="onDismiss"
    :initial-breakpoint="0.9"
    :breakpoints="[0, 0.9]"
  >
    <ion-page class="clearance-page">
      <div class="sheet-header">
        <div class="sheet-handle" />
        <div class="header-row">
          <div>
            <div class="sheet-title">Gate Exit Clearance</div>
            <div class="sheet-sub">Verify departure & items before gate release</div>
          </div>
          <button class="close-btn" @click="$emit('close')">✕</button>
        </div>
      </div>

      <ion-content class="sheet-content">
        <div class="sheet-body">
          <!-- Visitor summary card -->
          <div class="visitor-banner">
            <div class="vb-main">
              <div class="vb-avatar">{{ visitorInitials }}</div>
              <div class="vb-info">
                <div class="vb-name">{{ pass.visitorName }}</div>
                <div class="vb-sub">Host: {{ pass.hostUnit }} · {{ pass.hostName }}</div>
                <div v-if="pass.vehiclePlate" class="vb-plate">🚗 {{ pass.vehiclePlate }}</div>
              </div>
            </div>
          </div>

          <!-- Section A: Declared items -->
          <div v-if="pass.flaggedItems && pass.flaggedItems.length > 0" class="inspection-section">
            <div class="section-badge warn">
              <span>⚠️ DECLARED EXIT ITEMS ({{ pass.flaggedItems.length }})</span>
            </div>
            <div class="inspection-desc">
              Resident flagged items departing with this visitor. Each item must be physically verified by security before gate release.
            </div>

            <div class="items-list">
              <div
                v-for="item in pass.flaggedItems"
                :key="item.id"
                class="declared-item-card"
                :class="{ verified: isItemVerified(item.id) }"
                @click="toggleItemVerified(item.id)"
              >
                <div class="item-img-wrap">
                  <img v-if="item.photoUrl" :src="item.photoUrl" alt="Item" class="item-img" />
                  <span v-else class="item-icon-ph">📦</span>
                </div>
                <div class="item-details">
                  <div class="item-desc">{{ item.description }}</div>
                  <div class="item-meta">{{ item.flaggedAt ? fmtRelative(item.flaggedAt) : 'Flagged by host' }}</div>
                </div>
                <div class="item-check" :class="{ active: isItemVerified(item.id) }">
                  <span v-if="isItemVerified(item.id)">✓</span>
                </div>
              </div>
            </div>

            <div v-if="!allDeclaredItemsVerified" class="unverified-hint">
              Tap each declared item above after physical inspection to clear it.
            </div>
          </div>

          <!-- Section B: Routine gate checks (if no items declared) -->
          <div v-else class="inspection-section">
            <div class="section-badge ok">
              <span>🛡️ ROUTINE DEPARTURE VERIFICATION</span>
            </div>
            <div class="inspection-desc">
              No exit items were flagged by the resident for this pass. Confirm routine gate departure checks:
            </div>

            <div class="checklist">
              <label class="check-row" @click="checkTrunk = !checkTrunk">
                <div class="custom-checkbox" :class="{ checked: checkTrunk }">
                  <span v-if="checkTrunk">✓</span>
                </div>
                <span class="check-label">Visual baggage & trunk clearance completed</span>
              </label>

              <label v-if="pass.vehiclePlate" class="check-row" @click="checkPlate = !checkPlate">
                <div class="custom-checkbox" :class="{ checked: checkPlate }">
                  <span v-if="checkPlate">✓</span>
                </div>
                <span class="check-label">Vehicle plate confirmed ({{ pass.vehiclePlate }})</span>
              </label>
            </div>
          </div>

          <!-- Section C: Guard remarks -->
          <div class="notes-section">
            <label class="notes-label">SECURITY LOG REMARKS (OPTIONAL)</label>
            <input
              v-model="guardNotes"
              type="text"
              placeholder="e.g. Regular contractor tools inspected and cleared"
              class="notes-input"
            />
          </div>

          <!-- Section D: Action Button -->
          <div class="submit-area">
            <button
              class="confirm-exit-btn"
              :disabled="!canConfirmExit || submitting"
              @click="submitExit"
            >
              <ion-spinner v-if="submitting" name="crescent" color="light" style="width:20px;height:20px" />
              <template v-else>🚪 Confirm Exit & Open Barrier</template>
            </button>
            <div class="submit-sub">Visitor status will transition to Exited with gate timestamp.</div>
          </div>
        </div>
      </ion-content>
    </ion-page>
  </ion-modal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { IonModal, IonPage, IonContent, IonSpinner } from '@ionic/vue'
import type { Pass } from '@/types'
import { passesApi } from '@/api/passes'
import { useToast } from '@/composables/useToast'

const props = defineProps<{
  isOpen: boolean
  pass: Pass
}>()

const emit = defineEmits<{
  close: []
  exited: []
}>()

const { showToast } = useToast()

const submitting = ref(false)
const guardNotes = ref('')
const verifiedItemIds = ref<Set<string>>(new Set())
const checkTrunk = ref(true)
const checkPlate = ref(true)

const visitorInitials = computed(() => {
  return props.pass.visitorName
    .split(' ')
    .map(w => w[0])
    .slice(0, 2)
    .join('')
    .toUpperCase() || 'V'
})

function isItemVerified(id: string): boolean {
  return verifiedItemIds.value.has(id)
}

function toggleItemVerified(id: string) {
  if (verifiedItemIds.value.has(id)) {
    verifiedItemIds.value.delete(id)
  } else {
    verifiedItemIds.value.add(id)
  }
}

const allDeclaredItemsVerified = computed(() => {
  const items = props.pass.flaggedItems || []
  if (items.length === 0) return true
  return items.every(item => verifiedItemIds.value.has(item.id))
})

const canConfirmExit = computed(() => {
  const items = props.pass.flaggedItems || []
  if (items.length > 0) {
    return allDeclaredItemsVerified.value
  }
  return checkTrunk.value && (!props.pass.vehiclePlate || checkPlate.value)
})

async function submitExit() {
  if (!canConfirmExit.value || submitting.value) return
  submitting.value = true

  try {
    await passesApi.markExited(props.pass.id)
    showToast(`Visitor exit recorded. Barrier opened.`, 'success')
    emit('exited')
    emit('close')
  } catch (err: any) {
    showToast(err.response?.data?.message || 'Failed to record exit at gate.', 'error')
  } finally {
    submitting.value = false
  }
}

function onDismiss() {
  emit('close')
}

function fmtRelative(iso: string) {
  const diff = (Date.now() - new Date(iso).getTime()) / 1000
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
  return `${Math.floor(diff / 3600)}h ago`
}
</script>

<style scoped>
.clearance-page {
  background: #ffffff;
}

.sheet-header {
  padding: 12px 16px 14px;
  border-bottom: 1px solid #e2e8f0;
  background: #ffffff;
}

.sheet-handle {
  width: 38px;
  height: 4px;
  border-radius: 99px;
  background: #cbd5e1;
  margin: 0 auto 10px;
}

.header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sheet-title {
  font-family: var(--w-font-display, inherit);
  font-size: 19px;
  font-weight: 800;
  color: #0f172a;
}

.sheet-sub {
  font-size: 12px;
  color: #64748b;
  margin-top: 2px;
}

.close-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: #f1f5f9;
  color: #475569;
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.sheet-content {
  --background: #f8fafc;
}

.sheet-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Visitor Banner */
.visitor-banner {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 14px;
}

.vb-main {
  display: flex;
  align-items: center;
  gap: 12px;
}

.vb-avatar {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: linear-gradient(135deg, #0a5c38, #059669);
  color: #ffffff;
  font-weight: 800;
  font-size: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.vb-info {
  flex: 1;
  min-width: 0;
}

.vb-name {
  font-size: 16px;
  font-weight: 700;
  color: #0f172a;
}

.vb-sub {
  font-size: 13px;
  color: #64748b;
  margin-top: 1px;
}

.vb-plate {
  margin-top: 4px;
  display: inline-block;
  padding: 2px 8px;
  background: #f1f5f9;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 700;
  color: #0f172a;
}

/* Inspection Section */
.inspection-section {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.section-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.05em;
  padding: 4px 8px;
  border-radius: 6px;
  width: fit-content;
}

.section-badge.warn {
  background: #fef3c7;
  color: #b45309;
}

.section-badge.ok {
  background: #ecfdf5;
  color: #047857;
}

.inspection-desc {
  font-size: 13px;
  color: #475569;
  line-height: 1.45;
}

/* Declared items cards */
.items-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 4px;
}

.declared-item-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.declared-item-card.verified {
  background: #ecfdf5;
  border-color: #10b981;
}

.item-img-wrap {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  flex-shrink: 0;
}

.item-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item-icon-ph {
  font-size: 20px;
}

.item-details {
  flex: 1;
  min-width: 0;
}

.item-desc {
  font-size: 13px;
  font-weight: 600;
  color: #0f172a;
}

.item-meta {
  font-size: 11px;
  color: #64748b;
  margin-top: 2px;
}

.item-check {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  border: 2px solid #cbd5e1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 800;
  color: #ffffff;
  flex-shrink: 0;
}

.item-check.active {
  background: #10b981;
  border-color: #10b981;
}

.unverified-hint {
  font-size: 12px;
  color: #d97706;
  font-weight: 600;
}

/* Checklist for routine */
.checklist {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 4px;
}

.check-row {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
}

.custom-checkbox {
  width: 20px;
  height: 20px;
  border-radius: 6px;
  border: 2px solid #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  font-size: 12px;
  font-weight: 800;
  flex-shrink: 0;
}

.custom-checkbox.checked {
  background: #0a5c38;
  border-color: #0a5c38;
}

.check-label {
  font-size: 13px;
  color: #1e293b;
  font-weight: 500;
}

/* Guard Notes */
.notes-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.notes-label {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.05em;
  color: #64748b;
}

.notes-input {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 13px;
  color: #0f172a;
  outline: none;
  background: #ffffff;
}

.notes-input:focus {
  border-color: #0a5c38;
}

/* Submit */
.submit-area {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 6px;
  padding-bottom: 24px;
}

.confirm-exit-btn {
  width: 100%;
  height: 52px;
  border-radius: 14px;
  border: none;
  background: #0a5c38;
  color: #ffffff;
  font-size: 16px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(10, 92, 56, 0.25);
  transition: opacity 0.15s ease;
}

.confirm-exit-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.submit-sub {
  text-align: center;
  font-size: 11px;
  color: #64748b;
}
</style>
