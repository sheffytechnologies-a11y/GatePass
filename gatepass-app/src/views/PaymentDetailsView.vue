<template>
  <ion-page>
    <ion-content :fullscreen="true" class="details-content">
      <div class="details-wrap">
        <div class="heading">Payment Details</div>
        <div class="subheading">Review your fees and pay securely online</div>

        <div v-if="loading" class="state-card">Loading payment details...</div>
        <div v-else-if="errorMessage" class="state-card state-error">{{ errorMessage }}</div>

        <template v-else>
          <div class="summary-card">
            <div class="summary-row">
              <span>Total Amount</span>
              <strong>{{ formatAmount(totalAmount) }}</strong>
            </div>
            <div class="summary-row">
              <span>Selected Fees</span>
              <strong>{{ fees.length }}</strong>
            </div>
          </div>

          <div class="fees-card">
            <div class="fees-title">Breakdown</div>
            <div v-for="item in fees" :key="item.id" class="fee-row">
              <span>{{ item.title }}</span>
              <strong>{{ formatAmount(item.amount) }}</strong>
            </div>
          </div>

          <button class="pay-btn" :disabled="paying" @click="payWithPaystack">
            <span v-if="paying" class="pay-btn-inner">
              <span class="spinner-sm"></span> Processing…
            </span>
            <span v-else class="pay-btn-inner">
              Pay {{ formatAmount(totalAmount) }} with Paystack
            </span>
          </button>
        </template>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { IonPage, IonContent } from '@ionic/vue'
import client from '@/api/client'

declare global {
  interface Window {
    PaystackPop: any
  }
}

type FeeItem = {
  id: string
  title: string
  amount: number
}

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const paying = ref(false)
const errorMessage = ref('')
const totalAmount = ref(0)
const fees = ref<FeeItem[]>([])

function parseIdsFromQuery(): string[] {
  const raw = route.query.ids
  if (!raw) return []
  const idsText = Array.isArray(raw) ? raw.join(',') : String(raw)
  return idsText.split(',').map((p) => p.trim()).filter(Boolean)
}

async function loadPaymentDetails() {
  const feeIds = parseIdsFromQuery()
  if (feeIds.length === 0) {
    errorMessage.value = 'No fee items were selected.'
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    const res = await client.post('/v1/fees/payment-details', { feeIds })
    totalAmount.value = Number(res.data.totalAmount || 0)
    fees.value = (res.data.fees || []).map((item: FeeItem) => ({
      id: item.id,
      title: item.title,
      amount: Number(item.amount || 0),
    }))
  } catch {
    errorMessage.value = 'Unable to fetch payment details. Please try again.'
  } finally {
    loading.value = false
  }
}

function formatAmount(amount: number) {
  return `₦${amount.toLocaleString('en-NG')}`
}

function loadPaystackScript(): Promise<void> {
  return new Promise((resolve) => {
    if (window.PaystackPop) { resolve(); return }
    const script = document.createElement('script')
    script.src = 'https://js.paystack.co/v2/inline.js'
    script.onload = () => resolve()
    document.head.appendChild(script)
  })
}

async function payWithPaystack() {
  if (paying.value) return

  const feeIds = parseIdsFromQuery()
  if (feeIds.length === 0) {
    errorMessage.value = 'No fee items were selected.'
    return
  }

  paying.value = true
  errorMessage.value = ''

  try {
    const initRes = await client.post('/v1/fees/paystack/initialize', { feeIds })
    const { reference, amount, email } = initRes.data

    await loadPaystackScript()

    const paystack = new window.PaystackPop()
    paystack.newTransaction({
      key: import.meta.env.VITE_PAYSTACK_PUBLIC_KEY,
      email,
      amount,
      reference,
      onSuccess: async (transaction: { reference: string }) => {
        try {
          await client.post('/v1/fees/paystack/verify', {
            reference: transaction.reference,
            feeIds,
          })
          router.push('/fees/payment/success')
        } catch {
          errorMessage.value = 'Payment was made but verification failed. Please contact support.'
        } finally {
          paying.value = false
        }
      },
      onCancel: () => {
        paying.value = false
        errorMessage.value = 'Payment was cancelled. You can try again.'
      },
    })
  } catch (err: any) {
    errorMessage.value = err?.response?.data?.message ?? 'Unable to start payment. Please try again.'
    paying.value = false
  }
}

onMounted(loadPaymentDetails)
</script>

<style scoped>
.details-content { --background: var(--w-bg); }
.details-wrap {
  min-height: 100%;
  padding: 18px 16px 28px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.heading {
  text-align: center;
  color: #1f2531;
  font-family: var(--w-font-display);
  font-size: 26px;
  line-height: 1;
  font-weight: 700;
}

.subheading {
  text-align: center;
  color: var(--w-muted);
  font-size: 15px;
  line-height: 1.4;
  font-family: var(--w-font-body);
}

.state-card {
  border-radius: var(--w-radius-md);
  border: 1px solid var(--w-border);
  background: var(--w-surface);
  color: #5a6372;
  text-align: center;
  padding: 15px 16px;
  font-size: 15px;
  font-family: var(--w-font-body);
}

.state-error {
  background: var(--w-danger-light);
  border-color: #f6caca;
  color: #b33a3a;
}

.summary-card,
.fees-card {
  border-radius: var(--w-radius-md);
  border: 1px solid var(--w-border);
  background: var(--w-surface);
  padding: 15px 16px;
}

.summary-row,
.fee-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  color: #2a3140;
  font-size: 15px;
  font-family: var(--w-font-body);
}

.summary-row strong,
.fee-row strong {
  color: #1f2531;
}

.summary-row + .summary-row,
.fee-row + .fee-row {
  margin-top: 10px;
}

.fees-title {
  color: #1f2531;
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 10px;
  font-family: var(--w-font-body);
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.pay-btn {
  margin-top: auto;
  width: 100%;
  border: none;
  border-radius: 14px;
  background: #0b6c40;
  color: #fff;
  font-size: 16px;
  font-family: var(--w-font-body);
  font-weight: 700;
  height: 56px;
  padding: 0 16px;
}

.pay-btn:disabled {
  opacity: 0.6;
}

.pay-btn-inner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.spinner-sm {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
