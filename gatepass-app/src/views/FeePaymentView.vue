<template>
  <ion-page>
    <ion-content :fullscreen="true" class="pay-content">
      <div class="pay-wrap">
        <div class="pay-title">Make Payment</div>
        <div class="pay-subtitle">Select fees to pay</div>

        <div class="pay-list">
          <button
            v-for="item in feeItems"
            :key="item.id"
            class="pay-item"
            :disabled="item.status !== 'Due'"
            :class="{ selected: selectedIds.has(item.id) }"
            @click="toggle(item.id)"
          >
            <div class="pay-item-top">
              <div class="pay-item-amount">{{ formatAmount(item.amount) }}</div>
              <span class="status-pill" :class="statusClass(item.status)">{{ item.status }}</span>
              <div class="check-wrap" :class="{ checked: selectedIds.has(item.id) }">
                <ion-icon v-if="selectedIds.has(item.id)" :icon="checkmarkOutline" />
              </div>
            </div>

            <div class="pay-meta-row">
              <div class="pay-meta">
                <ion-icon :icon="cardOutline" />
                <span>{{ item.title }}</span>
              </div>
              <div class="pay-meta">
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
import client from '@/api/client'

type FeeItem = {
  id: string
  amount: number
  title: string
  date: string
  status: 'Paid' | 'Pending' | 'Due'
}

const feeItems = ref<FeeItem[]>([
])

const loading = ref(false)
const router = useRouter()

const selectedIds = ref<Set<string>>(new Set())

async function loadFeeItems() {
  loading.value = true
  try {
    const res = await client.get('/v1/fees')
    const items: FeeItem[] = (res.data.fees || []).map((item: {
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
  } catch {
    feeItems.value = []
    selectedIds.value = new Set()
  } finally {
    loading.value = false
  }
}

onMounted(loadFeeItems)

const totalSelected = computed(() => {
  return feeItems.value
    .filter((item) => selectedIds.value.has(item.id))
    .reduce((sum, item) => sum + item.amount, 0)
})

function toggle(id: string) {
  const item = feeItems.value.find((fee) => fee.id === id)
  if (!item || item.status !== 'Due') return

  const next = new Set(selectedIds.value)
  if (next.has(id)) next.delete(id)
  else next.add(id)
  selectedIds.value = next
}

function statusClass(status: FeeItem['status']) {
  if (status === 'Paid') return 'status-paid'
  if (status === 'Pending') return 'status-pending'
  return 'status-due'
}

function formatAmount(amount: number) {
  return `₦${amount.toLocaleString('en-NG')}`
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
.pay-title {
  font-family: var(--w-font-display);
  font-size: 26px;
  font-weight: 700;
  color: #1f2531;
  text-align: center;
}
.pay-subtitle {
  margin-top: 12px;
  font-size: 15px;
  font-weight: 400;
  color: var(--w-muted);
  text-align: center;
  font-family: var(--w-font-body);
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
  font-size: 31px;
  line-height: 1;
  font-weight: 700;
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
  gap: 16px;
}
.pay-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #6f7685;
  font-size: 13px;
  font-family: var(--w-font-body);
}
.pay-meta ion-icon { font-size: 14px; }

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
