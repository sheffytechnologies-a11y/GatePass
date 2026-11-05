<template>
  <div class="landing-wrapper">
    <!-- Full-Screen Brand Loading Cover -->
    <Transition name="fade-landing-loader">
      <div v-if="pageLoading" class="landing-screen-loader">
        <div class="landing-loader-center">
          <BrandSpinner :size="72" color="#0A5C38" />
        </div>
      </div>
    </Transition>


<!-- NAVBAR -->
<nav class="navbar hero-nav" id="navbar">
  <div class="nav-inner">
    <div class="nav-logo" onclick="showPage('home')">
      <span class="nav-logo-lockup" aria-label="CheckPass">
        <img class="logo-dark" src="/checkpass-logo.svg" alt="Checkpass">
        <img class="logo-light" src="/checkpass-logo-white.svg" alt="Checkpass">
      </span>
    </div>
    <div class="nav-links">
      <a class="nav-link" href="#" onclick="showPage('how-it-works');return false">How It Works</a>
      <a class="nav-link" href="#" onclick="showPage('features');return false">Features</a>
      <a class="nav-link" href="#" onclick="showPage('pricing');return false">Pricing</a>
      <a class="nav-link" href="#" onclick="showPage('estates');return false">Estates</a>
      <a class="nav-link" href="#" onclick="showPage('contact');return false">Contact</a>
    </div>
    <div class="nav-cta">
      <button class="btn btn-login" onclick="window.goToLogin()">Log In</button>
      <button class="btn btn-primary btn-sm" onclick="window.goToRegister()">Create Free Account</button>
    </div>
    <div class="nav-hamburger" id="hamburger" @click="toggleMobileNav" onclick="toggleMobileNav()" :class="{ open: isMobileNavOpen }" aria-label="Toggle Menu">
      <span></span><span></span><span></span>
    </div>
  </div>
</nav>

<div class="nav-mobile" id="mobileNav" :class="{ open: isMobileNavOpen }">
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
        <p class="hero-sub hero-animate" style="transition-delay:100ms">From resident-issued invites to secure verification, CheckPass<br>gives you full control over visitor access and many more.</p>
        <div class="hero-actions">
          <button class="btn btn-primary" onclick="window.goToRegister()">
            Create Free Account
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <button class="btn btn-outline" onclick="openDemoModal()">
            Watch Demo
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="margin-left:6px"><path d="M8 5v14l11-7z"/></svg>
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

  <!-- STATS STRIP (STATIC CENTERED ON WEB, SMOOTH SLIDE ON MOBILE) -->
  <section class="stats-strip" aria-label="Deployment statistics">
    <div class="stats-ticker-outer">
      <div class="stats-ticker-track">
        <!-- Set 1 (Desktop + Mobile) -->
        <div class="stat-card">
          <div class="stat-num" :data-count="platformStats.propertiesOnboarded">{{ platformStats.propertiesOnboarded }}</div>
          <div class="stat-label">{{ platformStats.propertiesOnboarded === 1 ? 'Property onboarded' : 'Properties onboarded' }}</div>
        </div>
        <div class="stat-card">
          <div class="stat-num" :data-count="platformStats.activeResidents">{{ platformStats.activeResidents }}</div>
          <div class="stat-label">Active residents</div>
        </div>
        <div class="stat-card">
          <div class="stat-num" :data-count="platformStats.passesCreated">{{ platformStats.passesCreated }}</div>
          <div class="stat-label">Passes created</div>
        </div>
        <div class="stat-card">
          <div class="stat-num" :data-count="platformStats.entriesVerified">{{ platformStats.entriesVerified }}</div>
          <div class="stat-label">Entries verified</div>
        </div>
        <div class="stat-card">
          <div class="stat-num" :data-count="platformStats.itemsDeclared">{{ platformStats.itemsDeclared }}</div>
          <div class="stat-label">Items declared</div>
        </div>

        <!-- Set 2 (for seamless continuous infinite loop on mobile only) -->
        <div class="stat-card" aria-hidden="true">
          <div class="stat-num" :data-count="platformStats.propertiesOnboarded">{{ platformStats.propertiesOnboarded }}</div>
          <div class="stat-label">{{ platformStats.propertiesOnboarded === 1 ? 'Property onboarded' : 'Properties onboarded' }}</div>
        </div>
        <div class="stat-card" aria-hidden="true">
          <div class="stat-num" :data-count="platformStats.activeResidents">{{ platformStats.activeResidents }}</div>
          <div class="stat-label">Active residents</div>
        </div>
        <div class="stat-card" aria-hidden="true">
          <div class="stat-num" :data-count="platformStats.passesCreated">{{ platformStats.passesCreated }}</div>
          <div class="stat-label">Passes created</div>
        </div>
        <div class="stat-card" aria-hidden="true">
          <div class="stat-num" :data-count="platformStats.entriesVerified">{{ platformStats.entriesVerified }}</div>
          <div class="stat-label">Entries verified</div>
        </div>
        <div class="stat-card" aria-hidden="true">
          <div class="stat-num" :data-count="platformStats.itemsDeclared">{{ platformStats.itemsDeclared }}</div>
          <div class="stat-label">Items declared</div>
        </div>
      </div>
    </div>
  </section>

  <!-- SECOND SECTION: BETTER PROPERTY MANAGEMENT -->
  <section class="bpm-section" id="property-management" data-reveal>
    <div class="container bpm-container">
      <div class="bpm-grid">
        <!-- Left: Brand Visual Illustration (Aligned left, occupying almost half viewport) -->
        <div class="bpm-visual-col" data-reveal="left">
          <div class="bpm-image-wrapper">
            <img
              src="/assets/images/better-property-management.png"
              alt="Better Property Management Starts Here - Checkpass"
              class="bpm-image"
              loading="lazy"
            />
          </div>
        </div>

        <!-- Right: Copy & Narrative -->
        <div class="bpm-content-col" data-reveal="right">
          <div class="bpm-content-inner">
            <h2 class="bpm-title">
            Better Property<br>
            Management <span class="bpm-title-accent">Starts Here</span>
          </h2>
            <p class="bpm-description">
            From residential estates and gated communities<br class="bpm-sub-br">
            to offices and business premises, Check Pass makes<br class="bpm-sub-br">
            it easier to manage occupants, coordinate visitor access,<br class="bpm-sub-br">
            handle payments and oversee daily operations.
          </p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- OVERLAPPING FEATURES SCENE (SECTION A PINNED AT CENTER + SECTION B OVERLAPS ON SCROLL) -->
  <div class="features-overlap-scene" id="featuresOverlapScene">
    <!-- SECTION A: TAKE CHARGE OF YOUR PROPERTY (AFTER-DELAY AUTO SLIDER) -->
    <section class="section-a-feature" id="take-charge">
      <div class="container section-a-container">
        <div class="section-a-grid">
          <!-- Left Column: Interactive Visual Animation Stage -->
          <div class="section-a-visual-col" data-reveal="left">
            <div 
              class="section-a-stage-wrapper"
              @mouseenter="pauseSlideA"
              @mouseleave="resumeSlideA"
            >
              <!-- Background Frame SVG -->
              <div class="section-a-bg-frame">
                <svg class="section-a-bg-svg" viewBox="0 0 1885 2013" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M202.001 526C202.001 305.086 381.087 126 602.001 126H1759V1410C1759 1630.91 1579.91 1810 1359 1810H202.001V526Z" fill="#D9EBDF"/>
                  <path d="M521.584 1810.18H203.342L201.947 1490L0 1490.88L0.539062 1614.65C1.01402 1723.69 87.8267 1812.18 195.933 1815.62C198.811 1924.64 288.092 2012.13 397.81 2012.13H521.584V1810.18Z" fill="#005E34"/>
                  <path d="M1559 126.087H1757.7L1758.57 325.995L1884.65 325.446L1884.32 248.168C1884.02 180.087 1829.82 124.843 1762.32 122.695C1760.52 54.6274 1704.78 0.000117865 1636.28 0H1559V126.087Z" fill="#005E34"/>
                </svg>
              </div>

              <!-- Slides Viewport Layer -->
              <div class="section-a-slides-viewport" :class="{ 'is-exiting': isExitingSlideA }">
                
                <!-- SLIDE 1: ESTATE CREATION / CHECK -->
                <div v-show="activeSlideA === 1" class="section-a-slide slide-1" :class="{ active: activeSlideA === 1 }">
                  <!-- Check Icon with Shakara Swagger Tilt -->
                  <div class="slide1-check-wrapper shakara-elem" data-delay="0">
                    <svg class="slide1-check-svg" viewBox="0 0 283 283" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M282.5 141.25C282.5 178.712 267.618 214.639 241.129 241.129C214.639 267.618 178.712 282.5 141.25 282.5C103.788 282.5 67.8607 267.618 41.3712 241.129C14.8817 214.639 0 178.712 0 141.25C0 103.788 14.8817 67.8607 41.3712 41.3712C67.8607 14.8817 103.788 0 141.25 0C178.712 0 214.639 14.8817 241.129 41.3712C267.618 67.8607 282.5 103.788 282.5 141.25ZM212.405 87.7516C211.143 86.4948 209.642 85.5052 207.989 84.8421C206.337 84.1789 204.568 83.8559 202.788 83.8921C201.008 83.9284 199.253 84.3232 197.629 85.0531C196.005 85.783 194.545 86.8329 193.336 88.14L132.016 166.269L95.0612 129.297C92.551 126.958 89.2308 125.684 85.8001 125.745C82.3695 125.805 79.0963 127.195 76.6701 129.621C74.2439 132.047 72.8542 135.321 72.7936 138.751C72.7331 142.182 74.0065 145.502 76.3456 148.012L123.064 194.748C124.323 196.005 125.821 196.995 127.471 197.659C129.12 198.324 130.887 198.649 132.665 198.616C134.443 198.583 136.196 198.193 137.819 197.467C139.443 196.742 140.904 195.697 142.115 194.395L212.599 106.291C215.002 103.792 216.329 100.451 216.296 96.985C216.263 93.5188 214.872 90.2037 212.422 87.7516H212.405Z" fill="#0C8D53"/>
                    </svg>
                  </div>

                  <!-- Greenview Estate Card -->
                  <div class="slide1-card-wrapper after-delay-elem" data-delay="1">
                    <img 
                      src="/assets/images/section-a/slide1-estate.png" 
                      alt="Greenview Estate" 
                      class="slide-hero-card"
                    />
                  </div>
                </div>

                <!-- SLIDE 2: NEW RESIDENT ADDED + CENSUS -->
                <div v-show="activeSlideA === 2" class="section-a-slide slide-2" :class="{ active: activeSlideA === 2 }">
                  <!-- New Resident Added with Shakara Pop -->
                  <div class="slide2-new-resident shakara-elem" data-delay="0">
                    <img src="/assets/images/section-a/slide2-new-resident.svg" alt="New Resident Added" />
                  </div>
                  <!-- Total Residents Badge -->
                  <div class="slide2-total-residents after-delay-elem" data-delay="1">
                    <img src="/assets/images/section-a/slide2-total-residents.svg" alt="Total Residents: 23" />
                  </div>

                  <!-- Michael James Floating Card -->
                  <div class="slide2-card-wrapper after-delay-elem" data-delay="2">
                    <img 
                      src="/assets/images/section-a/slide2-michael-james.png" 
                      alt="Michael James - Unit B204" 
                      class="slide-hero-card"
                    />
                  </div>
                </div>

                <!-- SLIDE 3: ADMIN & SECURITY TEAMS -->
                <div v-show="activeSlideA === 3" class="section-a-slide slide-3" :class="{ active: activeSlideA === 3 }">
                  <!-- Slide 3 Admin Card -->
                  <div class="slide3-admin-card shakara-elem" data-delay="0">
                    <img 
                      src="/assets/images/section-a/slide3-admin.png" 
                      alt="Admin Assigned - Property Administrator" 
                      class="slide-hero-card"
                    />
                  </div>

                  <!-- Slide 3 Security Card -->
                  <div class="slide3-security-card after-delay-elem" data-delay="1">
                    <img 
                      src="/assets/images/section-a/slide3-security.png" 
                      alt="Security Added - Gate Verification" 
                      class="slide-hero-card"
                    />
                  </div>
                </div>

                <!-- SLIDE 4: PAYMENTS & DUES RECONCILIATION -->
                <div v-show="activeSlideA === 4" class="section-a-slide slide-4" :class="{ active: activeSlideA === 4 }">
                  <!-- Collection in Progress Badge -->
                  <div class="slide4-collection-badge shakara-elem" data-delay="0">
                    <img src="/assets/images/section-a/slide4-collection.svg" alt="Collection in progress" />
                  </div>

                  <!-- Payment Overview Card -->
                  <div class="slide4-card-wrapper after-delay-elem" data-delay="1">
                    <img 
                      src="/assets/images/section-a/slide4-payment-overview.svg" 
                      alt="Payment Overview" 
                      class="slide-hero-card"
                    />
                  </div>
                </div>

              </div>

              <!-- Slide Indicator Dots -->
              <div class="section-a-indicator" role="tablist" aria-label="Section A Slide Navigation">
                <button
                  v-for="idx in 4"
                  :key="idx"
                  type="button"
                  class="indicator-item"
                  :class="{ active: activeSlideA === idx }"
                  :aria-selected="activeSlideA === idx"
                  :aria-label="`Go to slide ${idx}`"
                  @click="setSlideA(idx)"
                >
                  <span class="indicator-shape"></span>
                </button>
              </div>

            </div>
          </div>

          <!-- Right Column: Heading & Descriptive Copy -->
          <div class="section-a-content-col" data-reveal="right">
            <div class="section-a-content-inner">
              <h2 class="section-a-title">
                Take Charge of<br>
                Your <span class="section-a-title-accent">Property</span>
              </h2>
              <p class="section-a-description">
                Whether it's a residential estate, gated community or<br class="sec-a-sub-br">
                workplace, manage occupants, assign responsibilities,<br class="sec-a-sub-br">
                organise payments and oversee daily operations from<br class="sec-a-sub-br">
                one dashboard.
              </p>
              <a href="#features" class="section-cta-link sec-a-cta" onclick="showPage('features');return false">
                <span>Learn more</span>
                <svg class="cta-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                  <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </a>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- SECTION B: MANAGE VISITORS & PAYMENTS WITH EASE (OVERLAPS SECTION A ON SCROLL) -->
    <section class="section-b-feature" id="manage-visitors">
      <!-- Decorative Mint Corner Accents -->
      <div class="sec-b-corner-accent sec-b-accent-tr" aria-hidden="true">
        <svg viewBox="0 0 326 326" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0 126H198.7L199.57 325.995L325.65 325.446L325.32 248.168C325.02 180.086 270.82 124.843 203.32 122.695C201.52 54.6274 145.78 0 77.28 0H0V126Z" fill="#96D0B6"/>
        </svg>
      </div>
      <div class="sec-b-corner-accent sec-b-accent-bl" aria-hidden="true">
        <svg viewBox="0 0 522 522" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M521.584 320H203.341L201.946 0L0 0.88L0.539062 124.65C1.01402 233.69 87.826 322.18 195.932 325.62C198.81 434.64 288.091 522.13 397.809 522.13H521.584V320Z" fill="#96D0B6"/>
        </svg>
      </div>

      <div class="container section-b-container">
        <div class="section-b-grid">
          <!-- Left Column: Heading & Descriptive Copy -->
          <div class="section-b-content-col" data-reveal="left">
            <div class="section-b-content-inner">
              <h2 class="section-b-title">
                Manage Visitors<br>
                &amp; Payments<br>
                With <span class="section-b-title-accent">Ease</span>
              </h2>
              <p class="section-b-description">
                From inviting visitors and managing access passes to<br class="sec-b-sub-br">
                paying estate dues and receiving important updates,<br class="sec-b-sub-br">
                everything is easier when it's right on your phone.
              </p>
              <a href="#features" class="section-cta-link sec-b-cta" onclick="showPage('features');return false">
                <span>Learn more</span>
                <svg class="cta-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                  <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </a>
            </div>
          </div>

          <!-- Right Column: Interactive Visual Animation Stage -->
          <div class="section-b-visual-col" data-reveal="right">
            <div 
              class="section-b-stage-wrapper"
              @mouseenter="pauseSlideB"
              @mouseleave="resumeSlideB"
            >
              <!-- Background Frame SVG -->
              <div class="section-b-bg-frame">
                <svg class="section-b-bg-svg" viewBox="0 0 1885 2013" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M202 526C202 305.086 381.086 126 602 126H1759V1410C1759 1630.91 1579.91 1810 1359 1810H202V526Z" fill="#044B2B"/>
                  <path d="M521.584 1810.18H203.341L201.946 1490L0 1490.88L0.539062 1614.65C1.01402 1723.69 87.826 1812.18 195.932 1815.62C198.81 1924.64 288.091 2012.13 397.809 2012.13H521.584V1810.18Z" fill="#96D0B6"/>
                  <path d="M1559 126.087H1757.69L1758.57 325.995L1884.65 325.446L1884.31 248.168C1884.02 180.086 1829.82 124.843 1762.32 122.695C1760.52 54.6274 1704.78 0.000117865 1636.28 0H1559V126.087Z" fill="#96D0B6"/>
                </svg>
              </div>

              <!-- Slides Viewport Layer -->
              <div class="section-b-slides-viewport" :class="{ 'is-exiting': isExitingSlideB }">
                <!-- SLIDE 1: VISITOR QR SCAN -->
                <div v-show="activeSlideB === 1" class="section-b-slide slide-1" :class="{ active: activeSlideB === 1 }">
                  <img 
                    src="/assets/images/section-b/slider-1.png" 
                    alt="Security Scanning Visitor QR Code" 
                    class="section-b-slide-img"
                  />
                </div>

                <!-- SLIDE 2: RESIDENT PASS & PHONE APP -->
                <div v-show="activeSlideB === 2" class="section-b-slide slide-2" :class="{ active: activeSlideB === 2 }">
                  <img 
                    src="/assets/images/section-b/slider-2.png" 
                    alt="Resident Managing Passes on Phone" 
                    class="section-b-slide-img"
                  />
                </div>
              </div>

              <!-- Slide Indicator (Exact proportions and colors from slider indicaroe.svg: 207 x 60) -->
              <div class="section-b-indicator" role="tablist" aria-label="Section B Slide Navigation">
                <button
                  v-for="idx in 2"
                  :key="idx"
                  type="button"
                  class="indicator-b-item"
                  :class="{ active: activeSlideB === idx }"
                  :aria-selected="activeSlideB === idx"
                  :aria-label="`Go to slide ${idx}`"
                  @click="setSlideB(idx)"
                >
                  <span class="indicator-b-shape"></span>
                </button>
              </div>

            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- SECTION C: STAY AHEAD OF EVERY ENTRY (OVERLAPS SECTION B ON SCROLL) -->
    <section class="section-c-feature" id="stay-ahead">
      <!-- Decorative Sky Blue Corner Accents -->
      <div class="sec-c-corner-accent sec-c-accent-tr" aria-hidden="true">
        <svg viewBox="0 0 326 326" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M0 126H198.7L199.57 325.995L325.65 325.446L325.32 248.168C325.02 180.086 270.82 124.843 203.32 122.695C201.52 54.6274 145.78 0 77.28 0H0V126Z" fill="#50B3D5"/>
        </svg>
      </div>
      <div class="sec-c-corner-accent sec-c-accent-bl" aria-hidden="true">
        <svg viewBox="0 0 522 522" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M521.584 320H203.341L201.946 0L0 0.88L0.539062 124.65C1.01402 233.69 87.826 322.18 195.932 325.62C198.81 434.64 288.091 522.13 397.809 522.13H521.584V320Z" fill="#50B3D5"/>
        </svg>
      </div>

      <div class="container section-c-container">
        <div class="section-c-grid">
          <!-- Left Column: Interactive Visual Animation Stage -->
          <div class="section-c-visual-col" data-reveal="left">
            <div 
              class="section-c-stage-wrapper"
              @mouseenter="pauseSlideC"
              @mouseleave="resumeSlideC"
            >
              <!-- Background Frame SVG -->
              <div class="section-c-bg-frame">
                <svg class="section-c-bg-svg" viewBox="0 0 1885 2013" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M202.001 526C202.001 305.086 381.087 126 602.001 126H1759V1410C1759 1630.91 1579.91 1810 1359 1810H202.001V526Z" fill="#033445"/>
                  <path d="M521.584 1810.18H203.342L201.947 1490L0 1490.88L0.539062 1614.65C1.01402 1723.69 87.8267 1812.18 195.933 1815.62C198.811 1924.64 288.092 2012.13 397.81 2012.13H521.584V1810.18Z" fill="#50B3D5"/>
                  <path d="M1559 126.087H1757.7L1758.57 325.995L1884.65 325.446L1884.32 248.168C1884.02 180.087 1829.82 124.843 1762.32 122.695C1760.52 54.6274 1704.78 0.000117865 1636.28 0H1559V126.087Z" fill="#50B3D5"/>
                </svg>
              </div>

              <!-- Slides Viewport Layer -->
              <div class="section-c-slides-viewport" :class="{ 'is-exiting': isExitingSlideC }">
                <!-- SLIDE 1: SECURITY GATE ENTRY PASS -->
                <div v-show="activeSlideC === 1" class="section-c-slide slide-1" :class="{ active: activeSlideC === 1 }">
                  <img 
                    src="/assets/images/section-c/slider-1.png" 
                    alt="Security Gate Verification" 
                    class="section-c-slide-img"
                  />
                </div>

                <!-- SLIDE 2: ACCESS RECORDS & MOVEMENT LOG -->
                <div v-show="activeSlideC === 2" class="section-c-slide slide-2" :class="{ active: activeSlideC === 2 }">
                  <img 
                    src="/assets/images/section-c/slider-2.png" 
                    alt="Access Granted Movement Records" 
                    class="section-c-slide-img"
                  />
                </div>
              </div>

              <!-- Slide Indicator (Exact proportions and colors from Slider indicator.svg: 207 x 60) -->
              <div class="section-c-indicator" role="tablist" aria-label="Section C Slide Navigation">
                <button
                  v-for="idx in 2"
                  :key="idx"
                  type="button"
                  class="indicator-c-item"
                  :class="{ active: activeSlideC === idx }"
                  :aria-selected="activeSlideC === idx"
                  :aria-label="`Go to slide ${idx}`"
                  @click="setSlideC(idx)"
                >
                  <span class="indicator-c-shape"></span>
                </button>
              </div>

            </div>
          </div>

          <!-- Right Column: Heading & Descriptive Copy -->
          <div class="section-c-content-col" data-reveal="right">
            <div class="section-c-content-inner">
              <h2 class="section-c-title">
                Stay Ahead of<br>
                Every <span class="section-c-title-accent">Entry.</span>
              </h2>
              <p class="section-c-description">
                Give your security team a reliable way to check visitor passes,<br class="sec-c-sub-br">
                confirm authorised access and keep records of movement in<br class="sec-c-sub-br">
                and out of your premises.
              </p>
              <a href="#features" class="section-cta-link sec-c-cta" onclick="showPage('features');return false">
                <span>Learn more</span>
                <svg class="cta-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                  <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </a>
            </div>
          </div>

        </div>
      </div>
    </section>
  </div>

    <!-- CORE SYSTEM HEADER (2-Column Web View Layout) -->
  <section class="core-control-section" id="core-systems">
    <div class="container">
      <div class="core-control-grid" data-reveal>
        <!-- Left Column: Primary Green Heading -->
        <div class="core-control-col-left">
          <h2 class="core-control-title">
            Total Control Over<br>
            Every Gate, Payment,<br>
            and Resident Record.
          </h2>
        </div>

        <!-- Right Column: Subheader & Primary CTA -->
        <div class="core-control-col-right">
          <p class="core-control-desc">
            Whether it's a residential estate, gated community, or workplace, manage occupants, assign responsibilities, organise payments, and oversee daily operations from one dashboard.
          </p>
          <a href="#how-it-works" class="core-control-btn" onclick="showPage('how-it-works');return false">
            <span>See how it works</span>
            <svg class="core-control-btn-arrow" width="18" height="18" viewBox="0 0 18 18" fill="none" aria-hidden="true">
              <path d="M3.75 9h10.5M9.75 4.5l4.5 4.5-4.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- STAKEHOLDERS INTERACTIVE SHOWCASE (SITEASSIST MODEL - 3 SLIDES) -->
  <section class="features-showcase-section" id="stakeholder-showcase" style="padding-top:40px">
    <div class="container">
      <div class="stakeholder-header-grid" data-reveal>
        <div class="stakeholder-header-col-left">
          <h2 class="stakeholder-header-title">
            The Right People.<br>
            The Right Access.
          </h2>
        </div>
        <div class="stakeholder-header-col-right">
          <p class="stakeholder-header-desc">
            Everything is easier when visitor access, dues, and updates are right on your pocket.
          </p>
        </div>
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
            
          </div>

          <!-- Lower Right Floating Content Card -->
          <div class="siteassist-content-wrap" id="saContentBox">
            <h3 class="siteassist-feature-title" id="saTitle">Run Your Estate Without Missing Records or Payment Disputes</h3>
            <p class="siteassist-feature-body" id="saBody">Create service charges and diesel levies in seconds, track paid versus defaulting units in real time, and export clean financial reports ready for your next AGM.</p>
          </div>

          <!-- Bottom Nav Strip with Horizontal Progress Loaders -->
          <div class="siteassist-tabs-nav">
            <button class="siteassist-tab-btn active" onclick="switchSiteAssistTab(0)">
              <span class="tab-lbl-desktop">For Estate Managers</span>
              <span class="tab-lbl-mobile">Managers</span>
              <div class="siteassist-progress-track"><div class="siteassist-progress-bar" id="saFill-0"></div></div>
            </button>
            <button class="siteassist-tab-btn" onclick="switchSiteAssistTab(1)">
              <span class="tab-lbl-desktop">For Security Guards</span>
              <span class="tab-lbl-mobile">Guards</span>
              <div class="siteassist-progress-track"><div class="siteassist-progress-bar" id="saFill-1"></div></div>
            </button>
            <button class="siteassist-tab-btn" onclick="switchSiteAssistTab(2)">
              <span class="tab-lbl-desktop">For Residents</span>
              <span class="tab-lbl-mobile">Residents</span>
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

  <!-- S9: TESTIMONIALS (Disabled until real reviews are collected; set showTestimonials to true to re-enable) -->
  <section v-if="showTestimonials" class="testimonials">
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
          <button class="btn btn-gold btn-lg" onclick="window.goToRegister()">
            Create Free Account
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <button class="btn btn-ghost btn-lg" style="color:#ffffff;border-color:rgba(255,255,255,.3)" onclick="showPage('contact')">
            Speak With Our Team
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
      <span class="badge badge-white label" style="margin-bottom:18px;display:inline-flex">Get in Touch</span>
      <h1>Speak With Our Estate Onboarding Team</h1>
      <p>Have questions about estate deployment, custom integrations, or want an assisted walkthrough for your Exco and security personnel? We are here to help.</p>
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
              <a class="social-btn icon-only-btn" href="https://x.com/checkpassng" target="_blank" rel="noopener noreferrer" aria-label="X (Twitter)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFFFFF"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.747l7.73-8.835L1.254 2.25H8.08l4.259 5.63L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z"/></svg>
              </a>
              <a class="social-btn icon-only-btn" href="https://www.instagram.com/checkpass.ng/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4.5"/><circle cx="17.5" cy="6.5" r="1.2" fill="#FFFFFF" stroke="none"/></svg>
              </a>
              <a class="social-btn icon-only-btn" href="https://linkedin.com/company/checkpassng" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFFFFF"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/></svg>
              </a>
              <a class="social-btn icon-only-btn" href="https://facebook.com/checkpassng" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFFFFF"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
              </a>
              <a class="social-btn icon-only-btn" href="https://wa.me/2348000000000" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#FFFFFF"><path d="M17.472 14.382c-.301-.15-1.78-.878-2.056-.979-.276-.1-.476-.15-.677.15-.2.301-.777.979-.953 1.18-.175.2-.351.226-.652.075-.3-.15-1.267-.467-2.414-1.488-.893-.796-1.496-1.779-1.671-2.08-.175-.3-.019-.463.132-.613.136-.134.301-.351.451-.527.151-.175.201-.301.301-.501.1-.2.05-.376-.025-.526-.075-.15-.677-1.633-.928-2.238-.244-.589-.493-.509-.677-.518l-.577-.01c-.2 0-.527.075-.802.376-.276.3-1.053 1.03-1.053 2.511 0 1.482 1.078 2.912 1.228 3.113.15.2 2.122 3.24 5.141 4.542.718.31 1.279.495 1.716.634.721.229 1.377.197 1.895.12.577-.087 1.78-.727 2.03-1.43.25-.702.25-1.304.175-1.43-.075-.125-.276-.201-.577-.351zM12.04 2C6.53 2 2.06 6.47 2.06 11.98c0 1.76.46 3.48 1.34 5.01L2 22l5.18-1.36c1.48.81 3.15 1.24 4.86 1.24 5.51 0 9.98-4.47 9.98-9.98C22.02 6.47 17.55 2 12.04 2z"/></svg>
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
<footer class="landing-footer">
  <div class="container footer-container">
    <div class="footer-inner">
      <div class="footer-brand">
        <div class="footer-logo-wrap" onclick="showPage('home')" style="cursor:pointer">
          <img class="footer-brand-logo" src="/checkpass-logo-white.svg" alt="Checkpass">
        </div>
        <p class="footer-tagline">Estate operations &amp; access management. Without the chaos.</p>
        <p class="footer-sub">A product of Sheffy Technologies</p>
        <div class="footer-social">
          <a href="https://www.instagram.com/checkpass.ng/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
            <img src="/social-media-icons/Instagram.svg" alt="Instagram" width="18" height="18" />
          </a>
          <a href="https://facebook.com/checkpassng" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
            <img src="/social-media-icons/Facebook.svg" alt="Facebook" width="18" height="18" />
          </a>
          <a href="https://x.com/checkpassng" target="_blank" rel="noopener noreferrer" aria-label="X">
            <img src="/social-media-icons/X.svg" alt="X" width="18" height="18" />
          </a>
          <a href="https://linkedin.com/company/checkpassng" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
            <img src="/social-media-icons/linkedin.svg" alt="LinkedIn" width="18" height="18" />
          </a>
        </div>
      </div>
      <div class="footer-col">
        <h5>Product</h5>
        <ul>
          <li><a href="#" onclick="showPage('how-it-works');return false">How It Works</a></li>
          <li><a href="#" onclick="showPage('features');return false">Features</a></li>
          <li><a href="#" onclick="showPage('pricing');return false">Pricing &amp; Plans</a></li>
          <li><a href="#" onclick="showPage('estates');return false">Supported Estates</a></li>
        </ul>
      </div>
      <div class="footer-col footer-col-contact">
        <h5>Contact &amp; Support</h5>
        <ul>
          <li><a href="mailto:support@checkpass.ng" class="footer-contact-link">support@checkpass.ng</a></li>
          <li><a href="mailto:sales@checkpass.ng" class="footer-contact-link">sales@checkpass.ng</a></li>
          <li><a href="tel:+2348002432572" class="footer-contact-link">+234 (0) 800 CHECKPASS</a></li>
          <li>
            <a href="https://wa.me/2348000000000" target="_blank" rel="noopener noreferrer" class="footer-whatsapp-link" style="display:inline-flex;align-items:center;gap:6px">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="#00C97A"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm0 1.67c2.2 0 4.27.86 5.82 2.42a8.173 8.173 0 0 1 2.41 5.82c0 4.54-3.7 8.24-8.24 8.24-1.45 0-2.87-.38-4.12-1.1l-.3-.17-3.07.8 0.82-2.99-.19-.31c-.79-1.28-1.21-2.76-1.21-4.29 0-4.54 3.7-8.24 8.24-8.24zm4.52 10.3c-.25-.13-1.47-.73-1.7-.81-.23-.08-.39-.13-.56.13-.17.25-.65.81-.79.98-.15.17-.29.19-.54.06-.25-.13-1.05-.39-2-1.23-.74-.66-1.24-1.48-1.39-1.73-.15-.25-.02-.39.11-.51.11-.11.25-.29.37-.44.13-.15.17-.25.25-.42.08-.17.04-.31-.02-.44-.06-.13-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43l-.48-.01c-.17 0-.44.06-.67.31-.23.25-.87.85-.87 2.08 0 1.23.89 2.41 1.02 2.58.12.17 1.76 2.69 4.26 3.77.6.26 1.06.41 1.42.53.6.19 1.15.16 1.58.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.08.15-1.18-.06-.1-.23-.17-.48-.29z"/></svg>
              <span>WhatsApp Chat</span>
            </a>
          </li>
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
      <p>&copy; 2026 CheckPass &middot; Sheffy Technologies. All rights reserved.</p>
      <div class="footer-legal"><a href="#">Privacy Policy</a><a href="#">Terms of Service</a></div>
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
      <h3>Log in to CheckPass</h3>
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
        Log In to Manager Portal &rarr;
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
import { ref, nextTick, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { publicApi } from '@/api'

interface PlatformStats {
  propertiesOnboarded: number
  activeResidents: number
  passesCreated: number
  entriesVerified: number
  itemsDeclared: number
}

const pageLoading = ref(true)
const isMobileNavOpen = ref(false)

// ── SECTION A (TAKE CHARGE OF YOUR PROPERTY) SLIDER ENGINE ──
const activeSlideA = ref(1)
const isExitingSlideA = ref(false)
const isHoveredSlideA = ref(false)
let slideATimer: ReturnType<typeof setInterval> | null = null

function setSlideA(idx: number) {
  if (idx === activeSlideA.value) return
  isExitingSlideA.value = true
  setTimeout(() => {
    activeSlideA.value = idx
    isExitingSlideA.value = false
  }, 220)
}

function nextSlideA() {
  const next = activeSlideA.value >= 4 ? 1 : activeSlideA.value + 1
  setSlideA(next)
}

function startSlideATimer() {
  if (slideATimer) clearInterval(slideATimer)
  slideATimer = setInterval(() => {
    if (!isHoveredSlideA.value) {
      nextSlideA()
    }
  }, 4200)
}

function pauseSlideA() {
  isHoveredSlideA.value = true
}

function resumeSlideA() {
  isHoveredSlideA.value = false
}

// ── SECTION B (MANAGE VISITORS & PAYMENTS) SLIDER ENGINE ──
const activeSlideB = ref(1)
const isExitingSlideB = ref(false)
const isHoveredSlideB = ref(false)
let slideBTimer: ReturnType<typeof setInterval> | null = null

function setSlideB(idx: number) {
  if (idx === activeSlideB.value) return
  isExitingSlideB.value = true
  setTimeout(() => {
    activeSlideB.value = idx
    isExitingSlideB.value = false
  }, 180)
}

function nextSlideB() {
  const next = activeSlideB.value >= 2 ? 1 : activeSlideB.value + 1
  setSlideB(next)
}

function startSlideBTimer() {
  if (slideBTimer) clearInterval(slideBTimer)
  slideBTimer = setInterval(() => {
    if (!isHoveredSlideB.value) {
      nextSlideB()
    }
  }, 4400)
}

function pauseSlideB() {
  isHoveredSlideB.value = true
}

function resumeSlideB() {
  isHoveredSlideB.value = false
}

// ── SECTION C (STAY AHEAD OF EVERY ENTRY) SLIDER ENGINE ──
const activeSlideC = ref(1)
const isExitingSlideC = ref(false)
const isHoveredSlideC = ref(false)
let slideCTimer: ReturnType<typeof setInterval> | null = null

function setSlideC(idx: number) {
  if (idx === activeSlideC.value) return
  isExitingSlideC.value = true
  setTimeout(() => {
    activeSlideC.value = idx
    isExitingSlideC.value = false
  }, 180)
}

function nextSlideC() {
  const next = activeSlideC.value >= 2 ? 1 : activeSlideC.value + 1
  setSlideC(next)
}

function startSlideCTimer() {
  if (slideCTimer) clearInterval(slideCTimer)
  slideCTimer = setInterval(() => {
    if (!isHoveredSlideC.value) {
      nextSlideC()
    }
  }, 4400)
}

function pauseSlideC() {
  isHoveredSlideC.value = true
}

function resumeSlideC() {
  isHoveredSlideC.value = false
}
// Set to true once verified real customer testimonials are collected from testing
const showTestimonials = ref(false)

// Real-time Central Platform Stats (Live totals queried from database)
const platformStats = ref<PlatformStats>({
  propertiesOnboarded: 1, // Active onboarded estate
  activeResidents: 2,     // Active registered residents
  passesCreated: 3,       // Passes created
  entriesVerified: 0,     // Verified gate entries
  itemsDeclared: 0,       // Declared items
})

async function fetchPlatformStats() {
  try {
    const res = await publicApi.getPlatformStats()
    if (res.data && typeof res.data.propertiesOnboarded === 'number') {
      platformStats.value = {
        propertiesOnboarded: Number(res.data.propertiesOnboarded) || 0,
        activeResidents: Number(res.data.activeResidents) || 0,
        passesCreated: Number(res.data.passesCreated) || 0,
        entriesVerified: Number(res.data.entriesVerified) || 0,
        itemsDeclared: Number(res.data.itemsDeclared) || 0,
      }
    }
  } catch (e) {
    console.debug('Live platform-stats API pending deployment; displaying live system baseline:', e)
  }
  await nextTick()
  if (typeof (window as any).initCounters === 'function') {
    (window as any).initCounters()
  }
  if (typeof (window as any).animateCounter === 'function') {
    document.querySelectorAll<HTMLElement>('.stats-strip [data-count]').forEach(el => {
      (window as any).animateCounter(el)
    })
  }
}

function toggleMobileNav() {
  isMobileNavOpen.value = !isMobileNavOpen.value
  const el = document.getElementById('mobileNav')
  if (el) el.classList.toggle('open', isMobileNavOpen.value)
}

function closeMobileNav() {
  isMobileNavOpen.value = false
  const el = document.getElementById('mobileNav')
  if (el) el.classList.remove('open')
}

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
  (window as any).toggleMobileNav = toggleMobileNav;
  (window as any).closeMobileNav = closeMobileNav;
}

onMounted(() => {
  startSlideATimer()
  startSlideBTimer()
  startSlideCTimer()
  fetchPlatformStats()

  let styleEl = document.getElementById('landing-styles') as HTMLLinkElement
  if (!styleEl) {
    styleEl = document.createElement('link')
    styleEl.id = 'landing-styles'
    styleEl.rel = 'stylesheet'
    styleEl.href = `/landing.css?v=${Date.now()}`
    styleEl.onload = () => {
      setTimeout(() => {
        pageLoading.value = false
      }, 250)
    }
    document.head.appendChild(styleEl)
  } else {
    styleEl.href = `/landing.css?v=${Date.now()}`
    setTimeout(() => {
      pageLoading.value = false
    }, 250)
  }

  // Safety fallback so loader never hangs
  setTimeout(() => {
    pageLoading.value = false
  }, 1200)

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
      script.textContent = "\r\ngsap.registerPlugin(ScrollTrigger);\r\nlet activePage='home',heroVideoFadeMs=450,heroVideoCycleStarted=false,heroVideoIndex=0,heroVideoTransitioning=false;\r\nlet currentBillingCycle = 'monthly';\r\nlet currentLoginRole = 'manager';\r\n\r\nfunction showPage(id, plan){\r\n  const next=document.getElementById('page-'+id);\r\n  if(!next)return;\r\n  document.querySelectorAll('.page').forEach(p => { if(p !== next) p.classList.remove('active', 'entering'); });\r\n  next.classList.add('active','entering');\r\n  setTimeout(()=>next.classList.remove('entering'),420);\r\n  activePage=id;\r\n  window.scrollTo({top:0,behavior:'instant'});\r\n  updateNavStyle(id);setActiveNavLink(id);\r\n  setTimeout(()=>initReveal(),80);\r\n  if(id==='home')setTimeout(initHeroAnim,60);\r\n  if(id==='contact' && plan){\r\n    const ps = document.getElementById('contactPlanSelect');\r\n    if(ps && ps.querySelector(`option[value=\"${plan}\"]`)){\r\n      ps.value = plan;\r\n    }\r\n  }\r\n  closeMobileNav();\r\n}\r\n\r\nfunction setActiveNavLink(id){\r\n  document.querySelectorAll('.nav-link').forEach(l=>l.classList.remove('active-nav'));\r\n  const map={'how-it-works':0,'features':1,'pricing':2,'estates':3,'contact':4};\r\n  const idx=map[id];\r\n  if(idx!==undefined){const links=document.querySelectorAll('.nav-links .nav-link');if(links[idx])links[idx].classList.add('active-nav');}\r\n}\r\n\r\nfunction updateNavStyle(pid){\r\n  const nav=document.getElementById('navbar');\r\n  if(!nav)return;\r\n  nav.classList.remove('nav-hidden','hero-nav','dark-nav','scrolled');\r\n  nav.classList.add(window.scrollY>60?'scrolled':pid==='home'?'hero-nav':'dark-nav');\r\n}\r\n\r\nlet lastScrollY = window.pageYOffset || document.documentElement.scrollTop;\r\nlet navScrollTicking = false;\r\nconst NAV_SCROLL_DELTA = 6;\r\n\r\nfunction handleSmartNavScroll(){\r\n  const nav = document.getElementById('navbar');\r\n  if(!nav) return;\r\n  const currentY = Math.max(0, window.pageYOffset || document.documentElement.scrollTop);\r\n  const delta = currentY - lastScrollY;\r\n  const mobileNav = document.getElementById('mobileNav');\r\n  const isMobileOpen = mobileNav && mobileNav.classList.contains('open');\r\n\r\n  if(isMobileOpen){\r\n    nav.classList.remove('nav-hidden');\r\n    nav.classList.add('scrolled');\r\n    lastScrollY = currentY;\r\n    return;\r\n  }\r\n\r\n  if(currentY <= 40){\r\n    nav.classList.remove('nav-hidden','scrolled');\r\n    nav.classList.add(activePage === 'home' ? 'hero-nav' : 'dark-nav');\r\n  } else if(delta > NAV_SCROLL_DELTA && currentY > 70){\r\n    nav.classList.add('nav-hidden');\r\n  } else if(delta < -NAV_SCROLL_DELTA){\r\n    nav.classList.remove('nav-hidden','hero-nav','dark-nav');\r\n    nav.classList.add('scrolled');\r\n  }\r\n  lastScrollY = currentY;\r\n}\r\n\r\nwindow.addEventListener('scroll', ()=>{\r\n  if(!navScrollTicking){\r\n    window.requestAnimationFrame(()=>{\r\n      handleSmartNavScroll();\r\n      navScrollTicking = false;\r\n    });\r\n    navScrollTicking = true;\r\n  }\r\n}, {passive:true});\r\n\r\nfunction toggleMobileNav(){const m=document.getElementById('mobileNav');const h=document.getElementById('hamburger');if(m)m.classList.toggle('open');if(h)h.classList.toggle('open');}\r\nfunction closeMobileNav(){const m=document.getElementById('mobileNav');const h=document.getElementById('hamburger');if(m)m.classList.remove('open');if(h)h.classList.remove('open');}\r\n\r\nfunction initReveal(){\r\n  ScrollTrigger.getAll().forEach(t=>t.kill());\r\n  const page=document.querySelector('.page.active');\r\n  if(!page)return;\r\n  // HIW steps: staggered group entrance\r\n  const hiwSteps=page.querySelectorAll('.hiw-step');\r\n  if(hiwSteps.length){\r\n    const hiwWrap=page.querySelector('.hiw-steps');\r\n    gsap.fromTo(hiwSteps,{opacity:0,y:32},{opacity:1,y:0,duration:.7,stagger:.15,ease:'power3.out',scrollTrigger:{trigger:hiwWrap||hiwSteps[0],start:'top 82%',once:true}});\r\n  }\r\n  // General section reveals\r\n  page.querySelectorAll('[data-reveal]').forEach(el=>{\r\n    if(el.classList.contains('hiw-step'))return;\r\n    const dir=el.dataset.reveal;const d=(parseInt(el.dataset.delay||'0'))*0.12;\r\n    const base={duration:.65,delay:d,ease:'power3.out'};\r\n    let from,to;\r\n    if(dir==='left'){from={opacity:0,x:-28};to={...base,opacity:1,x:0};}\r\n    else if(dir==='right'){from={opacity:0,x:28};to={...base,opacity:1,x:0};}\r\n    else if(dir==='scale'){from={opacity:0,scale:.95};to={...base,opacity:1,scale:1};}\r\n    else{from={opacity:0,y:24};to={...base,opacity:1,y:0};}\r\n    gsap.fromTo(el,from,{...to,scrollTrigger:{trigger:el,start:'top 88%',once:true}});\r\n  });\r\n  // HIW connector line draw\r\n  const conn=page.querySelector('.hiw-connector');\r\n  if(conn)gsap.fromTo(conn,{scaleX:0},{scaleX:1,duration:.9,ease:'power2.inOut',transformOrigin:'left center',scrollTrigger:{trigger:conn,start:'top 85%',once:true}});\r\n  ScrollTrigger.refresh();\r\n}\r\n\r\nfunction animateCounter(el){\r\n  const raw=el.dataset.count;if(!raw)return;\r\n  const target=parseInt(raw);const suffix=raw.replace(String(target),'');\r\n  const dur=1800;const t0=performance.now();\r\n  (function tick(now){const p=Math.min((now-t0)/dur,1);const e=1-Math.pow(1-p,3);el.textContent=Math.round(e*target)+suffix;if(p<1)requestAnimationFrame(tick);})(t0);\r\n}\r\nwindow.animateCounter = animateCounter;\r\nwindow.initCounters = function initCounters(){\r\n  const io=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){animateCounter(e.target);io.unobserve(e.target);}});},{threshold:0.5});\r\n  document.querySelectorAll('[data-count]').forEach(el=>io.observe(el));\r\n}\r\n\r\nfunction toggleFaq(el){\r\n  const item=el.closest('.faq-item');const was=item.classList.contains('open');\r\n  document.querySelectorAll('.faq-item').forEach(i=>i.classList.remove('open'));\r\n  if(!was)item.classList.add('open');\r\n}\r\n\r\nfunction handleFormSubmit(e){\r\n  e.preventDefault();\r\n  document.getElementById('contactForm').style.display='none';\r\n  document.getElementById('formSuccess').style.display='block';\r\n}\r\n\r\nfunction initHeroAnim(){\r\n  const home=document.getElementById('page-home');\r\n  if(!home||!home.classList.contains('active'))return;\r\n  const items=home.querySelectorAll('.hero-animate');\r\n  items.forEach(i=>i.classList.remove('is-visible'));\r\n  requestAnimationFrame(()=>items.forEach(i=>i.classList.add('is-visible')));\r\n}\r\n\r\nfunction setupHeroVideoSequence(){\r\n  if(heroVideoCycleStarted)return;\r\n  const videos=[document.getElementById('heroVideo1'),document.getElementById('heroVideo2')].filter(Boolean);\r\n  if(videos.length!==2)return;\r\n  videos.forEach(v=>{v.muted=true;v.playsInline=true;v.setAttribute('muted','');v.setAttribute('playsinline','');});\r\n  const play=i=>{const v=videos[i];if(!v)return;v.currentTime=0;const p=v.play();if(p&&p.catch)p.catch(()=>{});};\r\n  const nextVid=()=>{\r\n    if(heroVideoTransitioning)return;heroVideoTransitioning=true;\r\n    const cur=videos[heroVideoIndex];const ni=(heroVideoIndex+1)%videos.length;const nxt=videos[ni];\r\n    if(!cur||!nxt){heroVideoTransitioning=false;return;}\r\n    nxt.currentTime=0;const p=nxt.play();if(p&&p.catch)p.catch(()=>{});\r\n    nxt.classList.add('is-active');cur.classList.remove('is-active');\r\n    setTimeout(()=>{cur.pause();cur.currentTime=0;heroVideoIndex=ni;heroVideoTransitioning=false;},heroVideoFadeMs);\r\n  };\r\n  videos.forEach((v,i)=>{\r\n    v.addEventListener('timeupdate',()=>{if(i!==heroVideoIndex||heroVideoTransitioning||!v.duration)return;if(v.duration-v.currentTime<=heroVideoFadeMs/1000+0.05)nextVid();});\r\n    v.addEventListener('ended',()=>{if(i===heroVideoIndex)nextVid();});\r\n  });\r\n  play(0);heroVideoCycleStarted=true;\r\n}\r\n\r\n/* Scrollytelling How It Works: Smooth Scroll & Active Scroll-Spy */\r\nfunction scrollToHowStep(stepId){\r\n  const el = document.getElementById(stepId);\r\n  if(el){\r\n    const yOffset = -140; // account for navbar + sticky pill bar\r\n    const y = el.getBoundingClientRect().top + window.pageYOffset + yOffset;\r\n    window.scrollTo({top: y, behavior: 'smooth'});\r\n  }\r\n}\r\n\r\n// Backwards-compatible helper\r\nfunction goToHowStep(step){\r\n  scrollToHowStep('how-step-' + step);\r\n}\r\n\r\nfunction setHowMode(mode){\r\n  const map = { lifecycle: 'how-step-1', resident: 'how-step-1', guard: 'how-step-3', admin: 'how-step-4' };\r\n  if(map[mode]) scrollToHowStep(map[mode]);\r\n}\r\n\r\n// Scroll-Spy to dynamically highlight active step in sticky progress bar\r\nwindow.addEventListener('scroll', () => {\r\n  if(typeof activePage !== 'undefined' && activePage !== 'how-it-works') return;\r\n  const steps = ['how-step-1', 'how-step-2', 'how-step-3', 'how-step-4', 'how-step-5'];\r\n  const scrollPos = window.scrollY + 220;\r\n\r\n  for(let i = steps.length - 1; i >= 0; i--){\r\n    const sec = document.getElementById(steps[i]);\r\n    if(sec && sec.offsetTop <= scrollPos){\r\n      document.querySelectorAll('.how-step-pill-btn').forEach(btn => btn.classList.remove('active'));\r\n      const activeBtn = document.getElementById('pill-' + steps[i]);\r\n      if(activeBtn) activeBtn.classList.add('active');\r\n      break;\r\n    }\r\n  }\r\n}, {passive: true});\r\n\r\n/* Estate Deployment & Scope Planner (Consultation Model) */\r\nfunction updatePricingSlider(units){\r\n  const count = parseInt(units);\r\n  const display = document.getElementById('sliderUnitCount');\r\n  const tierBadge = document.getElementById('calcTierBadge');\r\n  const totalDisplay = document.getElementById('calcTotal');\r\n  const annualSub = document.getElementById('calcAnnualSub');\r\n  const actionBtn = document.getElementById('calcActionBtn');\r\n  const maxResidents = count * 3;\r\n\r\n  if(display) display.textContent = count + (count === 1 ? ' Unit' : ' Units') + ' (up to ' + maxResidents + ' residents)';\r\n\r\n  if(count <= 50){\r\n    if(tierBadge) tierBadge.textContent = 'Boutique Estate Scope (' + count + ' Units)';\r\n    if(totalDisplay) totalDisplay.textContent = '1 Gate Terminal + 1 Admin Portal';\r\n    if(annualSub) annualSub.textContent = 'Recommended for private closes & courts. Includes 1-week managed onboarding, guard orientation, and visitor pass setup.';\r\n    if(actionBtn) {\r\n      actionBtn.textContent = 'Request Quote for ' + count + ' Units';\r\n      actionBtn.onclick = () => {\r\n        showPage('contact', 'boutique');\r\n        const inp = document.getElementById('contactUnitsInput');\r\n        if(inp) inp.value = count + ' units';\r\n      };\r\n    }\r\n  } else if(count <= 300){\r\n    const terminals = count <= 150 ? '2 Gate Terminals' : '3 Gate Terminals';\r\n    if(tierBadge) tierBadge.textContent = 'Gated Residential Scope (' + count + ' Units)';\r\n    if(totalDisplay) totalDisplay.textContent = terminals + ' + Multi-Zone Sync';\r\n    if(annualSub) annualSub.textContent = 'Includes automated dues ledger, anti-theft item tracking, and dedicated guard physical drills.';\r\n    if(actionBtn) {\r\n      actionBtn.textContent = 'Request Proposal for ' + count + ' Units';\r\n      actionBtn.onclick = () => {\r\n        showPage('contact', 'residential');\r\n        const inp = document.getElementById('contactUnitsInput');\r\n        if(inp) inp.value = count + ' units';\r\n      };\r\n    }\r\n  } else {\r\n    if(tierBadge) tierBadge.textContent = 'Master Planned Estate (' + count + ' Units)';\r\n    if(totalDisplay) totalDisplay.textContent = 'Multi-Gate Network + Turnstile Integration';\r\n    if(annualSub) annualSub.textContent = 'Includes executive AGM reporting, SLA guarantee, and custom multi-zone security hierarchy.';\r\n    if(actionBtn) {\r\n      actionBtn.textContent = 'Speak with Enterprise Team (' + count + ' Units)';\r\n      actionBtn.onclick = () => {\r\n        showPage('contact', 'enterprise');\r\n        const inp = document.getElementById('contactUnitsInput');\r\n        if(inp) inp.value = count + ' units';\r\n      };\r\n    }\r\n  }\r\n}\r\n\r\n/* Demo Video Modal Functions */\r\nfunction openDemoModal(){\r\n  const modal = document.getElementById('demoModal');\r\n  const video = document.getElementById('demoVideoPlayer');\r\n  if(modal){\r\n    modal.style.display = 'flex';\r\n    requestAnimationFrame(()=>modal.classList.add('open'));\r\n    if(video){\r\n      video.currentTime = 0;\r\n      video.play().catch(()=>{});\r\n    }\r\n  }\r\n}\r\n\r\nfunction closeDemoModal(){\r\n  const modal = document.getElementById('demoModal');\r\n  const video = document.getElementById('demoVideoPlayer');\r\n  if(modal){\r\n    modal.classList.remove('open');\r\n    if(video) video.pause();\r\n    setTimeout(()=>modal.style.display = 'none', 300);\r\n  }\r\n}\r\n\r\n/* Login Modal Functions */\r\nfunction openLoginModal(role){\r\n  if(role) setLoginRole(role);\r\n  const modal = document.getElementById('loginModal');\r\n  if(modal){\r\n    modal.style.display = 'flex';\r\n    requestAnimationFrame(()=>modal.classList.add('open'));\r\n  }\r\n}\r\n\r\nfunction closeLoginModal(){\r\n  const modal = document.getElementById('loginModal');\r\n  if(modal){\r\n    modal.classList.remove('open');\r\n    setTimeout(()=>modal.style.display = 'none', 300);\r\n  }\r\n}\r\n\r\nfunction setLoginRole(role){\r\n  currentLoginRole = role;\r\n  const tM = document.getElementById('roleTabManager');\r\n  const tR = document.getElementById('roleTabResident');\r\n  const tS = document.getElementById('roleTabSecurity');\r\n  const uLbl = document.getElementById('loginUserLabel');\r\n  const pLbl = document.getElementById('loginPassLabel');\r\n  const idInput = document.getElementById('loginIdentifier');\r\n  const passInput = document.getElementById('loginPassword');\r\n  const submitBtn = document.getElementById('loginSubmitBtn');\r\n\r\n  [tM, tR, tS].forEach(t => t && t.classList.remove('active'));\r\n\r\n  if(role === 'manager'){\r\n    if(tM) tM.classList.add('active');\r\n    if(uLbl) uLbl.textContent = 'Admin Email Address';\r\n    if(pLbl) pLbl.textContent = 'Password';\r\n    if(idInput) { idInput.placeholder = 'manager@estate.ng'; idInput.value = 'admin@lekkigardens.ng'; }\r\n    if(passInput) passInput.value = 'password123';\r\n    if(submitBtn) submitBtn.textContent = 'Sign In to Manager Portal →';\r\n  } else if(role === 'resident'){\r\n    if(tR) tR.classList.add('active');\r\n    if(uLbl) uLbl.textContent = 'Phone Number or Email';\r\n    if(pLbl) pLbl.textContent = '4-Digit Resident PIN';\r\n    if(idInput) { idInput.placeholder = '08012345678 or name@email.com'; idInput.value = '08023456789'; }\r\n    if(passInput) passInput.value = '4821';\r\n    if(submitBtn) submitBtn.textContent = 'Sign In as Resident →';\r\n  } else if(role === 'security'){\r\n    if(tS) tS.classList.add('active');\r\n    if(uLbl) uLbl.textContent = 'Officer Username / Badge ID';\r\n    if(pLbl) pLbl.textContent = 'Gate Access PIN';\r\n    if(idInput) { idInput.placeholder = 'e.g. GUARD-GATE-01'; idInput.value = 'GUARD-MAIN-GATE'; }\r\n    if(passInput) passInput.value = '1122';\r\n    if(submitBtn) submitBtn.textContent = 'Sign In to Gate Scanner →';\r\n  }\r\n}\r\n\r\nfunction fillLoginDemo(role){\r\n  setLoginRole(role);\r\n}\r\n\r\nfunction handleLoginSubmit(e){\r\n  e.preventDefault();\r\n  const form = document.getElementById('loginForm');\r\n  const notice = document.getElementById('loginSuccessNotice');\r\n  const title = document.getElementById('loginSuccessTitle');\r\n  const desc = document.getElementById('loginSuccessDesc');\r\n\r\n  if(form) form.style.display = 'none';\r\n  if(notice) notice.style.display = 'block';\r\n\r\n  if(currentLoginRole === 'manager'){\r\n    if(title) title.textContent = 'Manager Portal Connected!';\r\n    if(desc) desc.textContent = 'Authenticated: Lekki Phase 1 Estate Admin. Dashboard active.';\r\n  } else if(currentLoginRole === 'resident'){\r\n    if(title) title.textContent = 'Welcome, Sarah O. (Flat 12B)';\r\n    if(desc) desc.textContent = 'Active Invites & Service charge receipts synced.';\r\n  } else {\r\n    if(title) title.textContent = 'Gate Scanner Active';\r\n    if(desc) desc.textContent = 'Main Gate Terminal verified and online.';\r\n  }\r\n\r\n  setTimeout(()=>{\r\n    closeLoginModal();\r\n    if(form) form.style.display = 'block';\r\n    if(notice) notice.style.display = 'none';\r\n  }, 2200);\r\n}\r\n\r\n/* SiteAssist Stakeholders Interactive Loader & Auto-Switch (3 Slides) */\r\nconst siteAssistFeatures = [\r\n  {\r\n    num: '01',\r\n    kicker: 'ESTATE MANAGERS & EXCO',\r\n    title: 'Run Your Estate Without Missing Records or Payment Disputes',\r\n    body: 'Create service charges and diesel levies in seconds, track paid versus defaulting units in real time, and export clean financial reports ready for your next AGM.'\r\n  },\r\n  {\r\n    num: '02',\r\n    kicker: 'SECURITY GUARDS & GATE TEAMS',\r\n    title: 'Clear Visitors in Seconds Without Endless Phone Calls',\r\n    body: 'Scan QR passes directly on your phone to verify guests instantly, log vehicles without paper logbooks, and inspect moving items with photo proof before opening the gate.'\r\n  },\r\n  {\r\n    num: '03',\r\n    kicker: 'RESIDENTS & HOMEOWNERS',\r\n    title: 'Invite Guests in Two Taps and Keep Receipts in One Place',\r\n    body: 'Share WhatsApp entry passes that get your visitors through the gate without delays, pay estate dues with instant receipts, and trigger emergency SOS if you ever need help.'\r\n  }\r\n];\r\n\r\nlet activeSiteAssistIdx = 0;\r\nlet siteAssistInterval = null;\r\nlet siteAssistProgress = 0;\r\nlet isSiteAssistPaused = false;\r\nconst SA_DURATION = 6000;\r\nconst SA_STEP = 50;\r\n\r\nfunction initSiteAssistAutoCycle(){\r\n  if(siteAssistInterval) clearInterval(siteAssistInterval);\r\n  siteAssistProgress = 0;\r\n  \r\n  const container = document.getElementById('siteassistContainer');\r\n  if(container && !container.__saEventsBound){\r\n    container.__saEventsBound = true;\r\n    container.addEventListener('mouseenter', () => { isSiteAssistPaused = true; });\r\n    container.addEventListener('mouseleave', () => { isSiteAssistPaused = false; });\r\n    let saTouchStartX = 0;\r\n    let saTouchStartY = 0;\r\n    container.addEventListener('touchstart', (e) => { isSiteAssistPaused = true; if(e.touches && e.touches[0]){ saTouchStartX = e.touches[0].clientX; saTouchStartY = e.touches[0].clientY; } }, {passive: true});\r\n    container.addEventListener('touchend', (e) => { isSiteAssistPaused = false; if(e.changedTouches && e.changedTouches[0]){ const dx = e.changedTouches[0].clientX - saTouchStartX; const dy = e.changedTouches[0].clientY - saTouchStartY; if(Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)){ if(dx < 0){ switchSiteAssistTab((activeSiteAssistIdx + 1) % 3); } else { switchSiteAssistTab((activeSiteAssistIdx + 2) % 3); } } } }, {passive: true});\r\n  }\r\n\r\n  for(let i = 0; i < 3; i++){\r\n    const fill = document.getElementById('saFill-' + i);\r\n    if(fill) fill.style.width = '0%';\r\n  }\r\n\r\n  siteAssistInterval = setInterval(()=>{\r\n    if(isSiteAssistPaused) return;\r\n    siteAssistProgress += (SA_STEP / SA_DURATION) * 100;\r\n    \r\n    const fill = document.getElementById('saFill-' + activeSiteAssistIdx);\r\n    if(fill) fill.style.width = Math.min(siteAssistProgress, 100) + '%';\r\n    \r\n    if(siteAssistProgress >= 100){\r\n      siteAssistProgress = 0;\r\n      switchSiteAssistTab((activeSiteAssistIdx + 1) % 3, true);\r\n    }\r\n  }, SA_STEP);\r\n}\r\n\r\nfunction switchSiteAssistTab(idx, isAuto){\r\n  activeSiteAssistIdx = idx;\r\n  siteAssistProgress = 0;\r\n  \r\n  const item = siteAssistFeatures[idx];\r\n  const kickerEl = document.getElementById('saKicker');\r\n  const counterEl = document.getElementById('saCounter');\r\n  const titleEl = document.getElementById('saTitle');\r\n  const bodyEl = document.getElementById('saBody');\r\n  \r\n  if(kickerEl){\r\n    kickerEl.textContent = item.kicker;\r\n    kickerEl.style.animation = 'none';\r\n    kickerEl.offsetHeight;\r\n    kickerEl.style.animation = 'saKickerFade 0.45s ease forwards';\r\n  }\r\n  if(counterEl){\r\n    counterEl.textContent = item.num;\r\n    counterEl.style.animation = 'none';\r\n    counterEl.offsetHeight;\r\n    counterEl.style.animation = 'saKickerFade .38s ease forwards';\r\n  }\r\n  if(titleEl){\r\n    titleEl.textContent = item.title;\r\n    titleEl.style.animation = 'none';\r\n    titleEl.offsetHeight;\r\n    titleEl.style.animation = 'saHeadlineFadeUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards';\r\n  }\r\n  if(bodyEl){\r\n    bodyEl.textContent = item.body;\r\n    bodyEl.style.animation = 'none';\r\n    bodyEl.offsetHeight;\r\n    bodyEl.style.animation = 'saBodyFadeUp 0.95s cubic-bezier(0.16, 1, 0.3, 1) 0.22s both';\r\n  }\r\n\r\n  // Update background slides & tabs\r\n  for(let i = 0; i < 3; i++){\r\n    const slide = document.getElementById('saSlide-' + i);\r\n    const fill = document.getElementById('saFill-' + i);\r\n    const btns = document.querySelectorAll('.siteassist-tab-btn');\r\n    \r\n    if(slide) slide.classList.toggle('active', i === idx);\r\n    if(btns[i]) btns[i].classList.toggle('active', i === idx);\r\n    if(fill) fill.style.width = '0%';\r\n  }\r\n}\r\n\r\ndocument.addEventListener('click',e=>{\r\n  const btn=e.target.closest('.btn');if(!btn)return;\r\n  const r=document.createElement('span');const rect=btn.getBoundingClientRect();const size=Math.max(rect.width,rect.height);\r\n  r.style.cssText=`position:absolute;width:${size}px;height:${size}px;border-radius:50%;background:rgba(255,255,255,.2);transform:scale(0);animation:ripple .5s ease;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px;pointer-events:none`;\r\n  if(getComputedStyle(btn).position==='static')btn.style.position='relative';\r\n  btn.style.overflow='hidden';btn.appendChild(r);setTimeout(()=>r.remove(),550);\r\n});\r\nconst rs=document.createElement('style');rs.textContent='@keyframes ripple{to{transform:scale(2.5);opacity:0}}';document.head.appendChild(rs);\r\n\r\nfunction bootLanding(){\r\n  updateNavStyle('home');\r\n  if(typeof initReveal === 'function') initReveal();\r\n  if(typeof initCounters === 'function') initCounters();\r\n  if(typeof updatePricingSlider === 'function') updatePricingSlider(60);\r\n  initSiteAssistAutoCycle();\r\n}\r\nif(document.readyState === 'loading'){\r\n  document.addEventListener('DOMContentLoaded', bootLanding);\r\n}else{\r\n  bootLanding();\r\n}\r\nwindow.initSiteAssistAutoCycle = initSiteAssistAutoCycle;\r\nwindow.switchSiteAssistTab = switchSiteAssistTab;\r\n"
      document.body.appendChild(script)
    } catch (err) {
      console.warn('Landing script init error:', err)
    }
  })
})

onUnmounted(() => {
  if (slideATimer) clearInterval(slideATimer)
  if (slideBTimer) clearInterval(slideBTimer)
  if (slideCTimer) clearInterval(slideCTimer)
  const styleEl = document.getElementById('landing-styles')
  if (styleEl) styleEl.remove()
  const scriptEl = document.getElementById('landing-inline-script')
  if (scriptEl) scriptEl.remove()
})
</script>

<style scoped>

/* ── Full-Screen Loading Cover ── */
.landing-screen-loader {
  position: fixed;
  inset: 0;
  z-index: 999999;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
}
.landing-loader-center {
  display: flex;
  align-items: center;
  justify-content: center;
}
.fade-landing-loader-enter-active,
.fade-landing-loader-leave-active {
  transition: opacity 0.35s ease;
}
.fade-landing-loader-enter-from,
.fade-landing-loader-leave-to {
  opacity: 0;
}


/* ── CRITICAL PRE-PAINT NAVBAR & MOBILE STYLES (Guarantees zero FOUC) ── */
.navbar {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  z-index: 1000 !important;
  height: var(--nav-h, 72px) !important;
  display: flex !important;
  align-items: center !important;
  box-sizing: border-box !important;
}
.nav-inner {
  max-width: 1200px !important;
  margin: 0 auto !important;
  padding: 0 20px !important;
  width: 100% !important;
  display: flex !important;
  align-items: center !important;
  justify-content: space-between !important;
  box-sizing: border-box !important;
}
.nav-logo {
  display: flex !important;
  align-items: center !important;
  cursor: pointer !important;
  flex-shrink: 0 !important;
}
.nav-logo-lockup {
  position: relative !important;
  width: min(180px, 42vw) !important;
  min-width: 130px !important;
  height: 34px !important;
  display: block !important;
  flex-shrink: 0 !important;
  overflow: hidden !important;
}
.nav-logo-lockup img {
  position: absolute !important;
  inset: 0 !important;
  width: 100% !important;
  height: 100% !important;
  object-fit: contain !important;
  max-width: 100% !important;
  max-height: 100% !important;
}
.nav-logo .logo-dark { opacity: 1; }
.nav-logo .logo-light { opacity: 0; }
.navbar.hero-nav .nav-logo .logo-dark,
.navbar.dark-nav .nav-logo .logo-dark { opacity: 0 !important; }
.navbar.hero-nav .nav-logo .logo-light,
.navbar.dark-nav .nav-logo .logo-light { opacity: 1 !important; }
.navbar.scrolled .nav-logo .logo-dark { opacity: 1 !important; }
.navbar.scrolled .nav-logo .logo-light { opacity: 0 !important; }

.nav-links {
  display: flex;
  align-items: center;
  gap: 2px;
}
.nav-cta {
  display: flex;
  align-items: center;
  gap: 10px;
}
.nav-hamburger {
  display: none !important;
  flex-direction: column !important;
  justify-content: center !important;
  align-items: center !important;
  gap: 5px !important;
  width: 40px !important;
  height: 40px !important;
  padding: 6px !important;
  border-radius: 10px !important;
  cursor: pointer !important;
  background: rgba(255, 255, 255, 0.08) !important;
  border: 1px solid rgba(255, 255, 255, 0.16) !important;
  color: #ffffff !important;
  z-index: 1001 !important;
  transition: all 0.2s ease !important;
}
.navbar.scrolled .nav-hamburger {
  background: rgba(11, 31, 23, 0.06) !important;
  border: 1px solid rgba(11, 31, 23, 0.12) !important;
  color: #0B1F17 !important;
}
.nav-hamburger span {
  display: block !important;
  width: 22px !important;
  height: 2px !important;
  background: currentColor !important;
  border-radius: 2px !important;
  transition: transform 0.25s ease, opacity 0.25s ease !important;
}
.nav-hamburger.open span:nth-child(1) {
  transform: translateY(7px) rotate(45deg) !important;
}
.nav-hamburger.open span:nth-child(2) {
  opacity: 0 !important;
}
.nav-hamburger.open span:nth-child(3) {
  transform: translateY(-7px) rotate(-45deg) !important;
}
.nav-mobile {
  position: fixed !important;
  top: var(--nav-h, 72px) !important;
  left: 0 !important;
  right: 0 !important;
  background: #ffffff !important;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08) !important;
  padding: 16px 20px 24px !important;
  display: none !important;
  flex-direction: column !important;
  gap: 8px !important;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.14) !important;
  z-index: 999 !important;
  box-sizing: border-box !important;
}
.nav-mobile.open {
  display: flex !important;
}
.nav-mobile .nav-link {
  color: #1a2e24 !important;
  padding: 12px 14px !important;
  font-size: 1rem !important;
  font-weight: 600 !important;
  text-decoration: none !important;
  border-radius: 8px !important;
  transition: background 0.15s ease !important;
}
.nav-mobile .nav-link:hover {
  background: rgba(10, 92, 56, 0.06) !important;
  color: #0A5C38 !important;
}

@media (max-width: 900px) {
  .nav-links,
  .nav-cta {
    display: none !important;
  }
  .nav-hamburger {
    display: flex !important;
  }
}

/* ── STATS STRIP: STATIONARY CENTERED ON WEB, CAROUSEL ONLY ON MOBILE ── */
.stats-strip {
  background: #ffffff !important;
  border-top: 1px solid var(--border, #E6EAE7) !important;
  border-bottom: 1px solid var(--border, #E6EAE7) !important;
  padding: 38px 0 !important;
  overflow: hidden !important;
  position: relative !important;
}

.stats-ticker-outer {
  position: relative !important;
  width: 100% !important;
  max-width: 1240px !important;
  margin: 0 auto !important;
  padding: 4px 24px !important;
  overflow: visible !important;
  mask-image: none !important;
  -webkit-mask-image: none !important;
}

.stats-ticker-track {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 20px !important;
  width: 100% !important;
  animation: none !important;
  transform: none !important;
}

.stats-ticker-track .stat-card[aria-hidden="true"] {
  display: none !important;
}

.stat-card {
  flex: 1 1 0 !important;
  display: flex !important;
  flex-direction: column !important;
  align-items: center !important;
  justify-content: center !important;
  background: #FAFAF9 !important;
  border: 1px solid rgba(11, 31, 23, 0.08) !important;
  border-radius: 18px !important;
  padding: 18px 24px !important;
  min-width: 160px !important;
  max-width: 230px !important;
  box-shadow: 0 2px 10px rgba(11, 31, 23, 0.03) !important;
  transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease !important;
  user-select: none !important;
}

.stat-card:hover {
  transform: translateY(-2px) !important;
  border-color: rgba(10, 91, 56, 0.22) !important;
  box-shadow: 0 8px 24px rgba(10, 91, 56, 0.08) !important;
}

.stat-num {
  font-family: 'Cabinet Grotesk', -apple-system, BlinkMacSystemFont, sans-serif !important;
  font-weight: 800 !important;
  font-size: 2.1rem !important;
  color: #0A5C38 !important;
  letter-spacing: -0.035em !important;
  line-height: 1 !important;
}

.stat-label {
  font-size: 0.78rem !important;
  font-weight: 600 !important;
  color: #526059 !important;
  margin-top: 6px !important;
  text-align: center !important;
  line-height: 1.35 !important;
  white-space: nowrap !important;
}

@keyframes statsTickerSlide {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

@media (max-width: 768px) {
  .stats-strip {
    padding: 24px 0 26px !important;
  }
  .stats-ticker-outer {
    overflow: hidden !important;
    padding: 6px 0 !important;
    max-width: 100% !important;
    mask-image: linear-gradient(to right, transparent, black 6%, black 94%, transparent) !important;
    -webkit-mask-image: linear-gradient(to right, transparent, black 6%, black 94%, transparent) !important;
  }
  .stats-ticker-track {
    width: max-content !important;
    justify-content: flex-start !important;
    gap: 14px !important;
    animation: statsTickerSlide 20s linear infinite !important;
    will-change: transform !important;
  }
  .stats-ticker-track .stat-card[aria-hidden="true"] {
    display: flex !important;
  }
  .stat-card {
    flex: 0 0 auto !important;
    min-width: 145px !important;
    max-width: none !important;
    padding: 14px 18px !important;
  }
  .stat-num {
    font-size: 1.7rem !important;
  }
  .stat-label {
    font-size: 0.72rem !important;
  }
}

/* ── FOOTER SOCIAL BUTTONS: WHITE ICONS ONLY, NO TEXT ── */
.footer-social {
  display: flex !important;
  align-items: center !important;
  gap: 12px !important;
  margin-top: 20px !important;
}

.footer-social a {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  width: 40px !important;
  height: 40px !important;
  min-width: 40px !important;
  padding: 0 !important;
  border-radius: 50% !important;
  background: rgba(255, 255, 255, 0.08) !important;
  border: 1px solid rgba(255, 255, 255, 0.18) !important;
  color: #FFFFFF !important;
  text-decoration: none !important;
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.16) !important;
}

.footer-social a svg {
  width: 16px !important;
  height: 16px !important;
  color: #FFFFFF !important;
  fill: #FFFFFF !important;
  flex-shrink: 0 !important;
  transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.footer-social a:hover {
  background: rgba(255, 255, 255, 0.22) !important;
  border-color: #00C97A !important;
  transform: translateY(-2px) scale(1.06) !important;
  box-shadow: 0 6px 16px rgba(0, 201, 122, 0.24) !important;
}

.footer-social a:hover svg {
  transform: scale(1.08) !important;
}

/* ── STAKEHOLDER CAROUSEL & LOADING GREEN PROGRESS LINE ── */
.siteassist-progress-track {
  position: absolute !important;
  bottom: 0 !important;
  left: 0 !important;
  right: 0 !important;
  height: 3px !important;
  background: rgba(255, 255, 255, 0.22) !important;
  border-radius: 3px !important;
  overflow: hidden !important;
  z-index: 5 !important;
}

@media (max-width: 768px) {
  .siteassist-progress-track {
    height: 2.5px !important;
  }
}

.siteassist-progress-bar {
  height: 100% !important;
  width: 0%;
  background: #00C97A !important;
  box-shadow: 0 0 12px rgba(0, 201, 122, 0.9) !important;
  border-radius: 3px !important;
  transition: width 0.05s linear !important;
}

@keyframes saHeadlineFadeUp {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes saBodyFadeUp {
  0% {
    opacity: 0;
    transform: translateY(8px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes saKickerFade {
  0% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}

.siteassist-counter {
  display: none !important;
}

.siteassist-feature-title {
  animation: saHeadlineFadeUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
}

.siteassist-feature-body {
  animation: saBodyFadeUp 0.95s cubic-bezier(0.16, 1, 0.3, 1) 0.22s both !important;
}

</style>
