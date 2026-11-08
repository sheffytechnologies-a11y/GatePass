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

            <!-- Gradient: 0 Opacity at top, fading to background color at bottom for text to show -->
            <div class="hero-stage-gradient"></div>
            <div class="frame-glass-reflection"></div>
          </div>

          <!-- Bottom Text Content (Over Gradient - Pure, Uncluttered Headline + Subtle Subhead) -->
          <div class="hero-text-block">
            <!-- Moment Headline -->
            <transition name="moment-headline" mode="out-in" appear>
              <h2 :key="'h-' + activeMoment.id" class="hero-headline">
                {{ activeMoment.headline }}
              </h2>
            </transition>

            <!-- Moment Subtle Subheader (Staggered) -->
            <transition name="moment-subhead" mode="out-in" appear>
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
              <h1 class="form-title">Create Account</h1>
              <p class="form-subtitle">One portal to onboard residents, clear visitors at the gate, and keep every entry on record.</p>
            </div>

          <!-- EXISTING ACCOUNT FOUND MODAL (SOLID BACKGROUND & REFINED UX) -->
          <teleport to="body">
            <transition name="modal-fade">
              <div v-if="emailTaken" class="modal-backdrop-overlay" @click.self="dismissEmailTakenModal">
                <div class="existing-account-modal-card" role="dialog" aria-modal="true" aria-labelledby="modal-existing-title">
                  
                  <!-- Close Button -->
                  <button type="button" class="modal-close-icon-btn" @click="dismissEmailTakenModal" aria-label="Close modal">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                  </button>

                  <!-- Top Brand / Icon Badge -->
                  <div class="modal-brand-row">
                    <div class="modal-icon-bubble">
                      <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0A5C38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="16" height="20" x="4" y="2" rx="2" ry="2"/>
                        <path d="M9 22v-4h6v4"/>
                        <path d="M8 6h.01"/>
                        <path d="M16 6h.01"/>
                        <path d="M12 6h.01"/>
                        <path d="M12 10h.01"/>
                        <path d="M12 14h.01"/>
                        <path d="M16 10h.01"/>
                        <path d="M16 14h.01"/>
                        <path d="M8 10h.01"/>
                        <path d="M8 14h.01"/>
                      </svg>
                    </div>
                    <div class="modal-header-titles">
                      <span class="modal-badge-tag">EXISTING ACCOUNT FOUND</span>
                      <h3 id="modal-existing-title" class="modal-main-title">Welcome back, {{ name || 'Estate Manager' }}!</h3>
                    </div>
                  </div>

                  <div class="modal-desc-text">
                    An account for <strong class="email-highlight">{{ email }}</strong> already exists on CheckPass.
                  </div>

                  <!-- Value Callout -->
                  <div class="multi-estate-notice-box">
                    <div class="notice-icon">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0A5C38" stroke-width="2.2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                    <div class="notice-text">
                      <strong>Multi-Estate Portfolio:</strong> In CheckPass, you don't need separate emails for each estate! You can manage multiple communities from this single login.
                    </div>
                  </div>

                  <!-- Error Alert inside Modal if password incorrect -->
                  <transition name="alert-slide">
                    <div v-if="existingModalError" class="auth-alert modal-inner-alert">
                      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                      </svg>
                      <span>{{ existingModalError }}</span>
                    </div>
                  </transition>

                  <!-- Primary Action: Enter password to attach new estate -->
                  <div class="modal-action-card">
                    <label class="modal-input-label" for="existing-acc-pwd">
                      Enter your password to add this new estate:
                    </label>
                    <div class="input-wrapper">
                      <span class="input-icon">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                      </span>
                      <input
                        id="existing-acc-pwd"
                        v-model="existingAccountPassword"
                        :type="showExistingPassword ? 'text' : 'password'"
                        class="text-input with-toggle"
                        placeholder="Enter your existing account password"
                        @keyup.enter="handleLinkWithModalPassword"
                      />
                      <button
                        type="button"
                        class="password-toggle"
                        @click="showExistingPassword = !showExistingPassword"
                        aria-label="Toggle password visibility"
                      >
                        <svg v-if="!showExistingPassword" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                      </button>
                    </div>

                    <button
                      type="button"
                      class="modal-submit-btn"
                      :disabled="loading || !existingAccountPassword"
                      @click="handleLinkWithModalPassword"
                    >
                      <BrandSpinner v-if="loading" :size="18" color="#ffffff" />
                      <span v-else class="btn-text">
                        Verify &amp; Add New Estate
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                      </span>
                    </button>
                  </div>

                  <!-- Secondary Options -->
                  <div class="modal-options-divider">
                    <span>or choose an alternative</span>
                  </div>

                  <div class="modal-footer-actions">
                    <router-link
                      :to="`/login?email=${encodeURIComponent(email)}`"
                      class="modal-secondary-btn"
                    >
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>
                      Log In to Existing Dashboard
                    </router-link>

                    <button
                      type="button"
                      class="modal-tertiary-btn"
                      @click="useDifferentEmail"
                    >
                      Use a Different Email Address
                    </button>
                  </div>

                </div>
              </div>
            </transition>
          </teleport>

          <!-- Error Alert -->
          <transition name="alert-slide">
            <div v-if="error && !emailTaken" class="auth-alert">
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
                <div class="country-select-wrapper" title="Select country dial code">
                  <span class="selected-dial-code">{{ countryCode }}</span>
                  <svg class="select-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m6 9 6 6 6-6"/>
                  </svg>
                  <select
                    id="country-code"
                    v-model="countryCode"
                    class="country-select-hidden"
                    aria-label="Country dial code"
                  >
                    <option v-for="c in countryCodes" :key="c.code" :value="c.code">
                      {{ c.code }} &nbsp; ({{ c.country }})
                    </option>
                  </select>
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
                <span>I agree to the <a href="#" @click.prevent="openLegal('terms')" onclick="window.openRegisterLegal && window.openRegisterLegal('terms'); return false;">Terms of Service</a>, <a href="#" @click.prevent="openLegal('privacy')" onclick="window.openRegisterLegal && window.openRegisterLegal('privacy'); return false;">Privacy Policy</a> &amp; <a href="#" @click.prevent="openLegal('agreement')" onclick="window.openRegisterLegal && window.openRegisterLegal('agreement'); return false;">Master Services Agreement (MSA &amp; DPA)</a></span>
              </label>
            </div>

            <!-- Submit Button (Primary Emerald) -->
            <button
              type="submit"
              class="submit-account-btn"
              :disabled="loading || !canSubmit"
            >
              <BrandSpinner v-if="loading" :size="18" color="#ffffff" />
              <span v-else class="btn-content">
                Create Account
              </span>
            </button>

          </form>

          <!-- Footer Switch to Login -->
          <div class="form-bottom-links">
            <span class="already-text">Already registered your community?</span>
            <router-link to="/login" class="signin-link">Log In</router-link>
          </div>

          <!-- NDPR Trust Footer -->
          <div class="form-trust-footer">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>Bank-grade 256-bit encryption &middot; NDPR &amp; ISO 27001 Compliant</span>
          </div>

        </div>
      </div>

    <!-- Legal Modal Dialog (Terms, Privacy & Master Services Agreement) -->
    <Teleport to="body">
      <div
        v-if="legalModalOpen"
        class="legal-modal-backdrop"
        @click.self="legalModalOpen = false"
        data-lenis-prevent
        data-lenis-prevent-wheel
        @wheel.stop
      >
        <div class="legal-modal-card" data-lenis-prevent data-lenis-prevent-wheel @wheel.stop>
          <div class="legal-modal-header">
            <div class="legal-tabs">
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
              <button
                type="button"
                class="legal-tab-btn"
                :class="{ active: legalTab === 'agreement' }"
                @click="legalTab = 'agreement'"
              >
                Master Services Agreement (MSA &amp; DPA)
              </button>
            </div>
            <button type="button" class="legal-close-btn" @click="legalModalOpen = false" aria-label="Close modal">&times;</button>
          </div>

          <div class="legal-modal-body" data-lenis-prevent data-lenis-prevent-wheel @wheel.stop>
            <!-- Terms Tab -->
            <div v-if="legalTab === 'terms'" class="legal-text-flow">
              <h3>CheckPass Terms of Service</h3>
              <p class="legal-date">Last Updated: October 2026 &middot; Sheffy Technologies Limited (RC 1928471, Nigeria)</p>
              
              <h4>1. Definitions &amp; Statutory Framework</h4>
              <p>These Terms of Service (&ldquo;Terms&rdquo;) constitute a legally binding agreement between Sheffy Technologies Limited (&ldquo;CheckPass&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo;) and the Subscribing Estate, Community Development Association (CDA), Property Board, Facility Management Company, or Resident (&ldquo;User&rdquo; or &ldquo;You&rdquo;). Under Section 24 of the Nigeria Data Protection Act (NDPA) 2023 and NDPC GAID 2025 Directive 15, the Estate Homeowners Association or Facility Board operates as the <strong>Data Controller</strong>, while CheckPass acts as the technical <strong>Data Processor</strong> providing perimeter digital pass verification, resident registries, and automated dues billing infrastructure.</p>

              <h4>2. Proptech Nature &amp; Estate Authority Governance</h4>
              <p>CheckPass is an independent digital proptech platform providing access software, verification tokens, and administrative consoles. CheckPass is NOT a licensed private security guard company, armed guard service, or law enforcement agency. Physical admittance into any gated residential community, corporate park, or commercial estate remains governed exclusively by the estate's internal bylaws and security guards stationed at the gate. CheckPass holds no authority or liability regarding physical barrier arms, bollards, or manual guard decisions.</p>

              <h4>3. Account Registration &amp; Administrative Warranty</h4>
              <p>When registering an Estate or Community Account, the Administrator warrants and represents that they possess verified legal authority (via HOA Executive Resolution, Board Appointment, or formal Facility Management Service Contract) to onboard residents, configure perimeter rules, manage gate security officers, and administer payment collection. The Administrator covenants to keep security scanner PINs, multi-factor authentication tokens, and administrative credentials strictly confidential.</p>

              <h4>4. Pass Issuance &amp; Host Warranty</h4>
              <p>When a resident, landlord, or tenant generates an access pass, QR code, or alphanumeric verification code for a visitor, delivery courier, or contractor, the issuing host explicitly warrants that they have obtained the guest's consent to log their full name, telephone number, vehicle license plate, and entry timestamp for perimeter clearance in compliance with the NDPA 2023.</p>

              <h4>5. Offline Gate Validation &amp; Redundancy</h4>
              <p>CheckPass incorporates high-availability offline cryptographic pass verification (HMAC-SHA256). During local cellular network downtime or power outages, guard mobile terminals can validate pre-issued passes locally without internet connectivity. Once internet connectivity restores, gate records automatically synchronize to the estate cloud ledger without data loss.</p>

              <h4>6. Move-Out Clearance &amp; Anti-Theft Protocols</h4>
              <p>To safeguard estate property and prevent tenant asset theft, CheckPass provides digital Move-Out Manifest passes. Security personnel must inspect physical goods against digital manifests approved by the estate executive or landlord before authorizing outbound vehicle gate passage.</p>

              <h4>7. Financial Levies &amp; Direct Bank Settlement</h4>
              <p>CheckPass enables digital collection of estate dues, security levies, utility tariffs, and development levies. All digital card, USSD, and bank transfer payments are processed directly via Central Bank of Nigeria (CBN) licensed payment switches (Paystack Payments Limited, PCI-DSS Level 1 certified). CheckPass NEVER holds, escrows, or commingles resident funds; 100% of collected dues settle directly into the subscribing property's commercial bank account.</p>

              <h4>8. Emergency SOS Alert Disclaimer</h4>
              <p>The 1-Tap Emergency SOS feature is a digital information relay intended to notify estate security officers and administrative executives. CheckPass does not provide armed emergency response, medical dispatch, or fire suppression services. In the event of an active life-threatening emergency, residents must contact Nigerian emergency services immediately (112 or 199).</p>

              <h4>9. Domestic Staff &amp; Artisan Directory</h4>
              <p>CheckPass provides a directory module allowing estates to index vetted domestic artisans, plumbers, and technicians. CheckPass does not employ, insure, or guarantee the craftsmanship of listed artisans. The estate administration and individual residents retain sole responsibility for supervising artisan work inside private residences.</p>

              <h4>10. Prohibited Conduct &amp; Platform Integrity</h4>
              <p>Users shall not: (a) reverse-engineer, decompile, or tamper with the CheckPass mobile or web applications; (b) share guard terminal login credentials across multiple unauthorized individuals; (c) commercially harvest or export visitor records without lawful controller authorization; or (d) input fraudulent vehicle plates or fictitious identities.</p>

              <h4>11. Limitation of Liability &amp; Disclaimers</h4>
              <p>To the maximum extent permitted under Nigerian law, CheckPass and Sheffy Technologies Limited disclaim all liability for physical theft, unauthorized trespass, civil unrest, or property damage occurring within the estate. CheckPass's cumulative monetary liability shall not exceed the subscription fees received from the subscribing estate in the preceding three (3) calendar months.</p>

              <h4>12. Governing Law &amp; Dispute Resolution</h4>
              <p>These Terms are governed by and construed under the laws of the Federal Republic of Nigeria. Any dispute arising under these Terms shall first be resolved through good-faith mediation. Failing settlement within 30 days, disputes shall be submitted to binding arbitration under the Arbitration and Mediation Act 2023 in Lagos State, Nigeria.</p>
            </div>

            <!-- Privacy Tab -->
            <div v-else-if="legalTab === 'privacy'" class="legal-text-flow">
              <h3>CheckPass Privacy Policy</h3>
              <p class="legal-date">Certified Compliant: Nigeria Data Protection Act (NDPA) 2023 &middot; NDPC GAID 2025 Directive 15</p>

              <h4>1. Preamble &amp; Statutory Authority</h4>
              <p>Sheffy Technologies Limited (&ldquo;CheckPass&rdquo;, &ldquo;we&rdquo;, &ldquo;us&rdquo;) is committed to the highest standards of data privacy and security. This Privacy Policy details how we collect, process, store, and protect personal data across the CheckPass Resident Mobile App, Gate Terminal App, and Administrator Dashboard in strict compliance with the <strong>Nigeria Data Protection Act (NDPA) 2023</strong> and the <strong>General Application and Implementation Directive (GAID) 2025</strong> overseen by the <strong>Nigeria Data Protection Commission (NDPC)</strong>.</p>

              <h4>2. Statutory Roles: Data Controller vs. Data Processor</h4>
              <p>Under Section 24 of the NDPA 2023 and GAID 2025 Directive 15:</p>
              <ul>
                <li><strong>The Subscribing Estate HOA / CDA / Facility Board:</strong> Operates as the statutory <strong>Data Controller</strong>. The Estate determines who resides on the premises, approves tenant rosters, establishes gate entry protocols, and configures financial dues structures.</li>
                <li><strong>CheckPass (Sheffy Technologies Limited):</strong> Operates strictly as the statutory <strong>Data Processor</strong>. We provide cloud infrastructure, cryptographic QR token generation, and multi-channel messaging (WhatsApp, SMS, Email) solely pursuant to the documented instructions of the Data Controller.</li>
              </ul>

              <h4>3. Lawful Bases for Processing</h4>
              <p>In accordance with Section 25 of the NDPA 2023, data is processed under the following lawful bases: (a) <strong>Contractual Necessity:</strong> To generate and validate access passes requested by residents; (b) <strong>Legitimate Interests:</strong> To maintain physical perimeter security, deter burglaries, and safeguard community assets; and (c) <strong>Legal &amp; Tax Obligations:</strong> To generate verifiable dues payment invoices and audit trails.</p>

              <h4>4. Categories of Data Collected</h4>
              <ul>
                <li><strong>Resident &amp; Property Information:</strong> Full name, telephone number, email address, residential unit address, occupancy type (landlord, tenant, household member), and vehicle registration numbers.</li>
                <li><strong>Visitor &amp; Courier Telemetry:</strong> Visitor name, mobile phone number, vehicle plate, host unit, entry timestamp, exit timestamp, and clearing guard badge identifier.</li>
                <li><strong>Payment &amp; Dues Verification:</strong> Invoicing amounts, payment dates, bank transaction reference codes, and uploaded payment teller receipts. (CheckPass NEVER collects or stores full debit/credit card numbers or CVVs).</li>
                <li><strong>Security &amp; Gate Verification:</strong> Terminal IP address, guard check-in logs, pass scan validation outcome (Granted / Expired / Denied), and device hardware ID.</li>
              </ul>

              <h4>5. Data Security &amp; Cryptographic Architecture</h4>
              <p>CheckPass enforces enterprise-grade data security protocols: (a) <strong>AES-256 bit encryption at rest</strong> for all databases, disk volumes, and backup archives; (b) <strong>TLS 1.3 encryption in transit</strong> across all API endpoints, mobile devices, and browser sessions; (c) Strict Role-Based Access Control (RBAC) preventing guards from viewing residential contact lists; and (d) Immutable, append-only security audit ledgers.</p>

              <h4>6. Sub-Processors &amp; Data Sovereignty</h4>
              <p>We work with vetted enterprise infrastructure providers: Amazon Web Services (AWS, ISO 27001 certified cloud hosting), Paystack Payments Limited (PCI-DSS Level 1 certified payment processing), and licensed Nigerian telecommunications gateways (Termii / Twilio for SMS and WhatsApp pass delivery). CheckPass strictly prohibits third-party advertising tracking, data reselling, or commercial data monetization.</p>

              <h4>7. Data Retention &amp; Automatic Purging Schedule</h4>
              <p>Visitor gate passage telemetry is retained for security auditing for a standard cycle of <strong>180 days</strong>, after which movement records are permanently deleted or anonymized. Financial dues ledgers are preserved for seven (7) years in compliance with statutory Nigerian corporate and tax record-keeping regulations.</p>

              <h4>8. Data Subject Rights under NDPA 2023</h4>
              <p>Under Sections 34-39 of the NDPA 2023, every data subject possesses the right to: (a) Request confirmation and access to personal data processed; (b) Request prompt rectification of inaccurate records; (c) Request erasure of obsolete visitor entries; (d) Request data portability in a structured CSV/JSON format; and (e) Lodge a complaint with the Nigeria Data Protection Commission (NDPC).</p>

              <h4>9. Data Protection Officer (DPO) Contact</h4>
              <p>For data privacy inquiries, Subject Access Requests (SAR), or compliance verifications, contact our Data Protection Officer at: <a href="mailto:dpo@checkpass.ng" style="color:#0A5C38;font-weight:700">dpo@checkpass.ng</a> or write to Sheffy Technologies Limited, Legal &amp; Privacy Dept, Lagos State, Nigeria.</p>
            </div>

            <!-- Master Services Agreement & DPA Tab -->
            <div v-else-if="legalTab === 'agreement'" class="legal-text-flow">
              <div style="background:#F0FDF4;border:1px solid #BBF7D0;padding:14px 18px;border-radius:12px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
                <div>
                  <strong style="color:#166534;font-size:13.5px">📄 Master Services Agreement &amp; Data Processing Addendum (MSA &amp; DPA)</strong>
                  <p style="font-size:12px;color:#15803D;margin:3px 0 0">Universal proptech contract for Estates, CDAs, Facilities &amp; Corporate Parks &middot; Form Ref: CP-MSA-2026-V4</p>
                </div>
                <button
                  type="button"
                  class="btn-msa-download"
                  @click="downloadMsaPdf"
                  style="background:#0A5C38;color:#FFFFFF;border:none;padding:8px 16px;border-radius:8px;font-size:12.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 2px 8px rgba(10,92,56,0.2)"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                  </svg>
                  <span>Download Official Agreement (PDF)</span>
                </button>
              </div>

              <h3>Master Services Agreement &amp; Data Processing Addendum</h3>
              <p class="legal-date">Executed Between Sheffy Technologies Limited (RC 1928471) &amp; The Subscribing Property Board / CDA</p>

              <h4>PART 1 &middot; COMMERCIAL TERMS &amp; CLOUD SLA</h4>
              <p><strong>1. Parties &amp; Recitals:</strong> This Master Services Agreement is entered into between <strong>Sheffy Technologies Limited</strong> (Service Provider, RC 1928471, Lagos, Nigeria) and the <strong>Subscribing Client</strong> (&ldquo;The Property / HOA / CDA&rdquo;), represented by its authorized executive upon account onboarding.</p>
              
              <p><strong>2. Scope of Cloud Services:</strong> CheckPass grants non-exclusive cloud software access to: (a) 4-Second QR Gate Pass Verification for security guards; (b) Multi-Channel Pass Dispatch (WhatsApp, SMS, and Email); (c) Automated Estate Dues Invoicing &amp; Direct Bank Settlement; (d) Anti-Theft Move-Out Manifests; (e) Verified Domestic Artisan Directory; and (f) 1-Tap Emergency SOS Gate Alert.</p>
              
              <p><strong>3. Cloud Availability SLA &amp; Offline Redundancy:</strong> CheckPass guarantees <strong>99.8% monthly platform uptime</strong>. In addition, guard mobile terminals include local cryptographic pass verification ensuring zero gate disruptions during cellular network outages.</p>
              
              <p><strong>4. Direct Dues Settlement:</strong> 100% of resident estate dues, service levies, and utility charges paid via card or direct bank transfer settle directly into the subscribing property's commercial bank account. CheckPass never commingles or custodians resident funds.</p>

              <p><strong>5. Zero Hardware Lock-In:</strong> Guard terminals operate on standard Android or iOS smartphones and tablets. The subscriber is never required to procure proprietary turnstile or scanning hardware.</p>

              <h4>PART 2 &middot; NDPA 2023 / GAID 2025 DATA PROCESSING ADDENDUM</h4>
              <p><strong>6. Statutory Roles:</strong> Under Section 24 of the NDP Act 2023 and NDPC GAID 2025 Directive 15, the Subscriber is the statutory Data Controller; CheckPass is strictly the Data Processor. CheckPass shall enforce AES-256 bit encryption at rest, TLS 1.3 in transit, and strictly never monetize, sell, or disclose resident data.</p>

              <p><strong>7. 30-Day Exit &amp; Data Portability:</strong> The Subscriber may terminate service with thirty (30) days notice without exit penalty or lock-in fee. Upon termination, CheckPass shall deliver an encrypted, complete CSV export of all resident archives and ledgers within fourteen (14) days.</p>

              <p><strong>8. Data Breach Notification:</strong> In the event of a confirmed security incident, CheckPass covenants to notify the Subscriber within seventy-two (72) hours in compliance with Section 40 NDPA 2023.</p>

              <h4>PART 3 &middot; SIGNATURE EXECUTION BLOCK</h4>
              <div style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:20px;border-top:1px solid #E2E8F0;padding-top:16px;background:#F8FAFC;padding:16px;border-radius:10px">
                <div>
                  <p style="font-size:12px;font-weight:700;color:#0F172A;margin-bottom:14px">FOR: SHEFFY TECHNOLOGIES LIMITED</p>
                  <div style="border-bottom:1px solid #475569;width:80%;height:20px"></div>
                  <p style="font-size:11px;color:#64748B;margin:4px 0 0">Authorized Executive Signatory &middot; RC 1928471</p>
                </div>
                <div>
                  <p style="font-size:12px;font-weight:700;color:#0F172A;margin-bottom:14px">FOR: SUBSCRIBING CLIENT / ESTATE BOARD</p>
                  <div style="border-bottom:1px solid #475569;width:80%;height:20px"></div>
                  <p style="font-size:11px;color:#64748B;margin:4px 0 0">Chairman / Secretary / Facility Manager</p>
                </div>
              </div>
            </div>
          </div>

          <div class="legal-modal-footer">
            <button
              type="button"
              class="legal-download-link-btn"
              @click="downloadMsaPdf"
              style="background:transparent;border:1px solid #CBD5E1;color:#0A5C38;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px"
            >
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
              </svg>
              <span>Download PDF Agreement</span>
            </button>
            <button type="button" class="legal-accept-btn" @click="acceptLegal">
              I Understand &amp; Agree
            </button>
          </div>
        </div>
      </div>
    </Teleport>

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
const legalModalOpen = ref(false)
const legalTab = ref<'terms' | 'privacy' | 'agreement'>('terms')

function openLegal(tab: 'terms' | 'privacy' | 'agreement') {
  legalTab.value = tab
  legalModalOpen.value = true
}

if (typeof window !== 'undefined') {
  ;(window as any).openRegisterLegal = openLegal;
  ;(window as any).downloadMsaPdf = downloadMsaPdf;
}

function downloadMsaPdf() {
  const link = document.createElement('a')
  link.href = '/docs/CheckPass_Master_Services_Agreement_and_DPA.pdf'
  link.download = 'CheckPass_Master_Services_Agreement_and_DPA.pdf'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

function acceptLegal() {
  agreeTerms.value = true
  legalModalOpen.value = false
}
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)
const error = ref('')
const emailTaken = ref(false)

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

const existingAccountPassword = ref('')
const showExistingPassword = ref(false)
const existingModalError = ref('')

function dismissEmailTakenModal() {
  emailTaken.value = false
  existingModalError.value = ''
}

function useDifferentEmail() {
  emailTaken.value = false
  existingModalError.value = ''
  email.value = ''
  setTimeout(() => {
    document.getElementById('admin-email')?.focus()
  }, 100)
}

async function handleLinkWithModalPassword() {
  if (!existingAccountPassword.value) {
    existingModalError.value = 'Please enter your account password to continue.'
    return
  }
  loading.value = true
  existingModalError.value = ''
  try {
    await auth.loginAdmin(email.value.trim(), existingAccountPassword.value)
    emailTaken.value = false
    router.push('/register/estate')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    existingModalError.value = err.response?.data?.message || 'Incorrect password for this account. Please try again or use another email.'
  } finally {
    loading.value = false
  }
}

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
    router.push('/register/estate')
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }
    const emailErr = err.response?.data?.errors?.email?.[0] || ''
    if (emailErr.toLowerCase().includes('already been taken')) {
      emailTaken.value = true
      existingAccountPassword.value = password.value
      existingModalError.value = ''
      error.value = ''
      return
    }
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
  height: 34px;
  width: auto;
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
  animation: heroEntranceFadeUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.hero-copy {
  font-size: clamp(16px, 1.2vw, 18.5px);
  line-height: 1.55;
  color: rgba(255, 255, 255, 0.92);
  margin-bottom: 28px;
  max-width: 540px;
  min-height: 56px;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
  animation: heroEntranceFadeUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.14s backwards;
}

@keyframes heroEntranceFadeUp {
  0% {
    opacity: 0;
    transform: translateY(14px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Moment Transitions - Staggered Subtle Entrance */
/* 1. Headline enters first */
.moment-headline-enter-active,
.moment-headline-appear-active {
  transition: opacity 0.45s cubic-bezier(0.16, 1, 0.3, 1), transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
  will-change: opacity, transform;
}

.moment-headline-leave-active {
  transition: opacity 0.22s ease-in, transform 0.22s ease-in;
  will-change: opacity, transform;
}

.moment-headline-enter-from,
.moment-headline-appear-from {
  opacity: 0;
  transform: translateY(10px);
}

.moment-headline-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* 2. Subhead enters with smooth subtle stagger delay */
.moment-subhead-enter-active,
.moment-subhead-appear-active {
  transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.14s, transform 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.14s;
  will-change: opacity, transform;
}

.moment-subhead-leave-active {
  transition: opacity 0.2s ease-in, transform 0.2s ease-in;
  will-change: opacity, transform;
}

.moment-subhead-enter-from,
.moment-subhead-appear-from {
  opacity: 0;
  transform: translateY(10px);
}

.moment-subhead-leave-to {
  opacity: 0;
  transform: translateY(-6px);
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
  overflow: hidden;
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
  z-index: 2;
}

.text-input {
  width: 100%;
  height: 44px;
  border: none;
  background: transparent;
  border-radius: 14px;
  padding: 0 14px 0 42px;
  font-size: 14px;
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
  border-radius: 14px !important;
  transition: background-color 5000s ease-in-out 0s;
}

.input-wrapper:not(:focus-within) .text-input:-webkit-autofill {
  -webkit-box-shadow: 0 0 0 1000px #F8FAFC inset !important;
  box-shadow: 0 0 0 1000px #F8FAFC inset !important;
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
  gap: 6px;
  height: 44px;
  min-width: 72px;
  background: #F1F5F9;
  border-right: 1.5px solid #E2E8F0;
  border-top-left-radius: 12px;
  border-bottom-left-radius: 12px;
  padding: 0 12px 0 14px;
  cursor: pointer;
  transition: background 0.15s ease, border-color 0.15s ease;
}

.country-select-wrapper:hover {
  background: #E2E8F0;
}

.selected-dial-code {
  font-size: 14px;
  font-weight: 700;
  color: #0F172A;
  letter-spacing: -0.01em;
  user-select: none;
  pointer-events: none;
}

.select-chevron {
  pointer-events: none;
  color: #64748B;
  flex-shrink: 0;
  transition: transform 0.15s ease;
}

.country-select-hidden {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
  z-index: 2;
  font-size: 16px;
}

.country-select-hidden option {
  font-weight: 500;
  color: #0F172A;
  background: #FFFFFF;
  padding: 8px 12px;
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

/* Legal Modal Dialog */
.legal-modal-backdrop {
  position: fixed !important;
  inset: 0 !important;
  z-index: 999999 !important;
  background: rgba(15, 23, 42, 0.72) !important;
  backdrop-filter: blur(8px) !important;
  -webkit-backdrop-filter: blur(8px) !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 16px !important;
}
.legal-modal-card {
  background: #ffffff !important;
  border-radius: 20px !important;
  width: 100% !important;
  max-width: 820px !important;
  height: min(88vh, 840px) !important;
  max-height: 88vh !important;
  min-height: 0 !important;
  display: flex !important;
  flex-direction: column !important;
  box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.35) !important;
  overflow: hidden !important;
  border: 1px solid #e2e8f0 !important;
  position: relative !important;
}
.legal-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #e2e8f0;
  background: #f8fafc;
  flex-wrap: wrap;
  gap: 10px;
}
.legal-tabs {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.legal-tab-btn {
  padding: 7px 14px;
  border-radius: 8px;
  border: 1px solid transparent;
  background: transparent;
  font-size: 13px;
  font-weight: 700;
  color: #64748b;
  cursor: pointer;
  transition: all 0.15s ease;
}
.legal-tab-btn.active {
  background: #ffffff;
  color: #0a5c38;
  border-color: #e2e8f0;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.legal-close-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: #e2e8f0;
  color: #475569;
  font-size: 18px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.legal-modal-body {
  padding: 24px 28px;
  overflow-y: auto !important;
  -webkit-overflow-scrolling: touch !important;
  flex: 1 1 0% !important;
  min-height: 0 !important;
  max-height: calc(88vh - 130px) !important;
  overscroll-behavior: contain !important;
  scrollbar-width: thin !important;
  scrollbar-color: #0A5C38 #E2E8F0 !important;
}

.legal-modal-body::-webkit-scrollbar {
  width: 8px;
}

.legal-modal-body::-webkit-scrollbar-track {
  background: #F1F5F9;
  border-radius: 4px;
}

.legal-modal-body::-webkit-scrollbar-thumb {
  background: #0A5C38;
  border-radius: 4px;
}

.legal-modal-body::-webkit-scrollbar-thumb:hover {
  background: #064026;
}
.legal-text-flow h3 {
  font-size: 19px;
  font-weight: 800;
  color: #0f172a;
  margin-bottom: 4px;
}
.legal-date {
  font-size: 12px;
  color: #0a5c38;
  font-weight: 700;
  margin-bottom: 18px;
}
.legal-text-flow h4 {
  font-size: 14.5px;
  font-weight: 800;
  color: #1e293b;
  margin-top: 16px;
  margin-bottom: 6px;
}
.legal-text-flow p {
  font-size: 13.5px;
  line-height: 1.6;
  color: #475569;
  margin-bottom: 12px;
}
.legal-modal-footer {
  padding: 16px 20px;
  border-top: 1px solid #e2e8f0;
  background: #f8fafc;
  display: flex;
  justify-content: flex-end;
}
.legal-accept-btn {
  padding: 12px 24px;
  border-radius: 12px;
  border: none;
  background: #0a5c38;
  color: #ffffff;
  font-size: 14.5px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(10, 92, 56, 0.2);
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

</style>

/* ── EXISTING ACCOUNT FOUND MODAL ── */
.modal-backdrop-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(15, 23, 42, 0.68);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.existing-account-modal-card {
  position: relative;
  background: #FFFFFF;
  border-radius: 22px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.28), 0 0 0 1px rgba(15, 23, 42, 0.05);
  border: 1px solid #E2E8F0;
  padding: 32px;
  display: flex;
  flex-direction: column;
  animation: modalEnter 0.22s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  box-sizing: border-box;
}

@keyframes modalEnter {
  from {
    opacity: 0;
    transform: translateY(16px) scale(0.97);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.modal-close-icon-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  width: 34px;
  height: 34px;
  border-radius: 10px;
  border: 1px solid #E2E8F0;
  background: #F8FAFC;
  color: #64748B;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
}

.modal-close-icon-btn:hover {
  background: #E2E8F0;
  color: #0F172A;
}

.modal-brand-row {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 14px;
}

.modal-icon-bubble {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: #E6F4EE;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid #C4E7D7;
}

.modal-header-titles {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.modal-badge-tag {
  display: inline-block;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.06em;
  color: #0A5C38;
  text-transform: uppercase;
}

.modal-main-title {
  font-size: 20px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  letter-spacing: -0.02em;
}

.modal-desc-text {
  font-size: 14px;
  line-height: 1.55;
  color: #475569;
  margin-bottom: 16px;
}

.email-highlight {
  color: #0F172A;
  font-weight: 700;
}

.multi-estate-notice-box {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: #F8FAFC;
  border: 1.5px solid #E2E8F0;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 13px;
  line-height: 1.5;
  color: #334155;
  margin-bottom: 20px;
}

.notice-icon {
  color: #0A5C38;
  flex-shrink: 0;
  margin-top: 1px;
}

.modal-inner-alert {
  margin-bottom: 14px;
}

.modal-action-card {
  display: flex;
  flex-direction: column;
  gap: 10px;
  background: #F0FDF4;
  border: 1.5px solid #BBF7D0;
  border-radius: 14px;
  padding: 16px;
  margin-bottom: 18px;
}

.modal-input-label {
  font-size: 13px;
  font-weight: 700;
  color: #166534;
}

.modal-submit-btn {
  height: 44px;
  background: #0A5C38;
  color: #FFFFFF;
  border: none;
  border-radius: 12px;
  font-size: 14.5px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 14px rgba(10, 92, 56, 0.22);
  transition: all 0.15s ease;
}

.modal-submit-btn:hover:not(:disabled) {
  background: #084D2F;
  transform: translateY(-1px);
}

.modal-submit-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.modal-submit-btn .btn-text {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.modal-options-divider {
  position: relative;
  text-align: center;
  margin: 0 0 16px;
}

.modal-options-divider::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: #E2E8F0;
  z-index: 1;
}

.modal-options-divider span {
  position: relative;
  z-index: 2;
  background: #FFFFFF;
  padding: 0 12px;
  font-size: 12px;
  color: #94A3B8;
  text-transform: uppercase;
  font-weight: 600;
  letter-spacing: 0.04em;
}

.modal-footer-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.modal-secondary-btn {
  height: 42px;
  background: #FFFFFF;
  color: #0F172A;
  border: 1.5px solid #CBD5E1;
  border-radius: 12px;
  font-size: 13.5px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  text-decoration: none;
  transition: all 0.15s ease;
}

.modal-secondary-btn:hover {
  background: #F8FAFC;
  border-color: #94A3B8;
}

.modal-tertiary-btn {
  background: transparent;
  border: none;
  color: #64748B;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 6px;
  transition: color 0.15s ease;
}

.modal-tertiary-btn:hover {
  color: #0A5C38;
  text-decoration: underline;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}
