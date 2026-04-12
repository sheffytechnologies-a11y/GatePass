<template>
  <ion-page>
    <ion-content :fullscreen="true" class="details-content">
      <div class="details-wrap">
        <div class="heading">Payment Details</div>
        <div class="subheading">Transfer the exact amount to the account below</div>

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

          <div class="bank-card">
            <div class="bank-title">Bank Account Details</div>

            <div class="bank-row">
              <span>Bank Name</span>
              <strong>{{ bankDetails.bankName || '-' }}</strong>
            </div>
            <div class="bank-row">
              <span>Account Name</span>
              <strong>{{ bankDetails.accountName || '-' }}</strong>
            </div>
            <div class="bank-row">
              <span>Account Number</span>
              <strong>{{ bankDetails.accountNumber || '-' }}</strong>
            </div>
          </div>

          <div class="fees-card">
            <div class="fees-title">Breakdown</div>
            <div v-for="item in fees" :key="item.id" class="fee-row">
              <span>{{ item.title }}</span>
              <strong>{{ formatAmount(item.amount) }}</strong>
            </div>
          </div>

          <label class="upload-card" for="receiptFile">
            <div class="upload-title">Upload Payment Receipt</div>
            <div class="upload-subtitle">{{ selectedFileName || 'Tap to choose receipt image' }}</div>
            <input id="receiptFile" class="file-input" type="file" accept="image/*" @change="onFileSelected" />
          </label>

          <button class="done-btn" :disabled="submitting" @click="submitPayment">
            {{ submitting ? 'Submitting...' : "I've Made Payment" }}
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

type FeeItem = {
  id: string
  title: string
  amount: number
}

type PaymentDetailsResponse = {
  totalAmount: number
  fees: FeeItem[]
  bankDetails: {
    bankName: string | null
    accountName: string | null
    accountNumber: string | null
  }
}

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const submitting = ref(false)
const errorMessage = ref('')
const totalAmount = ref(0)
const fees = ref<FeeItem[]>([])
const selectedFile = ref<File | null>(null)
const selectedFileName = ref('')

const bankDetails = ref({
  bankName: null as string | null,
  accountName: null as string | null,
  accountNumber: null as string | null,
})

function parseIdsFromQuery() {
  const raw = route.query.ids
  if (!raw) return [] as string[]

  const idsText = Array.isArray(raw) ? raw.join(',') : String(raw)

  return idsText
    .split(',')
    .map((part) => part.trim())
    .filter((part) => part.length > 0)
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
    const res = await client.post<PaymentDetailsResponse>('/v1/fees/payment-details', { feeIds })

    totalAmount.value = Number(res.data.totalAmount || 0)
    fees.value = (res.data.fees || []).map((item) => ({
      id: item.id,
      title: item.title,
      amount: Number(item.amount || 0),
    }))

    bankDetails.value = {
      bankName: res.data.bankDetails?.bankName ?? null,
      accountName: res.data.bankDetails?.accountName ?? null,
      accountNumber: res.data.bankDetails?.accountNumber ?? null,
    }
  } catch {
    errorMessage.value = 'Unable to fetch payment details. Please try again.'
    fees.value = []
    totalAmount.value = 0
  } finally {
    loading.value = false
  }
}

function onFileSelected(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  selectedFile.value = file ?? null
  selectedFileName.value = file ? file.name : ''
}

function formatAmount(amount: number) {
  return `₦${amount.toLocaleString('en-NG')}`
}

async function submitPayment() {
  if (submitting.value) return

  const feeIds = parseIdsFromQuery()
  if (feeIds.length === 0) {
    errorMessage.value = 'No fee items were selected.'
    return
  }

  if (!selectedFile.value) {
    errorMessage.value = 'Please upload your payment receipt image before continuing.'
    return
  }

  submitting.value = true
  errorMessage.value = ''

  try {
    const formData = new FormData()
    feeIds.forEach((id) => formData.append('feeIds[]', id))
    formData.append('file', selectedFile.value)

    await client.post('/v1/fees/payments', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    router.push('/fees/payment/success')
  } catch {
    errorMessage.value = 'Unable to submit payment. Please try again.'
  } finally {
    submitting.value = false
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
.bank-card,
.fees-card,
.upload-card {
  border-radius: var(--w-radius-md);
  border: 1px solid var(--w-border);
  background: var(--w-surface);
  padding: 15px 16px;
}

.summary-row,
.bank-row,
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
.bank-row strong,
.fee-row strong {
  color: #1f2531;
}

.summary-row + .summary-row,
.bank-row + .bank-row,
.fee-row + .fee-row {
  margin-top: 10px;
}

.bank-title,
.fees-title,
.upload-title {
  color: #1f2531;
  font-size: 13px;
  font-weight: 700;
  margin-bottom: 10px;
  font-family: var(--w-font-body);
  text-transform: uppercase;
  letter-spacing: 0.02em;
}

.upload-card {
  display: block;
  cursor: pointer;
}

.upload-subtitle {
  color: var(--w-muted);
  font-size: 15px;
  font-family: var(--w-font-body);
}

.file-input {
  display: none;
}

.done-btn {
  margin-top: auto;
  width: 100%;
  border: none;
  border-radius: 14px;
  background: #0b6c40;
  color: #ffffff;
  font-family: var(--w-font-body);
  font-size: 16px;
  line-height: 1;
  font-weight: 700;
  height: 52px;
  padding: 0 16px;
}
</style>
