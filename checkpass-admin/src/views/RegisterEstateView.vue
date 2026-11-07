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
          <img src="/checkpass-logo-white.svg" alt="Checkpass" class="brand-logo-white" />
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
            <span>Powered by Sheffy Technologies &middot; Enterprise Security</span>
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

              <!-- Authorization & Master Services Agreement Consent -->
              <div class="community-terms-block">
                <label class="community-terms-label">
                  <input
                    v-model="agreeCommunityTerms"
                    type="checkbox"
                    class="community-terms-checkbox"
                    required
                  />
                  <span>
                    I confirm I am an authorized representative of this property/community and agree to the
                    <a href="#" @click.prevent="openLegal('agreement')">CheckPass Master Services Agreement &amp; DPA</a> and
                    <a href="#" @click.prevent="openLegal('terms')">Terms of Use</a>.
                  </span>
                </label>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                class="submit-account-btn"
                :disabled="loading || !name.trim() || !agreeCommunityTerms"
              >
                <BrandSpinner v-if="loading" :size="18" color="#ffffff" />
                <span v-else class="btn-content">
                  Activate Free Tier &amp; Enter Dashboard
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </span>
              </button>

            </form>

            <!-- NDPR Trust Footer -->
            <div class="form-trust-footer">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <span>Bank-grade 256-bit encryption &middot; NDPA 2023 &amp; GAID 2025 Compliant</span>
            </div>
          
    <!-- CheckPass Master Services Agreement Modal -->
    <div v-if="legalModalOpen" class="estate-legal-modal-backdrop" @click.self="legalModalOpen = false">
      <div class="estate-legal-modal-card">
        <div class="estate-legal-header">
          <div class="estate-legal-tabs">
            <button type="button" class="estate-tab-btn" :class="{ active: legalTab === 'agreement' }" @click="legalTab = 'agreement'">Master Services Agreement (MSA &amp; DPA)</button>
            <button type="button" class="estate-tab-btn" :class="{ active: legalTab === 'terms' }" @click="legalTab = 'terms'">Platform Terms</button>
            <button type="button" class="estate-tab-btn" :class="{ active: legalTab === 'gaid' }" @click="legalTab = 'gaid'">NDPA &amp; GAID 2025</button>
          </div>
          <button type="button" class="estate-legal-close" @click="legalModalOpen = false">&times;</button>
        </div>

        <div class="estate-legal-body">
          <div v-if="legalTab === 'agreement'" class="legal-flow">
            <div class="dpa-badge">Regulated under NDP Act 2023 &amp; NDPC GAID 2025</div>
            <h3>CheckPass Master Services Agreement &amp; Data Processing Addendum (MSA &amp; DPA)</h3>
            <p class="legal-sub">Between Sheffy Technologies Limited (RC 1928471) &amp; The Subscribing Client / Property Management</p>

            <h4>1. Purpose &amp; Scope of Cloud Platform</h4>
            <p>This Master Services Agreement governs access to the CheckPass access management, gatehouse verification, and facility operations system. CheckPass provides digital pass clearance, multi-channel pass distribution (WhatsApp, SMS, Email), automated dues reconciliation, and emergency dispatch tools.</p>

            <h4>2. Data Controller vs. Data Processor (NDP Act 2023 Section 24 &amp; GAID 2025)</h4>
            <p>The Subscribing Property/Client acts as the primary <strong>Data Controller</strong>, determining legitimate access rules and resident records. Sheffy Technologies Limited acts strictly as the <strong>Data Processor</strong>, processing visitor, resident, and movement telemetry solely on the Controller's instructions with zero third-party monetization.</p>

            <h4>3. Bank-Grade Security &amp; Direct Dues Settlement</h4>
            <p>All data at rest is protected with AES-256 bit encryption and TLS 1.3 in transit. All dues and service charges paid digitally or via direct bank transfer settlement go 100% directly into the designated bank account of the property.</p>

            <h4>4. Data Portability &amp; 30-Day Exit Guarantee</h4>
            <p>The Subscriber may terminate service with 30 days written notice with zero exit penalties. CheckPass will supply an encrypted, full CSV export of all resident rosters, dues ledgers, and audit history prior to secure data purging.</p>
          </div>

          <div v-else-if="legalTab === 'terms'" class="legal-flow">
            <h3>Platform Terms of Use</h3>
            <p>Authorized personnel must maintain confidentiality of gatehouse PINs and admin credentials. Automated local offline token caching ensures security guards can continue validating digital passes even during cellular internet downtime.</p>
          </div>

          <div v-else class="legal-flow">
            <h3>NDP Act 2023 &amp; GAID 2025 Compliance Standards</h3>
            <p>CheckPass adheres to the <strong>Nigeria Data Protection Act 2023</strong> and the <strong>General Application and Implementation Directive (GAID) 2025</strong> published by the Nigeria Data Protection Commission (NDPC). Visitor logs are purged after 180 days, personal phone numbers are masked on guard terminals, and full audit logs are archived for corporate governance.</p>
          </div>
        </div>

        <div class="estate-legal-footer">
          <button type="button" class="btn-legal-accept" @click="acceptLegal">Accept &amp; Continue Onboarding</button>
        </div>
      </div>
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
const agreeCommunityTerms = ref(false)
const legalModalOpen = ref(false)
const legalTab = ref<'agreement' | 'terms' | 'gaid'>('agreement')

function openLegal(tab: 'agreement' | 'terms' | 'gaid') {
  legalTab.value = tab
  legalModalOpen.value = true
}

function acceptLegal() {
  agreeCommunityTerms.value = true
  legalModalOpen.value = false
}
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

@keyframes heroEntrance {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.hero-headline {
  animation: heroEntrance 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
  will-change: opacity, transform;
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
  animation: heroEntrance 0.52s cubic-bezier(0.16, 1, 0.3, 1) 0.12s both;
  will-change: opacity, transform;
  font-size: clamp(16px, 1.2vw, 18.5px);
  line-height: 1.48;
  color: rgba(255, 255, 255, 0.92);
  margin: 0 0 24px;
  font-weight: 450;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
}

.hero-footer-trust {
  position: relative;
  z-index: 5;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 11.5px;
  color: rgba(255, 255, 255, 0.65);
  padding-top: 14px;
  border-top: 1px solid rgba(255, 255, 255, 0.14);
}

.hero-footer-trust svg {
  color: #00C97A;
  flex-shrink: 0;
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
  overflow: hidden;
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
  z-index: 2;
}

.text-input {
  width: 100%;
  height: 44px;
  padding: 0 14px 0 42px;
  border: none;
  background: transparent;
  border-radius: 12px;
  font-size: 14.5px;
  color: #0F172A;
  outline: none;
}

/* Eliminate browser autofill light-blue background and cut-corner clipping */
.text-input:-webkit-autofill,
.text-input:-webkit-autofill:hover,
.text-input:-webkit-autofill:focus,
.text-input:-webkit-autofill:active {
  -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset !important;
  box-shadow: 0 0 0 1000px #FFFFFF inset !important;
  -webkit-text-fill-color: #0F172A !important;
  caret-color: #0F172A !important;
  border-radius: 12px !important;
  transition: background-color 5000s ease-in-out 0s;
}

.input-wrapper:not(:focus-within) .text-input:-webkit-autofill {
  -webkit-box-shadow: 0 0 0 1000px #F8FAFC inset !important;
  box-shadow: 0 0 0 1000px #F8FAFC inset !important;
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

.community-terms-block {
  margin: 14px 0 16px;
  padding: 12px 14px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
}
.community-terms-label {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  font-size: 12px;
  line-height: 1.45;
  color: #475569;
  cursor: pointer;
}
.community-terms-checkbox {
  width: 16px;
  height: 16px;
  margin-top: 2px;
  accent-color: #0A5C38;
  cursor: pointer;
  flex-shrink: 0;
}
.community-terms-label a {
  color: #0A5C38;
  font-weight: 600;
  text-decoration: underline;
}
.estate-legal-modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(7, 31, 20, 0.75);
  backdrop-filter: blur(6px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.estate-legal-modal-card {
  background: #fff;
  border-radius: 16px;
  width: 100%;
  max-width: 760px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 20px 50px rgba(0,0,0,0.3);
}
.estate-legal-header {
  padding: 16px 20px;
  border-bottom: 1px solid #E2E8F0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #F8FAFC;
}
.estate-legal-tabs {
  display: flex;
  gap: 6px;
  background: #E2E8F0;
  padding: 3px;
  border-radius: 8px;
}
.estate-tab-btn {
  border: none;
  background: transparent;
  padding: 6px 12px;
  font-size: 11.5px;
  font-weight: 600;
  color: #64748B;
  border-radius: 6px;
  cursor: pointer;
}
.estate-tab-btn.active {
  background: #fff;
  color: #0A5C38;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.estate-legal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #64748B;
  cursor: pointer;
}
.estate-legal-body {
  padding: 24px;
  overflow-y: auto;
  font-size: 13.5px;
  line-height: 1.65;
  color: #334155;
}
.dpa-badge {
  display: inline-block;
  background: #ECFDF5;
  color: #065F46;
  border: 1px solid #A7F3D0;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 700;
  margin-bottom: 10px;
}
.legal-flow h3 {
  font-size: 16px;
  font-weight: 800;
  color: #071F14;
  margin: 0 0 4px;
}
.legal-flow .legal-sub {
  font-size: 12px;
  color: #64748B;
  margin: 0 0 16px;
}
.legal-flow h4 {
  font-size: 13.5px;
  font-weight: 700;
  color: #071F14;
  margin: 16px 0 6px;
}
.estate-legal-footer {
  padding: 14px 20px;
  border-top: 1px solid #E2E8F0;
  background: #F8FAFC;
  display: flex;
  justify-content: flex-end;
}
.btn-legal-accept {
  background: #0A5C38;
  color: #fff;
  border: none;
  padding: 9px 20px;
  border-radius: 8px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
}

</style>

