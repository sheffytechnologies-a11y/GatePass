<template>
  <ion-page>
    <ion-content :fullscreen="true" class="fees-content">
      <div class="fees-wrap">
        <div class="outstanding-card">
          <div>
            <div class="outstanding-amount">{{ formatAmount(totalOutstanding) }}</div>
            <div class="outstanding-label">Total Outstanding</div>
          </div>
          <button class="pay-btn" @click="goToPayment">Make Payment</button>
        </div>

        <div class="filter-row">
          <button
            v-for="option in filterOptions"
            :key="option"
            class="filter-chip"
            :class="{ active: activeFilter === option }"
            @click="activeFilter = option"
          >
            {{ option }}
          </button>
        </div>

        <div v-if="loading" class="fees-empty">Loading fees...</div>
        <div v-else-if="filteredFees.length === 0" class="fees-empty">No fees found.</div>
        <div v-else class="fee-list">
          <div v-for="item in filteredFees" :key="item.id" class="fee-item">
            <div class="fee-top-row">
              <div class="fee-amount">{{ formatAmount(item.amount) }}</div>
              <span class="status-pill" :class="statusClass(item.status)">{{ item.status }}</span>
            </div>

            <div class="fee-meta-row">
              <div class="fee-meta">
                <ion-icon :icon="cardOutline" />
                <span>{{ item.title }}</span>
              </div>
              <div class="fee-meta">
                <ion-icon :icon="calendarOutline" />
                <span>{{ item.date }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { IonPage, IonContent, IonIcon } from '@ionic/vue'
import { cardOutline, calendarOutline } from 'ionicons/icons'
import client from '@/api/client'

type FeeStatus = 'Paid' | 'Pending' | 'Due'

type FeeRow = {
  id: string
  amount: number
  title: string
  date: string
  status: FeeStatus
}

const fees = ref<FeeRow[]>([])
const loading = ref(false)

const filterOptions = ['All', 'Due', 'Pending', 'Paid'] as const
const activeFilter = ref<(typeof filterOptions)[number]>('All')
const router = useRouter()

const filteredFees = computed(() => {
  if (activeFilter.value === 'All') return fees.value
  return fees.value.filter((item) => item.status === activeFilter.value)
})

const totalOutstanding = computed(() =>
  fees.value
    .filter((item) => item.status === 'Due' || item.status === 'Pending')
    .reduce((sum, item) => sum + item.amount, 0)
)

function formatAmount(amount: number) {
  return `₦${amount.toLocaleString('en-NG')}`
}

function statusClass(status: FeeStatus) {
  if (status === 'Paid') return 'status-paid'
  if (status === 'Pending') return 'status-pending'
  return 'status-due'
}

function goToPayment() {
  router.push('/fees/payment')
}

async function loadFees() {
  loading.value = true
  try {
    const res = await client.get('/v1/fees')
    fees.value = (res.data.fees || []).map((item: {
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
      status: item.status === 'Paid' || item.status === 'Due' ? item.status : 'Pending',
    }))
  } catch {
    fees.value = []
  } finally {
    loading.value = false
  }
}

onMounted(loadFees)
</script>

<style scoped>
.fees-content { --background: var(--w-bg); }
.fees-wrap {
  padding: 18px 16px 100px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.outstanding-card {
  background: var(--w-primary);
  border-radius: 14px;
  padding: 14px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
}
.outstanding-amount {
  color: #fff;
  font-family: var(--w-font-display);
  font-size: 42px;
  line-height: 1;
  font-weight: 700;
}
.outstanding-label {
  color: rgba(255, 255, 255, 0.85);
  font-size: 15px;
  margin-top: 8px;
  font-family: var(--w-font-body);
}
.pay-btn {
  border: none;
  border-radius: 14px;
  background: #fff;
  color: var(--w-primary);
  font-weight: 700;
  font-size: 16px;
  font-family: var(--w-font-body);
  height: 52px;
  padding: 0 18px;
  min-width: 144px;
}

.filter-row {
  display: flex;
  gap: 8px;
}
.filter-chip {
  border: 1.5px solid var(--w-border);
  border-radius: 20px;
  background: var(--w-surface);
  color: var(--w-muted);
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 600;
  font-family: var(--w-font-body);
}
.filter-chip.active {
  border-color: var(--w-primary);
  background: var(--w-primary);
  color: #fff;
}

.fee-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.fees-empty {
  border-radius: var(--w-radius-md);
  border: 1px solid var(--w-border);
  background: var(--w-surface);
  color: var(--w-muted);
  text-align: center;
  padding: 15px 16px;
  font-size: 15px;
  font-family: var(--w-font-body);
}
.fee-item {
  border-radius: var(--w-radius-md);
  border: 1px solid var(--w-border);
  background: var(--w-surface);
  padding: 15px 16px;
}
.fee-top-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.fee-amount {
  color: #232833;
  font-size: 31px;
  line-height: 1;
  font-family: var(--w-font-display);
  font-weight: 700;
}
.status-pill {
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
  font-family: var(--w-font-body);
}
.status-paid { background: #d8f0d3; color: #2f9c44; }
.status-pending { background: #f2e6c8; color: #ce9700; }
.status-due { background: #f3d4d4; color: #ff4c4c; }

.fee-meta-row {
  margin-top: 10px;
  display: flex;
  align-items: center;
  gap: 16px;
}
.fee-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #6f7685;
  font-size: 13px;
  font-family: var(--w-font-body);
}
.fee-meta ion-icon {
  font-size: 14px;
}
</style>
