<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-logo">
        <span>🛡️</span>
        <h1>Gatepass</h1>
        <p>Sign in to manage your estate</p>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input v-model="email" type="email" class="form-input" placeholder="admin@estate.com" autocomplete="username" required />
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

      <div class="login-footer">
        New estate? <router-link to="/register">Create a free account</router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth   = useAuthStore()

const email = ref('')
const password = ref('')
const loading  = ref(false)
const error    = ref('')

async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    await auth.loginAdmin(email.value, password.value)
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
  background:
    radial-gradient(circle at top left, rgba(10, 92, 56, 0.12), transparent 30%),
    radial-gradient(circle at bottom right, rgba(0, 201, 122, 0.10), transparent 24%),
    var(--c-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.login-card {
  background: var(--c-surface);
  border-radius: 30px;
  box-shadow: var(--shadow-md);
  padding: 32px 24px;
  width: 100%;
  max-width: 430px;
}
.login-logo {
  text-align: left;
  margin-bottom: 24px;
}
.login-logo span { font-size: 34px; }
.login-logo h1 { font-size: 30px; font-weight: 800; margin-top: 8px; font-family: var(--font-display); }
.login-logo p { font-size: 14px; color: var(--c-muted); margin-top: 6px; }
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
.login-footer { text-align: center; font-size: 13px; color: var(--c-muted); margin-top: 20px; }
.login-footer a { color: var(--c-primary); font-weight: 700; }
</style>
