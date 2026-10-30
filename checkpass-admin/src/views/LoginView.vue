<template>
  <div class="auth-page">
    <div class="auth-container">

      <!-- Left Hero Visual (58% Screen Real Estate) -->
      <div class="hero-slider-section">
        <div class="hero-backdrop-image" />
        <div class="hero-top-vignette" />
        <div class="hero-bottom-gradient" />

        <!-- Top Checkpass Brand Logo -->
        <div class="hero-brand-header">
          <img src="/checkpass-logo.svg" alt="Checkpass" class="brand-logo-white" />
        </div>

        <!-- Bottom Narrative Overlay -->
        <div class="hero-content-overlay">
          <div class="hero-tag">Estate Command &middot; Gate Audit &middot; Resident Dues</div>
          <h2 class="hero-headline">Build Trust.</h2>
          <p class="hero-subhead">
            Zero guesswork at the gate. Every visitor is pre-cleared by residents and instantly verified by security before arrival.
          </p>
          <div class="hero-footer-trust">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Powered by Sheffy Technologies &middot; Enterprise Security</span>
          </div>
        </div>
      </div>

      <!-- Right Form Section (42% Screen Real Estate) -->
      <div class="form-interaction-section">
        <div class="form-inner-container">

          <!-- Header Group -->
          <div class="form-header-group">
            <h1 class="form-title">Sign in to Checkpass</h1>
            <p class="form-subtitle">Enter your official work credentials to manage your estate, monitor gate clearance, and track finances.</p>
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

          <!-- Sign-In Form -->
          <form @submit.prevent="handleLogin" class="signin-form">

            <!-- Work Email -->
            <div class="input-block">
              <label class="input-label" for="login-email">Official work email</label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </span>
                <input
                  id="login-email"
                  v-model="email"
                  type="email"
                  class="text-input"
                  placeholder="admin@estate.com"
                  autocomplete="username"
                  required
                />
              </div>
            </div>

            <!-- Password -->
            <div class="input-block">
              <div class="label-row">
                <label class="input-label" for="login-password">Password</label>
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

            <!-- Submit Button -->
            <button
              type="submit"
              class="submit-signin-btn"
              :disabled="loading || !canSubmit"
            >
              <span v-if="loading" class="spinner"></span>
              <span v-else class="btn-content">
                Sign In to Admin Portal
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </span>
            </button>

          </form>

          <!-- Footer Switch to Registration -->
          <div class="form-bottom-links">
            <span class="new-estate-text">Setting up a new estate?</span>
            <router-link to="/register" class="register-link">Create a free estate account &rarr;</router-link>
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
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth   = useAuthStore()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading  = ref(false)
const error    = ref('')

const canSubmit = computed(() => {
  return email.value.trim().length >= 4 && password.value.length >= 1
})

async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    await auth.loginAdmin(email.value, password.value)
    router.push('/dashboard')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Invalid work credentials. Please verify your email and password.'
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

/* ── 58% Left Hero Visual ── */
.hero-slider-section {
  width: 58%;
  min-height: 100vh;
  position: relative;
  background: #030605;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

.hero-backdrop-image {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  background-image: url('/register-hero-trust.jpg');
  background-size: cover;
  background-position: center 25%;
  background-repeat: no-repeat;
  filter: brightness(0.92);
}

.hero-top-vignette {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 140px;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.72) 0%, rgba(0, 0, 0, 0.2) 60%, transparent 100%);
  pointer-events: none;
  z-index: 2;
}

.hero-bottom-gradient {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 60%;
  background: linear-gradient(180deg, transparent 0%, rgba(3, 10, 6, 0.45) 45%, rgba(2, 8, 5, 0.94) 90%, #020704 100%);
  pointer-events: none;
  z-index: 2;
}

.hero-brand-header {
  position: relative;
  z-index: 5;
  padding: clamp(24px, 3.2vw, 40px);
}

.brand-logo-white {
  height: clamp(34px, 3.2vw, 44px);
  width: auto;
  display: block;
  filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.5));
}

.hero-content-overlay {
  position: relative;
  z-index: 5;
  padding: clamp(28px, 4vw, 56px);
  max-width: 620px;
}

.hero-tag {
  display: inline-block;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #34D399;
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(52, 211, 153, 0.35);
  padding: 4px 12px;
  border-radius: 999px;
  margin-bottom: 14px;
}

.hero-headline {
  font-size: clamp(38px, 4.4vw, 54px);
  font-weight: 800;
  color: #FFFFFF;
  line-height: 1.08;
  letter-spacing: -0.035em;
  margin: 0 0 12px;
  font-family: -apple-system, BlinkMacSystemFont, "Plus Jakarta Sans", "SF Pro Display", sans-serif;
  text-shadow: 0 3px 18px rgba(0, 0, 0, 0.7);
}

.hero-subhead {
  font-size: clamp(16px, 1.2vw, 18.5px);
  line-height: 1.48;
  color: rgba(255, 255, 255, 0.92);
  margin: 0 0 24px;
  font-weight: 450;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
}

.hero-footer-trust {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  color: rgba(255, 255, 255, 0.75);
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.14);
}

/* ── 42% Right Form Section ── */
.form-interaction-section {
  width: 42%;
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

.form-header-group {
  margin-bottom: 24px;
}

.form-title {
  font-size: clamp(26px, 2.2vw, 32px);
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 8px;
  letter-spacing: -0.025em;
  font-family: -apple-system, BlinkMacSystemFont, "Plus Jakarta Sans", sans-serif;
}

.form-subtitle {
  font-size: 14px;
  line-height: 1.5;
  color: #64748B;
  margin: 0;
}

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
  margin-top: 10px;
  width: 100%;
  height: 50px;
  border-radius: 12px;
  background: #059669;
  color: #FFFFFF;
  border: none;
  font-size: 15.5px;
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
  .hero-slider-section { display: none; }
  .form-interaction-section { width: 100%; min-height: 100vh; padding: 24px 16px; }
}
</style>
