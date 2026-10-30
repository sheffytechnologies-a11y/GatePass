<template>
  <ion-page>
    <ion-content class="forgot-content" :fullscreen="true">
      <div class="forgot-screen">

        <!-- Top Header with Back Navigation -->
        <div class="primary-header">
          <div class="header-top-bar">
            <button type="button" class="back-nav-btn" @click="goBack" aria-label="Go back">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
              </svg>
            </button>
            <img src="/logo-dark.png" alt="Checkpass" class="brand-logo" />
            <div style="width: 36px;" /> <!-- Spacer -->
          </div>

          <div class="header-text-block">
            <h1>{{ step === 1 ? 'Reset Password' : 'Verify & Set Password' }}</h1>
            <p v-if="step === 1">Enter your registered estate phone number or email to receive a 6-digit verification code.</p>
            <p v-else>We dispatched a 6-digit code to <strong>{{ identifier }}</strong>. Enter it below with your new password.</p>
          </div>
        </div>

        <!-- Form Card -->
        <div class="form-sheet-card">

          <!-- Error Alert -->
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

          <!-- Debug OTP Alert (Local Testing helper) -->
          <div v-if="debugOtp" class="debug-otp-banner">
            <span>Test OTP Code: <strong>{{ debugOtp }}</strong></span>
            <button type="button" class="use-otp-btn" @click="otp = debugOtp">Fill Code</button>
          </div>

          <!-- STEP 1: REQUEST CODE -->
          <form v-if="step === 1" @submit.prevent="handleRequestOtp" class="recovery-form">
            <div class="field">
              <label class="field-label">Registered Phone or Email <span class="req">*</span></label>
              <div class="input-wrap">
                <span class="field-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <input
                  v-model="identifier"
                  type="text"
                  placeholder="e.g. 0813 248 9619 or name@domain.com"
                  autocomplete="username"
                  required
                />
              </div>
            </div>

            <button
              type="submit"
              class="primary-btn"
              :disabled="loading || identifier.trim().length < 5"
            >
              <span v-if="loading" class="spinner"></span>
              <span v-else class="btn-text">
                Send Verification Code
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </span>
            </button>
          </form>

          <!-- STEP 2: VERIFY CODE & NEW PASSWORD -->
          <form v-else @submit.prevent="handleResetPassword" class="recovery-form">
            
            <!-- OTP Input -->
            <div class="field">
              <div class="field-header-row">
                <label class="field-label">6-digit verification code <span class="req">*</span></label>
                <button
                  type="button"
                  class="resend-link"
                  :disabled="resendCooldown > 0 || loading"
                  @click="handleRequestOtp"
                >
                  {{ resendCooldown > 0 ? `Resend in ${resendCooldown}s` : 'Resend Code' }}
                </button>
              </div>
              <div class="input-wrap otp-input-wrap">
                <input
                  v-model="otp"
                  type="text"
                  inputmode="numeric"
                  maxlength="6"
                  placeholder="••••••"
                  class="otp-input"
                  required
                />
              </div>
            </div>

            <!-- New Password -->
            <div class="field">
              <label class="field-label">New password <span class="req">*</span></label>
              <div class="input-wrap">
                <input
                  v-model="newPassword"
                  :type="showNewPassword ? 'text' : 'password'"
                  placeholder="At least 8 characters"
                  minlength="8"
                  autocomplete="new-password"
                  required
                />
                <button type="button" class="toggle-pw" @click="showNewPassword = !showNewPassword">
                  <svg v-if="!showNewPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <div class="pw-guidance" :class="{ valid: newPassword.length >= 8 }">
                <span class="dot">●</span> Must be at least 8 characters
              </div>
            </div>

            <!-- Confirm New Password -->
            <div class="field">
              <label class="field-label">Confirm new password <span class="req">*</span></label>
              <div class="input-wrap">
                <input
                  v-model="confirmPassword"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  placeholder="Repeat new password"
                  minlength="8"
                  autocomplete="new-password"
                  required
                />
                <button type="button" class="toggle-pw" @click="showConfirmPassword = !showConfirmPassword">
                  <svg v-if="!showConfirmPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
              </div>
              <div v-if="confirmPassword && newPassword !== confirmPassword" class="field-error-text">
                Passwords do not match
              </div>
            </div>

            <button
              type="submit"
              class="primary-btn"
              :disabled="loading || !canReset"
            >
              <span v-if="loading" class="spinner"></span>
              <span v-else class="btn-text">
                Reset Password &amp; Enter Checkpass
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </span>
            </button>
          </form>

          <!-- Back to Login Link -->
          <div class="bottom-back-row">
            <button type="button" class="back-link-btn" @click="goBack">
              &larr; Remember your password? Return to login
            </button>
          </div>

        </div>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { IonPage, IonContent } from '@ionic/vue'
import { authApi } from '@/api/auth'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const auth   = useAuthStore()
const { showToast } = useToast()

const step            = ref<1 | 2>(1)
const identifier      = ref('')
const otp             = ref('')
const newPassword     = ref('')
const confirmPassword = ref('')
const loading         = ref(false)
const errorMsg        = ref('')
const debugOtp        = ref('')

const showNewPassword     = ref(false)
const showConfirmPassword = ref(false)

const resendCooldown = ref(0)
let timer: ReturnType<typeof setInterval> | null = null

function startCooldown() {
  resendCooldown.value = 60
  if (timer) clearInterval(timer)
  timer = setInterval(() => {
    if (resendCooldown.value > 0) {
      resendCooldown.value--
    } else if (timer) {
      clearInterval(timer)
      timer = null
    }
  }, 1000)
}

onUnmounted(() => {
  if (timer) clearInterval(timer)
})

const canReset = computed(() => {
  return (
    otp.value.trim().length === 6 &&
    newPassword.value.length >= 8 &&
    newPassword.value === confirmPassword.value
  )
})

async function handleRequestOtp() {
  errorMsg.value = ''
  loading.value  = true
  try {
    const res = await authApi.forgotPassword(identifier.value.trim())
    step.value = 2
    startCooldown()
    showToast(res.data?.message || 'Verification code dispatched.', 'success')
    if (res.data?.debugOtp) {
      debugOtp.value = res.data.debugOtp
    }
  } catch (e: any) {
    const msg = e.response?.data?.message || e.response?.data?.errors?.identifier?.[0] || 'Could not send verification code.'
    errorMsg.value = msg
    showToast(msg, 'error')
  } finally {
    loading.value = false
  }
}

async function handleResetPassword() {
  errorMsg.value = ''

  if (newPassword.value.length < 8) {
    errorMsg.value = 'Password must be at least 8 characters long.'
    return
  }

  if (newPassword.value !== confirmPassword.value) {
    errorMsg.value = 'Passwords do not match.'
    return
  }

  loading.value = true
  try {
    const res = await authApi.resetPassword(identifier.value.trim(), otp.value.trim(), newPassword.value)
    const { token, refreshToken, resident, userType, data: user } = res.data
    
    // Auto-login user with returned credentials
    auth.setSession({ token, refreshToken, resident, userType, user })
    showToast('Password reset successfully! Welcome to Checkpass.', 'success')
    router.replace('/tabs/home')
  } catch (e: any) {
    const msg = e.response?.data?.message || e.response?.data?.errors?.otp?.[0] || e.response?.data?.errors?.newPassword?.[0] || 'Password reset failed. Please check the code.'
    errorMsg.value = msg
    showToast(msg, 'error')
  } finally {
    loading.value = false
  }
}

function goBack() {
  if (step.value === 2) {
    step.value = 1
  } else {
    router.replace('/login')
  }
}
</script>

<style scoped>
.forgot-content {
  --background: #0A5C38;
}

.forgot-screen {
  min-height: 100%;
  display: flex;
  flex-direction: column;
  background: #0A5C38;
}

.primary-header {
  padding: 38px 24px 28px;
  color: #FFFFFF;
}

.header-top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.back-nav-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.25);
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.brand-logo {
  height: 32px;
  width: auto;
  filter: brightness(0) invert(1);
}

.header-text-block {
  max-width: 440px;
  margin: 0 auto;
  text-align: center;
}

.header-text-block h1 {
  font-size: 24px;
  font-weight: 800;
  margin: 0 0 8px;
  color: #FFFFFF;
  letter-spacing: -0.02em;
}

.header-text-block p {
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

.recovery-form {
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
  margin-bottom: 16px;
}

.debug-otp-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  color: #1E40AF;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 13px;
  margin-bottom: 16px;
}

.use-otp-btn {
  background: #2563EB;
  color: #FFFFFF;
  border: none;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.field-label {
  font-size: 13px;
  font-weight: 700;
  color: #334155;
}

.req {
  color: #DC2626;
}

.resend-link {
  background: transparent;
  border: none;
  padding: 0;
  font-size: 12.5px;
  font-weight: 700;
  color: #0A5C38;
  cursor: pointer;
}

.resend-link:disabled {
  color: #94A3B8;
  cursor: not-allowed;
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

.field-icon {
  position: absolute;
  left: 14px;
  display: flex;
  align-items: center;
  color: #94A3B8;
  pointer-events: none;
}

.input-wrap input {
  flex: 1;
  height: 48px;
  padding: 0 16px 0 42px;
  border: none;
  background: transparent;
  font-size: 15px;
  color: #0F172A;
  outline: none;
}

.otp-input-wrap input {
  padding: 0 16px;
  text-align: center;
  letter-spacing: 0.35em;
  font-size: 22px;
  font-weight: 800;
  color: #0A5C38;
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

.primary-btn {
  margin-top: 8px;
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

.primary-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

.primary-btn:active:not(:disabled) {
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

.bottom-back-row {
  margin-top: 24px;
  text-align: center;
}

.back-link-btn {
  background: transparent;
  border: none;
  font-size: 13px;
  color: #64748B;
  font-weight: 600;
  cursor: pointer;
}

.back-link-btn:hover {
  color: #0A5C38;
  text-decoration: underline;
}
</style>
