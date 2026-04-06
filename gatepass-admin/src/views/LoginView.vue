<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-logo">
        <span>🛡️</span>
        <h1>Wardn Admin</h1>
        <p>Sign in to manage the estate</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input v-model="phone" type="tel" class="form-input" placeholder="e.g. 08024035326" autocomplete="username" required />
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input v-model="password" type="password" class="form-input" placeholder="••••••••" autocomplete="current-password" required />
        </div>
        <div v-if="error" class="login-error">{{ error }}</div>
        <button type="submit" class="btn btn-primary login-btn" :disabled="loading">
          <span v-if="loading" class="spinner" />
          <span v-else>Sign In</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth   = useAuthStore()

const phone    = ref('')
const password = ref('')
const loading  = ref(false)
const error    = ref('')

async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    await auth.login(phone.value, password.value)
    router.push('/dashboard')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Invalid credentials. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  background: var(--c-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.login-card {
  background: var(--c-surface);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  padding: 40px 36px;
  width: 100%;
  max-width: 400px;
}
.login-logo {
  text-align: center;
  margin-bottom: 32px;
}
.login-logo span { font-size: 40px; }
.login-logo h1 { font-size: 22px; font-weight: 800; margin-top: 8px; }
.login-logo p { font-size: 14px; color: var(--c-muted); margin-top: 4px; }
.login-form { display: flex; flex-direction: column; gap: 16px; }
.login-error {
  font-size: 13px; color: var(--c-danger);
  background: var(--c-danger-light);
  padding: 10px 12px; border-radius: var(--radius);
}
.login-btn {
  width: 100%; padding: 12px;
  font-size: 15px; justify-content: center;
  margin-top: 4px;
}
</style>
