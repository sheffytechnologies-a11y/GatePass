<template>
  <ion-page>
    <ion-content :fullscreen="true" class="pay-content">
      <div class="pay-wrap">
        <div class="pay-top-bar">
          <button class="pay-back-btn" @click="goBack" aria-label="Go back">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
          </button>
          <div class="pay-title">Make Payment</div>
          <div class="pay-top-spacer"></div>
        </div>
        <div class="pay-subtitle">Select fees to pay</div>

        <div v-if="payableFeeItems.length === 0 && !loading" class="pay-empty-wrap">
          <EmptyState
            icon="🎉"
            heading="All Dues Cleared"
            body="You have no outstanding fees to pay. All your estate levies are up to date!"
            cta-label="Back to Dues"
            @cta="router.push('/tabs/fees')"
          />
        </div>

        <div v-else class="pay-list">
          <button
            v-for="item in payableFeeItems"
            :key="item.id"
            class="pay-item"
            :class="{ selected: selectedIds.has(item.id) }"
            @click="toggle(item.id)"
          >
            <div class="pay-item-top">
              <div class="pay-item-amount">{{ formatAmount(item.amount) }}</div>
              <span class="status-pill status-due">Due</span>
              <div class="check-wrap" :class="{ checked: selectedIds.has(item.id) }">
                <ion-icon v-if="selectedIds.has(item.id)" :icon="checkmarkOutline" />
              </div>
            </div>

            <div class="pay-meta-row">
              <div class="pay-meta pay-meta-title">
                <ion-icon :icon="cardOutline" />
                <span>{{ item.title }}</span>
              </div>
              <div class="pay-meta pay-meta-date">
                <ion-icon :icon="calendarOutline" />
                <span>{{ item.date }}</span>
              </div>
            </div>
          </button>
        </div>

        <div class="total-row">
          <span>Total:</span>
          <span class="total-value">{{ formatAmount(totalSelected) }}</span>
        </div>

        <button class="continue-btn" :disabled="totalSelected === 0" @click="proceedToDetails">Continue</button>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { IonPage, IonContent, IonIcon } from '@ionic/vue'
import { calendarOutline, cardOutline, checkmarkOutline } from 'ionicons/icons'
import EmptyState from '@/components/EmptyState.vue'
import client from '@/api/client'

type FeeItem = {
  id: string
  amount: number
  title: string
  date: string
  status: 'Paid' | 'Pending' | 'Due'
}

const DEFAULT_PAYMENT_FEES: FeeItem[] = [
  { id: 'fee-101', title: 'Estate Security & Maintenance Levy', amount: 21000, date: '2026-11-01', status: 'Due' },
  { id: 'fee-102', title: 'Central Diesel & Energy Surcharge', amount: 12000, date: '2026-11-01', status: 'Due' },
]

const feeItems = ref<FeeItem[]>(DEFAULT_PAYMENT_FEES)

const loading = ref(false)
const router = useRouter()

const payableFeeItems = computed(() => {
  return feeItems.value.filter((item) => item.status === 'Due')
})

const selectedIds = ref<Set<string>>(new Set(['fee-101', 'fee-102']))

async function loadFeeItems() {
  loading.value = true
  try {
    const res = await client.get('/v1/fees')
    const raw = res.data?.fees || []
    if (raw.length > 0) {
      const items: FeeItem[] = raw.map((item: {
        id: string
        amount: number
        title: string
        date: string
        status?: string
      }) => ({
        id: item.id,
        amount: Number(item.amount || 0),
        title: item.title,
        date: item.date,
        status: item.status === 'Paid' || item.status === 'Pending' ? item.status : 'Due',
      }))

      feeItems.value = items
      selectedIds.value = new Set(items.filter((item: FeeItem) => item.status === 'Due').map((item: FeeItem) => item.id))
    } else {
      feeItems.value = DEFAULT_PAYMENT_FEES
      selectedIds.value = new Set(['fee-101', 'fee-102'])
    }
  } catch {
    feeItems.value = DEFAULT_PAYMENT_FEES
    selectedIds.value = new Set(['fee-101', 'fee-102'])
  } finally {
    loading.value = false
  }
}

onMounted(loadFeeItems)

const totalSelected = computed(() => {
  return payableFeeItems.value
    .filter((item) => selectedIds.value.has(item.id))
    .reduce((sum, item) => sum + item.amount, 0)
})

function toggle(id: string) {
  const item = payableFeeItems.value.find((fee) => fee.id === id)
  if (!item) return

  const next = new Set(selectedIds.value)
  if (next.has(id)) next.delete(id)
  else next.add(id)
  selectedIds.value = next
}

function formatAmount(amount: number) {
  return `₦${amount.toLocaleString('en-NG')}`
}

function goBack() {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/tabs/fees')
  }
}

function proceedToDetails() {
  const ids = Array.from(selectedIds.value)
  if (ids.length === 0) return
  router.push({ path: '/fees/payment/details', query: { ids: ids.join(',') } })
}
</script>

<style scoped>
.pay-content { --background: var(--w-bg); }
.pay-wrap {
  padding: 18px 16px 34px;
  display: flex;
  flex-direction: column;
  min-height: 100%;
}
.pay-top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  margin-bottom: 2px;
}
.pay-back-btn {
  background: #FFFFFF;
  border: 1px solid var(--w-border);
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1f2531;
  cursor: pointer;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: transform 0.15s ease, background 0.15s ease;
}
.pay-back-btn:active {
  transform: scale(0.94);
  background: #F1F4F2;
}
.pay-top-spacer {
  width: 36px;
}
.pay-title {
  font-family: var(--w-font-display);
  font-size: 24px;
  font-weight: 700;
  color: #1f2531;
  text-align: center;
  flex: 1;
}
.pay-subtitle {
  margin-top: 6px;
  font-size: 14.5px;
  font-weight: 400;
  color: var(--w-muted);
  text-align: center;
  font-family: var(--w-font-body);
}
.pay-empty-wrap {
  margin-top: 24px;
}
.pay-list {
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.pay-item {
  border: 1px solid var(--w-border);
  border-radius: var(--w-radius-md);
  background: var(--w-surface);
  padding: 15px 16px;
  text-align: left;
  box-shadow: 0 0 0 2px transparent;
}
.pay-item:disabled {
  opacity: 0.72;
}
.pay-item.selected {
  border-color: var(--w-primary);
  box-shadow: 0 0 0 1px var(--w-primary);
}
.pay-item-top {
  display: flex;
  align-items: center;
  gap: 10px;
}
.pay-item-amount {
  color: #232833;
  font-family: var(--w-font-display);
  font-size: 19px;
  line-height: 1.2;
  font-weight: 700;
  letter-spacing: -0.01em;
}
.status-pill {
  margin-left: auto;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
  font-family: var(--w-font-body);
}
.status-paid { background: #d8f0d3; color: #2f9c44; }
.status-pending { background: #f2e6c8; color: #ce9700; }
.status-due { background: #f3d4d4; color: #ff4c4c; }
.check-wrap {
  width: 25px;
  height: 25px;
  border-radius: 7px;
  border: 2px solid #b8bec8;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
}
.check-wrap.checked {
  background: #0b6c40;
  border-color: #0b6c40;
}
.check-wrap ion-icon { font-size: 16px; }

.pay-meta-row {
  margin-top: 10px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.pay-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #6f7685;
  font-size: 13px;
  font-family: var(--w-font-body);
}
.pay-meta-title {
  flex: 1;
  min-width: 0;
}
.pay-meta-title span {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.pay-meta-date {
  flex-shrink: 0;
  white-space: nowrap;
  font-size: 12.5px;
}
.pay-meta ion-icon {
  flex-shrink: 0;
  font-size: 14px;
}

.total-row {
  margin-top: 14px;
  display: flex;
  justify-content: flex-end;
  align-items: baseline;
  gap: 8px;
  font-size: 15px;
  font-weight: 700;
  color: #273043;
  font-family: var(--w-font-body);
}
.total-value {
  font-family: var(--w-font-display);
  font-size: 26px;
  line-height: 1;
}

.continue-btn {
  margin-top: auto;
  width: 100%;
  border: none;
  border-radius: 14px;
  background: #0b6c40;
  color: #fff;
  font-size: 16px;
  font-family: var(--w-font-body);
  font-weight: 700;
  line-height: 1;
  height: 52px;
  padding: 0 16px;
}
.continue-btn:disabled {
  opacity: 0.5;
}
</style>
