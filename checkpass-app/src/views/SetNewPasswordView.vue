<template>
  <ion-page>
    <ion-content class="pw-reset-content" :fullscreen="true">
      <div class="pw-reset-screen">
        
        <!-- Header Banner -->
        <div class="primary-header">
          <div class="header-inner">
            <img src="/logo-dark.png" alt="Checkpass" class="brand-logo" />
            <div class="shield-badge">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </div>
            <h1>Secure Your Account</h1>
            <p>Welcome to Checkpass! Please replace your temporary default password with a secure personal password to continue.</p>
          </div>
        </div>

        <!-- Form Card -->
        <div class="form-sheet-card">
          <form @submit.prevent="handleSubmit" class="pw-form">
            
            <!-- Error Banner -->
            <transition name="fade">
              <div v-if="errorMsg" class="error-banner">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <line x1="12" y1="8" x2="12" y2="12"/>
                  <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ errorMsg }}</span>
              </div>
            </transition>

            <!-- Current / Default Password (Pre-filled or Editable) -->
            <div class="field">
              <label class="field-label">Current temporary password</label>
              <div class="input-wrap">
                <input
                  v-model="currentPassword"
                  :type="showCurrent ? 'text' : 'password'"
                  placeholder="Temporary password"
                  required
                />
                <button type="button" class="toggle-pw" @click="showCurrent = !showCurrent">
                  <svg v-if="!showCurrent" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
            </div>

            <!-- New Password -->
            <div class="field">
              <label class="field-label">New personal password <span class="req">*</span></label>
              <div class="input-wrap" :class="{ 'has-error': newPassword && newPassword.length < 8 }">
                <input
                  v-model="newPassword"
                  :type="showNew ? 'text' : 'password'"
                  placeholder="Min. 8 characters"
                  minlength="8"
                  autocomplete="new-password"
                  required
                />
                <button type="button" class="toggle-pw" @click="showNew = !showNew">
                  <svg v-if="!showNew" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <div class="pw-guidance" :class="{ valid: newPassword.length >= 8 }">
                <span class="dot">●</span> Must be at least 8 characters
              </div>
            </div>

            <!-- Confirm Password -->
            <div class="field">
              <label class="field-label">Confirm new password <span class="req">*</span></label>
              <div class="input-wrap" :class="{ 'has-error': confirmPassword && !passwordsMatch }">
                <input
                  v-model="confirmPassword"
                  :type="showConfirm ? 'text' : 'password'"
                  placeholder="Repeat new password"
                  minlength="8"
                  autocomplete="new-password"
                  required
                />
                <button type="button" class="toggle-pw" @click="showConfirm = !showConfirm">
                  <svg v-if="!showConfirm" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <div v-if="confirmPassword && !passwordsMatch" class="field-error-text">
                Passwords do not match
              </div>
            </div>

            <!-- Submit Button -->
            <button
              type="submit"
              class="submit-btn"
              :disabled="!canSubmit || loading"
            >
              <span v-if="loading" class="spinner"></span>
              <span v-else class="btn-text">
                Save &amp; Enter Checkpass
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </span>
            </button>

            <!-- Security Notice -->
            <div class="security-foot">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <span>Encrypted with SHA-256 &amp; NDPR compliant</span>
            </div>

          </form>
        </div>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { IonPage, IonContent } from '@ionic/vue'
import { authApi } from '@/api/auth'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const { showToast } = useToast()

const currentPassword = ref(sessionStorage.getItem('w_temp_pwd') || '123456')
const newPassword     = ref('')
const confirmPassword = ref('')
const loading         = ref(false)
const errorMsg        = ref('')

const showCurrent = ref(false)
const showNew     = ref(false)
const showConfirm = ref(false)

const passwordsMatch = computed(() => {
  return newPassword.value === confirmPassword.value
})

const canSubmit = computed(() => {
  return (
    currentPassword.value.length >= 1 &&
    newPassword.value.length >= 8 &&
    passwordsMatch.value
  )
})

async function handleSubmit() {
  errorMsg.value = ''

  if (newPassword.value.length < 8) {
    errorMsg.value = 'New password must be at least 8 characters long.'
    return
  }

  if (newPassword.value !== confirmPassword.value) {
    errorMsg.value = 'Passwords do not match. Please verify.'
    return
  }

  loading.value = true
  try {
    await authApi.changePassword(currentPassword.value, newPassword.value)
    sessionStorage.removeItem('w_temp_pwd')
    showToast('Password updated successfully! Welcome to Checkpass.', 'success')
    router.replace('/tabs/home')
  } catch (e: any) {
    const msg = e.response?.data?.message || e.response?.data?.errors?.newPassword?.[0] || 'Could not update password. Please check your credentials.'
    errorMsg.value = msg
    showToast(msg, 'error')
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.pw-reset-content {
  --background: #0A5C38;
}

.pw-reset-screen {
  min-height: 100%;
  display: flex;
  flex-direction: column;
  background: #0A5C38;
}

.primary-header {
  padding: 44px 24px 28px;
  color: #FFFFFF;
  text-align: center;
}

.header-inner {
  max-width: 440px;
  margin: 0 auto;
}

.brand-logo {
  height: 38px;
  width: auto;
  margin: 0 auto 16px;
  display: block;
  filter: brightness(0) invert(1);
}

.shield-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 54px;
  height: 54px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
  border: 1.5px solid rgba(255, 255, 255, 0.3);
  color: #FFFFFF;
  margin-bottom: 14px;
}

.header-inner h1 {
  font-size: 24px;
  font-weight: 800;
  margin: 0 0 8px;
  color: #FFFFFF;
  letter-spacing: -0.02em;
}

.header-inner p {
  font-size: 13.5px;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.88);
  margin: 0;
}

.form-sheet-card {
  flex: 1;
  background: #FFFFFF;
  border-top-left-radius: 28px;
  border-top-right-radius: 28px;
  padding: 28px 24px 40px;
  box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.12);
}

.pw-form {
  max-width: 440px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.error-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #FEF2F2;
  border: 1px solid #FCA5A5;
  color: #B91C1C;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-label {
  font-size: 13px;
  font-weight: 700;
  color: #334155;
}

.req {
  color: #DC2626;
}

.input-wrap {
  position: relative;
  display: flex;
  align-items: center;
  background: #F8FAFC;
  border: 1.5px solid #E2E8F0;
  border-radius: 14px;
  transition: all 0.2s ease;
}

.input-wrap:focus-within {
  border-color: #0A5C38;
  background: #FFFFFF;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.12);
}

.input-wrap.has-error {
  border-color: #EF4444;
  background: #FEF2F2;
}

.input-wrap input {
  flex: 1;
  height: 48px;
  padding: 0 16px;
  border: none;
  background: transparent;
  font-size: 15px;
  color: #0F172A;
  outline: none;
}

.toggle-pw {
  padding: 0 14px;
  height: 48px;
  background: transparent;
  border: none;
  color: #64748B;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.pw-guidance {
  font-size: 12px;
  color: #64748B;
  display: flex;
  align-items: center;
  gap: 5px;
}

.pw-guidance.valid {
  color: #0A5C38;
  font-weight: 600;
}

.field-error-text {
  font-size: 12px;
  color: #DC2626;
  font-weight: 600;
}

.submit-btn {
  margin-top: 10px;
  width: 100%;
  height: 52px;
  border-radius: 14px;
  background: #0A5C38;
  color: #FFFFFF;
  border: none;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: transform 0.15s ease, background 0.15s ease;
  box-shadow: 0 4px 16px rgba(10, 92, 56, 0.25);
}

.submit-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

.submit-btn:active:not(:disabled) {
  transform: scale(0.98);
}

.btn-text {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.spinner {
  width: 22px;
  height: 22px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-top-color: #FFFFFF;
  border-radius: 50%;
  animation: spin 0.75s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.security-foot {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 6px;
  font-size: 12px;
  color: #64748B;
}
</style>
