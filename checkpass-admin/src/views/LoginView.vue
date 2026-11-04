<template>
  <div class="auth-page">
    <div class="auth-container">

      <!-- ─── LEFT HERO PANEL: SLIDER CONSISTENT WITH CREATE ACCOUNT ─── -->
      <div class="hero-panel" @mouseenter="pauseRotation" @mouseleave="resumeRotation">
        <div class="hero-pattern-overlay"></div>

        <!-- Top Brand Logo -->
        <header class="hero-brand-header">
          <router-link to="/" class="brand-logo-link" aria-label="Checkpass Home">
            <img src="/checkpass-logo-white.svg" alt="Checkpass" class="brand-logo-img" />
          </router-link>
        </header>

        <!-- Cross-fading Cinematic Images Stage -->
        <div class="hero-image-stage">
          <div
            v-for="(moment, idx) in moments"
            :key="moment.id"
            class="hero-slide-layer"
            :class="{ 'is-active': activeMomentIndex === idx }"
          >
            <img
              :src="moment.image"
              :alt="moment.headline"
              class="hero-stage-img"
            />
          </div>

          <!-- Gradient: Vignette at top, fading to deep green at bottom for text readability -->
          <div class="hero-stage-gradient"></div>
          <div class="frame-glass-reflection"></div>
        </div>

        <!-- Bottom Text Content (Over Gradient) -->
        <div class="hero-text-block">
          <transition name="moment-fade" mode="out-in">
            <h2 :key="'h-' + activeMoment.id" class="hero-headline">
              {{ activeMoment.headline }}
            </h2>
          </transition>

          <transition name="moment-fade" mode="out-in">
            <p :key="'sub-' + activeMoment.id" class="hero-copy">
              {{ activeMoment.subhead }}
            </p>
          </transition>

          <!-- Enterprise Security Footer Badge -->
          <div class="hero-footer-trust">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shield-icon">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Powered by Sheffy Technologies &middot; Enterprise Security</span>
          </div>
        </div>
      </div>

      <!-- ─── RIGHT FORM PANEL: CLEAN & STREAMLINED ─── -->
      <div class="form-interaction-section">
        <div class="form-inner-container">

          <!-- ════════════════════════════════════════════════════════════════ -->
          <!-- VIEW A: SIGN IN FORM                                             -->
          <!-- ════════════════════════════════════════════════════════════════ -->
          <div v-if="!showForgotView">

            <!-- Top Header: "Sign in as" -->
            <div class="form-header-group">
              <h1 class="form-title">Sign in as</h1>
            </div>

            <!-- Followed by Role Navigation Bar with Person icon for Resident -->
            <div class="role-tabs-wrap">
              <div class="role-tabs-grid" role="tablist">
                <!-- Admin Tab -->
                <button
                  type="button"
                  class="role-tab"
                  :class="{ active: activeRole === 'admin' }"
                  @click="setRole('admin')"
                  role="tab"
                  :aria-selected="activeRole === 'admin'"
                >
                  <!-- Phosphor Buildings Icon -->
                  <svg viewBox="0 0 256 256" width="18" height="18" fill="currentColor" class="tab-icon">
                    <path d="M240,208H224V96a8,8,0,0,0-8-8H168V40a8,8,0,0,0-8-8H40a8,8,0,0,0-8,8V208H16a8,8,0,0,0,0,16H240a8,8,0,0,0,0-16ZM48,48H152V208H48ZM208,104V208H168V104ZM88,80a8,8,0,0,1,8-8h16a8,8,0,0,1,0,16H96A8,8,0,0,1,88,80Zm0,40a8,8,0,0,1,8-8h16a8,8,0,0,1,0,16H96A8,8,0,0,1,88,120Zm0,40a8,8,0,0,1,8-8h16a8,8,0,0,1,0,16H96A8,8,0,0,1,88,160Z"/>
                  </svg>
                  <span class="role-name">Estate Admin</span>
                </button>

                <!-- Security Tab -->
                <button
                  type="button"
                  class="role-tab"
                  :class="{ active: activeRole === 'guard' }"
                  @click="setRole('guard')"
                  role="tab"
                  :aria-selected="activeRole === 'guard'"
                >
                  <!-- Phosphor ShieldCheck Icon -->
                  <svg viewBox="0 0 256 256" width="18" height="18" fill="currentColor" class="tab-icon">
                    <path d="M208,40H48A16,16,0,0,0,32,56v56c0,82.4,62.74,123.63,92.51,134.78a8.26,8.26,0,0,0,6.98,0C161.26,235.63,224,194.4,224,112V56A16,16,0,0,0,208,40Zm0,72c0,71.22-54.3,107.41-80,118.25C102.3,219.41,48,183.22,48,112V56H208Zm-34.34-29.66a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,132.69l50.34-50.35A8,8,0,0,1,173.66,82.34Z"/>
                  </svg>
                  <span class="role-name">Security</span>
                </button>

                <!-- Resident Tab: Authentic Phosphor Person (User) Icon -->
                <button
                  type="button"
                  class="role-tab"
                  :class="{ active: activeRole === 'resident' }"
                  @click="setRole('resident')"
                  role="tab"
                  :aria-selected="activeRole === 'resident'"
                >
                  <!-- Phosphor User (Person) Icon -->
                  <svg viewBox="0 0 256 256" width="18" height="18" fill="currentColor" class="tab-icon">
                    <path d="M230.92,212c-15.23-26.33-38.7-45.21-66.09-54.16a72,72,0,1,0-73.66,0C63.78,166.78,40.31,185.66,25.08,212a8,8,0,1,0,13.85,8c18.84-32.56,52.14-52,89.07-52s70.23,19.44,89.07,52a8,8,0,1,0,13.85-8ZM72,96a56,56,0,1,1,56,56A56.06,56.06,0,0,1,72,96Z"/>
                  </svg>
                  <span class="role-name">Resident</span>
                </button>
              </div>
            </div>

            <!-- Error Alert -->
            <transition name="alert-slide">
              <div v-if="error" class="auth-alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <line x1="12" y1="8" x2="12" y2="12"/>
                  <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ error }}</span>
              </div>
            </transition>

            <!-- Resident Success Card -->
            <transition name="alert-slide">
              <div v-if="residentLoggedIn" class="resident-success-card">
                <div class="success-header">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <strong>Welcome back!</strong>
                </div>
                <p>You are authenticated. Launch the resident mobile app:</p>
                <a :href="residentAppUrl" class="btn-launch-resident">
                  Open Checkpass Resident App
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                </a>
              </div>
            </transition>

            <!-- Sign-In Form -->
            <form v-if="!residentLoggedIn" @submit.prevent="handleLogin" class="signin-form">

              <!-- Email Address input -->
              <div class="input-block">
                <label class="input-label" for="login-email">{{ inputLabel }}</label>
                <div class="input-wrapper">
                  <span class="input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                  </span>
                  <input
                    id="login-email"
                    v-model="identifier"
                    :type="activeRole === 'admin' ? 'email' : 'text'"
                    class="text-input"
                    :placeholder="inputPlaceholder"
                    :autocomplete="activeRole === 'admin' ? 'email' : 'username'"
                    required
                  />
                </div>
              </div>

              <!-- Password with "Forgot password?" Link -->
              <div class="input-block">
                <div class="label-row">
                  <label class="input-label" for="login-password">Password</label>
                  <button
                    type="button"
                    class="forgot-link-btn"
                    @click="openForgotPassword"
                  >
                    Forgot password?
                  </button>
                </div>
                <div class="input-wrapper">
                  <span class="input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  </span>
                  <input
                    id="login-password"
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    class="text-input with-toggle"
                    placeholder="••••••••"
                    autocomplete="current-password"
                    required
                  />
                  <button
                    type="button"
                    class="password-toggle"
                    @click="showPassword = !showPassword"
                    aria-label="Toggle password visibility"
                  >
                    <svg v-if="!showPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                  </button>
                </div>
              </div>

              <!-- Submit Button (Just "Sign In") -->
              <button
                type="submit"
                class="submit-signin-btn"
                :disabled="loading || !canSubmit"
              >
                <BrandSpinner v-if="loading" :size="18" color="#ffffff" />
                <span v-else class="btn-content">
                  Sign In
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
              </button>

            </form>

            <!-- Footer Switch to Registration -->
            <div class="form-bottom-links">
              <template v-if="activeRole === 'admin'">
                <span class="new-estate-text">Setting up a new estate?</span>
                <router-link to="/register" class="register-link">Create a free account &rarr;</router-link>
              </template>
              <template v-else-if="activeRole === 'guard'">
                <span class="new-estate-text">Gate security staff? Contact your estate manager to be assigned to a post.</span>
              </template>
              <template v-else>
                <span class="new-estate-text">Prefer mobile app?</span>
                <a :href="residentAppUrl" class="register-link">Open resident web app &rarr;</a>
              </template>
            </div>

          </div>

          <!-- ════════════════════════════════════════════════════════════════ -->
          <!-- VIEW B: FORGOT PASSWORD RECOVERY FLOW (ADMIN, RESIDENT, GUARD)     -->
          <!-- ════════════════════════════════════════════════════════════════ -->
          <div v-else class="forgot-flow-container">

            <!-- Back to Sign In Link -->
            <button type="button" class="back-link-btn" @click="closeForgotPassword">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
              Back to Sign In
            </button>

            <div class="form-header-group">
              <h1 class="form-title">Reset Password</h1>
              <p class="forgot-guide-text">{{ forgotGuideText }}</p>
            </div>

            <!-- Error Alert -->
            <transition name="alert-slide">
              <div v-if="forgotError" class="auth-alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <line x1="12" y1="8" x2="12" y2="12"/>
                  <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ forgotError }}</span>
              </div>
            </transition>

            <!-- STEP 1: REQUEST VERIFICATION CODE -->
            <form v-if="forgotStep === 'request'" @submit.prevent="handleSendResetOtp" class="signin-form">
              <div class="input-block">
                <label class="input-label" for="forgot-identifier">{{ forgotIdentifierLabel }}</label>
                <div class="input-wrapper">
                  <span class="input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                  </span>
                  <input
                    id="forgot-identifier"
                    v-model="forgotIdentifier"
                    :type="activeRole === 'admin' ? 'email' : 'text'"
                    class="text-input"
                    :placeholder="forgotPlaceholder"
                    required
                  />
                </div>
              </div>

              <button
                type="submit"
                class="submit-signin-btn"
                :disabled="forgotLoading || forgotIdentifier.trim().length < 3"
              >
                <BrandSpinner v-if="forgotLoading" :size="18" color="#ffffff" />
                <span v-else class="btn-content">
                  Send 6-Digit Code
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
              </button>
            </form>

            <!-- STEP 2: VERIFY OTP AND SET NEW PASSWORD -->
            <form v-else-if="forgotStep === 'verify'" @submit.prevent="handlePerformPasswordReset" class="signin-form">
              <div class="auth-info-banner">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                <span>Code dispatched to <strong>{{ forgotIdentifier }}</strong></span>
              </div>

              <div class="input-block">
                <label class="input-label" for="reset-otp">6-Digit Verification Code</label>
                <div class="input-wrapper">
                  <input
                    id="reset-otp"
                    v-model="resetOtp"
                    type="text"
                    maxlength="6"
                    class="text-input otp-field"
                    placeholder="e.g. 123456"
                    required
                  />
                </div>
              </div>

              <div class="input-block">
                <label class="input-label" for="reset-new-password">New Password</label>
                <div class="input-wrapper">
                  <input
                    id="reset-new-password"
                    v-model="resetNewPassword"
                    :type="showResetPassword ? 'text' : 'password'"
                    class="text-input with-toggle"
                    placeholder="Min. 8 characters"
                    minlength="8"
                    required
                  />
                  <button
                    type="button"
                    class="password-toggle"
                    @click="showResetPassword = !showResetPassword"
                    aria-label="Toggle password visibility"
                  >
                    <svg v-if="!showResetPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                  </button>
                </div>
              </div>

              <div class="input-block">
                <label class="input-label" for="reset-confirm-password">Confirm New Password</label>
                <div class="input-wrapper">
                  <input
                    id="reset-confirm-password"
                    v-model="resetConfirmPassword"
                    :type="showResetPassword ? 'text' : 'password'"
                    class="text-input"
                    placeholder="Repeat password"
                    minlength="8"
                    required
                  />
                </div>
              </div>

              <button
                type="submit"
                class="submit-signin-btn"
                :disabled="forgotLoading || resetOtp.length < 6 || resetNewPassword.length < 8"
              >
                <BrandSpinner v-if="forgotLoading" :size="18" color="#ffffff" />
                <span v-else class="btn-content">
                  Update Password
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
              </button>
            </form>

            <!-- STEP 3: SUCCESS STATE -->
            <div v-else-if="forgotStep === 'success'" class="forgot-success-box">
              <div class="success-icon-bubble">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <h3 class="success-title">Password Reset Complete!</h3>
              <p class="success-desc">Your password has been updated securely. You can now log in to Checkpass with your new credentials.</p>
              <button
                type="button"
                class="submit-signin-btn"
                @click="closeForgotPassword"
              >
                Sign In Now &rarr;
              </button>
            </div>

          </div>

          <!-- Trust Footer -->
          <div class="form-trust-footer">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>Bank-grade 256-bit encryption &middot; NDPR &amp; ISO 27001 Compliant</span>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { authApi } from '@/api'

type RoleType = 'admin' | 'guard' | 'resident'

const router = useRouter()
const auth   = useAuthStore()

// ─── 3 NARRATIVE MOMENTS (CONSISTENT WITH CREATE ACCOUNT SLIDER) ───
interface Moment {
  id: number
  headline: string
  subhead: string
  image: string
}

const moments: Moment[] = [
  {
    id: 1,
    headline: 'Build Trust.',
    subhead: 'Zero guesswork at the gate. Every visitor is pre-cleared by residents and instantly verified by security before arrival.',
    image: '/register-hero-trust.jpg'
  },
  {
    id: 2,
    headline: 'Protect Value.',
    subhead: 'Eliminate paper logbooks and perimeter blindspots with a tamper-proof digital audit trail that safeguards your property.',
    image: '/register-hero-value.jpg'
  },
  {
    id: 3,
    headline: 'Premium Experience.',
    subhead: 'Frictionless QR access and white-glove gate clearance that give residents, guests, and buyers an elevated first impression.',
    image: '/register-hero-experience.jpg'
  }
]

const activeMomentIndex = ref(0)
const activeMoment = computed(() => moments[activeMomentIndex.value])
let rotationTimer: ReturnType<typeof setInterval> | null = null
const isPaused = ref(false)

function nextMoment() {
  activeMomentIndex.value = (activeMomentIndex.value + 1) % moments.length
}

function startRotation() {
  stopRotation()
  rotationTimer = setInterval(() => {
    if (!isPaused.value) {
      nextMoment()
    }
  }, 6000)
}

function stopRotation() {
  if (rotationTimer) {
    clearInterval(rotationTimer)
    rotationTimer = null
  }
}

function pauseRotation() {
  isPaused.value = true
}

function resumeRotation() {
  isPaused.value = false
}

onMounted(() => {
  startRotation()
})

onUnmounted(() => {
  stopRotation()
})

// ─── ROLE & LOGIN FORM STATE ───
const activeRole = ref<RoleType>('admin')
const identifier = ref('')
const password = ref('')
const showPassword = ref(false)
const loading  = ref(false)
const error    = ref('')
const residentLoggedIn = ref(false)

// ─── FORGOT PASSWORD STATE (FOR ALL 3 ROLES) ───
const showForgotView = ref(false)
const forgotStep = ref<'request' | 'verify' | 'success'>('request')
const forgotIdentifier = ref('')
const resetOtp = ref('')
const resetNewPassword = ref('')
const resetConfirmPassword = ref('')
const showResetPassword = ref(false)
const forgotLoading = ref(false)
const forgotError = ref('')

function openForgotPassword() {
  showForgotView.value = true
  forgotStep.value = 'request'
  forgotError.value = ''
  forgotIdentifier.value = identifier.value.trim()
}

function closeForgotPassword() {
  showForgotView.value = false
  forgotStep.value = 'request'
  forgotError.value = ''
  resetOtp.value = ''
  resetNewPassword.value = ''
  resetConfirmPassword.value = ''
}

const forgotGuideText = computed(() => {
  if (activeRole.value === 'admin') {
    return 'Enter your official administrator email address. We will dispatch a 6-digit verification code to reset your password.'
  }
  if (activeRole.value === 'guard') {
    return 'Enter your registered guard phone number or email to receive a 6-digit verification code.'
  }
  return 'Enter your registered phone number or email address to receive your 6-digit verification code.'
})

const forgotIdentifierLabel = computed(() => {
  if (activeRole.value === 'admin') return 'Email Address'
  return 'Email Address / Phone No.'
})

const forgotPlaceholder = computed(() => {
  if (activeRole.value === 'admin') return 'admin@estate.com'
  if (activeRole.value === 'guard') return '08012345678 or security@estate.com'
  return '08012345678 or resident@estate.com'
})

async function handleSendResetOtp() {
  forgotError.value = ''
  forgotLoading.value = true
  try {
    const res = await authApi.forgotPassword(forgotIdentifier.value.trim())
    // If backend returns debugOtp (in local dev), autofill for testing convenience
    if (res.data && res.data.debugOtp) {
      resetOtp.value = String(res.data.debugOtp)
    }
    forgotStep.value = 'verify'
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    forgotError.value = err.response?.data?.message || 'Could not dispatch verification code. Please check your details.'
  } finally {
    forgotLoading.value = false
  }
}

async function handlePerformPasswordReset() {
  forgotError.value = ''
  if (resetNewPassword.value !== resetConfirmPassword.value) {
    forgotError.value = 'Passwords do not match. Please verify and try again.'
    return
  }
  if (resetNewPassword.value.length < 8) {
    forgotError.value = 'Password must be at least 8 characters long.'
    return
  }

  forgotLoading.value = true
  try {
    await authApi.resetPassword(forgotIdentifier.value.trim(), resetOtp.value.trim(), resetNewPassword.value)
    forgotStep.value = 'success'
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    forgotError.value = err.response?.data?.message || 'The verification code is invalid or has expired. Please try again.'
  } finally {
    forgotLoading.value = false
  }
}

const residentAppUrl = computed(() => {
  if (typeof window !== 'undefined' && window.location.hostname === 'localhost') {
    return 'http://localhost:5173/login'
  }
  return 'https://app.checkpass.ng/login'
})

function setRole(role: RoleType) {
  if (activeRole.value === role) return
  activeRole.value = role
  error.value = ''
  residentLoggedIn.value = false
  forgotError.value = ''
  // Pre-clear if switching to admin with non-email
  if (role === 'admin' && identifier.value.includes('@') === false && identifier.value.length > 0) {
    identifier.value = ''
  }
}

const inputLabel = computed(() => {
  if (activeRole.value === 'admin') return 'Email Address'
  return 'Email Address / Phone No.'
})

const inputPlaceholder = computed(() => {
  if (activeRole.value === 'admin') return 'admin@estate.com'
  return 'e.g. 08012345678 or name@domain.com'
})

const canSubmit = computed(() => {
  return identifier.value.trim().length >= 4 && password.value.length >= 1
})

async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    if (activeRole.value === 'admin') {
      await auth.loginAdmin(identifier.value.trim(), password.value)
      router.push('/dashboard')
    } else if (activeRole.value === 'guard') {
      await auth.loginUser(identifier.value.trim(), password.value, 'security')
      router.push('/access')
    } else {
      await auth.loginUser(identifier.value.trim(), password.value, 'resident')
      residentLoggedIn.value = true
    }
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Incorrect login credentials. Please verify your details and password.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ── Full-Bleed Page Container ── */
.auth-page {
  min-height: 100vh;
  width: 100vw;
  display: flex;
  background: #FFFFFF;
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  overflow-x: hidden;
}

.auth-container {
  display: flex;
  width: 100vw;
  min-height: 100vh;
  background: #FFFFFF;
  overflow: hidden;
}

/* ── 58% LEFT HERO PANEL: IDENTICAL TO CREATE ACCOUNT SLIDER ── */
.hero-panel {
  flex: 1 1 58%;
  background-color: #022315;
  background-image: linear-gradient(155deg, #0A5C38 0%, #063C24 55%, #022315 100%);
  padding: clamp(36px, 5vh, 60px) clamp(36px, 4vw, 64px);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  position: relative;
  overflow: hidden;
  color: #FFFFFF;
  min-height: 100vh;
}

.hero-pattern-overlay {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
  background-size: 24px 24px;
  pointer-events: none;
  opacity: 0.6;
  z-index: 2;
}

.hero-brand-header {
  position: relative;
  z-index: 10;
  margin-bottom: auto;
}

.brand-logo-link {
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.brand-logo-link:hover {
  opacity: 0.88;
  transform: translateY(-1px);
}

.brand-logo-img {
  height: 34px;
  width: auto;
  display: block;
  filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.5));
}

.hero-image-stage {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
}

.hero-slide-layer {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  transform: scale(1.04);
  transition: opacity 0.85s cubic-bezier(0.16, 1, 0.3, 1), transform 1.2s cubic-bezier(0.16, 1, 0.3, 1);
  pointer-events: none;
}

.hero-slide-layer.is-active {
  opacity: 1;
  transform: scale(1);
  z-index: 1;
}

.hero-stage-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center 25%;
  display: block;
}

.hero-stage-gradient {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    180deg,
    rgba(2, 35, 21, 0.65) 0%,
    rgba(2, 35, 21, 0.12) 18%,
    rgba(2, 35, 21, 0) 32%,
    rgba(2, 35, 21, 0.45) 50%,
    rgba(2, 35, 21, 0.88) 72%,
    #022315 88%,
    #022315 100%
  );
  pointer-events: none;
  z-index: 2;
}

.frame-glass-reflection {
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
  pointer-events: none;
  z-index: 2;
}

.hero-text-block {
  position: relative;
  z-index: 3;
  margin-top: auto;
  padding-top: 24px;
}

.hero-headline {
  font-family: var(--font-display, 'Bricolage Grotesque', sans-serif);
  font-size: clamp(38px, 4.4vw, 54px);
  font-weight: 800;
  line-height: 1.08;
  margin-bottom: 14px;
  color: #FFFFFF;
  letter-spacing: -0.035em;
  text-shadow: 0 3px 20px rgba(0, 0, 0, 0.7);
}

.hero-copy {
  font-size: clamp(16px, 1.2vw, 18.5px);
  line-height: 1.55;
  color: rgba(255, 255, 255, 0.92);
  margin-bottom: 28px;
  max-width: 540px;
  text-shadow: 0 1px 8px rgba(0, 0, 0, 0.6);
}

.hero-footer-trust {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.75);
  background: rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(8px);
  padding: 6px 14px;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.12);
}

.hero-footer-trust .shield-icon {
  color: #34D399;
}

/* Cross-fade moment animation */
.moment-fade-enter-active,
.moment-fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}

.moment-fade-enter-from {
  opacity: 0;
  transform: translateY(6px);
}

.moment-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* ── 42% RIGHT FORM PANEL ── */
.form-interaction-section {
  flex: 0 0 42%;
  min-height: 100vh;
  background: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: clamp(24px, 3.5vw, 48px);
  box-sizing: border-box;
}

.form-inner-container {
  width: 100%;
  max-width: 440px;
}

/* ── CLEAN 3-ROLE TABS: ICON BY THE SIDE OF TEXT ── */
.role-tabs-wrap {
  margin-bottom: 24px;
}

.role-tabs-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 6px;
  background: #F1F5F9;
  padding: 5px;
  border-radius: 12px;
  border: 1px solid #E2E8F0;
}

.role-tab {
  background: transparent;
  border: 1px solid transparent;
  border-radius: 8px;
  padding: 9px 10px;
  cursor: pointer;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: center;
  gap: 7px;
  color: #64748B;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  white-space: nowrap;
}

.role-tab:hover:not(.active) {
  background: rgba(255, 255, 255, 0.6);
  color: #1E293B;
}

.role-tab.active {
  background: #FFFFFF;
  color: #047857;
  border-color: rgba(5, 150, 105, 0.18);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
}

.tab-icon {
  flex-shrink: 0;
  color: #94A3B8;
  transition: color 0.2s ease;
}

.role-tab.active .tab-icon {
  color: #059669;
}

.role-name {
  font-size: 13px;
  font-weight: 700;
  line-height: 1;
}

/* ── HEADER GROUP: JUST "Sign In" ── */
.form-header-group {
  margin-bottom: 16px;
}

.form-title {
  font-size: clamp(26px, 2.3vw, 32px);
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  letter-spacing: -0.025em;
  font-family: -apple-system, BlinkMacSystemFont, "Plus Jakarta Sans", sans-serif;
}

/* ── ALERTS & BANNERS ── */
.auth-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #FEF2F2;
  border: 1px solid #FCA5A5;
  color: #B91C1C;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 13.5px;
  margin-bottom: 18px;
}

.auth-info-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #EFF6FF;
  border: 1px solid #BFDBFE;
  color: #1E40AF;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 13.5px;
}

.resident-success-card {
  background: #ECFDF5;
  border: 1.5px solid #10B981;
  border-radius: 14px;
  padding: 16px;
  margin-bottom: 20px;
  text-align: center;
}

.resident-success-card .success-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: #065F46;
  font-size: 15px;
  margin-bottom: 6px;
}

.resident-success-card p {
  font-size: 13px;
  color: #047857;
  margin: 0 0 14px;
}

.btn-launch-resident {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #059669;
  color: #FFFFFF;
  padding: 10px 18px;
  border-radius: 10px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
  transition: all 0.2s ease;
}

.btn-launch-resident:hover {
  background: #047857;
  transform: translateY(-1px);
}

/* ── FORM INPUTS ── */
.signin-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.input-block {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.input-label {
  font-size: 13px;
  font-weight: 700;
  color: #1E293B;
}

/* Forgot password link button */
.forgot-link-btn {
  background: transparent;
  border: none;
  color: #059669;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  transition: color 0.15s ease, text-decoration 0.15s ease;
}

.forgot-link-btn:hover {
  color: #047857;
  text-decoration: underline;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: #F8FAFC;
  border: 1.5px solid #E2E8F0;
  border-radius: 12px;
  transition: all 0.2s ease;
}

.input-wrapper:focus-within {
  border-color: #059669;
  background: #FFFFFF;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
}

.input-icon {
  position: absolute;
  left: 14px;
  display: flex;
  align-items: center;
  color: #94A3B8;
  pointer-events: none;
}

.text-input {
  width: 100%;
  height: 44px;
  padding: 0 14px 0 42px;
  border: none;
  background: transparent;
  font-size: 14.5px;
  color: #0F172A;
  outline: none;
}

.text-input::placeholder {
  color: #94A3B8;
}

.text-input.with-toggle {
  padding-right: 44px;
}

.otp-field {
  letter-spacing: 0.25em;
  font-size: 17px;
  font-weight: 700;
  padding-left: 14px;
}

.password-toggle {
  position: absolute;
  right: 12px;
  background: transparent;
  border: none;
  color: #94A3B8;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s ease;
}

.password-toggle:hover {
  color: #475569;
}

.submit-signin-btn {
  margin-top: 8px;
  width: 100%;
  height: 48px;
  border-radius: 12px;
  background: #059669;
  color: #FFFFFF;
  border: none;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s ease;
  box-shadow: 0 4px 14px rgba(5, 150, 105, 0.25);
}

.submit-signin-btn:hover:not(:disabled) {
  background: #047857;
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(5, 150, 105, 0.35);
}

.submit-signin-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  box-shadow: none;
}

.btn-content {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

/* ── FORGOT PASSWORD RECOVERY CONTAINER ── */
.forgot-flow-container {
  display: flex;
  flex-direction: column;
}

.back-link-btn {
  background: transparent;
  border: none;
  color: #64748B;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  margin-bottom: 16px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: color 0.15s ease;
  align-self: flex-start;
}

.back-link-btn:hover {
  color: #0F172A;
}

.forgot-guide-text {
  font-size: 13.5px;
  color: #64748B;
  line-height: 1.5;
  margin: 6px 0 0;
}

.forgot-success-box {
  text-align: center;
  padding: 12px 0;
}

.success-icon-bubble {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: #ECFDF5;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
}

.success-title {
  font-size: 20px;
  font-weight: 800;
  color: #064E3B;
  margin: 0 0 8px;
}

.success-desc {
  font-size: 14px;
  color: #64748B;
  line-height: 1.5;
  margin: 0 0 20px;
}

/* ── FOOTER LINKS ── */
.form-bottom-links {
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #F1F5F9;
  text-align: center;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 13.5px;
}

.new-estate-text {
  color: #64748B;
  font-size: 13px;
}

.register-link {
  color: #059669;
  font-weight: 700;
  text-decoration: none;
}

.register-link:hover {
  text-decoration: underline;
}

.form-trust-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 24px;
  font-size: 12px;
  color: #64748B;
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .hero-panel { display: none; }
  .form-interaction-section { flex: 1 1 100%; min-height: 100vh; padding: 24px 16px; }
}
</style>
