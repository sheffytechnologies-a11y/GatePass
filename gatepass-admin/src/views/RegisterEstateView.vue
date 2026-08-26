<template>
  <div class="auth-page">
    <div class="auth-card">
      <template v-if="!activated">
        <div class="auth-logo">
          <span>🏘️</span>
          <h1>Tell us about your estate</h1>
          <p>We'll set you up with a free trial &mdash; up to 3 units, 3 residents each, forever free.</p>
        </div>

        <div class="step-track">
          <div class="step-dot step-dot--done">&check;</div>
          <div class="step-line step-line--done" />
          <div class="step-dot step-dot--active">2</div>
        </div>
        <div class="step-caption">Step 2 of 2 &middot; Your estate</div>

        <form @submit.prevent="handleCreateEstate" class="auth-form">
          <div class="form-group">
            <label class="form-label">Estate name</label>
            <input v-model="name" type="text" class="form-input" placeholder="e.g. PHDL Estate" required />
          </div>
          <div class="form-group">
            <label class="form-label">Address</label>
            <input v-model="address" type="text" class="form-input" placeholder="Street address" />
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">City</label>
              <input v-model="city" type="text" class="form-input" placeholder="City" />
            </div>
            <div class="form-group">
              <label class="form-label">State</label>
              <input v-model="state" type="text" class="form-input" placeholder="State" />
            </div>
          </div>

          <div v-if="error" class="auth-error">{{ error }}</div>

          <button type="submit" class="btn btn-primary auth-btn" :disabled="loading">
            <span v-if="loading" class="spinner" />
            <span v-else>Activate my free trial &rarr;</span>
          </button>
        </form>

        <div class="auth-footer">
          <a href="#" @click.prevent="handleLogout">Log out</a>
        </div>
      </template>

      <template v-else>
        <div class="success-panel">
          <div class="success-icon">&check;</div>
          <h1>Your free trial is live!</h1>
          <p>
            <strong>{{ estateName }}</strong> is ready with up to
            <strong>3 units</strong> and <strong>3 residents per unit</strong>, forever free.
          </p>
          <button class="btn btn-primary auth-btn" @click="goToDashboard">Go to dashboard &rarr;</button>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { onboardingApi } from '@/api'

const router = useRouter()
const auth = useAuthStore()

const name = ref('')
const address = ref('')
const city = ref('')
const state = ref('')
const loading = ref(false)
const error = ref('')
const activated = ref(false)
const estateName = ref('')

async function handleCreateEstate() {
  error.value = ''
  loading.value = true
  try {
    const res = await onboardingApi.createEstate({
      name: name.value,
      address: address.value || undefined,
      city: city.value || undefined,
      state: state.value || undefined,
    })
    estateName.value = res.data?.estate?.name ?? name.value
    activated.value = true
    setTimeout(() => goToDashboard(), 2200)
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Could not create your estate. Please try again.'
  } finally {
    loading.value = false
  }
}

function goToDashboard() {
  router.push('/dashboard')
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<style scoped>
.auth-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top left, rgba(10, 92, 56, 0.12), transparent 30%),
    radial-gradient(circle at bottom right, rgba(0, 201, 122, 0.10), transparent 24%),
    var(--c-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.auth-card {
  background: var(--c-surface);
  border-radius: 30px;
  box-shadow: var(--shadow-md);
  padding: 32px 24px;
  width: 100%;
  max-width: 460px;
}
.auth-logo { text-align: left; margin-bottom: 20px; }
.auth-logo span { font-size: 34px; }
.auth-logo h1 { font-size: 26px; font-weight: 800; margin-top: 8px; font-family: var(--font-display); }
.auth-logo p { font-size: 14px; color: var(--c-muted); margin-top: 6px; }

.step-track { display: flex; align-items: center; gap: 8px; margin-top: 22px; }
.step-dot {
  width: 26px; height: 26px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 800;
  background: var(--c-bg); color: var(--c-muted);
  border: 1.5px solid var(--c-border);
}
.step-dot--active { background: var(--c-primary); color: #fff; border-color: var(--c-primary); }
.step-dot--done { background: var(--c-accent); color: #fff; border-color: var(--c-accent); }
.step-line { flex: 1; height: 2px; background: var(--c-border); }
.step-line--done { background: var(--c-accent); }
.step-caption { font-size: 12px; color: var(--c-muted); margin-top: 8px; font-weight: 600; }

.auth-form { display: flex; flex-direction: column; gap: 16px; margin-top: 20px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.auth-error {
  font-size: 13px; color: var(--c-danger);
  background: var(--c-danger-light);
  padding: 10px 12px; border-radius: var(--radius);
}
.auth-btn { width: 100%; padding: 13px; font-size: 15px; justify-content: center; margin-top: 4px; }
.auth-footer { text-align: center; font-size: 13px; color: var(--c-muted); margin-top: 20px; }
.auth-footer a { color: var(--c-primary); font-weight: 700; }

.success-panel { text-align: center; padding: 12px 4px; }
.success-icon {
  width: 64px; height: 64px; border-radius: 50%; margin: 0 auto 18px;
  background: var(--c-success-light); color: var(--c-success);
  display: flex; align-items: center; justify-content: center;
  font-size: 30px; font-weight: 800;
  animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.success-panel h1 { font-size: 22px; font-weight: 800; font-family: var(--font-display); margin-bottom: 10px; }
.success-panel p { font-size: 14px; color: var(--c-muted); line-height: 1.6; margin-bottom: 22px; }
@keyframes pop { from { transform: scale(0.5); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>
