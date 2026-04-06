<template>
  <ion-page>
    <ion-content class="login-content" :fullscreen="true">
      <div class="login-wrap">

        <!-- Logo -->
        <div class="logo-block">
          <div class="wordmark">Gatepass</div>
          <div class="tagline">Estate Security & Visitor Management</div>
        </div>

        <!-- Heading -->
        <div class="heading-block">
          <h1>Welcome back</h1>
          <p>Sign in to continue</p>
        </div>

        <!-- Expired / suspended banners -->
        <div v-if="expiredBanner" class="banner banner-warn">
          Your session expired. Please log in again.
        </div>
        <div v-if="suspendedBanner" class="banner banner-error">
          Your account has been suspended. Contact your estate manager.
        </div>

        <!-- Form -->
        <div class="form-block">
          <div class="field">
            <label>Phone Number</label>
            <div class="input-wrap" :class="{ 'input-error': !!fieldError.phone }">
              <span class="prefix">+234</span>
              <input
                v-model="phone"
                type="tel"
                inputmode="numeric"
                placeholder="080 0000 0000"
                @input="stripPrefix"
              />
            </div>
          </div>

          <div class="field">
            <label>Password</label>
            <div class="input-wrap" :class="{ 'input-error': !!authError }">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Enter your password"
              />
              <button class="toggle-pw" @click="showPassword = !showPassword" type="button">
                <ion-icon :icon="showPassword ? eyeOffOutline : eyeOutline" />
              </button>
            </div>
            <div v-if="authError" class="inline-error">{{ authError }}</div>
          </div>

          <ion-button
            expand="block"
            color="primary"
            :disabled="!canSubmit || loading"
            @click="handleLogin"
            class="login-btn"
          >
            <ion-spinner v-if="loading" name="crescent" />
            <span v-else>Sign In</span>
          </ion-button>

          <div class="forgot">
            <a href="#" @click.prevent="showForgotInfo">Forgot password?</a>
          </div>
        </div>

      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { IonPage, IonContent, IonButton, IonSpinner, IonIcon, alertController } from '@ionic/vue'
import { eyeOutline, eyeOffOutline } from 'ionicons/icons'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { authApi } from '@/api/auth'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()
const { showToast } = useToast()

const phone        = ref('')
const password     = ref('')
const loading      = ref(false)
const authError    = ref('')
const showPassword = ref(false)
const fieldError   = ref({ phone: '', password: '' })

const expiredBanner   = computed(() => route.query.expired === '1')
const suspendedBanner = computed(() => route.query.suspended === '1')
const canSubmit = computed(() => phone.value.trim().length >= 10 && password.value.length >= 1)

// Strip any accidentally typed +234 prefix
function stripPrefix() {
  phone.value = phone.value.replace(/^\+?234/, '').replace(/\D/g, '')
}

async function handleLogin() {
  authError.value = ''
  loading.value = true

  try {
    const res = await authApi.login(phone.value, password.value)
    const { token, refreshToken, resident, userType, data: user } = res.data
    auth.setSession({ token, refreshToken, resident, userType, user })

    showToast('Welcome back!', 'success')

    if (userType === 'security' || auth.onboardingComplete) {
      router.replace('/tabs/home')
    } else {
      router.replace('/onboarding')
    }
  } catch (e: any) {
    const code = e.response?.data?.code
    if (code === 'AUTH_INVALID_CREDENTIALS') {
      authError.value = 'Incorrect phone number or password. Try again.'
      showToast('Incorrect phone number or password.', 'error')
    } else if (code === 'AUTH_ACCOUNT_DISABLED' || code === 'ACCOUNT_SUSPENDED') {
      authError.value = 'Your account has been suspended. Contact your estate manager.'
      showToast('Account suspended. Contact your estate manager.', 'error')
    } else {
      authError.value = 'Something went wrong. Try again.'
      showToast('Something went wrong. Try again.', 'error')
    }
  } finally {
    loading.value = false
  }
}

async function showForgotInfo() {
  const alert = await alertController.create({
    header: 'Forgot Password',
    message: 'Contact your estate manager to reset your password.',
    buttons: ['OK'],
  })
  await alert.present()
}
</script>

<style scoped>
.login-content { --background: var(--w-bg); }
.login-wrap {
  min-height: 100vh;
  display: flex; flex-direction: column;
  padding: 60px 24px 40px;
}
.logo-block { margin-bottom: 40px; }
.wordmark   { font-family: var(--w-font-display); font-size: 36px; font-weight: 800; color: var(--w-primary); }
.tagline    { font-size: 13px; color: var(--w-muted); margin-top: 4px; }
.heading-block { margin-bottom: 32px; }
.heading-block h1 { font-family: var(--w-font-display); font-size: 26px; font-weight: 700; margin: 0 0 6px; color: var(--w-text); }
.heading-block p  { font-size: 15px; color: var(--w-muted); margin: 0; }
.banner { border-radius: var(--w-radius-md); padding: 12px 16px; font-size: 14px; margin-bottom: 16px; }
.banner-warn  { background: var(--w-warning-light); color: var(--w-warning); }
.banner-error { background: var(--w-danger-light); color: var(--w-danger); }
.form-block   { display: flex; flex-direction: column; gap: 18px; }
.field label  { display: block; font-size: 13px; font-weight: 600; color: var(--w-text); margin-bottom: 8px; }
.input-wrap {
  display: flex; align-items: center;
  background: var(--w-surface); border: 1.5px solid var(--w-border);
  border-radius: var(--w-radius-md); overflow: hidden;
}
.input-wrap.input-error { border-color: var(--w-danger); }
.prefix     { padding: 0 10px 0 14px; font-size: 15px; color: var(--w-muted); font-weight: 600; border-right: 1px solid var(--w-border); }
.input-wrap input {
  flex: 1; border: none; outline: none; padding: 14px;
  font-size: 15px; background: transparent; color: var(--w-text); font-family: var(--w-font-body);
}
.toggle-pw  { background: none; border: none; padding: 0 14px; color: var(--w-muted); font-size: 20px; cursor: pointer; }
.inline-error { font-size: 13px; color: var(--w-danger); margin-top: 6px; }
.login-btn  { --border-radius: 14px; margin-top: 8px; height: 52px; font-size: 16px; font-weight: 700; }
.forgot     { text-align: center; }
.forgot a   { font-size: 14px; color: var(--w-primary); text-decoration: none; }
</style>
