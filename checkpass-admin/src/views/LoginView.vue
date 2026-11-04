<template>
  <div class="auth-page">
    <div class="auth-container">

      <!-- Left Hero Visual (58% Screen Real Estate) -->
      <div class="hero-slider-section">
        <div
          class="hero-backdrop-image"
          :style="{ backgroundImage: `url('${currentHero.image}')` }"
        />
        <div class="hero-top-vignette" />
        <div class="hero-bottom-gradient" />

        <!-- Top Checkpass Brand Logo -->
        <div class="hero-brand-header">
          <img src="/checkpass-logo.svg" alt="Checkpass" class="brand-logo-white" />
        </div>

        <!-- Bottom Narrative Overlay -->
        <div class="hero-content-overlay">
          <div class="hero-tag">{{ currentHero.tag }}</div>
          <h2 class="hero-headline">{{ currentHero.headline }}</h2>
          <p class="hero-subhead">
            {{ currentHero.subhead }}
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

          <!-- Categorized 3-Role Selector Tabs -->
          <div class="role-selector-wrap">
            <label class="role-selector-label">SELECT ACCESS PORTAL</label>
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
                <div class="tab-icon-wrap">
                  <!-- Phosphor Buildings Icon -->
                  <svg viewBox="0 0 256 256" width="20" height="20" fill="currentColor">
                    <path d="M240,208H224V96a8,8,0,0,0-8-8H168V40a8,8,0,0,0-8-8H40a8,8,0,0,0-8,8V208H16a8,8,0,0,0,0,16H240a8,8,0,0,0,0-16ZM48,48H152V208H48ZM208,104V208H168V104ZM88,80a8,8,0,0,1,8-8h16a8,8,0,0,1,0,16H96A8,8,0,0,1,88,80Zm0,40a8,8,0,0,1,8-8h16a8,8,0,0,1,0,16H96A8,8,0,0,1,88,120Zm0,40a8,8,0,0,1,8-8h16a8,8,0,0,1,0,16H96A8,8,0,0,1,88,160Z"/>
                  </svg>
                </div>
                <div class="tab-text-wrap">
                  <span class="role-title">Estate Admin</span>
                  <span class="role-desc">Facility &amp; Dues</span>
                </div>
              </button>

              <!-- Security / Guard Tab -->
              <button
                type="button"
                class="role-tab"
                :class="{ active: activeRole === 'guard' }"
                @click="setRole('guard')"
                role="tab"
                :aria-selected="activeRole === 'guard'"
              >
                <div class="tab-icon-wrap">
                  <!-- Phosphor ShieldCheck Icon -->
                  <svg viewBox="0 0 256 256" width="20" height="20" fill="currentColor">
                    <path d="M208,40H48A16,16,0,0,0,32,56v56c0,82.4,62.74,123.63,92.51,134.78a8.26,8.26,0,0,0,6.98,0C161.26,235.63,224,194.4,224,112V56A16,16,0,0,0,208,40Zm0,72c0,71.22-54.3,107.41-80,118.25C102.3,219.41,48,183.22,48,112V56H208Zm-34.34-29.66a8,8,0,0,1,0,11.32l-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,132.69l50.34-50.35A8,8,0,0,1,173.66,82.34Z"/>
                  </svg>
                </div>
                <div class="tab-text-wrap">
                  <span class="role-title">Security</span>
                  <span class="role-desc">Gate Terminal</span>
                </div>
              </button>

              <!-- Resident Tab -->
              <button
                type="button"
                class="role-tab"
                :class="{ active: activeRole === 'resident' }"
                @click="setRole('resident')"
                role="tab"
                :aria-selected="activeRole === 'resident'"
              >
                <div class="tab-icon-wrap">
                  <!-- Phosphor HouseLine Icon -->
                  <svg viewBox="0 0 256 256" width="20" height="20" fill="currentColor">
                    <path d="M240,208H224V136l11.42,8.57a8,8,0,1,0,9.6-12.8l-112-84a8,8,0,0,0-9.6,0l-112,84a8,8,0,1,0,9.6,12.8L32,136v72H16a8,8,0,0,0,0,16H240a8,8,0,0,0,0-16ZM48,124,128,64l80,60V208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48Zm96,84H112V160h32Z"/>
                  </svg>
                </div>
                <div class="tab-text-wrap">
                  <span class="role-title">Resident</span>
                  <span class="role-desc">Passes &amp; SOS</span>
                </div>
              </button>
            </div>
          </div>

          <!-- Header Group -->
          <div class="form-header-group">
            <h1 class="form-title">{{ currentForm.title }}</h1>
            <p class="form-subtitle">{{ currentForm.subtitle }}</p>
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

          <!-- Resident Success Feedback -->
          <transition name="alert-slide">
            <div v-if="residentLoggedIn" class="resident-success-card">
              <div class="success-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <strong>Welcome back, Resident!</strong>
              </div>
              <p>You are authenticated. Launch the full resident mobile app experience:</p>
              <a :href="residentAppUrl" class="btn-launch-resident">
                Open Checkpass Resident App
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
              </a>
            </div>
          </transition>

          <!-- Sign-In Form -->
          <form v-if="!residentLoggedIn" @submit.prevent="handleLogin" class="signin-form">

            <!-- Dynamic Identifier (Work Email / Phone / Gate Login) -->
            <div class="input-block">
              <label class="input-label" for="login-identifier">{{ currentForm.identifierLabel }}</label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <!-- Phone / User icon for guard/resident or Email for admin -->
                  <svg v-if="activeRole === 'admin'" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                  <svg v-else width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                </span>
                <input
                  id="login-identifier"
                  v-model="identifier"
                  :type="activeRole === 'admin' ? 'email' : 'text'"
                  class="text-input"
                  :placeholder="currentForm.identifierPlaceholder"
                  :autocomplete="activeRole === 'admin' ? 'email' : 'username'"
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
              <BrandSpinner v-if="loading" :size="18" color="#ffffff" />
              <span v-else class="btn-content">
                {{ currentForm.buttonText }}
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </span>
            </button>

          </form>

          <!-- Dynamic Footer Links based on Role -->
          <div class="form-bottom-links">
            <template v-if="activeRole === 'admin'">
              <span class="new-estate-text">Setting up a new estate?</span>
              <router-link to="/register" class="register-link">Create a free estate account &rarr;</router-link>
            </template>
            <template v-else-if="activeRole === 'guard'">
              <span class="new-estate-text">New security post?</span>
              <span class="role-help-text">Contact your Chief Security Officer or Estate Manager to be assigned to a gate terminal.</span>
            </template>
            <template v-else>
              <span class="new-estate-text">Prefer mobile app?</span>
              <a :href="residentAppUrl" class="register-link">Open resident web app &rarr;</a>
            </template>
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

type RoleType = 'admin' | 'guard' | 'resident'

const router = useRouter()
const auth   = useAuthStore()

const activeRole = ref<RoleType>('admin')
const identifier = ref('')
const password = ref('')
const showPassword = ref(false)
const loading  = ref(false)
const error    = ref('')
const residentLoggedIn = ref(false)

const residentAppUrl = computed(() => {
  // Points to local dev port for checkpass-app or production host
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
  // Pre-clear identifier if switching between admin email and phone/guard ID
  if (role === 'admin' && identifier.value.includes('@') === false && identifier.value.length > 0) {
    identifier.value = ''
  }
}

const currentHero = computed(() => {
  switch (activeRole.value) {
    case 'guard':
      return {
        image: '/register-hero-value.jpg',
        tag: 'Gate Terminal · Fast Pass Scanner · License Plate Log',
        headline: 'Sub-4-Second Clearance.',
        subhead: 'Instant QR and phone-code pass verification at every gate. Keep estate traffic moving smoothly and securely without paperwork.',
      }
    case 'resident':
      return {
        image: '/register-hero-experience.jpg',
        tag: 'Fast Passes · Family Clearance · Instant SOS',
        headline: 'Effortless Living.',
        subhead: 'Pre-approve visitor passes in seconds, receive arrival alerts, pay estate dues, and instantly dispatch emergency SOS.',
      }
    case 'admin':
    default:
      return {
        image: '/register-hero-trust.jpg',
        tag: 'Estate Command · Gate Audit · Resident Dues',
        headline: 'Build Trust.',
        subhead: 'Zero guesswork at the gate. Every visitor is pre-cleared by residents and instantly verified by security before arrival.',
      }
  }
})

const currentForm = computed(() => {
  switch (activeRole.value) {
    case 'guard':
      return {
        title: 'Sign in to Gate Terminal',
        subtitle: 'Log in with your guard phone number or gate credentials to scan passes and clear visitor arrivals.',
        identifierLabel: 'Guard phone or gate ID',
        identifierPlaceholder: 'e.g. 08012345678 or gate_security1',
        buttonText: 'Launch Gate Terminal',
      }
    case 'resident':
      return {
        title: 'Resident Sign-In',
        subtitle: 'Access your household portal to create guest passes, pay dues, and trigger emergency support.',
        identifierLabel: 'Registered phone or email',
        identifierPlaceholder: 'e.g. 08012345678 or resident@flat.com',
        buttonText: 'Sign In as Resident',
      }
    case 'admin':
    default:
      return {
        title: 'Sign in to Checkpass',
        subtitle: 'Enter your official work credentials to manage your estate, monitor gate clearance, and track finances.',
        identifierLabel: 'Official work email',
        identifierPlaceholder: 'admin@estate.com',
        buttonText: 'Sign In to Admin Portal',
      }
  }
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
      // Resident
      await auth.loginUser(identifier.value.trim(), password.value, 'resident')
      residentLoggedIn.value = true
    }
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    if (activeRole.value === 'guard') {
      error.value = err.response?.data?.message ?? 'Invalid gate credentials. Please verify your phone or guard username and password.'
    } else if (activeRole.value === 'resident') {
      error.value = err.response?.data?.message ?? 'Incorrect resident credentials. Please verify your phone number or email and password.'
    } else {
      error.value = err.response?.data?.message ?? 'Invalid work credentials. Please verify your official work email and password.'
    }
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
  background-size: cover;
  background-position: center 25%;
  background-repeat: no-repeat;
  filter: brightness(0.92);
  transition: background-image 0.4s ease-in-out;
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
  font-family: -apple-system, BlinkMacSystemFont, "Plus Jakarta Sans", sans-serif;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
}

.hero-subhead {
  font-size: clamp(14.5px, 1.25vw, 17px);
  line-height: 1.55;
  color: rgba(255, 255, 255, 0.88);
  margin: 0 0 24px;
  max-width: 520px;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
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

.hero-footer-trust svg {
  color: #34D399;
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

/* ── 3-Role Categorized Tabs Grid ── */
.role-selector-wrap {
  margin-bottom: 24px;
}

.role-selector-label {
  display: block;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: #64748B;
  margin-bottom: 8px;
  text-transform: uppercase;
}

.role-tabs-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 6px;
  background: #F1F5F9;
  padding: 5px;
  border-radius: 14px;
  border: 1px solid #E2E8F0;
}

.role-tab {
  background: transparent;
  border: 1px solid transparent;
  border-radius: 10px;
  padding: 10px 4px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 5px;
  color: #64748B;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  text-align: center;
}

.role-tab:hover:not(.active) {
  background: rgba(255, 255, 255, 0.6);
  color: #1E293B;
}

.role-tab.active {
  background: #FFFFFF;
  color: #047857;
  border-color: rgba(5, 150, 105, 0.2);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
}

.tab-icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  color: inherit;
  transition: transform 0.2s ease;
}

.role-tab.active .tab-icon-wrap {
  color: #059669;
  transform: scale(1.08);
}

.tab-text-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1px;
}

.role-title {
  font-size: 12.5px;
  font-weight: 700;
  line-height: 1.2;
}

.role-desc {
  font-size: 10px;
  font-weight: 500;
  color: #94A3B8;
  line-height: 1.2;
}

.role-tab.active .role-desc {
  color: #059669;
  font-weight: 600;
}

/* ── Header Group ── */
.form-header-group {
  margin-bottom: 22px;
}

.form-title {
  font-size: clamp(24px, 2.1vw, 30px);
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 6px;
  letter-spacing: -0.025em;
  font-family: -apple-system, BlinkMacSystemFont, "Plus Jakarta Sans", sans-serif;
}

.form-subtitle {
  font-size: 13.5px;
  line-height: 1.5;
  color: #64748B;
  margin: 0;
}

/* ── Alerts ── */
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

/* ── Form Inputs ── */
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

/* ── Footer Links ── */
.form-bottom-links {
  margin-top: 22px;
  padding-top: 18px;
  border-top: 1px solid #F1F5F9;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
  font-size: 13.5px;
}

.new-estate-text {
  color: #64748B;
  font-size: 13px;
}

.role-help-text {
  font-size: 12px;
  color: #94A3B8;
  max-width: 340px;
  line-height: 1.4;
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
  margin-top: 20px;
  font-size: 12px;
  color: #64748B;
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .hero-slider-section { display: none; }
  .form-interaction-section { width: 100%; min-height: 100vh; padding: 24px 16px; }
}
</style>
