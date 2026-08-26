<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-logo">
        <span>🛡️</span>
        <h1>Create your Gatepass account</h1>
        <p>Set up estate access management for your community in minutes.</p>
      </div>

      <div class="step-track">
        <div class="step-dot step-dot--active">1</div>
        <div class="step-line" />
        <div class="step-dot">2</div>
      </div>
      <div class="step-caption">Step 1 of 2 &middot; Your account</div>

      <form @submit.prevent="handleRegister" class="auth-form">
        <div class="form-group">
          <label class="form-label">Full name</label>
          <input v-model="name" type="text" class="form-input" placeholder="Jane Doe" autocomplete="name" required />
        </div>
        <div class="form-group">
          <label class="form-label">Email address</label>
          <input v-model="email" type="email" class="form-input" placeholder="you@estate.com" autocomplete="email" required />
        </div>
        <div class="form-group">
          <label class="form-label">Phone number</label>
          <input v-model="phone" type="tel" class="form-input" placeholder="e.g. 08024035326" autocomplete="tel" />
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input v-model="password" type="password" class="form-input" placeholder="At least 6 characters" autocomplete="new-password" minlength="6" required />
        </div>

        <div v-if="error" class="auth-error">{{ error }}</div>

        <button type="submit" class="btn btn-primary auth-btn" :disabled="loading">
          <span v-if="loading" class="spinner" />
          <span v-else>Continue &rarr;</span>
        </button>
      </form>

      <div class="auth-footer">
        Already have an account? <router-link to="/login">Sign in</router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const name = ref('')
const email = ref('')
const phone = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function handleRegister() {
  error.value = ''
  loading.value = true
  try {
    await auth.registerAdmin(name.value, email.value, password.value, phone.value || undefined)
    router.push('/register/estate')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
    const firstFieldError = err.response?.data?.errors
      ? Object.values(err.response.data.errors)[0]?.[0]
      : undefined
    error.value = firstFieldError ?? err.response?.data?.message ?? 'Could not create your account. Please try again.'
  } finally {
    loading.value = false
  }
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
.step-line { flex: 1; height: 2px; background: var(--c-border); }
.step-caption { font-size: 12px; color: var(--c-muted); margin-top: 8px; font-weight: 600; }

.auth-form { display: flex; flex-direction: column; gap: 16px; margin-top: 20px; }
.auth-error {
  font-size: 13px; color: var(--c-danger);
  background: var(--c-danger-light);
  padding: 10px 12px; border-radius: var(--radius);
}
.auth-btn { width: 100%; padding: 13px; font-size: 15px; justify-content: center; margin-top: 4px; }
.auth-footer { text-align: center; font-size: 13px; color: var(--c-muted); margin-top: 20px; }
.auth-footer a { color: var(--c-primary); font-weight: 700; }
</style>
