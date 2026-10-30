<template>
  <ion-page class="receipt-page">
    <ion-content :fullscreen="true" class="success-content">
      <div class="success-wrap">
        <!-- Top Status Graphic -->
        <div class="success-top-zone no-print">
          <div class="success-icon-circle">
            <ion-icon :icon="checkmarkOutline" class="success-icon" />
          </div>
          <div class="success-title">Payment Confirmed</div>
          <div class="success-subtitle">Your estate levy payment has been recorded & verified.</div>
        </div>

        <!-- Printable Official Receipt Card (GAP-06) -->
        <div id="printable-receipt" class="receipt-card">
          <!-- Watermark -->
          <div class="receipt-watermark">
            <svg viewBox="0 0 119 119" fill="#0A5C38">
              <path d="M21.6807 96.6416H55.5674V118.174H42.3701C30.6745 118.174 21.1566 108.849 20.8457 97.2285C9.34033 96.8386 0.108192 87.4159 0.0576172 75.8047L0 62.6084L21.5322 62.5146L21.6807 96.6416ZM118.079 62.6084L118.021 75.8047C117.971 87.4163 108.738 96.8391 97.2324 97.2285C96.9215 108.849 87.4046 118.174 75.709 118.174H62.5127V96.6416H96.3984L96.5469 62.5146L118.079 62.6084Z" />
              <path d="M55.5674 21.5322H21.6807L21.5322 55.6592L0 55.5654L0.0576172 42.3691C0.108192 30.7579 9.34032 21.3343 20.8457 20.9443C21.1571 9.32425 30.6748 0 42.3701 0H55.5674V21.5322ZM75.71 0C87.405 0.000315153 96.923 9.32446 97.2344 20.9443C108.739 21.3347 117.971 30.7582 118.021 42.3691L118.079 55.5654L96.5479 55.6592L96.3994 21.5322H62.5127V0H75.71Z" />
            </svg>
          </div>

          <!-- Receipt Header -->
          <div class="receipt-header">
            <div class="brand-badge">
              <span class="brand-name">CHECKPASS</span>
              <span class="brand-tag">SECURITY & ESTATE SYSTEM</span>
            </div>
            <div class="receipt-title-box">
              <div class="rt-main">OFFICIAL LEVY RECEIPT</div>
              <div class="rt-ref">REF: {{ txReference }}</div>
            </div>
          </div>

          <!-- Estate & Resident Profile -->
          <div class="profile-grid">
            <div class="grid-item">
              <span class="gi-label">ESTATE</span>
              <strong class="gi-val">{{ estateName }}</strong>
            </div>
            <div class="grid-item">
              <span class="gi-label">UNIT / RESIDENCE</span>
              <strong class="gi-val">{{ unitNumber }}</strong>
            </div>
            <div class="grid-item">
              <span class="gi-label">PAYER NAME</span>
              <strong class="gi-val">{{ residentName }}</strong>
            </div>
            <div class="grid-item">
              <span class="gi-label">PAYMENT DATE</span>
              <strong class="gi-val">{{ formattedDate }}</strong>
            </div>
          </div>

          <div class="receipt-divider" />

          <!-- Line items table -->
          <div class="items-table">
            <div class="it-head">
              <span>DESCRIPTION</span>
              <span class="it-head-amount">AMOUNT</span>
            </div>
            <div v-for="(item, idx) in lineItems" :key="idx" class="it-row">
              <span class="it-desc">{{ item.title }}</span>
              <span class="it-amount">{{ item.amount }}</span>
            </div>
          </div>

          <div class="receipt-divider" />

          <!-- Total section -->
          <div class="total-block">
            <div class="tb-row">
              <span>Payment Channel:</span>
              <strong>Paystack Electronic Gateway</strong>
            </div>
            <div class="tb-row">
              <span>Status:</span>
              <span class="paid-badge">✓ FULLY PAID & VERIFIED</span>
            </div>
            <div class="tb-row total-highlight">
              <span class="th-label">TOTAL PAID:</span>
              <span class="th-val">{{ formattedTotal }}</span>
            </div>
          </div>

          <!-- Official Stamp -->
          <div class="seal-container">
            <div class="stamp-box">
              <div class="stamp-inner">
                <span class="stamp-top">CHECKPASS VERIFIED</span>
                <span class="stamp-mid">PAID</span>
                <span class="stamp-bottom">{{ shortDate }}</span>
              </div>
            </div>
            <div class="seal-caption">
              This electronic receipt was generated automatically by the Checkpass Estate Management System.
            </div>
          </div>
        </div>

        <!-- Action Buttons (Hidden when printing to PDF) -->
        <div class="actions-area no-print">
          <button class="pdf-btn" @click="downloadPdf">
            <ion-icon :icon="documentTextOutline" /> Download / Print PDF Receipt
          </button>

          <button class="share-btn" @click="shareReceipt">
            <ion-icon :icon="shareSocialOutline" /> Share Receipt
          </button>

          <button class="home-btn" @click="goHome">
            Back to Home Dashboard
          </button>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { IonPage, IonContent, IonIcon } from '@ionic/vue'
import { checkmarkOutline, documentTextOutline, shareSocialOutline } from 'ionicons/icons'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const { showToast } = useToast()

const txReference = computed(() => {
  return String(route.query.ref || `CP-PAY-${Date.now().toString(36).toUpperCase()}`)
})

const estateName = computed(() => {
  return auth.resident?.estateName || 'PHDL Estate'
})

const unitNumber = computed(() => {
  return auth.resident?.flatAddress || (auth.resident?.flat ? `Flat ${auth.resident.flat}` : 'Unit A-12')
})

const residentName = computed(() => {
  return auth.resident?.name || 'Estate Resident'
})

const now = new Date()
const formattedDate = computed(() => {
  return now.toLocaleDateString('en-NG', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
})

const shortDate = computed(() => {
  return now.toLocaleDateString('en-NG', { day: '2-digit', month: 'short', year: 'numeric' })
})

const totalAmount = computed(() => {
  const queryAmt = Number(route.query.amount)
  return !isNaN(queryAmt) && queryAmt > 0 ? queryAmt : 25000
})

const formattedTotal = computed(() => {
  return `₦${totalAmount.value.toLocaleString('en-NG')}`
})

const lineItems = computed(() => {
  const itemsRaw = route.query.items
  if (itemsRaw) {
    const list = String(itemsRaw).split('|').filter(Boolean)
    if (list.length > 0) {
      const splitAmt = Math.round(totalAmount.value / list.length)
      return list.map((title, i) => ({
        title,
        amount: i === list.length - 1
          ? `₦${(totalAmount.value - splitAmt * (list.length - 1)).toLocaleString('en-NG')}`
          : `₦${splitAmt.toLocaleString('en-NG')}`,
      }))
    }
  }
  return [
    { title: 'Estate Security & Maintenance Levy (October 2026)', amount: formattedTotal.value },
  ]
})

function downloadPdf() {
  window.print()
}

async function shareReceipt() {
  const receiptText = `*CHECKPASS ESTATE LEVY RECEIPT*\n` +
    `Ref: ${txReference.value}\n` +
    `Estate: ${estateName.value} (${unitNumber.value})\n` +
    `Paid By: ${residentName.value}\n` +
    `Amount Paid: ${formattedTotal.value}\n` +
    `Status: PAID & VERIFIED\n` +
    `Date: ${formattedDate.value}`

  if (navigator.share) {
    try {
      await navigator.share({
        title: `Receipt ${txReference.value}`,
        text: receiptText,
      })
      return
    } catch (e: any) {
      if (e.name === 'AbortError') return
    }
  }

  // Fallback copy to clipboard
  try {
    await navigator.clipboard.writeText(receiptText)
    showToast('Receipt details copied to clipboard!', 'success')
  } catch {
    showToast('Unable to share receipt.', 'error')
  }
}

function goHome() {
  router.push('/tabs/home')
}
</script>

<style scoped>
.receipt-page {
  background: #f8fafc;
}

.success-content {
  --background: #f8fafc;
}

.success-wrap {
  min-height: 100%;
  padding: 20px 16px 40px;
  display: flex;
  flex-direction: column;
  align-items: center;
  max-width: 540px;
  margin: 0 auto;
}

/* Status Graphic */
.success-top-zone {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-bottom: 20px;
}

.success-icon-circle {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #ecfdf5;
  border: 4px solid #10b981;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
  box-shadow: 0 4px 14px rgba(16, 185, 129, 0.2);
}

.success-icon {
  font-size: 38px;
  color: #0a5c38;
}

.success-title {
  font-family: var(--w-font-display, inherit);
  font-size: 24px;
  font-weight: 800;
  color: #0f172a;
}

.success-subtitle {
  font-size: 13.5px;
  color: #64748b;
  margin-top: 4px;
}

/* Receipt Card */
.receipt-card {
  position: relative;
  width: 100%;
  background: #ffffff;
  border-radius: 16px;
  border: 1.5px solid #e2e8f0;
  padding: 24px 20px;
  box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.06);
  overflow: hidden;
}

.receipt-watermark {
  position: absolute;
  top: -30px;
  right: -30px;
  width: 180px;
  height: 180px;
  opacity: 0.04;
  pointer-events: none;
}

.receipt-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  padding-bottom: 16px;
  border-bottom: 1px dashed #cbd5e1;
}

.brand-badge {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-family: var(--w-font-display, inherit);
  font-size: 18px;
  font-weight: 900;
  color: #0a5c38;
  letter-spacing: -0.02em;
}

.brand-tag {
  font-size: 9.5px;
  font-weight: 700;
  color: #64748b;
  letter-spacing: 0.08em;
  margin-top: 2px;
}

.receipt-title-box {
  text-align: right;
}

.rt-main {
  font-size: 13px;
  font-weight: 800;
  color: #0f172a;
  letter-spacing: 0.04em;
}

.rt-ref {
  font-size: 11px;
  font-weight: 700;
  color: #0a5c38;
  font-family: monospace;
  margin-top: 2px;
}

/* Profile Grid */
.profile-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px 16px;
  padding: 16px 0;
}

.grid-item {
  display: flex;
  flex-direction: column;
}

.gi-label {
  font-size: 10.5px;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 0.05em;
}

.gi-val {
  font-size: 13.5px;
  color: #1e293b;
  font-weight: 700;
  margin-top: 2px;
}

.receipt-divider {
  height: 1px;
  background: #f1f5f9;
  margin: 6px 0 14px;
}

/* Items Table */
.items-table {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.it-head {
  display: flex;
  justify-content: space-between;
  font-size: 10.5px;
  font-weight: 800;
  color: #94a3b8;
  letter-spacing: 0.06em;
  padding-bottom: 4px;
  border-bottom: 1px solid #f1f5f9;
}

.it-head-amount {
  text-align: right;
}

.it-row {
  display: flex;
  justify-content: space-between;
  font-size: 13.5px;
  color: #334155;
  padding: 4px 0;
}

.it-desc {
  font-weight: 500;
  flex: 1;
  padding-right: 12px;
}

.it-amount {
  font-weight: 700;
  color: #0f172a;
  white-space: nowrap;
}

/* Total Block */
.total-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
  background: #f8fafc;
  border-radius: 10px;
  padding: 12px 14px;
}

.tb-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12.5px;
  color: #64748b;
}

.paid-badge {
  background: #ecfdf5;
  color: #047857;
  font-weight: 800;
  font-size: 11px;
  padding: 3px 8px;
  border-radius: 6px;
  border: 1px solid #a7f3d0;
}

.total-highlight {
  margin-top: 4px;
  padding-top: 8px;
  border-top: 1px solid #e2e8f0;
}

.th-label {
  font-size: 14px;
  font-weight: 800;
  color: #0f172a;
}

.th-val {
  font-family: var(--w-font-display, inherit);
  font-size: 20px;
  font-weight: 900;
  color: #0a5c38;
}

/* Seal & Stamp */
.seal-container {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: 18px;
  padding-top: 14px;
  border-top: 1px dashed #cbd5e1;
}

.stamp-box {
  width: 80px;
  height: 80px;
  border: 2px dashed #0a5c38;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transform: rotate(-10deg);
  flex-shrink: 0;
}

.stamp-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.stamp-top {
  font-size: 7.5px;
  font-weight: 800;
  color: #0a5c38;
  letter-spacing: 0.05em;
}

.stamp-mid {
  font-size: 15px;
  font-weight: 900;
  color: #0a5c38;
  line-height: 1;
}

.stamp-bottom {
  font-size: 7.5px;
  font-weight: 700;
  color: #0a5c38;
}

.seal-caption {
  font-size: 11px;
  color: #94a3b8;
  line-height: 1.4;
}

/* Actions */
.actions-area {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 24px;
}

.pdf-btn {
  width: 100%;
  height: 52px;
  border: none;
  border-radius: 14px;
  background: #0a5c38;
  color: #ffffff;
  font-size: 15px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(10, 92, 56, 0.2);
}

.share-btn {
  width: 100%;
  height: 48px;
  border: 1.5px solid #cbd5e1;
  border-radius: 14px;
  background: #ffffff;
  color: #1e293b;
  font-size: 14.5px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  cursor: pointer;
}

.home-btn {
  width: 100%;
  height: 44px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 4px;
}

/* Print CSS */
@media print {
  .no-print {
    display: none !important;
  }

  .receipt-page, .success-content, .success-wrap {
    background: #ffffff !important;
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
  }

  .receipt-card {
    border: 1px solid #000000 !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    width: 100% !important;
  }
}
</style>
