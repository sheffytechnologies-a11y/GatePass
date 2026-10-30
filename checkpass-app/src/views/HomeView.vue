<template>
  <ion-page>
    <ion-header class="ion-no-border">
      <ion-toolbar>
        <ion-title slot="start" class="home-title">{{ greeting }}, {{ firstName }} 👋</ion-title>
        <div slot="end">
          <NotifBell :count="notifStore.unreadCount" @click="router.push('/notifications')" />
        </div>
      </ion-toolbar>
    </ion-header>

    <ion-content :fullscreen="true" class="home-content">
      <ion-refresher slot="fixed" @ionRefresh="onRefresh">
        <ion-refresher-content />
      </ion-refresher>

      <div class="content-pad" v-if="!summaryLoading && !summaryError">

        <!-- ── SECURITY VIEW ─────────────────────────────── -->
        <template v-if="auth.isSecurityUser">
          <!-- Scan QR button -->
          <button class="scan-qr-btn" @click="scannerOpen = true">
            <span class="scan-icon">📷</span>
            <div>
              <div class="scan-btn-title">Scan QR Pass</div>
              <div class="scan-btn-sub">Verify visitor at the gate</div>
            </div>
          </button>

          <button class="phrase-entry-btn" @click="phraseSheetOpen = true">
            <span class="scan-icon">🗣️</span>
            <div>
              <div class="phrase-entry-title">Visitor Without Phone</div>
              <div class="phrase-entry-sub">Verify the unit's daily access phrase</div>
            </div>
          </button>

          <div class="security-stats">
            <div class="stat-card">
              <div class="stat-value">{{ summary.stats.onPremises }}</div>
              <div class="stat-label">On-site</div>
            </div>
            <div class="stat-card">
              <div class="stat-value">{{ summary.stats.activePasses }}</div>
              <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
              <div class="stat-value">{{ summary.stats.newToday }}</div>
              <div class="stat-label">Today</div>
            </div>
          </div>

          <section class="section">
            <div class="section-header">
              <div class="section-title">Today's Passes</div>
            </div>
            <div v-if="summary.recentPasses.length === 0">
              <EmptyState icon="🎟️" heading="No passes today" body="No visitor passes have been created today." />
            </div>
            <div v-else class="pass-list">
              <PassCard
                v-for="pass in summary.recentPasses"
                :key="pass.id"
                :pass="pass"
                @click="router.push(`/pass/${pass.id}`)"
              />
            </div>
          </section>
        </template>

        <!-- ── RESIDENT VIEW ─────────────────────────────── -->
        <template v-else>
          <EstateHero
            :flat-address="auth.resident?.flatAddress || ''"
            :estate-name="auth.resident?.estateName || ''"
            :gate-open="true"
            :stats="summary.stats"
          />

          <AccessPhraseCard />

          <section class="section">
            <div class="passes-shell">
              <!-- Sticky Tab Navigation Bar (fixed when reaching the top) -->
              <div class="passes-tabs-sticky-bar">
                <div class="passes-tabs">
                  <button
                    class="passes-tab"
                    :class="{ active: activeHomeTab === 'passes' }"
                    @click="activeHomeTab = 'passes'"
                  >
                    <span>Passes</span>
                  </button>
                  <button
                    class="passes-tab"
                    :class="{ active: activeHomeTab === 'news' }"
                    @click="activeHomeTab = 'news'"
                  >
                    <span>News</span>
                    <span v-if="unreadNewsCount > 0" class="tab-unread-pill">{{ unreadNewsCount }}</span>
                  </button>
                  <button
                    class="passes-tab"
                    :class="{ active: activeHomeTab === 'services' }"
                    @click="activeHomeTab = 'services'"
                  >
                    <span>Services</span>
                  </button>
                </div>
              </div>

              <!-- ── PASSES TAB ── -->
              <template v-if="activeHomeTab === 'passes'">
                <div v-if="activePasses.length === 0">
                  <EmptyState
                    icon="🎟️"
                    heading="No active passes"
                    body="Create a pass for your next visitor and it will appear here."
                    cta-label="Create a pass"
                    @cta="router.push('/tabs/create')"
                  />
                </div>
                <div v-else class="pass-mini-list">
                  <button
                    v-for="pass in activePasses"
                    :key="pass.id"
                    class="pass-mini-row"
                    @click="router.push(`/pass/${pass.id}`)"
                  >
                    <div class="pass-mini-avatar" :style="{ background: avatarColor(pass.visitorName) }">
                      {{ initials(pass.visitorName) }}
                    </div>
                    <div class="pass-mini-body">
                      <div class="pass-mini-name">{{ pass.visitorName }}</div>
                      <div class="pass-mini-purpose">{{ pass.purpose || 'Personal Visit' }}</div>
                    </div>
                    <span class="pass-mini-status" :class="statusClass(pass.status)">{{ pass.status }}</span>
                  </button>

                  <button class="view-all-btn" @click="router.push('/tabs/passes')">View all passes</button>
                </div>
              </template>

              <!-- ── NEWS TAB ── -->
              <template v-else-if="activeHomeTab === 'news'">
                <div class="news-section-wrap">
                  <!-- News Filter and Action Bar -->
                  <div class="news-filter-bar">
                    <div class="news-filter-chips">
                      <button
                        class="n-chip"
                        :class="{ active: activeNewsFilter === 'all' }"
                        @click="activeNewsFilter = 'all'"
                      >
                        All ({{ activeNewsList.length }})
                      </button>
                      <button
                        class="n-chip"
                        :class="{ active: activeNewsFilter === 'unread' }"
                        @click="activeNewsFilter = 'unread'"
                      >
                        Unread ({{ unreadNews.length }})
                      </button>
                      <button
                        class="n-chip"
                        :class="{ active: activeNewsFilter === 'read' }"
                        @click="activeNewsFilter = 'read'"
                      >
                        Read ({{ readNews.length }})
                      </button>
                    </div>

                    <button
                      v-if="unreadNews.length > 0"
                      class="mark-all-read-btn"
                      @click="markAllNewsRead"
                    >
                      Mark all read
                    </button>
                  </div>

                  <div v-if="newsLoading" class="news-mini-empty">Loading estate announcements...</div>
                  <div v-else-if="displayedNews.length === 0" class="news-empty-container">
                    <EmptyState
                      icon="📰"
                      :heading="activeNewsFilter === 'unread' ? 'No unread announcements' : 'No announcements available'"
                      :body="activeNewsFilter === 'unread' ? 'You have read all recent estate notices.' : (dismissedNewsIds.length > 0 ? 'You have dismissed previous announcements.' : 'No estate announcements published yet.')"
                      :cta-label="dismissedNewsIds.length > 0 ? 'Restore Dismissed News' : ''"
                      @cta="restoreDismissedNews"
                    />
                  </div>
                  <div v-else class="news-feed-list">
                    <div
                      v-for="item in displayedNews"
                      :key="item.id"
                      class="news-card-item"
                      :class="{ 'is-unread': !readNewsIds.includes(item.id) }"
                      @click="openNewsArticle(item)"
                    >
                      <div class="news-card-header">
                        <div class="news-badge-group">
                          <span v-if="!readNewsIds.includes(item.id)" class="news-status-badge news-status-badge--unread">
                            ● New
                          </span>
                          <span v-else class="news-status-badge news-status-badge--read">
                            Read
                          </span>
                          <span class="news-date-text">{{ fmtNewsDate(item.createdAt) }}</span>
                        </div>

                        <!-- Delete / Dismiss Button -->
                        <button
                          class="news-delete-btn"
                          title="Dismiss announcement"
                          @click.stop="dismissNews(item.id, $event)"
                        >
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                          </svg>
                        </button>
                      </div>

                      <div class="news-card-title">{{ item.title }}</div>
                      <div class="news-card-snippet">{{ item.content }}</div>

                      <div class="news-card-footer">
                        <span class="read-more-link">Tap to read full notice &rarr;</span>
                      </div>
                    </div>
                  </div>
                </div>
              </template>

              <!-- ── SERVICES TAB ── -->
              <template v-else-if="activeHomeTab === 'services'">
                <div class="marketplace-wrap">
                  <!-- Service Category Filter Row -->
                  <div class="category-scroll-row">
                    <button
                      v-for="cat in serviceCategories"
                      :key="cat"
                      class="cat-chip"
                      :class="{ active: activeServiceCategory === cat }"
                      @click="activeServiceCategory = cat"
                    >
                      {{ cat }}
                    </button>
                    <!-- State toggle for testing empty vs filled -->
                    <button
                      class="preview-toggle-chip"
                      @click="simulateEmptyServices = !simulateEmptyServices"
                    >
                      {{ simulateEmptyServices ? '↺ Show Filled' : '⚙ Empty Preview' }}
                    </button>
                  </div>

                  <!-- If Buy & Sell category is selected -->
                  <div v-if="activeServiceCategory === 'Buy & Sell'" class="market-section">
                    <div class="category-scroll-row" style="margin-bottom: 10px;">
                      <button
                        v-for="bCat in buyCategories"
                        :key="bCat"
                        class="cat-chip"
                        :class="{ active: activeBuyCategory === bCat }"
                        @click="activeBuyCategory = bCat"
                      >
                        {{ bCat }}
                      </button>
                    </div>

                    <div v-if="simulateEmptyServices || displayedBuyItems.length === 0" class="market-empty-box">
                      <EmptyState
                        icon="🛍️"
                        heading="No items for sale"
                        :body="simulateEmptyServices ? 'Previewing empty state: No community items have been listed in this category yet.' : 'No resident listings match right now.'"
                        :cta-label="simulateEmptyServices ? 'Restore Filled View' : 'View All Services'"
                        @cta="simulateEmptyServices ? (simulateEmptyServices = false) : (activeServiceCategory = 'All')"
                      />
                    </div>
                    <div v-else class="buy-items-grid">
                      <div v-for="item in displayedBuyItems" :key="item.id" class="buy-card">
                        <div class="buy-card-top">
                          <span class="buy-emoji-badge">{{ item.iconEmoji }}</span>
                          <span class="condition-badge" :class="item.condition.toLowerCase().replace(/\s+/g, '-')">
                            {{ item.condition }}
                          </span>
                        </div>
                        <div class="buy-card-body">
                          <div class="buy-item-title">{{ item.title }}</div>
                          <div class="buy-item-price">₦{{ item.price.toLocaleString('en-NG') }}</div>
                          <div class="buy-seller-row">
                            <span class="seller-unit">{{ item.sellerUnit }}</span>
                            <span class="time-ago">{{ item.timeAgo }}</span>
                          </div>
                        </div>
                        <a
                          :href="`https://wa.me/${item.whatsapp}?text=Hello,%20I%20saw%20your%20listing%20for%20${encodeURIComponent(item.title)}%20on%20Checkpass.`"
                          target="_blank"
                          class="contact-seller-btn"
                        >
                          <span>Contact Seller</span>
                          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>

                  <!-- Standard Services / Artisans -->
                  <div v-else class="market-section">
                    <!-- Services Empty State -->
                    <div v-if="simulateEmptyServices || displayedArtisans.length === 0" class="market-empty-box">
                      <EmptyState
                        icon="🛠️"
                        heading="No artisans found"
                        :body="simulateEmptyServices ? 'Previewing empty state: No verified artisans have been added to this category yet.' : 'No estate-vetted artisans are listed in this category right now.'"
                        :cta-label="simulateEmptyServices ? 'Restore Filled View' : 'View All Services'"
                        @cta="simulateEmptyServices ? (simulateEmptyServices = false) : (activeServiceCategory = 'All')"
                      />
                    </div>

                    <!-- Services Filled State (Clean card: opens details modal) -->
                    <div v-else class="artisan-list">
                      <div
                        v-for="artisan in displayedArtisans"
                        :key="artisan.id"
                        class="artisan-card"
                        @click="openArtisanModal(artisan)"
                      >
                        <div class="artisan-top">
                          <div class="artisan-avatar" :style="{ background: artisan.avatarBg }">
                            {{ artisan.initials }}
                          </div>
                          <div class="artisan-meta">
                            <div class="artisan-name-row">
                              <span class="artisan-name">{{ artisan.name }}</span>
                              <span class="vetted-inline-badge">✓ Verified</span>
                            </div>
                            <div class="artisan-trade">{{ artisan.trade }}</div>
                            <div class="artisan-rating-row">
                              <span class="rating-star">★</span>
                              <span class="rating-val">{{ artisan.rating }}</span>
                              <span class="rating-count">({{ artisan.reviews.length }} resident reviews)</span>
                            </div>
                          </div>
                          <div class="artisan-chevron-box">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M9 18l6-6-6-6"/>
                            </svg>
                          </div>
                        </div>

                        <div class="artisan-card-footer">
                          <span class="card-action-text">View Profile &amp; Reviews</span>
                          <span class="card-action-arrow">→</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </section>

          <!-- <section class="section">
            <div class="section-header">
              <div class="section-title">Recent Activity</div>
            </div>
            <div class="notif-list">
              <div
                v-for="n in recentNotifs"
                :key="n.id"
                class="notif-row"
                :class="{ unread: !n.read }"
                @click="goToNotif(n)"
              >
                <span class="notif-icon">{{ notifIcon(n.type) }}</span>
                <div class="notif-body">
                  <div class="notif-msg">{{ n.message }}</div>
                  <div class="notif-time">{{ fmtRelative(n.createdAt) }}</div>
                </div>
                <span v-if="!n.read" class="unread-dot" />
              </div>
              <div v-if="recentNotifs.length === 0" class="notif-empty">No recent activity</div>
            </div>
          </section> -->

        </template>

      </div>

      <!-- Loading -->
      <div v-else-if="summaryLoading" class="content-pad">
        <ion-skeleton-text class="sk-hero" animated />
        <div class="section">
          <SkeletonList :rows="2" />
        </div>
      </div>

      <!-- Error -->
      <div v-else class="content-pad">
        <ErrorState message="Couldn't load your dashboard." @retry="loadSummary" />
      </div>

    </ion-content>

    <!-- QR Scanner -->
    <QRScannerModal
      v-if="scannerOpen"
      :is-open="true"
      @close="scannerOpen = false"
      @scanned="onQRScanned"
    />

    <!-- Daily phrase entry (visitors without smartphones) -->
    <PhraseEntrySheet
      v-if="auth.isSecurityUser"
      :is-open="phraseSheetOpen"
      @close="phraseSheetOpen = false"
      @admitted="onPhraseAdmitted"
    />

    <!-- Artisan Details & Reviews Modal -->
    <ion-modal
      :is-open="!!selectedArtisan"
      class="artisan-sheet-modal"
      @didDismiss="closeArtisanModal"
    >
      <div v-if="selectedArtisan" class="sheet-modal-shell artisan-sheet-content">
        <!-- Top Header -->
        <div class="sheet-modal-header">
          <div>
            <div class="sheet-header-tag">Estate Verified Service</div>
            <h2 class="sheet-header-title">{{ selectedArtisan.name }}</h2>
          </div>
          <button class="modal-close-btn" @click="closeArtisanModal">&times;</button>
        </div>

        <div class="sheet-modal-body artisan-sheet-scroll">
          <!-- Artisan Profile Header -->
          <div class="artisan-sheet-header">
            <div class="artisan-sheet-avatar" :style="{ background: selectedArtisan.avatarBg }">
              {{ selectedArtisan.initials }}
            </div>
            <div class="artisan-sheet-info">
              <div class="artisan-sheet-name-row">
                <h2 class="artisan-sheet-name">{{ selectedArtisan.name }}</h2>
                <span class="vetted-badge-detail">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                  </svg>
                  Estate Verified
                </span>
              </div>
              <p class="artisan-sheet-trade">{{ selectedArtisan.trade }}</p>
              <div class="artisan-sheet-rating-meta">
                <div class="rating-stars-inline">
                  <span class="sheet-star">★</span>
                  <span class="sheet-rating-score">{{ selectedArtisan.rating }}</span>
                </div>
                <span class="rating-dot-separator">•</span>
                <span class="sheet-reviews-count">{{ selectedArtisan.reviews.length }} resident review{{ selectedArtisan.reviews.length === 1 ? '' : 's' }}</span>
                <span class="rating-dot-separator">•</span>
                <span class="sheet-category-tag">{{ selectedArtisan.category }}</span>
              </div>
            </div>
          </div>

          <!-- Artisan Bio & Rate Info -->
          <div class="artisan-info-card">
            <div v-if="selectedArtisan.priceTag" class="info-card-item">
              <span class="info-label">Standard Call-out / Estimate:</span>
              <span class="info-value price-highlight">{{ selectedArtisan.priceTag }}</span>
            </div>
            <div class="info-card-item">
              <span class="info-label">Estate Security Clearance:</span>
              <span class="info-value text-green">Cleared for Resident Access</span>
            </div>
            <p v-if="selectedArtisan.bio" class="artisan-bio-text">{{ selectedArtisan.bio }}</p>
          </div>

          <!-- Direct Actions: WhatsApp and Phone Call -->
          <div class="artisan-action-row">
            <a
              :href="`https://wa.me/${selectedArtisan.whatsapp}?text=Hello%20${encodeURIComponent(selectedArtisan.name)},%20I%20am%20a%20resident%20on%20Checkpass%20and%20need%20your%20services.`"
              target="_blank"
              class="artisan-btn-link btn-whatsapp-action"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
              </svg>
              <span>Chat on WhatsApp</span>
            </a>
            <a
              :href="`tel:${selectedArtisan.phone}`"
              class="artisan-btn-link btn-call-action"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
              <span>Call Direct</span>
            </a>
          </div>

          <!-- Reviews & Rating Section -->
          <div class="sheet-reviews-section">
            <div class="reviews-section-header">
              <div>
                <h3 class="reviews-section-title">Resident Reviews & Ratings</h3>
                <p class="reviews-section-sub">Verified feedback from neighbors in your estate</p>
              </div>
              <button
                class="write-review-btn"
                @click="showReviewForm = !showReviewForm"
              >
                {{ showReviewForm ? 'Cancel' : '✍️ Rate & Review' }}
              </button>
            </div>

            <!-- Review Writing Form -->
            <div v-if="showReviewForm" class="review-compose-card">
              <h4 class="compose-title">Review for {{ selectedArtisan.name }}</h4>

              <div class="rating-stars-picker">
                <span class="picker-label">Your Rating:</span>
                <div class="stars-row">
                  <button
                    v-for="star in 5"
                    :key="star"
                    type="button"
                    class="star-btn"
                    :class="{ selected: star <= newReviewRating }"
                    @click="newReviewRating = star"
                  >
                    ★
                  </button>
                </div>
                <span class="rating-label-text">{{ ratingLabel(newReviewRating) }}</span>
              </div>

              <textarea
                v-model="newReviewComment"
                class="review-textarea"
                rows="3"
                placeholder="Share your experience (e.g. arrived promptly, neat cabling, fair charges)..."
              />

              <button
                class="submit-review-btn"
                :disabled="submittingReview || !newReviewComment.trim()"
                @click="submitArtisanReview"
              >
                {{ submittingReview ? 'Posting Review...' : 'Submit Rating & Review' }}
              </button>
            </div>

            <!-- Reviews Feed List -->
            <div class="reviews-feed">
              <div
                v-for="rev in selectedArtisan.reviews"
                :key="rev.id"
                class="review-card-item"
              >
                <div class="review-header">
                  <div class="reviewer-meta">
                    <span class="reviewer-name">{{ rev.residentName }}</span>
                    <span class="reviewer-unit">{{ rev.residentUnit }}</span>
                  </div>
                  <span class="review-date">{{ rev.date }}</span>
                </div>
                <div class="review-stars-row">
                  <span
                    v-for="s in 5"
                    :key="s"
                    class="r-star"
                    :class="{ filled: s <= rev.rating }"
                  >
                    ★
                  </span>
                </div>
                <p class="review-comment-text">{{ rev.comment }}</p>
              </div>

              <div v-if="selectedArtisan.reviews.length === 0" class="no-reviews-box">
                No reviews yet. Be the first neighbor to rate this service!
              </div>
            </div>
          </div>
        </div>
      </div>
    </ion-modal>

    <!-- News Article Detail Modal -->
    <ion-modal
      :is-open="!!selectedNewsItem"
      class="news-modal-sheet"
      @didDismiss="selectedNewsItem = null"
    >
      <div v-if="selectedNewsItem" class="sheet-modal-shell news-sheet-content">
        <div class="sheet-modal-header">
          <div>
            <div class="sheet-header-tag">Estate Announcement</div>
            <h2 class="sheet-header-title">{{ selectedNewsItem.title }}</h2>
          </div>
          <button class="modal-close-btn" @click="selectedNewsItem = null">&times;</button>
        </div>

        <div class="sheet-modal-body news-sheet-scroll">
          <div class="news-sheet-meta">
            <span class="news-sheet-badge">Estate Announcement</span>
            <span class="news-sheet-date">{{ fmtNewsDate(selectedNewsItem.createdAt) }}</span>
          </div>

          <h2 class="news-sheet-title">{{ selectedNewsItem.title }}</h2>

          <div v-if="selectedNewsItem.image" class="news-sheet-img-wrap">
            <img :src="selectedNewsItem.image" :alt="selectedNewsItem.title" class="news-sheet-img" />
          </div>

          <div class="news-sheet-body">
            <p v-for="(paragraph, idx) in selectedNewsItem.content.split('\n')" :key="idx" class="news-sheet-p">
              {{ paragraph }}
            </p>
          </div>

          <div class="news-sheet-actions">
            <button
              class="news-dismiss-action-btn"
              @click="dismissNews(selectedNewsItem.id); selectedNewsItem = null"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
              </svg>
              <span>Dismiss from feed</span>
            </button>
            <button class="news-close-action-btn" @click="selectedNewsItem = null">
              Close
            </button>
          </div>
        </div>
      </div>
    </ion-modal>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  IonPage, IonHeader, IonToolbar, IonTitle, IonContent,
  IonRefresher, IonRefresherContent, IonSkeletonText, IonModal,
} from '@ionic/vue'
// import type { Notification } from '@/types'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import EstateHero from '@/components/EstateHero.vue'
import PassCard from '@/components/PassCard.vue'
import NotifBell from '@/components/NotifBell.vue'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonList from '@/components/SkeletonList.vue'
import ErrorState from '@/components/ErrorState.vue'
import QRScannerModal from '@/components/QRScannerModal.vue'
import AccessPhraseCard from '@/components/AccessPhraseCard.vue'
import PhraseEntrySheet from '@/components/PhraseEntrySheet.vue'
import client from '@/api/client'
import { useToast } from '@/composables/useToast'
import type { HomeSummary } from '@/types'

export type NewsFeedItem = {
  id: number
  title: string
  content: string
  image: string | null
  createdAt: string | null
}

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
  reviews: ArtisanReview[]
}

const router     = useRouter()
const auth       = useAuthStore()
const notifStore = useNotificationsStore()
const { showToast } = useToast()

const scannerOpen = ref(false)
const phraseSheetOpen = ref(false)

function onPhraseAdmitted(passId: string) {
  phraseSheetOpen.value = false
  showToast('Phrase verified — visitor allowed in')
  loadSummary()
  router.push(`/pass/${passId}`)
}

async function onQRScanned(value: string) {
  try {
    // value is the qr_data from the pass (ULID)
    const res = await client.get(`/v1/passes/${value}`)
    const passId = res.data.pass?.ulid || res.data.pass?.id || value
    router.push(`/pass/${passId}`)
  } catch {
    showToast('Pass not found or invalid QR code', 'error')
  }
}

const summaryLoading = ref(true)
const summaryError   = ref(false)
const summary        = ref<HomeSummary>({ resident: auth.resident!, stats: { onPremises: 0, activePasses: 0, newToday: 0 }, recentPasses: [], notifications: [] })
const activeHomeTab  = ref<'passes' | 'news' | 'services'>('passes')
const newsLoading    = ref(false)
const newsItems      = ref<NewsFeedItem[]>([])

// ── ESTATE ANNOUNCEMENTS (News) State ──────────────────────────
const defaultEstateNews: NewsFeedItem[] = [
  {
    id: 101,
    title: 'Routine Transformer Maintenance & Grid Switchover',
    content: 'Dear Residents, please be informed that the estate facilities management team will be conducting scheduled quarterly maintenance on the central power transformer this Saturday between 10:00 AM and 2:00 PM. Alternative backup generators will supply essential street lighting and security gate systems during the maintenance window.',
    image: null,
    createdAt: new Date(Date.now() - 3600 * 1000 * 4).toISOString(),
  },
  {
    id: 102,
    title: 'Access Control Security Notice: Visitor Pre-Registration',
    content: 'In accordance with our estate security protocol, all visitors, ride-hailing cabs, and delivery dispatch riders must have a valid Checkpass QR code or 6-digit access code generated prior to arrival at the main gates. Thank you for keeping our community safe.',
    image: null,
    createdAt: new Date(Date.now() - 3600 * 1000 * 28).toISOString(),
  },
  {
    id: 103,
    title: 'Monthly Estate Environmental Sanitation Exercise',
    content: 'This is a gentle reminder that the mandatory estate-wide clean-up exercise holds on the last Saturday of the month from 7:00 AM to 10:00 AM. Vehicular movement within the estate will be temporarily restricted during these hours.',
    image: null,
    createdAt: new Date(Date.now() - 3600 * 1000 * 72).toISOString(),
  },
]

const readNewsStorageKey = 'checkpass_read_news_ids'
const dismissedNewsStorageKey = 'checkpass_dismissed_news_ids'

const readNewsIds = ref<number[]>(JSON.parse(localStorage.getItem(readNewsStorageKey) || '[]'))
const dismissedNewsIds = ref<number[]>(JSON.parse(localStorage.getItem(dismissedNewsStorageKey) || '[]'))
const activeNewsFilter = ref<'all' | 'unread' | 'read'>('all')
const selectedNewsItem = ref<NewsFeedItem | null>(null)

// Combined active news list (backend items + default announcements, excluding dismissed)
const activeNewsList = computed(() => {
  const combined = [...newsItems.value]
  for (const def of defaultEstateNews) {
    if (!combined.some(n => n.id === def.id)) {
      combined.push(def)
    }
  }
  return combined.filter(n => !dismissedNewsIds.value.includes(n.id))
})

const unreadNews = computed(() => activeNewsList.value.filter(n => !readNewsIds.value.includes(n.id)))
const readNews = computed(() => activeNewsList.value.filter(n => readNewsIds.value.includes(n.id)))
const unreadNewsCount = computed(() => unreadNews.value.length)

const displayedNews = computed(() => {
  if (activeNewsFilter.value === 'unread') return unreadNews.value
  if (activeNewsFilter.value === 'read') return readNews.value
  return activeNewsList.value
})

function markNewsRead(id: number) {
  if (!readNewsIds.value.includes(id)) {
    readNewsIds.value.push(id)
    localStorage.setItem(readNewsStorageKey, JSON.stringify(readNewsIds.value))
  }
}

function markAllNewsRead() {
  for (const item of activeNewsList.value) {
    if (!readNewsIds.value.includes(item.id)) {
      readNewsIds.value.push(item.id)
    }
  }
  localStorage.setItem(readNewsStorageKey, JSON.stringify(readNewsIds.value))
  showToast('All announcements marked as read', 'success')
}

function openNewsArticle(item: NewsFeedItem) {
  markNewsRead(item.id)
  selectedNewsItem.value = item
}

function dismissNews(id: number, e?: Event) {
  if (e) e.stopPropagation()
  if (!dismissedNewsIds.value.includes(id)) {
    dismissedNewsIds.value.push(id)
    localStorage.setItem(dismissedNewsStorageKey, JSON.stringify(dismissedNewsIds.value))
    showToast('Announcement removed from your feed', 'warning')
  }
}

function restoreDismissedNews() {
  dismissedNewsIds.value = []
  localStorage.removeItem(dismissedNewsStorageKey)
  showToast('Dismissed announcements restored', 'success')
}

// ── SERVICES (Artisans & Community Buy/Sell) State ──────────────────────────
// Services (Artisans)
const serviceCategories = ['All', 'Electrician', 'Plumber', 'AC Repair', 'Cleaning', 'Carpentry', 'Buy & Sell']
const activeServiceCategory = ref('All')
const simulateEmptyServices = ref(false)

const artisansList = ref<ArtisanItem[]>([
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
        residentName: 'Mrs. Folake Johnson',
        residentUnit: 'Flat 4B • Block B',
        rating: 5,
        comment: 'He fixed our distribution board and labeled all circuits clearly. Highly recommended for any electrical fault.',
        date: 'Last week',
      },
      {
        id: 'rev-3',
        residentName: 'Tunde Bakare',
        residentUnit: 'Unit 9 • Jasmine',
        rating: 4.8,
        comment: 'Prompt and courteous. Replaced our changeover switch smoothly.',
        date: '2 weeks ago',
      },
    ],
  },
  {
    id: 'art-2',
    name: 'Sunday Adebayo',
    trade: 'Plumbing & Water Heaters',
    category: 'Plumber',
    phone: '+2348059876543',
    whatsapp: '2348059876543',
    rating: 4.8,
    priceTag: 'From ₦3,500 call-out',
    avatarBg: '#0284C7',
    initials: 'SA',
    bio: 'Certified plumbing specialist. Expert in borehole pressure pumps, water heater installations, PPR pipe welding, and hidden underground leak detection.',
    reviews: [
      {
        id: 'rev-4',
        residentName: 'Dr. Stella Okon',
        residentUnit: 'House 7 • Coral Close',
        rating: 5,
        comment: 'Diagnosed a stubborn pipe leakage under our kitchen tile without breaking excessive walls. Top-tier professional.',
        date: '3 days ago',
      },
      {
        id: 'rev-5',
        residentName: 'Emeka Umeh',
        residentUnit: 'Flat 2C • Pine Block',
        rating: 4.5,
        comment: 'Installed 2 Ariston water heaters in our bathrooms. Neat fittings and no drips.',
        date: '10 days ago',
      },
    ],
  },
  {
    id: 'art-3',
    name: 'Musa Ibrahim',
    trade: 'HVAC & AC Service / Gas Refill',
    category: 'AC Repair',
    phone: '+2348123456789',
    whatsapp: '2348123456789',
    rating: 5.0,
    priceTag: 'From ₦5,000 service',
    avatarBg: '#D97706',
    initials: 'MI',
    bio: 'Comprehensive air conditioning servicing, coil chemical wash, R410A / R22 gas top-up, compressor troubleshooting, and smart inverter AC diagnostics.',
    reviews: [
      {
        id: 'rev-6',
        residentName: 'Barr. Charles Eze',
        residentUnit: 'Villa 5 • Orchard Way',
        rating: 5,
        comment: 'Serviced 4 split units in my apartment. Cooling returned to ice-cold within 30 minutes. Cleaned up after himself.',
        date: 'Yesterday',
      },
      {
        id: 'rev-7',
        residentName: 'Amina Bello',
        residentUnit: 'House 22 • Silver Crest',
        rating: 5,
        comment: 'Very polite and trustworthy. Tested capacitor and fixed the AC without charging unnecessary spare part fees.',
        date: '5 days ago',
      },
    ],
  },
  {
    id: 'art-4',
    name: 'Grace Sparkle Pro',
    trade: 'Deep Home Cleaning & Fumigation',
    category: 'Cleaning',
    phone: '+2348098765432',
    whatsapp: '2348098765432',
    rating: 4.9,
    priceTag: 'From ₦8,000 flat',
    avatarBg: '#7C3AED',
    initials: 'GS',
    bio: 'Trained residential cleaning crew. Post-construction scrubbing, sofa steam extraction, kitchen degreasing, and eco-friendly odorless fumigation.',
    reviews: [
      {
        id: 'rev-8',
        residentName: 'Ngozi Adeyemi',
        residentUnit: 'House 3 • Maple View',
        rating: 5,
        comment: 'Grace and her team made our 4-bedroom terrace sparkle before our house-warming. Punctual and thorough.',
        date: '4 days ago',
      },
    ],
  },
  {
    id: 'art-5',
    name: 'Yakubu Danladi',
    trade: 'Cabinetry, Door Locks & Woodwork',
    category: 'Carpentry',
    phone: '+2348076543210',
    whatsapp: '2348076543210',
    rating: 4.7,
    priceTag: 'From ₦4,500 call-out',
    avatarBg: '#B45309',
    initials: 'YD',
    bio: 'Master carpenter specializing in modern MDF/HDF kitchen cabinets, wardrobe repairs, smart digital door lock installations, and sagging door adjustments.',
    reviews: [
      {
        id: 'rev-9',
        residentName: 'Kalu Nwosu',
        residentUnit: 'Flat 5A • Block D',
        rating: 5,
        comment: 'Fixed our master bedroom sliding wardrobe door that was off track for months. Sturdy craftsmanship.',
        date: '1 week ago',
      },
    ],
  },
])

const displayedArtisans = computed(() => {
  if (activeServiceCategory.value === 'All') return artisansList.value
  return artisansList.value.filter(a => a.category === activeServiceCategory.value)
})

// Artisan Details & Reviews Modal State
const selectedArtisan = ref<ArtisanItem | null>(null)
const showReviewForm = ref(false)
const newReviewRating = ref(5)
const newReviewComment = ref('')
const submittingReview = ref(false)

function openArtisanModal(artisan: ArtisanItem) {
  selectedArtisan.value = artisan
  showReviewForm.value = false
  newReviewRating.value = 5
  newReviewComment.value = ''
}

function closeArtisanModal() {
  selectedArtisan.value = null
  showReviewForm.value = false
}

function ratingLabel(rating: number) {
  if (rating === 5) return '5.0 - Excellent / Outstanding'
  if (rating === 4) return '4.0 - Very Good'
  if (rating === 3) return '3.0 - Average'
  if (rating === 2) return '2.0 - Poor'
  return '1.0 - Very Unsatisfactory'
}

function submitArtisanReview() {
  if (!selectedArtisan.value || !newReviewComment.value.trim()) return
  submittingReview.value = true

  setTimeout(() => {
    const residentName = auth.resident?.name || 'Resident Neighbor'
    const residentUnit = auth.resident?.flatAddress || 'Estate Resident'

    const newRev: ArtisanReview = {
      id: 'rev-' + Date.now(),
      residentName,
      residentUnit,
      rating: newReviewRating.value,
      comment: newReviewComment.value.trim(),
      date: 'Just now',
    }

    selectedArtisan.value!.reviews.unshift(newRev)

    // Recalculate average rating
    const total = selectedArtisan.value!.reviews.reduce((acc, r) => acc + r.rating, 0)
    selectedArtisan.value!.rating = Number((total / selectedArtisan.value!.reviews.length).toFixed(1))
    try {
      localStorage.setItem('cp_estate_artisans', JSON.stringify(artisansList.value))
    } catch {}

    submittingReview.value = false
    showReviewForm.value = false
    newReviewComment.value = ''
    showToast('Your review has been posted successfully!', 'success')
  }, 400)
}

// Buy & Sell (Resident Community Items)
const buyCategories = ['All', 'Appliances', 'Furniture', 'Electronics']
const activeBuyCategory = ref('All')

const buyItemsList = ref([
  {
    id: 'buy-1',
    title: 'Scanfrost 4-Burner Gas Cooker + Oven',
    category: 'Appliances',
    price: 68000,
    condition: 'Like New',
    sellerUnit: 'Flat 3B • Block A',
    timeAgo: '2h ago',
    whatsapp: '2348132489619',
    iconEmoji: '🍳',
  },
  {
    id: 'buy-2',
    title: 'Nordic Fabric Armchair (Grey)',
    category: 'Furniture',
    price: 42000,
    condition: 'Gently Used',
    sellerUnit: 'House 12 • Palm Way',
    timeAgo: 'Yesterday',
    whatsapp: '2348132489619',
    iconEmoji: '🛋️',
  },
  {
    id: 'buy-3',
    title: 'Rechargeable Standing Fan (18")',
    category: 'Appliances',
    price: 28000,
    condition: 'Brand New',
    sellerUnit: 'Unit 7 • Emerald',
    timeAgo: '3d ago',
    whatsapp: '2348132489619',
    iconEmoji: '🌀',
  },
  {
    id: 'buy-4',
    title: 'Samsung 43" Smart 4K UHD TV',
    category: 'Electronics',
    price: 175000,
    condition: 'Like New',
    sellerUnit: 'Flat 1A • Block C',
    timeAgo: '4d ago',
    whatsapp: '2348132489619',
    iconEmoji: '📺',
  },
])

const displayedBuyItems = computed(() => {
  if (activeBuyCategory.value === 'All') return buyItemsList.value
  return buyItemsList.value.filter(item => item.category === activeBuyCategory.value)
})

const firstName = computed(() => {
  const name = auth.resident?.name || JSON.parse(localStorage.getItem('w_user') ?? 'null')?.name || ''
  return name.split(' ')[0]
})
const greeting  = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Good morning'
  if (h < 17) return 'Good afternoon'
  return 'Good evening'
})

const activePasses  = computed(() => summary.value.recentPasses.filter(p => p.status !== 'Exited' && p.status !== 'Revoked').slice(0, 4))
// const recentNotifs  = computed(() => summary.value.notifications.slice(0, 3))

async function loadSummary() {
  summaryLoading.value = true
  summaryError.value   = false
  try {
    const res = await client.get('/v1/home/summary')
    summary.value = res.data
    notifStore.notifications = res.data.notifications
    if (res.data.resident) auth.updateResident(res.data.resident)
  } catch {
    summaryError.value = true
  } finally {
    summaryLoading.value = false
  }

  await loadNews()
}

async function loadNews() {
  newsLoading.value = true
  try {
    const res = await client.get('/v1/news')
    newsItems.value = (res.data.news || []).map((item: NewsFeedItem & { image?: string | null; createdAt?: string | null }) => ({
      id: item.id,
      title: item.title,
      content: item.content,
      image: item.image ?? null,
      createdAt: item.createdAt ?? null,
    }))
  } catch {
    newsItems.value = []
  } finally {
    newsLoading.value = false
  }
}

async function onRefresh(event: CustomEvent) {
  await loadSummary()
  ;(event.target as HTMLIonRefresherElement).complete()
}

// function goToNotif(n: Notification) {
//   notifStore.markOneRead(n.id)
//   if (n.passId) router.push(`/pass/${n.passId}`)
// }

// const NOTIF_ICONS: Record<string, string> = { arrival: '👤', denied: '🚫', expired: '⏱️', item_flagged: '📦' }
// function notifIcon(type: string) { return NOTIF_ICONS[type] || '🔔' }

// function fmtRelative(iso: string) {
//   const diff = (Date.now() - new Date(iso).getTime()) / 1000
//   if (diff < 60)   return 'Just now'
//   if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
//   if (diff < 86400)return `${Math.floor(diff / 3600)}h ago`
//   return `${Math.floor(diff / 86400)}d ago`
// }

function fmtNewsDate(iso: string | null) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('en-NG', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

function initials(name: string) {
  return name
    .split(' ')
    .filter(Boolean)
    .map((word) => word[0])
    .slice(0, 2)
    .join('')
    .toUpperCase()
}

function avatarColor(name: string) {
  const colors = ['#F08A00', '#0E9F24', '#6D17E6', '#E5007E', '#1A3F8F']
  const idx = (name.charCodeAt(0) || 0) % colors.length
  return colors[idx]
}

function statusClass(status: string) {
  if (status === 'On-site') return 'status-onsite'
  if (status === 'Pending') return 'status-pending'
  if (status === 'Expired') return 'status-expired'
  return 'status-exited'
}

onMounted(() => {
  loadSummary()
  try {
    const saved = localStorage.getItem('cp_estate_artisans')
    if (saved) {
      artisansList.value = JSON.parse(saved)
    }
  } catch {}
})
</script>

<style scoped>
.home-content { --background: var(--w-bg); }
.home-title { font-size: 16px !important; font-weight: 600; }
.content-pad { padding: 16px 16px 100px; display: flex; flex-direction: column; gap: 20px; }
.section { display: flex; flex-direction: column; gap: 10px; }
.section-header { display: flex; justify-content: space-between; align-items: center; }
.section-title  { font-size: 17px; font-weight: 700; color: var(--w-text); }
.see-all { background: none; border: none; color: var(--w-primary); font-size: 14px; font-weight: 600; cursor: pointer; padding: 4px 0; }
.pass-list { display: flex; flex-direction: column; gap: 10px; }
.passes-shell { display: flex; flex-direction: column; gap: 12px; }

/* ── STICKY NAVIGATION TABS BAR ── */
.passes-tabs-sticky-bar {
  position: sticky;
  top: -16px; /* docks seamlessly under ion-toolbar at the top of ion-content */
  z-index: 35;
  background: var(--w-bg, #F7F9F7);
  padding-top: 14px;
  margin-top: -14px;
  margin-left: -16px;
  margin-right: -16px;
  padding-left: 16px;
  padding-right: 16px;
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1.5px solid var(--w-border, #E5EAE7);
  box-shadow: 0 4px 12px -3px rgba(0, 0, 0, 0.03);
}

.passes-tabs {
  display: flex;
  gap: 22px;
  border-bottom: none;
  margin-bottom: 0;
}
.passes-tab {
  background: none;
  border: none;
  padding: 0 0 10px;
  color: #8C9991;
  font-size: 16.5px; /* Increased in size by 20% */
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  display: inline-flex;
  align-items: center;
}
.passes-tab.active {
  color: var(--w-primary, #0A5C38);
  font-weight: 700;
}
.passes-tab.active::after {
  content: '';
  position: absolute;
  bottom: 0px;
  left: 0;
  right: 0;
  height: 2.5px;
  background: var(--w-primary, #0A5C38);
  border-radius: 999px;
}

.tab-unread-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #E53935;
  color: #FFFFFF;
  font-size: 11px;
  font-weight: 800;
  height: 18px;
  min-width: 18px;
  border-radius: 999px;
  padding: 0 5px;
  margin-left: 6px;
  line-height: 1;
}

/* ── MARKETPLACE STYLING ── */
.marketplace-wrap {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Sub-tabs: Services & Buy */
.marketplace-subtabs {
  display: flex;
  background: #E8EFEA;
  border-radius: 12px;
  padding: 3px;
  gap: 4px;
}
.subtab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  border: none;
  background: transparent;
  padding: 8px 12px;
  border-radius: 9px;
  font-size: 13px;
  font-weight: 600;
  color: #556B60;
  cursor: pointer;
  transition: all 0.2s ease;
}
.subtab-btn.active {
  background: #FFFFFF;
  color: var(--w-primary, #0A5C38);
  font-weight: 700;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
}

.market-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Category Filter Row */
.category-scroll-row {
  display: flex;
  align-items: center;
  gap: 6px;
  overflow-x: auto;
  padding-bottom: 2px;
  scrollbar-width: none;
}
.category-scroll-row::-webkit-scrollbar {
  display: none;
}
.cat-chip {
  flex-shrink: 0;
  border: 1px solid var(--w-border, #E2E8E4);
  border-radius: 999px;
  background: #FFFFFF;
  color: #64746C;
  font-size: 12px;
  font-weight: 600;
  padding: 5px 12px;
  cursor: pointer;
  transition: all 0.15s ease;
}
.cat-chip.active {
  border-color: var(--w-primary, #0A5C38);
  background: var(--w-primary, #0A5C38);
  color: #FFFFFF;
}

/* Interactive Preview Toggle (Filled vs Empty) */
.preview-toggle-chip {
  flex-shrink: 0;
  margin-left: auto;
  border: 1px dashed #B8C8BF;
  border-radius: 999px;
  background: #F3F8F5;
  color: #0A5C38;
  font-size: 11px;
  font-weight: 700;
  padding: 5px 10px;
  cursor: pointer;
}

.market-empty-box {
  background: #FFFFFF;
  border-radius: 18px;
  border: 1px solid var(--w-border, #E2E8E4);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

/* ── ARTISAN CARDS (Services) ── */
.artisan-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.artisan-card {
  background: #FFFFFF;
  border: 1px solid rgba(10, 92, 56, 0.12);
  border-radius: 16px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.artisan-card:active {
  transform: scale(0.99);
  background: #FAFCFB;
}
.artisan-top {
  display: flex;
  align-items: center;
  gap: 12px;
}
.artisan-avatar {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  color: #FFFFFF;
  font-size: 16px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.artisan-meta {
  flex: 1;
  min-width: 0;
}
.artisan-name-row {
  display: flex;
  align-items: center;
  gap: 6px;
}
.artisan-name {
  font-size: 14.5px;
  font-weight: 700;
  color: #112019;
}
.artisan-trade {
  font-size: 12px;
  color: #64746C;
  margin-top: 2px;
}
.artisan-rating-row {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11.5px;
  color: #4A5850;
  margin-top: 4px;
}
.rating-star {
  color: #F59E0B;
  font-size: 13px;
}
.rating-val {
  font-weight: 700;
  color: #112019;
}
.rating-count {
  color: #7A8B81;
}
.artisan-chevron-box {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #9AA79F;
  flex-shrink: 0;
  padding-right: 2px;
}

/* ── ARTISAN CARD ENHANCEMENTS ── */
.vetted-inline-badge {
  display: inline-flex;
  align-items: center;
  font-size: 10px;
  font-weight: 700;
  color: #0A5C38;
  background: #EAF7EE;
  padding: 1px 6px;
  border-radius: 999px;
  margin-left: 4px;
}
.artisan-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 8px;
  border-top: 1px dashed rgba(10, 92, 56, 0.12);
  margin-top: 2px;
}
.card-action-text {
  font-size: 12px;
  font-weight: 600;
  color: #0A5C38;
}
.card-action-arrow {
  font-size: 13px;
  color: #0A5C38;
  font-weight: 700;
}

/* ── ARTISAN & NEWS SHEET MODALS ── */
ion-modal.artisan-sheet-modal,
ion-modal.news-modal-sheet {
  --height: 90%;
  --border-radius: 24px 24px 0 0;
  --backdrop-opacity: 0.65;
  align-items: flex-end;
}
.sheet-modal-shell {
  display: flex;
  flex-direction: column;
  height: 100%;
  background: #F8FAF9;
  color: #112019;
  overflow: hidden;
}
.sheet-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 20px 14px;
  background: #FFFFFF;
  border-bottom: 1px solid #EAEFEA;
  flex-shrink: 0;
}
.sheet-header-tag {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #0A5C38;
}
.sheet-header-title {
  font-size: 18px;
  font-weight: 700;
  margin: 2px 0 0;
  color: #112019;
}
.modal-close-btn {
  background: #F1F4F2;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-size: 20px;
  color: #4A5850;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
}
.sheet-modal-body {
  flex: 1;
  overflow-y: auto;
  -webkit-overflow-scrolling: touch;
  padding: 16px 18px 40px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* ── ARTISAN PROFILE DETAILS ── */
.artisan-sheet-header {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #FFFFFF;
  padding: 16px;
  border-radius: 16px;
  border: 1px solid #EAEFEA;
}
.artisan-sheet-avatar {
  width: 54px;
  height: 54px;
  border-radius: 16px;
  color: #FFFFFF;
  font-size: 20px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}
.artisan-sheet-info {
  flex: 1;
  min-width: 0;
}
.artisan-sheet-name-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.artisan-sheet-name {
  font-size: 17px;
  font-weight: 800;
  color: #112019;
  margin: 0;
}
.vetted-badge-detail {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #EAF7EE;
  color: #0A5C38;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
  border: 1px solid rgba(10, 92, 56, 0.2);
}
.artisan-sheet-trade {
  font-size: 13px;
  color: #55655D;
  margin: 3px 0 0;
}
.artisan-sheet-rating-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #64746C;
  margin-top: 5px;
  flex-wrap: wrap;
}
.rating-stars-inline {
  display: flex;
  align-items: center;
  gap: 2px;
}
.sheet-star {
  color: #F59E0B;
  font-size: 14px;
}
.sheet-rating-score {
  font-weight: 700;
  color: #112019;
}
.rating-dot-separator {
  color: #CBD5E1;
}
.sheet-reviews-count {
  color: #55655D;
}
.sheet-category-tag {
  background: #F1F5F9;
  color: #475569;
  font-size: 10.5px;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 6px;
}

/* ── ARTISAN BIO & PRICING CARD ── */
.artisan-info-card {
  background: #FFFFFF;
  padding: 16px;
  border-radius: 16px;
  border: 1px solid #EAEFEA;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.info-card-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 13px;
  padding-bottom: 6px;
  border-bottom: 1px solid #F1F4F2;
}
.info-label {
  color: #64746C;
  font-weight: 500;
}
.info-value {
  font-weight: 700;
  color: #112019;
}
.price-highlight {
  color: #0A5C38;
}
.text-green {
  color: #059669;
}
.artisan-bio-text {
  font-size: 13px;
  color: #4A5850;
  line-height: 1.55;
  margin: 0;
}

/* ── DIRECT ACTIONS (WHATSAPP & CALL) ── */
.artisan-action-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.artisan-btn-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 13.5px;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.artisan-btn-link:active {
  transform: scale(0.98);
}
.btn-whatsapp-action {
  background: #25D366;
  color: #FFFFFF;
}
.btn-call-action {
  background: #112019;
  color: #FFFFFF;
}

/* ── REVIEWS & RATINGS SECTION ── */
.sheet-reviews-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-top: 4px;
}
.reviews-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.reviews-section-title {
  font-size: 16px;
  font-weight: 700;
  color: #112019;
  margin: 0;
}
.reviews-section-sub {
  font-size: 11.5px;
  color: #64746C;
  margin: 2px 0 0;
}
.write-review-btn {
  background: #EAF7EE;
  border: 1px solid rgba(10, 92, 56, 0.25);
  color: #0A5C38;
  font-size: 12.5px;
  font-weight: 700;
  padding: 7px 12px;
  border-radius: 10px;
  cursor: pointer;
  white-space: nowrap;
}
.write-review-btn:active {
  background: #0A5C38;
  color: #FFFFFF;
}

/* ── REVIEW COMPOSE CARD ── */
.review-compose-card {
  background: #FFFFFF;
  border: 1px solid #D8E4DC;
  border-radius: 16px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-shadow: 0 3px 12px rgba(10, 92, 56, 0.06);
}
.compose-title {
  font-size: 14px;
  font-weight: 700;
  color: #112019;
  margin: 0;
}
.rating-stars-picker {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}
.picker-label {
  font-size: 12px;
  font-weight: 600;
  color: #4A5850;
}
.stars-row {
  display: flex;
  gap: 4px;
}
.star-btn {
  background: none;
  border: none;
  font-size: 24px;
  color: #CBD5E1;
  cursor: pointer;
  padding: 0;
  line-height: 1;
  transition: transform 0.15s ease, color 0.15s ease;
}
.star-btn.selected {
  color: #F59E0B;
}
.star-btn:active {
  transform: scale(1.2);
}
.rating-label-text {
  font-size: 12px;
  font-weight: 600;
  color: #0A5C38;
}
.review-textarea {
  width: 100%;
  border: 1px solid #D8E4DC;
  border-radius: 12px;
  padding: 10px 12px;
  font-size: 13px;
  color: #112019;
  background: #FAFCFB;
  resize: vertical;
  font-family: inherit;
  box-sizing: border-box;
}
.review-textarea:focus {
  outline: none;
  border-color: #0A5C38;
  background: #FFFFFF;
}
.submit-review-btn {
  background: #0A5C38;
  color: #FFFFFF;
  border: none;
  border-radius: 12px;
  padding: 12px;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.15s ease;
}
.submit-review-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* ── REVIEWS FEED ── */
.reviews-feed {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.review-card-item {
  background: #FFFFFF;
  border: 1px solid #EAEFEA;
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.review-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.reviewer-meta {
  display: flex;
  align-items: baseline;
  gap: 6px;
}
.reviewer-name {
  font-size: 13px;
  font-weight: 700;
  color: #112019;
}
.reviewer-unit {
  font-size: 11.5px;
  color: #7A8B81;
}
.review-date {
  font-size: 11px;
  color: #94A3B8;
}
.review-stars-row {
  display: flex;
  gap: 2px;
}
.r-star {
  color: #E2E8F0;
  font-size: 13px;
}
.r-star.filled {
  color: #F59E0B;
}
.review-comment-text {
  font-size: 12.5px;
  color: #334155;
  line-height: 1.5;
  margin: 0;
}
.no-reviews-box {
  background: #FFFFFF;
  border: 1px dashed #CBD5E1;
  border-radius: 14px;
  padding: 20px;
  text-align: center;
  font-size: 13px;
  color: #64746C;
}

/* ── NEWS SHEET MODAL STYLES ── */
.news-sheet-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}
.news-sheet-badge {
  font-size: 11px;
  font-weight: 700;
  color: #0A5C38;
  background: #EAF7EE;
  padding: 3px 8px;
  border-radius: 999px;
}
.news-sheet-date {
  font-size: 12px;
  color: #7A8B81;
}
.news-sheet-title {
  font-size: 18px;
  font-weight: 800;
  color: #112019;
  line-height: 1.35;
  margin: 0 0 12px;
}
.news-sheet-img-wrap {
  width: 100%;
  max-height: 200px;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 14px;
}
.news-sheet-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.news-sheet-body {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.news-sheet-p {
  font-size: 13.5px;
  color: #334155;
  line-height: 1.6;
  margin: 0;
}
.news-sheet-actions {
  display: flex;
  gap: 10px;
  margin-top: 18px;
}
.news-dismiss-action-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #FEF2F2;
  border: 1px solid #FEE2E2;
  color: #DC2626;
  padding: 11px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}
.news-close-action-btn {
  background: #E2E8F0;
  border: none;
  color: #334155;
  padding: 11px 20px;
  border-radius: 12px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
}

/* ── NEWS & ANNOUNCEMENTS FEED ── */
.news-section-wrap {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.news-filter-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding-bottom: 2px;
}
.news-filter-chips {
  display: flex;
  gap: 6px;
  overflow-x: auto;
  scrollbar-width: none;
}
.news-filter-chips::-webkit-scrollbar {
  display: none;
}
.n-chip {
  border: 1px solid var(--w-border, #E2E8E4);
  border-radius: 999px;
  background: #FFFFFF;
  color: #64746C;
  font-size: 12px;
  font-weight: 600;
  padding: 5px 12px;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}
.n-chip.active {
  border-color: var(--w-primary, #0A5C38);
  background: var(--w-primary, #0A5C38);
  color: #FFFFFF;
}
.mark-all-read-btn {
  background: none;
  border: none;
  color: var(--w-primary, #0A5C38);
  font-size: 11.5px;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
  padding: 4px;
}
.news-empty-container {
  background: #FFFFFF;
  border-radius: 18px;
  border: 1px solid var(--w-border, #E2E8E4);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}
.news-feed-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.news-card-item {
  background: #FFFFFF;
  border: 1px solid #E2E8E4;
  border-radius: 16px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}
.news-card-item.is-unread {
  border-color: rgba(10, 92, 56, 0.35);
  background: #FAFDFB;
  box-shadow: 0 4px 14px rgba(10, 92, 56, 0.06);
}
.news-card-item:active {
  transform: scale(0.99);
}
.news-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.news-badge-group {
  display: flex;
  align-items: center;
  gap: 8px;
}
.news-status-badge {
  font-size: 10.5px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
}
.news-status-badge--unread {
  background: #E6F4EA;
  color: #0A5C38;
}
.news-status-badge--read {
  background: #F1F3F2;
  color: #7B8780;
}
.news-date-text {
  font-size: 11px;
  color: #8C9991;
}
.news-delete-btn {
  background: transparent;
  border: none;
  color: #9AA79F;
  padding: 4px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.15s, background 0.15s;
}
.news-delete-btn:hover, .news-delete-btn:active {
  color: #DC2626;
  background: #FEE2E2;
}
.news-card-title {
  font-size: 14.5px;
  font-weight: 700;
  color: #112019;
  line-height: 1.35;
}
.is-unread .news-card-title {
  color: #053B23;
  font-weight: 800;
}
.news-card-snippet {
  font-size: 12.5px;
  color: #556B60;
  line-height: 1.45;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.news-card-footer {
  display: flex;
  align-items: center;
  padding-top: 2px;
}
.read-more-link {
  font-size: 11.5px;
  font-weight: 700;
  color: var(--w-primary, #0A5C38);
}

/* ── ARTISAN DETAILS & REVIEWS SHEET MODAL ── */
.artisan-sheet-modal {
  --border-radius: 24px 24px 0 0;
}
.artisan-sheet-content {
  background: #FFFFFF;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.sheet-top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px 8px;
  position: relative;
}
.sheet-drag-pill {
  width: 40px;
  height: 4.5px;
  background: #D8DFDB;
  border-radius: 999px;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  top: 10px;
}
.sheet-close-btn {
  margin-left: auto;
  background: #F1F4F2;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #556B60;
  cursor: pointer;
}
.artisan-sheet-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 4px 18px 36px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.artisan-sheet-header {
  display: flex;
  align-items: center;
  gap: 14px;
  padding-top: 4px;
}
.artisan-sheet-avatar {
  width: 60px;
  height: 60px;
  border-radius: 18px;
  color: #FFFFFF;
  font-size: 22px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
.artisan-sheet-info {
  flex: 1;
  min-width: 0;
}
.artisan-sheet-name-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}
.artisan-sheet-name {
  font-size: 19px;
  font-weight: 800;
  color: #112019;
  margin: 0;
}
.vetted-badge-detail {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #E6F4EA;
  color: #0A5C38;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 999px;
}
.artisan-sheet-trade {
  font-size: 13.5px;
  color: #556B60;
  margin: 4px 0 0;
  font-weight: 500;
}
.artisan-sheet-rating-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #64746C;
  margin-top: 6px;
}
.rating-stars-inline {
  display: flex;
  align-items: center;
  gap: 3px;
}
.sheet-star {
  color: #F59E0B;
  font-size: 15px;
}
.sheet-rating-score {
  font-weight: 800;
  color: #112019;
}
.rating-dot-separator {
  color: #CBD5E1;
}
.sheet-category-tag {
  background: #F1F4F2;
  color: #4A5850;
  padding: 2px 7px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 600;
}
.artisan-info-card {
  background: #F8FAF9;
  border: 1px solid #E5EBE7;
  border-radius: 14px;
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.info-card-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12.5px;
}
.info-label {
  color: #64746C;
}
.info-value {
  font-weight: 700;
  color: #112019;
}
.price-highlight {
  color: #0A5C38;
  font-size: 13px;
}
.text-green {
  color: #059669;
}
.artisan-bio-text {
  font-size: 12.5px;
  color: #44544C;
  line-height: 1.45;
  margin: 6px 0 0;
  padding-top: 6px;
  border-top: 1px dashed #E0E7E2;
}

/* Call & WhatsApp direct action buttons inside modal */
.artisan-action-row {
  display: grid;
  grid-template-columns: 1.2fr 1fr;
  gap: 10px;
}
.artisan-btn-link {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 44px;
  border-radius: 12px;
  font-size: 13.5px;
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, opacity 0.15s ease;
}
.artisan-btn-link:active {
  transform: scale(0.98);
  opacity: 0.9;
}
.btn-whatsapp-action {
  background: #25D366;
  color: #FFFFFF;
  box-shadow: 0 3px 10px rgba(37, 211, 102, 0.28);
}
.btn-call-action {
  background: #0A5C38;
  color: #FFFFFF;
  box-shadow: 0 3px 10px rgba(10, 92, 56, 0.22);
}

/* Reviews Feed & Rating Composer inside Sheet */
.sheet-reviews-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding-top: 4px;
}
.reviews-section-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}
.reviews-section-title {
  font-size: 15px;
  font-weight: 800;
  color: #112019;
  margin: 0;
}
.reviews-section-sub {
  font-size: 11.5px;
  color: #64746C;
  margin: 2px 0 0;
}
.write-review-btn {
  background: #E8F5EE;
  color: #0A5C38;
  border: 1px solid rgba(10, 92, 56, 0.2);
  border-radius: 8px;
  padding: 6px 10px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
}
.review-compose-card {
  background: #F2F8F4;
  border: 1px solid rgba(10, 92, 56, 0.2);
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.compose-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #0A5C38;
  margin: 0;
}
.rating-stars-picker {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.picker-label {
  font-size: 12px;
  font-weight: 600;
  color: #4A5850;
}
.stars-row {
  display: flex;
  gap: 4px;
}
.star-btn {
  background: none;
  border: none;
  font-size: 22px;
  color: #CBD5E1;
  cursor: pointer;
  padding: 0;
  line-height: 1;
  transition: color 0.15s ease, transform 0.1s ease;
}
.star-btn.selected {
  color: #F59E0B;
}
.star-btn:active {
  transform: scale(1.2);
}
.rating-label-text {
  font-size: 11.5px;
  font-weight: 700;
  color: #0A5C38;
}
.review-textarea {
  width: 100%;
  border: 1px solid #D0DDD5;
  border-radius: 10px;
  padding: 10px;
  font-size: 13px;
  font-family: inherit;
  color: #112019;
  background: #FFFFFF;
  resize: vertical;
  outline: none;
}
.review-textarea:focus {
  border-color: #0A5C38;
}
.submit-review-btn {
  height: 38px;
  border: none;
  border-radius: 10px;
  background: #0A5C38;
  color: #FFFFFF;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.15s ease;
}
.submit-review-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.reviews-feed {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.review-card-item {
  background: #F9FBFA;
  border: 1px solid #E5EBE7;
  border-radius: 12px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.reviewer-meta {
  display: flex;
  align-items: center;
  gap: 4px;
}
.reviewer-name {
  font-size: 13px;
  font-weight: 700;
  color: #112019;
}
.reviewer-unit {
  font-size: 11.5px;
  color: #64746C;
}
.review-date {
  font-size: 11px;
  color: #8C9991;
}
.review-stars-row {
  display: flex;
  gap: 2px;
}
.r-star {
  font-size: 13px;
  color: #E2E8F0;
}
.r-star.filled {
  color: #F59E0B;
}
.review-comment-text {
  font-size: 12.5px;
  color: #3B4B42;
  line-height: 1.4;
  margin: 0;
}
.no-reviews-box {
  background: #F9FBFA;
  border: 1px dashed #D0DDD5;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  font-size: 12.5px;
  color: #7A8B81;
}

/* ── NEWS MODAL SHEET ── */
.news-modal-sheet {
  --border-radius: 24px 24px 0 0;
}
.news-sheet-content {
  background: #FFFFFF;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.news-sheet-scroll {
  flex: 1;
  overflow-y: auto;
  padding: 4px 18px 36px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.news-sheet-meta {
  display: flex;
  align-items: center;
  gap: 8px;
}
.news-sheet-badge {
  background: #E8F5EE;
  color: #0A5C38;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
}
.news-sheet-date {
  font-size: 12px;
  color: #7B8780;
}
.news-sheet-title {
  font-size: 20px;
  font-weight: 800;
  color: #112019;
  line-height: 1.35;
  margin: 0;
}
.news-sheet-img-wrap {
  border-radius: 14px;
  overflow: hidden;
  max-height: 200px;
}
.news-sheet-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.news-sheet-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}
.news-sheet-p {
  font-size: 14px;
  color: #374740;
  line-height: 1.6;
  margin: 0;
}
.news-sheet-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-top: 10px;
  border-top: 1px solid #EAEFEA;
}
.news-dismiss-action-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  border: 1px solid #E2E8E4;
  border-radius: 10px;
  background: #FFF;
  color: #DC2626;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 14px;
  cursor: pointer;
}
.news-close-action-btn {
  background: var(--w-primary, #0A5C38);
  color: #FFFFFF;
  border: none;
  border-radius: 10px;
  font-size: 13.5px;
  font-weight: 700;
  padding: 9px 20px;
  cursor: pointer;
}

/* ── BUY ITEMS GRID ── */
.buy-items-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}
.buy-card {
  background: #FFFFFF;
  border: 1px solid rgba(10, 92, 56, 0.12);
  border-radius: 16px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}
.buy-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.buy-emoji-badge {
  font-size: 26px;
}
.condition-badge {
  font-size: 9.5px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.condition-badge.brand-new {
  background: #DCFCE7;
  color: #15803D;
}
.condition-badge.like-new {
  background: #E0F2FE;
  color: #0369A1;
}
.condition-badge.gently-used {
  background: #FEF3C7;
  color: #B45309;
}
.buy-card-body {
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.buy-item-title {
  font-size: 12.5px;
  font-weight: 700;
  color: #112019;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 32px;
}
.buy-item-price {
  font-size: 15px;
  font-weight: 800;
  color: #0A5C38;
}
.buy-seller-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 10.5px;
  color: #7A8B81;
  margin-top: 2px;
}
.contact-seller-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 4px;
  height: 32px;
  border-radius: 8px;
  background: #F1F8F4;
  color: #0A5C38;
  font-size: 11.5px;
  font-weight: 700;
  text-decoration: none;
  border: 1px solid rgba(10, 92, 56, 0.18);
  transition: all 0.15s ease;
  margin-top: 2px;
}
.contact-seller-btn:active {
  background: #E4F2EB;
}
.pass-mini-list { display: flex; flex-direction: column; gap: 6px; }
.pass-mini-row {
  width: 100%;
  border: none;
  border-radius: 16px;
  background: #ececef;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 14px;
  text-align: left;
  cursor: pointer;
}
.pass-mini-avatar {
  width: 42px;
  height: 42px;
  border-radius: 999px;
  color: #fff;
  font-size: 24px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.pass-mini-body { flex: 1; min-width: 0; }
.pass-mini-name {
  font-size: 18px;
  font-weight: 700;
  color: #22252b;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.pass-mini-purpose { margin-top: 3px; color: #787f8b; font-size: 13px; }
.pass-mini-status {
  flex-shrink: 0;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  line-height: 1.1;
}
.status-onsite { background: #d8f0d3; color: #2a8b35; }
.status-pending { background: #efe8d2; color: #c39200; }
.status-expired { background: #f3dedf; color: #f24141; }
.status-exited { background: #d8d8da; color: #2b2f38; }
.view-all-btn {
  margin-top: 2px;
  width: 100%;
  border: none;
  border-radius: 14px;
  background: #ececef;
  color: #5f6470;
  font-size: 18px;
  font-weight: 600;
  padding: 10px 12px;
  cursor: pointer;
}
.news-mini-empty {
  border-radius: 14px;
  background: #ececef;
  color: #6b7280;
  text-align: center;
  padding: 18px;
  font-size: 14px;
}
.news-mini-list { display: flex; flex-direction: column; gap: 8px; }
.news-mini-row {
  width: 100%;
  border: none;
  border-radius: 14px;
  background: #ececef;
  padding: 12px;
  text-align: left;
}
.news-mini-top {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 10px;
}
.news-mini-title {
  color: #232833;
  font-size: 14px;
  font-weight: 700;
  line-height: 1.3;
}
.news-mini-date {
  color: #7b8290;
  font-size: 11px;
  flex-shrink: 0;
}
.news-mini-content {
  margin-top: 6px;
  color: #636b79;
  font-size: 12px;
  line-height: 1.45;
}
.sk-hero { height: 140px; border-radius: var(--w-radius-xl); display: block; }
.security-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.stat-card { background: var(--w-surface); border-radius: var(--w-radius-md); padding: 16px 12px; text-align: center; }
.stat-value { font-size: 28px; font-weight: 800; color: var(--w-primary); }
.stat-label { font-size: 12px; color: var(--w-muted); font-weight: 600; margin-top: 4px; }
.scan-qr-btn {
  display: flex; align-items: center; gap: 14px;
  width: 100%; padding: 18px 20px;
  background: var(--w-primary); border: none; border-radius: var(--w-radius-lg);
  cursor: pointer; text-align: left; transition: opacity 0.15s;
}
.scan-qr-btn:active { opacity: 0.85; }
.scan-icon { font-size: 30px; flex-shrink: 0; }
.scan-btn-title { font-size: 16px; font-weight: 700; color: #fff; }
.scan-btn-sub   { font-size: 13px; color: rgba(255,255,255,0.7); margin-top: 2px; }
.phrase-entry-btn {
  display: flex; align-items: center; gap: 14px;
  width: 100%; padding: 16px 20px; margin-top: 10px;
  background: var(--w-surface); border: 2px solid var(--w-primary); border-radius: var(--w-radius-lg);
  cursor: pointer; text-align: left; transition: opacity 0.15s;
}
.phrase-entry-btn:active { opacity: 0.85; }
.phrase-entry-title { font-size: 16px; font-weight: 700; color: var(--w-primary); }
.phrase-entry-sub   { font-size: 13px; color: var(--w-muted); margin-top: 2px; }
.notif-list { background: var(--w-surface); border-radius: var(--w-radius-md); overflow: hidden; }
.notif-row {
  display: flex; align-items: center; gap: 12px; padding: 14px 16px;
  cursor: pointer; border-bottom: 1px solid var(--w-border);
  transition: background 0.15s;
}
.notif-row:last-child { border-bottom: none; }
.notif-row.unread { background: var(--w-primary-light); }
.notif-icon { font-size: 22px; flex-shrink: 0; }
.notif-body { flex: 1; min-width: 0; }
.notif-msg  { font-size: 13px; color: var(--w-text); line-height: 1.4; }
.notif-time { font-size: 11px; color: var(--w-muted); margin-top: 3px; }
.unread-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--w-primary); flex-shrink: 0; }
.notif-empty{ padding: 24px; text-align: center; font-size: 14px; color: var(--w-muted); }
</style>
