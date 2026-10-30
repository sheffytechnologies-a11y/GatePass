<template>
  <div class="auth-page">

    <!-- ─── FULL-SCREEN SPLIT CONTAINER (NO OUTER VOID) ─── -->
    <div class="auth-container">

      <!-- ─── LEFT HERO PANEL: SLIDER CAPTURING MORE SPACE (58%-60%) ─── -->
      <div class="hero-panel" @mouseenter="pauseRotation" @mouseleave="resumeRotation">
        <div class="hero-pattern-overlay"></div>

        <!-- Top-Left Brand Logo (Positioned naturally in hero panel) -->
        <header class="hero-brand-header">
          <router-link to="/" class="brand-logo-link" aria-label="Checkpass Home">
            <img src="/logo-dark.png" alt="Checkpass" class="brand-logo-img" />
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

            <!-- Gradient: 0 Opacity at top, fading to background color at bottom for text to show -->
            <div class="hero-stage-gradient"></div>
            <div class="frame-glass-reflection"></div>
          </div>

          <!-- Bottom Text Content (Over Gradient - Pure, Uncluttered Headline + Subtle Subhead) -->
          <div class="hero-text-block">
            <!-- Moment Headline -->
            <transition name="moment-fade" mode="out-in">
              <h2 :key="'h-' + activeMoment.id" class="hero-headline">
                {{ activeMoment.headline }}
              </h2>
            </transition>

            <!-- Moment Subtle Subheader -->
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

        <!-- ─── RIGHT CARD: PRECISION REGISTRATION FORM ─── -->
        <div class="form-panel">
          <div class="form-panel-inner">

            <!-- Header -->
            <div class="form-header">
              <h1 class="form-title">Create Estate Account</h1>
              <p class="form-subtitle">One portal to onboard residents, clear visitors at the gate, and keep every entry on record.</p>
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

          <!-- Form Fields -->
          <form @submit.prevent="handleRegister" class="registration-form">
            
            <!-- Full Name -->
            <div class="input-block">
              <label class="input-label" for="admin-name">Full name <span class="req">*</span></label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <input
                  id="admin-name"
                  v-model="name"
                  type="text"
                  class="text-input"
                  placeholder="e.g. Engr. Tunde Adeleke"
                  autocomplete="name"
                  required
                />
              </div>
            </div>

            <!-- Email Address -->
            <div class="input-block">
              <label class="input-label" for="admin-email">Official work email <span class="req">*</span></label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </span>
                <input
                  id="admin-email"
                  v-model="email"
                  type="email"
                  class="text-input"
                  placeholder="manager@estatehoa.org"
                  autocomplete="email"
                  required
                />
              </div>
            </div>

            <!-- Phone Number with Country Code Dropdown -->
            <div class="input-block">
              <label class="input-label" for="admin-phone">Mobile phone number <span class="req">*</span></label>
              <div class="input-wrapper phone-wrapper">
                <div class="country-select-wrapper">
                  <select
                    id="country-code"
                    v-model="countryCode"
                    class="country-select"
                    aria-label="Country dial code"
                  >
                    <option v-for="c in countryCodes" :key="c.code" :value="c.code">
                      {{ c.code }} ({{ c.country }})
                    </option>
                  </select>
                  <svg class="select-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6"/>
                  </svg>
                </div>
                <input
                  id="admin-phone"
                  v-model="phone"
                  type="tel"
                  class="text-input phone-input"
                  placeholder="802 403 5326"
                  autocomplete="tel"
                  required
                />
              </div>
            </div>

            <!-- Passwords Row -->
            <div class="password-grid">
              
              <!-- Password -->
              <div class="input-block">
                <label class="input-label" for="admin-pwd">Password <span class="req">*</span></label>
                <div class="input-wrapper">
                  <span class="input-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  </span>
                  <input
                    id="admin-pwd"
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    class="text-input with-toggle"
                    placeholder="Min. 6 chars"
                    autocomplete="new-password"
                    minlength="6"
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

              <!-- Confirm Password -->
              <div class="input-block">
                <label class="input-label" for="admin-confirm-pwd">Confirm <span class="req">*</span></label>
                <div class="input-wrapper">
                  <span class="input-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 12 2 2 4-4"/><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  </span>
                  <input
                    id="admin-confirm-pwd"
                    v-model="confirmPassword"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    class="text-input with-toggle"
                    placeholder="Repeat password"
                    autocomplete="new-password"
                    minlength="6"
                    required
                  />
                  <button
                    type="button"
                    class="password-toggle"
                    @click="showConfirmPassword = !showConfirmPassword"
                    aria-label="Toggle confirm password visibility"
                  >
                    <svg v-if="!showConfirmPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                  </button>
                </div>
              </div>

            </div>

            <!-- Terms & Consent Checkbox -->
            <div class="terms-row">
              <label class="terms-label">
                <input v-model="agreeTerms" type="checkbox" class="terms-checkbox" required />
                <span>I agree to the <a href="#" @click.prevent>Terms of Service</a> &amp; <a href="#" @click.prevent>Privacy Policy</a></span>
              </label>
            </div>

            <!-- Submit Button (Primary Emerald) -->
            <button
              type="submit"
              class="submit-account-btn"
              :disabled="loading || !canSubmit"
            >
              <span v-if="loading" class="spinner"></span>
              <span v-else class="btn-content">
                Create Estate Account
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </span>
            </button>

          </form>

          <!-- Footer Switch to Login -->
          <div class="form-bottom-links">
            <span class="already-text">Already registered your community?</span>
            <router-link to="/login" class="signin-link">Sign in here &rarr;</router-link>
          </div>

          <!-- NDPR Trust Footer -->
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

const router = useRouter()
const auth   = useAuthStore()

// ─── 3 NARRATIVE MOMENTS DATA (BUILD TRUST / PROTECT VALUE / PREMIUM EXPERIENCE) ───
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

const countryCode = ref('+234')
const countryCodes = [
  { code: '+234', country: 'Nigeria' },
  { code: '+233', country: 'Ghana' },
  { code: '+254', country: 'Kenya' },
  { code: '+27',  country: 'South Africa' },
  { code: '+250', country: 'Rwanda' },
  { code: '+237', country: 'Cameroon' },
  { code: '+225', country: 'Ivory Coast' },
  { code: '+44',  country: 'UK' },
  { code: '+1',   country: 'US / Canada' },
  { code: '+971', country: 'UAE' },
  { code: '+33',  country: 'France' },
  { code: '+49',  country: 'Germany' },
  { code: '+91',  country: 'India' },
  { code: '+61',  country: 'Australia' },
  { code: '+86',  country: 'China' }
]

const name = ref('')
const email = ref('')
const phone = ref('')
const password = ref('')
const confirmPassword = ref('')
const agreeTerms = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)
const error = ref('')

const canSubmit = computed(() => {
  return (
    name.value.trim().length >= 2 &&
    email.value.trim().length >= 4 &&
    phone.value.trim().length >= 7 &&
    password.value.length >= 6 &&
    confirmPassword.value.length >= 6 &&
    agreeTerms.value
  )
})

async function handleRegister() {
  error.value = ''
  if (password.value !== confirmPassword.value) {
    error.value = 'Passwords do not match. Please verify and try again.'
    return
  }

  loading.value = true
  try {
    const rawDigits = phone.value.replace(/\D/g, '')
    const codeDigits = countryCode.value.replace(/\D/g, '')
    const trimmedNumber = rawDigits.replace(/^0+/, '')
    const cleanPhone = `+${codeDigits}${trimmedNumber}`
    
    await auth.registerAdmin(name.value, email.value, password.value, cleanPhone)
    router.push('/dashboard')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
    const firstFieldError = err.response?.data?.errors
      ? Object.values(err.response.data.errors)[0]?.[0]
      : undefined
    error.value = firstFieldError ?? err.response?.data?.message ?? 'Could not create estate account. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* ── Full-Bleed Page Container: Seamless Edge-to-Edge ── */
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

/* ── Split Screen Container ── */
.auth-container {
  display: flex;
  width: 100vw;
  min-height: 100vh;
  background: #FFFFFF;
  overflow: hidden;
}

/* ── Top-Left Brand Logo inside Hero Panel ── */
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
  height: 32px;
  width: auto;
  filter: brightness(0) invert(1);
  display: block;
}

/* ── LEFT HERO PANEL: Captures More Space (58%-60%) with Full-Bleed Imagery ── */
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

/* 3D Illustration Filling the Container */
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

/* Gradient: Soft top vignette for logo + solid bottom for text readability */
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

/* Bottom Text Content Sitting Over Gradient */
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
  min-height: 56px;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
}

/* Moment Transitions */
.moment-fade-enter-active,
.moment-fade-leave-active {
  transition: opacity 0.35s ease, transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.moment-fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

.moment-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

.hero-footer-trust {
  position: relative;
  z-index: 3;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11.5px;
  color: rgba(255, 255, 255, 0.65);
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.14);
}

.shield-icon {
  color: #00C97A;
  flex-shrink: 0;
}

/* ── RIGHT PANEL: PRECISION FORM (42%) ── */
.form-panel {
  flex: 1 1 42%;
  background: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: clamp(32px, 5vh, 60px) clamp(28px, 4vw, 56px);
  min-height: 100vh;
  overflow-y: auto;
}

.form-panel-inner {
  width: 100%;
  max-width: 440px;
}

.form-header {
  margin-bottom: 22px;
}

.form-title {
  font-family: var(--font-display, 'Bricolage Grotesque', sans-serif);
  font-size: 26px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.02em;
  margin-bottom: 6px;
}

.form-subtitle {
  font-size: 13.5px;
  color: #64748B;
  line-height: 1.45;
}

/* Error Banner */
.auth-alert {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #FEF2F2;
  border: 1px solid #FCA5A5;
  color: #B91C1C;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 13px;
  margin-bottom: 18px;
}

/* Form Styles */
.registration-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.input-block {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.input-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #334155;
}

.req {
  color: #DC2626;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  background: #F8FAFC;
  border: 1.5px solid #E2E8F0;
  border-radius: 14px;
  transition: all 0.2s ease;
}

.input-wrapper:focus-within {
  border-color: #0A5C38;
  background: #FFFFFF;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.12);
}

.input-icon {
  position: absolute;
  left: 14px;
  color: #94A3B8;
  display: flex;
  align-items: center;
  pointer-events: none;
}

.text-input {
  width: 100%;
  height: 44px;
  border: none;
  background: transparent;
  padding: 0 14px 0 42px;
  font-size: 14px;
  color: #0F172A;
  outline: none;
}

.text-input::placeholder {
  color: #94A3B8;
  font-size: 13.5px;
}

/* Phone Field with Country Code Dropdown */
.phone-wrapper {
  padding-left: 0;
}

.country-select-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  height: 44px;
  background: #F1F5F9;
  border-right: 1.5px solid #E2E8F0;
  border-top-left-radius: 12px;
  border-bottom-left-radius: 12px;
  padding: 0 10px 0 14px;
  transition: background 0.15s ease;
}

.country-select-wrapper:hover {
  background: #E2E8F0;
}

.country-select {
  height: 100%;
  border: none;
  background: transparent;
  font-size: 13.5px;
  font-weight: 700;
  color: #1E293B;
  cursor: pointer;
  outline: none;
  padding-right: 16px;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
}

.country-select option {
  font-weight: 500;
  color: #0F172A;
  background: #FFFFFF;
}

.select-chevron {
  position: absolute;
  right: 8px;
  pointer-events: none;
  color: #64748B;
}

.phone-input {
  padding-left: 14px !important;
}

/* Passwords Grid */
.password-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.with-toggle {
  padding-right: 38px !important;
}

.password-toggle {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #94A3B8;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 4px;
  transition: color 0.15s;
}

.password-toggle:hover {
  color: #0A5C38;
}

/* Terms Row */
.terms-row {
  margin-top: 2px;
}

.terms-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  color: #475569;
  cursor: pointer;
}

.terms-checkbox {
  width: 16px;
  height: 16px;
  border-radius: 4px;
  accent-color: #0A5C38;
  cursor: pointer;
}

.terms-label a {
  color: #0A5C38;
  font-weight: 600;
  text-decoration: underline;
}

/* Submit Button */
.submit-account-btn {
  width: 100%;
  height: 48px;
  border-radius: 14px;
  border: none;
  background: #0A5C38;
  color: #FFFFFF;
  font-size: 15px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(10, 92, 56, 0.28);
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  margin-top: 6px;
}

.submit-account-btn:hover:not(:disabled) {
  background: #0E8A54;
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(10, 92, 56, 0.36);
}

.submit-account-btn:disabled {
  background: #86CBA8;
  opacity: 0.85;
  cursor: not-allowed;
  box-shadow: none;
}

.btn-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Bottom Links */
.form-bottom-links {
  text-align: center;
  margin-top: 18px;
  font-size: 13.5px;
}

.already-text {
  color: #64748B;
  margin-right: 6px;
}

.signin-link {
  color: #0A5C38;
  font-weight: 700;
  text-decoration: none;
  transition: color 0.15s;
}

.signin-link:hover {
  color: #00C97A;
  text-decoration: underline;
}

/* Trust Footer */
.form-trust-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #F1F5F9;
  font-size: 11px;
  color: #94A3B8;
}

/* Slide Transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(12px);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-12px);
}

/* ── Responsive Mobile & Tablet ── */
@media (max-width: 960px) {
  .auth-container {
    flex-direction: column;
    min-height: 100vh;
  }
  .hero-panel {
    flex: none;
    width: 100%;
    min-height: 420px;
    padding: 28px 24px 32px;
  }
  .hero-stage-img {
    object-position: center 25%;
  }
  .form-panel {
    flex: none;
    width: 100%;
    min-height: auto;
    padding: 36px 24px 48px;
  }
  .password-grid {
    grid-template-columns: 1fr;
  }
}
</style>
