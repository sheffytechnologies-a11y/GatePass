<template>
  <div class="billing-page">
    <section class="hero-card">
      <div class="hero-eyebrow">Billing &amp; Subscription</div>
      <h1 class="hero-title">Manage your plan, usage, and payments — fully self-service.</h1>
      <p class="hero-copy">Upgrade, renew, or top up units any time. Payments are handled securely through Paystack.</p>
    </section>

    <div v-if="loading" class="loading-state"><div class="spinner"></div></div>

    <template v-else-if="subscription">
      <div v-if="subscription.isPastDue || subscription.renewalOverdue" class="banner banner-warning">
        <strong>{{ subscription.isPastDue ? 'Your subscription is past due.' : 'Your renewal is overdue.' }}</strong>
        <span>{{ subscription.isPastDue ? 'Adding new units or residents is paused until you renew.' : `You're in a short grace period — renew soon to avoid interruption.` }}</span>
      </div>

      <section class="card plan-card">
        <div class="plan-card-head">
          <div>
            <div class="plan-eyebrow">Current plan</div>
            <h2 class="plan-name">{{ subscription.plan.name }}</h2>
          </div>
          <span class="badge" :class="statusBadgeClass">{{ statusLabel }}</span>
        </div>

        <div v-if="subscription.daysUntilRenewal !== null" class="plan-renewal">
          {{ subscription.daysUntilRenewal >= 0
            ? `Renews in ${subscription.daysUntilRenewal} day${subscription.daysUntilRenewal === 1 ? '' : 's'}`
            : `Renewal was ${Math.abs(subscription.daysUntilRenewal)} day${Math.abs(subscription.daysUntilRenewal) === 1 ? '' : 's'} ago` }}
        </div>

        <div class="usage-grid">
          <div class="usage-block">
            <div class="usage-label">
              <span>Units</span>
              <span>{{ usage.unitsUsed }} / {{ usage.unitLimit ?? '∞' }}</span>
            </div>
            <div class="usage-bar"><div class="usage-fill" :style="{ width: unitsPct + '%' }" /></div>
          </div>
          <div class="usage-block">
            <div class="usage-label">
              <span>Residents</span>
              <span>{{ usage.residentsUsed }} / {{ usage.residentLimit ?? '∞' }}</span>
            </div>
            <div class="usage-bar"><div class="usage-fill usage-fill--accent" :style="{ width: residentsPct + '%' }" /></div>
          </div>
        </div>

        <div v-if="subscription.plan.code === 'free_trial'" class="addon-box">
          <div>
            <strong>Need more units?</strong>
            <p>Buy extra capacity at ₦{{ formatMoney(subscription.plan.priceAddonUnitMonthly) }} / unit / month. Purchased units stay active until you upgrade to a full plan.</p>
          </div>
          <div class="addon-form">
            <input v-model.number="extraUnitsCount" type="number" min="1" max="1000" class="form-input addon-input" />
            <button class="btn btn-primary btn-sm" :disabled="checkingOut" @click="buyExtraUnits">
              Buy {{ extraUnitsCount || 0 }} unit{{ extraUnitsCount === 1 ? '' : 's' }}
            </button>
          </div>
        </div>
      </section>

      <section class="plans-section">
        <div class="plans-head">
          <h2 class="section-title">Upgrade your plan</h2>
          <div class="cycle-toggle">
            <button class="cycle-chip" :class="{ active: cycle === 'monthly' }" @click="cycle = 'monthly'">Monthly</button>
            <button class="cycle-chip" :class="{ active: cycle === 'yearly' }" @click="cycle = 'yearly'">Yearly <span class="save-tag">2 months free</span></button>
          </div>
        </div>

        <div class="plans-grid">
          <div v-for="plan in paidPlans" :key="plan.id" class="card tier-card" :class="{ 'tier-card--current': plan.id === subscription.plan.id }">
            <div class="tier-name">{{ plan.name }}</div>
            <div class="tier-price">
              ₦{{ formatMoney(cycle === 'yearly' ? plan.priceYearly : plan.priceMonthly) }}
              <span>/ {{ cycle === 'yearly' ? 'yr' : 'mo' }}</span>
            </div>
            <ul class="tier-features">
              <li>Up to {{ plan.unitLimit }} units</li>
              <li>Up to {{ plan.residentLimit }} residents</li>
            </ul>
            <button
              class="btn btn-block"
              :class="plan.id === subscription.plan.id ? 'btn-outline' : 'btn-primary'"
              :disabled="checkingOut"
              @click="choosePlan(plan)"
            >
              {{ plan.id === subscription.plan.id ? 'Renew' : 'Choose plan' }}
            </button>
          </div>

          <div class="card tier-card tier-card--custom">
            <div class="tier-name">1,000+ Units</div>
            <div class="tier-price">Custom quote</div>
            <ul class="tier-features">
              <li>Unlimited units &amp; residents</li>
              <li>Dedicated SLA &amp; onboarding</li>
            </ul>
            <span class="badge badge-gray tier-contact">Contact us for pricing</span>
          </div>
        </div>
      </section>

      <section class="card transactions-card">
        <div class="card-header">
          <h2 class="card-title">Payment history</h2>
        </div>
        <div v-if="transactions.length === 0" class="empty-state">
          <p>No payments yet.</p>
        </div>
        <div v-else class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Reference</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in transactions" :key="t.id">
                <td>{{ t.reference }}</td>
                <td>{{ transactionTypeLabel(t.type) }}</td>
                <td>₦{{ formatMoney(t.amount) }}</td>
                <td><span class="badge" :class="transactionStatusClass(t.status)">{{ t.status }}</span></td>
                <td>{{ fmtDate(t.createdAt) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { billingApi } from '@/api'
import { useToast } from '@/composables/useToast'
import { usePaystack } from '@/composables/usePaystack'
import { useAuthStore } from '@/stores/auth'

const { showToast } = useToast()
const { openCheckout } = usePaystack()
const auth = useAuthStore()

const loading = ref(true)
const checkingOut = ref(false)
const subscription = ref<any>(null)
const usage = ref<any>({})
const plans = ref<any[]>([])
const transactions = ref<any[]>([])
const cycle = ref<'monthly' | 'yearly'>('monthly')
const extraUnitsCount = ref(1)

const paidPlans = computed(() => plans.value.filter((p) => !p.isCustom))

const unitsPct = computed(() => {
  if (!usage.value.unitLimit) return 8
  return Math.min(100, Math.round((usage.value.unitsUsed / usage.value.unitLimit) * 100))
})
const residentsPct = computed(() => {
  if (!usage.value.residentLimit) return 8
  return Math.min(100, Math.round((usage.value.residentsUsed / usage.value.residentLimit) * 100))
})

const statusLabel = computed(() => {
  const map: Record<string, string> = {
    trialing: 'Free trial',
    active: 'Active',
    past_due: 'Past due',
    canceled: 'Canceled',
  }
  return map[subscription.value?.status] ?? subscription.value?.status
})
const statusBadgeClass = computed(() => ({
  'badge-green': subscription.value?.status === 'active',
  'badge-blue': subscription.value?.status === 'trialing',
  'badge-red': subscription.value?.status === 'past_due',
  'badge-gray': subscription.value?.status === 'canceled',
}))

function formatMoney(n: number | null | undefined) {
  return Number(n ?? 0).toLocaleString('en-NG')
}
function fmtDate(iso: string | null) {
  if (!iso) return '—'
  return new Date(iso).toLocaleDateString('en-NG', { dateStyle: 'medium' })
}
function transactionTypeLabel(type: string) {
  const map: Record<string, string> = { plan_purchase: 'Plan purchase', renewal: 'Renewal', extra_units: 'Extra units' }
  return map[type] ?? type
}
function transactionStatusClass(status: string) {
  return { 'badge-green': status === 'success', 'badge-yellow': status === 'pending', 'badge-red': status === 'failed' }
}

async function loadAll() {
  loading.value = true
  try {
    const [subRes, plansRes, txRes] = await Promise.all([
      billingApi.getSubscription(),
      billingApi.getPlans(),
      billingApi.getTransactions(),
    ])
    subscription.value = subRes.data.subscription
    usage.value = subRes.data.usage
    plans.value = plansRes.data.plans
    transactions.value = txRes.data.transactions
  } catch {
    showToast('Failed to load billing information.', 'error')
  } finally {
    loading.value = false
  }
}

async function runCheckout(payload: Record<string, unknown>, successMessage: string) {
  checkingOut.value = true
  try {
    const res = await billingApi.checkout(payload)
    const { reference, amount, email } = res.data
    await openCheckout({
      email,
      amount,
      reference,
      onSuccess: async (ref: string) => {
        try {
          await billingApi.verify(ref)
          showToast(successMessage, 'success')
          await loadAll()
        } catch {
          showToast('Payment made but verification failed — contact support.', 'error')
        } finally {
          checkingOut.value = false
        }
      },
      onCancel: () => {
        checkingOut.value = false
        showToast('Payment cancelled.', 'warning')
      },
    })
  } catch (err: any) {
    checkingOut.value = false
    showToast(err?.response?.data?.message ?? 'Could not start payment.', 'error')
  }
}

function choosePlan(plan: any) {
  runCheckout({ planId: plan.id, billingCycle: cycle.value }, 'Plan updated!')
}

function buyExtraUnits() {
  if (!extraUnitsCount.value || extraUnitsCount.value < 1) return
  runCheckout({ extraUnits: extraUnitsCount.value }, 'Extra units added!')
}

onMounted(loadAll)
void auth
</script>

<style scoped>
.billing-page { display: flex; flex-direction: column; gap: 18px; }
.hero-card {
  background: radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 38%), linear-gradient(150deg, #102a21 0%, #0a5c38 60%, #78cca2 100%);
  color: white;
  border-radius: 28px;
  padding: 22px;
  box-shadow: var(--shadow-md);
}
.hero-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.16em; color: rgba(255,255,255,0.74); }
.hero-title { margin-top: 10px; font-size: 26px; line-height: 1.08; font-family: var(--font-display); }
.hero-copy { margin-top: 8px; color: rgba(255,255,255,0.80); font-size: 14px; }

.loading-state { display: flex; justify-content: center; padding: 80px 0; }

.banner { border-radius: var(--radius-lg); padding: 14px 18px; display: flex; flex-direction: column; gap: 4px; font-size: 13px; }
.banner-warning { background: var(--c-warning-light); color: #7a4a06; border: 1px solid rgba(217,119,6,0.25); }

.plan-card { padding: 20px; display: flex; flex-direction: column; gap: 16px; }
.plan-card-head { display: flex; justify-content: space-between; align-items: flex-start; }
.plan-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.12em; color: var(--c-muted); font-weight: 700; }
.plan-name { font-size: 22px; font-weight: 800; font-family: var(--font-display); margin-top: 4px; }
.plan-renewal { font-size: 13px; color: var(--c-muted); }

.usage-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.usage-label { display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: var(--c-text); margin-bottom: 6px; }
.usage-bar { height: 8px; border-radius: 999px; background: var(--c-bg); overflow: hidden; }
.usage-fill { height: 100%; background: var(--c-primary); border-radius: 999px; transition: width 0.3s; }
.usage-fill--accent { background: var(--c-accent); }

.addon-box {
  display: flex; justify-content: space-between; align-items: center; gap: 16px;
  background: var(--c-primary-light); border-radius: var(--radius);
  padding: 14px 16px; flex-wrap: wrap;
}
.addon-box strong { font-size: 14px; }
.addon-box p { font-size: 12px; color: var(--c-muted); margin-top: 4px; max-width: 320px; }
.addon-form { display: flex; gap: 8px; align-items: center; }
.addon-input { width: 70px; text-align: center; padding: 9px; }

.plans-section { display: flex; flex-direction: column; gap: 12px; }
.plans-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
.section-title { font-size: 18px; font-weight: 700; }
.cycle-toggle { display: flex; gap: 6px; background: var(--c-bg); padding: 4px; border-radius: 999px; }
.cycle-chip { border: none; background: transparent; padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 700; color: var(--c-muted); display: flex; align-items: center; gap: 6px; }
.cycle-chip.active { background: var(--c-surface); color: var(--c-text); box-shadow: var(--shadow); }
.save-tag { font-size: 10px; color: var(--c-success); background: var(--c-success-light); padding: 1px 6px; border-radius: 999px; }

.plans-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
.tier-card { padding: 18px; display: flex; flex-direction: column; gap: 10px; }
.tier-card--current { border: 2px solid var(--c-primary); }
.tier-card--custom { justify-content: space-between; background: var(--c-bg); box-shadow: none; }
.tier-name { font-size: 15px; font-weight: 700; }
.tier-price { font-size: 22px; font-weight: 800; font-family: var(--font-display); }
.tier-price span { font-size: 12px; font-weight: 500; color: var(--c-muted); }
.tier-features { list-style: none; display: flex; flex-direction: column; gap: 6px; font-size: 13px; color: var(--c-muted); flex: 1; }
.tier-features li::before { content: '✓ '; color: var(--c-success); font-weight: 700; }
.btn-block { width: 100%; justify-content: center; }
.tier-contact { align-self: flex-start; }

.transactions-card { padding: 0; }
.card-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--c-border); }
.card-title { font-size: 15px; font-weight: 600; }

@media (max-width: 560px) {
  .usage-grid, .plans-grid { grid-template-columns: 1fr; }
}
</style>
