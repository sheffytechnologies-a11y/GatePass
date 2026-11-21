<template>
  <div class="billing-page">
    <!-- SUPER ADMIN VIEW: Estate Subscriptions & Expiration Manager -->
    <template v-if="auth.isSuperAdmin">
      <section class="hero-card">
        <div class="hero-eyebrow">Estate Subscriptions &amp; Client Roster</div>
        <h1 class="hero-title">Monitor client estate subscriptions, plan tiers, and renewal expirations.</h1>
        <p class="hero-copy">Super Admin platform oversight of client estates, active tiers, unit allocations, renewal schedules, and automated expiration warnings.</p>
      </section>

      <!-- KPI Metrics Strip -->
      <section class="kpi-grid">
        <div class="card kpi-card">
          <div class="kpi-label">Total Client Estates</div>
          <div class="kpi-value">{{ metrics.totalEstates || subscribers.length }}</div>
          <div class="kpi-sub">Onboarded across Checkpass</div>
        </div>
        <div class="card kpi-card">
          <div class="kpi-label">Active Paid Subscriptions</div>
          <div class="kpi-value text-green">{{ metrics.activePaid }}</div>
          <div class="kpi-sub">Paying client communities</div>
        </div>
        <div class="card kpi-card" :class="{ 'kpi-card--alert': metrics.expiringSoon > 0 }">
          <div class="kpi-label">Expiring Soon (&le; 14 Days)</div>
          <div class="kpi-value" :class="metrics.expiringSoon > 0 ? 'text-amber' : ''">{{ metrics.expiringSoon }}</div>
          <div class="kpi-sub">Needs follow-up or renewal</div>
        </div>
        <div class="card kpi-card">
          <div class="kpi-label">Estimated MRR</div>
          <div class="kpi-value text-green">₦{{ formatMoney(metrics.mrr) }}</div>
          <div class="kpi-sub">Monthly Recurring Revenue</div>
        </div>
      </section>

      <!-- Controls & Filter Bar -->
      <section class="filter-bar card">
        <div class="search-box">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" class="search-icon">
            <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2" />
            <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by estate name, city, or admin contact..."
            class="search-input"
          />
        </div>

        <div class="filter-actions">
          <div class="filter-group">
            <label class="filter-label">Status</label>
            <select v-model="statusFilter" class="filter-select">
              <option value="all">All Statuses</option>
              <option value="active">Active</option>
              <option value="trialing">Free Trial</option>
              <option value="expiring_soon">Expiring Soon (&le; 14d)</option>
              <option value="expired">Past Due / Expired</option>
            </select>
          </div>

          <div class="filter-group">
            <label class="filter-label">Plan Tier</label>
            <select v-model="planFilter" class="filter-select">
              <option value="all">All Plans</option>
              <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>

          <button class="btn btn-outline btn-refresh" :disabled="loading" @click="loadSuperAdminData">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" :class="{ 'spin-anim': loading }">
              <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            Refresh
          </button>
        </div>
      </section>

      <!-- Subscribers Table -->
      <div v-if="loading" class="loading-state">
        <BrandSpinner :size="40" color="var(--c-primary)" />
      </div>

      <div v-else-if="filteredSubscribers.length === 0" class="card empty-state">
        <div class="empty-icon">🏢</div>
        <h3>No matching estate subscriptions</h3>
        <p>No estates match your search or filter criteria.</p>
      </div>

      <section v-else class="card subscribers-card">
        <div class="table-wrap">
          <table class="subscribers-table">
            <thead>
              <tr>
                <th>Client Estate</th>
                <th>Admin Contact</th>
                <th>Current Plan</th>
                <th>Unit Usage</th>
                <th>Expires / Renews</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="sub in filteredSubscribers" :key="sub.id">
                <td>
                  <div class="estate-name-wrap">
                    <span class="estate-name">{{ sub.estateName }}</span>
                    <span class="estate-loc">{{ sub.estateCity || sub.estateAddress || 'Nigeria' }}</span>
                  </div>
                </td>
                <td>
                  <div class="admin-contact-wrap">
                    <span class="admin-name">{{ sub.adminName }}</span>
                    <span class="admin-email">{{ sub.adminEmail }}</span>
                    <span v-if="sub.adminPhone && sub.adminPhone !== '—'" class="admin-phone">{{ sub.adminPhone }}</span>
                  </div>
                </td>
                <td>
                  <div class="plan-info-wrap">
                    <span class="plan-pill">{{ sub.plan?.name || 'Free Trial' }}</span>
                    <span class="plan-cycle-tag">{{ sub.billingCycle === 'yearly' ? 'Yearly' : 'Monthly' }}</span>
                  </div>
                  <div class="plan-price-sub">
                    ₦{{ formatMoney(sub.billingCycle === 'yearly' ? sub.plan?.priceYearly : sub.plan?.priceMonthly) }} / {{ sub.billingCycle === 'yearly' ? 'yr' : 'mo' }}
                  </div>
                </td>
                <td>
                  <div class="usage-col">
                    <div class="usage-text">
                      <strong>{{ sub.unitsUsed }}</strong> / {{ sub.unitLimit ?? '∞' }} units
                    </div>
                    <div class="micro-bar">
                      <div
                        class="micro-fill"
                        :style="{ width: calculateUnitPct(sub.unitsUsed, sub.unitLimit) + '%' }"
                      />
                    </div>
                    <span class="micro-residents">{{ sub.residentsUsed }} residents registered</span>
                  </div>
                </td>
                <td>
                  <div class="expiry-col">
                    <span class="expiry-date">{{ fmtDate(sub.currentPeriodEnd) }}</span>
                    <span
                      class="expiry-badge"
                      :class="getExpiryBadgeClass(sub)"
                    >
                      {{ getExpiryText(sub) }}
                    </span>
                  </div>
                </td>
                <td>
                  <span class="badge" :class="getSubscriberStatusClass(sub.status)">
                    {{ getSubscriberStatusLabel(sub.status) }}
                  </span>
                </td>
                <td class="text-right">
                  <button class="btn btn-sm btn-outline" @click="openEditModal(sub)">
                    Manage Plan
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <!-- Super Admin Edit / Extend Subscription Modal -->
      <div v-if="editingSubscriber" class="modal-backdrop" @click.self="closeEditModal">
        <div class="modal-card">
          <div class="modal-header">
            <div>
              <div class="modal-eyebrow">Modify Client Subscription</div>
              <h2 class="modal-title">{{ editingSubscriber.estateName }}</h2>
            </div>
            <button class="close-btn" @click="closeEditModal">&times;</button>
          </div>

          <div class="modal-body">
            <div class="info-alert">
              <span>Admin: <strong>{{ editingSubscriber.adminName }}</strong> ({{ editingSubscriber.adminEmail }})</span>
              <span>Current Expiry: <strong>{{ fmtDate(editingSubscriber.currentPeriodEnd) }}</strong></span>
            </div>

            <div class="form-grid">
              <div class="form-field">
                <label>Subscription Plan Tier</label>
                <select v-model="editForm.plan_id" class="form-select">
                  <option v-for="p in plans" :key="p.id" :value="p.id">
                    {{ p.name }} ({{ p.unit_limit ? p.unit_limit + ' units' : 'Custom' }}) - ₦{{ formatMoney(p.price_monthly) }}/mo
                  </option>
                </select>
              </div>

              <div class="form-field">
                <label>Billing Cycle</label>
                <select v-model="editForm.billing_cycle" class="form-select">
                  <option value="monthly">Monthly</option>
                  <option value="yearly">Yearly</option>
                </select>
              </div>

              <div class="form-field">
                <label>Status</label>
                <select v-model="editForm.status" class="form-select">
                  <option value="active">Active (Full Access)</option>
                  <option value="trialing">Free Trial</option>
                  <option value="past_due">Past Due (Paused top-up)</option>
                  <option value="canceled">Canceled</option>
                </select>
              </div>

              <div class="form-field">
                <label>Extra Purchased Units</label>
                <input v-model.number="editForm.extra_units" type="number" min="0" class="form-input" />
              </div>
            </div>

            <div class="extension-section">
              <label class="extension-label">Extend Expiration / Add Validity</label>
              <div class="extension-quick-buttons">
                <button
                  type="button"
                  class="btn btn-sm"
                  :class="editForm.extend_days === 30 ? 'btn-primary' : 'btn-outline'"
                  @click="editForm.extend_days = 30"
                >
                  +30 Days (1 Month)
                </button>
                <button
                  type="button"
                  class="btn btn-sm"
                  :class="editForm.extend_days === 90 ? 'btn-primary' : 'btn-outline'"
                  @click="editForm.extend_days = 90"
                >
                  +90 Days (Quarter)
                </button>
                <button
                  type="button"
                  class="btn btn-sm"
                  :class="editForm.extend_days === 365 ? 'btn-primary' : 'btn-outline'"
                  @click="editForm.extend_days = 365"
                >
                  +365 Days (1 Year)
                </button>
                <button
                  type="button"
                  class="btn btn-sm"
                  :class="editForm.extend_days === null ? 'btn-outline' : 'btn-ghost'"
                  @click="editForm.extend_days = null"
                >
                  Clear Extension
                </button>
              </div>

              <div class="form-field" style="margin-top: 12px;">
                <label>Or Set Specific Expiration Date</label>
                <input v-model="editForm.custom_end_date" type="date" class="form-input" />
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-ghost" @click="closeEditModal">Cancel</button>
            <button class="btn btn-primary" :disabled="savingSubscriber" @click="saveSubscriberChanges">
              {{ savingSubscriber ? 'Saving...' : 'Save & Update Subscription' }}
            </button>
          </div>
        </div>
      </div>
    </template>

    <!-- REGULAR ESTATE ADMIN VIEW: Self-Serve Plan & Paystack Checkout -->
    <template v-else>
      <section class="hero-card">
        <div class="hero-eyebrow">Billing &amp; Subscription</div>
        <h1 class="hero-title">Manage your plan, usage, and payments — fully self-service.</h1>
        <p class="hero-copy">Upgrade, renew, or top up units any time. Payments are handled securely through Paystack.</p>
      </section>

      <div v-if="loading" class="loading-state"><BrandSpinner :size="40" color="var(--c-primary)" /></div>

      <template v-else-if="subscription">
        <div v-if="subscription.isPastDue || subscription.renewalOverdue" class="banner banner-warning">
          <strong>{{ subscription.isPastDue ? 'Your subscription is past due.' : 'Your renewal is overdue.' }}</strong>
          <span>{{ subscription.isPastDue ? 'Adding new units or residents is paused until you renew.' : `You're in a short grace period — renew soon to avoid interruption.` }}</span>
        </div>

        <section class="card plan-card">
          <div class="plan-card-head">
            <div>
              <div class="plan-eyebrow">Current plan</div>
              <h2 class="plan-name">{{ subscription.plan?.name || 'Free Trial' }}</h2>
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

          <div v-if="subscription.plan?.code === 'free_trial'" class="addon-box">
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
            <div v-for="plan in paidPlans" :key="plan.id" class="card tier-card" :class="{ 'tier-card--current': plan.id === subscription.plan?.id }">
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
                :class="plan.id === subscription.plan?.id ? 'btn-outline' : 'btn-primary'"
                :disabled="checkingOut"
                @click="choosePlan(plan)"
              >
                {{ plan.id === subscription.plan?.id ? 'Renew' : 'Choose plan' }}
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

// Super Admin state
const subscribers = ref<any[]>([])
const metrics = ref({
  totalEstates: 0,
  activePaid: 0,
  expiringSoon: 0,
  mrr: 0,
})
const searchQuery = ref('')
const statusFilter = ref('all')
const planFilter = ref('all')

const editingSubscriber = ref<any | null>(null)
const savingSubscriber = ref(false)
const editForm = ref({
  plan_id: null as number | null,
  billing_cycle: 'monthly',
  status: 'active',
  extra_units: 0,
  extend_days: null as number | null,
  custom_end_date: '',
})

// Estate Admin state
const subscription = ref<any>(null)
const usage = ref<any>({})
const plans = ref<any[]>([])
const transactions = ref<any[]>([])
const cycle = ref<'monthly' | 'yearly'>('monthly')
const extraUnitsCount = ref(1)

const paidPlans = computed(() => plans.value.filter((p) => !p.isCustom))

// Super Admin filtering
const filteredSubscribers = computed(() => {
  return subscribers.value.filter((s) => {
    // Search query
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase().trim()
      const matchEstate = (s.estateName || '').toLowerCase().includes(q)
      const matchCity = (s.estateCity || '').toLowerCase().includes(q)
      const matchAddress = (s.estateAddress || '').toLowerCase().includes(q)
      const matchAdmin = (s.adminName || '').toLowerCase().includes(q)
      const matchEmail = (s.adminEmail || '').toLowerCase().includes(q)
      if (!matchEstate && !matchCity && !matchAddress && !matchAdmin && !matchEmail) {
        return false
      }
    }

    // Status filter
    if (statusFilter.value === 'active' && s.status !== 'active') return false
    if (statusFilter.value === 'trialing' && s.status !== 'trialing') return false
    if (statusFilter.value === 'expiring_soon' && !s.isExpiringSoon) return false
    if (statusFilter.value === 'expired' && !s.isExpired && s.status !== 'past_due') return false

    // Plan filter
    if (planFilter.value !== 'all' && s.plan?.id !== Number(planFilter.value)) return false

    return true
  })
})

function calculateUnitPct(used: number, limit: number | null) {
  if (!limit) return 10
  return Math.min(100, Math.round((used / limit) * 100))
}

function getExpiryText(sub: any) {
  if (!sub.currentPeriodEnd) return 'No renewal date'
  if (sub.daysRemaining === null) return 'Untouched'
  if (sub.daysRemaining < 0) return `Expired ${Math.abs(sub.daysRemaining)}d ago`
  if (sub.daysRemaining === 0) return 'Expires today'
  if (sub.daysRemaining <= 14) return `Expires in ${sub.daysRemaining}d`
  return `${sub.daysRemaining} days remaining`
}

function getExpiryBadgeClass(sub: any) {
  if (!sub.currentPeriodEnd) return 'badge-neutral'
  if (sub.daysRemaining === null) return 'badge-neutral'
  if (sub.daysRemaining < 0) return 'badge-danger'
  if (sub.daysRemaining <= 14) return 'badge-warning'
  return 'badge-success'
}

function getSubscriberStatusLabel(status: string) {
  const map: Record<string, string> = {
    active: 'Active',
    trialing: 'Free Trial',
    past_due: 'Past Due',
    canceled: 'Canceled',
  }
  return map[status] ?? status
}

function getSubscriberStatusClass(status: string) {
  return {
    'badge-green': status === 'active',
    'badge-blue': status === 'trialing',
    'badge-red': status === 'past_due',
    'badge-gray': status === 'canceled',
  }
}

function openEditModal(sub: any) {
  editingSubscriber.value = sub
  editForm.value = {
    plan_id: sub.plan?.id ?? (plans.value[0]?.id || 1),
    billing_cycle: sub.billingCycle || 'monthly',
    status: sub.status || 'active',
    extra_units: sub.extraUnits || 0,
    extend_days: null,
    custom_end_date: sub.currentPeriodEnd ? sub.currentPeriodEnd.substring(0, 10) : '',
  }
}

function closeEditModal() {
  editingSubscriber.value = null
  savingSubscriber.value = false
}

async function saveSubscriberChanges() {
  if (!editingSubscriber.value) return
  savingSubscriber.value = true
  try {
    const payload: Record<string, any> = {
      plan_id: editForm.value.plan_id,
      billing_cycle: editForm.value.billing_cycle,
      status: editForm.value.status,
      extra_units: editForm.value.extra_units,
    }

    if (editForm.value.extend_days) {
      payload.extend_days = editForm.value.extend_days
    } else if (editForm.value.custom_end_date) {
      payload.current_period_end = editForm.value.custom_end_date
    }

    await billingApi.updateSubscriber(editingSubscriber.value.estateId, payload)
    showToast(`Updated subscription for ${editingSubscriber.value.estateName}`, 'success')
    closeEditModal()
    await loadSuperAdminData()
  } catch (err: any) {
    showToast(err?.response?.data?.message || 'Failed to update subscription', 'error')
  } finally {
    savingSubscriber.value = false
  }
}

async function loadSuperAdminData() {
  loading.value = true
  try {
    const [subscribersRes, plansRes] = await Promise.all([
      billingApi.getSubscribers(),
      billingApi.getPlans(),
    ])
    subscribers.value = subscribersRes.data.subscribers || []
    metrics.value = subscribersRes.data.metrics || {
      totalEstates: 0,
      activePaid: 0,
      expiringSoon: 0,
      mrr: 0,
    }
    plans.value = plansRes.data.plans || []
  } catch (err: any) {
    showToast('Failed to load estate subscriptions roster.', 'error')
  } finally {
    loading.value = false
  }
}

// Estate Admin Computed
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

async function loadEstateAdminData() {
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
          await loadEstateAdminData()
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

onMounted(() => {
  if (auth.isSuperAdmin) {
    loadSuperAdminData()
  } else {
    loadEstateAdminData()
  }
})
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

/* Super Admin KPI Grid */
.kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.kpi-card { padding: 18px; display: flex; flex-direction: column; gap: 6px; }
.kpi-card--alert { border: 1.5px solid #d97706; background: #fffbeb; }
.kpi-label { font-size: 12px; font-weight: 700; color: var(--c-muted); text-transform: uppercase; letter-spacing: 0.05em; }
.kpi-value { font-size: 26px; font-weight: 800; font-family: var(--font-display); }
.kpi-sub { font-size: 11px; color: var(--c-muted); }
.text-green { color: #0a5c38; }
.text-amber { color: #d97706; }

/* Filter Bar */
.filter-bar {
  display: flex; justify-content: space-between; align-items: center; gap: 14px;
  padding: 14px 18px; flex-wrap: wrap;
}
.search-box {
  display: flex; align-items: center; gap: 10px;
  background: var(--c-bg); border-radius: 999px;
  padding: 8px 16px; flex: 1; min-width: 260px;
}
.search-icon { color: var(--c-muted); flex-shrink: 0; }
.search-input {
  border: none; background: transparent; outline: none;
  font-size: 13px; width: 100%; color: var(--c-text);
}
.filter-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.filter-group { display: flex; align-items: center; gap: 6px; }
.filter-label { font-size: 12px; font-weight: 600; color: var(--c-muted); }
.filter-select {
  border: 1px solid var(--c-border); background: var(--c-surface);
  border-radius: 999px; padding: 6px 12px; font-size: 12px; font-weight: 600;
  color: var(--c-text); outline: none; cursor: pointer;
}
.btn-refresh { display: flex; align-items: center; gap: 6px; padding: 6px 14px; font-size: 12px; border-radius: 999px; }
.spin-anim { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Table */
.subscribers-card { padding: 0; overflow: hidden; }
.subscribers-table { width: 100%; border-collapse: collapse; }
.subscribers-table th {
  text-align: left; padding: 12px 18px; font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.05em; color: var(--c-muted);
  background: rgba(0,0,0,0.02); border-bottom: 1px solid var(--c-border);
}
.subscribers-table td {
  padding: 14px 18px; font-size: 13px; vertical-align: middle;
  border-bottom: 1px solid var(--c-border);
}
.subscribers-table tr:last-child td { border-bottom: none; }
.text-right { text-align: right; }

.estate-name-wrap { display: flex; flex-direction: column; gap: 2px; }
.estate-name { font-weight: 700; color: var(--c-text); font-size: 14px; }
.estate-loc { font-size: 11px; color: var(--c-muted); }

.admin-contact-wrap { display: flex; flex-direction: column; gap: 2px; }
.admin-name { font-weight: 600; color: var(--c-text); }
.admin-email { font-size: 11px; color: var(--c-muted); }
.admin-phone { font-size: 11px; color: var(--c-muted); }

.plan-info-wrap { display: flex; align-items: center; gap: 6px; }
.plan-pill {
  font-weight: 700; font-size: 12px; color: #0a5c38;
  background: rgba(10, 92, 56, 0.08); padding: 2px 8px; border-radius: 6px;
}
.plan-cycle-tag { font-size: 11px; color: var(--c-muted); text-transform: uppercase; font-weight: 600; }
.plan-price-sub { font-size: 11px; color: var(--c-muted); margin-top: 4px; }

.usage-col { display: flex; flex-direction: column; gap: 4px; min-width: 140px; }
.usage-text { font-size: 12px; }
.micro-bar { height: 5px; border-radius: 999px; background: var(--c-bg); overflow: hidden; width: 100%; }
.micro-fill { height: 100%; background: #0a5c38; border-radius: 999px; }
.micro-residents { font-size: 10px; color: var(--c-muted); }

.expiry-col { display: flex; flex-direction: column; gap: 4px; }
.expiry-date { font-weight: 600; font-size: 12px; }
.expiry-badge {
  display: inline-block; font-size: 10px; font-weight: 700;
  padding: 2px 6px; border-radius: 4px; width: fit-content;
}
.badge-success { background: #dcfce7; color: #166534; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-neutral { background: #f3f4f6; color: #4b5563; }

.loading-state { display: flex; justify-content: center; padding: 80px 0; }
.empty-state { padding: 48px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.empty-icon { font-size: 36px; }

/* Modal */
.modal-backdrop {
  position: fixed; inset: 0; background: rgba(0,0,0,0.45);
  display: flex; align-items: center; justify-content: center; z-index: 999;
  padding: 16px; backdrop-filter: blur(2px);
}
.modal-card {
  background: var(--c-surface); border-radius: 20px; width: 100%; max-width: 520px;
  box-shadow: var(--shadow-lg); overflow: hidden; display: flex; flex-direction: column;
}
.modal-header {
  display: flex; justify-content: space-between; align-items: flex-start;
  padding: 20px 24px; border-bottom: 1px solid var(--c-border);
}
.modal-eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: var(--c-muted); font-weight: 700; }
.modal-title { font-size: 18px; font-weight: 800; margin-top: 4px; }
.close-btn { border: none; background: transparent; font-size: 24px; line-height: 1; cursor: pointer; color: var(--c-muted); }
.modal-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 16px; max-height: 75vh; overflow-y: auto; }
.info-alert {
  background: var(--c-bg); border-radius: 12px; padding: 12px 14px;
  display: flex; flex-direction: column; gap: 4px; font-size: 12px; color: var(--c-text);
}
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-field { display: flex; flex-direction: column; gap: 6px; }
.form-field label { font-size: 12px; font-weight: 600; color: var(--c-text); }
.form-select, .form-input {
  border: 1px solid var(--c-border); border-radius: 8px; padding: 9px 12px;
  font-size: 13px; background: var(--c-surface); color: var(--c-text); outline: none;
}
.extension-section {
  border-top: 1px solid var(--c-border); padding-top: 14px;
  display: flex; flex-direction: column; gap: 8px;
}
.extension-label { font-size: 12px; font-weight: 700; color: var(--c-text); }
.extension-quick-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
.modal-footer {
  display: flex; justify-content: flex-end; gap: 10px;
  padding: 16px 24px; border-top: 1px solid var(--c-border); background: rgba(0,0,0,0.01);
}

/* ESTATE ADMIN SELF-SERVICE STYLES */
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

@media (max-width: 800px) {
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .form-grid { grid-template-columns: 1fr; }
}

@media (max-width: 560px) {
  .kpi-grid { grid-template-columns: 1fr; }
  .usage-grid, .plans-grid { grid-template-columns: 1fr; }
}
</style>
