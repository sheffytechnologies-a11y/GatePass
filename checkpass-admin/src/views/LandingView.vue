<template>
  <div class="landing-wrapper">


<!-- NAVBAR -->
<nav class="navbar hero-nav" id="navbar">
  <div class="nav-inner">
    <div class="nav-logo" onclick="showPage('home')">
      <span class="nav-logo-lockup" aria-label="CheckPass">
        <img class="logo-dark" src="/assets/logo/for white background.png" alt="CheckPass">
        <img class="logo-light" src="/assets/logo/for dark background.png" alt="">
      </span>
    </div>
    <div class="nav-links">
      <a class="nav-link" href="#" onclick="showPage('how-it-works');return false">How It Works</a>
      <a class="nav-link" href="#" onclick="showPage('features');return false">Features</a>
      <a class="nav-link" href="#" onclick="showPage('pricing');return false">Pricing</a>
      <a class="nav-link" href="#" onclick="showPage('estates');return false">Estates</a>
      <a class="nav-link" href="#" onclick="showPage('contact');return false">Contact</a>
    </div>
    <div class="nav-cta" style="display:flex;align-items:center;gap:10px">
      <button class="btn btn-login" onclick="window.goToLogin()">Log In</button>
      <button class="btn btn-primary btn-sm" onclick="window.goToRegister()">Create Free Account</button>
    </div>
    <div class="nav-hamburger" id="hamburger" onclick="toggleMobileNav()">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<div class="nav-mobile" id="mobileNav">
  <a class="nav-link" href="#" onclick="showPage('how-it-works');closeMobileNav();return false">How It Works</a>
  <a class="nav-link" href="#" onclick="showPage('features');closeMobileNav();return false">Features</a>
  <a class="nav-link" href="#" onclick="showPage('pricing');closeMobileNav();return false">Pricing</a>
  <a class="nav-link" href="#" onclick="showPage('estates');closeMobileNav();return false">Estates</a>
  <a class="nav-link" href="#" onclick="showPage('contact');closeMobileNav();return false">Contact</a>
  <div style="display:flex;flex-direction:column;gap:8px;margin-top:10px">
    <button class="btn btn-ghost" style="width:100%;justify-content:center" onclick="window.goToLogin()">Log In to Portal</button>
    <button class="btn btn-primary" style="width:100%;justify-content:center" onclick="window.goToRegister();closeMobileNav()">Create Free Account</button>
  </div>
</div>

<!-- ==================== HOME ==================== -->
<div class="page active" id="page-home">

  <!-- HERO — PRESERVED -->
  <section class="hero">
    <div class="hero-media" aria-hidden="true">
      <video class="hero-video is-active" id="heroVideo1" muted playsinline preload="auto" autoplay>
        <source src="/assets/hero Banner/hero image 1.mp4" type="video/mp4">
      </video>
      <video class="hero-video" id="heroVideo2" muted playsinline preload="auto">
        <source src="/assets/hero Banner/hero image 2.mp4" type="video/mp4">
      </video>
    </div>
    <div class="hero-bg-gradient"></div>
    <div class="hero-noise"></div>
    <div class="hero-content">
      <div class="hero-inner">
        <h1 class="hero-animate" style="transition-delay:0ms">Smarter <span class="hero-accent">access</span><br>management</h1>
        <p class="hero-sub hero-animate" style="transition-delay:100ms">From resident-issued invites to secure verification,<br>CheckPass gives you full control over visitor access and movement.</p>
        <div class="hero-actions">
          <button class="btn btn-primary" onclick="window.goToRegister()">
            Create Free Account
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <button class="btn btn-outline" onclick="openDemoModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-right:6px"><path d="M8 5v14l11-7z"/></svg>
            Watch Demo
          </button>
        </div>
        <div class="hero-trust">
          <div class="hero-trust-item">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 3L6 12L3 8" stroke="#0A5B38" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Custom Tailored for Your Estate
          </div>
          <div class="hero-trust-item">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 3L6 12L3 8" stroke="#0A5B38" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Dedicated Guard &amp; Staff Training
          </div>
          <div class="hero-trust-item">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 3L6 12L3 8" stroke="#0A5B38" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            1–2 Week Managed Deployment
          </div>
        </div>
      </div>
    </div>
    <div class="hero-scroll-hint">
      <span>Scroll</span>
      <svg width="16" height="20" viewBox="0 0 16 20" fill="none"><rect x="5" y="1" width="6" height="10" rx="3" stroke="currentColor" stroke-width="1.5"/><path d="M8 14v4M6 16l2 2 2-2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
    </div>
  </section>

  <!-- STATS STRIP -->
  <div class="stats-strip">
    <div class="container">
      <p class="stats-eyebrow">Live data from active deployment</p>
      <div class="stats-inner">
        <div class="stat-item"><div class="stat-num" data-count="1">0</div><div class="stat-label">Property onboarded</div></div>
        <div class="stat-item"><div class="stat-num" data-count="120+">0+</div><div class="stat-label">Active residents</div></div>
        <div class="stat-item"><div class="stat-num" data-count="350+">0+</div><div class="stat-label">Passes created</div></div>
        <div class="stat-item"><div class="stat-num" data-count="500+">0+</div><div class="stat-label">Entries verified</div></div>
        <div class="stat-item"><div class="stat-num" data-count="80+">0+</div><div class="stat-label">Items declared</div></div>
      </div>
    </div>
  </div>

  <!-- FLAGSHIP INSPIRATION PHONE SHOWCASE & FLOATING CARDS -->
  <section class="flagship-showcase-section" id="flagshipSection">
    <div class="flagship-sticky-wrapper">
      <div class="container flagship-container">
        <!-- Minimal Stepper Progress Bar (No bulky tabs, purely shows active stage & progress) -->
        <div class="flagship-stepper-wrap">
          <div class="flagship-stepper">
            <button class="flagship-step-btn active" id="stepBtn0" onclick="goToFlagshipSlide(0)" aria-label="Step 1: Gate Verification">
              <span class="step-num">01</span>
              <span class="step-title">Gate Verification</span>
              <span class="step-line"><span class="step-line-fill" id="stepLine0"></span></span>
            </button>
            <button class="flagship-step-btn" id="stepBtn1" onclick="goToFlagshipSlide(1)" aria-label="Step 2: Visitor Pass">
              <span class="step-num">02</span>
              <span class="step-title">Visitor Pass &amp; QR</span>
              <span class="step-line"><span class="step-line-fill" id="stepLine1"></span></span>
            </button>
            <button class="flagship-step-btn" id="stepBtn2" onclick="goToFlagshipSlide(2)" aria-label="Step 3: Exit Declarations">
              <span class="step-num">03</span>
              <span class="step-title">Exit Item Declaration</span>
              <span class="step-line"><span class="step-line-fill" id="stepLine2"></span></span>
            </button>
            <button class="flagship-step-btn" id="stepBtn3" onclick="goToFlagshipSlide(3)" aria-label="Step 4: Estate Dues">
              <span class="step-num">04</span>
              <span class="step-title">Estate Dues &amp; Ledger</span>
              <span class="step-line"><span class="step-line-fill" id="stepLine3"></span></span>
            </button>
          </div>
        </div>

        <!-- Split Showcase Grid (Header & Sub-header only, No CTA | Right: Centered Phone Visual) -->
        <div class="flagship-split-grid">
          
          <!-- Left Column: Pure Header & Sub-header Copy Slides (No CTA) -->
          <div class="flagship-info-col">
            <div class="flagship-slides-container">
              <!-- Slide 0: Gate Verification -->
              <div class="flagship-slide-item active" data-slide="0">
                <span class="flagship-badge-pill">🛡️ Gate Verification</span>
                <h2 class="flagship-heading">Sub-4-Second Gate Clearance That Keeps Estate Traffic Moving</h2>
                <p class="flagship-desc">Equip your security officers with instant optical verification. CheckPass Gate Scanner verifies visitor identity, host authorization, and vehicle plates in under 4 seconds—powered by 100% offline edge caching that never fails during network cuts or power outages.</p>
              </div>

              <!-- Slide 1: Visitor Pass & QR -->
              <div class="flagship-slide-item" data-slide="1">
                <span class="flagship-badge-pill">🔑 Visitor Passes</span>
                <h2 class="flagship-heading">Instant Digital Passes via WhatsApp — Zero App Download</h2>
                <p class="flagship-desc">Guests never need to download an application or register an account. Residents generate visitor permits in 12 seconds, and CheckPass automatically delivers an encrypted, high-contrast QR pass directly via WhatsApp and SMS.</p>
              </div>

              <!-- Slide 2: Exit Item Declaration -->
              <div class="flagship-slide-item" data-slide="2">
                <span class="flagship-badge-pill">📦 Exit Declarations</span>
                <h2 class="flagship-heading">Tamper-Proof Exit Manifests That Prevent Asset Theft</h2>
                <p class="flagship-desc">Moving out furniture or contractor equipment? Residents attach serialized photo manifests directly in the app before the vehicle reaches the gate, eliminating disputes and preventing unauthorized property exit.</p>
              </div>

              <!-- Slide 3: Estate Dues & Ledger -->
              <div class="flagship-slide-item" data-slide="3">
                <span class="flagship-badge-pill">💳 Estate Dues</span>
                <h2 class="flagship-heading">Automate Service Charges &amp; Stop Chasing Defaulters</h2>
                <p class="flagship-desc">Track diesel levies, security dues, and waste management charges in real time. Residents upload payment receipts in-app with automated reconciliation, digital receipts, and gate clearance defaulting alerts.</p>
              </div>
            </div>
          </div>

          <!-- Right Column: Visual Stage with Organic Tinted Backdrop -->
          <div class="flagship-visual-col">
            <div class="flagship-organic-stage">
              <div class="phone-device-pro" id="flagshipPhoneDevice">
                <!-- Dynamic Island -->
                <div class="phone-dynamic-island">
                  <div class="island-camera-dot"></div>
                  <div class="island-sensor-dot"></div>
                </div>

                <!-- Status Bar -->
                <div class="phone-status-bar">
                  <span style="font-weight:700">9:41</span>
                  <div class="phone-status-icons">
                    <svg width="15" height="11" viewBox="0 0 17 11" fill="currentColor"><path d="M1 8h2v2H1V8zm4-3h2v5H5V5zm4-3h2v8H9V2zm4-2h2v10h-2V0z"/></svg>
                    <svg width="14" height="11" viewBox="0 0 16 12" fill="currentColor"><path d="M8 2.5c2.8 0 5.3 1.1 7.1 3L16 4.6C13.9 2.3 11.1 1 8 1 4.9 1 2.1 2.3 0 4.6l.9.9c1.8-1.9 4.3-3 7.1-3zm0 4c1.7 0 3.3.7 4.4 1.9l.9-.9C12 6.1 10.1 5.3 8 5.3s-4 .8-5.3 2.2l.9.9C4.7 7.2 6.3 6.5 8 6.5zm0 4c.8 0 1.5.3 2.1.9l.9-.9C10.2 9.8 9.2 9.3 8 9.3s-2.2.5-3 1.2l.9.9c.6-.6 1.3-.9 2.1-.9z"/></svg>
                    <svg width="22" height="11" viewBox="0 0 24 12" fill="currentColor"><rect x="1" y="1" width="19" height="10" rx="3" stroke="currentColor" stroke-width="1.5" fill="none"/><rect x="3" y="3" width="14" height="6" rx="1.5" fill="currentColor"/><path d="M22 4.5v3a1.5 1.5 0 001.5-1.5 1.5 1.5 0 00-1.5-1.5z"/></svg>
                  </div>
                </div>

                <!-- Phone Screen (Stacked 4 Screens with Smooth Crossfade) -->
                <div class="phone-screen-pro">
                  <img class="phone-screen-img active" data-phone-slide="0" src="/assets/app-screens/mockup_phone_guard_approval.png" alt="Gate Verification">
                  <img class="phone-screen-img" data-phone-slide="1" src="/assets/app-screens/pass-details-screen.png" alt="Visitor Pass">
                  <img class="phone-screen-img" data-phone-slide="2" src="/assets/app-screens/item-declaration-screen.png" alt="Item Declaration">
                  <img class="phone-screen-img" data-phone-slide="3" src="/assets/ui screens/Resident Interface/Fees.png" alt="Estate Fees">
                  <div class="phone-bottom-fade"></div>
                  <div class="phone-home-indicator"></div>
                </div>

                <!-- Floating Pop-out Cards (Pre-rendered for seamless slide swap) -->
                <div class="phone-floating-cards" id="flagshipFloatingCards">
                  <!-- Group 0: Gate -->
                  <div class="phone-floating-cards-group active" data-pop-slide="0">
                    <div class="pop-card-mid">
                      <div class="pop-card-left">
                        <div class="pop-card-avatar" style="background:#E8F5EE;color:#0A5B38">🛡️</div>
                        <div>
                          <div class="pop-card-title">Main Gate Terminal 1</div>
                          <div class="pop-card-sub">Pass #CP-8842 &bull; 3.2s Verification</div>
                        </div>
                      </div>
                      <div class="pop-card-right">
                        <div class="pop-badge-status">Verified Entry</div>
                        <div class="pop-micro-bar">
                          <div class="pop-micro-fill" style="width:100%;background:linear-gradient(90deg,#0A5B38 75%,#22C55E 100%)"></div>
                        </div>
                      </div>
                    </div>
                    <div class="pop-card-front">
                      <div class="pop-card-left">
                        <div class="pop-card-avatar" style="background:#F6F8F7">🚗</div>
                        <div>
                          <div class="pop-card-title">Visitor Access Cleared</div>
                          <div class="pop-card-sub">Engr. Farouk Al-Mansur &bull; Flat 12B</div>
                        </div>
                      </div>
                      <div class="pop-card-right">
                        <div class="pop-badge-status" style="color:#0A5B38">Access Granted</div>
                        <div class="pop-card-sub" style="color:#22C55E;font-weight:700">Approved by Resident</div>
                      </div>
                    </div>
                  </div>

                  <!-- Group 1: Pass -->
                  <div class="phone-floating-cards-group" data-pop-slide="1">
                    <div class="pop-card-mid">
                      <div class="pop-card-left">
                        <div class="pop-card-avatar" style="background:#E8F5EE;color:#0A5B38">🔑</div>
                        <div>
                          <div class="pop-card-title">1-Day Digital Visitor Pass</div>
                          <div class="pop-card-sub">Valid Today until 11:59 PM</div>
                        </div>
                      </div>
                      <div class="pop-card-right">
                        <div class="pop-badge-status">Active Pass</div>
                        <div class="pop-micro-bar">
                          <div class="pop-micro-fill" style="width:100%;background:linear-gradient(90deg,#0A5B38 90%,#22C55E 100%)"></div>
                        </div>
                      </div>
                    </div>
                    <div class="pop-card-front">
                      <div class="pop-card-left">
                        <div class="pop-card-avatar" style="background:#F6F8F7">📲</div>
                        <div>
                          <div class="pop-card-title">Instant WhatsApp QR Invite</div>
                          <div class="pop-card-sub">Delivered to Guest &bull; Zero App Download</div>
                        </div>
                      </div>
                      <div class="pop-card-right">
                        <div class="pop-badge-status" style="color:#0A5B38">Pass #8842</div>
                        <div class="pop-card-sub" style="color:#22C55E;font-weight:700">Cryptographic QR</div>
                      </div>
                    </div>
                  </div>

                  <!-- Group 2: Item -->
                  <div class="phone-floating-cards-group" data-pop-slide="2">
                    <div class="pop-card-mid">
                      <div class="pop-card-left">
                        <div class="pop-card-avatar" style="background:#FFF9E6;color:#B45309">📸</div>
                        <div>
                          <div class="pop-card-title">Photo Item Declaration</div>
                          <div class="pop-card-sub">Serialized Equipment Registered</div>
                        </div>
                      </div>
                      <div class="pop-card-right">
                        <div class="pop-badge-status">Logged to Gate</div>
                        <div class="pop-micro-bar">
                          <div class="pop-micro-fill" style="width:100%;background:linear-gradient(90deg,#F4C84A 70%,#0A5B38 100%)"></div>
                        </div>
                      </div>
                    </div>
                    <div class="pop-card-front">
                      <div class="pop-card-left">
                        <div class="pop-card-avatar" style="background:#F6F8F7">📦</div>
                        <div>
                          <div class="pop-card-title">Exit Inspection Cleared</div>
                          <div class="pop-card-sub">2x LG 27" Displays &bull; Verified at Barrier</div>
                        </div>
                      </div>
                      <div class="pop-card-right">
                        <div class="pop-badge-status" style="color:#0A5B38">Anti-Theft Ok</div>
                        <div class="pop-card-sub" style="color:#22C55E;font-weight:700">Signed by Resident</div>
                      </div>
                    </div>
                  </div>

                  <!-- Group 3: Fees -->
                  <div class="phone-floating-cards-group" data-pop-slide="3">
                    <div class="pop-card-mid">
                      <div class="pop-card-left">
                        <div class="pop-card-avatar" style="background:#E8F5EE;color:#0A5B38">💳</div>
                        <div>
                          <div class="pop-card-title">Service Dues Reconciliation</div>
                          <div class="pop-card-sub">Security, Sanitation & Diesel Levy</div>
                        </div>
                      </div>
                      <div class="pop-card-right">
                        <div class="pop-badge-status">Receipt Verified</div>
                        <div class="pop-micro-bar">
                          <div class="pop-micro-fill" style="width:100%;background:linear-gradient(90deg,#22C55E 85%,#0A5B38 100%)"></div>
                        </div>
                      </div>
                    </div>
                    <div class="pop-card-front">
                      <div class="pop-card-left">
                        <div class="pop-card-avatar" style="background:#F6F8F7">🧾</div>
                        <div>
                          <div class="pop-card-title">Automated Digital Receipt</div>
                          <div class="pop-card-sub">Flat 12B &bull; 2026 Q3 Estate Dues</div>
                        </div>
                      </div>
                      <div class="pop-card-right">
                        <div class="pop-badge-status" style="color:#0A5B38">Paid in Full</div>
                        <div class="pop-card-sub" style="color:#22C55E;font-weight:700">Verified by Admin</div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CORE SYSTEM HEADER -->
  <section class="features-showcase-section" id="core-systems" style="padding-bottom:20px">
    <div class="container">
      <div class="features-showcase-header" data-reveal>
        <div class="features-pill-badge">✨ Key System Capabilities</div>
        <h2>Total Control Over Every Gate, Payment, and Resident Record</h2>
        <p>CheckPass replaces phone calls, missing logbooks, and WhatsApp payment disputes with one reliable system that estate managers, security guards, and residents use every day.</p>
      </div>
    </div>
  </section>

  <!-- CORE SYSTEM SPLIT -->
  <section class="split-section">
    <div class="container">
      <div class="split-inner">
        <div class="split-img-wrap" data-reveal="left">
          <img src="/assets/images/2.png" alt="Security guard verifying visitor" class="split-img">
        </div>
        <div class="split-content" data-reveal="right">
          <h2 class="display-md">Core System</h2>
          <div class="split-list">
            <div class="split-list-item">
              <strong>Controlled access</strong>
              <p>Every visitor is approved before arrival and verified on entry.</p>
            </div>
            <div class="split-list-item">
              <strong>Central management</strong>
              <p>Handle residents, security activity, and operations from one place.</p>
            </div>
            <div class="split-list-item">
              <strong>Verified records</strong>
              <p>Entries, exits, and actions are logged with time and status.</p>
            </div>
            <div class="split-list-item">
              <strong>Item declaration and tracking</strong>
              <p>Items moving in or out are declared, checked, and recorded.</p>
            </div>
          </div>
          <button class="btn btn-ghost" onclick="showPage('features')">
            Learn More
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- ADDITIONAL CAPABILITIES SPLIT -->
  <section class="split-section split-section-alt">
    <div class="container">
      <div class="split-inner sf-reverse">
        <div class="split-content" data-reveal="left">
          <h2 class="display-md">Additional capabilities</h2>
          <div class="split-list">
            <div class="split-list-item">
              <strong>Service charge management</strong>
              <p>Track payments and confirm status with clear records.</p>
            </div>
            <div class="split-list-item">
              <strong>Community marketplace</strong>
              <p>Manage trusted service providers within your property.</p>
            </div>
            <div class="split-list-item">
              <strong>Announcements</strong>
              <p>Send updates and keep residents informed from a central point.</p>
            </div>
          </div>
          <button class="btn btn-ghost" onclick="showPage('how-it-works')">
            See how it works
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
        <div class="split-img-wrap" data-reveal="right">
          <img src="/assets/images/3.png" alt="Resident using CheckPass mobile app" class="split-img">
        </div>
      </div>
    </div>
  </section>

  <!-- STAKEHOLDERS INTERACTIVE SHOWCASE (SITEASSIST MODEL - 3 SLIDES) -->
  <section class="features-showcase-section" id="stakeholder-showcase" style="padding-top:40px">
    <div class="container">
      <div class="features-showcase-header" data-reveal>
        <div class="features-pill-badge">Who Uses CheckPass</div>
        <h2>Built for the People Running and Living in Your Estate</h2>
        <p>From the estate manager balancing the books to the security guard at the gate and the resident expecting a guest.</p>
      </div>

      <!-- Cinematic Full-Bleed Photographic Card Container -->
      <div class="siteassist-card" data-reveal id="siteassistContainer">
        <!-- 3 Photographic Background Slides -->
        <div class="siteassist-bg-layer">
          <img src="/assets/stakeholders/manager.jpg" alt="Estate manager reviewing records on dashboard" class="siteassist-bg-slide active" id="saSlide-0">
          <img src="/assets/stakeholders/security.jpg" alt="Security guard scanning visitor pass" class="siteassist-bg-slide" id="saSlide-1">
          <img src="/assets/stakeholders/resident.jpg" alt="Resident generating visitor pass at home" class="siteassist-bg-slide" id="saSlide-2">
        </div>

        <!-- Cinematic Ambient Overlay -->
        <div class="siteassist-overlay"></div>

        <!-- Inner Content Grid -->
        <div class="siteassist-inner">
          <!-- Top Row: Kicker & Counter -->
          <div class="siteassist-top-bar">
            <div class="siteassist-kicker" id="saKicker">ESTATE MANAGERS &amp; EXCO</div>
            <div class="siteassist-counter" id="saCounter">01</div>
          </div>

          <!-- Lower Right Floating Content Card -->
          <div class="siteassist-content-wrap" id="saContentBox">
            <h3 class="siteassist-feature-title" id="saTitle">Run Your Estate Without Missing Records or Payment Disputes</h3>
            <p class="siteassist-feature-body" id="saBody">Create service charges and diesel levies in seconds, track paid versus defaulting units in real time, and export clean financial reports ready for your next AGM.</p>
          </div>

          <!-- Bottom Nav Strip with Horizontal Progress Loaders -->
          <div class="siteassist-tabs-nav">
            <button class="siteassist-tab-btn active" onclick="switchSiteAssistTab(0)">
              <span>For Estate Managers</span>
              <div class="siteassist-progress-track"><div class="siteassist-progress-bar" id="saFill-0"></div></div>
            </button>
            <button class="siteassist-tab-btn" onclick="switchSiteAssistTab(1)">
              <span>For Security Guards</span>
              <div class="siteassist-progress-track"><div class="siteassist-progress-bar" id="saFill-1"></div></div>
            </button>
            <button class="siteassist-tab-btn" onclick="switchSiteAssistTab(2)">
              <span>For Residents</span>
              <div class="siteassist-progress-track"><div class="siteassist-progress-bar" id="saFill-2"></div></div>
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>



  <!-- S8: TRUST — INFRASTRUCTURE SECTION (WITH LAPTOP MOCKUP) -->
  <section class="trust-section">
    <div class="container">
      <div class="trust-split-grid">
        <!-- Left: Text & Key Capabilities -->
        <div class="trust-header" data-reveal="left">
          <span class="badge badge-white label">Built to Be Relied On</span>
          <h2>Infrastructure for estates that cannot afford to operate on guesswork</h2>
          <p>CheckPass is engineered to run the access, payment, and accountability operations of real estates with zero downtime and complete audit trails.</p>

          <div class="trust-cards-stack">
            <div class="trust-mini-card">
              <div class="trust-mini-icon">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><path d="M8 1.5L2 4.5v4c0 3.5 2.5 6.2 6 7 3.5-.8 6-3.5 6-7v-4L8 1.5z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              </div>
              <div>
                <h4>Every action is permanently recorded</h4>
                <p>From visitor entry to payment confirmation, every event is timestamped and tied to a responsible user.</p>
              </div>
            </div>

            <div class="trust-mini-card">
              <div class="trust-mini-icon">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6.5" stroke="currentColor" stroke-width="1.5"/><path d="M5 8l2 2 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              </div>
              <div>
                <h4>Strict role-based boundaries</h4>
                <p>Residents, guards, and managers each have dedicated interfaces. No unauthorized data exposure.</p>
              </div>
            </div>

            <div class="trust-mini-card">
              <div class="trust-mini-icon">
                <svg width="18" height="18" viewBox="0 0 16 16" fill="none"><rect x="2" y="6" width="12" height="9" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M5 6V4.5a3 3 0 016 0V6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
              </div>
              <div>
                <h4>Configured specifically for your estate</h4>
                <p>Custom setup for your exact unit count, gate barriers, and management bylaws before going live.</p>
              </div>
            </div>
          </div>

          <div class="trust-tags">
            <span class="trust-tag">Gated Communities</span>
            <span class="trust-tag">Residential Estates</span>
            <span class="trust-tag">Blocks of Flats</span>
            <span class="trust-tag">Multi-Gate Estates</span>
            <span class="trust-tag">Mixed-Use Developments</span>
          </div>
        </div>

        <!-- Right: Sleek Operations Laptop Mockup -->
        <div class="trust-laptop-wrap" data-reveal="right">
          <div class="trust-laptop-glow"></div>
          <div class="laptop-frame">
            <!-- Screen & Bezel -->
            <div class="laptop-screen-bezel">
              <div class="laptop-camera"></div>
              <div class="laptop-display">
                <!-- App Top Navigation Bar -->
                <div class="laptop-ui-nav">
                  <div class="laptop-ui-estate">
                    <span class="laptop-status-dot"></span>
                    <span>Royal Palm Estate &middot; Admin Portal</span>
                  </div>
                  <span class="laptop-ui-badge">4 Gates Online</span>
                </div>

                <!-- App Dashboard Body -->
                <div class="laptop-ui-body">
                  <!-- KPI Stat Cards -->
                  <div class="laptop-kpi-row">
                    <div class="laptop-kpi-card">
                      <div class="laptop-kpi-val" style="color:#4ADE80">&#8358;14.8M</div>
                      <div class="laptop-kpi-lbl">Q3 Dues (98.6%)</div>
                    </div>
                    <div class="laptop-kpi-card">
                      <div class="laptop-kpi-val" style="color:#38BDF8">142</div>
                      <div class="laptop-kpi-lbl">Visitors Today</div>
                    </div>
                    <div class="laptop-kpi-card">
                      <div class="laptop-kpi-val" style="color:#FBBF24">0</div>
                      <div class="laptop-kpi-lbl">Unresolved SOS</div>
                    </div>
                  </div>

                  <!-- Live Gate Traffic Table -->
                  <div class="laptop-table-wrap">
                    <div class="laptop-table-hdr">
                      <span>Live Gate Audit Feed</span>
                      <span style="color:#22C55E">Auto-Syncing</span>
                    </div>

                    <div class="laptop-table-row">
                      <div class="laptop-row-meta">
                        <span class="laptop-row-time">14:32</span>
                        <strong>Main Gate</strong>
                        <span>&middot; Unit 12B (Sarah O.)</span>
                      </div>
                      <span class="laptop-tag-green">Pass Verified</span>
                    </div>

                    <div class="laptop-table-row">
                      <div class="laptop-row-meta">
                        <span class="laptop-row-time">14:26</span>
                        <strong>Gate 2 Exit</strong>
                        <span>&middot; Unit 04A (Generator)</span>
                      </div>
                      <span class="laptop-tag-amber">Photo Cleared</span>
                    </div>

                    <div class="laptop-table-row">
                      <div class="laptop-row-meta">
                        <span class="laptop-row-time">14:18</span>
                        <strong>West Gate</strong>
                        <span>&middot; Unit 28C (Contractor)</span>
                      </div>
                      <span class="laptop-tag-green">Pass Verified</span>
                    </div>

                    <div class="laptop-table-row">
                      <div class="laptop-row-meta">
                        <span class="laptop-row-time">14:05</span>
                        <strong>Main Gate</strong>
                        <span>&middot; Unit 09A (Resident)</span>
                      </div>
                      <span class="laptop-tag-green">Tag In</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Laptop Base & Hinge -->
            <div class="laptop-base-top">
              <div class="laptop-notch"></div>
            </div>
            <div class="laptop-base-bottom"></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- S9: TESTIMONIALS -->
  <section class="testimonials">
    <div class="container">
      <div class="section-header centered" data-reveal>
        <span class="badge badge-green label">What Managers Say</span>
        <h2 class="display-md" style="margin-top:12px">Estates that made the switch</h2>
      </div>
      <div class="testi-grid">
        <div class="testi-card" data-reveal data-delay="1">
          <div class="testi-stars"><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span></div>
          <div class="testi-quote">&ldquo;</div>
          <p class="testi-text">We used to spend every Monday chasing payment confirmations. Now the dashboard shows everything. I haven't made a single "have you paid?" call in two months.</p>
          <div class="testi-author"><div class="testi-avatar">AM</div><div><div class="testi-name">Adaeze M.</div><div class="testi-role">Estate Manager &middot; Lekki Phase 1</div></div></div>
        </div>
        <div class="testi-card" data-reveal data-delay="2">
          <div class="testi-stars"><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span></div>
          <div class="testi-quote">&ldquo;</div>
          <p class="testi-text">Gate disputes dropped to nearly zero. Every entry is logged. When someone says a visitor wasn't approved, we check the record. It's that simple.</p>
          <div class="testi-author"><div class="testi-avatar">KO</div><div><div class="testi-name">Kunle O.</div><div class="testi-role">Chairman &middot; Magodo Residents Assoc.</div></div></div>
        </div>
        <div class="testi-card" data-reveal data-delay="3">
          <div class="testi-stars"><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span><span>&#9733;</span></div>
          <div class="testi-quote">&ldquo;</div>
          <p class="testi-text">Setup was done for us in two days. The CheckPass team configured everything. We just showed up and started using it. Our security team adapted faster than expected.</p>
          <div class="testi-author"><div class="testi-avatar">FI</div><div><div class="testi-name">Fatima I.</div><div class="testi-role">Facility Manager &middot; Abuja Estate</div></div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- S10: FAQ -->
  <section class="faq">
    <div class="container">
      <div class="section-header centered" data-reveal>
        <span class="badge badge-green label">Common Questions</span>
        <h2 class="display-md" style="margin-top:12px">Before you ask</h2>
      </div>
      <div class="faq-list">
        <div class="faq-item" data-reveal data-delay="1">
          <div class="faq-q" onclick="toggleFaq(this)"><h4>"Will our security guards be able to use this?"</h4><div class="faq-arrow"><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 5l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div></div>
          <div class="faq-a"><div class="faq-a-inner">Yes. CheckPass is designed specifically for guards who need fast, simple actions. Scanning a QR code and confirming a visitor takes under five seconds — no training beyond a brief walkthrough.</div></div>
        </div>
        <div class="faq-item" data-reveal data-delay="2">
          <div class="faq-q" onclick="toggleFaq(this)"><h4>"What if residents don't cooperate at first?"</h4><div class="faq-arrow"><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 5l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div></div>
          <div class="faq-a"><div class="faq-a-inner">Once it's enforced at the gate — no pass, no entry — adoption becomes natural within a few days. We help you structure the rollout so residents understand the system before it goes live.</div></div>
        </div>
        <div class="faq-item" data-reveal data-delay="3">
          <div class="faq-q" onclick="toggleFaq(this)"><h4>"Do we need special devices or hardware?"</h4><div class="faq-arrow"><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 5l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div></div>
          <div class="faq-a"><div class="faq-a-inner">No. CheckPass runs on existing smartphones. Residents, security, and managers each use the relevant app on their own phones — no scanners, tablets, or additional hardware needed.</div></div>
        </div>
        <div class="faq-item" data-reveal data-delay="4">
          <div class="faq-q" onclick="toggleFaq(this)"><h4>"How long does setup take?"</h4><div class="faq-arrow"><svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 5l5 5 5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></div></div>
          <div class="faq-a"><div class="faq-a-inner">Most estates go live within two days. We handle the full configuration — units, residents, security access, and payment structures — and walk your team through everything before launch.</div></div>
        </div>
      </div>
    </div>
  </section>

  <!-- S11: FINAL CTA -->
  <section class="final-cta">
    <div class="container">
      <div class="final-cta-inner" data-reveal>
        <span class="badge badge-white label" style="margin-bottom:20px;display:inline-flex">Free Account &middot; Instant Setup</span>
        <h2>Set up your estate in minutes.<br>Start 100% free.</h2>
        <p>Create your estate account, invite your team, and test QR visitor passes with zero hardware.</p>
        <div class="btn-row">
          <button class="btn btn-gold btn-lg" onclick="showPage('contact', 'free')">
            Create Free Account
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </div>
      </div>
    </div>
  </section>

</div><!-- /page-home -->


<!-- ==================== HOW IT WORKS PAGE ==================== -->
<div class="page" id="page-how-it-works">
  <div class="page-hero page-hero-green">
    <div class="container">
      <span class="badge badge-white label" style="margin-bottom:18px;display:inline-flex">End-to-End Operational Lifecycle</span>
      <h1>How CheckPass Works —<br>From Invite to Gate Verification</h1>
      <p>Connecting residents, security guards, and estate managers into one synchronized ecosystem.</p>
    </div>
  </div>

  <!-- SCROLLYTELLING JOURNEY WRAPPER -->
  <div class="how-scroll-journey">
    
    <!-- Section Introduction Header -->
    <div class="how-header" data-reveal>
      <span class="badge badge-green label" style="margin-bottom:14px;display:inline-flex">End-to-End Operational Lifecycle</span>
      <h2>From Resident Invitation to Live Gate Audit</h2>
      <p>Scroll through each operational phase below to see how the mobile app, gate security terminal, and admin command portal work in seamless real-time synchronization.</p>
    </div>

    <!-- Sticky Step Navigation Pill Bar -->
    <div class="how-step-nav-sticky">
      <div class="how-step-nav-inner">
        <button class="how-step-pill-btn active" id="pill-how-step-1" onclick="scrollToHowStep('how-step-1')">
          <span class="pill-num">1</span> Resident App &amp; Item Log
        </button>
        <button class="how-step-pill-btn" id="pill-how-step-2" onclick="scrollToHowStep('how-step-2')">
          <span class="pill-num">2</span> Visitor WhatsApp Pass
        </button>
        <button class="how-step-pill-btn" id="pill-how-step-3" onclick="scrollToHowStep('how-step-3')">
          <span class="pill-num">3</span> Sub-4s Gate Scan
        </button>
        <button class="how-step-pill-btn" id="pill-how-step-4" onclick="scrollToHowStep('how-step-4')">
          <span class="pill-num">4</span> Admin Command &amp; Dues
        </button>
        <button class="how-step-pill-btn" id="pill-how-step-5" onclick="scrollToHowStep('how-step-5')">
          <span class="pill-num">5</span> 2-Week Rollout Plan
        </button>
      </div>
    </div>

    <div class="container">

      <!-- STEP 01: RESIDENT MOBILE APP -->
      <section class="how-scroll-section" id="how-step-1">
        <div class="how-scroll-grid">
          <!-- Left: Narrative & Features -->
          <div class="how-scroll-content" data-reveal="left">
            <div class="how-step-kicker">
              <span>📱</span> STEP 01 &bull; RESIDENT INITIATION
            </div>
            <h3>Issue a Gate Pass in 12 Seconds &amp; Declare Move-Out Assets</h3>
            <p>
              No more handwritten paper slips, intercom calls to the guardhouse, or missed deliveries. From the CheckPass iOS or Android app, homeowners and verified tenants generate visitor passes in just a few taps.
            </p>
            <p>
              Moving household furniture, corporate equipment, or contractor materials? Residents attach tamper-evident item photos and serial numbers right in the app before the vehicle reaches the gate—preventing unauthorized removal of estate assets.
            </p>

            <div class="how-feature-checklist">
              <div class="how-feature-item">
                <div class="how-feature-icon">⏱️</div>
                <div class="how-feature-text">
                  <h5>12-Second Generation</h5>
                  <p>Choose guest category, enter name, set validity from 2 hours to recurring.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">📸</div>
                <div class="how-feature-text">
                  <h5>Move-Out Photo Manifest</h5>
                  <p>Declare high-value items with photo evidence before departure.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">🚗</div>
                <div class="how-feature-text">
                  <h5>Vehicle Plate Indexing</h5>
                  <p>Pre-registers vehicle plate for instant gate boom barrier clearance.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">🔔</div>
                <div class="how-feature-text">
                  <h5>Real-Time Arrival Push</h5>
                  <p>Instant push ping the exact second your guest is scanned at the gate.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Visual Stage (Pro iPhone + Item Declaration Pop-Out Card) -->
          <div class="how-scroll-visual" data-reveal="right">
            <div class="how-step1-visual-wrap">
              <!-- Main Resident App Phone -->
              <div class="how-step1-main-phone">
                <div class="phone-device-pro" style="width:260px;padding:7px;border-radius:38px">
                  <div class="phone-dynamic-island" style="width:82px;height:24px;top:8px">
                    <div class="island-camera-dot" style="width:7px;height:7px"></div>
                    <div class="island-sensor-dot" style="width:3px;height:3px"></div>
                  </div>
                  <div class="phone-screen-pro" style="border-radius:30px">
                    <img class="phone-img-layer" src="/assets/app-screens/create-pass-screen.png" alt="CheckPass Resident App Create Pass Screen">
                    <div class="phone-home-indicator" style="width:85px;bottom:6px"></div>
                  </div>
                </div>
              </div>

              <!-- Overlapping Companion Phone: Item Declaration Manifest -->
              <div class="how-step1-companion-phone">
                <div class="phone-device-pro" style="width:210px;padding:6px;border-radius:32px">
                  <div class="phone-dynamic-island" style="width:68px;height:20px;top:7px">
                    <div class="island-camera-dot" style="width:6px;height:6px"></div>
                    <div class="island-sensor-dot" style="width:2px;height:2px"></div>
                  </div>
                  <div class="phone-screen-pro" style="border-radius:24px">
                    <img class="phone-img-layer" src="/assets/app-screens/item-declaration-screen.png" alt="Item Declaration Screen">
                    <div class="phone-home-indicator" style="width:70px;bottom:5px"></div>
                  </div>
                </div>
                <div class="companion-phone-tag">
                  <span>📦</span> Declared Manifest
                </div>
              </div>

              <!-- Floating Status Badge -->
              <div class="how-visual-badge">
                <span class="badge-icon">🔑</span>
                <div>
                  <div class="badge-title">Pass Issued &bull; 11.4s</div>
                  <div class="badge-sub">Unit 14B &bull; 1 Vehicle Logged</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- STEP 02: VISITOR ZERO-DOWNLOAD PASS -->
      <section class="how-scroll-section bg-subtle" id="how-step-2">
        <div class="how-scroll-grid reverse">
          <!-- Left: Visual Stage (Pro iPhone with Real QR Pass + WhatsApp Notification) -->
          <div class="how-scroll-visual" data-reveal="left">
            <div class="how-step2-visual-wrap">
              <!-- Main Visitor Pass Phone -->
              <div class="phone-device-pro" style="width:260px;padding:7px;border-radius:38px">
                <div class="phone-dynamic-island" style="width:82px;height:24px;top:8px">
                  <div class="island-camera-dot" style="width:7px;height:7px"></div>
                  <div class="island-sensor-dot" style="width:3px;height:3px"></div>
                </div>
                <div class="phone-screen-pro" style="border-radius:30px">
                  <img class="phone-img-layer" src="/assets/app-screens/pass-details-screen.png" alt="CheckPass Visitor Digital QR Permit">
                  <div class="phone-home-indicator" style="width:85px;bottom:6px"></div>
                </div>
              </div>

              <!-- Floating WhatsApp Notification Toast -->
              <div class="how-whatsapp-toast">
                <div class="how-whatsapp-icon">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2z"/></svg>
                </div>
                <div class="how-whatsapp-text">
                  <strong>CheckPass Dispatch</strong>
                  Entry permit #CP-9824 received from Unit 14B. Tap to display pass at gate.
                </div>
              </div>

              <!-- Floating Status Badge -->
              <div class="how-visual-badge">
                <span class="badge-icon">📲</span>
                <div>
                  <div class="badge-title">Zero App Download</div>
                  <div class="badge-sub">Runs natively in mobile browser</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Narrative & Features -->
          <div class="how-scroll-content" data-reveal="right">
            <div class="how-step-kicker">
              <span>📲</span> STEP 02 &bull; ZERO-DOWNLOAD GUEST PASS
            </div>
            <h3>Instant WhatsApp Delivery — No App Download Required</h3>
            <p>
              Visitor access should never be friction-filled. Guests never have to install an app, create an account, or remember a password.
            </p>
            <p>
              The moment a resident confirms an invitation, CheckPass delivers an encrypted pass link directly via WhatsApp or SMS. With a single tap, visitors display an ultra-high-contrast dynamic QR pass in their standard browser, complete with driving directions to the host unit.
            </p>

            <div class="how-feature-checklist">
              <div class="how-feature-item">
                <div class="how-feature-icon">⚡</div>
                <div class="how-feature-text">
                  <h5>Instant Browser Pass</h5>
                  <p>Works in Safari, Chrome, or Samsung Internet with zero friction.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">💬</div>
                <div class="how-feature-text">
                  <h5>WhatsApp Integration</h5>
                  <p>Delivers personalized greeting, pass code, and estate guidelines.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">🔐</div>
                <div class="how-feature-text">
                  <h5>Anti-Fraud Security</h5>
                  <p>Dynamic time-synced QR tokens prevent screenshot reuse.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">🗺️</div>
                <div class="how-feature-text">
                  <h5>GPS Wayfinding</h5>
                  <p>Integrated turn-by-turn map directs guests straight to Unit 14B.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- STEP 03: GATE SECURITY SCANNER -->
      <section class="how-scroll-section" id="how-step-3">
        <div class="how-scroll-grid">
          <!-- Left: Narrative & Features -->
          <div class="how-scroll-content" data-reveal="left">
            <div class="how-step-kicker">
              <span>🛡️</span> STEP 03 &bull; SECURITY GATE TERMINAL
            </div>
            <h3>Gate Clearance in Under 4 Seconds — 100% Offline-Resilient</h3>
            <p>
              Long gate queues create frustration and security vulnerabilities. With the CheckPass Gate Terminal, security guards scan the visitor's QR code in less than 4 seconds using ruggedized handhelds or smartphones.
            </p>
            <p>
              Powered by local edge-replicated SQLite caching, verification completes instantaneously even during complete cellular network blackouts or estate generator switchovers. Guards immediately verify guest identity, vehicle plate, and declared move-out assets before triggering barriers.
            </p>

            <div class="how-feature-checklist">
              <div class="how-feature-item">
                <div class="how-feature-icon">⚡</div>
                <div class="how-feature-text">
                  <h5>Sub-4s Optical Scan</h5>
                  <p>Reads dimmed, scratched, or high-glare phone screens effortlessly.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">📶</div>
                <div class="how-feature-text">
                  <h5>100% Offline SQLite</h5>
                  <p>Zero gate stoppage during ISP outages or power switchovers.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">📦</div>
                <div class="how-feature-text">
                  <h5>Move-Out Manifest Check</h5>
                  <p>Visual verification of declared items before letting trucks exit.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">🚨</div>
                <div class="how-feature-text">
                  <h5>Silent Panic Duress Alert</h5>
                  <p>One-tap emergency trigger dispatches armed patrol to the gate.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Visual Stage (Dual Handheld Terminals: Scanner + Approval) -->
          <div class="how-scroll-visual" data-reveal="right">
            <div class="how-step3-visual-wrap">
              <!-- Handheld 1: Optical QR Scanner Reticle -->
              <div class="how-step3-phone-scanner">
                <div class="phone-device-pro" style="padding:6px;border-radius:34px">
                  <div class="phone-dynamic-island" style="width:72px;height:20px;top:7px">
                    <div class="island-camera-dot" style="width:6px;height:6px"></div>
                    <div class="island-sensor-dot" style="width:2px;height:2px"></div>
                  </div>
                  <div class="phone-screen-pro" style="border-radius:26px">
                    <img class="phone-img-layer" src="/assets/app-screens/mockup_phone_guard_scanner.png" alt="CheckPass Gate Scanner Terminal Viewfinder">
                    <div class="phone-home-indicator" style="width:75px;bottom:5px"></div>
                  </div>
                </div>
              </div>

              <!-- Handheld 2: Green Verified Access Granted -->
              <div class="how-step3-phone-approval">
                <div class="phone-device-pro" style="padding:6px;border-radius:34px;border-color:rgba(10,91,56,0.5)">
                  <div class="phone-dynamic-island" style="width:72px;height:20px;top:7px">
                    <div class="island-camera-dot" style="width:6px;height:6px"></div>
                    <div class="island-sensor-dot" style="width:2px;height:2px"></div>
                  </div>
                  <div class="phone-screen-pro" style="border-radius:26px">
                    <img class="phone-img-layer" src="/assets/app-screens/mockup_phone_guard_approval.png" alt="CheckPass Gate Guard Access Granted Modal">
                    <div class="phone-home-indicator" style="width:75px;bottom:5px"></div>
                  </div>
                </div>
              </div>

              <!-- Floating Security Status Badge -->
              <div class="how-visual-badge">
                <span class="badge-icon">🛡️</span>
                <div>
                  <div class="badge-title">Verified in 3.1 Seconds</div>
                  <div class="badge-sub">Guard Musa &bull; Main Gate Barrier 1</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- STEP 04: ESTATE ADMIN COMMAND & DUES -->
      <section class="how-scroll-section bg-subtle" id="how-step-4">
        <div class="how-scroll-grid reverse">
          <!-- Left: Visual Stage (MacBook Pro Laptop + Overlapping Audit Log Window) -->
          <div class="how-scroll-visual" data-reveal="left">
            <div class="how-step4-visual-wrap">
              <!-- MacBook Pro Chassis -->
              <div class="how-step4-laptop">
                <div class="laptop-chassis">
                  <div class="laptop-screen">
                    <div class="browser-tab-bar">
                      <div class="browser-dots"><i></i><i></i><i></i></div>
                      <div class="browser-address-bar">checkpass.estate/admin/dashboard</div>
                    </div>
                    <img src="/assets/app-screens/mockup_laptop_admin_dashboard.png" alt="CheckPass Estate Admin Command Dashboard">
                  </div>
                </div>
                <div class="laptop-base"></div>
              </div>

              <!-- Overlapping Pop-Out Window: Live Gate Audit Feed -->
              <div class="how-step4-pop-window">
                <div class="how-step4-pop-bar">
                  <div class="how-step4-pop-title">
                    <span style="color:#10B981">●</span> Live Gate Audit Log
                  </div>
                  <span style="font-size:10px;color:#64748B">SYNCED</span>
                </div>
                <img class="how-step4-pop-img" src="/assets/app-screens/mockup_laptop_gate_audit_feed.png" alt="CheckPass Live Gate Audit Feed">
              </div>

              <!-- Floating Metric Badge -->
              <div class="how-visual-badge">
                <span class="badge-icon">💻</span>
                <div>
                  <div class="badge-title">100% Immutable Audit Trail</div>
                  <div class="badge-sub">Zero Unverified Gate Entries</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: Narrative & Features -->
          <div class="how-scroll-content" data-reveal="right">
            <div class="how-step-kicker">
              <span>💻</span> STEP 04 &bull; MASTER COMMAND CENTER
            </div>
            <h3>Live Gate Audit Feeds &amp; Automated Service Dues Tracking</h3>
            <p>
              Complete transparency for Estate Facility Managers and Exco committees. Every single visitor entry and exit is logged permanently to an immutable, searchable digital ledger with exact timestamps and guard IDs.
            </p>
            <p>
              Synchronized financial ledgers track service charges, diesel levies, and security dues, alerting gate guards to chronic defaulters while keeping estate accounts solvent and ready for AGM audits.
            </p>

            <div class="how-feature-checklist">
              <div class="how-feature-item">
                <div class="how-feature-icon">📊</div>
                <div class="how-feature-text">
                  <h5>Real-Time Traffic Analytics</h5>
                  <p>Monitor peak arrival hours, active vehicles, and visitor volume.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">📜</div>
                <div class="how-feature-text">
                  <h5>Immutable Audit Ledger</h5>
                  <p>Permanent searchable records for incident investigation.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">💳</div>
                <div class="how-feature-text">
                  <h5>Automated Dues Tracking</h5>
                  <p>Auto-generate receipts, track diesel levies, and flag defaulters.</p>
                </div>
              </div>
              <div class="how-feature-item">
                <div class="how-feature-icon">👥</div>
                <div class="how-feature-text">
                  <h5>Unified Resident Directory</h5>
                  <p>Centralized registry for units, domestic staff, and vehicles.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- STEP 05: STRUCTURED 1-2 WEEK ONBOARDING ROADMAP -->
      <section class="how-scroll-section" id="how-step-5" style="border-bottom:none">
        <div class="onboarding-roadmap-wrap" data-reveal>
        <div class="roadmap-header">
          <span class="badge badge-gold label" style="margin-bottom:12px;display:inline-flex">Structured Deployment</span>
          <h3>How We Get Your Estate Live in 1–2 Weeks</h3>
          <p>A proven, hands-on onboarding methodology that configures your bylaws, sanitizes resident directories, and drills your gate security teams thoroughly.</p>
        </div>

        <div class="roadmap-timeline">
          <!-- Phase 1 -->
          <div class="timeline-phase">
            <div class="phase-badge">Phase 1 &bull; Days 1–3</div>
            <h4>Estate Bylaw &amp; Boundary Mapping</h4>
            <p>Executive alignment session with estate facility managers. Mapping estate bylaws, zone boundaries, multi-gate barrier protocols, and unit numbering.</p>
          </div>

          <!-- Phase 2 -->
          <div class="timeline-phase">
            <div class="phase-badge">Phase 2 &bull; Days 4–6</div>
            <h4>Resident Directory Ingestion</h4>
            <p>Data sanitization and bulk onboarding of homeowners, tenants, and domestic staff rosters with digital credentials and resident guidelines.</p>
          </div>

          <!-- Phase 3 -->
          <div class="timeline-phase">
            <div class="phase-badge">Phase 3 &bull; Days 7–9</div>
            <h4>Security Guard Physical Drills</h4>
            <p>Hands-on physical drills with gate security shift teams covering the sub-4s QR scanner, offline SQLite fallback codes, and outgoing item comparisons.</p>
          </div>

          <!-- Phase 4 -->
          <div class="timeline-phase">
            <div class="phase-badge">Phase 4 &bull; Days 10–11</div>
            <h4>Dues Ledgers &amp; Calibration</h4>
            <p>Configuration of custom estate dues categories (diesel levy, security, sanitation, maintenance) and test receipt reconciliation workflows.</p>
          </div>

          <!-- Phase 5 -->
          <div class="timeline-phase active">
            <div class="phase-badge" style="background:#0A5B38;color:#fff">Phase 5 &bull; Day 12+</div>
            <h4>Managed Gate Go-Live</h4>
            <p>Digital gate barrier activation with dedicated Sheffy field and remote operational support ensuring zero visitor traffic interruption.</p>
          </div>
        </div>
        </div>
      </section>

      <!-- Bottom Mini-CTA -->
      <div class="mini-cta" style="margin-top:54px" data-reveal>
        <h2>Ready to see CheckPass deployed in your estate?</h2>
        <p>We'll walk your facility committee through a live demonstration and prepare an executive onboarding proposal.</p>
        <button class="btn btn-gold btn-lg" onclick="showPage('contact')">Book a Setup Call &amp; Walkthrough</button>
      </div>

    </div><!-- /container -->
  </div><!-- /how-scroll-journey -->
</div><!-- /page-how-it-works -->


<!-- ==================== FEATURES PAGE ==================== -->
<div class="page" id="page-features">
  <div class="page-hero page-hero-dark">
    <div class="container">
      <span class="badge badge-white label" style="margin-bottom:18px;display:inline-flex">Features</span>
      <h1>Everything you need to run<br>your estate properly</h1>
      <p>Built for how real estates operate — not how software companies imagine they do.</p>
    </div>
  </div>
  <div class="container">
    <div class="features-full">
      <div class="feature-row" data-reveal>
        <div class="feature-text">
          <span class="badge badge-green label">Visitor Access</span>
          <h2>Approve visitors in seconds</h2>
          <p>Every visitor is pre-registered before arrival. Security scans a QR code and confirms in under five seconds. No calls. No waiting. No confusion about who's expected.</p>
          <div class="feature-perks">
            <div class="feature-perk"><div class="perk-dot"></div>Resident creates a pass from the app</div>
            <div class="feature-perk"><div class="perk-dot"></div>Visitor shows QR code at the gate</div>
            <div class="feature-perk"><div class="perk-dot"></div>Guard scans and confirms instantly</div>
            <div class="feature-perk"><div class="perk-dot"></div>Entry logged with timestamp automatically</div>
          </div>
        </div>
        <div class="feature-visual">
          <div class="feature-phone-wrap">
            <div class="feature-phone-pro">
              <div class="phone-dynamic-island" style="width:76px;height:21px;top:8px">
                <div class="island-camera-dot" style="width:6px;height:6px"></div>
                <div class="island-sensor-dot" style="width:2px;height:2px"></div>
              </div>
              <div class="feature-phone-screen">
                <img src="/assets/app-screens/pass-details-screen.png" alt="Visitor Pass Details">
                <div class="feature-phone-pill">
                  <span style="font-size:18px">🛡️</span>
                  <div>
                    <div style="font-weight:800;font-size:.82rem;color:var(--dark);line-height:1.2">Pass Verified &bull; Gate 1</div>
                    <div style="font-size:.72rem;color:var(--n600)">Sub-4s Instant Gate Entry</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="feature-row reverse" data-reveal>
        <div class="feature-text">
          <span class="badge badge-green label">Payments</span>
          <h2>Stop chasing service charges</h2>
          <p>Track payments, balances, and confirmations in one place. Residents upload proof directly in the app. You see who has paid, who hasn't, and the full history — no spreadsheets.</p>
          <div class="feature-perks">
            <div class="feature-perk"><div class="perk-dot"></div>Track all service charge balances</div>
            <div class="feature-perk"><div class="perk-dot"></div>Residents upload payment receipts in-app</div>
            <div class="feature-perk"><div class="perk-dot"></div>Manager approves and confirms with one tap</div>
            <div class="feature-perk"><div class="perk-dot"></div>Complete payment history always available</div>
          </div>
        </div>
        <div class="feature-visual">
          <div class="feature-phone-wrap">
            <div class="feature-phone-pro">
              <div class="phone-dynamic-island" style="width:76px;height:21px;top:8px">
                <div class="island-camera-dot" style="width:6px;height:6px"></div>
                <div class="island-sensor-dot" style="width:2px;height:2px"></div>
              </div>
              <div class="feature-phone-screen">
                <img src="/assets/ui screens/Resident Interface/Fees.png" alt="Service Charge and Estate Dues">
                <div class="feature-phone-pill">
                  <span style="font-size:18px">💳</span>
                  <div>
                    <div style="font-weight:800;font-size:.82rem;color:var(--dark);line-height:1.2">Levy Reconciled</div>
                    <div style="font-size:.72rem;color:var(--n600)">Digital Receipt Confirmed</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="feature-row" data-reveal>
        <div class="feature-text">
          <span class="badge badge-green label">Item Tracking</span>
          <h2>Avoid disputes and confusion</h2>
          <p>All items entering or leaving the estate are declared and recorded. When something is disputed, the record is there — clear, timestamped, and tied to the resident's account.</p>
          <div class="feature-perks">
            <div class="feature-perk"><div class="perk-dot"></div>Residents declare items before moving</div>
            <div class="feature-perk"><div class="perk-dot"></div>Security confirms against the declaration</div>
            <div class="feature-perk"><div class="perk-dot"></div>Full item log accessible to management</div>
            <div class="feature-perk"><div class="perk-dot"></div>Eliminates disputes with verifiable records</div>
          </div>
        </div>
        <div class="feature-visual">
          <div class="feature-phone-wrap">
            <div class="feature-phone-pro">
              <div class="phone-dynamic-island" style="width:76px;height:21px;top:8px">
                <div class="island-camera-dot" style="width:6px;height:6px"></div>
                <div class="island-sensor-dot" style="width:2px;height:2px"></div>
              </div>
              <div class="feature-phone-screen">
                <img src="/assets/app-screens/item-declaration-screen.png" alt="Item Declaration and Anti-Theft">
                <div class="feature-phone-pill">
                  <span style="font-size:18px">📦</span>
                  <div>
                    <div style="font-weight:800;font-size:.82rem;color:var(--dark);line-height:1.2">Exit Inspected</div>
                    <div style="font-size:.72rem;color:var(--n600)">2x Displays &bull; Verified by Guard</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="feature-row reverse" data-reveal>
        <div class="feature-text">
          <span class="badge badge-green label">Admin Dashboard</span>
          <h2>See everything clearly</h2>
          <p>One view shows every resident, visitor, payment, and activity across your estate. Filter, search, and export. Stay fully informed without chasing anyone.</p>
          <div class="feature-perks">
            <div class="feature-perk"><div class="perk-dot"></div>Live visitor entry and exit log</div>
            <div class="feature-perk"><div class="perk-dot"></div>Payment status for every unit</div>
            <div class="feature-perk"><div class="perk-dot"></div>Resident directory and contact details</div>
            <div class="feature-perk"><div class="perk-dot"></div>Exportable reports for committee meetings</div>
          </div>
        </div>
        <div class="feature-visual" style="background:#0F172A;padding:24px;border-radius:18px;display:flex;align-items:center;justify-content:center">
          <div style="width:100%;border-radius:10px;overflow:hidden;box-shadow:0 18px 40px rgba(0,0,0,.4);border:1px solid rgba(255,255,255,.1)">
            <img src="/assets/app-screens/mockup_laptop_admin_dashboard.png" alt="CheckPass Admin Dashboard" style="width:100%;display:block">
          </div>
        </div>
      </div>
    </div>
  </div>
  <section class="section-sm" style="background:var(--n50)">
    <div class="container-sm">
      <div class="mini-cta" data-reveal>
        <h2>Ready to see CheckPass in action?</h2>
        <p>Book a dedicated consultation and walkthrough tailored to your estate's gates and bylaws.</p>
        <button class="btn btn-gold btn-lg" onclick="showPage('contact')">Book an Estate Walkthrough</button>
      </div>
    </div>
  </section>
</div>


<!-- ==================== PRICING PAGE ==================== -->
<div class="page" id="page-pricing">
  <div class="page-hero page-hero-dark">
    <div class="container">
      <span class="badge badge-white label" style="margin-bottom:18px;display:inline-flex">Tailored Estate Plans</span>
      <h1>Custom Deployment For<br>Estates of Every Scale</h1>
      <p>Every estate has distinct requirements—from private boutique closes to multi-gate residential CDAs. Contact our team for a tailored proposal and quote.</p>
    </div>
  </div>

  <section class="pricing-section">
    <div class="container">
      <!-- 3 Tailored Estate Packages Grid (No Prices - Consultation Based) -->
      <div class="pricing-grid" style="grid-template-columns:repeat(3,1fr)">
        <!-- Boutique Estate Tier -->
        <div class="pricing-consult-card" data-reveal data-delay="1">
          <div class="pricing-capacity-badge">Up to 50 Units</div>
          <h3 style="font-size:1.55rem;font-weight:800;color:var(--dark);margin-bottom:8px">Boutique Estate &amp; Closes</h3>
          <p style="font-size:.86rem;color:var(--n600);line-height:1.5;margin-bottom:20px">Ideal for private closes, luxury residential blocks, and compact communities seeking organized visitor entry.</p>

          <div class="pricing-scope-list">
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span><strong>Up to 50 Units</strong> (Up to 150 resident accounts)</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Instant QR Visitor Passes (SMS &amp; WhatsApp)</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Dedicated Guard Scanner Web App (Sub-4s scan)</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Digital Service Charge &amp; Dues Ledger</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Standard Security Personnel Onboarding &amp; Guide</span>
            </div>
          </div>
          <button class="btn btn-ghost" style="width:100%;justify-content:center" onclick="showPage('contact', 'boutique')">Request Boutique Quote</button>
        </div>

        <!-- 50-300 Units Plan (Most Requested) -->
        <div class="pricing-consult-card featured" data-reveal data-delay="2">
          <div class="popular-tag">⭐ Most Requested</div>
          <div class="pricing-capacity-badge" style="background:#0A5B38;color:#fff">50 &ndash; 300 Units</div>
          <h3 style="font-size:1.55rem;font-weight:800;color:var(--dark);margin-bottom:8px">Gated Residential Communities</h3>
          <p style="font-size:.86rem;color:var(--n600);line-height:1.5;margin-bottom:20px">The complete operational system for established gated residential estates and CDAs with active gate traffic.</p>

          <div class="pricing-scope-list">
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span><strong>50 to 300 Units</strong> (Up to 900 resident profiles)</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Multi-Gate Synchronization &amp; Live Audit Feed</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Photo Item Exit Declaration &amp; Anti-Theft Checks</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Automated Dues Invoicing &amp; Digital Receipts</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Hands-on Security Shift Drills &amp; On-Site Training</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Dedicated Sheffy Account Manager &amp; VIP Support</span>
            </div>
          </div>
          <button class="btn btn-primary btn-lg" style="width:100%;justify-content:center" onclick="showPage('contact', 'residential')">Request Proposal &amp; Quote</button>
        </div>

        <!-- Master Planned Estate (300+ Units) -->
        <div class="pricing-consult-card" data-reveal data-delay="3">
          <div class="pricing-capacity-badge">300+ Units / Enterprise</div>
          <h3 style="font-size:1.55rem;font-weight:800;color:var(--dark);margin-bottom:8px">Master Planned Estates &amp; CDAs</h3>
          <p style="font-size:.86rem;color:var(--n600);line-height:1.5;margin-bottom:20px">Enterprise-grade architecture for mega-estates with multiple perimeter gates, internal CDA zones, and amenities.</p>

          <div class="pricing-scope-list">
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span><strong>300+ to 2,000+ Units</strong> (Custom capacity)</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Multi-Zone Sub-Gate Routing &amp; Zone Permissions</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Barrier Arm, Turnstile &amp; Hardware Integrations</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Executive Board &amp; AGM Financial Audit Dossiers</span>
            </div>
            <div class="pricing-scope-item">
              <svg width="18" height="18" viewBox="0 0 20 20" fill="none"><path d="M16.6 5L7.5 14.1 3.4 10" stroke="#0A5B38" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <span>Guaranteed Response SLA &amp; 24/7 Operations Hotline</span>
            </div>
          </div>
          <button class="btn btn-ghost" style="width:100%;justify-content:center" onclick="showPage('contact', 'enterprise')">Contact Enterprise Team</button>
        </div>
      </div>

      <!-- Enterprise Banner -->
      <div class="enterprise-banner" data-reveal>
        <div>
          <span class="badge badge-gold label" style="margin-bottom:12px;display:inline-flex">Bespoke Rollout</span>
          <h3>Need a Customized Estate Demonstration or Board Pitch?</h3>
          <p>We prepare official proposal letters, visual architectural guides, and presentation slides for your estate executive committee or AGM.</p>
        </div>
        <button class="btn btn-gold btn-lg" onclick="showPage('contact', 'enterprise')">Request Executive Proposal</button>
      </div>

      <!-- Interactive Deployment Scope Planner (Replaces Price Calculator) -->
      <div class="calculator-box" data-reveal>
        <div class="calculator-header">
          <span class="badge badge-green label" style="margin-bottom:12px;display:inline-flex">Scope Estimator</span>
          <h3>Estate Deployment &amp; Capacity Planner</h3>
          <p>Adjust the slider to your estate's unit count to calculate recommended gate terminals, resident capacity, and deployment timeline.</p>
        </div>
        <div class="calculator-grid">
          <div class="slider-container">
            <div class="slider-labels">
              <span style="font-weight:700;color:var(--dark)">Total Estate Units:</span>
              <span class="slider-val-display" id="sliderUnitCount">120 Units (up to 360 residents)</span>
            </div>
            <input type="range" class="unit-slider" id="calcRange" min="15" max="1000" step="5" value="120" oninput="updatePricingSlider(this.value)">
            <div style="display:flex;justify-content:space-between;font-size:.78rem;color:var(--n400)">
              <span>15 Units</span>
              <span>50 Units</span>
              <span>300 Units</span>
              <span>1,000+ Units</span>
            </div>
          </div>
          <div class="calc-result-card">
            <div class="calc-res-tier" id="calcTierBadge">Gated Residential Scope (120 Units)</div>
            <div class="calc-res-label">Recommended Deployment Scope</div>
            <div class="calc-res-amount" id="calcTotal" style="font-size:1.55rem;line-height:1.25">2 Gate Terminals + 1 Admin Portal</div>
            <div class="calc-res-sub" id="calcAnnualSub">Includes 1–2 weeks managed onboarding, bulk resident directory ingestion, and security personnel physical drills.</div>
            <button class="btn btn-gold btn-sm" id="calcActionBtn" style="width:100%;justify-content:center" onclick="showPage('contact', 'residential')">Request Custom Quote for 120 Units</button>
          </div>
        </div>
      </div>

      <div class="mini-cta" style="margin-top:40px" data-reveal>
        <h2>Need transparent pricing for your estate?</h2>
        <p>Speak with our estate operations team to get a detailed breakdown tailored specifically to your gates, guards, and resident capacity.</p>
        <button class="btn btn-gold btn-lg" onclick="showPage('contact')">Speak With Our Estate Team</button>
      </div>
    </div>
  </section>
</div>


<!-- ==================== ESTATES PAGE ==================== -->
<div class="page" id="page-estates">
  <div class="page-hero page-hero-dark">
    <div class="container">
      <span class="badge badge-white label" style="margin-bottom:18px;display:inline-flex">Who It's For</span>
      <h1>Built for real estates</h1>
      <p>CheckPass is configured to match how your estate actually operates — not a generic one-size-fits-all tool.</p>
    </div>
  </div>
  <section class="section" style="background:#fff">
    <div class="container">
      <div class="section-header centered" data-reveal>
        <h2 class="display-md">Your estate type, fully supported</h2>
        <p class="body-md" style="margin-top:14px">From small gated communities to large multi-gate estates — CheckPass handles the complexity without adding more of it.</p>
      </div>
      <div class="estates-types">
        <div class="estate-type" data-reveal data-delay="1"><div class="estate-icon">🏘️</div><div><h3>Residential Estates</h3><p>Gated communities with multiple units, shared facilities, and service charges. Full structure, one system.</p></div></div>
        <div class="estate-type" data-reveal data-delay="2"><div class="estate-icon">🏰</div><div><h3>Gated Communities</h3><p>Multiple households sharing security and governance. Full visibility for management, privacy for each home.</p></div></div>
        <div class="estate-type" data-reveal data-delay="1"><div class="estate-icon">🏢</div><div><h3>Blocks of Flats</h3><p>Dense multi-unit buildings where visitor management and payment tracking are most critical.</p></div></div>
        <div class="estate-type" data-reveal data-delay="2"><div class="estate-icon">🚧</div><div><h3>Multi-Gate Estates</h3><p>Large estates with more than one entry point. Each gate runs independently — management sees the full picture.</p></div></div>
      </div>
      <div class="mini-cta" style="margin-top:40px" data-reveal>
        <h2>Customised to your structure</h2>
        <p>Set up your estate account for free and test with up to 3 residents — or request our assisted setup package.</p>
        <button class="btn btn-gold btn-lg" onclick="window.goToRegister()">Create Free Estate Account</button>
      </div>
    </div>
  </section>
</div>


<!-- ==================== CONTACT PAGE ==================== -->
<div class="page" id="page-contact">
  <div class="page-hero page-hero-dark">
    <div class="container">
      <span class="badge badge-white label" style="margin-bottom:18px;display:inline-flex">Get Started Free</span>
      <h1>Set up your estate in 2 minutes</h1>
      <p>Create your estate account for free. Add up to 3 residents to test before full community rollout — or speak with our team for hands-on onboarding.</p>
    </div>
  </div>
  <div class="contact-page-bg">
    <div class="container">
      <!-- Direct Contact Channels -->
      <div class="contact-direct-grid" data-reveal>
        <div class="contact-direct-card">
          <div class="contact-icon-bubble">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
          </div>
          <div class="contact-direct-info">
            <h4>Email Support</h4>
            <p>Direct inquiries & onboarding</p>
            <a href="mailto:support@checkpass.ng">support@checkpass.ng</a><br>
            <a href="mailto:sales@checkpass.ng" style="font-size:.82rem;color:var(--n400)">sales@checkpass.ng</a>
          </div>
        </div>

        <div class="contact-direct-card">
          <div class="contact-icon-bubble">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
          </div>
          <div class="contact-direct-info">
            <h4>Phone & WhatsApp</h4>
            <p>Mon – Sat, 8am – 7pm</p>
            <a href="tel:+2348002432572">+234 (0) 800 CHECKPASS</a><br>
            <a href="https://wa.me/2348000000000" target="_blank" style="font-size:.82rem;color:#25D366;font-weight:700">Chat on WhatsApp &rarr;</a>
          </div>
        </div>

        <div class="contact-direct-card">
          <div class="contact-icon-bubble">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
          </div>
          <div class="contact-direct-info">
            <h4>Office Location</h4>
            <p>Lekki Phase 1, Lagos</p>
            <span style="font-size:.84rem;color:#374038;font-weight:600">Lagos &amp; Abuja Deployments</span>
          </div>
        </div>
      </div>

      <div class="contact-wrap">
        <div class="contact-info" data-reveal="left">
          <h2>What happens next</h2>
          <p>After registering your estate or sending your inquiry, here is what to expect.</p>
          <div class="what-next">
            <div class="wn-item"><div class="wn-num">1</div><p>Your free estate portal is provisioned instantly with 3 trial resident slots</p></div>
            <div class="wn-item"><div class="wn-num">2</div><p>Test visitor QR codes and guard terminal scanner immediately on your phone</p></div>
            <div class="wn-item"><div class="wn-num">3</div><p>Upgrade to a capacity tier (50, 200, or 1,000) whenever you are ready to onboard all units</p></div>
          </div>
          <div class="contact-note">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none"><circle cx="6.5" cy="6.5" r="5.5" stroke="#9CA3A0" stroke-width="1.4"/><path d="M6.5 4v3M6.5 8.5v.5" stroke="#9CA3A0" stroke-width="1.4" stroke-linecap="round"/></svg>
            Assisted onboarding and staff walkthrough available on demand.
          </div>
          <div style="margin-top:28px">
            <p style="font-size:.8rem;color:var(--n400);margin-bottom:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em">Connect on Social Media</p>
            <div class="social-row">
              <a class="social-btn" href="https://x.com/checkpassng" target="_blank" aria-label="Twitter/X">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.259 5.63L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg>
                X / Twitter
              </a>
              <a class="social-btn" href="https://www.instagram.com/checkpass.ng/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
                Instagram
              </a>
              <a class="social-btn" href="https://linkedin.com/company/checkpassng" target="_blank" aria-label="LinkedIn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7H10V9h4v1.765A4 4 0 0116 8zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                LinkedIn
              </a>
              <a class="social-btn" href="https://facebook.com/checkpassng" target="_blank" aria-label="Facebook">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                Facebook
              </a>
              <a class="social-btn" href="https://wa.me/2348000000000" target="_blank" aria-label="WhatsApp" style="color:#128C7E;border-color:rgba(18,140,126,.3)">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.301-.15-1.78-.878-2.056-.979-.276-.1-.476-.15-.677.15-.2.301-.777.979-.953 1.18-.175.2-.351.226-.652.075-.3-.15-1.267-.467-2.414-1.488-.893-.796-1.496-1.779-1.671-2.08-.175-.3-.019-.463.132-.613.136-.134.301-.351.451-.527.151-.175.201-.301.301-.501.1-.2.05-.376-.025-.526-.075-.15-.677-1.633-.928-2.238-.244-.589-.493-.509-.677-.518l-.577-.01c-.2 0-.527.075-.802.376-.276.3-1.053 1.03-1.053 2.511 0 1.482 1.078 2.912 1.228 3.113.15.2 2.122 3.24 5.141 4.542.718.31 1.279.495 1.716.634.721.229 1.377.197 1.895.12.577-.087 1.78-.727 2.03-1.43.25-.702.25-1.304.175-1.43-.075-.125-.276-.201-.577-.351zM12.04 2C6.53 2 2.06 6.47 2.06 11.98c0 1.76.46 3.48 1.34 5.01L2 22l5.18-1.36c1.48.81 3.15 1.24 4.86 1.24 5.51 0 9.98-4.47 9.98-9.98C22.02 6.47 17.55 2 12.04 2z"/></svg>
                WhatsApp
              </a>
            </div>
          </div>
        </div>
        <div class="contact-form-card" data-reveal="right">
          <form id="contactForm" onsubmit="handleFormSubmit(event)">
            <div class="form-grid">
              <div class="form-group"><label>Full Name *</label><input type="text" placeholder="Your full name" required></div>
              <div class="form-group"><label>Your Role *</label>
                <select required><option value="">Select your role</option><option>Estate Manager / Admin</option><option>Resident Association Chair</option><option>Property Developer</option><option>Facility Manager</option><option>Resident / Homeowner</option><option>Other</option></select>
              </div>
              <div class="form-group"><label>Estate Name *</label><input type="text" placeholder="e.g. Lekki Gardens Phase 4" required></div>
              <div class="form-group"><label>Estate Location / City *</label>
                <select required><option value="">Select city</option><option>Lagos</option><option>Abuja</option><option>Port Harcourt</option><option>Ibadan</option><option>Other Nigerian City</option></select>
              </div>
              <div class="form-group"><label>Phone Number *</label><input type="tel" placeholder="+234 800 000 0000" required></div>
              <div class="form-group"><label>Email Address *</label><input type="email" placeholder="admin@estate.ng" required></div>
              <div class="form-group full">
                <label style="font-weight:700;color:var(--dark)">Select Estate Capacity / Package *</label>
                <select id="contactPlanSelect" style="font-weight:600;border-color:rgba(10,91,56,0.3);background-color:#F7FAF8;padding:12px 14px">
                  <option value="boutique">Boutique Estate &amp; Closes (Up to 50 Units)</option>
                  <option value="residential" selected>Gated Residential Communities (50 &ndash; 300 Units)</option>
                  <option value="enterprise">Master Planned Estate / Large CDA (300+ Units)</option>
                  <option value="commercial">Commercial / Mixed-Use Facility</option>
                  <option value="custom">Custom Deployment Consultation</option>
                </select>
              </div>
              <div class="form-group full"><label>Estate Units &amp; Gates (Optional)</label><input type="text" id="contactUnitsInput" placeholder="e.g. 120 units, 2 access gates"></div>
            </div>
            <div class="form-submit">
              <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center">
                Request Custom Estate Quote &amp; Proposal
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
              </button>
              <p style="font-size:.76rem;color:var(--n400);text-align:center;margin-top:8px">⚡ Fast turnaround within 24 hours. Custom proposal tailored to your gate and resident capacity.</p>
            </div>
          </form>
          <div class="form-success" id="formSuccess">
            <div class="check-circle"><svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M5 12l5 5 9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
            <h3>Quote Request Received!</h3>
            <p>Thank you for reaching out. Our estate deployment team has received your information and will prepare a tailored proposal and quote for your community within 24 hours.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- FOOTER -->
<footer>
  <div class="container">
    <div class="footer-inner" style="grid-template-columns: 2fr 1fr 1.2fr 1fr;">
      <div class="footer-brand">
        <div class="nav-logo" onclick="showPage('home')" style="cursor:pointer">
          <span class="nav-logo-lockup" aria-label="Checkpass">
            <img class="logo-light" src="/assets/logo/for dark background.png" alt="Checkpass" style="opacity: 1 !important; display: block;">
          </span>
        </div>
        <p class="footer-tagline">Estate operations & access management. Without the chaos.</p>
        <p class="footer-sub">A product of Sheffy Technologies</p>
        <div class="footer-social">
          <a href="https://x.com/checkpassng" target="_blank" aria-label="Twitter/X"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.259 5.63L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg> X</a>
          <a href="https://www.instagram.com/checkpass.ng/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg> Instagram</a>
          <a href="https://linkedin.com/company/checkpassng" target="_blank" aria-label="LinkedIn"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-4 0v7H10V9h4v1.765A4 4 0 0116 8zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg> LinkedIn</a>
          <a href="https://facebook.com/checkpassng" target="_blank" aria-label="Facebook"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg> Facebook</a>
          <a href="https://wa.me/2348000000000" target="_blank" aria-label="WhatsApp"><svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.301-.15-1.78-.878-2.056-.979-.276-.1-.476-.15-.677.15-.2.301-.777.979-.953 1.18-.175.2-.351.226-.652.075-.3-.15-1.267-.467-2.414-1.488-.893-.796-1.496-1.779-1.671-2.08-.175-.3-.019-.463.132-.613.136-.134.301-.351.451-.527.151-.175.201-.301.301-.501.1-.2.05-.376-.025-.526-.075-.15-.677-1.633-.928-2.238-.244-.589-.493-.509-.677-.518l-.577-.01c-.2 0-.527.075-.802.376-.276.3-1.053 1.03-1.053 2.511 0 1.482 1.078 2.912 1.228 3.113.15.2 2.122 3.24 5.141 4.542.718.31 1.279.495 1.716.634.721.229 1.377.197 1.895.12.577-.087 1.78-.727 2.03-1.43.25-.702.25-1.304.175-1.43-.075-.125-.276-.201-.577-.351zM12.04 2C6.53 2 2.06 6.47 2.06 11.98c0 1.76.46 3.48 1.34 5.01L2 22l5.18-1.36c1.48.81 3.15 1.24 4.86 1.24 5.51 0 9.98-4.47 9.98-9.98C22.02 6.47 17.55 2 12.04 2z"/></svg> WhatsApp</a>
        </div>
      </div>
      <div class="footer-col">
        <h5>Product</h5>
        <ul>
          <li><a href="#" onclick="showPage('how-it-works');return false">How It Works</a></li>
          <li><a href="#" onclick="showPage('features');return false">Features</a></li>
          <li><a href="#" onclick="showPage('pricing');return false">Pricing & Plans</a></li>
          <li><a href="#" onclick="showPage('estates');return false">Supported Estates</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Contact & Support</h5>
        <ul>
          <li><a href="mailto:support@checkpass.ng">support@checkpass.ng</a></li>
          <li><a href="mailto:sales@checkpass.ng">sales@checkpass.ng</a></li>
          <li><a href="tel:+2348002432572">+234 (0) 800 CHECKPASS</a></li>
          <li><a href="https://wa.me/2348000000000" target="_blank" style="color:var(--gold)">WhatsApp Chat</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>Company</h5>
        <ul>
          <li><a href="#" onclick="showPage('contact');return false">Contact Us</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
          <li><a href="#">Security Overview</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 CheckPass &middot; Sheffy Technologies. All rights reserved.</p>
      <div class="footer-legal"><a href="#">Privacy</a><a href="#">Terms</a></div>
    </div>
  </div>
</footer>


<!-- DEMO VIDEO MODAL -->
<div class="login-modal-overlay" id="demoModal" onclick="if(event.target===this)closeDemoModal()">
  <div class="demo-video-modal-box">
    <div class="login-close-btn" onclick="closeDemoModal()" style="background:rgba(255,255,255,.15);color:#fff">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </div>
    
    <div class="demo-modal-header">
      <div>
        <span class="badge badge-green label" style="margin-bottom:8px;display:inline-flex">Product Demo</span>
        <h3>
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
          CheckPass Gate &amp; Access Walkthrough
        </h3>
      </div>
    </div>

    <!-- Video Player Frame -->
    <div class="demo-video-frame">
      <video id="demoVideoPlayer" controls playsinline poster="/assets/hero Banner/hero image 2.mp4">
        <source src="/assets/hero Banner/hero image 2.mp4" type="video/mp4">
        Your browser does not support video playback.
      </video>
    </div>

    <!-- 3 Core Steps -->
    <div class="demo-steps-strip">
      <div class="demo-step-card">
        <div class="demo-step-num">1</div>
        <p><strong>Resident Generates Pass</strong><br>Enters guest name, validity time &amp; vehicle info</p>
      </div>
      <div class="demo-step-card">
        <div class="demo-step-num">2</div>
        <p><strong>Instant WhatsApp Share</strong><br>Guest receives encrypted QR code pass on phone</p>
      </div>
      <div class="demo-step-card">
        <div class="demo-step-num">3</div>
        <p><strong>Guard Scans in 3 Secs</strong><br>Sub-4s scan on guard phone with real-time log</p>
      </div>
    </div>

    <div class="demo-modal-footer">
      <span style="font-size:.84rem;color:rgba(255,255,255,.7)">⚡ Ready to try it for your estate? Start free with up to 3 units.</span>
      <button class="btn btn-primary" onclick="closeDemoModal();window.goToRegister()">
        Create Free Account
        <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
    </div>
  </div>
</div>


<!-- LOGIN MODAL -->
<div class="login-modal-overlay" id="loginModal" onclick="if(event.target===this)closeLoginModal()">
  <div class="login-modal-box">
    <div class="login-close-btn" onclick="closeLoginModal()">
      <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
    </div>
    
    <div class="login-header">
      <div class="nav-logo-lockup" style="margin:0 auto 14px;width:150px;height:30px">
        <img src="/assets/logo/for white background.png" alt="CheckPass">
      </div>
      <h3>Sign in to CheckPass</h3>
      <p id="loginSubText">Select your role to access your portal</p>
    </div>

    <!-- Stakeholder Tabs -->
    <div class="login-role-tabs">
      <div class="login-role-btn active" id="roleTabManager" onclick="setLoginRole('manager')">🧑‍💼 Manager</div>
      <div class="login-role-btn" id="roleTabResident" onclick="setLoginRole('resident')">🏠 Resident</div>
      <div class="login-role-btn" id="roleTabSecurity" onclick="setLoginRole('security')">🛡️ Guard</div>
    </div>

    <!-- Form -->
    <form id="loginForm" onsubmit="handleLoginSubmit(event)">
      <div class="login-form-group" id="groupEstateCode">
        <label>Estate ID / Code</label>
        <input type="text" id="loginEstateCode" placeholder="e.g. CP-LEKKI-01" required value="CP-LEKKI-01">
      </div>
      <div class="login-form-group">
        <label id="loginUserLabel">Admin Email Address</label>
        <input type="text" id="loginIdentifier" placeholder="manager@estate.ng" required value="admin@lekkigardens.ng">
      </div>
      <div class="login-form-group">
        <label id="loginPassLabel">Password</label>
        <input type="password" id="loginPassword" placeholder="••••••••" required value="password123">
      </div>

      <div class="login-options">
        <label><input type="checkbox" checked> Remember me</label>
        <a href="#" onclick="alert('Password reset link sent to your registered contact channel.');return false">Forgot PIN?</a>
      </div>

      <button type="submit" class="btn btn-primary" id="loginSubmitBtn" style="width:100%;justify-content:center">
        Sign In to Manager Portal &rarr;
      </button>
    </form>

    <div id="loginSuccessNotice" style="display:none;text-align:center;padding:18px 0">
      <div class="check-circle" style="width:48px;height:48px;border-radius:50%;background:var(--p-lt);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:var(--p)">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M5 12l5 5 9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </div>
      <h4 style="font-size:1.1rem;margin-bottom:4px;color:var(--dark)" id="loginSuccessTitle">Welcome Back!</h4>
      <p style="font-size:.85rem;color:var(--n400)" id="loginSuccessDesc">Redirecting to your dashboard...</p>
    </div>

    <!-- Quick Demo Logins -->
    <div class="demo-pills-wrap">
      <p>⚡ Quick Test Credentials</p>
      <div class="demo-pills">
        <span class="demo-pill" onclick="fillLoginDemo('manager')">Manager Demo</span>
        <span class="demo-pill" onclick="fillLoginDemo('resident')">Resident Demo</span>
        <span class="demo-pill" onclick="fillLoginDemo('security')">Guard Demo</span>
      </div>
    </div>
  </div>
</div>






  </div>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

function goToLogin() {
  router.push('/login')
}

function goToRegister() {
  router.push('/register')
}

if (typeof window !== 'undefined') {
  (window as any).goToLogin = goToLogin;
  (window as any).goToRegister = goToRegister;
  (window as any).openLoginModal = goToLogin;
}

onMounted(() => {
  let styleEl = document.getElementById('landing-styles') as HTMLLinkElement
  if (!styleEl) {
    styleEl = document.createElement('link')
    styleEl.id = 'landing-styles'
    styleEl.rel = 'stylesheet'
    styleEl.href = '/landing.css'
    document.head.appendChild(styleEl)
  }

  const loadScript = (src: string): Promise<void> => {
    return new Promise((resolve) => {
      if (document.querySelector(`script[src="${src}"]`)) {
        resolve()
        return
      }
      const s = document.createElement('script')
      s.src = src
      s.onload = () => resolve()
      document.body.appendChild(s)
    })
  }

  Promise.all([
    loadScript('https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js'),
    loadScript('https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js')
  ]).then(() => {
    try {
      const existingScript = document.getElementById('landing-inline-script')
      if (existingScript) existingScript.remove()
      const script = document.createElement('script')
      script.id = 'landing-inline-script'
      script.textContent = "\r\ngsap.registerPlugin(ScrollTrigger);\r\nlet activePage='home',heroVideoFadeMs=450,heroVideoCycleStarted=false,heroVideoIndex=0,heroVideoTransitioning=false;\r\nlet currentBillingCycle = 'monthly';\r\nlet currentLoginRole = 'manager';\r\n\r\nfunction showPage(id, plan){\r\n  const prev=document.querySelector('.page.active');\r\n  const next=document.getElementById('page-'+id);\r\n  if(!next)return;\r\n  if(prev && prev !== next) prev.classList.remove('active');\r\n  next.classList.add('active','entering');\r\n  setTimeout(()=>next.classList.remove('entering'),420);\r\n  activePage=id;\r\n  window.scrollTo({top:0,behavior:'instant'});\r\n  updateNavStyle(id);setActiveNavLink(id);\r\n  setTimeout(()=>initReveal(),80);\r\n  if(id==='home')setTimeout(initHeroAnim,60);\r\n  if(id==='contact' && plan){\r\n    const ps = document.getElementById('contactPlanSelect');\r\n    if(ps && ps.querySelector(`option[value=\"${plan}\"]`)){\r\n      ps.value = plan;\r\n    }\r\n  }\r\n  closeMobileNav();\r\n}\r\n\r\nfunction setActiveNavLink(id){\r\n  document.querySelectorAll('.nav-link').forEach(l=>l.classList.remove('active-nav'));\r\n  const map={'how-it-works':0,'features':1,'pricing':2,'estates':3,'contact':4};\r\n  const idx=map[id];\r\n  if(idx!==undefined){const links=document.querySelectorAll('.nav-links .nav-link');if(links[idx])links[idx].classList.add('active-nav');}\r\n}\r\n\r\nfunction updateNavStyle(pid){\r\n  const nav=document.getElementById('navbar');\r\n  nav.classList.remove('hero-nav','dark-nav','scrolled');\r\n  nav.classList.add(window.scrollY>60?'scrolled':pid==='home'?'hero-nav':'dark-nav');\r\n}\r\n\r\nwindow.addEventListener('scroll',()=>{\r\n  const nav=document.getElementById('navbar');\r\n  if(window.scrollY>60){nav.classList.remove('hero-nav','dark-nav');nav.classList.add('scrolled');}\r\n  else{nav.classList.remove('scrolled');nav.classList.add(activePage==='home'?'hero-nav':'dark-nav');}\r\n},{passive:true});\r\n\r\nfunction toggleMobileNav(){document.getElementById('mobileNav').classList.toggle('open');}\r\nfunction closeMobileNav(){document.getElementById('mobileNav').classList.remove('open');}\r\n\r\nfunction initReveal(){\r\n  ScrollTrigger.getAll().forEach(t=>t.kill());\r\n  const page=document.querySelector('.page.active');\r\n  if(!page)return;\r\n  // HIW steps: staggered group entrance\r\n  const hiwSteps=page.querySelectorAll('.hiw-step');\r\n  if(hiwSteps.length){\r\n    const hiwWrap=page.querySelector('.hiw-steps');\r\n    gsap.fromTo(hiwSteps,{opacity:0,y:32},{opacity:1,y:0,duration:.7,stagger:.15,ease:'power3.out',scrollTrigger:{trigger:hiwWrap||hiwSteps[0],start:'top 82%',once:true}});\r\n  }\r\n  // General section reveals\r\n  page.querySelectorAll('[data-reveal]').forEach(el=>{\r\n    if(el.classList.contains('hiw-step'))return;\r\n    const dir=el.dataset.reveal;const d=(parseInt(el.dataset.delay||'0'))*0.12;\r\n    const base={duration:.65,delay:d,ease:'power3.out'};\r\n    let from,to;\r\n    if(dir==='left'){from={opacity:0,x:-28};to={...base,opacity:1,x:0};}\r\n    else if(dir==='right'){from={opacity:0,x:28};to={...base,opacity:1,x:0};}\r\n    else if(dir==='scale'){from={opacity:0,scale:.95};to={...base,opacity:1,scale:1};}\r\n    else{from={opacity:0,y:24};to={...base,opacity:1,y:0};}\r\n    gsap.fromTo(el,from,{...to,scrollTrigger:{trigger:el,start:'top 88%',once:true}});\r\n  });\r\n  // HIW connector line draw\r\n  const conn=page.querySelector('.hiw-connector');\r\n  if(conn)gsap.fromTo(conn,{scaleX:0},{scaleX:1,duration:.9,ease:'power2.inOut',transformOrigin:'left center',scrollTrigger:{trigger:conn,start:'top 85%',once:true}});\r\n  ScrollTrigger.refresh();\r\n}\r\n\r\nfunction animateCounter(el){\r\n  const raw=el.dataset.count;if(!raw)return;\r\n  const target=parseInt(raw);const suffix=raw.replace(String(target),'');\r\n  const dur=1800;const t0=performance.now();\r\n  (function tick(now){const p=Math.min((now-t0)/dur,1);const e=1-Math.pow(1-p,3);el.textContent=Math.round(e*target)+suffix;if(p<1)requestAnimationFrame(tick);})(t0);\r\n}\r\nfunction initCounters(){\r\n  const io=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){animateCounter(e.target);io.unobserve(e.target);}});},{threshold:0.5});\r\n  document.querySelectorAll('[data-count]').forEach(el=>io.observe(el));\r\n}\r\n\r\nfunction toggleFaq(el){\r\n  const item=el.closest('.faq-item');const was=item.classList.contains('open');\r\n  document.querySelectorAll('.faq-item').forEach(i=>i.classList.remove('open'));\r\n  if(!was)item.classList.add('open');\r\n}\r\n\r\nfunction handleFormSubmit(e){\r\n  e.preventDefault();\r\n  document.getElementById('contactForm').style.display='none';\r\n  document.getElementById('formSuccess').style.display='block';\r\n}\r\n\r\nfunction initHeroAnim(){\r\n  const home=document.getElementById('page-home');\r\n  if(!home||!home.classList.contains('active'))return;\r\n  const items=home.querySelectorAll('.hero-animate');\r\n  items.forEach(i=>i.classList.remove('is-visible'));\r\n  requestAnimationFrame(()=>items.forEach(i=>i.classList.add('is-visible')));\r\n}\r\n\r\nfunction setupHeroVideoSequence(){\r\n  if(heroVideoCycleStarted)return;\r\n  const videos=[document.getElementById('heroVideo1'),document.getElementById('heroVideo2')].filter(Boolean);\r\n  if(videos.length!==2)return;\r\n  videos.forEach(v=>{v.muted=true;v.playsInline=true;v.setAttribute('muted','');v.setAttribute('playsinline','');});\r\n  const play=i=>{const v=videos[i];if(!v)return;v.currentTime=0;const p=v.play();if(p&&p.catch)p.catch(()=>{});};\r\n  const nextVid=()=>{\r\n    if(heroVideoTransitioning)return;heroVideoTransitioning=true;\r\n    const cur=videos[heroVideoIndex];const ni=(heroVideoIndex+1)%videos.length;const nxt=videos[ni];\r\n    if(!cur||!nxt){heroVideoTransitioning=false;return;}\r\n    nxt.currentTime=0;const p=nxt.play();if(p&&p.catch)p.catch(()=>{});\r\n    nxt.classList.add('is-active');cur.classList.remove('is-active');\r\n    setTimeout(()=>{cur.pause();cur.currentTime=0;heroVideoIndex=ni;heroVideoTransitioning=false;},heroVideoFadeMs);\r\n  };\r\n  videos.forEach((v,i)=>{\r\n    v.addEventListener('timeupdate',()=>{if(i!==heroVideoIndex||heroVideoTransitioning||!v.duration)return;if(v.duration-v.currentTime<=heroVideoFadeMs/1000+0.05)nextVid();});\r\n    v.addEventListener('ended',()=>{if(i===heroVideoIndex)nextVid();});\r\n  });\r\n  play(0);heroVideoCycleStarted=true;\r\n}\r\n\r\n/* Flagship Phone Showcase: Scroll-Driven Pinned Scrollytelling */\r\nlet currentFlagshipSlide = 0;\r\n\r\nfunction setFlagshipSlide(index) {\r\n  if (index < 0 || index > 3) return;\r\n  if (index === currentFlagshipSlide) return;\r\n  currentFlagshipSlide = index;\r\n\r\n  // 1. Update Step Buttons\r\n  for (let i = 0; i < 4; i++) {\r\n    const btn = document.getElementById('stepBtn' + i);\r\n    if (btn) {\r\n      if (i === index) btn.classList.add('active');\r\n      else btn.classList.remove('active');\r\n    }\r\n  }\r\n\r\n  // 2. Update Left Text Slides\r\n  const textSlides = document.querySelectorAll('.flagship-slide-item');\r\n  textSlides.forEach((slide) => {\r\n    const slideIdx = parseInt(slide.getAttribute('data-slide'), 10);\r\n    if (slideIdx === index) {\r\n      slide.classList.remove('leaving-up', 'leaving-down');\r\n      slide.classList.add('active');\r\n    } else {\r\n      slide.classList.remove('active');\r\n      if (slideIdx < index) {\r\n        slide.classList.add('leaving-up');\r\n        slide.classList.remove('leaving-down');\r\n      } else {\r\n        slide.classList.add('leaving-down');\r\n        slide.classList.remove('leaving-up');\r\n      }\r\n    }\r\n  });\r\n\r\n  // 3. Update Phone Screen Images\r\n  const phoneImgs = document.querySelectorAll('.phone-screen-img');\r\n  phoneImgs.forEach((img) => {\r\n    const imgIdx = parseInt(img.getAttribute('data-phone-slide'), 10);\r\n    if (imgIdx === index) {\r\n      img.classList.add('active');\r\n    } else {\r\n      img.classList.remove('active');\r\n    }\r\n  });\r\n\r\n  // 4. Update Floating Pop-out Card Groups\r\n  const popGroups = document.querySelectorAll('.phone-floating-cards-group');\r\n  popGroups.forEach((group) => {\r\n    const grpIdx = parseInt(group.getAttribute('data-pop-slide'), 10);\r\n    if (grpIdx === index) {\r\n      group.classList.add('active');\r\n    } else {\r\n      group.classList.remove('active');\r\n    }\r\n  });\r\n}\r\n\r\nfunction updateStepProgress(progress) {\r\n  for (let i = 0; i < 4; i++) {\r\n    const fill = document.getElementById('stepLine' + i);\r\n    if (!fill) continue;\r\n    const start = i * 0.25;\r\n    const end = (i + 1) * 0.25;\r\n    if (progress >= end) {\r\n      fill.style.width = '100%';\r\n    } else if (progress <= start) {\r\n      fill.style.width = '0%';\r\n    } else {\r\n      const pct = Math.round(((progress - start) / 0.25) * 100);\r\n      fill.style.width = pct + '%';\r\n    }\r\n  }\r\n}\r\n\r\nfunction goToFlagshipSlide(index) {\r\n  const section = document.getElementById('flagshipSection');\r\n  if (!section) return;\r\n  if (window.innerWidth > 768) {\r\n    const navH = 72;\r\n    const totalTravel = section.offsetHeight - (window.innerHeight - navH);\r\n    const sectionTop = window.pageYOffset + section.getBoundingClientRect().top;\r\n    const targetY = sectionTop - navH + (totalTravel * (index + 0.12) / 4);\r\n    window.scrollTo({ top: targetY, behavior: 'smooth' });\r\n  } else {\r\n    setFlagshipSlide(index);\r\n    updateStepProgress((index + 0.5) / 4);\r\n  }\r\n}\r\n\r\nfunction switchFlagshipScreen(type) {\r\n  const map = { gate: 0, pass: 1, item: 2, fees: 3 };\r\n  if (typeof map[type] !== 'undefined') goToFlagshipSlide(map[type]);\r\n}\r\n\r\nfunction updateFlagshipScroll() {\r\n  if (window.innerWidth <= 768) return;\r\n  const section = document.getElementById('flagshipSection');\r\n  if (!section) return;\r\n\r\n  const rect = section.getBoundingClientRect();\r\n  const navH = 72;\r\n  const totalTravel = section.offsetHeight - (window.innerHeight - navH);\r\n  if (totalTravel <= 0) return;\r\n\r\n  const scrolled = navH - rect.top;\r\n  const progress = Math.max(0, Math.min(1, scrolled / totalTravel));\r\n\r\n  const targetIndex = Math.min(3, Math.floor(progress * 4));\r\n  setFlagshipSlide(targetIndex);\r\n  updateStepProgress(progress);\r\n}\r\n\r\nlet flagshipScrollTicking = false;\r\nwindow.addEventListener('scroll', () => {\r\n  if (!flagshipScrollTicking) {\r\n    window.requestAnimationFrame(() => {\r\n      updateFlagshipScroll();\r\n      flagshipScrollTicking = false;\r\n    });\r\n    flagshipScrollTicking = true;\r\n  }\r\n}, { passive: true });\r\n\r\n/* Scrollytelling How It Works: Smooth Scroll & Active Scroll-Spy */\r\nfunction scrollToHowStep(stepId){\r\n  const el = document.getElementById(stepId);\r\n  if(el){\r\n    const yOffset = -140; // account for navbar + sticky pill bar\r\n    const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;\r\n    window.scrollTo({top: y, behavior: 'smooth'});\r\n  }\r\n}\r\n\r\n// Backwards-compatible helper\r\nfunction goToHowStep(step){\r\n  scrollToHowStep('how-step-' + step);\r\n}\r\n\r\nfunction setHowMode(mode){\r\n  const map = { lifecycle: 'how-step-1', resident: 'how-step-1', guard: 'how-step-3', admin: 'how-step-4' };\r\n  if(map[mode]) scrollToHowStep(map[mode]);\r\n}\r\n\r\n// Scroll-Spy to dynamically highlight active step in sticky progress bar\r\nwindow.addEventListener('scroll', () => {\r\n  if(typeof activePage !== 'undefined' && activePage !== 'how-it-works') return;\r\n  const steps = ['how-step-1', 'how-step-2', 'how-step-3', 'how-step-4', 'how-step-5'];\r\n  const scrollPos = window.scrollY + 220;\r\n\r\n  for(let i = steps.length - 1; i >= 0; i--){\r\n    const sec = document.getElementById(steps[i]);\r\n    if(sec && sec.offsetTop <= scrollPos){\r\n      document.querySelectorAll('.how-step-pill-btn').forEach(btn => btn.classList.remove('active'));\r\n      const activeBtn = document.getElementById('pill-' + steps[i]);\r\n      if(activeBtn) activeBtn.classList.add('active');\r\n      break;\r\n    }\r\n  }\r\n}, {passive: true});\r\n\r\n/* Estate Deployment & Scope Planner (Consultation Model) */\r\nfunction updatePricingSlider(units){\r\n  const count = parseInt(units);\r\n  const display = document.getElementById('sliderUnitCount');\r\n  const tierBadge = document.getElementById('calcTierBadge');\r\n  const totalDisplay = document.getElementById('calcTotal');\r\n  const annualSub = document.getElementById('calcAnnualSub');\r\n  const actionBtn = document.getElementById('calcActionBtn');\r\n  const maxResidents = count * 3;\r\n\r\n  if(display) display.textContent = count + (count === 1 ? ' Unit' : ' Units') + ' (up to ' + maxResidents + ' residents)';\r\n\r\n  if(count <= 50){\r\n    if(tierBadge) tierBadge.textContent = 'Boutique Estate Scope (' + count + ' Units)';\r\n    if(totalDisplay) totalDisplay.textContent = '1 Gate Terminal + 1 Admin Portal';\r\n    if(annualSub) annualSub.textContent = 'Recommended for private closes & courts. Includes 1-week managed onboarding, guard orientation, and visitor pass setup.';\r\n    if(actionBtn) {\r\n      actionBtn.textContent = 'Request Quote for ' + count + ' Units';\r\n      actionBtn.onclick = () => {\r\n        showPage('contact', 'boutique');\r\n        const inp = document.getElementById('contactUnitsInput');\r\n        if(inp) inp.value = count + ' units';\r\n      };\r\n    }\r\n  } else if(count <= 300){\r\n    const terminals = count <= 150 ? '2 Gate Terminals' : '3 Gate Terminals';\r\n    if(tierBadge) tierBadge.textContent = 'Gated Residential Scope (' + count + ' Units)';\r\n    if(totalDisplay) totalDisplay.textContent = terminals + ' + Multi-Zone Sync';\r\n    if(annualSub) annualSub.textContent = 'Includes automated dues ledger, anti-theft item tracking, and dedicated guard physical drills.';\r\n    if(actionBtn) {\r\n      actionBtn.textContent = 'Request Proposal for ' + count + ' Units';\r\n      actionBtn.onclick = () => {\r\n        showPage('contact', 'residential');\r\n        const inp = document.getElementById('contactUnitsInput');\r\n        if(inp) inp.value = count + ' units';\r\n      };\r\n    }\r\n  } else {\r\n    if(tierBadge) tierBadge.textContent = 'Master Planned Estate (' + count + ' Units)';\r\n    if(totalDisplay) totalDisplay.textContent = 'Multi-Gate Network + Turnstile Integration';\r\n    if(annualSub) annualSub.textContent = 'Includes executive AGM reporting, SLA guarantee, and custom multi-zone security hierarchy.';\r\n    if(actionBtn) {\r\n      actionBtn.textContent = 'Speak with Enterprise Team (' + count + ' Units)';\r\n      actionBtn.onclick = () => {\r\n        showPage('contact', 'enterprise');\r\n        const inp = document.getElementById('contactUnitsInput');\r\n        if(inp) inp.value = count + ' units';\r\n      };\r\n    }\r\n  }\r\n}\r\n\r\n/* Demo Video Modal Functions */\r\nfunction openDemoModal(){\r\n  const modal = document.getElementById('demoModal');\r\n  const video = document.getElementById('demoVideoPlayer');\r\n  if(modal){\r\n    modal.style.display = 'flex';\r\n    requestAnimationFrame(()=>modal.classList.add('open'));\r\n    if(video){\r\n      video.currentTime = 0;\r\n      video.play().catch(()=>{});\r\n    }\r\n  }\r\n}\r\n\r\nfunction closeDemoModal(){\r\n  const modal = document.getElementById('demoModal');\r\n  const video = document.getElementById('demoVideoPlayer');\r\n  if(modal){\r\n    modal.classList.remove('open');\r\n    if(video) video.pause();\r\n    setTimeout(()=>modal.style.display = 'none', 300);\r\n  }\r\n}\r\n\r\n/* Login Modal Functions */\r\nfunction openLoginModal(role){\r\n  if(role) setLoginRole(role);\r\n  const modal = document.getElementById('loginModal');\r\n  if(modal){\r\n    modal.style.display = 'flex';\r\n    requestAnimationFrame(()=>modal.classList.add('open'));\r\n  }\r\n}\r\n\r\nfunction closeLoginModal(){\r\n  const modal = document.getElementById('loginModal');\r\n  if(modal){\r\n    modal.classList.remove('open');\r\n    setTimeout(()=>modal.style.display = 'none', 300);\r\n  }\r\n}\r\n\r\nfunction setLoginRole(role){\r\n  currentLoginRole = role;\r\n  const tM = document.getElementById('roleTabManager');\r\n  const tR = document.getElementById('roleTabResident');\r\n  const tS = document.getElementById('roleTabSecurity');\r\n  const uLbl = document.getElementById('loginUserLabel');\r\n  const pLbl = document.getElementById('loginPassLabel');\r\n  const idInput = document.getElementById('loginIdentifier');\r\n  const passInput = document.getElementById('loginPassword');\r\n  const submitBtn = document.getElementById('loginSubmitBtn');\r\n\r\n  [tM, tR, tS].forEach(t => t && t.classList.remove('active'));\r\n\r\n  if(role === 'manager'){\r\n    if(tM) tM.classList.add('active');\r\n    if(uLbl) uLbl.textContent = 'Admin Email Address';\r\n    if(pLbl) pLbl.textContent = 'Password';\r\n    if(idInput) { idInput.placeholder = 'manager@estate.ng'; idInput.value = 'admin@lekkigardens.ng'; }\r\n    if(passInput) passInput.value = 'password123';\r\n    if(submitBtn) submitBtn.textContent = 'Sign In to Manager Portal →';\r\n  } else if(role === 'resident'){\r\n    if(tR) tR.classList.add('active');\r\n    if(uLbl) uLbl.textContent = 'Phone Number or Email';\r\n    if(pLbl) pLbl.textContent = '4-Digit Resident PIN';\r\n    if(idInput) { idInput.placeholder = '08012345678 or name@email.com'; idInput.value = '08023456789'; }\r\n    if(passInput) passInput.value = '4821';\r\n    if(submitBtn) submitBtn.textContent = 'Sign In as Resident →';\r\n  } else if(role === 'security'){\r\n    if(tS) tS.classList.add('active');\r\n    if(uLbl) uLbl.textContent = 'Officer Username / Badge ID';\r\n    if(pLbl) pLbl.textContent = 'Gate Access PIN';\r\n    if(idInput) { idInput.placeholder = 'e.g. GUARD-GATE-01'; idInput.value = 'GUARD-MAIN-GATE'; }\r\n    if(passInput) passInput.value = '1122';\r\n    if(submitBtn) submitBtn.textContent = 'Sign In to Gate Scanner →';\r\n  }\r\n}\r\n\r\nfunction fillLoginDemo(role){\r\n  setLoginRole(role);\r\n}\r\n\r\nfunction handleLoginSubmit(e){\r\n  e.preventDefault();\r\n  const form = document.getElementById('loginForm');\r\n  const notice = document.getElementById('loginSuccessNotice');\r\n  const title = document.getElementById('loginSuccessTitle');\r\n  const desc = document.getElementById('loginSuccessDesc');\r\n\r\n  if(form) form.style.display = 'none';\r\n  if(notice) notice.style.display = 'block';\r\n\r\n  if(currentLoginRole === 'manager'){\r\n    if(title) title.textContent = 'Manager Portal Connected!';\r\n    if(desc) desc.textContent = 'Authenticated: Lekki Phase 1 Estate Admin. Dashboard active.';\r\n  } else if(currentLoginRole === 'resident'){\r\n    if(title) title.textContent = 'Welcome, Sarah O. (Flat 12B)';\r\n    if(desc) desc.textContent = 'Active Invites & Service charge receipts synced.';\r\n  } else {\r\n    if(title) title.textContent = 'Gate Scanner Active';\r\n    if(desc) desc.textContent = 'Main Gate Terminal verified and online.';\r\n  }\r\n\r\n  setTimeout(()=>{\r\n    closeLoginModal();\r\n    if(form) form.style.display = 'block';\r\n    if(notice) notice.style.display = 'none';\r\n  }, 2200);\r\n}\r\n\r\n/* SiteAssist Stakeholders Interactive Loader & Auto-Switch (3 Slides) */\r\nconst siteAssistFeatures = [\r\n  {\r\n    num: '01',\r\n    kicker: 'ESTATE MANAGERS & EXCO',\r\n    title: 'Run Your Estate Without Missing Records or Payment Disputes',\r\n    body: 'Create service charges and diesel levies in seconds, track paid versus defaulting units in real time, and export clean financial reports ready for your next AGM.'\r\n  },\r\n  {\r\n    num: '02',\r\n    kicker: 'SECURITY GUARDS & GATE TEAMS',\r\n    title: 'Clear Visitors in Seconds Without Endless Phone Calls',\r\n    body: 'Scan QR passes directly on your phone to verify guests instantly, log vehicles without paper logbooks, and inspect moving items with photo proof before opening the gate.'\r\n  },\r\n  {\r\n    num: '03',\r\n    kicker: 'RESIDENTS & HOMEOWNERS',\r\n    title: 'Invite Guests in Two Taps and Keep Receipts in One Place',\r\n    body: 'Share WhatsApp entry passes that get your visitors through the gate without delays, pay estate dues with instant receipts, and trigger emergency SOS if you ever need help.'\r\n  }\r\n];\r\n\r\nlet activeSiteAssistIdx = 0;\r\nlet siteAssistInterval = null;\r\nlet siteAssistProgress = 0;\r\nlet isSiteAssistPaused = false;\r\nconst SA_DURATION = 6000;\r\nconst SA_STEP = 50;\r\n\r\nfunction initSiteAssistAutoCycle(){\r\n  if(siteAssistInterval) clearInterval(siteAssistInterval);\r\n  siteAssistProgress = 0;\r\n  \r\n  const container = document.getElementById('siteassistContainer');\r\n  if(container){\r\n    container.addEventListener('mouseenter', () => { isSiteAssistPaused = true; });\r\n    container.addEventListener('mouseleave', () => { isSiteAssistPaused = false; });\r\n    container.addEventListener('touchstart', () => { isSiteAssistPaused = true; }, {passive: true});\r\n    container.addEventListener('touchend', () => { isSiteAssistPaused = false; }, {passive: true});\r\n  }\r\n\r\n  siteAssistInterval = setInterval(()=>{\r\n    if(isSiteAssistPaused) return;\r\n    siteAssistProgress += (SA_STEP / SA_DURATION) * 100;\r\n    \r\n    const fill = document.getElementById('saFill-' + activeSiteAssistIdx);\r\n    if(fill) fill.style.width = Math.min(siteAssistProgress, 100) + '%';\r\n    \r\n    if(siteAssistProgress >= 100){\r\n      siteAssistProgress = 0;\r\n      switchSiteAssistTab((activeSiteAssistIdx + 1) % 3, true);\r\n    }\r\n  }, SA_STEP);\r\n}\r\n\r\nfunction switchSiteAssistTab(idx, isAuto){\r\n  activeSiteAssistIdx = idx;\r\n  siteAssistProgress = 0;\r\n  \r\n  const item = siteAssistFeatures[idx];\r\n  const kickerEl = document.getElementById('saKicker');\r\n  const counterEl = document.getElementById('saCounter');\r\n  const titleEl = document.getElementById('saTitle');\r\n  const bodyEl = document.getElementById('saBody');\r\n  const contentBox = document.getElementById('saContentBox');\r\n  \r\n  if(kickerEl) kickerEl.textContent = item.kicker;\r\n  if(counterEl) counterEl.textContent = item.num;\r\n  if(titleEl) titleEl.textContent = item.title;\r\n  if(bodyEl) bodyEl.textContent = item.body;\r\n  \r\n  if(contentBox){\r\n    contentBox.style.animation = 'none';\r\n    contentBox.offsetHeight; // trigger reflow\r\n    contentBox.style.animation = 'saFadeUp .4s cubic-bezier(0.16,1,0.3,1) forwards';\r\n  }\r\n\r\n  // Update background slides & tabs\r\n  for(let i = 0; i < 3; i++){\r\n    const slide = document.getElementById('saSlide-' + i);\r\n    const fill = document.getElementById('saFill-' + i);\r\n    const btns = document.querySelectorAll('.siteassist-tab-btn');\r\n    \r\n    if(slide) slide.classList.toggle('active', i === idx);\r\n    if(btns[i]) btns[i].classList.toggle('active', i === idx);\r\n    if(fill) fill.style.width = '0%';\r\n  }\r\n}\r\n\r\ndocument.addEventListener('click',e=>{\r\n  const btn=e.target.closest('.btn');if(!btn)return;\r\n  const r=document.createElement('span');const rect=btn.getBoundingClientRect();const size=Math.max(rect.width,rect.height);\r\n  r.style.cssText=`position:absolute;width:${size}px;height:${size}px;border-radius:50%;background:rgba(255,255,255,.2);transform:scale(0);animation:ripple .5s ease;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px;pointer-events:none`;\r\n  if(getComputedStyle(btn).position==='static')btn.style.position='relative';\r\n  btn.style.overflow='hidden';btn.appendChild(r);setTimeout(()=>r.remove(),550);\r\n});\r\nconst rs=document.createElement('style');rs.textContent='@keyframes ripple{to{transform:scale(2.5);opacity:0}}';document.head.appendChild(rs);\r\n\r\ndocument.addEventListener('DOMContentLoaded',()=>{\r\n  updateNavStyle('home');\r\n  if(typeof initReveal === 'function') initReveal();\r\n  if(typeof initCounters === 'function') initCounters();\r\n  if(typeof updatePricingSlider === 'function') updatePricingSlider(60);\r\n  initSiteAssistAutoCycle();\r\n});\r\n\n"
      document.body.appendChild(script)
    } catch (err) {
      console.warn('Landing script init error:', err)
    }
  })
})

onUnmounted(() => {
  const styleEl = document.getElementById('landing-styles')
  if (styleEl) styleEl.remove()
  const scriptEl = document.getElementById('landing-inline-script')
  if (scriptEl) scriptEl.remove()
})
</script>
