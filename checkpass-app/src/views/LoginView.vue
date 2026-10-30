<template>
  <ion-page>
    <ion-content class="login-content" :fullscreen="true">
      <div class="login-screen">
        <div class="login-container">

          <!-- Top Primary Header (The Circled Area from Reference) -->
          <div class="primary-header">
            <div class="header-content">
              <!-- White Checkpass Logo - Reduced Size, Clean & Isolated -->
              <img src="/logo-dark.png" alt="Checkpass" class="brand-logo-white" />
            </div>
          </div>

        <!-- Curved White Bottom Card holding Form -->
        <div class="sheet-card">
          
          <!-- Heading -->
          <div class="heading-block">
            <h1>Log In</h1>
            <p>Securely access visitor passes, gate alerts, and estate clearance.</p>
          </div>

          <!-- Banners for session expired or suspended -->
          <div v-if="expiredBanner" class="banner banner-warn">
            Your session expired. Please log in again.
          </div>
          <div v-if="suspendedBanner" class="banner banner-error">
            Your account has been suspended. Contact your estate manager.
          </div>

          <!-- Login Form -->
          <form @submit.prevent="handleLogin" class="form-block">
            
            <!-- Email / Phone Number Field -->
            <div class="field">
              <label class="field-label">Email / Phone No.<span class="required">*</span></label>
              <div class="input-wrap" :class="{ 'input-error': !!fieldError.login }">
                <input
                  v-model="loginIdentifier"
                  :type="isEmail ? 'email' : 'text'"
                  placeholder="e.g. 0813 248 9619 or name@email.com"
                  autocomplete="username"
                  required
                />
              </div>
            </div>

            <!-- Password Field -->
            <div class="field">
              <label class="field-label">Password<span class="required">*</span></label>
              <div class="input-wrap" :class="{ 'input-error': !!authError }">
                <input
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  placeholder="Enter your password"
                  autocomplete="current-password"
                  required
                />
                <button
                  type="button"
                  class="toggle-pw"
                  @click="showPassword = !showPassword"
                  :aria-label="showPassword ? 'Hide password' : 'Show password'"
                >
                  <!-- Closed eye with slash when password is NOT showing -->
                  <svg v-if="!showPassword" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                  <!-- Open eye when password IS shown -->
                  <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
              </div>
              <div v-if="authError" class="inline-error">{{ authError }}</div>
            </div>

            <!-- Forgot Password Row -->
            <div class="options-row">
              <router-link to="/forgot-password" class="forgot-link">Forgot your Password?</router-link>
            </div>

            <!-- Terms of Service & Privacy Policy Checkbox (Always unchecked by default) -->
            <div class="terms-consent-row">
              <label class="terms-checkbox-label">
                <input
                  v-model="agreeTerms"
                  type="checkbox"
                  class="terms-checkbox-input"
                  required
                />
                <span class="terms-text">
                  I agree to the <button type="button" class="legal-link-btn" @click="openLegalModal('terms')">Terms of Service</button> &amp; <button type="button" class="legal-link-btn" @click="openLegalModal('privacy')">Privacy Policy</button>
                </span>
              </label>
            </div>

            <!-- Primary Action Button -->
            <button
              type="submit"
              class="login-submit-btn"
              :disabled="!canSubmit || loading"
            >
              <span>Log In</span>
            </button>

            <!-- Section 1: Resident Guidance (No self-registration) -->
            <div class="resident-access-prompt">
              <span>Don't have an account?</span>
              <button type="button" class="contact-estate-btn" @click="showResidentAccessInfo">
                Contact your estate office
              </button>
            </div>

            <!-- Subtle Section Divider -->
            <div class="auth-divider">
              <span class="divider-line"></span>
              <span class="divider-text">OR</span>
              <span class="divider-line"></span>
            </div>

            <!-- Section 2: Estate Manager / Landlord Web Portal CTA -->
            <div class="estate-manager-card">
              <div class="manager-card-top">
                <div class="manager-icon-pod">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 21H21" stroke="#0A5C38" stroke-width="2" stroke-linecap="round"/>
                    <path d="M5 21V7L13 3V21" stroke="#0A5C38" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19 21V11L13 7" stroke="#0A5C38" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M9 9V9.01" stroke="#0A5C38" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M9 13V13.01" stroke="#0A5C38" stroke-width="2.2" stroke-linecap="round"/>
                    <path d="M9 17V17.01" stroke="#0A5C38" stroke-width="2.2" stroke-linecap="round"/>
                  </svg>
                </div>
                <div class="manager-card-info">
                  <div class="manager-card-title">Have an estate to manage?</div>
                  <div class="manager-card-desc">Set up Checkpass for your estate or HOA on the web portal.</div>
                  <a
                    href="https://checkpass.ng/register"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="manager-portal-link-text"
                  >
                    <span>Create estate account</span>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M7 17L17 7"/>
                      <path d="M7 7H17V17"/>
                    </svg>
                  </a>
                </div>
              </div>
            </div>

            <!-- Legal Footer Attribution -->
            <div class="login-footer">
              <p>&copy; 2026 Checkpass &middot; Privacy &middot; Security &middot; Terms</p>
              <p class="sheffy-sub">Powered by <strong>Sheffy Technologies</strong></p>
            </div>

          </form>

        </div>
        </div>

      </div>

      <!-- Centered Brand Loading Popup Overlay -->
      <BrandLoadingOverlay :visible="loading" :size="96" color="#FFFFFF" />

      <!-- Legal Modal Sheet (Terms & Privacy Viewer) -->
      <ion-modal :is-open="legalModalOpen" @didDismiss="legalModalOpen = false" class="legal-sheet-modal">
        <div class="legal-modal-shell">
          <div class="legal-modal-header">
            <div class="legal-tab-group">
              <button
                type="button"
                class="legal-tab-btn"
                :class="{ active: legalTab === 'terms' }"
                @click="legalTab = 'terms'"
              >
                Terms of Service
              </button>
              <button
                type="button"
                class="legal-tab-btn"
                :class="{ active: legalTab === 'privacy' }"
                @click="legalTab = 'privacy'"
              >
                Privacy Policy
              </button>
            </div>
            <button type="button" class="legal-close-btn" @click="legalModalOpen = false">&times;</button>
          </div>

          <div class="legal-modal-body">
            <!-- Terms Tab -->
            <div v-if="legalTab === 'terms'" class="legal-text-flow">
              <h3>Checkpass Terms of Service</h3>
              <p class="legal-date">Last Updated: October 2026 &middot; Sheffy Technologies Limited (Nigeria)</p>
              
              <h4>1. Platform Nature &amp; Estate Authority</h4>
              <p>Checkpass is an independent digital proptech infrastructure platform operated by Sheffy Technologies Limited. Checkpass is not a security guard agency, emergency dispatch unit, or law enforcement body. Physical admission into any residential estate is governed exclusively by that estate's Homeowners Association (HOA) bylaws and on-duty security guards.</p>

              <h4>2. Resident Accounts &amp; Authorized Provisioning</h4>
              <p>Resident accounts are provisioned exclusively through authorized Estate Administrators. Residents cannot self-onboard without an assigned unit. Upon first login, residents must replace default or temporary passwords with a confidential, high-strength password. Residents are strictly responsible for safeguarding their login credentials.</p>

              <h4>3. Visitor Passes &amp; Host Warranty</h4>
              <p>When issuing digital visitor passes, QR codes, or daily access phrases, the resident host warrants that: (a) they have obtained the visitor's prior consent to provide their name and phone number for gate verification; (b) the visit complies with estate community regulations; and (c) the resident assumes responsibility for their visitor's conduct and departure.</p>

              <h4>4. Estate Dues &amp; Payment Processing</h4>
              <p>All estate service charges, facility dues, and levy payments are processed through PCI-DSS Level 1 certified payment gateways (Paystack Payments Limited). Checkpass never stores raw credit or debit card numbers. Any dispute regarding levy calculations, penalties, or estate refunds is strictly between the resident and the Estate HOA.</p>

              <h4>5. Emergency &amp; Panic Alerts Disclaimer</h4>
              <p>The emergency panic distress functionality is a digital broadcast relay designed to alert estate gate security. Checkpass DOES NOT guarantee physical response times by security, police, or medical services. In critical emergencies, users must contact Nigerian emergency services directly (112 or 199).</p>

              <h4>6. Prohibited Activities</h4>
              <p>Users shall not generate fraudulent passes for commercial trespass, reverse engineer platform APIs, bypass QR cryptographic verification, or submit false emergency alerts.</p>

              <h4>7. Governing Law</h4>
              <p>These Terms are governed by and construed in accordance with the laws of the Federal Republic of Nigeria (Arbitration and Mediation Act 2023).</p>
            </div>

            <!-- Privacy Tab -->
            <div v-else class="legal-text-flow">
              <h3>Checkpass Privacy Framework &amp; NDPA Compliance</h3>
              <p class="legal-date">NDPA 2023 &amp; NDPR Certified &middot; Bank-Grade Data Protection</p>

              <h4>1. Dual-Role Architecture (Controller vs. Processor)</h4>
              <p>Under Section 24 of the Nigeria Data Protection Act 2023 (NDPA), your Estate Homeowners Association (HOA) acts as the Data Controller for unit allocations, resident directories, and gate logs. Checkpass acts as the Data Processor managing cloud hosting, cryptographic QR tokens, and mobile app infrastructure.</p>

              <h4>2. Information We Process</h4>
              <p>We process only data essential for gated perimeter integrity: (a) Resident full name, verified telephone number, estate name, and unit flat address; (b) Visitor full name, phone number, vehicle plate, and gate timestamp; (c) Push notification device tokens for arrival alerts; (d) Emergency SOS logs.</p>

              <h4>3. Telecommunications &amp; Camera Permissions</h4>
              <p>Camera access is requested solely for scanning visitor QR codes at gatehouses or photographing declared exit items. Camera streams are processed locally and are never stored without user action. Device push tokens are used exclusively for security notifications.</p>

              <h4>4. Cardholder Data &amp; Financial Security</h4>
              <p>Checkpass does NOT collect or store full credit/debit card numbers, CVVs, or card PINs. All payment transactions are securely tokenized and handled directly by Paystack (PCI-DSS compliant).</p>

              <h4>5. Data Subject Rights &amp; Retention</h4>
              <p>In accordance with the NDPA 2023, you have the right to inspect visitor logs linked to your residence, request data rectification, or request account closure through your Estate Administration office.</p>
            </div>
          </div>

          <div class="legal-modal-footer">
            <button type="button" class="legal-agree-action-btn" @click="acceptLegalModal">
              I Understand &amp; Agree
            </button>
          </div>
        </div>
      </ion-modal>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { IonPage, IonContent, IonModal, alertController } from '@ionic/vue'
import BrandLoadingOverlay from '@/components/BrandLoadingOverlay.vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import { authApi } from '@/api/auth'

const router = useRouter()
const route  = useRoute()
const auth   = useAuthStore()
const { showToast } = useToast()

const loginIdentifier = ref('')
const password        = ref('')
const loading         = ref(false)
const authError       = ref('')
const showPassword    = ref(false)
const fieldError      = ref({ login: '', password: '' })
const agreeTerms      = ref(false)
const legalModalOpen  = ref(false)
const legalTab        = ref<'terms' | 'privacy'>('terms')

function openLegalModal(tab: 'terms' | 'privacy') {
  legalTab.value = tab
  legalModalOpen.value = true
}

function acceptLegalModal() {
  agreeTerms.value = true
  legalModalOpen.value = false
}

const expiredBanner   = computed(() => route.query.expired === '1')
const suspendedBanner = computed(() => route.query.suspended === '1')
const isEmail         = computed(() => loginIdentifier.value.includes('@'))

const canSubmit = computed(() => {
  if (!agreeTerms.value) return false
  const val = loginIdentifier.value.trim()
  if (val.includes('@')) {
    return val.length >= 5 && password.value.length >= 1
  }
  return val.replace(/\D/g, '').length >= 9 && password.value.length >= 1
})

async function handleLogin() {
  authError.value = ''
  loading.value = true

  try {
    const val = loginIdentifier.value.trim()
    let credential = val
    if (!val.includes('@')) {
      const rawDigits = val.replace(/\D/g, '')
      const clean = rawDigits.startsWith('234') ? rawDigits.slice(3) : rawDigits.replace(/^0+/, '')
      credential = `0${clean}`
    }

    const res = await authApi.login(credential, password.value)
    const { token, refreshToken, resident, userType, data: user } = res.data
    auth.setSession({ token, refreshToken, resident, userType, user })

    // Check if user logged in with default/initial password or requires password change
    const isDefaultPassword = password.value === '123456'
    const mustChange = isDefaultPassword || !!(user as any)?.must_change_password

    if (mustChange) {
      sessionStorage.setItem('w_temp_pwd', password.value)
      showToast('Temporary password detected. Please set your new password.', 'warning')
      router.replace('/set-new-password')
    } else {
      showToast('Welcome back!', 'success')
      router.replace('/tabs/home')
    }
  } catch (e: any) {
    const code = e.response?.data?.code
    const status = e.response?.status
    const validationErrors = e.response?.data?.errors
    if (code === 'AUTH_INVALID_CREDENTIALS' || status === 422 || validationErrors?.phone) {
      authError.value = 'Incorrect phone number or password. Try again.'
      showToast('Incorrect phone number or password.', 'error')
    } else if (code === 'AUTH_ACCOUNT_DISABLED' || code === 'ACCOUNT_SUSPENDED' || status === 403) {
      const msg = e.response?.data?.message || 'Your account has been suspended. Contact your estate manager.'
      authError.value = msg
      showToast(msg, 'error')
    } else {
      authError.value = e.response?.data?.message || 'Something went wrong. Try again.'
      showToast(authError.value, 'error')
    }
  } finally {
    loading.value = false
  }
}



async function showResidentAccessInfo() {
  const alert = await alertController.create({
    header: 'Resident Account Setup',
    subHeader: 'Managed by Estate Office',
    message: 'To maintain estate perimeter security, resident accounts cannot be created directly on the mobile app.\n\nYour Estate Manager or HOA administrative office registers each apartment unit and issues login credentials. Please contact your estate office with your unit details to get access.',
    buttons: ['Understood'],
  })
  await alert.present()
}
</script>

<style scoped>
.login-content {
  --background: #0A5C38;
}

.login-screen {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #0A5C38;
  align-items: center;
  justify-content: flex-start;
}

.login-container {
  width: 100%;
  max-width: 440px;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background-color: #0A5C38;
  position: relative;
}

/* ─── PRIMARY GREEN HEADER ─── */
.primary-header {
  background: #0A5C38;
  padding: calc(env(safe-area-inset-top, 0px) + 38px) 24px 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.header-content {
  position: relative;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* White Checkpass Logo - Clean & Centered */
.brand-logo-white {
  height: 28px;
  width: auto;
  max-width: 170px;
  object-fit: contain;
  display: block;
  filter: brightness(0) invert(1);
  transition: transform 0.2s ease;
}

/* ─── CURVED WHITE CARD HOLDING FORM ─── */
.sheet-card {
  flex: 1;
  background: #FFFFFF;
  border-top-left-radius: 32px;
  border-top-right-radius: 32px;
  padding: 32px 24px 40px;
  display: flex;
  flex-direction: column;
}

/* Heading */
.heading-block {
  margin-bottom: 24px;
}

.heading-block h1 {
  font-family: var(--w-font-display, 'Bricolage Grotesque', system-ui, sans-serif);
  font-size: 24px;
  font-weight: 700;
  color: #112019;
  letter-spacing: -0.02em;
  margin: 0 0 6px;
}

.heading-block p {
  font-size: 13.5px;
  color: #66756C;
  line-height: 1.45;
  margin: 0;
}

/* Banners */
.banner {
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 13px;
  font-weight: 500;
  margin-bottom: 18px;
}
.banner-warn {
  background: #FEF3C7;
  color: #92400E;
  border: 1px solid #FDE68A;
}
.banner-error {
  background: #FEE2E2;
  color: #B91C1C;
  border: 1px solid #FECACA;
}

/* Form */
.form-block {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.field-label {
  font-size: 13px;
  font-weight: 600;
  color: #1A2E23;
}

.required {
  color: #DC2626;
  margin-left: 2px;
}

.input-wrap {
  display: flex;
  align-items: center;
  background: #F8FAF9;
  border: 1.5px solid #DCE6E0;
  border-radius: 14px;
  overflow: hidden;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.input-wrap:focus-within {
  background: #FFFFFF;
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.12);
}

.input-wrap.input-error {
  border-color: #DC2626;
  background: #FEF2F2;
}

.prefix {
  padding: 0 10px 0 14px;
  font-size: 14.5px;
  color: #0A5C38;
  font-weight: 700;
  border-right: 1px solid #DCE6E0;
}

.input-wrap input {
  flex: 1;
  border: none;
  outline: none;
  padding: 13px 14px;
  font-size: 14.5px;
  background: transparent;
  color: #112019;
  font-family: inherit;
}

.input-wrap input::placeholder {
  color: #94A39A;
}

.toggle-pw {
  background: none;
  border: none;
  padding: 0 14px;
  color: #66756C;
  font-size: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.toggle-pw:hover {
  color: #0A5C38;
}

.inline-error {
  font-size: 12.5px;
  color: #DC2626;
  margin-top: 4px;
}

/* Forgot Password Row */
.options-row {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  margin-top: 4px;
  margin-bottom: 8px;
  font-size: 13px;
}

.forgot-link {
  color: #0A5C38;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.15s ease;
}

.forgot-link:hover {
  color: #074429;
  text-decoration: underline;
}

/* Primary Submit Button (Lighter shade of primary green) */
.login-submit-btn {
  background: #0E8A54;
  color: #FFFFFF;
  border: none;
  outline: none;
  height: 50px;
  border-radius: 999px;
  font-size: 15px;
  font-weight: 700;
  letter-spacing: -0.01em;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 4px 14px rgba(14, 138, 84, 0.28);
  margin-top: 6px;
}

.login-submit-btn:hover:not(:disabled) {
  background: #119E61;
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(14, 138, 84, 0.36);
}

.login-submit-btn:active:not(:disabled) {
  transform: translateY(0);
}

.login-submit-btn:disabled {
  background: #86CBA8;
  color: #FFFFFF;
  opacity: 0.85;
  cursor: not-allowed;
  box-shadow: none;
}

/* Resident Guidance Prompt */
.resident-access-prompt {
  text-align: center;
  font-size: 13px;
  color: #66756C;
  margin-top: 16px;
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 5px;
}

.contact-estate-btn {
  background: none;
  border: none;
  padding: 0;
  color: #0A5C38;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 2px;
  font-family: inherit;
  transition: color 0.15s ease;
}

.contact-estate-btn:hover {
  color: #074429;
}

/* Subtle Divider */
.auth-divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 18px 0 14px;
}

.divider-line {
  flex: 1;
  height: 1px;
  background: #E5E3DF;
}

.divider-text {
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: 0.08em;
  color: #94A3B8;
  text-transform: uppercase;
}

/* Estate Manager Portal Card */
.estate-manager-card {
  background: #F4FBF7;
  border: 1px solid rgba(10, 92, 56, 0.14);
  border-radius: 16px;
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.manager-card-top {
  display: flex;
  align-items: flex-start;
  gap: 10px;
}

.manager-icon-pod {
  width: 32px;
  height: 32px;
  border-radius: 9px;
  background: rgba(10, 92, 56, 0.10);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.manager-card-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.manager-card-title {
  font-size: 13px;
  font-weight: 700;
  color: #0D261B;
}

.manager-card-desc {
  font-size: 11.5px;
  color: #556B60;
  line-height: 1.4;
}

.manager-portal-link-text {
  background: none;
  border: none;
  padding: 0;
  color: #0A5C38;
  font-weight: 700;
  font-size: 12.5px;
  cursor: pointer;
  text-decoration: underline;
  text-underline-offset: 2px;
  font-family: inherit;
  display: inline-flex;
  align-items: center;
  gap: 3px;
  margin-top: 4px;
  transition: color 0.15s ease;
}

.manager-portal-link-text:hover {
  color: #074429;
}

/* Footer Attribution */
.login-footer {
  margin-top: auto;
  padding-top: 28px;
  text-align: center;
  font-size: 11.5px;
  color: #8A9A90;
  line-height: 1.5;
}

.sheffy-sub {
  margin-top: 2px;
  font-size: 11px;
  color: #A3B3A9;
}

/* ── Terms & Privacy Checkbox Row ── */
.terms-consent-row {
  margin: 14px 0 6px;
  padding: 0 4px;
}

.terms-checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}

.terms-checkbox-input {
  margin-top: 3px;
  width: 17px;
  height: 17px;
  accent-color: #0A5C38;
  cursor: pointer;
  flex-shrink: 0;
  border-radius: 4px;
}

.terms-text {
  font-size: 12.5px;
  line-height: 1.45;
  color: #475569;
}

.legal-link-btn {
  background: transparent;
  border: none;
  padding: 0;
  color: #0A5C38;
  font-weight: 700;
  text-decoration: underline;
  cursor: pointer;
  font-size: 12.5px;
  display: inline;
}

/* ── In-App Legal Modal Sheet ── */
.legal-sheet-modal {
  --height: 80%;
  --border-radius: 24px 24px 0 0;
}

.legal-modal-shell {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: #FFFFFF;
}

.legal-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #E2E8F0;
}

.legal-tab-group {
  display: flex;
  gap: 8px;
  background: #F1F5F9;
  padding: 4px;
  border-radius: 10px;
}

.legal-tab-btn {
  background: transparent;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: #64748B;
  cursor: pointer;
  transition: all 0.15s ease;
}

.legal-tab-btn.active {
  background: #FFFFFF;
  color: #0A5C38;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.legal-close-btn {
  background: transparent;
  border: none;
  font-size: 26px;
  color: #64748B;
  cursor: pointer;
  padding: 0 4px;
  line-height: 1;
}

.legal-modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.legal-text-flow h3 {
  font-size: 18px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 4px;
}

.legal-date {
  font-size: 12px;
  color: #64748B;
  margin: 0 0 16px;
}

.legal-text-flow h4 {
  font-size: 14px;
  font-weight: 700;
  color: #1E293B;
  margin: 16px 0 6px;
}

.legal-text-flow p {
  font-size: 13px;
  line-height: 1.55;
  color: #475569;
  margin: 0 0 10px;
}

.legal-modal-footer {
  padding: 16px 20px;
  border-top: 1px solid #E2E8F0;
  background: #F8FAFC;
}

.legal-agree-action-btn {
  width: 100%;
  height: 48px;
  border-radius: 12px;
  background: #0A5C38;
  color: #FFFFFF;
  border: none;
  font-size: 15px;
  font-weight: 700;
  cursor: pointer;
}

</style>
