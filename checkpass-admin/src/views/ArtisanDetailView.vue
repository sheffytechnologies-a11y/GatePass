<template>
  <div class="artisan-detail-page">
    <!-- Top Bar with Back Link & Creative Sibling Navigator -->
    <div class="top-nav-bar">
      <router-link to="/artisans" class="btn-back">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"/>
          <polyline points="12 19 5 12 12 5"/>
        </svg>
        <span>Back to Verified Artisans</span>
      </router-link>

      <div class="top-bar-right">
        <!-- Keyboard shortcuts hint -->
        <span class="keyboard-tip" title="Use Left / Right arrow keys to navigate">
          <kbd>&larr;</kbd> <kbd>&rarr;</kbd> keys
        </span>

        <!-- Sibling Navigator -->
        <div v-if="allArtisans.length > 1" class="sibling-nav">
          <button
            type="button"
            class="sibling-btn"
            :disabled="!prevArtisanId"
            title="Previous Artisan (Left Arrow)"
            @click="goToArtisan(prevArtisanId)"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span class="sibling-text">Prev</span>
          </button>

          <span class="sibling-counter">
            <strong>{{ currentIndex + 1 }}</strong> of {{ allArtisans.length }}
          </span>

          <button
            type="button"
            class="sibling-btn"
            :disabled="!nextArtisanId"
            title="Next Artisan (Right Arrow)"
            @click="goToArtisan(nextArtisanId)"
          >
            <span class="sibling-text">Next</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </button>
        </div>

        <button type="button" class="btn btn-outline" @click="openEditModal">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          <span>Edit Profile</span>
        </button>
      </div>
    </div>

    <!-- NOT FOUND STATE -->
    <div v-if="!artisan" class="card not-found-card">
      <div class="empty-icon-wrap">🛠️</div>
      <h2>Artisan Record Not Found</h2>
      <p>The requested service provider could not be found or has been unlisted.</p>
      <router-link to="/artisans" class="btn btn-primary">Return to Directory</router-link>
    </div>

    <!-- MAIN CONTENT -->
    <div v-else class="artisan-content">
      <!-- ── HERO PROFILE BANNER ── -->
      <section class="hero-banner card">
        <div class="hero-main-row">
          <div class="hero-profile-group">
            <div class="avatar-badge-hero" :style="{ background: artisan.avatarBg || '#0A5C38' }">
              {{ artisan.initials || 'AR' }}
            </div>

            <div class="hero-identity">
              <div class="hero-badge-row">
                <span class="accredited-pill">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <polyline points="9 12 11 14 15 10"/>
                  </svg>
                  Estate Accredited Artisan
                </span>
                <span class="category-pill">{{ artisan.category }}</span>
                <span class="status-pill" :class="artisan.status === 'Active' ? 'status-pill--active' : 'status-pill--inactive'">
                  <span class="status-dot"></span>
                  {{ artisan.status || 'Active' }}
                </span>
              </div>

              <h1 class="hero-name">{{ artisan.name }}</h1>
              <p class="hero-trade">{{ artisan.trade }}</p>

              <div class="hero-meta-strip">
                <span class="meta-item">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                  </svg>
                  {{ artisan.address || 'Estate Commercial Annex / On-Call' }}
                </span>
                <span class="meta-dot">&bull;</span>
                <span class="meta-item">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                  </svg>
                  Verified {{ artisan.verifiedSince || 'since 2024' }}
                </span>
                <span class="meta-dot">&bull;</span>
                <span class="meta-item">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                  </svg>
                  {{ artisan.experienceYears || 8 }}+ Years Experience
                </span>
              </div>
            </div>
          </div>

          <!-- Hero Action Buttons -->
          <div class="hero-actions">
            <a
              :href="`https://wa.me/${artisan.whatsapp}?text=${encodeURIComponent(`Hello ${artisan.name}, I am reaching out from Checkpass Estate regarding a maintenance request.`)}`"
              target="_blank"
              class="btn-cta btn-whatsapp"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.311.045-.698.059-1.146-.086-.285-.093-.654-.234-1.157-.453-2.147-.936-3.535-3.125-3.642-3.267-.107-.142-.871-1.159-.871-2.211 0-1.052.55-1.57.747-1.784.197-.214.43-.268.574-.268.143 0 .287.002.412.008.132.006.309-.05.483.37.18.435.617 1.506.671 1.616.054.11.09.239.018.384-.072.146-.108.236-.214.361-.107.125-.226.279-.323.375-.107.107-.219.224-.094.439.125.214.557.919 1.194 1.488.82.732 1.512.959 1.727 1.066.215.107.34.09.466-.054.125-.143.538-.626.681-.841.143-.215.287-.179.483-.107.197.072 1.253.591 1.468.699.215.107.359.161.412.251.054.09.054.52-.09.925z"/>
                <path d="M12 2C6.477 2 2 6.477 2 12c0 1.891.524 3.662 1.435 5.178L2 22l4.981-1.399C8.423 21.492 10.151 22 12 22c5.523 0 10-4.477 10-10S17.523 2 12 2zm0 18.25c-1.637 0-3.17-.488-4.46-1.328l-.32-.208-2.96.831.815-2.884-.225-.339c-.933-1.407-1.45-3.076-1.45-4.822 0-4.742 3.858-8.6 8.6-8.6 4.743 0 8.6 3.858 8.6 8.6 0 4.742-3.857 8.6-8.6 8.6z"/>
              </svg>
              <span>Chat on WhatsApp</span>
            </a>

            <a :href="`tel:${artisan.phone}`" class="btn-cta btn-call">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
              <span>Call Provider</span>
            </a>

            <button type="button" class="btn-cta btn-copy" @click="copyPhone">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
              </svg>
              <span>{{ copied ? 'Copied!' : 'Copy Phone' }}</span>
            </button>
          </div>
        </div>
      </section>

      <!-- ── KPI & TRUST STRIP (4 METRICS) ── -->
      <section class="kpi-strip">
        <div class="kpi-card card">
          <div class="kpi-icon-wrap kpi-icon--amber">★</div>
          <div class="kpi-content">
            <div class="kpi-value-row">
              <span class="kpi-value">{{ artisan.rating ? Number(artisan.rating).toFixed(1) : '5.0' }}</span>
              <span class="kpi-subval">/ 5.0</span>
            </div>
            <div class="kpi-label">Resident Rating</div>
            <div class="kpi-footnote">{{ (artisan.reviews && artisan.reviews.length) || 0 }} Verified Reviews</div>
          </div>
        </div>

        <div class="kpi-card card">
          <div class="kpi-icon-wrap kpi-icon--green">🛡️</div>
          <div class="kpi-content">
            <div class="kpi-value-row">
              <span class="kpi-value text-green">Accredited</span>
            </div>
            <div class="kpi-label">Estate Gate Clearance</div>
            <div class="kpi-footnote">Active pass &amp; background check</div>
          </div>
        </div>

        <div class="kpi-card card">
          <div class="kpi-icon-wrap kpi-icon--blue">⚡</div>
          <div class="kpi-content">
            <div class="kpi-value-row">
              <span class="kpi-value">{{ artisan.responseTime || '< 20 mins' }}</span>
            </div>
            <div class="kpi-label">Avg. Response Time</div>
            <div class="kpi-footnote">Fast on-site dispatch</div>
          </div>
        </div>

        <div class="kpi-card card">
          <div class="kpi-icon-wrap kpi-icon--purple">🤝</div>
          <div class="kpi-content">
            <div class="kpi-value-row">
              <span class="kpi-value">{{ artisan.jobsCompleted || 48 }}+</span>
            </div>
            <div class="kpi-label">Completed Jobs</div>
            <div class="kpi-footnote">100% Satisfaction Record</div>
          </div>
        </div>
      </section>

      <!-- ── 2-COLUMN MAIN LAYOUT ── -->
      <div class="detail-grid">
        <!-- ── LEFT COLUMN: CREDENTIALS & CLEARANCE ── -->
        <div class="col-left">
          <!-- Card: Security & Accreditation Credentials -->
          <div class="card panel-card">
            <div class="panel-head">
              <div class="panel-icon-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0A5C38" stroke-width="2.2">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <h3>Estate Accreditation Checklist</h3>
              </div>
              <span class="badge-status-ok">100% Cleared</span>
            </div>

            <div class="checklist-items">
              <div class="check-item">
                <div class="check-icon-ok">✓</div>
                <div class="check-text">
                  <strong>National Identity Verification</strong>
                  <span>Valid NIN / Driver's License uploaded and verified on estate register</span>
                </div>
              </div>

              <div class="check-item">
                <div class="check-icon-ok">✓</div>
                <div class="check-text">
                  <strong>Security &amp; Police Background Clearance</strong>
                  <span>Certified crime-free background vetting completed for estate entry</span>
                </div>
              </div>

              <div class="check-item">
                <div class="check-icon-ok">✓</div>
                <div class="check-text">
                  <strong>GatePass RFID / QR Security Tag</strong>
                  <span>Automatic gate validation pass issued for authorized maintenance visits</span>
                </div>
              </div>

              <div class="check-item">
                <div class="check-icon-ok">✓</div>
                <div class="check-text">
                  <strong>Estate Code of Conduct Signed</strong>
                  <span>Agreed to noise limits, respectful demeanor, and clean work policy</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card: Contact & Dispatch Directory -->
          <div class="card panel-card">
            <div class="panel-head">
              <div class="panel-icon-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="2">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <h3>Contact &amp; Dispatch Channels</h3>
              </div>
            </div>

            <div class="contact-details-list">
              <div class="contact-row">
                <span class="contact-label">Primary Phone</span>
                <a :href="`tel:${artisan.phone}`" class="contact-link">
                  <span>{{ artisan.phone }}</span>
                  <span class="link-arrow">&nearr;</span>
                </a>
              </div>

              <div class="contact-row">
                <span class="contact-label">WhatsApp Hotline</span>
                <a :href="`https://wa.me/${artisan.whatsapp}`" target="_blank" class="contact-link text-green">
                  <span>+{{ artisan.whatsapp }}</span>
                  <span class="link-arrow">&nearr;</span>
                </a>
              </div>

              <div class="contact-row">
                <span class="contact-label">Operating Schedule</span>
                <span class="contact-value">Mon &ndash; Sat: 8:00 AM &ndash; 7:00 PM</span>
              </div>

              <div class="contact-row">
                <span class="contact-label">Emergency Out-of-hours</span>
                <span class="contact-value text-green">Available 24/7 on callout</span>
              </div>
            </div>
          </div>

          <!-- Card: Service Scope & Skills Tags -->
          <div class="card panel-card">
            <div class="panel-head">
              <div class="panel-icon-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="2">
                  <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                </svg>
                <h3>Specializations &amp; Service Scope</h3>
              </div>
            </div>

            <div class="skills-tag-wrap">
              <span v-for="skill in (artisan.skills && artisan.skills.length ? artisan.skills : [artisan.trade, 'Diagnostic Inspection', 'Emergency Repairs'])" :key="skill" class="skill-tag">
                {{ skill }}
              </span>
            </div>
          </div>

          <!-- Card: Recent Gate Access Runs -->
          <div class="card panel-card">
            <div class="panel-head">
              <div class="panel-icon-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <polyline points="12 6 12 12 16 14"/>
                </svg>
                <h3>Recent Gate Entries for Maintenance</h3>
              </div>
            </div>

            <div v-if="!artisan.recentGateLogs || artisan.recentGateLogs.length === 0" class="no-logs-note">
              No recent gate entry records logged this month.
            </div>
            <div v-else class="gate-logs-list">
              <div v-for="log in artisan.recentGateLogs" :key="log.id" class="gate-log-item">
                <div class="gate-log-dot"></div>
                <div class="gate-log-info">
                  <div class="gate-log-unit">{{ log.unit }}</div>
                  <div class="gate-log-meta">
                    <span>Resident: <strong>{{ log.resident }}</strong></span>
                    <span>&bull;</span>
                    <span>{{ log.date }}</span>
                  </div>
                </div>
                <span class="gate-tag">{{ log.gate }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- ── RIGHT COLUMN: REVIEWS & COMMUNITY TRUST FEED ── -->
        <div class="col-right">
          <!-- Rating Summary Bar Card -->
          <div class="card panel-card rating-breakdown-card">
            <div class="rating-hero-flex">
              <div class="rating-big-box">
                <div class="rating-score">{{ artisan.rating ? Number(artisan.rating).toFixed(1) : '5.0' }}</div>
                <div class="rating-star-cluster">
                  <span v-for="s in 5" :key="s" class="star-gold">★</span>
                </div>
                <div class="rating-count-lbl">{{ (artisan.reviews && artisan.reviews.length) || 0 }} Resident Ratings</div>
              </div>

              <div class="rating-bars-col">
                <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="rating-bar-row">
                  <span class="bar-lbl">{{ star }} ★</span>
                  <div class="bar-track">
                    <div class="bar-fill" :style="{ width: getStarPercentage(star) + '%' }"></div>
                  </div>
                  <span class="bar-pct">{{ getStarPercentage(star) }}%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Reviews Feed Card -->
          <div class="card panel-card reviews-feed-card">
            <div class="panel-head">
              <div class="panel-icon-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1F2937" stroke-width="2">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <h3>Resident Reviews &amp; Feedback</h3>
                <span class="badge-count">{{ (artisan.reviews && artisan.reviews.length) || 0 }}</span>
              </div>

              <button type="button" class="btn btn-sm btn-outline" @click="showAddReviewForm = !showAddReviewForm">
                {{ showAddReviewForm ? 'Cancel' : '+ Log Resident Review' }}
              </button>
            </div>

            <!-- Expandable Add Review Form -->
            <transition name="form-expand">
              <div v-if="showAddReviewForm" class="add-review-box">
                <h4 class="add-review-title">Record Resident Feedback</h4>
                <p class="add-review-desc">Record verbal or written feedback received from an estate resident after a service visit.</p>

                <form @submit.prevent="submitResidentReview">
                  <div class="form-grid-2">
                    <div class="form-group">
                      <label class="form-label-xs">Resident Name *</label>
                      <input
                        v-model="newReviewForm.residentName"
                        type="text"
                        required
                        placeholder="e.g. Chief Adeleke"
                        class="form-input-sm"
                      />
                    </div>
                    <div class="form-group">
                      <label class="form-label-xs">Property / Unit Address *</label>
                      <input
                        v-model="newReviewForm.residentUnit"
                        type="text"
                        required
                        placeholder="e.g. House 12, Palm Court"
                        class="form-input-sm"
                      />
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label-xs">Star Rating *</label>
                    <div class="star-rating-picker">
                      <button
                        v-for="s in 5"
                        :key="s"
                        type="button"
                        class="star-pick-btn"
                        :class="{ 'is-active': s <= newReviewForm.rating }"
                        @click="newReviewForm.rating = s"
                      >
                        ★
                      </button>
                      <span class="star-rating-name">{{ ratingLabels[newReviewForm.rating] || '' }}</span>
                    </div>
                  </div>

                  <div class="form-group">
                    <label class="form-label-xs">Review Details &amp; Experience *</label>
                    <textarea
                      v-model="newReviewForm.comment"
                      required
                      rows="3"
                      placeholder="e.g. Arrived on time, neatly resolved the circuit breaker tripping issue, and left the workspace clean."
                      class="form-textarea-sm"
                    ></textarea>
                  </div>

                  <div class="add-review-actions">
                    <button type="button" class="btn btn-sm btn-secondary" @click="showAddReviewForm = false">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Resident Review</button>
                  </div>
                </form>
              </div>
            </transition>

            <!-- Reviews List -->
            <div v-if="!artisan.reviews || artisan.reviews.length === 0" class="no-reviews-state">
              <div class="empty-bubble-icon">💬</div>
              <h4>No Reviews Logged Yet</h4>
              <p>Residents have not submitted reviews for {{ artisan.name }} yet. Click "+ Log Resident Review" above to record feedback.</p>
            </div>

            <div v-else class="reviews-stream">
              <div v-for="rev in artisan.reviews" :key="rev.id" class="review-card">
                <div class="review-header">
                  <div class="reviewer-identity">
                    <div class="reviewer-avatar">
                      {{ rev.residentName.split(' ').map((n: string) => n[0]).slice(0, 2).join('') }}
                    </div>
                    <div>
                      <div class="reviewer-name-row">
                        <strong>{{ rev.residentName }}</strong>
                        <span class="verified-resident-tag">Verified Resident</span>
                      </div>
                      <div class="reviewer-unit-row">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                          <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span>{{ rev.residentUnit }}</span>
                      </div>
                    </div>
                  </div>

                  <div class="review-date-stars">
                    <div class="review-stars-cluster">
                      <span v-for="s in 5" :key="s" class="star" :class="{ filled: s <= rev.rating }">★</span>
                    </div>
                    <span class="review-date-badge">{{ rev.date }}</span>
                  </div>
                </div>

                <p class="review-text">&ldquo;{{ rev.comment }}&rdquo;</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── MODAL: EDIT ARTISAN PROFILE ── -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
      <div class="modal-dialog">
        <div class="modal-header">
          <div>
            <h3>Edit Artisan Profile</h3>
            <p>Update credentials, specialty, or verification status</p>
          </div>
          <button type="button" class="modal-close" @click="showEditModal = false">&times;</button>
        </div>

        <form @submit.prevent="saveArtisanChanges">
          <div class="modal-body">
            <div class="form-group">
              <label class="form-label">Full Name *</label>
              <input v-model="editForm.name" type="text" required class="form-input" />
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Category *</label>
                <select v-model="editForm.category" required class="form-input">
                  <option value="Electrician">Electrician</option>
                  <option value="Plumber">Plumber</option>
                  <option value="AC Repair">AC Repair</option>
                  <option value="Cleaning">Cleaning</option>
                  <option value="Carpentry">Carpentry</option>
                  <option value="Painting">Painting</option>
                  <option value="Generator Tech">Generator Tech</option>
                  <option value="Pest Control">Pest Control</option>
                  <option value="Other">Other Service</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Status *</label>
                <select v-model="editForm.status" class="form-input">
                  <option value="Active">Active (Visible to Residents)</option>
                  <option value="Under Review">Under Review</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Specialty / Trade Title *</label>
              <input v-model="editForm.trade" type="text" required class="form-input" />
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input v-model="editForm.phone" type="tel" required class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">WhatsApp Number *</label>
                <input v-model="editForm.whatsapp" type="text" required class="form-input" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Operating Address / Station</label>
              <input v-model="editForm.address" type="text" class="form-input" />
            </div>

            <div class="form-group">
              <label class="form-label">Skills &amp; Specializations (comma separated)</label>
              <input v-model="editForm.skillsStr" type="text" class="form-input" placeholder="e.g. Inverter Wiring, DB Box Balancing" />
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showEditModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Profile Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  type ArtisanItem,
  loadArtisans,
  getArtisanById,
  updateArtisan,
  addArtisanReview
} from '@/data/artisans'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const { showToast } = useToast()

const artisan = ref<ArtisanItem | null>(null)
const allArtisans = ref<ArtisanItem[]>([])
const copied = ref(false)

// Edit Modal state
const showEditModal = ref(false)
const editForm = ref({
  name: '',
  category: 'Electrician',
  trade: '',
  phone: '',
  whatsapp: '',
  status: 'Active' as 'Active' | 'Under Review' | 'Inactive',
  address: '',
  skillsStr: ''
})

// Add Review form
const showAddReviewForm = ref(false)
const newReviewForm = ref({
  residentName: '',
  residentUnit: '',
  rating: 5,
  comment: ''
})

const ratingLabels: Record<number, string> = {
  5: '5 Stars - Outstanding & Neat',
  4: '4 Stars - Very Good',
  3: '3 Stars - Satisfactory',
  2: '2 Stars - Needs Improvement',
  1: '1 Star - Unsatisfactory'
}

function refreshArtisan() {
  allArtisans.value = loadArtisans()
  const id = String(route.params.id)
  const found = getArtisanById(id)
  if (found) {
    artisan.value = found
  } else {
    artisan.value = null
  }
}

watch(() => route.params.id, () => {
  refreshArtisan()
})

const currentIndex = computed(() => {
  if (!artisan.value) return -1
  return allArtisans.value.findIndex(a => String(a.id) === String(artisan.value?.id))
})

const prevArtisanId = computed(() => {
  if (currentIndex.value > 0) {
    return allArtisans.value[currentIndex.value - 1].id
  }
  return null
})

const nextArtisanId = computed(() => {
  if (currentIndex.value >= 0 && currentIndex.value < allArtisans.value.length - 1) {
    return allArtisans.value[currentIndex.value + 1].id
  }
  return null
})

function goToArtisan(id: string | null) {
  if (!id) return
  router.push(`/artisans/${id}`)
}

function handleKeydown(e: KeyboardEvent) {
  if (['INPUT', 'TEXTAREA', 'SELECT'].includes((e.target as HTMLElement)?.tagName)) return
  if (e.key === 'ArrowLeft' && prevArtisanId.value) {
    goToArtisan(prevArtisanId.value)
  } else if (e.key === 'ArrowRight' && nextArtisanId.value) {
    goToArtisan(nextArtisanId.value)
  }
}

async function copyPhone() {
  if (!artisan.value?.phone) return
  try {
    await navigator.clipboard.writeText(artisan.value.phone)
    copied.value = true
    showToast('Phone number copied to clipboard', 'success')
    setTimeout(() => { copied.value = false }, 2500)
  } catch {
    showToast('Failed to copy', 'error')
  }
}

function getStarPercentage(star: number): number {
  if (!artisan.value || !artisan.value.reviews || artisan.value.reviews.length === 0) {
    return star === 5 ? 100 : 0
  }
  const count = artisan.value.reviews.filter(r => Math.round(r.rating) === star).length
  return Math.round((count / artisan.value.reviews.length) * 100)
}

function openEditModal() {
  if (!artisan.value) return
  editForm.value = {
    name: artisan.value.name,
    category: artisan.value.category,
    trade: artisan.value.trade,
    phone: artisan.value.phone,
    whatsapp: artisan.value.whatsapp,
    status: artisan.value.status || 'Active',
    address: artisan.value.address || '',
    skillsStr: (artisan.value.skills || []).join(', ')
  }
  showEditModal.value = true
}

function saveArtisanChanges() {
  if (!artisan.value) return
  const skills = editForm.value.skillsStr
    .split(',')
    .map(s => s.trim())
    .filter(Boolean)

  const updated = updateArtisan(artisan.value.id, {
    name: editForm.value.name,
    category: editForm.value.category,
    trade: editForm.value.trade,
    phone: editForm.value.phone,
    whatsapp: editForm.value.whatsapp.replace(/\D/g, ''),
    status: editForm.value.status,
    address: editForm.value.address,
    skills: skills.length ? skills : artisan.value.skills
  })

  if (updated) {
    artisan.value = updated
    showEditModal.value = false
    showToast('Artisan profile updated successfully', 'success')
  }
}

function submitResidentReview() {
  if (!artisan.value) return
  if (!newReviewForm.value.residentName.trim() || !newReviewForm.value.comment.trim()) {
    showToast('Please fill all review fields', 'error')
    return
  }

  const added = addArtisanReview(artisan.value.id, {
    residentName: newReviewForm.value.residentName,
    residentUnit: newReviewForm.value.residentUnit || 'Estate Resident',
    rating: newReviewForm.value.rating,
    comment: newReviewForm.value.comment,
    date: 'Just now'
  })

  if (added) {
    refreshArtisan()
    showAddReviewForm.value = false
    newReviewForm.value = { residentName: '', residentUnit: '', rating: 5, comment: '' }
    showToast('Resident review recorded successfully!', 'success')
  }
}

onMounted(() => {
  refreshArtisan()
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.artisan-detail-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
  max-width: 1280px;
  margin: 0 auto;
  padding-bottom: 50px;
}

/* ── Top Bar ── */
.top-nav-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 4px 0;
}

.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  text-decoration: none;
  padding: 7px 14px;
  border-radius: 9px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  transition: all 160ms cubic-bezier(0.16, 1, 0.3, 1);
}

.btn-back:hover {
  background: #F9FAFB;
  border-color: #D1D5DB;
  color: #111827;
  transform: translateX(-2px);
}

.top-bar-right {
  display: flex;
  align-items: center;
  gap: 14px;
}

.keyboard-tip {
  font-size: 11.5px;
  color: #9CA3AF;
  display: inline-flex;
  align-items: center;
  gap: 3px;
}

.keyboard-tip kbd {
  background: #F3F4F6;
  border: 1px solid #D1D5DB;
  border-radius: 4px;
  padding: 1px 5px;
  font-size: 10px;
  font-family: inherit;
  font-weight: 600;
  color: #4B5563;
}

.sibling-nav {
  display: inline-flex;
  align-items: center;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 9px;
  padding: 3px;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.sibling-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: transparent;
  border: none;
  font-size: 12.5px;
  font-weight: 600;
  color: #374151;
  padding: 5px 10px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 140ms ease;
}

.sibling-btn:hover:not(:disabled) {
  background: #F3F4F6;
  color: #111827;
}

.sibling-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.sibling-counter {
  font-size: 12px;
  color: #6B7280;
  padding: 0 8px;
  border-left: 1px solid #F3F4F6;
  border-right: 1px solid #F3F4F6;
}

/* ── Hero Banner ── */
.hero-banner {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 28px 32px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.hero-main-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 28px;
  flex-wrap: wrap;
}

.hero-profile-group {
  display: flex;
  align-items: center;
  gap: 22px;
}

.avatar-badge-hero {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  color: #ffffff;
  font-size: 28px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: -0.5px;
  flex-shrink: 0;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
}

.hero-identity {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.hero-badge-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.accredited-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  color: #065F46;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  padding: 3px 9px;
  border-radius: 999px;
  letter-spacing: 0.2px;
}

.category-pill {
  font-size: 11px;
  font-weight: 700;
  color: #1E40AF;
  background: #EFF6FF;
  border: 1px solid #DBEAFE;
  padding: 3px 9px;
  border-radius: 999px;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 9px;
  border-radius: 999px;
}

.status-pill--active {
  background: #F0FDF4;
  color: #15803D;
  border: 1px solid #DCFCE7;
}

.status-pill--inactive {
  background: #FEF2F2;
  color: #B91C1C;
  border: 1px solid #FEE2E2;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.hero-name {
  font-size: 26px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.6px;
  margin: 0;
}

.hero-trade {
  font-size: 14.5px;
  font-weight: 500;
  color: #4B5563;
  margin: 0;
}

.hero-meta-strip {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12.5px;
  color: #6B7280;
  margin-top: 2px;
  flex-wrap: wrap;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.meta-dot {
  color: #D1D5DB;
}

/* ── Hero Action CTAs ── */
.hero-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13.5px;
  font-weight: 600;
  padding: 10px 18px;
  border-radius: 10px;
  text-decoration: none;
  cursor: pointer;
  transition: all 160ms cubic-bezier(0.16, 1, 0.3, 1);
  border: none;
}

.btn-whatsapp {
  background: #25D366;
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(37, 211, 102, 0.28);
}

.btn-whatsapp:hover {
  background: #20BA5A;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(37, 211, 102, 0.38);
}

.btn-call {
  background: #0A5C38;
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(10, 92, 56, 0.25);
}

.btn-call:hover {
  background: #08482C;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(10, 92, 56, 0.35);
}

.btn-copy {
  background: #ffffff;
  color: #374151;
  border: 1px solid #D1D5DB;
}

.btn-copy:hover {
  background: #F9FAFB;
  border-color: #9CA3AF;
  color: #111827;
}

/* ── KPI Strip ── */
.kpi-strip {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.kpi-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  padding: 18px 20px;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  transition: all 160ms ease;
}

.kpi-card:hover {
  border-color: #D1D5DB;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.kpi-icon-wrap {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}

.kpi-icon--amber { background: #FEF3C7; color: #D97706; }
.kpi-icon--green { background: #DCFCE7; color: #16A34A; }
.kpi-icon--blue  { background: #DBEAFE; color: #2563EB; }
.kpi-icon--purple{ background: #F3E8FF; color: #7E22CE; }

.kpi-content {
  display: flex;
  flex-direction: column;
}

.kpi-value-row {
  display: flex;
  align-items: baseline;
  gap: 4px;
}

.kpi-value {
  font-size: 20px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.4px;
}

.kpi-subval {
  font-size: 13px;
  font-weight: 600;
  color: #9CA3AF;
}

.kpi-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #4B5563;
  margin-top: 1px;
}

.kpi-footnote {
  font-size: 11px;
  color: #9CA3AF;
  margin-top: 3px;
}

/* ── 2-Column Detail Grid ── */
.detail-grid {
  display: grid;
  grid-template-columns: 410px 1fr;
  gap: 20px;
  align-items: start;
}

.col-left, .col-right {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.panel-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  padding: 22px 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 18px;
  gap: 12px;
}

.panel-icon-title {
  display: flex;
  align-items: center;
  gap: 9px;
}

.panel-icon-title h3 {
  font-size: 15px;
  font-weight: 700;
  color: #111827;
  margin: 0;
  letter-spacing: -0.2px;
}

.badge-status-ok {
  font-size: 11px;
  font-weight: 700;
  color: #065F46;
  background: #D1FAE5;
  padding: 2px 8px;
  border-radius: 6px;
}

.badge-count {
  font-size: 11px;
  font-weight: 700;
  color: #4B5563;
  background: #F3F4F6;
  padding: 2px 7px;
  border-radius: 999px;
}

/* ── Checklist ── */
.checklist-items {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.check-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.check-icon-ok {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #0A5C38;
  color: #ffffff;
  font-size: 11px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 2px;
}

.check-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.check-text strong {
  font-size: 13.5px;
  font-weight: 600;
  color: #1F2937;
}

.check-text span {
  font-size: 12px;
  color: #6B7280;
  line-height: 1.4;
}

/* ── Contact Details ── */
.contact-details-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.contact-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 11px;
  border-bottom: 1px solid #F3F4F6;
  font-size: 13px;
}

.contact-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.contact-label {
  color: #6B7280;
  font-weight: 500;
}

.contact-link {
  font-weight: 600;
  color: #0A5C38;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 3px;
}

.contact-link:hover {
  text-decoration: underline;
}

.contact-value {
  font-weight: 600;
  color: #1F2937;
}

.text-green { color: #0A5C38 !important; }

/* ── Skills Tag Wrap ── */
.skills-tag-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.skill-tag {
  background: #F3F4F6;
  color: #374151;
  font-size: 12px;
  font-weight: 600;
  padding: 5px 11px;
  border-radius: 8px;
  border: 1px solid #E5E7EB;
}

/* ── Gate Access Logs ── */
.gate-logs-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.gate-log-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  background: #F9FAFB;
  border: 1px solid #F3F4F6;
  border-radius: 9px;
}

.gate-log-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #0A5C38;
  flex-shrink: 0;
}

.gate-log-info {
  flex: 1;
}

.gate-log-unit {
  font-size: 13px;
  font-weight: 600;
  color: #111827;
}

.gate-log-meta {
  font-size: 11.5px;
  color: #6B7280;
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 2px;
}

.gate-tag {
  font-size: 11px;
  font-weight: 600;
  color: #4B5563;
  background: #E5E7EB;
  padding: 2px 7px;
  border-radius: 5px;
}

.no-logs-note, .no-reviews-note {
  font-size: 12.5px;
  color: #9CA3AF;
  font-style: italic;
  padding: 8px 0;
}

/* ── Rating Breakdown Card ── */
.rating-hero-flex {
  display: flex;
  align-items: center;
  gap: 32px;
  padding: 8px 0;
}

.rating-big-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding-right: 28px;
  border-right: 1px solid #E5E7EB;
  min-width: 140px;
}

.rating-score {
  font-size: 42px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -1px;
  line-height: 1;
}

.rating-star-cluster {
  display: flex;
  gap: 2px;
  font-size: 18px;
  color: #F59E0B;
  margin: 6px 0 4px;
}

.rating-count-lbl {
  font-size: 12px;
  font-weight: 600;
  color: #6B7280;
}

.rating-bars-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 7px;
}

.rating-bar-row {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12px;
}

.bar-lbl {
  font-weight: 600;
  color: #4B5563;
  width: 28px;
}

.bar-track {
  flex: 1;
  height: 8px;
  background: #F3F4F6;
  border-radius: 999px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  background: #F59E0B;
  border-radius: 999px;
  transition: width 300ms ease;
}

.bar-pct {
  font-weight: 600;
  color: #6B7280;
  width: 34px;
  text-align: right;
}

/* ── Add Review Form Box ── */
.add-review-box {
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 18px 20px;
  margin-bottom: 20px;
}

.add-review-title {
  font-size: 14px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 3px;
}

.add-review-desc {
  font-size: 12px;
  color: #6B7280;
  margin: 0 0 14px;
}

.star-rating-picker {
  display: flex;
  align-items: center;
  gap: 6px;
}

.star-pick-btn {
  background: transparent;
  border: none;
  font-size: 22px;
  color: #D1D5DB;
  cursor: pointer;
  padding: 0;
  line-height: 1;
  transition: transform 120ms ease, color 120ms ease;
}

.star-pick-btn:hover {
  transform: scale(1.2);
  color: #F59E0B;
}

.star-pick-btn.is-active {
  color: #F59E0B;
}

.star-rating-name {
  font-size: 12px;
  font-weight: 600;
  color: #4B5563;
  margin-left: 8px;
}

.add-review-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 14px;
}

/* ── Reviews Stream ── */
.reviews-stream {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.review-card {
  background: #ffffff;
  border: 1px solid #F3F4F6;
  border-radius: 12px;
  padding: 16px 18px;
  transition: all 140ms ease;
}

.review-card:hover {
  border-color: #E5E7EB;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.review-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
}

.reviewer-identity {
  display: flex;
  align-items: center;
  gap: 11px;
}

.reviewer-avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: #F3F4F6;
  color: #374151;
  font-size: 12.5px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.reviewer-name-row {
  display: flex;
  align-items: center;
  gap: 7px;
}

.reviewer-name-row strong {
  font-size: 13.5px;
  font-weight: 700;
  color: #111827;
}

.verified-resident-tag {
  font-size: 10px;
  font-weight: 700;
  color: #0A5C38;
  background: #ECFDF5;
  padding: 1px 6px;
  border-radius: 4px;
  border: 1px solid #A7F3D0;
}

.reviewer-unit-row {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11.5px;
  color: #6B7280;
  margin-top: 1px;
}

.review-date-stars {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.review-stars-cluster {
  display: flex;
  gap: 1px;
  font-size: 14px;
}

.star {
  color: #E5E7EB;
}

.star.filled {
  color: #F59E0B;
}

.review-date-badge {
  font-size: 11px;
  color: #9CA3AF;
}

.review-text {
  font-size: 13.5px;
  color: #374151;
  line-height: 1.5;
  margin: 0;
  font-style: italic;
}

.no-reviews-state {
  text-align: center;
  padding: 40px 20px;
  color: #6B7280;
}

.empty-bubble-icon {
  font-size: 32px;
  margin-bottom: 8px;
}

.no-reviews-state h4 {
  font-size: 15px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 4px;
}

.no-reviews-state p {
  font-size: 13px;
  color: #6B7280;
  margin: 0;
}

/* ── Generic Form controls ── */
.form-grid-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
  margin-bottom: 12px;
}

.form-label-xs {
  font-size: 11.5px;
  font-weight: 600;
  color: #4B5563;
}

.form-input-sm, .form-textarea-sm {
  width: 100%;
  font-size: 13px;
  padding: 7px 11px;
  border-radius: 8px;
  border: 1px solid #D1D5DB;
  background: #ffffff;
  color: #111827;
  font-family: inherit;
  transition: all 140ms ease;
}

.form-input-sm:focus, .form-textarea-sm:focus {
  outline: none;
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.1);
}

.form-textarea-sm {
  resize: vertical;
}

/* ── Modal ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(3px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-dialog {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 560px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  animation: modalPop 180ms ease-out;
}

@keyframes modalPop {
  from { opacity: 0; transform: scale(0.96); }
  to { opacity: 1; transform: scale(1); }
}

.modal-header {
  padding: 18px 24px;
  border-bottom: 1px solid #F3F4F6;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-header h3 {
  font-size: 17px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.modal-header p {
  font-size: 12.5px;
  color: #6B7280;
  margin: 2px 0 0;
}

.modal-close {
  background: transparent;
  border: none;
  font-size: 22px;
  color: #9CA3AF;
  cursor: pointer;
}

.modal-close:hover {
  color: #111827;
}

.modal-body {
  padding: 20px 24px;
  max-height: 70vh;
  overflow-y: auto;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #374151;
}

.form-input {
  width: 100%;
  font-size: 13.5px;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #D1D5DB;
  background: #ffffff;
  color: #111827;
  font-family: inherit;
}

.form-input:focus {
  outline: none;
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.1);
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #F3F4F6;
  background: #F9FAFB;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 13.5px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 9px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 140ms ease;
}

.btn-primary {
  background: #0A5C38;
  color: #ffffff;
}

.btn-primary:hover {
  background: #08482C;
}

.btn-secondary {
  background: #F3F4F6;
  color: #374151;
  border-color: #E5E7EB;
}

.btn-secondary:hover {
  background: #E5E7EB;
}

.btn-outline {
  background: #ffffff;
  color: #374151;
  border-color: #D1D5DB;
}

.btn-outline:hover {
  background: #F9FAFB;
  border-color: #9CA3AF;
}

.btn-sm {
  font-size: 12px;
  padding: 5px 12px;
  border-radius: 7px;
}

.not-found-card {
  text-align: center;
  padding: 60px 20px;
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #E5E7EB;
}

.not-found-card h2 {
  font-size: 20px;
  font-weight: 800;
  color: #111827;
  margin: 12px 0 6px;
}

.not-found-card p {
  color: #6B7280;
  font-size: 14px;
  margin-bottom: 20px;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
  .kpi-strip {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .hero-profile-group {
    flex-direction: column;
    align-items: flex-start;
  }
  .hero-main-row {
    flex-direction: column;
    align-items: flex-start;
  }
  .kpi-strip {
    grid-template-columns: 1fr;
  }
  .rating-hero-flex {
    flex-direction: column;
    align-items: flex-start;
  }
  .rating-big-box {
    border-right: none;
    padding-right: 0;
    border-bottom: 1px solid #E5E7EB;
    padding-bottom: 16px;
    width: 100%;
  }
}
</style>
