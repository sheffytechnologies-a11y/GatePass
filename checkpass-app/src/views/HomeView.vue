<template>
  <ion-page>
    <ion-content :fullscreen="true" class="home-content">
      <!-- Scrollable Top Greeting Bar (scrolls away as tabs approach the top) -->
      <div class="home-top-bar">
        <h1 class="home-title">{{ greeting }}, {{ firstName }} 👋</h1>
        <div class="home-notif-wrap">
          <NotifBell :count="notifStore.unreadCount" @click="router.push('/notifications')" />
        </div>
      </div>
      <ion-refresher slot="fixed" @ionRefresh="onRefresh">
        <ion-refresher-content />
      </ion-refresher>

      <div class="content-pad" v-if="!summaryLoading && !summaryError">

        <!-- Multi-Role Persona Switcher Tabs (Activated when user holds > 1 role; Homeowner first, Occupant second) -->
        <div v-if="hasMultipleRoles" class="role-selector-wrap">
          <div class="role-selector-tabs" role="tablist">
            <button
              v-for="r in orderedRoles"
              :key="r.role"
              type="button"
              class="role-tab-btn"
              :class="{ 'is-active': auth.activeRole === r.role }"
              @click="handleRoleSwitch(r.role)"
              role="tab"
              :aria-selected="auth.activeRole === r.role"
            >
              <!-- 1. Admin -->
              <svg v-if="r.role === 'admin'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-role-icon">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <path d="m9 12 2 2 4-4"/>
              </svg>
              <!-- 2. Homeowner -->
              <svg v-else-if="r.role === 'homeowner'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-role-icon">
                <path d="M3 21h18M3 7v14M21 7v14M9 3h6v18M13 7h-2M13 11h-2M13 15h-2"/>
              </svg>
              <!-- 3. Occupant (Person icon) -->
              <svg v-else-if="r.role === 'resident' || r.role === 'occupant'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-role-icon">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              <!-- 4. Security -->
              <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="tab-role-icon">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              </svg>
              <span class="role-tab-label">{{ r.label }}</span>
            </button>
          </div>
        </div>

        <!-- ── ADMIN VIEW ─────────────────────────────── -->
        <template v-if="auth.isAdminUser">
          <div class="security-hero-card admin-hero-card">
            <div class="sec-hero-top">
              <div class="sec-hero-left">
                <div class="sec-estate-eyebrow">{{ summary?.user?.estateName || auth.resident?.estateName || 'Sheffy Estate' }}</div>
                <div class="sec-station-title">Estate Administration</div>
              </div>
              <div class="sec-duty-pill admin-pill">
                <span class="sec-duty-pulse"></span>
                <span>Command Active</span>
              </div>
            </div>

            <!-- Matrix: Passes Today | On-Site Now | Total Arrears -->
            <div class="sec-matrix-strip">
              <div class="sec-matrix-box">
                <div class="sec-matrix-val">{{ summary?.stats?.grantedToday ?? 0 }}</div>
                <div class="sec-matrix-lbl">Passes Today</div>
              </div>
              <div class="sec-matrix-vdivider"></div>
              <div class="sec-matrix-box">
                <div class="sec-matrix-val">{{ summary?.stats?.onPremises ?? 0 }}</div>
                <div class="sec-matrix-lbl">On-Site Now</div>
              </div>
              <div class="sec-matrix-vdivider"></div>
              <div class="sec-matrix-box">
                <div class="sec-matrix-val text-white">{{ formatCompactCurrency((summary as any)?.stats?.totalArrears ?? 0) }}</div>
                <div class="sec-matrix-lbl">Levies In Arrears</div>
              </div>
            </div>
          </div>

          <!-- Direct Admin Console Link / Quick Access -->
          <div class="admin-quick-portal-card">
            <div class="admin-portal-header">
              <div class="admin-portal-icon">🏢</div>
              <div class="admin-portal-info">
                <h4>Estate Management Console</h4>
                <p>Access resident roster, billing, pass logs, and operations.</p>
              </div>
            </div>
            <a href="http://localhost:3100" target="_blank" class="admin-portal-btn">
              <span>Open Admin Dashboard</span>
              <span>→</span>
            </a>
          </div>

          <!-- Live Gate Activity Feed -->
          <div class="admin-activity-section">
            <div class="sec-section-header">
              <h3 class="sec-section-heading">Live Gate Operations</h3>
            </div>
            <div v-if="displayedSecurityPasses.length === 0" class="sec-empty-box">
              <div class="sec-empty-icon">🛡️</div>
              <h3 class="sec-empty-title">All Gates Clear</h3>
              <p class="sec-empty-desc">Real-time visitor gate admissions and dispatches will appear here.</p>
            </div>
            <div v-else class="pass-mini-list">
              <PassCard
                v-for="pass in displayedSecurityPasses"
                :key="pass.id"
                :pass="pass"
                @admitted="onPhraseAdmitted"
              />
            </div>
          </div>
        </template>

        <!-- ── SECURITY VIEW ─────────────────────────────── -->
        <template v-else-if="auth.isSecurityUser">
          <!-- Top Hero Card: Styled like EstateHero, displaying matrix of passes -->
          <div class="security-hero-card">
            <div class="sec-hero-top">
              <div class="sec-hero-left">
                <div class="sec-estate-eyebrow">{{ summary?.user?.estateName || auth.resident?.estateName || 'Sheffy Estate' }}</div>
                <div class="sec-station-title">{{ securityStation }}</div>
              </div>
              <div class="sec-duty-pill">
                <span class="sec-duty-pulse"></span>
                <span>On Duty</span>
              </div>
            </div>

            <!-- Matrix of Passes: Admitted Today | On-Site Now | Declined Access -->
            <div class="sec-matrix-strip">
              <button type="button" class="sec-matrix-box" @click="router.push('/passes?filter=all')">
                <div class="sec-matrix-val">{{ summary?.stats?.grantedToday ?? 0 }}</div>
                <div class="sec-matrix-lbl">Granted Today</div>
              </button>
              <div class="sec-matrix-vdivider"></div>
              <button type="button" class="sec-matrix-box" @click="router.push('/passes?filter=On-site')">
                <div class="sec-matrix-val">{{ summary?.stats?.onPremises ?? 0 }}</div>
                <div class="sec-matrix-lbl">Currently On-Site</div>
              </button>
              <div class="sec-matrix-vdivider"></div>
              <button type="button" class="sec-matrix-box" @click="router.push('/passes?filter=Declined')">
                <div class="sec-matrix-val">{{ summary?.stats?.declinedToday ?? 0 }}</div>
                <div class="sec-matrix-lbl">Declined Access</div>
              </button>
            </div>
          </div>

          <!-- Sticky Sub-Tabs Bar (Identical Underline Nav Style as Resident Home) -->
          <div class="passes-tabs-sticky-bar">
            <div class="passes-tabs">
              <button
                class="passes-tab"
                :class="{ active: activeSecurityTab === 'access' }"
                @click="activeSecurityTab = 'access'"
              >
                <span>Access</span>
              </button>
              <button
                class="passes-tab"
                :class="{ active: activeSecurityTab === 'announcements' }"
                @click="activeSecurityTab = 'announcements'"
              >
                <span>Announcements</span>
                <span v-if="securityAnnouncements.length > 0" class="tab-unread-pill">{{ securityAnnouncements.length }}</span>
              </button>
              <button
                class="passes-tab"
                :class="{ active: activeSecurityTab === 'emergencies' }"
                @click="activeSecurityTab = 'emergencies'"
              >
                <span>Emergencies</span>
                <span v-if="securityEmergencies.length > 0" class="tab-unread-pill tab-unread-pill--red">{{ securityEmergencies.length }}</span>
              </button>
            </div>
          </div>

          <!-- ── TAB 1: ACCESS CLEARANCES ── -->
          <template v-if="activeSecurityTab === 'access'">
            <!-- Visitor Without Phone: Quick Access Phrase Trigger -->
            <div class="sec-phrase-trigger-row">
              <button type="button" class="sec-phrase-trigger-btn" @click="phraseSheetOpen = true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                  <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                  <line x1="12" y1="19" x2="12" y2="23"/>
                  <line x1="8" y1="23" x2="16" y2="23"/>
                </svg>
                <span>Visitor Without Phone? Verify Daily Access Phrase</span>
              </button>
            </div>

            <!-- Recent Clearances Stream / Mini List (Top 5) -->
            <div v-if="displayedSecurityPasses.length === 0">
              <EmptyState
                icon="🛡️"
                heading="No clearances yet"
                body="Visitors you grant or decline access to will appear here."
              />
            </div>

            <div v-else class="pass-mini-list">
              <button
                v-for="pass in displayedSecurityPasses"
                :key="pass.id"
                class="pass-mini-row"
                @click="router.push(`/pass/${pass.id}`)"
              >
                <div class="pass-mini-avatar" :style="{ background: avatarColor(pass.visitorName) }">
                  {{ initials(pass.visitorName) }}
                </div>
                <div class="pass-mini-body">
                  <div class="pass-mini-name">{{ pass.visitorName }}</div>
                  <div class="pass-mini-purpose">{{ pass.hostUnit || pass.purpose || 'Visitor Access' }}</div>
                </div>
                <span class="pass-mini-status" :class="statusClass(pass.status)">{{ pass.status }}</span>
              </button>

              <!-- View All Clearances button navigating to /passes where all, on-site, exited, declined are -->
              <button
                class="view-all-btn"
                @click="router.push('/passes')"
              >
                View all {{ securityPasses.length }} clearances &amp; history →
              </button>
            </div>
          </template>

          <!-- ── TAB 2: ANNOUNCEMENTS ── -->
          <div v-else-if="activeSecurityTab === 'announcements'" class="sec-tab-content">
            <div v-if="securityAnnouncements.length === 0" class="sec-empty-box">
              <div class="sec-empty-icon">📢</div>
              <h3 class="sec-empty-title">No Estate Announcements</h3>
              <p class="sec-empty-desc">New security bulletins and broadcasts from estate management will appear here.</p>
            </div>

            <div v-else class="sec-news-stream">
              <div
                v-for="item in securityAnnouncements"
                :key="item.id"
                class="sec-news-card"
              >
                <div class="sec-news-top">
                  <span class="sec-news-cat">{{ item.category || 'Security Bulletin' }}</span>
                  <span class="sec-news-date">{{ formatPassTime(item.createdAt) }}</span>
                </div>
                <h4 class="sec-news-title">{{ item.title }}</h4>
                <p class="sec-news-text">{{ item.content }}</p>

                <!-- Footer: Comments count & Reactions -->
                <div class="news-card-footer sec-news-card-footer">
                  <div class="news-card-actions-left">
                    <button
                      type="button"
                      class="news-card-comment-btn"
                      @click.stop="toggleInlineComments(item.id)"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                      </svg>
                      <span>{{ getNewsComments(item.id).length }} {{ getNewsComments(item.id).length === 1 ? 'Comment' : 'Comments' }}</span>
                    </button>
                  </div>

                  <div class="news-card-reactions">
                    <button
                      type="button"
                      class="news-reaction-btn"
                      :class="{ active: getNewsReaction(item.id).userReaction === 'like' }"
                      title="Like announcement"
                      @click.stop="toggleReaction(item.id, 'like')"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
                      </svg>
                      <span class="reaction-count">{{ getNewsReaction(item.id).likes }}</span>
                    </button>

                    <button
                      type="button"
                      class="news-reaction-btn is-dislike"
                      :class="{ active: getNewsReaction(item.id).userReaction === 'dislike' }"
                      title="Dislike announcement"
                      @click.stop="toggleReaction(item.id, 'dislike')"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"></path>
                      </svg>
                      <span class="reaction-count">{{ getNewsReaction(item.id).dislikes }}</span>
                    </button>
                  </div>
                </div>

                <!-- Inline Comments Accordion for Security Announcements -->
                <transition name="slide-fade">
                  <div
                    v-if="openInlineCommentsIds.includes(item.id)"
                    class="news-inline-comments-box"
                    @click.stop
                  >
                    <!-- Comment Composer -->
                    <div class="inline-comment-row">
                      <input
                        v-model="inlineCommentInputs[item.id]"
                        type="text"
                        class="inline-comment-input"
                        placeholder="Write a comment..."
                        maxlength="200"
                        @keydown.enter.prevent="submitInlineComment(item.id)"
                      />
                      <button
                        class="inline-comment-post-btn"
                        :disabled="!inlineCommentInputs[item.id] || !inlineCommentInputs[item.id].trim()"
                        @click="submitInlineComment(item.id)"
                      >
                        Post
                      </button>
                    </div>

                    <!-- Existing Comments List -->
                    <div v-if="getNewsComments(item.id).length > 0" class="inline-comments-stream">
                      <div
                        v-for="c in getNewsComments(item.id)"
                        :key="c.id"
                        class="inline-comment-entry"
                      >
                        <div class="inline-comment-header">
                          <div class="inline-comment-author-wrap">
                            <span class="inline-comment-author">{{ c.authorName }}</span>
                            <span v-if="c.authorRole" class="inline-comment-role-tag">{{ c.authorRole }}</span>
                            <span v-else-if="c.authorUnit" class="inline-comment-unit-tag">• {{ c.authorUnit }}</span>
                          </div>
                          <span class="inline-comment-time">{{ c.createdAt }}</span>
                        </div>
                        <div class="inline-comment-msg">{{ c.content }}</div>
                      </div>
                    </div>
                    <div v-else class="inline-no-comments">No comments yet. Be the first to comment!</div>
                  </div>
                </transition>
              </div>
            </div>
          </div>

          <!-- ── TAB 3: EMERGENCIES ── -->
          <div v-else-if="activeSecurityTab === 'emergencies'" class="sec-tab-content">
            <div v-if="securityEmergencies.length === 0" class="sec-empty-box">
              <div class="sec-empty-icon">🛡️</div>
              <h3 class="sec-empty-title">No Active Emergencies</h3>
              <p class="sec-empty-desc">Perimeter is secure. Panic signals and emergency alerts from residents will appear here.</p>
            </div>

            <div v-else class="sec-emergency-stream">
              <div
                v-for="em in securityEmergencies"
                :key="em.id"
                class="sec-emergency-card"
              >
                <div class="sec-em-head">
                  <div class="sec-em-badge">
                    <span class="sec-em-dot"></span>
                    <span>{{ (em.type || 'EMERGENCY').toUpperCase() }}</span>
                  </div>
                  <span class="sec-em-time">{{ formatPassTime(em.createdAt) }}</span>
                </div>

                <div class="sec-em-body">
                  <div class="sec-em-resident">{{ em.residentName }}</div>
                  <div class="sec-em-unit">{{ em.flatAddress }}</div>
                </div>

                <div v-if="em.phone" class="sec-em-footer">
                  <a :href="'tel:' + em.phone" class="sec-em-call-btn">
                    📞 Call Resident
                  </a>
                </div>
              </div>
            </div>
          </div>
        </template>

        <!-- ── HOMEOWNER VIEW ────────────────────────────── -->
        <template v-else-if="auth.isHomeownerUser">
          <!-- Top Hero Card: Styled like EstateHero with Portfolio Matrix -->
          <div class="security-hero-card homeowner-hero-card">
            <div class="sec-hero-top">
              <div class="sec-hero-left">
                <div class="sec-estate-eyebrow">{{ (summary as any)?.user?.estateName || auth.resident?.estateName || 'Sheffy Estate' }}</div>
                <div class="sec-station-title">Property Portfolio</div>
              </div>
            </div>

            <!-- Matrix: Total Properties | Occupied Units | Levies Due -->
            <div class="sec-matrix-strip">
              <div class="sec-matrix-box">
                <div class="sec-matrix-val">{{ homeownerProperties.length }}</div>
                <div class="sec-matrix-lbl">Properties Owned</div>
              </div>
              <div class="sec-matrix-box">
                <div class="sec-matrix-val">{{ homeownerOccupiedCount }}</div>
                <div class="sec-matrix-lbl">Occupied Units</div>
              </div>
              <div class="sec-matrix-box" @click="router.push('/tabs/fees')">
                <div class="sec-matrix-val text-white">{{ formatCompactCurrency(homeownerLeviesInArrears) }}</div>
                <div class="sec-matrix-lbl">Levies In Arrears</div>
              </div>
            </div>
          </div>

          <!-- Sticky Navigation Tabs for Homeowner (Properties | Announcements | Emergencies) -->
          <div class="passes-tabs-sticky-bar">
            <div class="passes-tabs" role="tablist">
              <button
                class="passes-tab"
                :class="{ active: activeHomeownerTab === 'properties' }"
                @click="activeHomeownerTab = 'properties'"
              >
                <span>Properties</span>
              </button>
              <button
                class="passes-tab"
                :class="{ active: activeHomeownerTab === 'announcements' }"
                @click="activeHomeownerTab = 'announcements'"
              >
                <span>Announcements</span>
                <span v-if="displayedNews.length > 0" class="tab-unread-pill">{{ displayedNews.length }}</span>
              </button>
              <button
                class="passes-tab"
                :class="{ active: activeHomeownerTab === 'emergencies' }"
                @click="activeHomeownerTab = 'emergencies'"
              >
                <span>Emergencies</span>
              </button>
            </div>
          </div>

          <!-- ── TAB 1: PROPERTIES ── -->
          <div v-if="activeHomeownerTab === 'properties'" class="homeowner-tab-content">
            <div class="prop-portfolio-stream">
              <div
                v-for="prop in homeownerProperties"
                :key="prop.id"
                class="prop-portfolio-card"
                @click="openPropertyDetails(prop)"
              >
                <div class="prop-card-head">
                  <div class="prop-badge-wrap">
                    <span class="prop-type-badge">{{ prop.type }}</span>
                    <span class="prop-status-pill" :class="prop.isOccupied ? 'is-occupied' : 'is-vacant'">
                      {{ prop.isOccupied ? '● Occupied' : '○ Vacant' }}
                    </span>
                  </div>
                  <h3 class="prop-card-title">{{ prop.unitNumber }} - {{ prop.streetName }}</h3>
                </div>

                <div v-if="prop.isOccupied && prop.primaryTenant" class="prop-occupant-box">
                  <div class="prop-tenant-row">
                    <div class="prop-tenant-avatar">{{ prop.primaryTenant.name.charAt(0) }}</div>
                    <div class="prop-tenant-meta">
                      <div class="prop-tenant-name">{{ prop.primaryTenant.name }}</div>
                      <div class="prop-tenant-sub">
                        <span class="tenant-role-tag">Primary Tenant</span>
                        <span class="sub-dot">•</span>
                        <span v-if="getAdditionalOccupantsCount(prop) > 0" class="additional-occupants-badge">
                          +{{ getAdditionalOccupantsCount(prop) }} occupant{{ getAdditionalOccupantsCount(prop) === 1 ? '' : 's' }}
                        </span>
                        <span v-else class="additional-occupants-badge sole-badge">
                          Sole occupant
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="prop-vacant-box">
                  <p>Unit is currently vacant. No active tenant on record.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- ── TAB 2: ANNOUNCEMENTS ── -->
          <div v-else-if="activeHomeownerTab === 'announcements'" class="homeowner-tab-content">
            <div v-if="displayedNews.length === 0" class="sec-empty-box">
              <div class="sec-empty-icon">📢</div>
              <h3 class="sec-empty-title">No Estate Announcements</h3>
              <p class="sec-empty-desc">New notices and landlord broadcasts from estate management will appear here.</p>
            </div>
            <div v-else class="sec-news-stream">
              <div
                v-for="item in displayedNews"
                :key="item.id"
                class="sec-news-card"
              >
                <div class="sec-news-top">
                  <span class="sec-news-cat">{{ (item as any).category || item.authorRole || 'Estate Notice' }}</span>
                  <span class="sec-news-date">{{ fmtNewsDate(item.createdAt) }}</span>
                </div>
                <h4 class="sec-news-title">{{ item.title }}</h4>
                <p class="sec-news-text">{{ item.content }}</p>

                <!-- Footer: Comments count & Reactions -->
                <div class="news-card-footer sec-news-card-footer">
                  <div class="news-card-actions-left">
                    <button
                      type="button"
                      class="news-card-comment-btn"
                      @click.stop="toggleInlineComments(item.id)"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                      </svg>
                      <span>{{ getNewsComments(item.id).length }} {{ getNewsComments(item.id).length === 1 ? 'Comment' : 'Comments' }}</span>
                    </button>
                  </div>

                  <div class="news-card-reactions">
                    <button
                      type="button"
                      class="news-reaction-btn"
                      :class="{ active: getNewsReaction(item.id).userReaction === 'like' }"
                      title="Like announcement"
                      @click.stop="toggleReaction(item.id, 'like')"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
                      </svg>
                      <span class="reaction-count">{{ getNewsReaction(item.id).likes }}</span>
                    </button>

                    <button
                      type="button"
                      class="news-reaction-btn is-dislike"
                      :class="{ active: getNewsReaction(item.id).userReaction === 'dislike' }"
                      title="Dislike announcement"
                      @click.stop="toggleReaction(item.id, 'dislike')"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"></path>
                      </svg>
                      <span class="reaction-count">{{ getNewsReaction(item.id).dislikes }}</span>
                    </button>
                  </div>
                </div>

                <!-- Inline Comments Accordion -->
                <transition name="slide-fade">
                  <div
                    v-if="openInlineCommentsIds.includes(item.id)"
                    class="news-inline-comments-box"
                    @click.stop
                  >
                    <div class="inline-comment-row">
                      <input
                        v-model="inlineCommentInputs[item.id]"
                        type="text"
                        class="inline-comment-input"
                        placeholder="Write a comment..."
                        maxlength="200"
                        @keydown.enter.prevent="submitInlineComment(item.id)"
                      />
                      <button
                        class="inline-comment-post-btn"
                        :disabled="!inlineCommentInputs[item.id] || !inlineCommentInputs[item.id].trim()"
                        @click="submitInlineComment(item.id)"
                      >
                        Post
                      </button>
                    </div>

                    <div v-if="getNewsComments(item.id).length > 0" class="inline-comments-stream">
                      <div
                        v-for="c in getNewsComments(item.id)"
                        :key="c.id"
                        class="inline-comment-entry"
                      >
                        <div class="inline-comment-header">
                          <div class="inline-comment-author-wrap">
                            <span class="inline-comment-author">{{ c.authorName }}</span>
                            <span v-if="c.authorRole" class="inline-comment-role-tag">{{ c.authorRole }}</span>
                            <span v-else-if="c.authorUnit" class="inline-comment-unit-tag">• {{ c.authorUnit }}</span>
                          </div>
                          <span class="inline-comment-time">{{ c.createdAt }}</span>
                        </div>
                        <div class="inline-comment-msg">{{ c.content }}</div>
                      </div>
                    </div>
                    <div v-else class="inline-no-comments">No comments yet. Be the first to comment!</div>
                  </div>
                </transition>
              </div>
            </div>
          </div>

          <!-- ── TAB 3: EMERGENCIES ── -->
          <div v-else-if="activeHomeownerTab === 'emergencies'" class="homeowner-tab-content">
            <div class="homeowner-dispatch-card">
              <h3 class="dispatch-title">Direct Gate &amp; Security Dispatch</h3>
              <p class="dispatch-sub">Instantly reach duty security guards and estate emergency lines for rapid response to your property.</p>
              
              <div class="dispatch-actions-grid">
                <a href="tel:08143948473" class="dispatch-btn">
                  <div class="dispatch-icon">📞</div>
                  <div class="dispatch-info">
                    <span class="dispatch-name">Main Gatehouse Control</span>
                    <span class="dispatch-num">0814 394 8473 (Direct)</span>
                  </div>
                </a>
                <a href="tel:08021234567" class="dispatch-btn">
                  <div class="dispatch-icon">🛡️</div>
                  <div class="dispatch-info">
                    <span class="dispatch-name">Security Chief &amp; Dispatch</span>
                    <span class="dispatch-num">0802 123 4567</span>
                  </div>
                </a>
                <a href="tel:112" class="dispatch-btn dispatch-btn--red">
                  <div class="dispatch-icon">🚨</div>
                  <div class="dispatch-info">
                    <span class="dispatch-name">State Emergency Response</span>
                    <span class="dispatch-num">112 / 199 (Police, Fire, Ambulance)</span>
                  </div>
                </a>
              </div>
            </div>

            <!-- Estate Incident Stream -->
            <div class="homeowner-incidents-section">
              <h4 class="incident-sec-title">Recent Security &amp; Utility Bulletins</h4>
              <div class="incident-log-card">
                <div class="incident-log-badge">PERIMETER CHECK</div>
                <div class="incident-log-text">Perimeter cameras and electric fence tested — 100% operational across all sectors.</div>
                <div class="incident-log-time">Today, 04:30 AM • Main Gate Control</div>
              </div>
              <div class="incident-log-card">
                <div class="incident-log-badge badge-green">FACILITY NOTICE</div>
                <div class="incident-log-text">Estate generator switch completed smoothly for early morning scheduled maintenance.</div>
                <div class="incident-log-time">Yesterday, 11:15 PM • Facility Office</div>
              </div>
            </div>
          </div>
        </template>

        <!-- ── RESIDENT VIEW ─────────────────────────────── -->
        <template v-else>
          <EstateHero
            :flat-address="auth.resident?.flatAddress || summary?.resident?.flatAddress || ''"
            :estate-name="auth.resident?.estateName || summary?.resident?.estateName || ''"
            :gate-open="true"
            :stats="summary?.stats"
          />

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
                    <span>Announcements</span>
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
                <div v-if="displayedHomePasses.length === 0">
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
                    v-for="pass in displayedHomePasses"
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

                  <button
                    v-if="hasMoreThanFivePasses"
                    class="view-all-btn"
                    @click="router.push('/passes')"
                  >
                    View all passes
                  </button>
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
                          <span class="news-role-badge">
                            <span v-if="!readNewsIds.includes(item.id)" class="news-unread-dot"></span>
                            {{ formatAdminRole(item.authorRole || item.adminRole) }}
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
                      <div
                        class="news-card-snippet"
                        :class="{ 'is-expanded': expandedNewsIds.includes(item.id) }"
                      >
                        {{ item.content }}
                      </div>

                      <div class="news-card-footer">
                        <div class="news-card-actions-left">
                          <button
                            type="button"
                            class="news-card-comment-btn"
                            @click.stop="toggleInlineComments(item.id)"
                          >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                            </svg>
                            <span>{{ getNewsComments(item.id).length }} {{ getNewsComments(item.id).length === 1 ? 'Comment' : 'Comments' }}</span>
                          </button>

                          <button
                            v-if="isLongNewsContent(item.content)"
                            type="button"
                            class="news-see-more-link"
                            @click.stop="toggleExpandNews(item.id)"
                          >
                            {{ expandedNewsIds.includes(item.id) ? 'See less' : 'See more' }}
                          </button>
                        </div>

                        <div class="news-card-reactions">
                          <button
                            type="button"
                            class="news-reaction-btn"
                            :class="{ active: getNewsReaction(item.id).userReaction === 'like' }"
                            title="Like announcement"
                            @click.stop="toggleReaction(item.id, 'like')"
                          >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
                            </svg>
                            <span class="reaction-count">{{ getNewsReaction(item.id).likes }}</span>
                          </button>

                          <button
                            type="button"
                            class="news-reaction-btn is-dislike"
                            :class="{ active: getNewsReaction(item.id).userReaction === 'dislike' }"
                            title="Dislike announcement"
                            @click.stop="toggleReaction(item.id, 'dislike')"
                          >
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"></path>
                            </svg>
                            <span class="reaction-count">{{ getNewsReaction(item.id).dislikes }}</span>
                          </button>
                        </div>
                      </div>

                      <!-- Inline Comments Accordion -->
                      <transition name="slide-fade">
                        <div
                          v-if="openInlineCommentsIds.includes(item.id)"
                          class="news-inline-comments-box"
                          @click.stop
                        >
                          <!-- Comment Composer -->
                          <div class="inline-comment-row">
                            <input
                              v-model="inlineCommentInputs[item.id]"
                              type="text"
                              class="inline-comment-input"
                              placeholder="Write a comment..."
                              maxlength="200"
                              @keydown.enter.prevent="submitInlineComment(item.id)"
                            />
                            <button
                              class="inline-comment-post-btn"
                              :disabled="!inlineCommentInputs[item.id] || !inlineCommentInputs[item.id].trim()"
                              @click="submitInlineComment(item.id)"
                            >
                              Post
                            </button>
                          </div>

                          <!-- Existing Comments List -->
                          <div v-if="getNewsComments(item.id).length > 0" class="inline-comments-stream">
                            <div
                              v-for="c in getNewsComments(item.id)"
                              :key="c.id"
                              class="inline-comment-entry"
                            >
                              <div class="inline-comment-header">
                                <div class="inline-comment-author-wrap">
                                  <span class="inline-comment-author">{{ c.authorName }}</span>
                                  <span v-if="c.authorRole" class="inline-comment-role-tag">{{ c.authorRole }}</span>
                                  <span v-else-if="c.authorUnit" class="inline-comment-unit-tag">• {{ c.authorUnit }}</span>
                                </div>
                                <span class="inline-comment-time">{{ c.createdAt }}</span>
                              </div>
                              <div class="inline-comment-msg">{{ c.content }}</div>
                            </div>
                          </div>
                          <div v-else class="inline-no-comments">No comments yet. Be the first to comment!</div>
                        </div>
                      </transition>
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

                    <!-- Services Filled State: 2 Cards per Row Grid -->
                    <div v-else class="artisan-grid">
                      <div
                        v-for="artisan in displayedArtisans"
                        :key="artisan.id"
                        class="artisan-card"
                        @click="openArtisanModal(artisan)"
                      >
                        <!-- Cover Image Banner -->
                        <div class="artisan-cover-wrap">
                          <div class="artisan-cover-placeholder">
                            <span>{{ getCategoryIcon(artisan.category) }}</span>
                          </div>
                          <img
                            :src="artisan.imageUrl || getCategoryCover(artisan.category)"
                            :alt="artisan.name"
                            class="artisan-cover-img"
                            loading="lazy"
                            @error="(e) => (e.target as HTMLElement).style.opacity = '0'"
                          />
                          <span class="artisan-category-chip-floating">{{ artisan.category }}</span>
                        </div>

                        <!-- Card Body: Name on top, Rating & Reviews together at the bottom of the name -->
                        <div class="artisan-card-body">
                          <div class="artisan-name">{{ artisan.name }}</div>

                          <div class="artisan-rating-row">
                            <span class="rating-star">★</span>
                            <span class="rating-score">{{ artisan.rating }}</span>
                            <span class="rating-reviews">({{ artisan.reviews.length }} {{ artisan.reviews.length === 1 ? 'review' : 'reviews' }})</span>
                          </div>

                          <div class="artisan-trade">{{ artisan.trade }}</div>
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
    <Teleport to="body">
      <Transition name="sheet-modal">
        <div
          v-if="selectedArtisan"
          class="custom-sheet-overlay"
          @click.self="closeArtisanModal"
        >
          <div class="custom-sheet-container artisan-custom-sheet">
            <!-- Grab handle -->
            <div class="sheet-drag-handle-bar" @click="closeArtisanModal">
              <div class="sheet-drag-handle" />
            </div>

            <!-- Top Header -->
            <div class="sheet-modal-header">
              <div>
                <div class="sheet-header-tag">Estate Verified Service</div>
                <h2 class="sheet-header-title">{{ selectedArtisan.name }}</h2>
              </div>
              <button class="modal-close-btn" @click="closeArtisanModal" aria-label="Close">&times;</button>
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

          <!-- Estate Security Clearance Info -->
          <div class="artisan-info-card">
            <div class="info-card-item">
              <span class="info-label">Estate Security Clearance:</span>
              <span class="info-value text-green">Cleared for Resident Access</span>
            </div>
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
    </div>
  </Transition>
</Teleport>

    <!-- News Article Detail Modal -->
    <Teleport to="body">
      <Transition name="sheet-modal">
        <div
          v-if="selectedNewsItem"
          class="custom-sheet-overlay"
          @click.self="selectedNewsItem = null"
        >
          <div class="custom-sheet-container news-custom-sheet">
            <!-- Grab handle -->
            <div class="sheet-drag-handle-bar" @click="selectedNewsItem = null">
              <div class="sheet-drag-handle" />
            </div>

            <div class="sheet-modal-header">
              <div>
                <div class="sheet-header-tag">Estate Announcement</div>
                <h2 class="sheet-header-title">{{ selectedNewsItem.title }}</h2>
              </div>
              <button class="modal-close-btn" @click="selectedNewsItem = null">&times;</button>
            </div>

        <div class="sheet-modal-body news-sheet-scroll" id="newsModalScrollBody">
          <div class="news-sheet-meta">
            <span class="news-role-badge">
              {{ formatAdminRole(selectedNewsItem.authorRole || selectedNewsItem.adminRole) }}
            </span>
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

          <!-- ── COMMENTS SECTION ── -->
          <div class="news-comments-section" id="newsCommentsSection">
            <div class="news-comments-header">
              <span class="news-comments-title">
                Comments ({{ getNewsComments(selectedNewsItem.id).length }})
              </span>
              <div class="news-card-reactions">
                <button
                  type="button"
                  class="news-reaction-btn"
                  :class="{ active: getNewsReaction(selectedNewsItem.id).userReaction === 'like' }"
                  title="Like announcement"
                  @click.stop="toggleReaction(selectedNewsItem.id, 'like')"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 9V5a3 3 0 0 0-3-3l-4 9v11h11.28a2 2 0 0 0 2-1.7l1.38-9a2 2 0 0 0-2-2.3zM7 22H4a2 2 0 0 1-2-2v-7a2 2 0 0 1 2-2h3"></path>
                  </svg>
                  <span class="reaction-count">{{ getNewsReaction(selectedNewsItem.id).likes }}</span>
                </button>

                <button
                  type="button"
                  class="news-reaction-btn is-dislike"
                  :class="{ active: getNewsReaction(selectedNewsItem.id).userReaction === 'dislike' }"
                  title="Dislike announcement"
                  @click.stop="toggleReaction(selectedNewsItem.id, 'dislike')"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 15v4a3 3 0 0 0 3 3l4-9V2H5.72a2 2 0 0 0-2 1.7l-1.38 9a2 2 0 0 0 2 2.3zm7-13h3a2 2 0 0 1 2 2v7a2 2 0 0 1-2 2h-3"></path>
                  </svg>
                  <span class="reaction-count">{{ getNewsReaction(selectedNewsItem.id).dislikes }}</span>
                </button>
              </div>
            </div>

            <!-- Comment Composer -->
            <div class="news-comment-composer">
              <input
                id="newsCommentInput"
                v-model="newNewsCommentText"
                type="text"
                class="news-comment-input"
                placeholder="Write a comment..."
                @keydown.enter.prevent="submitNewsComment(selectedNewsItem.id)"
              />
              <button
                class="news-comment-submit-btn"
                :disabled="!newNewsCommentText.trim() || submittingComment"
                @click="submitNewsComment(selectedNewsItem.id)"
              >
                Post
              </button>
            </div>

            <!-- Comments List -->
            <div class="news-comments-list">
              <div
                v-for="c in getNewsComments(selectedNewsItem.id)"
                :key="c.id"
                class="news-comment-item"
              >
                <div class="news-comment-avatar">
                  {{ (c.authorName || 'R').charAt(0).toUpperCase() }}
                </div>
                <div class="news-comment-content">
                  <div class="news-comment-top">
                    <span class="news-comment-author">{{ c.authorName }}</span>
                    <span v-if="c.authorUnit" class="news-comment-unit">• {{ c.authorUnit }}</span>
                    <span class="news-comment-time">{{ c.createdAt }}</span>
                  </div>
                  <div class="news-comment-text">{{ c.content }}</div>
                </div>
              </div>
              <div v-if="getNewsComments(selectedNewsItem.id).length === 0" class="news-comments-empty">
                No comments yet. Be the first to leave a comment.
              </div>
            </div>
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
    </div>
  </Transition>
</Teleport>

    <!-- Property & Occupant Detail Bottom Sheet Modal -->
    <Teleport to="body">
      <Transition name="sheet-modal">
        <div
          v-if="selectedProperty"
          class="custom-sheet-overlay"
          @click.self="selectedProperty = null"
        >
          <div class="custom-sheet-container property-custom-sheet">
            <div class="sheet-drag-handle-bar" @click="selectedProperty = null">
              <div class="sheet-drag-handle" />
            </div>

            <div class="sheet-modal-header">
              <div>
                <div class="sheet-header-tag">OFFICIAL ESTATE DEED RECORD</div>
                <h2 class="sheet-header-title">{{ selectedProperty.unitNumber }} - {{ selectedProperty.streetName }}</h2>
              </div>
              <button class="modal-close-btn" @click="selectedProperty = null">&times;</button>
            </div>

            <div class="sheet-modal-body property-sheet-body">
              <!-- Property Specs Chips -->
              <div class="prop-detail-chips">
                <span class="prop-chip">{{ selectedProperty.type }}</span>
                <span class="prop-chip" :class="selectedProperty.isOccupied ? 'chip-occupied' : 'chip-vacant'">
                  {{ selectedProperty.isOccupied ? '● Occupied' : '○ Vacant' }}
                </span>
                <span class="prop-chip">{{ selectedProperty.deedRef || 'DEED-CP-' + selectedProperty.id }}</span>
              </div>

              <!-- Verified Occupants & Tenancy Section -->
              <div class="prop-sheet-section">
                <div class="prop-sheet-section-head">
                  <h4 class="prop-sheet-section-title">Verified Occupants</h4>
                  <span v-if="selectedProperty.isOccupied" class="sheet-occupants-count-pill">
                    {{ 1 + getAdditionalOccupantsCount(selectedProperty) }} Registered
                  </span>
                </div>

                <div v-if="selectedProperty.isOccupied && selectedProperty.primaryTenant" class="sheet-occupants-stack">
                  <!-- Primary Tenant Card with Contact Details -->
                  <div class="tenant-profile-card">
                    <div class="tenant-profile-header">
                      <div class="tenant-avatar-large">{{ selectedProperty.primaryTenant.name.charAt(0) }}</div>
                      <div class="tenant-profile-info">
                        <div class="tenant-name-row">
                          <span class="tenant-name-lg">{{ selectedProperty.primaryTenant.name }}</span>
                          <span class="tenant-badge-primary">Primary Tenant</span>
                        </div>
                        <div class="tenant-lease-meta">Move-in: {{ selectedProperty.primaryTenant.moveInDate }}</div>
                      </div>
                    </div>

                    <!-- Direct Contact Actions -->
                    <div class="tenant-contact-row">
                      <a :href="'tel:' + selectedProperty.primaryTenant.phone" class="tenant-call-action-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        <span>{{ selectedProperty.primaryTenant.phone }}</span>
                      </a>
                      <a :href="'https://wa.me/' + selectedProperty.primaryTenant.phone.replace(/[^0-9]/g, '')" target="_blank" rel="noopener noreferrer" class="tenant-wa-action-btn">
                        <span>WhatsApp</span>
                      </a>
                    </div>
                  </div>

                  <!-- Other Registered Occupants List -->
                  <div v-if="selectedProperty.occupants && selectedProperty.occupants.length > 0" class="additional-occupants-block">
                    <div class="additional-occupants-head">
                      <span class="add-occ-title">Other Occupants (+{{ selectedProperty.occupants.length }})</span>
                    </div>
                    <div class="additional-occupants-list">
                      <div
                        v-for="(occ, idx) in selectedProperty.occupants"
                        :key="idx"
                        class="additional-occupant-card"
                      >
                        <div class="add-occ-avatar" :class="'add-occ-avatar--' + (idx % 3)">
                          {{ occ.name.charAt(0) }}
                        </div>
                        <div class="add-occ-info">
                          <div class="add-occ-name-row">
                            <span class="add-occ-name">{{ occ.name }}</span>
                            <span class="add-occ-relation-tag">{{ occ.relation }}</span>
                          </div>
                          <div v-if="occ.phone" class="add-occ-phone-row">
                            <a :href="'tel:' + occ.phone" class="add-occ-quick-call">
                              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                              <span>{{ occ.phone }}</span>
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div v-else class="tenant-single-occupant-hint">
                    No additional occupants registered for this unit.
                  </div>
                </div>

                <div v-else class="tenant-empty-card">
                  <p>This unit is registered as vacant in estate management records. No active tenant occupant is currently attached.</p>
                </div>
              </div>

              <div class="prop-sheet-actions">
                <button
                  type="button"
                  class="prop-action-close-btn"
                  @click="selectedProperty = null"
                >
                  Done
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  IonPage, IonContent,
  IonRefresher, IonRefresherContent, IonSkeletonText,
} from '@ionic/vue'
// import type { Notification } from '@/types'
import { useAuthStore } from '@/stores/auth'
import { useNotificationsStore } from '@/stores/notifications'
import { usePassesStore } from '@/stores/passes'
import EstateHero from '@/components/EstateHero.vue'
import NotifBell from '@/components/NotifBell.vue'
import EmptyState from '@/components/EmptyState.vue'
import SkeletonList from '@/components/SkeletonList.vue'
import ErrorState from '@/components/ErrorState.vue'
import QRScannerModal from '@/components/QRScannerModal.vue'
import PhraseEntrySheet from '@/components/PhraseEntrySheet.vue'
import client from '@/api/client'
import { useToast } from '@/composables/useToast'
import type { HomeSummary } from '@/types'
import { MOCK_RESIDENT } from '@/api/mock'

export type NewsFeedItem = {
  id: number
  title: string
  content: string
  image: string | null
  authorRole?: string | null
  adminRole?: string | null
  adminName?: string | null
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
  avatarBg: string
  initials: string
  imageUrl?: string
  reviews: ArtisanReview[]
}

const router     = useRouter()
const auth       = useAuthStore()
const notifStore = useNotificationsStore()
const passesStore= usePassesStore()
const { showToast } = useToast()

const scannerOpen = ref(false)
const phraseSheetOpen = ref(false)

// ── Security Role State & Computeds ──
const activeSecurityTab   = ref<'access' | 'announcements' | 'emergencies'>('access')
const securityStation = computed(() => {
  const station = (summary.value as any)?.user?.assignedStation || (auth.resident as any)?.assignedStation
  if (!station || station.includes('Main Entrance Gate') || station.includes('Terminal 1')) {
    return 'Main Gate'
  }
  return station
})
const securityPasses = computed(() => {
  return (summary.value?.recentPasses || []) as any[]
})

const displayedSecurityPasses = computed(() => {
  return securityPasses.value.slice(0, 5)
})

const securityAnnouncements = computed(() => {
  return (summary.value as any)?.announcements || []
})

const securityEmergencies = computed(() => {
  return (summary.value as any)?.emergencies || []
})

// ── Homeowner Role State & Types ──
export interface PropertyOccupant {
  name: string
  relation: string
  phone?: string
  moveInDate?: string
}

export interface PropertyPortfolioItem {
  id: number | string
  unitNumber: string
  streetName: string
  type: string
  isOccupied: boolean
  deedRef?: string
  primaryTenant?: {
    name: string
    phone: string
    moveInDate: string
    verified: boolean
  } | null
  occupants?: PropertyOccupant[]
  registeredHouseholdCount: number
}

const activeHomeownerTab = ref<'properties' | 'announcements' | 'emergencies'>('properties')
const selectedProperty = ref<PropertyPortfolioItem | null>(null)

const homeownerProperties = ref<PropertyPortfolioItem[]>([
  {
    id: 101,
    unitNumber: 'House 4B',
    streetName: 'Palm Avenue',
    type: 'Detached 5-Bed Villa',
    isOccupied: true,
    deedRef: 'DEED-SE-2024-004B',
    primaryTenant: {
      name: 'Mr. Emeka Okafor',
      phone: '0803 123 4567',
      moveInDate: 'Jan 2025',
      verified: true
    },
    occupants: [
      { name: 'Mrs. Chioma Okafor', relation: 'Spouse', phone: '0803 987 6543', moveInDate: 'Jan 2025' },
      { name: 'Master David Okafor', relation: 'Son / Dependent', phone: '0803 123 4567', moveInDate: 'Jan 2025' }
    ],
    registeredHouseholdCount: 3
  },
  {
    id: 102,
    unitNumber: 'Flat 2, Block C',
    streetName: 'Orchid Close',
    type: 'Luxury 3-Bed Apartment',
    isOccupied: true,
    deedRef: 'DEED-SE-2023-012C',
    primaryTenant: {
      name: 'Dr. Folashade Adeleke',
      phone: '0809 876 5432',
      moveInDate: 'Aug 2024',
      verified: true
    },
    occupants: [
      { name: 'Mr. Tunde Adeleke', relation: 'Spouse / Co-Occupant', phone: '0802 345 6789', moveInDate: 'Aug 2024' }
    ],
    registeredHouseholdCount: 2
  }
])

function getAdditionalOccupantsCount(prop?: PropertyPortfolioItem | null): number {
  if (!prop || !prop.isOccupied) return 0
  if (prop.occupants && prop.occupants.length > 0) {
    return prop.occupants.length
  }
  if (prop.registeredHouseholdCount && prop.registeredHouseholdCount > 1) {
    return prop.registeredHouseholdCount - 1
  }
  return 0
}

function formatCompactCurrency(val: number | string | undefined | null): string {
  if (val === undefined || val === null || val === '') return '₦0'
  const num = typeof val === 'string' ? parseFloat(val.replace(/[^0-9.-]/g, '')) : val
  if (isNaN(num) || num === 0) return '₦0'
  const abs = Math.abs(num)
  const sign = num < 0 ? '-' : ''

  if (abs >= 1_000_000_000) {
    const formatted = (abs / 1_000_000_000).toFixed(abs % 1_000_000_000 === 0 ? 0 : 1)
    return `${sign}₦${formatted}b`
  }
  if (abs >= 1_000_000) {
    const formatted = (abs / 1_000_000).toFixed(abs % 1_000_000 === 0 ? 0 : 1)
    return `${sign}₦${formatted}m`
  }
  if (abs >= 100_000) {
    const formatted = (abs / 1_000).toFixed(abs % 1_000 === 0 ? 0 : 1)
    return `${sign}₦${formatted}k`
  }
  if (abs >= 1_000) {
    const formatted = (abs / 1_000).toFixed(abs % 1_000 === 0 ? 0 : 1)
    return `${sign}₦${formatted}k`
  }
  return `${sign}₦${abs.toLocaleString()}`
}

const homeownerLeviesInArrears = computed(() => {
  // Homeowners cannot view occupants' estate dues and levies.
  // They can only view dues applicable to them alone or assigned to them directly.
  return Number((summary.value as any)?.stats?.totalArrears ?? 0)
})

const homeownerOccupiedCount = computed(() => {
  return homeownerProperties.value.filter(p => p.isOccupied).length
})

function openPropertyDetails(prop: PropertyPortfolioItem) {
  selectedProperty.value = prop
}

function formatPassTime(dateStr?: string) {
  if (!dateStr) return ''
  try {
    const d = new Date(dateStr)
    return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })
  } catch {
    return dateStr
  }
}

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

const readNewsStorageKey = 'checkpass_read_news_ids'
const dismissedNewsStorageKey = 'checkpass_dismissed_news_ids'

const readNewsIds = ref<number[]>(JSON.parse(localStorage.getItem(readNewsStorageKey) || '[]'))
const dismissedNewsIds = ref<number[]>(JSON.parse(localStorage.getItem(dismissedNewsStorageKey) || '[]'))
const activeNewsFilter = ref<'all' | 'unread' | 'read'>('all')
const selectedNewsItem = ref<NewsFeedItem | null>(null)

// Active news list from live backend estate feed (excluding dismissed items)
const activeNewsList = computed(() => {
  return newsItems.value.filter(n => !dismissedNewsIds.value.includes(n.id))
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


export interface NewsComment {
  id: string
  newsId: number | string
  authorName: string
  authorUnit: string
  authorRole?: string
  content: string
  createdAt: string
}

const newsCommentsStorageKey = 'checkpass_news_comments'

const initialDefaultNewsComments: Record<string | number, NewsComment[]> = {
  101: [
    {
      id: 'c-101-1',
      newsId: 101,
      authorName: 'Engr. Tunde Adeleke',
      authorUnit: 'House 4B',
      content: 'Thank you for the early notice. Will borehole pumps remain powered on backup generators?',
      createdAt: '2 hours ago',
    },
    {
      id: 'c-101-2',
      newsId: 101,
      authorName: 'Estate Admin',
      authorUnit: 'Facility Office',
      authorRole: 'Estate Admin',
      content: 'Yes Engr. Tunde, generator circuit 2 feeds the borehole pump station throughout.',
      createdAt: '1 hour ago',
    },
  ],
  102: [
    {
      id: 'c-102-1',
      newsId: 102,
      authorName: 'Mrs. Folake Adeyemi',
      authorUnit: 'Flat 12',
      content: 'This has reduced waiting queues at the main gate significantly this morning.',
      createdAt: 'Yesterday',
    },
  ],
}

function loadInitialNewsComments(): Record<string | number, NewsComment[]> {
  try {
    const raw = localStorage.getItem(newsCommentsStorageKey)
    if (raw) return JSON.parse(raw)
  } catch {}
  return initialDefaultNewsComments
}

const newsCommentsMap = ref<Record<string | number, NewsComment[]>>(loadInitialNewsComments())

function getNewsComments(newsId: number | string): NewsComment[] {
  return newsCommentsMap.value[newsId] || []
}

// ── ESTATE ANNOUNCEMENT REACTIONS (Like & Dislike) ─────────────
export interface NewsReactionState {
  likes: number
  dislikes: number
  userReaction?: 'like' | 'dislike' | null
}

const newsReactionsStorageKey = 'checkpass_news_reactions'

const initialDefaultNewsReactions: Record<string | number, NewsReactionState> = {
  101: { likes: 12, dislikes: 0, userReaction: null },
  102: { likes: 8, dislikes: 1, userReaction: null },
}

function loadNewsReactions(): Record<string | number, NewsReactionState> {
  try {
    const raw = localStorage.getItem(newsReactionsStorageKey)
    if (raw) return JSON.parse(raw)
  } catch {}
  return initialDefaultNewsReactions
}

const newsReactions = ref<Record<string | number, NewsReactionState>>(loadNewsReactions())

function getNewsReaction(id: number | string): NewsReactionState {
  if (!newsReactions.value[id]) {
    newsReactions.value[id] = { likes: 0, dislikes: 0, userReaction: null }
  }
  return newsReactions.value[id]
}

function saveNewsReactions() {
  try {
    localStorage.setItem(newsReactionsStorageKey, JSON.stringify(newsReactions.value))
  } catch {}
}

function toggleReaction(id: number | string, type: 'like' | 'dislike') {
  const state = getNewsReaction(id)
  if (state.userReaction === type) {
    // Undo reaction
    if (type === 'like') {
      state.likes = Math.max(0, state.likes - 1)
    } else {
      state.dislikes = Math.max(0, state.dislikes - 1)
    }
    state.userReaction = null
  } else {
    // If switching reaction from opposite
    if (state.userReaction === 'like') {
      state.likes = Math.max(0, state.likes - 1)
    } else if (state.userReaction === 'dislike') {
      state.dislikes = Math.max(0, state.dislikes - 1)
    }
    // Set new reaction
    if (type === 'like') {
      state.likes++
    } else {
      state.dislikes++
    }
    state.userReaction = type
  }
  saveNewsReactions()
}

const newNewsCommentText = ref('')
const submittingComment = ref(false)

function submitNewsComment(newsId: number | string) {
  const text = newNewsCommentText.value.trim()
  if (!text || submittingComment.value) return
  submittingComment.value = true

  let authorName = 'Resident Neighbor'
  let authorUnit = 'Estate Resident'
  let authorRole: string | undefined

  if (auth.isSecurityUser) {
    authorName = (summary.value as any)?.user?.name || JSON.parse(localStorage.getItem('w_user') ?? 'null')?.name || 'Security Officer'
    authorUnit = securityStation.value || 'Main Gate'
    authorRole = 'Security Officer'
  } else {
    authorName = auth.resident?.name || 'Resident Neighbor'
    authorUnit = auth.resident?.flatAddress || 'Estate Resident'
    authorRole = auth.resident?.role || undefined
  }

  const newComment: NewsComment = {
    id: 'c-' + Date.now(),
    newsId,
    authorName,
    authorUnit,
    authorRole,
    content: text,
    createdAt: 'Just now',
  }

  if (!newsCommentsMap.value[newsId]) {
    newsCommentsMap.value[newsId] = []
  }
  newsCommentsMap.value[newsId].push(newComment)

  try {
    localStorage.setItem(newsCommentsStorageKey, JSON.stringify(newsCommentsMap.value))
  } catch {}

  newNewsCommentText.value = ''
  submittingComment.value = false
  showToast('Comment posted', 'success')
}


const expandedNewsIds = ref<(number | string)[]>([])
const openInlineCommentsIds = ref<(number | string)[]>([])
const inlineCommentInputs = ref<Record<string | number, string>>({})

function toggleExpandNews(id: number | string) {
  const idx = expandedNewsIds.value.indexOf(id)
  if (idx >= 0) expandedNewsIds.value.splice(idx, 1)
  else expandedNewsIds.value.push(id)
}

function toggleInlineComments(id: number | string) {
  const idx = openInlineCommentsIds.value.indexOf(id)
  if (idx >= 0) {
    openInlineCommentsIds.value.splice(idx, 1)
  } else {
    openInlineCommentsIds.value.push(id)
    if (!inlineCommentInputs.value[id]) {
      inlineCommentInputs.value[id] = ''
    }
  }
}

function submitInlineComment(newsId: number | string) {
  const text = (inlineCommentInputs.value[newsId] || '').trim()
  if (!text) return

  let authorName = 'Resident Neighbor'
  let authorUnit = 'Estate Resident'
  let authorRole: string | undefined

  if (auth.isSecurityUser) {
    authorName = (summary.value as any)?.user?.name || JSON.parse(localStorage.getItem('w_user') ?? 'null')?.name || 'Security Officer'
    authorUnit = securityStation.value || 'Main Gate'
    authorRole = 'Security Officer'
  } else {
    authorName = auth.resident?.name || 'Resident Neighbor'
    authorUnit = auth.resident?.flatAddress || 'Estate Resident'
    authorRole = auth.resident?.role || undefined
  }

  const newComment: NewsComment = {
    id: 'c-' + Date.now(),
    newsId,
    authorName,
    authorUnit,
    authorRole,
    content: text,
    createdAt: 'Just now',
  }

  if (!newsCommentsMap.value[newsId]) {
    newsCommentsMap.value[newsId] = []
  }
  newsCommentsMap.value[newsId].push(newComment)

  try {
    localStorage.setItem(newsCommentsStorageKey, JSON.stringify(newsCommentsMap.value))
  } catch {}

  inlineCommentInputs.value[newsId] = ''
  showToast('Comment posted', 'success')
}

function isLongNewsContent(text: string): boolean {
  if (!text) return false
  return text.length > 95 || text.includes('\n')
}

function formatAdminRole(role?: string | null): string {
  if (!role) return 'Estate Admin'
  if (role === 'super_admin') return 'Super Admin'
  if (role === 'estate_admin') return 'Estate Admin'
  if (role === 'exco') return 'Estate Exco'
  if (role === 'manager') return 'Estate Manager'
  return role
}

function openNewsArticle(item: NewsFeedItem, focusComment = false) {
  markNewsRead(item.id)
  selectedNewsItem.value = item
  newNewsCommentText.value = ''
  if (focusComment) {
    setTimeout(() => {
      const input = document.getElementById('newsCommentInput') as HTMLInputElement | null
      if (input) input.focus()
    }, 280)
  }
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

function getCategoryCover(category: string): string {
  switch (category) {
    case 'Electrician':
      return 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=500&q=80'
    case 'Plumber':
      return 'https://images.unsplash.com/photo-1585704032915-c3400ca199e7?auto=format&fit=crop&w=500&q=80'
    case 'AC Repair':
      return 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=500&q=80'
    case 'Cleaning':
      return 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=500&q=80'
    case 'Carpentry':
      return 'https://images.unsplash.com/photo-1538688525198-9b88f6f53126?auto=format&fit=crop&w=500&q=80'
    default:
      return 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=500&q=80'
  }
}

function getCategoryIcon(category: string): string {
  switch (category) {
    case 'Electrician': return '⚡'
    case 'Plumber': return '🔧'
    case 'AC Repair': return '❄️'
    case 'Cleaning': return '✨'
    case 'Carpentry': return '🪚'
    case 'Buy & Sell': return '🛍️'
    default: return '🛠️'
  }
}

const artisansList = ref<ArtisanItem[]>([])
const artisansLoading = ref(false)

async function loadArtisans() {
  artisansLoading.value = true
  try {
    const res = await client.get('/v1/artisans')
    if (res.data?.artisans && Array.isArray(res.data.artisans)) {
      artisansList.value = res.data.artisans.map((a: any) => ({
        ...a,
        imageUrl: a.imageUrl || getCategoryCover(a.category),
      }))
    }
  } catch (e) {
    console.warn('Failed loading estate artisans:', e)
  } finally {
    artisansLoading.value = false
  }
}

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

async function submitArtisanReview() {
  if (!selectedArtisan.value || !newReviewComment.value.trim() || submittingReview.value) return
  submittingReview.value = true

  try {
    const res = await client.post(`/v1/artisans/${selectedArtisan.value.id}/reviews`, {
      rating: newReviewRating.value,
      comment: newReviewComment.value.trim(),
    })

    if (res.data?.review && selectedArtisan.value) {
      selectedArtisan.value.reviews.unshift(res.data.review)
      if (res.data.artisan?.rating) {
        selectedArtisan.value.rating = res.data.artisan.rating
      }
    }
    showToast('Your review has been posted successfully!', 'success')
    showReviewForm.value = false
    newReviewComment.value = ''
    await loadArtisans()
  } catch (err: any) {
    showToast(err.response?.data?.message || 'Failed to submit review.', 'error')
  } finally {
    submittingReview.value = false
  }
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
    whatsapp: '2348000000000',
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
    whatsapp: '2348000000000',
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
    whatsapp: '2348000000000',
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
    whatsapp: '2348000000000',
    iconEmoji: '📺',
  },
])

const displayedBuyItems = computed(() => {
  if (activeBuyCategory.value === 'All') return buyItemsList.value
  return buyItemsList.value.filter(item => item.category === activeBuyCategory.value)
})

const firstName = computed(() => {
  if (auth.isSecurityUser) {
    const secName = (summary.value as any)?.user?.name || JSON.parse(localStorage.getItem('w_user') ?? 'null')?.name || 'Officer'
    return secName.trim().split(' ')[0]
  }
  const name = auth.resident?.name || summary.value?.resident?.name || JSON.parse(localStorage.getItem('w_user') ?? 'null')?.name || ''
  const trimmed = name.trim()
  return trimmed ? trimmed.split(' ')[0] : 'Resident'
})

// Multi-Role Persona State & Handler
const hasMultipleRoles = computed(() => {
  if (auth.userRoles && auth.userRoles.length > 1) return true
  if ((auth.resident as any)?.role === 'owner') return true
  return false
})

const orderedRoles = computed(() => {
  let roles = auth.userRoles || []
  if (roles.length <= 1 && (auth.resident as any)?.role === 'owner') {
    roles = [
      { role: 'homeowner', label: 'Homeowner', description: 'Estate Deed Holder' },
      { role: 'resident', label: 'Occupant', description: auth.resident?.flatAddress || 'Resident View' }
    ]
  }
  return roles.map(r => {
    let displayLabel = r.label
    if (r.role === 'admin') displayLabel = 'Admin'
    if (r.role === 'homeowner') displayLabel = 'Homeowner'
    if (r.role === 'resident' || r.role === 'occupant') displayLabel = 'Occupant'
    if (r.role === 'security' || r.role === 'guard') displayLabel = 'Security'
    return {
      ...r,
      label: displayLabel,
    }
  }).sort((a, b) => {
    // Strictly: admin -> homeowner -> occupants -> security
    const order: Record<string, number> = { admin: 1, homeowner: 2, resident: 3, occupant: 3, security: 4, guard: 4 }
    const orderA = order[a.role] ?? 99
    const orderB = order[b.role] ?? 99
    return orderA - orderB
  })
})

function handleRoleSwitch(role: any) {
  if (auth.activeRole === role) return
  auth.setActiveRole(role)
  const roleNames: Record<string, string> = {
    admin: 'Admin',
    homeowner: 'Homeowner',
    resident: 'Occupant',
    occupant: 'Occupant',
    security: 'Gate Security',
    guard: 'Gate Security',
  }
  const roleName = roleNames[role] || role
  showToast(`Switched to ${roleName} view`, 'success')
  loadSummary()
}
const greeting  = computed(() => {
  const h = new Date().getHours()
  if (h < 12) return 'Good morning'
  if (h < 17) return 'Good afternoon'
  return 'Good evening'
})

const totalPassesCount = computed(() => {
  return passesStore.passes.length
})

const hasMoreThanFivePasses = computed(() => totalPassesCount.value > 5)

const allActivePasses = computed(() => {
  return passesStore.passes.filter(p => p.status !== 'Exited' && p.status !== 'Revoked')
})

const displayedHomePasses = computed(() => allActivePasses.value.slice(0, 5))
// const recentNotifs  = computed(() => summary.value.notifications.slice(0, 3))

async function loadSummary() {
  summaryLoading.value = true
  summaryError.value   = false
  try {
    const res = await client.get('/v1/home/summary')
    if (res.data && typeof res.data === 'object' && res.data.stats) {
      summary.value = res.data
      notifStore.notifications = res.data.notifications || []
      if (res.data.resident) auth.updateResident(res.data.resident)
      try {
        localStorage.setItem('cp_home_summary', JSON.stringify(res.data))
      } catch {}
    } else {
      throw new Error('Invalid summary response from server')
    }
  } catch (err) {
    // Graceful offline/live fallback: safe stats with empty passes
    let fallback: HomeSummary | null = null
    try {
      const cached = localStorage.getItem('cp_home_summary')
      if (cached) {
        const parsed = JSON.parse(cached)
        if (parsed && typeof parsed === 'object' && parsed.stats) {
          fallback = parsed
        }
      }
    } catch {}

    if (!fallback) {
      const storedUser = JSON.parse(localStorage.getItem('w_user') ?? 'null')
      const storedResident = JSON.parse(localStorage.getItem('w_resident') ?? 'null')
      const residentData = auth.resident || storedResident || {
        ...MOCK_RESIDENT,
        name: storedUser?.name || 'Kemi Adeleke',
        estateName: storedUser?.estateName || 'Sheffy Estate',
        flatAddress: 'VIP 2, VVIP 2, 1% 2',
      }
      fallback = {
        resident: residentData,
        stats: { onPremises: 0, activePasses: 0, newToday: 0 },
        recentPasses: [],
        notifications: [],
      }
    }

    summary.value = fallback
    notifStore.notifications = fallback.notifications || []
    if (fallback.resident && (!auth.resident || !auth.resident.name)) {
      auth.updateResident(fallback.resident)
    }
    summaryError.value = false
  } finally {
    summaryLoading.value = false
  }

  await loadNews()
}

async function loadNews() {
  newsLoading.value = true
  try {
    const res = await client.get('/v1/news')
    const items = res.data?.news || []
    if (items.length > 0) {
      newsItems.value = items.map((item: NewsFeedItem & { image?: string | null; createdAt?: string | null }) => ({
        id: item.id,
        title: item.title,
        content: item.content,
        image: item.image ?? null,
        authorRole: (item as any).adminRole || (item as any).authorRole || 'Estate Admin',
        adminName: (item as any).adminName ?? null,
        createdAt: item.createdAt ?? null,
      }))
    } else {
      newsItems.value = []
    }
  } catch {
    newsItems.value = []
  } finally {
    newsLoading.value = false
  }
}

async function onRefresh(event: CustomEvent) {
  await Promise.all([loadSummary(), loadNews(), loadArtisans(), passesStore.fetchAll()])
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
  const gradients = [
    'linear-gradient(135deg, #0A5C38, #00C97A)',
    'linear-gradient(135deg, #1A3F8F, #4A90D9)',
    'linear-gradient(135deg, #7C3AED, #A855F7)',
    'linear-gradient(135deg, #D97706, #FBBF24)',
    'linear-gradient(135deg, #0284C7, #38BDF8)',
  ]
  const idx = (name.charCodeAt(0) || 0) % gradients.length
  return gradients[idx]
}

function statusClass(status: string) {
  if (status === 'On-site') return 'status-onsite'
  if (status === 'Pending') return 'status-pending'
  if (status === 'Expired') return 'status-expired'
  return 'status-exited'
}

onMounted(() => {
  loadSummary()
  loadNews()
  loadArtisans()
  passesStore.fetchAll()
})
</script>

<style scoped>
.home-content { --background: var(--w-bg); }
.home-title { font-size: 16px !important; font-weight: 600; }
.content-pad { padding: 16px 16px 100px; display: flex; flex-direction: column; gap: 20px; }

/* ─── ROLE SELECTOR TABS (HOMEOWNER / OCCUPANT) ─── */
/* ─── ROLE SELECTOR TABS (ADMIN / HOMEOWNER / OCCUPANT / SECURITY) ─── */
.role-selector-wrap {
  width: 100%;
  margin-bottom: -6px;
  overflow-x: auto;
  overflow-y: hidden;
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE/Edge */
  -webkit-overflow-scrolling: touch;
  padding: 2px 0;
}

.role-selector-wrap::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}

.role-selector-tabs {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: 1fr;
  display: flex;
  align-items: center;
  gap: 6px;
  background: #EBE8E2;
  padding: 4px;
  border-radius: 14px;
  border: 1px solid #DFDBD3;
  width: 100%;
  min-width: max-content;
}

.role-tab-btn {
  display: flex;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  background: transparent;
  border: 1px solid transparent;
  border-radius: 10px;
  padding: 9px 14px;
  padding: 9px 16px;
  color: #64748B;
  cursor: pointer;
  transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1);
  white-space: nowrap;
  flex: 1 0 auto;
}

.role-tab-btn:hover:not(.is-active) {
  background: rgba(255, 255, 255, 0.5);
  color: #1E293B;
}

.role-tab-btn.is-active {
  background: #FFFFFF;
  color: var(--w-primary, #0A5C38);
  border-color: rgba(10, 92, 56, 0.15);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
}

.role-tab-btn:active {
  transform: scale(0.975);
}

.tab-role-icon {
  flex-shrink: 0;
  color: #94A3B8;
  transition: color 0.18s ease;
}

.role-tab-btn.is-active .tab-role-icon {
  color: var(--w-primary, #0A5C38);
}

.role-tab-label {
  font-size: 14px;
  font-weight: 700;
  letter-spacing: -0.01em;
  font-family: var(--w-font-display, inherit);
}

/* ─── ADMIN VIEW HERO & PORTAL CARDS ─── */
.admin-hero-card {
  background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
  border-color: #334155;
}

.admin-pill {
  background: rgba(59, 130, 246, 0.18);
  color: #60A5FA;
  border: 1px solid rgba(59, 130, 246, 0.3);
}

.admin-quick-portal-card {
  background: #FFFFFF;
  border: 1.5px solid #E2E8F0;
  border-radius: 16px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.admin-portal-header {
  display: flex;
  gap: 12px;
  align-items: center;
}

.admin-portal-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #EFF6FF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}

.admin-portal-info h4 {
  font-size: 15px;
  font-weight: 700;
  color: #0F172A;
  margin: 0 0 2px;
}

.admin-portal-info p {
  font-size: 12px;
  color: #64748B;
  margin: 0;
}

.admin-portal-btn {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #0A5C38;
  color: #FFFFFF;
  font-size: 13.5px;
  font-weight: 700;
  padding: 11px 16px;
  border-radius: 10px;
  text-decoration: none;
  transition: opacity 0.2s ease;
}

.admin-portal-btn:active {
  opacity: 0.9;
}
.section { display: flex; flex-direction: column; gap: 10px; }
.section-header { display: flex; justify-content: space-between; align-items: center; }
.section-title  { font-size: 17px; font-weight: 700; color: var(--w-text); }
.see-all { background: none; border: none; color: var(--w-primary); font-size: 14px; font-weight: 600; cursor: pointer; padding: 4px 0; }
.pass-list { display: flex; flex-direction: column; gap: 10px; }
.passes-shell { display: flex; flex-direction: column; gap: 12px; }

/* ── STICKY NAVIGATION TABS BAR ── */
.home-top-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: calc(var(--ion-safe-area-top, 0px) + 14px) 16px 6px;
  background: var(--w-bg);
}
.home-title {
  margin: 0;
  font-size: 19px !important;
  font-weight: 700;
  color: var(--w-text);
  letter-spacing: -0.3px;
  line-height: 1.2;
}
.home-notif-wrap {
  display: flex;
  align-items: center;
}

/* ── STICKY NAVIGATION TABS BAR (Docks at top as greeting scrolls off) ── */
.passes-tabs-sticky-bar {
  position: sticky;
  top: 0;
  z-index: 40;
  background: var(--w-bg, #F4F2EE);
  padding-top: calc(var(--ion-safe-area-top, 0px) + 12px);
  margin-top: calc(-1 * (var(--ion-safe-area-top, 0px) + 12px));
  margin-left: -16px;
  margin-right: -16px;
  padding-left: 16px;
  padding-right: 16px;
  padding-bottom: 2px;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-bottom: 1.5px solid var(--w-border, #E5EAE7);
  box-shadow: 0 4px 12px -3px rgba(0, 0, 0, 0.04);
}

.passes-tabs {
  display: flex;
  gap: 24px;
  align-items: center;
  border-bottom: none;
  margin-bottom: 0;
  overflow-x: auto;
  scrollbar-width: none;
}
.passes-tabs::-webkit-scrollbar {
  display: none;
}
.passes-tab {
  background: none;
  border: none;
  padding: 0 0 10px;
  color: #8C9991;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  white-space: nowrap;
  flex-shrink: 0;
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

.tab-count-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #E8ECE9;
  color: #4A5568;
  font-size: 11px;
  font-weight: 700;
  height: 18px;
  min-width: 18px;
  border-radius: 999px;
  padding: 0 5.5px;
  line-height: 1;
  transition: all 0.2s ease;
}
.passes-tab.active .tab-count-pill {
  background: #E8F5EE;
  color: #0A5C38;
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
  padding: 0 5.5px;
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

/* ── ARTISAN CARDS (Services - 2 in a row with Cover Photo) ── */
.artisan-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}
.artisan-card {
  background: #FFFFFF;
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 14px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.artisan-card:active {
  transform: scale(0.97);
}
.artisan-cover-wrap {
  width: 100%;
  aspect-ratio: 16 / 10;
  overflow: hidden;
  background: linear-gradient(135deg, #0A5C38, #113824);
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
}
.artisan-cover-placeholder {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 30px;
  background: linear-gradient(135deg, #0A5C38 0%, #15803D 100%);
  color: #FFFFFF;
}
.artisan-cover-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  position: relative;
  z-index: 1;
  transition: transform 0.25s ease, opacity 0.2s ease;
}
.artisan-category-chip-floating {
  position: absolute;
  bottom: 6px;
  left: 6px;
  z-index: 2;
  background: rgba(17, 32, 25, 0.78);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  color: #FFFFFF;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 999px;
  letter-spacing: 0.01em;
}
.artisan-card:hover .artisan-cover-img {
  transform: scale(1.03);
}
.artisan-card-body {
  padding: 10px 12px 12px;
  display: flex;
  flex-direction: column;
  gap: 3px;
  flex: 1;
}
.artisan-name {
  font-size: 14.5px;
  font-weight: 700;
  color: #1E293B;
  line-height: 1.25;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.artisan-rating-row {
  display: flex;
  align-items: center;
  gap: 3px;
  margin-top: 1px;
}
.rating-star {
  color: #F59E0B;
  font-size: 13px;
  line-height: 1;
}
.rating-score {
  font-size: 12.5px;
  font-weight: 700;
  color: #1E293B;
}
.rating-reviews {
  font-size: 11.5px;
  font-weight: 400;
  color: #64748B;
  margin-left: 2px;
}
.artisan-trade {
  font-size: 11.5px;
  color: #64748B;
  line-height: 1.35;
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ── TELEPORT BOTTOM SHEET MODALS (Services Artisan & Estate Announcements) ── */
.custom-sheet-overlay {
  position: fixed;
  inset: 0;
  z-index: 99999;
  background: rgba(10, 20, 15, 0.6);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  display: flex;
  align-items: flex-end;
  justify-content: center;
}

.custom-sheet-container {
  width: 100%;
  max-width: 520px;
  height: 88vh;
  max-height: 88vh;
  background: #FFFFFF;
  border-radius: 28px 28px 0 0;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 -12px 40px rgba(0, 0, 0, 0.22);
  animation: sheetSlideUp 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.sheet-drag-handle-bar {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px 0 4px;
  cursor: pointer;
  touch-action: none;
  background: #FFFFFF;
  flex-shrink: 0;
}

.sheet-drag-handle {
  width: 44px;
  height: 5px;
  border-radius: 999px;
  background: #D1D5DB;
  transition: background 0.15s ease;
}

.sheet-drag-handle-bar:hover .sheet-drag-handle {
  background: #9CA3AF;
}

/* Vue Transition */
.sheet-modal-enter-active,
.sheet-modal-leave-active {
  transition: opacity 0.25s ease;
}
.sheet-modal-enter-from,
.sheet-modal-leave-to {
  opacity: 0;
}
.sheet-modal-enter-active .custom-sheet-container {
  animation: sheetSlideUp 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
.sheet-modal-leave-active .custom-sheet-container {
  animation: sheetSlideDown 0.22s cubic-bezier(0.7, 0, 0.84, 0) forwards;
}

@keyframes sheetSlideUp {
  from {
    transform: translateY(100%);
  }
  to {
    transform: translateY(0);
  }
}

@keyframes sheetSlideDown {
  from {
    transform: translateY(0);
  }
  to {
    transform: translateY(100%);
  }
}

/* ── ARTISAN & NEWS SHEET MODALS ── */
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
  font-size: 13.5px;
  line-height: 1.45;
  color: var(--w-muted);
  margin-bottom: 12px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-word;
}
.news-card-snippet.is-expanded {
  display: block;
  -webkit-line-clamp: unset;
  overflow: visible;
}
.news-card-footer {
  display: flex;
  align-items: center;
  padding-top: 2px;
}

/* ── Role Badge & Unread Dot ── */
.news-role-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 600;
  color: #475569;
  background: #F1F5F9;
  border: 1px solid #E2E8F0;
  border-radius: 999px;
  padding: 2.5px 8.5px;
  letter-spacing: 0.01em;
}
.news-unread-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #0A5C38;
  display: inline-block;
}

/* ── News Card Footer (Comment count on left, See more on right) ── */
.news-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 6px;
}
.news-card-comment-count {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11.5px;
  font-weight: 600;
  color: #6B7280;
  cursor: pointer;
  padding: 2px 4px;
  border-radius: 4px;
  transition: color 0.15s, background 0.15s;
}
.news-card-comment-count:hover, .news-card-comment-count:active {
  color: #0A5C38;
  background: #F0FDF4;
}
.news-see-more-link {
  font-size: 11.5px;
  font-weight: 700;
  color: #0A5C38;
  margin-left: auto;
  cursor: pointer;
  letter-spacing: 0.01em;
}
.news-see-more-link:hover {
  text-decoration: underline;
}

/* ── COMMENTS SECTION IN MODAL ── */
.news-comments-section {
  margin-top: 22px;
  padding-top: 18px;
  border-top: 1px solid #E5E7EB;
}
.news-comments-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}
.news-comments-title {
  font-size: 13.5px;
  font-weight: 700;
  color: #111827;
}
.news-comment-composer {
  display: flex;
  gap: 8px;
  align-items: center;
  margin-bottom: 16px;
}
.news-comment-input {
  flex: 1;
  border: 1px solid #D1D5DB;
  border-radius: 20px;
  padding: 8px 14px;
  font-size: 12.5px;
  outline: none;
  background: #F9FAFB;
  color: #111827;
  transition: border-color 0.15s, background 0.15s;
}
.news-comment-input:focus {
  border-color: #0A5C38;
  background: #FFFFFF;
}
.news-comment-submit-btn {
  background: #0A5C38;
  color: #FFFFFF;
  border: none;
  border-radius: 18px;
  padding: 8px 16px;
  font-size: 12.5px;
  font-weight: 700;
  cursor: pointer;
  transition: opacity 0.15s, transform 0.1s;
}
.news-comment-submit-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
.news-comment-submit-btn:not(:disabled):active {
  transform: scale(0.96);
}
.news-comments-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.news-comment-item {
  display: flex;
  gap: 10px;
  align-items: flex-start;
}
.news-comment-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: #E8F5EE;
  color: #0A5C38;
  font-weight: 700;
  font-size: 11.5px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.news-comment-content {
  flex: 1;
  background: #F8FAFC;
  border-radius: 12px;
  padding: 8px 12px;
  border: 1px solid #F1F5F9;
}
.news-comment-top {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-bottom: 3px;
  flex-wrap: wrap;
}
.news-comment-author {
  font-size: 12px;
  font-weight: 700;
  color: #1F2937;
}
.news-comment-unit {
  font-size: 11px;
  color: #6B7280;
}
.news-comment-time {
  font-size: 10.5px;
  color: #9CA3AF;
  margin-left: auto;
}
.news-comment-text {
  font-size: 12.5px;
  color: #374151;
  line-height: 1.4;
  word-break: break-word;
}
.news-comments-empty {
  font-size: 12px;
  color: #9CA3AF;
  text-align: center;
  padding: 14px 0;
  font-style: italic;
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
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 16px;
  background: #FFFFFF;
  box-shadow: none;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  text-align: left;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.pass-mini-row:active {
  transform: scale(0.98);
}
.pass-mini-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  color: #ffffff;
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.02em;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}
.pass-mini-body { flex: 1; min-width: 0; }
.pass-mini-name {
  font-size: 15px;
  font-weight: 600;
  color: #111827;
  line-height: 1.25;
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
  margin-top: 4px;
  width: 100%;
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 16px;
  background: #FFFFFF;
  box-shadow: none;
  color: #475569;
  font-size: 15px;
  font-weight: 600;
  padding: 12px 16px;
  cursor: pointer;
  transition: background-color 0.15s ease;
}
.view-all-btn:active {
  background: #F8FAFC;
}
.news-mini-empty {
  border-radius: 16px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  background: #FFFFFF;
  box-shadow: none;
  color: #6b7280;
  text-align: center;
  padding: 18px;
  font-size: 14px;
}
.news-mini-list { display: flex; flex-direction: column; gap: 8px; }
.news-mini-row {
  width: 100%;
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 16px;
  background: #FFFFFF;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
  padding: 14px 16px;
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
/* ─── SECURITY HERO CARD & MATRIX ─── */
.security-hero-card {
  background: var(--w-primary, #0A5C38);
  border-radius: var(--w-radius-xl, 18px);
  padding: 18px 20px;
  color: white;
  margin-bottom: 4px;
  box-shadow: 0 4px 14px rgba(10, 92, 56, 0.25);
}

.sec-hero-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}

.sec-estate-eyebrow {
  font-size: 12px;
  opacity: 0.85;
  margin-bottom: 3px;
  font-weight: 500;
}

.sec-station-title {
  font-family: var(--w-font-display, inherit);
  font-size: 17px;
  font-weight: 700;
  letter-spacing: -0.15px;
  line-height: 1.3;
}

.sec-duty-pill {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 9999px;
  font-size: 11.5px;
  font-weight: 700;
  background: rgba(0, 201, 122, 0.2);
  color: #00E68C;
  border: 1px solid rgba(0, 230, 140, 0.3);
}

.sec-duty-pulse {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #00E68C;
  box-shadow: 0 0 0 0 rgba(0, 230, 140, 0.7);
  animation: secPulseGreen 1.8s infinite;
}

@keyframes secPulseGreen {
  0% { box-shadow: 0 0 0 0 rgba(0, 230, 140, 0.7); }
  70% { box-shadow: 0 0 0 5px rgba(0, 230, 140, 0); }
  100% { box-shadow: 0 0 0 0 rgba(0, 230, 140, 0); }
}

.sec-matrix-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sec-matrix-box {
  flex: 1;
  min-width: 0;
  text-align: center;
  background: transparent;
  border: none;
  color: white;
  padding: 2px 2px;
  cursor: pointer;
  transition: transform 0.15s ease;
  overflow: hidden;
}

.sec-matrix-box:active {
  transform: scale(0.95);
}

.sec-matrix-val {
  font-family: var(--w-font-display, inherit);
  font-size: 20px;
  font-weight: 750;
  letter-spacing: -0.3px;
  line-height: 1.1;
  color: #FFFFFF !important;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sec-matrix-lbl {
  font-size: 10.5px;
  color: #FFFFFF;
  opacity: 0.85;
  margin-top: 2px;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sec-matrix-vdivider {
  width: 1px;
  height: 32px;
  background: rgba(255, 255, 255, 0.22);
}

/* ─── SECURITY STICKY TABS (ACCESS | ANNOUNCEMENTS | EMERGENCIES) ─── */
.sec-tabs-sticky-shell {
  position: sticky;
  top: 0;
  z-index: 20;
  background: var(--w-bg);
  padding: 8px 0 12px;
  margin: 0 -4px;
}

.sec-tabs-container {
  display: flex;
  background: #E5E7EB;
  padding: 4px;
  border-radius: 12px;
  gap: 4px;
}

.sec-tab-item {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 9px 8px;
  border-radius: 9px;
  border: none;
  background: transparent;
  color: #4B5563;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s ease;
}

.sec-tab-item.active {
  background: #FFFFFF;
  color: #0A5C38;
  font-weight: 700;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.sec-pill-count {
  font-size: 10px;
  font-weight: 800;
  padding: 1px 6px;
  border-radius: 9999px;
  background: rgba(0, 0, 0, 0.06);
  color: inherit;
}

.sec-pill-count--red {
  background: #FEE2E2 !important;
  color: #DC2626 !important;
}

/* ─── SECURITY TAB CONTENTS ─── */
.sec-tab-content {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.sec-filter-chips {
  display: flex;
  align-items: center;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 2px;
  -webkit-overflow-scrolling: touch;
}

.sec-filter-chips::-webkit-scrollbar {
  display: none;
}

.sec-chip-btn {
  padding: 6px 12px;
  border-radius: 9999px;
  border: 1px solid #D1D5DB;
  background: #FFFFFF;
  color: #4B5563;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.15s ease;
}

.sec-chip-btn.active {
  background: #0A5C38;
  color: #FFFFFF;
  border-color: #0A5C38;
}

.sec-search-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.sec-search-wrap svg {
  position: absolute;
  left: 14px;
  pointer-events: none;
}

.sec-search-input {
  width: 100%;
  padding: 10px 36px 10px 38px;
  font-size: 13px;
  border-radius: 10px;
  border: 1px solid #E5E7EB;
  background: #FFFFFF;
  outline: none;
}

.sec-search-input:focus {
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.12);
}

.sec-search-clear {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #9CA3AF;
  font-size: 12px;
  cursor: pointer;
}

.sec-phrase-trigger-row {
  margin-top: 4px;
}

.sec-phrase-trigger-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 10px 14px;
  background: #FFFFFF;
  border: 1.5px dashed #D1D5DB;
  border-radius: 12px;
  color: #374151;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.sec-phrase-trigger-btn svg {
  color: #0A5C38;
  flex-shrink: 0;
}

.sec-phrase-trigger-btn:hover {
  background: #F9FAFB;
  border-color: #0A5C38;
}

.sec-phrase-trigger-btn:active {
  background: rgba(10, 92, 56, 0.06);
  border-color: #0A5C38;
  color: #0A5C38;
  transform: scale(0.99);
}

.sec-empty-box {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid #E5E7EB;
  padding: 36px 20px;
  text-align: center;
}

.sec-empty-icon {
  font-size: 32px;
  margin-bottom: 8px;
}

.sec-empty-title {
  font-size: 15px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 4px;
}

.sec-empty-desc {
  font-size: 12.5px;
  color: #6B7280;
  margin: 0;
}

/* ── Clearance Passes Stream ── */
.sec-passes-stream {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.sec-pass-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid #E5E7EB;
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  cursor: pointer;
  transition: transform 0.14s ease, box-shadow 0.14s ease;
}

.sec-pass-card:active {
  transform: scale(0.985);
}

.sec-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.sec-visitor-info {
  display: flex;
  align-items: center;
  gap: 10px;
  min-width: 0;
}

.sec-visitor-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  color: #FFFFFF;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.sec-visitor-name {
  font-size: 14px;
  font-weight: 700;
  color: #111827;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sec-visitor-sub {
  font-size: 11.5px;
  color: #6B7280;
  display: flex;
  align-items: center;
  gap: 4px;
}

.sec-phone-link {
  color: #0A5C38;
  font-weight: 600;
  text-decoration: none;
}

.sec-dot {
  color: #D1D5DB;
}

.sec-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 9px;
  border-radius: 9999px;
  font-size: 10.5px;
  font-weight: 700;
}

.pill-onsite {
  background: #ECFDF5;
  color: #065F46;
  border: 1px solid #A7F3D0;
}

.pill-exited {
  background: #F3F4F6;
  color: #6B7280;
  border: 1px solid #E5E7EB;
}

.pill-declined {
  background: #FEF2F2;
  color: #DC2626;
  border: 1px solid #FECACA;
}

.status-micro-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: currentColor;
}

.sec-card-details {
  border-top: 1px solid #F3F4F6;
  padding-top: 8px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.sec-meta-row {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #4B5563;
}

.sec-meta-lbl {
  color: #9CA3AF;
  font-size: 11px;
}

.sec-meta-val {
  color: #1F2937;
}

.sec-meta-bottom {
  justify-content: space-between;
  margin-top: 2px;
}

.sec-plate-badge {
  font-family: ui-monospace, monospace;
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 5px;
}

.badge-plate {
  background: #F3F4F6;
  color: #111827;
  border: 1px solid #D1D5DB;
}

.badge-walk {
  background: #F9FAFB;
  color: #6B7280;
  border: 1px solid #E5E7EB;
}

.sec-time-stamp {
  font-size: 11px;
  color: #9CA3AF;
}

/* ── Announcements Stream ── */
.sec-news-stream {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.sec-news-card {
  background: #FFFFFF;
  border-radius: 14px;
  border: 1px solid #E5E7EB;
  padding: 16px;
}

.sec-news-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 6px;
}

.sec-news-cat {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 9999px;
  background: #ECFDF5;
  color: #0A5C38;
}

.sec-news-date {
  font-size: 11px;
  color: #9CA3AF;
}

.sec-news-title {
  font-size: 15px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 6px;
}

.sec-news-text {
  font-size: 13px;
  color: #4B5563;
  line-height: 1.5;
  margin: 0;
}

/* ── Emergencies Stream ── */
.sec-emergency-stream {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.sec-emergency-card {
  background: #FFF1F2;
  border: 1px solid #FECDD3;
  border-radius: 14px;
  padding: 16px;
}

.sec-em-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.sec-em-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  background: #E11D48;
  color: #FFFFFF;
  font-size: 11px;
  font-weight: 800;
  padding: 3px 9px;
  border-radius: 9999px;
  letter-spacing: 0.3px;
}

.sec-em-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #FFFFFF;
}

.sec-em-time {
  font-size: 11px;
  color: #9F1239;
  font-weight: 600;
}

.sec-em-body {
  margin-bottom: 12px;
}

.sec-em-resident {
  font-size: 15px;
  font-weight: 800;
  color: #881337;
}

.sec-em-unit {
  font-size: 12.5px;
  color: #9F1239;
  margin-top: 2px;
}

.sec-em-call-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #E11D48;
  color: #FFFFFF;
  text-decoration: none;
  font-size: 12.5px;
  font-weight: 700;
  padding: 7px 14px;
  border-radius: 8px;
}
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

/* ── Inline Comments Accordion & Controls ── */
.news-card-comment-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: transparent;
  border: none;
  padding: 4px 6px;
  border-radius: 6px;
  color: var(--w-muted);
  font-size: 12.5px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s ease;
}
.news-card-comment-btn:active {
  background: rgba(10, 92, 56, 0.08);
  color: #0A5C38;
}
.news-card-comment-btn svg {
  color: currentColor;
}
.news-see-more-link {
  background: transparent;
  border: none;
  padding: 4px 0;
  font-size: 12.5px;
  font-weight: 600;
  color: #0A5C38;
  cursor: pointer;
}

/* ── Reactions (Like & Dislike) ── */
.news-card-actions-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.news-card-reactions {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-left: auto;
}

.news-reaction-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  background: #F3F4F6;
  border: 1px solid #E5E7EB;
  border-radius: 999px;
  padding: 4px 9px;
  font-size: 11.5px;
  font-weight: 600;
  color: #4B5563;
  cursor: pointer;
  transition: all 0.16s ease;
  user-select: none;
}

.news-reaction-btn:hover {
  background: #E5E7EB;
  color: #1F2937;
}

.news-reaction-btn:active {
  transform: scale(0.92);
}

.news-reaction-btn.active {
  background: #E8F5EE;
  color: #0A5C38;
  border-color: #A3D9BD;
}

.news-reaction-btn.is-dislike.active {
  background: #FEE2E2;
  color: #DC2626;
  border-color: #FECACA;
}

.news-reaction-btn svg {
  width: 13.5px;
  height: 13.5px;
  stroke: currentColor;
  fill: transparent;
  transition: all 0.16s ease;
  flex-shrink: 0;
}

.news-reaction-btn.active svg {
  fill: currentColor;
}

.news-reaction-btn .reaction-count {
  font-size: 11.5px;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
  line-height: 1;
}

.sec-news-card-footer {
  margin-top: 12px;
  padding-top: 10px;
  border-top: 1px solid #F1F5F9;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.inline-comment-author-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.inline-comment-role-tag {
  display: inline-block;
  font-size: 9.5px;
  font-weight: 700;
  color: #0A5C38;
  background: #E8F5EE;
  padding: 1px 5px;
  border-radius: 4px;
  letter-spacing: 0.02em;
}

.inline-comment-unit-tag {
  font-size: 10.5px;
  color: #64748B;
}

.news-inline-comments-box {
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid var(--w-border);
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.inline-comment-row {
  display: flex;
  gap: 6px;
  align-items: center;
}
.inline-comment-input {
  flex: 1;
  height: 36px;
  background: #FFFFFF;
  border: 1.5px solid var(--w-border);
  border-radius: 8px;
  padding: 0 10px;
  font-size: 12.5px;
  color: var(--w-text);
  outline: none;
  transition: border-color 0.15s;
}
.inline-comment-input:focus {
  border-color: #0A5C38;
}
.inline-comment-post-btn {
  height: 36px;
  padding: 0 14px;
  background: #0A5C38;
  color: #FFFFFF;
  border: none;
  border-radius: 8px;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: opacity 0.15s;
}
.inline-comment-post-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}
.inline-comments-stream {
  display: flex;
  flex-direction: column;
  gap: 6px;
  max-height: 180px;
  overflow-y: auto;
  margin-top: 4px;
}
.inline-comment-entry {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  padding: 7px 10px;
}
.inline-comment-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: 2px;
}
.inline-comment-author {
  font-size: 11.5px;
  font-weight: 700;
  color: #1E293B;
}
.inline-comment-time {
  font-size: 10px;
  color: #94A3B8;
}
.inline-comment-msg {
  font-size: 12px;
  color: #334155;
  line-height: 1.35;
}
.inline-no-comments {
  font-size: 11.5px;
  color: #94A3B8;
  font-style: italic;
  padding: 4px 2px;
}

/* ─── HOMEOWNER VIEW STYLES ─── */
.homeowner-hero-card {
  background: linear-gradient(135deg, #0A5C38 0%, #07472A 100%) !important;
}

.homeowner-pill {
  background: rgba(255, 255, 255, 0.18) !important;
  color: #FFFFFF !important;
  border: 1px solid rgba(255, 255, 255, 0.3) !important;
}

.homeowner-tab-content {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.prop-portfolio-stream {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.prop-portfolio-card {
  background: var(--w-card-bg, #FFFFFF);
  border: 1.5px solid var(--w-border, #E2E8F0);
  border-radius: 16px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.prop-portfolio-card:active {
  transform: scale(0.985);
  border-color: #0A5C38;
}

.prop-card-head {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 12px;
}

.prop-badge-wrap {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.prop-type-badge {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #0A5C38;
  background: #E8F5EE;
  padding: 3px 8px;
  border-radius: 6px;
}

.prop-status-pill {
  font-size: 11.5px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 999px;
}

.prop-status-pill.is-occupied {
  background: #DCFCE7;
  color: #15803D;
}

.prop-status-pill.is-vacant {
  background: #F1F5F9;
  color: #64748B;
}

.prop-card-title {
  font-size: 16px;
  font-weight: 700;
  color: var(--w-text, #0F172A);
  margin: 0;
  letter-spacing: -0.01em;
}

.prop-occupant-box {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 0;
}

.prop-tenant-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.prop-tenant-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #0A5C38;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  font-weight: 700;
}

.prop-tenant-meta {
  display: flex;
  flex-direction: column;
}

.prop-tenant-name {
  font-size: 13.5px;
  font-weight: 700;
  color: #0F172A;
}

.prop-tenant-sub {
  font-size: 11px;
  color: #64748B;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 4px;
  flex-wrap: wrap;
}

.tenant-role-tag {
  color: #475569;
}

.sub-dot {
  color: #94A3B8;
}

.additional-occupants-badge {
  font-size: 10.5px;
  font-weight: 700;
  color: #0A5C38;
  background: #E8F5EE;
  padding: 1px 6px;
  border-radius: 9999px;
  border: 1px solid #C6E7D2;
  letter-spacing: -0.01em;
}

.additional-occupants-badge.sole-badge {
  color: #64748B;
  background: #F1F5F9;
  border-color: #E2E8F0;
}

.prop-vacant-box {
  background: #F8FAFC;
  border: 1px dashed #CBD5E1;
  border-radius: 12px;
  padding: 14px;
  margin-bottom: 0;
  color: #64748B;
  font-size: 12.5px;
  text-align: center;
}

/* Homeowner Emergencies & Dispatch */
.homeowner-dispatch-card {
  background: var(--w-card-bg, #FFFFFF);
  border: 1.5px solid var(--w-border, #E2E8F0);
  border-radius: 16px;
  padding: 16px;
}

.dispatch-title {
  font-size: 15px;
  font-weight: 700;
  color: var(--w-text, #0F172A);
  margin: 0 0 4px;
}

.dispatch-sub {
  font-size: 12px;
  color: #64748B;
  margin: 0 0 14px;
  line-height: 1.4;
}

.dispatch-actions-grid {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.dispatch-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 14px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  text-decoration: none;
  color: inherit;
  transition: all 0.15s ease;
}

.dispatch-btn:active {
  background: #E8F5EE;
  border-color: #0A5C38;
}

.dispatch-btn--red {
  background: #FEF2F2;
  border-color: #FCA5A5;
}

.dispatch-btn--red:active {
  background: #FEE2E2;
}

.dispatch-icon {
  font-size: 20px;
}

.dispatch-info {
  display: flex;
  flex-direction: column;
}

.dispatch-name {
  font-size: 13px;
  font-weight: 700;
  color: #0F172A;
}

.dispatch-num {
  font-size: 11.5px;
  color: #64748B;
  font-weight: 600;
}

.homeowner-incidents-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 4px;
}

.incident-sec-title {
  font-size: 13px;
  font-weight: 700;
  color: #64748B;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin: 0;
}

.incident-log-card {
  background: var(--w-card-bg, #FFFFFF);
  border: 1px solid var(--w-border, #E2E8F0);
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.incident-log-badge {
  font-size: 10px;
  font-weight: 800;
  color: #0A5C38;
  background: #E8F5EE;
  padding: 2px 6px;
  border-radius: 4px;
  align-self: flex-start;
  letter-spacing: 0.04em;
}

.incident-log-badge.badge-green {
  color: #0369A1;
  background: #E0F2FE;
}

.incident-log-text {
  font-size: 12.5px;
  color: var(--w-text, #1E293B);
  line-height: 1.4;
}

.incident-log-time {
  font-size: 11px;
  color: #94A3B8;
}

/* Property Modal Bottom Sheet */
.property-custom-sheet {
  max-height: 85vh;
  display: flex;
  flex-direction: column;
}

.property-sheet-body {
  overflow-y: auto;
  padding: 16px 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.prop-detail-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.prop-chip {
  font-size: 11.5px;
  font-weight: 600;
  background: #F1F5F9;
  color: #475569;
  padding: 4px 10px;
  border-radius: 8px;
}

.prop-chip.chip-occupied {
  background: #DCFCE7;
  color: #15803D;
  font-weight: 700;
}

.prop-chip.chip-vacant {
  background: #F8FAFC;
  color: #94A3B8;
}

.prop-sheet-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.prop-sheet-section-title {
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #64748B;
  margin: 0;
}

.prop-sheet-section-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.sheet-occupants-count-pill {
  font-size: 11px;
  font-weight: 700;
  color: #0A5C38;
  background: #E8F5EE;
  padding: 2px 8px;
  border-radius: 9999px;
  border: 1px solid #C6E7D2;
}

.sheet-occupants-stack {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.tenant-name-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.tenant-badge-primary {
  font-size: 9.5px;
  font-weight: 750;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #0A5C38;
  background: #E8F5EE;
  border: 1px solid #C6E7D2;
  padding: 1px 6px;
  border-radius: 9999px;
}

.additional-occupants-block {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 4px;
}

.additional-occupants-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.add-occ-title {
  font-size: 11.5px;
  font-weight: 750;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #475569;
}

.additional-occupants-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.additional-occupant-card {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 10px 12px;
}

.add-occ-avatar {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 750;
  flex-shrink: 0;
}

.add-occ-avatar--0 {
  background: #0284C7;
}

.add-occ-avatar--1 {
  background: #7C3AED;
}

.add-occ-avatar--2 {
  background: #D97706;
}

.add-occ-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
  min-width: 0;
}

.add-occ-name-row {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.add-occ-name {
  font-size: 13.5px;
  font-weight: 700;
  color: #0F172A;
}

.add-occ-relation-tag {
  font-size: 10.5px;
  font-weight: 600;
  color: #475569;
  background: #E2E8F0;
  padding: 1px 6px;
  border-radius: 9999px;
}

.add-occ-phone {
  font-size: 11.5px;
  color: #64748B;
  font-weight: 500;
}

.tenant-single-occupant-hint {
  font-size: 12px;
  color: #64748B;
  font-style: italic;
  padding: 6px 2px;
}

.tenant-profile-card {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 14px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.tenant-profile-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.tenant-avatar-large {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  background: #0A5C38;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: 800;
}

.tenant-profile-info {
  display: flex;
  flex-direction: column;
}

.tenant-name-lg {
  font-size: 15px;
  font-weight: 700;
  color: #0F172A;
}

.tenant-phone-lg {
  font-size: 12.5px;
  color: #0A5C38;
  font-weight: 600;
}

.tenant-lease-meta {
  font-size: 11px;
  color: #64748B;
}

.tenant-breakdown-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
  padding-top: 10px;
  border-top: 1px solid #E2E8F0;
}

.tenant-stat-box {
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  border-radius: 8px;
  padding: 8px 6px;
  text-align: center;
}

.stat-num {
  font-size: 15px;
  font-weight: 800;
  color: #0F172A;
}

.stat-lbl {
  font-size: 10px;
  color: #64748B;
  margin-top: 2px;
  font-weight: 500;
}

.tenant-empty-card {
  background: #F8FAFC;
  border: 1px dashed #CBD5E1;
  border-radius: 12px;
  padding: 16px;
  color: #64748B;
  font-size: 12.5px;
  text-align: center;
}

.levies-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.levy-item-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  padding: 10px 12px;
}

.levy-meta {
  display: flex;
  flex-direction: column;
}

.levy-name {
  font-size: 12.5px;
  font-weight: 600;
  color: #1E293B;
}

.levy-amt {
  font-size: 12px;
  font-weight: 700;
  color: #0A5C38;
}

.tenant-contact-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 4px;
}

.tenant-call-action-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #EBF8F2;
  color: #0A5C38;
  border: 1px solid #B6E5D0;
  border-radius: 10px;
  padding: 8px 12px;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: background 0.15s ease;
}

.tenant-call-action-btn:active {
  background: #D3F1E3;
}

.tenant-wa-action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #F0FDF4;
  color: #16A34A;
  border: 1px solid #BBF7D0;
  border-radius: 10px;
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
  transition: background 0.15s ease;
}

.tenant-wa-action-btn:active {
  background: #DCFCE7;
}

.add-occ-phone-row {
  margin-top: 2px;
}

.add-occ-quick-call {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: #0A5C38;
  font-size: 11.5px;
  font-weight: 600;
  text-decoration: none;
}

.prop-sheet-actions {
  margin-top: 12px;
}

.prop-action-close-btn {
  width: 100%;
  height: 44px;
  background: #F1F5F9;
  color: #334155;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
}

.prop-action-close-btn:active {
  background: #E2E8F0;
}



</style>
