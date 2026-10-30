<template>
  <div class="artisans-page">
    <!-- Header -->
    <section class="page-header">
      <div>
        <div class="eyebrow">Estate Marketplace &amp; Directory</div>
        <h1 class="page-title">Verified Artisans &amp; Services</h1>
        <p class="page-desc">
          Onboard, verify, and manage trusted service providers accessible to estate residents through the Checkpass Marketplace.
        </p>
      </div>
      <div class="header-actions">
        <button class="btn btn-secondary" @click="showReportModal = true">
          📑 Generate Report
        </button>
        <button class="btn btn-secondary" @click="toggleSimulation">
          {{ simulateEmpty ? '↺ View Filled State' : '⚙ Empty Preview' }}
        </button>
        <button class="btn btn-primary" @click="openCreateModal">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
          </svg>
          Add Verified Artisan
        </button>
      </div>
    </section>

    <!-- KPI Metrics -->
    <div class="metrics-grid">
      <div class="metric-card">
        <div class="metric-icon metric-icon--green">🛠️</div>
        <div class="metric-data">
          <div class="metric-val">{{ simulateEmpty ? 0 : activeArtisansCount }}</div>
          <div class="metric-lbl">Active Providers</div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon metric-icon--blue">📂</div>
        <div class="metric-data">
          <div class="metric-val">{{ simulateEmpty ? 0 : categoriesCount }}</div>
          <div class="metric-lbl">Service Categories</div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon metric-icon--gold">⭐</div>
        <div class="metric-data">
          <div class="metric-val">{{ simulateEmpty ? '0.0' : avgRating }}</div>
          <div class="metric-lbl">Avg. Resident Rating</div>
        </div>
      </div>
      <div class="metric-card">
        <div class="metric-icon metric-icon--teal">💬</div>
        <div class="metric-data">
          <div class="metric-val">{{ simulateEmpty ? 0 : totalReviewsCount }}</div>
          <div class="metric-lbl">Neighbor Reviews</div>
        </div>
      </div>
    </div>

    <!-- Main Subtabs: Directory vs Resident Reviews & Inquiries -->
    <div class="nav-tabs-bar">
      <button
        class="nav-tab"
        :class="{ active: activeAdminTab === 'directory' }"
        @click="activeAdminTab = 'directory'"
      >
        <span>Artisans &amp; Providers</span>
        <span class="tab-badge">{{ artisansList.length }}</span>
      </button>
      <button
        class="nav-tab"
        :class="{ active: activeAdminTab === 'reviews' }"
        @click="activeAdminTab = 'reviews'"
      >
        <span>Resident Reviews &amp; Inquiries</span>
        <span class="tab-badge">{{ allResidentReviews.length }}</span>
      </button>
    </div>

    <!-- ── TAB 1: ARTISANS DIRECTORY ── -->
    <div v-if="activeAdminTab === 'directory'">
      <!-- Filters & Search Toolbar -->
      <div class="filters-toolbar">
      <div class="category-pills">
        <button
          v-for="cat in availableCategories"
          :key="cat"
          class="pill-btn"
          :class="{ active: selectedCategory === cat }"
          @click="selectedCategory = cat"
        >
          {{ cat }}
        </button>
      </div>

      <div class="search-box">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search artisan by name, trade or phone..."
          class="search-input"
        />
      </div>
    </div>

    <!-- EMPTY STATE -->
    <div v-if="simulateEmpty || filteredArtisans.length === 0" class="card empty-card">
      <div class="empty-icon-wrap">🛠️</div>
      <h3 class="empty-title">
        {{ simulateEmpty ? 'No Artisans Listed' : (searchQuery ? 'No matching service providers' : 'No artisans in this category') }}
      </h3>
      <p class="empty-desc">
        {{ simulateEmpty ? 'Your estate community marketplace is currently empty. Add trusted local plumbers, electricians, and cleaners so residents can easily find and review vetted help.' : 'Try adjusting your search query or selecting a different category filter.' }}
      </p>
      <div class="empty-actions">
        <button v-if="simulateEmpty" class="btn btn-primary" @click="simulateEmpty = false">
          Restore Sample Artisans
        </button>
        <button class="btn btn-secondary" @click="openCreateModal">
          + Add First Artisan
        </button>
      </div>
    </div>

    <!-- FILLED STATE: ARTISANS GRID -->
    <div v-else class="artisans-grid">
      <div
        v-for="artisan in filteredArtisans"
        :key="artisan.id"
        class="artisan-card card"
      >
        <div class="card-top-row">
          <div class="artisan-identity">
            <div class="avatar-badge" :style="{ background: artisan.avatarBg || '#0A5C38' }">
              {{ artisan.initials || 'AR' }}
            </div>
            <div>
              <h3 class="artisan-name">{{ artisan.name }}</h3>
              <div class="artisan-trade">{{ artisan.trade }}</div>
            </div>
          </div>
          <span class="status-badge" :class="artisan.status === 'Active' ? 'status-badge--active' : 'status-badge--inactive'">
            {{ artisan.status || 'Active' }}
          </span>
        </div>

        <div class="card-category-row">
          <span class="cat-tag">{{ artisan.category }}</span>
          <span class="price-tag">{{ artisan.priceTag || 'Rates on request' }}</span>
          <div class="rating-box">
            <span class="star-icon">★</span>
            <span class="rating-num">{{ artisan.rating || 5.0 }}</span>
            <span class="reviews-count">({{ (artisan.reviews && artisan.reviews.length) || 0 }})</span>
          </div>
        </div>

        <p class="artisan-bio">{{ artisan.bio }}</p>

        <div class="contact-strip">
          <div class="contact-item">
            <span class="contact-lbl">Phone:</span>
            <a :href="`tel:${artisan.phone}`" class="contact-val">{{ artisan.phone }}</a>
          </div>
          <div class="contact-item">
            <span class="contact-lbl">WhatsApp:</span>
            <a
              :href="`https://wa.me/${artisan.whatsapp}`"
              target="_blank"
              class="contact-val contact-val--wa"
            >
              Chat on WA ↗
            </a>
          </div>
        </div>

        <!-- Action footer -->
        <div class="card-footer">
          <button class="btn-text" @click="openReviewsModal(artisan)">
            Reviews ({{ (artisan.reviews && artisan.reviews.length) || 0 }})
          </button>
          <div class="footer-btn-group">
            <button class="btn btn-sm btn-secondary" @click="toggleArtisanStatus(artisan)">
              {{ artisan.status === 'Active' ? 'Deactivate' : 'Activate' }}
            </button>
            <button class="btn btn-sm btn-secondary" @click="openEditModal(artisan)">
              ✏️ Edit Profile
            </button>
            <button class="btn btn-sm btn-danger" @click="deleteArtisan(artisan.id)">
              Remove
            </button>
          </div>
        </div>
      </div>
    </div>
    </div>

    <!-- ── TAB 2: RESIDENT REVIEWS & INQUIRIES ── -->
    <section v-else class="reviews-tab-section">
      <div class="card table-card">
        <div class="card-header-row">
          <div>
            <h3 class="card-header-title">Resident Reviews &amp; Service Ratings</h3>
            <p class="card-header-sub">Direct feedback submitted by estate residents via the mobile app</p>
          </div>
          <button class="btn btn-sm btn-secondary" @click="exportReviewsCSV">
            📥 Export Reviews CSV
          </button>
        </div>

        <div v-if="allResidentReviews.length === 0" class="empty-placeholder">
          <div class="empty-icon">💬</div>
          <div class="empty-title">No resident reviews logged yet</div>
          <div class="empty-sub">Reviews submitted by residents from the Checkpass mobile app will show up here.</div>
        </div>

        <div v-else class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Resident &amp; Unit</th>
                <th>Artisan / Service</th>
                <th>Category</th>
                <th>Resident Rating</th>
                <th>Review Comment</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="rev in allResidentReviews" :key="rev.id">
                <td>
                  <strong>{{ rev.residentName }}</strong>
                  <div class="cell-sub">{{ rev.residentUnit }}</div>
                </td>
                <td>
                  <strong>{{ rev.artisanName }}</strong>
                  <div class="cell-sub">{{ rev.artisanCategory }}</div>
                </td>
                <td>
                  <span class="cat-tag">{{ rev.artisanCategory }}</span>
                </td>
                <td>
                  <span class="star-icon">★</span>
                  <strong>{{ rev.rating }}</strong><span class="rating-max">/5</span>
                </td>
                <td class="review-comment-cell">{{ rev.comment }}</td>
                <td class="cell-date">{{ rev.date }}</td>
                <td>
                  <button class="btn btn-sm btn-secondary" @click="openReviewsModalById(rev.artisanId)">
                    View Artisan
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ── MODAL: ADD / EDIT ARTISAN ── -->
    <div v-if="showArtisanModal" class="modal-overlay" @click.self="showArtisanModal = false">
      <div class="modal-dialog">
        <div class="modal-header">
          <div>
            <h3>{{ isEditing ? 'Edit Verified Artisan' : 'Add New Verified Artisan' }}</h3>
            <p>Vetted contact details and pricing guidance for residents</p>
          </div>
          <button class="modal-close" @click="showArtisanModal = false">&times;</button>
        </div>

        <form @submit.prevent="saveArtisan" class="modal-body">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Full Name*</label>
              <input
                v-model="artisanForm.name"
                type="text"
                required
                placeholder="e.g. Babatunde Ogunleye"
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Category*</label>
              <select v-model="artisanForm.category" required class="form-input">
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
          </div>

          <div class="form-group">
            <label class="form-label">Trade Title / Specialty*</label>
            <input
              v-model="artisanForm.trade"
              type="text"
              required
              placeholder="e.g. Certified AC Technician & Chiller Specialist"
              class="form-input"
            />
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Phone Number*</label>
              <input
                v-model="artisanForm.phone"
                type="tel"
                required
                placeholder="+234 803 123 4567"
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label class="form-label">WhatsApp Number (digits only)*</label>
              <input
                v-model="artisanForm.whatsapp"
                type="text"
                required
                placeholder="2348031234567"
                class="form-input"
              />
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Pricing / Call-Out Fee Guidance</label>
              <input
                v-model="artisanForm.priceTag"
                type="text"
                placeholder="e.g. From ₦5,000 call-out"
                class="form-input"
              />
            </div>
            <div class="form-group">
              <label class="form-label">Verification Status</label>
              <select v-model="artisanForm.status" class="form-input">
                <option value="Active">Active (Visible to Residents)</option>
                <option value="Under Review">Under Review</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Bio &amp; Qualifications*</label>
            <textarea
              v-model="artisanForm.bio"
              rows="3"
              required
              placeholder="Describe their experience, specialties, estate clearance status..."
              class="form-input form-textarea"
            ></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showArtisanModal = false">
              Cancel
            </button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              {{ saving ? 'Saving...' : (isEditing ? 'Save Changes' : 'Add to Marketplace') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── MODAL: REVIEWS DRAWER ── -->
    <div v-if="activeReviewsArtisan" class="modal-overlay" @click.self="activeReviewsArtisan = null">
      <div class="modal-dialog">
        <div class="modal-header">
          <div>
            <h3>{{ activeReviewsArtisan.name }} — Resident Feedback</h3>
            <p>{{ activeReviewsArtisan.trade }} &middot; {{ activeReviewsArtisan.category }}</p>
          </div>
          <button class="modal-close" @click="activeReviewsArtisan = null">&times;</button>
        </div>

        <div class="modal-body">
          <div v-if="!activeReviewsArtisan.reviews || activeReviewsArtisan.reviews.length === 0" class="no-reviews-note">
            No resident reviews submitted yet for this service provider.
          </div>
          <div v-else class="reviews-list">
            <div
              v-for="rev in activeReviewsArtisan.reviews"
              :key="rev.id"
              class="review-item"
            >
              <div class="review-item-head">
                <div>
                  <strong>{{ rev.residentName }}</strong>
                  <span class="review-unit"> ({{ rev.residentUnit }})</span>
                </div>
                <span class="review-time">{{ rev.date }}</span>
              </div>
              <div class="review-stars">
                <span v-for="s in 5" :key="s" class="star" :class="{ filled: s <= rev.rating }">★</span>
              </div>
              <p class="review-comment">{{ rev.comment }}</p>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" @click="activeReviewsArtisan = null">Close</button>
        </div>
      </div>
    </div>

    <!-- ── MODAL: EXECUTIVE SERVICES REPORT ── -->
    <div v-if="showReportModal" class="modal-overlay" @click.self="showReportModal = false">
      <div class="modal-dialog modal-dialog--lg">
        <div class="modal-header">
          <div>
            <h3>Estate Services &amp; Verified Artisans Executive Report</h3>
            <p>Official status and performance audit for estate facilities management</p>
          </div>
          <button class="modal-close" @click="showReportModal = false">&times;</button>
        </div>

        <div class="modal-body report-printable">
          <!-- Report Header Info -->
          <div class="report-meta-box">
            <div class="report-meta-item">
              <span class="meta-label">Estate Name:</span>
              <span class="meta-val">Checkpass Estate Management</span>
            </div>
            <div class="report-meta-item">
              <span class="meta-label">Generated Date:</span>
              <span class="meta-val">{{ new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}</span>
            </div>
            <div class="report-meta-item">
              <span class="meta-label">Report ID:</span>
              <span class="meta-val">REP-ART-{{ Date.now().toString().slice(-6) }}</span>
            </div>
          </div>

          <!-- Summary Metrics Cards -->
          <div class="report-kpi-grid">
            <div class="kpi-box">
              <div class="kpi-num">{{ activeArtisansCount }}</div>
              <div class="kpi-title">Active Providers</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-num">{{ categoriesCount }}</div>
              <div class="kpi-title">Categories</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-num">⭐ {{ avgRating }}</div>
              <div class="kpi-title">Average Rating</div>
            </div>
            <div class="kpi-box">
              <div class="kpi-num">{{ totalReviewsCount }}</div>
              <div class="kpi-title">Resident Reviews</div>
            </div>
          </div>

          <!-- Providers Table -->
          <div class="report-table-section">
            <h4>Verified Service Providers Roster</h4>
            <table class="report-table">
              <thead>
                <tr>
                  <th>Provider Name</th>
                  <th>Category</th>
                  <th>Specialty / Trade</th>
                  <th>Contact Phone</th>
                  <th>Pricing Guidance</th>
                  <th>Resident Rating</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="a in artisansList" :key="a.id">
                  <td><strong>{{ a.name }}</strong></td>
                  <td>{{ a.category }}</td>
                  <td>{{ a.trade }}</td>
                  <td>{{ a.phone }}</td>
                  <td>{{ a.priceTag || 'Rates on request' }}</td>
                  <td>⭐ {{ a.rating }} ({{ (a.reviews && a.reviews.length) || 0 }})</td>
                  <td>
                    <span :class="a.status === 'Active' ? 'text-green' : 'text-amber'">
                      {{ a.status || 'Active' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showReportModal = false">Close</button>
          <button class="btn btn-secondary" @click="exportReportCSV">
            📥 Download CSV
          </button>
          <button class="btn btn-primary" @click="printReport">
            🖨️ Print / Save PDF
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useToast } from '@/composables/useToast'

export interface ArtisanReview {
  id: string
  residentName: string
  residentUnit: string
  rating: number
  comment: string
  date: string
}

export interface ArtisanItem {
  id: string
  name: string
  trade: string
  category: string
  phone: string
  whatsapp: string
  rating: number
  priceTag: string
  avatarBg: string
  initials: string
  bio: string
  status?: 'Active' | 'Under Review' | 'Inactive'
  reviews: ArtisanReview[]
}

const { showToast } = useToast()
const STORAGE_KEY = 'cp_estate_artisans'

const defaultArtisans: ArtisanItem[] = [
  {
    id: 'art-1',
    name: 'Chinedu Okafor',
    trade: 'Senior Electrician & Inverter Specialist',
    category: 'Electrician',
    phone: '+2348031234567',
    whatsapp: '2348031234567',
    rating: 4.9,
    priceTag: 'From ₦4,000 call-out',
    avatarBg: '#0A5C38',
    initials: 'CO',
    status: 'Active',
    bio: 'Over 12 years experience in high-grade residential electrical wiring, lithium/tubular inverter backup installations, solar hybrid integration, and DB panel troubleshooting.',
    reviews: [
      {
        id: 'rev-1',
        residentName: 'Engr. Dapo Alabi',
        residentUnit: 'House 14 • Palm Court',
        rating: 5,
        comment: 'Chinedu arrived in under 20 minutes when my inverter tripped. Very knowledgeable, neat cabling, and fair billing.',
        date: '2 days ago',
      },
      {
        id: 'rev-2',
        residentName: 'Mrs. Fatima Bello',
        residentUnit: 'Flat 3B • Block 1',
        rating: 5,
        comment: 'Replaced my water heater circuit breaker cleanly. No mess left behind, fully tested and verified.',
        date: 'Last week',
      },
    ],
  },
  {
    id: 'art-2',
    name: 'Musa Abdullahi',
    trade: 'Master Plumber & Water Pressure Engineer',
    category: 'Plumber',
    phone: '+2348023456789',
    whatsapp: '2348023456789',
    rating: 4.8,
    priceTag: 'From ₦3,500 call-out',
    avatarBg: '#1D4ED8',
    initials: 'MA',
    status: 'Active',
    bio: 'Specialist in overhead tank float valves, submersible pump repairs, silent pipe leak detection, PPR pipe fusion, and bathroom sanitary fixtures.',
    reviews: [
      {
        id: 'rev-3',
        residentName: 'Chief Emeka Eze',
        residentUnit: 'Unit 9A • Hibiscus Lane',
        rating: 5,
        comment: 'Resolved an annoying overhead tank overflow that two other plumbers failed to fix. Very polite gentleman.',
        date: '3 days ago',
      },
    ],
  },
  {
    id: 'art-3',
    name: 'Emmanuel Bassey',
    trade: 'HVAC & Inverter AC Specialist',
    category: 'AC Repair',
    phone: '+2348056789012',
    whatsapp: '2348056789012',
    rating: 4.9,
    priceTag: 'From ₦5,000 call-out',
    avatarBg: '#B45309',
    initials: 'EB',
    status: 'Active',
    bio: 'Expert diagnostic servicing for all inverter and split air conditioners (LG, Daikin, Panasonic, Midea). Gas top-ups, chemical jet washing, and motherboard repairs.',
    reviews: [
      {
        id: 'rev-4',
        residentName: 'Dr. Chinedu Okafor',
        residentUnit: 'House 4B • Emerald St',
        rating: 5,
        comment: 'Cleaned and recharged two inverter units. Excellent cooling restored and very neat drop cloths used indoors.',
        date: '4 days ago',
      },
    ],
  },
  {
    id: 'art-4',
    name: 'SparklePro Cleaners (Mrs. Joy)',
    trade: 'Deep Home & Move-In Cleaning',
    category: 'Cleaning',
    phone: '+2348089012345',
    whatsapp: '2348089012345',
    rating: 4.7,
    priceTag: 'From ₦15,000 deep clean',
    avatarBg: '#7C3AED',
    initials: 'SP',
    status: 'Active',
    bio: 'Professional residential cleaning crew. Post-construction scrubbing, upholstery steam wash, kitchen degreasing, and routine weekly housekeeping.',
    reviews: [
      {
        id: 'rev-5',
        residentName: 'Tolu Oladipo',
        residentUnit: 'Flat 12 • Block 5',
        rating: 5,
        comment: 'SparklePro did my move-in cleaning. Kitchen cabinets looked brand new.',
        date: '5 days ago',
      },
    ],
  },
  {
    id: 'art-5',
    name: 'Sunday Adeyemi',
    trade: 'Furniture Maker & Locksmith',
    category: 'Carpentry',
    phone: '+2348078901234',
    whatsapp: '2348078901234',
    rating: 4.8,
    priceTag: 'From ₦4,000 call-out',
    avatarBg: '#059669',
    initials: 'SA',
    status: 'Active',
    bio: 'Bespoke cabinet repairs, security lock installations, sliding door track realignments, and custom kitchen island refurbishment.',
    reviews: [
      {
        id: 'rev-6',
        residentName: 'Pastor Segun Martins',
        residentUnit: 'House 22 • Palm Court',
        rating: 5,
        comment: 'Fixed our master bedroom sliding wardrobe door that was off track for months. Sturdy craftsmanship.',
        date: '1 week ago',
      },
    ],
  },
]

function loadArtisans(): ArtisanItem[] {
  try {
    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved) return JSON.parse(saved)
  } catch {}
  return [...defaultArtisans]
}

const artisansList = ref<ArtisanItem[]>(loadArtisans())

function persistArtisans() {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(artisansList.value))
  } catch {}
}

const simulateEmpty = ref(false)
function toggleSimulation() {
  simulateEmpty.value = !simulateEmpty.value
}

const selectedCategory = ref('All')
const searchQuery = ref('')

const availableCategories = ['All', 'Electrician', 'Plumber', 'AC Repair', 'Cleaning', 'Carpentry', 'Painting', 'Generator Tech', 'Other']

const filteredArtisans = computed(() => {
  if (simulateEmpty.value) return []
  return artisansList.value.filter(a => {
    const matchCat = selectedCategory.value === 'All' || a.category === selectedCategory.value
    const q = searchQuery.value.toLowerCase().trim()
    const matchQuery = !q ||
      a.name.toLowerCase().includes(q) ||
      a.trade.toLowerCase().includes(q) ||
      a.category.toLowerCase().includes(q) ||
      a.phone.includes(q)
    return matchCat && matchQuery
  })
})

const activeArtisansCount = computed(() =>
  artisansList.value.filter(a => a.status === 'Active' || !a.status).length
)

const categoriesCount = computed(() => {
  const set = new Set(artisansList.value.map(a => a.category))
  return set.size
})

const avgRating = computed(() => {
  if (!artisansList.value.length) return '0.0'
  const total = artisansList.value.reduce((acc, a) => acc + (a.rating || 5), 0)
  return (total / artisansList.value.length).toFixed(1)
})

const totalReviewsCount = computed(() => {
  return artisansList.value.reduce((acc, a) => acc + (a.reviews?.length || 0), 0)
})

const activeAdminTab = ref<'directory' | 'reviews'>('directory')
const showReportModal = ref(false)

const allResidentReviews = computed(() => {
  const reviews: Array<ArtisanReview & { artisanName: string; artisanCategory: string; artisanId: string }> = []
  for (const a of artisansList.value) {
    if (a.reviews && a.reviews.length) {
      for (const r of a.reviews) {
        reviews.push({
          ...r,
          artisanName: a.name,
          artisanCategory: a.category,
          artisanId: a.id,
        })
      }
    }
  }
  return reviews
})

function openReviewsModalById(artisanId: string) {
  const art = artisansList.value.find(a => a.id === artisanId)
  if (art) {
    activeReviewsArtisan.value = art
  }
}

function exportReportCSV() {
  const headers = ['ID', 'Name', 'Category', 'Trade', 'Phone', 'Price Guidance', 'Status', 'Rating', 'Reviews Count']
  const rows = artisansList.value.map(a => [
    a.id,
    `"${a.name.replace(/"/g, '""')}"`,
    `"${a.category}"`,
    `"${a.trade.replace(/"/g, '""')}"`,
    `"${a.phone}"`,
    `"${(a.priceTag || '').replace(/"/g, '""')}"`,
    a.status || 'Active',
    a.rating,
    a.reviews?.length || 0,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `Estate_Artisans_Report_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  showToast('Artisans & Services report downloaded successfully!', 'success')
}

function exportReviewsCSV() {
  const headers = ['Review ID', 'Resident Name', 'Resident Unit', 'Artisan Name', 'Category', 'Rating', 'Comment', 'Date']
  const rows = allResidentReviews.value.map(r => [
    r.id,
    `"${r.residentName.replace(/"/g, '""')}"`,
    `"${r.residentUnit.replace(/"/g, '""')}"`,
    `"${r.artisanName.replace(/"/g, '""')}"`,
    `"${r.artisanCategory}"`,
    r.rating,
    `"${r.comment.replace(/"/g, '""')}"`,
    `"${r.date}"`,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `Resident_Reviews_Report_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  showToast('Resident reviews exported successfully!', 'success')
}

function printReport() {
  window.print()
}

// ── ADD / EDIT MODAL ──
const showArtisanModal = ref(false)
const isEditing = ref(false)
const editingId = ref<string | null>(null)
const saving = ref(false)

const artisanForm = ref({
  name: '',
  trade: '',
  category: 'Electrician',
  phone: '',
  whatsapp: '',
  priceTag: '',
  status: 'Active' as 'Active' | 'Under Review' | 'Inactive',
  bio: '',
})

function openCreateModal() {
  isEditing.value = false
  editingId.value = null
  artisanForm.value = {
    name: '',
    trade: '',
    category: 'Electrician',
    phone: '',
    whatsapp: '',
    priceTag: '',
    status: 'Active',
    bio: '',
  }
  showArtisanModal.value = true
}

function openEditModal(artisan: ArtisanItem) {
  isEditing.value = true
  editingId.value = artisan.id
  artisanForm.value = {
    name: artisan.name,
    trade: artisan.trade,
    category: artisan.category,
    phone: artisan.phone,
    whatsapp: artisan.whatsapp,
    priceTag: artisan.priceTag || '',
    status: artisan.status || 'Active',
    bio: artisan.bio,
  }
  showArtisanModal.value = true
}

function getInitials(name: string): string {
  return name
    .split(' ')
    .map(p => p[0])
    .filter(Boolean)
    .slice(0, 2)
    .join('')
    .toUpperCase() || 'AR'
}

function getAvatarColor(category: string): string {
  switch (category) {
    case 'Electrician': return '#0A5C38'
    case 'Plumber': return '#1D4ED8'
    case 'AC Repair': return '#B45309'
    case 'Cleaning': return '#7C3AED'
    case 'Carpentry': return '#059669'
    case 'Painting': return '#BE185D'
    case 'Generator Tech': return '#D97706'
    default: return '#4B5563'
  }
}

function saveArtisan() {
  saving.value = true
  try {
    if (isEditing.value && editingId.value) {
      const idx = artisansList.value.findIndex(a => a.id === editingId.value)
      if (idx !== -1) {
        artisansList.value[idx] = {
          ...artisansList.value[idx],
          name: artisanForm.value.name.trim(),
          trade: artisanForm.value.trade.trim(),
          category: artisanForm.value.category,
          phone: artisanForm.value.phone.trim(),
          whatsapp: artisanForm.value.whatsapp.replace(/\D/g, ''),
          priceTag: artisanForm.value.priceTag.trim() || 'Rates on request',
          status: artisanForm.value.status,
          bio: artisanForm.value.bio.trim(),
          initials: getInitials(artisanForm.value.name),
          avatarBg: getAvatarColor(artisanForm.value.category),
        }
        showToast('Artisan details updated successfully!', 'success')
      }
    } else {
      const newArtisan: ArtisanItem = {
        id: 'art-' + Date.now(),
        name: artisanForm.value.name.trim(),
        trade: artisanForm.value.trade.trim(),
        category: artisanForm.value.category,
        phone: artisanForm.value.phone.trim(),
        whatsapp: artisanForm.value.whatsapp.replace(/\D/g, '') || artisanForm.value.phone.replace(/\D/g, ''),
        priceTag: artisanForm.value.priceTag.trim() || 'Rates on request',
        status: artisanForm.value.status,
        bio: artisanForm.value.bio.trim(),
        rating: 5.0,
        initials: getInitials(artisanForm.value.name),
        avatarBg: getAvatarColor(artisanForm.value.category),
        reviews: [],
      }
      artisansList.value.unshift(newArtisan)
      showToast('New artisan added to estate marketplace!', 'success')
    }
    persistArtisans()
    showArtisanModal.value = false
    simulateEmpty.value = false
  } catch {
    showToast('Failed to save artisan details.', 'error')
  } finally {
    saving.value = false
  }
}

function toggleArtisanStatus(artisan: ArtisanItem) {
  artisan.status = artisan.status === 'Active' ? 'Inactive' : 'Active'
  persistArtisans()
  showToast(`Artisan status set to ${artisan.status}`, 'warning')
}

function deleteArtisan(id: string) {
  if (confirm('Are you sure you want to remove this artisan from the estate marketplace?')) {
    artisansList.value = artisansList.value.filter(a => a.id !== id)
    persistArtisans()
    showToast('Artisan removed from marketplace directory.', 'success')
  }
}

// ── REVIEWS MODAL ──
const activeReviewsArtisan = ref<ArtisanItem | null>(null)
function openReviewsModal(artisan: ArtisanItem) {
  activeReviewsArtisan.value = artisan
}
</script>

<style scoped>
.artisans-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.eyebrow {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--c-primary, #0A5C38);
  margin-bottom: 4px;
}
.page-title {
  font-size: 24px;
  font-weight: 800;
  color: #112019;
  letter-spacing: -0.02em;
  margin: 0 0 6px;
}
.page-desc {
  font-size: 14px;
  color: #616F67;
  max-width: 640px;
  margin: 0;
  line-height: 1.5;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Metrics */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 14px;
}
.metric-card {
  background: var(--c-surface, #FFFFFF);
  border: 1px solid var(--c-border, #E2E8E4);
  border-radius: 16px;
  padding: 16px 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}
.metric-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}
.metric-icon--green { background: #E6F4EA; color: #0A5C38; }
.metric-icon--blue  { background: #EFF6FF; color: #1D4ED8; }
.metric-icon--gold  { background: #FEF3C7; color: #B45309; }
.metric-icon--teal  { background: #ECFDF5; color: #059669; }

.metric-val {
  font-size: 24px;
  font-weight: 800;
  color: #112019;
  line-height: 1.1;
}
.metric-lbl {
  font-size: 12px;
  font-weight: 600;
  color: #718278;
  margin-top: 3px;
}

/* Toolbar */
.filters-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
.category-pills {
  display: flex;
  align-items: center;
  gap: 6px;
  overflow-x: auto;
  padding-bottom: 2px;
}
.pill-btn {
  border: 1px solid #D5DFD8;
  background: #FFFFFF;
  color: #556B60;
  font-size: 13px;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 999px;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.15s ease;
}
.pill-btn.active {
  background: var(--c-primary, #0A5C38);
  border-color: var(--c-primary, #0A5C38);
  color: #FFFFFF;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #FFFFFF;
  border: 1px solid #D5DFD8;
  border-radius: 999px;
  padding: 6px 16px;
  width: 320px;
  max-width: 100%;
  color: #616F67;
}
.search-input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 13px;
  color: #112019;
  width: 100%;
}

/* Artisans Grid */
.artisans-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 16px;
}
.artisan-card {
  background: #FFFFFF;
  border: 1px solid var(--c-border, #E2E8E4);
  border-radius: 18px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
  transition: transform 0.15s, box-shadow 0.15s;
}
.artisan-card:hover {
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
}

.card-top-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}
.artisan-identity {
  display: flex;
  align-items: center;
  gap: 12px;
}
.avatar-badge {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  color: #FFFFFF;
  font-size: 15px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.artisan-name {
  font-size: 16px;
  font-weight: 700;
  color: #112019;
  margin: 0;
}
.artisan-trade {
  font-size: 13px;
  color: #616F67;
  margin-top: 2px;
}

.status-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
}
.status-badge--active {
  background: #E6F4EA;
  color: #0A5C38;
}
.status-badge--inactive {
  background: #F3F4F6;
  color: #6B7280;
}

.card-category-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.cat-tag {
  background: #E8EFEA;
  color: #0A5C38;
  font-size: 11.5px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 999px;
}
.price-tag {
  background: #F3F4F6;
  color: #374151;
  font-size: 11.5px;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 999px;
}
.rating-box {
  display: flex;
  align-items: center;
  gap: 3px;
  margin-left: auto;
  font-size: 12px;
  font-weight: 700;
  color: #B45309;
}
.star-icon { color: #F59E0B; }
.reviews-count { color: #8C9991; font-weight: 500; font-size: 11px; }

.artisan-bio {
  font-size: 13px;
  line-height: 1.5;
  color: #4B5563;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.contact-strip {
  background: #F8FAF9;
  border-radius: 10px;
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.contact-item {
  display: flex;
  justify-content: space-between;
  font-size: 12.5px;
}
.contact-lbl { color: #718278; font-weight: 600; }
.contact-val { color: #112019; font-weight: 600; text-decoration: none; }
.contact-val--wa { color: #0A5C38; font-weight: 700; }

.card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px solid #EEF2F0;
  padding-top: 14px;
  margin-top: auto;
}
.btn-text {
  background: none;
  border: none;
  color: #0A5C38;
  font-size: 12.5px;
  font-weight: 700;
  cursor: pointer;
  padding: 0;
}
.footer-btn-group {
  display: flex;
  gap: 6px;
}

/* Empty State */
.empty-card {
  padding: 56px 24px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #FFFFFF;
  border: 1px dashed #B8C8BF;
  border-radius: 20px;
}
.empty-icon-wrap {
  font-size: 44px;
  margin-bottom: 12px;
}
.empty-title {
  font-size: 18px;
  font-weight: 700;
  color: #112019;
  margin: 0 0 6px;
}
.empty-desc {
  font-size: 13.5px;
  color: #616F67;
  max-width: 480px;
  line-height: 1.5;
  margin: 0 0 20px;
}
.empty-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  justify-content: center;
}

/* Buttons */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 9px 16px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  border: none;
  transition: all 0.15s ease;
}
.btn-primary {
  background: var(--c-primary, #0A5C38);
  color: #FFFFFF;
}
.btn-primary:hover {
  background: #08482b;
}
.btn-secondary {
  background: #E8EFEA;
  color: #0A5C38;
}
.btn-secondary:hover {
  background: #d9e5dc;
}
.btn-danger {
  background: #FEE2E2;
  color: #DC2626;
}
.btn-danger:hover {
  background: #fecaca;
}
.btn-sm {
  padding: 5px 10px;
  font-size: 11.5px;
  border-radius: 7px;
}

/* Modals */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(4px);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
}
.modal-dialog {
  background: #FFFFFF;
  border-radius: 20px;
  width: 100%;
  max-width: 540px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
}
.modal-header {
  padding: 20px 24px 16px;
  border-bottom: 1px solid #EEF2F0;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
}
.modal-header h3 {
  font-size: 18px;
  font-weight: 800;
  color: #112019;
  margin: 0;
}
.modal-header p {
  font-size: 12.5px;
  color: #616F67;
  margin: 3px 0 0;
}
.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  color: #8C9991;
  cursor: pointer;
}
.modal-body {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form-label {
  font-size: 12.5px;
  font-weight: 700;
  color: #2D3A33;
}
.form-input {
  border: 1px solid #D5DFD8;
  border-radius: 10px;
  padding: 9px 12px;
  font-size: 13.5px;
  color: #112019;
  outline: none;
  background: #FFFFFF;
  font-family: inherit;
}
.form-input:focus {
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.1);
}
.form-textarea {
  resize: vertical;
}
.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 12px;
  border-top: 1px solid #EEF2F0;
}

/* Reviews List in modal */
.no-reviews-note {
  padding: 24px;
  text-align: center;
  color: #718278;
  font-size: 13.5px;
}
.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.review-item {
  background: #F8FAF9;
  border-radius: 12px;
  padding: 12px 14px;
  border: 1px solid #E5EAE7;
}
.review-item-head {
  display: flex;
  justify-content: space-between;
  font-size: 13px;
  color: #112019;
}
.review-unit { color: #718278; font-weight: normal; }
.review-time { font-size: 11px; color: #8C9991; }
.review-stars { margin: 4px 0 6px; font-size: 12px; color: #D1D5DB; }
.review-stars .star.filled { color: #F59E0B; }
.review-comment { font-size: 12.5px; color: #374151; margin: 0; line-height: 1.45; }

/* ── Main Nav Tabs ── */
.nav-tabs-bar {
  display: flex;
  gap: 8px;
  border-bottom: 2px solid #E5EAE7;
  margin-bottom: 24px;
}
.nav-tab {
  background: none;
  border: none;
  padding: 12px 18px;
  font-size: 14.5px;
  font-weight: 600;
  color: #64746C;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 2px solid transparent;
  margin-bottom: -2px;
  transition: all 0.15s ease;
}
.nav-tab.active {
  color: #0A5C38;
  border-bottom-color: #0A5C38;
  font-weight: 700;
}
.tab-badge {
  background: #EAF7EE;
  color: #0A5C38;
  font-size: 11px;
  padding: 2px 7px;
  border-radius: 999px;
  font-weight: 700;
}
.nav-tab:hover:not(.active) {
  color: #112019;
}

/* ── Reviews Table Section ── */
.reviews-tab-section {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.card-header-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 20px;
  border-bottom: 1px solid #EEF2F0;
}
.card-header-title {
  font-size: 16px;
  font-weight: 700;
  color: #112019;
  margin: 0;
}
.card-header-sub {
  font-size: 12.5px;
  color: #64746C;
  margin: 2px 0 0;
}
.table-responsive {
  overflow-x: auto;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 13.5px;
}
.data-table th {
  background: #F8FAF9;
  padding: 12px 18px;
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #55655D;
  border-bottom: 1px solid #E5EAE7;
}
.data-table td {
  padding: 14px 18px;
  border-bottom: 1px solid #F1F4F2;
  color: #112019;
  vertical-align: middle;
}
.cell-sub {
  font-size: 11.5px;
  color: #718278;
  margin-top: 2px;
}
.rating-max {
  font-size: 11px;
  color: #94A3B8;
}
.review-comment-cell {
  max-width: 380px;
  color: #334155;
  line-height: 1.45;
}

/* ── Executive Report Modal ── */
.modal-dialog--lg {
  max-width: 820px;
}
.report-meta-box {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  background: #F8FAF9;
  padding: 14px 18px;
  border-radius: 12px;
  border: 1px solid #E5EAE7;
  margin-bottom: 20px;
}
.report-meta-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.meta-label {
  font-size: 11px;
  text-transform: uppercase;
  color: #64746C;
  font-weight: 600;
}
.meta-val {
  font-size: 13.5px;
  font-weight: 700;
  color: #112019;
}
.report-kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 12px;
  margin-bottom: 24px;
}
.kpi-box {
  background: #FFFFFF;
  border: 1px solid #E2E8E4;
  border-radius: 12px;
  padding: 12px;
  text-align: center;
}
.kpi-num {
  font-size: 20px;
  font-weight: 800;
  color: #0A5C38;
}
.kpi-title {
  font-size: 11.5px;
  color: #64746C;
  margin-top: 3px;
  font-weight: 600;
}
.report-table-section h4 {
  font-size: 14px;
  font-weight: 700;
  color: #112019;
  margin: 0 0 10px;
}
.report-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12.5px;
  border: 1px solid #E2E8E4;
}
.report-table th {
  background: #F1F5F3;
  padding: 8px 12px;
  font-size: 11px;
  font-weight: 700;
  color: #374151;
  text-align: left;
  border-bottom: 1px solid #E2E8E4;
}
.report-table td {
  padding: 10px 12px;
  border-bottom: 1px solid #E2E8E4;
  color: #1F2937;
}
.text-green { color: #059669; font-weight: 600; }
.text-amber { color: #D97706; font-weight: 600; }

@media print {
  body * {
    visibility: hidden;
  }
  .report-printable, .report-printable * {
    visibility: visible;
  }
  .report-printable {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>
