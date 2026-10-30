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
          <div class="step-indicator-tag">Step 2 of 2 &middot; Estate Onboarding</div>
          <h2 class="hero-headline">Protect Value.</h2>
          <p class="hero-subhead">
            Eliminate paper logbooks and perimeter blindspots with a tamper-proof digital audit trail that safeguards your property.
          </p>
          <div class="hero-footer-trust">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Powered by Sheffy Technologies &middot; Enterprise Estate Security</span>
          </div>
        </div>
      </div>

      <!-- Right Form Section (42% Screen Real Estate) -->
      <div class="form-interaction-section">
        <div class="form-inner-container">

          <!-- ACTIVE SETUP FORM -->
          <template v-if="!activated">
            <!-- Header Group -->
            <div class="form-header-group">
              <div class="step-progress-badge">
                <span class="step-counter">STEP 2 OF 2</span>
                <span class="step-name">ESTATE PROFILE</span>
              </div>
              <h1 class="form-title">Tell us about your estate</h1>
              <p class="form-subtitle">We'll set you up with your free tier &mdash; up to 3 units &amp; 3 residents each, forever free.</p>
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

            <!-- Form -->
            <form @submit.prevent="handleCreateEstate" class="estate-form">
              
              <!-- Estate Name -->
              <div class="input-block">
                <label class="input-label" for="estate-name">Estate name <span class="req">*</span></label>
                <div class="input-wrapper">
                  <span class="input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                  </span>
                  <input
                    id="estate-name"
                    v-model="name"
                    type="text"
                    class="text-input"
                    placeholder="e.g. Victoria Crest Estate, Ikoyi"
                    required
                  />
                </div>
              </div>

              <!-- Street Address -->
              <div class="input-block">
                <label class="input-label" for="estate-address">Street address</label>
                <div class="input-wrapper">
                  <span class="input-icon">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                  </span>
                  <input
                    id="estate-address"
                    v-model="address"
                    type="text"
                    class="text-input"
                    placeholder="Plot 14, Admirals Way"
                  />
                </div>
              </div>

              <!-- City & State Row -->
              <div class="location-grid">
                <div class="input-block">
                  <label class="input-label" for="estate-city">City</label>
                  <div class="input-wrapper">
                    <span class="input-icon">
                      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    </span>
                    <input
                      id="estate-city"
                      v-model="city"
                      type="text"
                      class="text-input"
                      placeholder="e.g. Lekki"
                    />
                  </div>
                </div>

                <div class="input-block">
                  <label class="input-label">State</label>
                  <StateSelect v-model="state" placeholder="Select Nigerian State" />
                </div>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                class="submit-account-btn"
                :disabled="loading || !name.trim()"
              >
                <span v-if="loading" class="spinner"></span>
                <span v-else class="btn-content">
                  Activate Free Tier &amp; Enter Dashboard
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
              </button>

            </form>

            <!-- NDPR Trust Footer -->
            <div class="form-trust-footer">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <span>Bank-grade 256-bit encryption &middot; NDPR &amp; ISO 27001 Compliant</span>
            </div>
          </template>

          <!-- ACTIVATED SUCCESS PANEL -->
          <template v-else>
            <div class="success-panel">
              <div class="success-icon-badge">&check;</div>
              <h1 class="success-title">Your Free Trial Is Live!</h1>
              <p class="success-desc">
                <strong>{{ estateName }}</strong> is ready. Your community has access to up to
                <strong>3 units</strong> and <strong>3 residents per unit</strong>, forever free.
              </p>
              <button class="submit-account-btn" @click="goToDashboard">
                <span class="btn-content">
                  Open Admin Dashboard
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
              </button>
            </div>
          </template>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { onboardingApi } from '@/api'
import StateSelect from '@/components/StateSelect.vue'

const router = useRouter()

const name = ref('')
const address = ref('')
const city = ref('')
const state = ref('')
const loading = ref(false)
const error = ref('')
const activated = ref(false)
const estateName = ref('')

async function handleCreateEstate() {
  error.value = ''
  loading.value = true
  try {
    const res = await onboardingApi.createEstate({
      name: name.value,
      address: address.value || undefined,
      city: city.value || undefined,
      state: state.value || undefined,
    })
    estateName.value = res.data?.estate?.name ?? name.value
    activated.value = true
    setTimeout(() => goToDashboard(), 2400)
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    error.value = err.response?.data?.message ?? 'Could not create your estate. Please try again.'
  } finally {
    loading.value = false
  }
}

function goToDashboard() {
  router.push('/dashboard')
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
  background-image: url('/register-hero-value.jpg');
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

.step-indicator-tag {
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

/* ── 42% Right Form Interaction Section ── */
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

.step-progress-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  padding: 4px 10px;
  border-radius: 8px;
  margin-bottom: 12px;
}

.step-counter {
  font-size: 11px;
  font-weight: 800;
  color: #065F46;
  letter-spacing: 0.05em;
}

.step-name {
  font-size: 10.5px;
  font-weight: 700;
  color: #047857;
  border-left: 1px solid #6EE7B7;
  padding-left: 8px;
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
  margin: 0 0 24px;
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

.estate-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.input-block {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.input-label {
  font-size: 13px;
  font-weight: 700;
  color: #1E293B;
}

.req {
  color: #059669;
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

.location-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.submit-account-btn {
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

.submit-account-btn:hover:not(:disabled) {
  background: #047857;
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(5, 150, 105, 0.35);
}

.submit-account-btn:disabled {
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

.form-trust-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 24px;
  font-size: 12px;
  color: #64748B;
}

/* ── Success Activated Panel ── */
.success-panel {
  text-align: center;
  padding: 24px 0;
}

.success-icon-badge {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  margin: 0 auto 18px;
  background: #ECFDF5;
  color: #059669;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 32px;
  font-weight: 800;
  box-shadow: 0 0 0 8px rgba(16, 185, 129, 0.12);
  animation: pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.success-title {
  font-size: 26px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 10px;
}

.success-desc {
  font-size: 14.5px;
  line-height: 1.6;
  color: #475569;
  margin: 0 0 28px;
}

@keyframes pop {
  from { transform: scale(0.5); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .hero-slider-section { display: none; }
  .form-interaction-section { width: 100%; min-height: 100vh; padding: 24px 16px; }
}
</style>
