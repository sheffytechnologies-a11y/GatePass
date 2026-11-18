<template>
  <div class="artisan-detail-page">
    <!-- Top Bar with Back Link & Action CTAs -->
    <div class="top-nav-bar">
      <router-link to="/artisans" class="btn-back">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"/>
          <polyline points="12 19 5 12 12 5"/>
        </svg>
        <span>Back to Verified Artisans</span>
      </router-link>

      <div class="top-bar-right">
        <button type="button" class="btn btn-outline" @click="openEditModal">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          <span>Edit Profile</span>
        </button>

        <button
          v-if="artisan"
          type="button"
          class="btn"
          :class="artisan.status === 'Active' ? 'btn-disable' : 'btn-enable'"
          :disabled="togglingStatus"
          @click="toggleArtisanStatus"
        >
          <svg v-if="artisan.status === 'Active'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
          </svg>
          <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <span>{{ artisan.status === 'Active' ? 'Disable Artisan' : 'Enable Artisan' }}</span>
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
              <div class="hero-name-row">
                <h1 class="hero-name">{{ artisan.name }}</h1>
                <div class="hero-pills-group">
                  <span class="category-pill">{{ artisan.category }}</span>
                  <span
                    class="status-pill"
                    :class="artisan.status === 'Active' ? 'status-pill--active' : 'status-pill--disabled'"
                  >
                    <span class="status-dot"></span>
                    {{ artisan.status || 'Active' }}
                  </span>
                </div>
              </div>

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
      <div class="modal-dialog modal-dialog--artisan">
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
                  <option value="Disabled">Disabled</option>
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

            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Operating Address / Station</label>
                <input v-model="editForm.address" type="text" class="form-input" placeholder="e.g. Commercial Plaza, Shop 2" />
              </div>
              <div class="form-group">
                <label class="form-label">Response Time / Hours</label>
                <input v-model="editForm.responseTime" type="text" class="form-input" placeholder="e.g. < 20 mins or Mon - Sat" />
              </div>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label class="form-label">Years of Experience</label>
                <input v-model.number="editForm.experienceYears" type="number" min="0" class="form-input" />
              </div>
              <div class="form-group">
                <label class="form-label">Verified Since</label>
                <input v-model="editForm.verifiedSince" type="text" class="form-input" placeholder="e.g. Nov 2024" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Skills &amp; Specializations (comma separated)</label>
              <input v-model="editForm.skillsStr" type="text" class="form-input" placeholder="e.g. Inverter Wiring, DB Box Balancing, Solar Balancing" />
            </div>

            <div class="form-group checklist-group">
              <label class="form-label">Estate Accreditation Checklist</label>
              <div class="checkbox-options">
                <label class="checkbox-label">
                  <input v-model="editForm.idVerified" type="checkbox" />
                  <span>National Identity Verified (NIN / Driver's License)</span>
                </label>
                <label class="checkbox-label">
                  <input v-model="editForm.policeCleared" type="checkbox" />
                  <span>Security &amp; Police Background Clearance</span>
                </label>
                <label class="checkbox-label">
                  <input v-model="editForm.gatePassCleared" type="checkbox" />
                  <span>GatePass RFID / QR Security Tag Cleared</span>
                </label>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showEditModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Saving...' : 'Save Profile Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { type ArtisanItem, getArtisanById, updateArtisan } from '@/data/artisans'
import { artisansApi } from '@/api'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const { showToast } = useToast()

const artisan = ref<ArtisanItem | null>(null)
const loading = ref(false)
const togglingStatus = ref(false)
const saving = ref(false)

// Edit Modal state
const showEditModal = ref(false)
const editForm = ref({
  name: '',
  category: 'Electrician',
  trade: '',
  phone: '',
  whatsapp: '',
  status: 'Active' as 'Active' | 'Under Review' | 'Inactive' | 'Disabled',
  address: '',
  experienceYears: 5,
  verifiedSince: '',
  responseTime: '< 30 mins',
  skillsStr: '',
  idVerified: true,
  policeCleared: true,
  gatePassCleared: true,
})

// Add Review form
const showAddReviewForm = ref(false)
const newReviewForm = ref({
  residentName: '',
  residentUnit: '',
  rating: 5,
  comment: '',
})

const ratingLabels: Record<number, string> = {
  5: '5 Stars - Outstanding & Neat',
  4: '4 Stars - Very Good',
  3: '3 Stars - Satisfactory',
  2: '2 Stars - Needs Improvement',
  1: '1 Star - Unsatisfactory',
}

async function refreshArtisan() {
  loading.value = true
  const id = String(route.params.id)
  try {
    const res = await artisansApi.getOne(id)
    if (res.data?.artisan) {
      artisan.value = res.data.artisan
      loading.value = false
      return
    }
  } catch (err) {
    console.warn('Backend fetch failed, falling back to local dataset:', err)
  }

  const found = getArtisanById(id)
  if (found) {
    artisan.value = found
  } else {
    artisan.value = null
  }
  loading.value = false
}

watch(() => route.params.id, () => {
  refreshArtisan()
})

async function toggleArtisanStatus() {
  const current = artisan.value
  if (!current || togglingStatus.value) return
  togglingStatus.value = true
  const newStatus: 'Active' | 'Disabled' = current.status === 'Active' ? 'Disabled' : 'Active'
  try {
    const res = await artisansApi.update(current.id, { status: newStatus })
    if (res.data?.artisan) {
      artisan.value = res.data.artisan
    } else {
      current.status = newStatus
    }
    updateArtisan(current.id, { status: newStatus })
    showToast(`Artisan ${current.name} marked as ${newStatus}`, newStatus === 'Active' ? 'success' : 'warning')
  } catch (err) {
    showToast('Failed to update artisan status.', 'error')
  } finally {
    togglingStatus.value = false
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
  const current = artisan.value
  if (!current) return
  editForm.value = {
    name: current.name,
    category: current.category,
    trade: current.trade,
    phone: current.phone,
    whatsapp: current.whatsapp,
    status: (current.status || 'Active') as 'Active' | 'Under Review' | 'Inactive' | 'Disabled',
    address: current.address || '',
    experienceYears: current.experienceYears || 5,
    verifiedSince: current.verifiedSince || 'Nov 2024',
    responseTime: current.responseTime || '< 30 mins',
    skillsStr: (current.skills || []).join(', '),
    idVerified: current.idVerified !== false,
    policeCleared: current.policeCleared !== false,
    gatePassCleared: current.gatePassCleared !== false,
  }
  showEditModal.value = true
}

async function saveArtisanChanges() {
  const current = artisan.value
  if (!current) return
  saving.value = true
  const skills = editForm.value.skillsStr
    .split(',')
    .map(s => s.trim())
    .filter(Boolean)

  const payload = {
    name: editForm.value.name.trim(),
    category: editForm.value.category,
    trade: editForm.value.trade.trim(),
    phone: editForm.value.phone.trim(),
    whatsapp: editForm.value.whatsapp.replace(/\D/g, ''),
    status: editForm.value.status,
    address: editForm.value.address.trim(),
    experience_years: editForm.value.experienceYears,
    verified_since: editForm.value.verifiedSince.trim(),
    response_time: editForm.value.responseTime.trim(),
    skills: skills.length ? skills : [editForm.value.trade],
    id_verified: editForm.value.idVerified,
    police_cleared: editForm.value.policeCleared,
    gate_pass_cleared: editForm.value.gatePassCleared,
  }

  try {
    const res = await artisansApi.update(current.id, payload)
    if (res.data?.artisan) {
      artisan.value = res.data.artisan
    } else {
      artisan.value = {
        ...current,
        name: payload.name,
        category: payload.category,
        trade: payload.trade,
        phone: payload.phone,
        whatsapp: payload.whatsapp,
        status: payload.status,
        address: payload.address,
        experienceYears: payload.experience_years,
        verifiedSince: payload.verified_since,
        responseTime: payload.response_time,
        skills: payload.skills,
        idVerified: payload.id_verified,
        policeCleared: payload.police_cleared,
        gatePassCleared: payload.gate_pass_cleared,
      }
    }
    if (artisan.value) {
      updateArtisan(current.id, artisan.value)
    }
    showEditModal.value = false
    showToast('Artisan profile updated successfully', 'success')
  } catch (err) {
    showToast('Failed to update artisan profile', 'error')
  } finally {
    saving.value = false
  }
}

async function submitResidentReview() {
  if (!artisan.value) return
  if (!newReviewForm.value.residentName.trim() || !newReviewForm.value.comment.trim()) {
    showToast('Please fill all review fields', 'error')
    return
  }

  try {
    const res = await artisansApi.addReview(artisan.value.id, {
      resident_name: newReviewForm.value.residentName.trim(),
      resident_unit: newReviewForm.value.residentUnit.trim() || 'Estate Resident',
      rating: newReviewForm.value.rating,
      comment: newReviewForm.value.comment.trim(),
    })

    if (res.data?.review && artisan.value) {
      artisan.value.reviews = artisan.value.reviews || []
      artisan.value.reviews.unshift(res.data.review)
      if (res.data.artisan?.rating) {
        artisan.value.rating = res.data.artisan.rating
      }
    }
    showAddReviewForm.value = false
    newReviewForm.value = { residentName: '', residentUnit: '', rating: 5, comment: '' }
    showToast('Resident review recorded successfully!', 'success')
    await refreshArtisan()
  } catch (err) {
    showToast('Failed to record review.', 'error')
  }
}

onMounted(() => {
  refreshArtisan()
})
</script>

<style scoped>
.artisan-detail-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
  max-width: 100%;
  margin: 0;
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
  gap: 12px;
}

.btn-disable {
  background: #ffffff;
  color: #DC2626;
  border: 1px solid #FECACA;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 150ms ease;
}

.btn-disable:hover:not(:disabled) {
  background: #FEF2F2;
  border-color: #F87171;
  color: #B91C1C;
}

.btn-enable {
  background: #ffffff;
  color: #059669;
  border: 1px solid #A7F3D0;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 8px 14px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 150ms ease;
}

.btn-enable:hover:not(:disabled) {
  background: #ECFDF5;
  border-color: #34D399;
  color: #047857;
}

/* ── Hero Banner ── */
.hero-banner {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 24px 28px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.hero-main-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
}

.hero-profile-group {
  display: flex;
  align-items: center;
  gap: 20px;
}

.avatar-badge-hero {
  width: 72px;
  height: 72px;
  border-radius: 18px;
  color: #ffffff;
  font-size: 26px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: -0.5px;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.hero-identity {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.hero-name-row {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.hero-name {
  font-size: 24px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.5px;
  margin: 0;
}

.hero-pills-group {
  display: inline-flex;
  align-items: center;
  gap: 8px;
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

.status-pill--disabled,
.status-pill--inactive {
  background: #FEF2F2;
  color: #DC2626;
  border: 1px solid #FEE2E2;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.hero-trade {
  font-size: 14px;
  font-weight: 500;
  color: #4B5563;
  margin: 0;
}

.hero-meta-strip {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 12px;
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

/* Modal sizing and checklist styling */
.modal-dialog--artisan {
  max-width: 620px;
  max-height: 90vh;
  overflow-y: auto;
}

.checklist-group {
  background: #F9FAFB;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid #E5E7EB;
}

.checkbox-options {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 6px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12.5px;
  font-weight: 500;
  color: #374151;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  accent-color: #0A5C38;
  width: 15px;
  height: 15px;
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
