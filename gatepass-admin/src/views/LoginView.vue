<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-logo">
        <span>🛡️</span>
        <h1>CheckPass Mobile</h1>
        <p>Admin and security teams share one mobile workspace</p>
      </div>

      <div class="mode-row">
        <button class="mode-chip" :class="{ active: mode === 'security' }" @click="mode = 'security'">Security</button>
        <button class="mode-chip" :class="{ active: mode === 'admin' }" @click="mode = 'admin'">Admin</button>
      </div>

      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label class="form-label">{{ mode === 'admin' ? 'Email Address' : 'Phone Number' }}</label>
          <input v-model="identifier" :type="mode === 'admin' ? 'email' : 'tel'" class="form-input" :placeholder="mode === 'admin' ? 'admin@estate.com' : 'e.g. 08024035326'" autocomplete="username" required />
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

const mode = ref<'admin' | 'security'>('security')
const identifier = ref('')
const password = ref('')
const loading  = ref(false)
const error    = ref('')

async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    if (mode.value === 'admin') {
      await auth.loginAdmin(identifier.value, password.value)
      router.push('/dashboard')
    } else {
      await auth.loginSecurity(identifier.value, password.value)
      router.push('/access')
    }
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
.mode-row { display: flex; gap: 10px; margin-bottom: 18px; }
.mode-chip {
  flex: 1;
  border: none;
  border-radius: 999px;
  background: #e5ece7;
  color: #4b5e54;
  padding: 12px 14px;
  font-weight: 800;
}
.mode-chip.active { background: var(--c-primary); color: white; }
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
