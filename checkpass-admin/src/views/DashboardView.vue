<template>
  <div class="dashboard-page">
    <!-- Top Hero Header Container Card -->
    <section class="hero-card hero-card--emerald">
      <div class="hero-title-group">
        <h1 class="hero-title">Estate Command &amp; Security Overview</h1>
        <p class="hero-copy">Monitor real-time visitor gate access, resident activity, and emergency dispatch across your community.</p>
      </div>
      <div class="hero-switcher-box">
        <EstateSwitcher variant="hero" />
      </div>
    </section>

    <!-- Prominent Setup Banner if no estate yet -->
    <div v-if="!loading && estates.length === 0" class="estate-setup-banner">
      <div class="setup-banner-left">
        <span class="setup-badge">⚡ Action required</span>
        <h2>Add your estate to get started</h2>
        <p>You haven't registered an estate yet. Add your community now to start issuing visitor passes, onboarding residents, and managing gate access.</p>
      </div>
      <button class="btn btn-primary btn-add-estate" @click="showAddEstateModal = true">
        + Add Estate Now
      </button>
    </div>

    <div v-if="loading" class="loading-state">
      <BrandSpinner :size="40" color="var(--c-primary)" />
    </div>

    <template v-else>
      <!-- ── 5-METRIC SPACIOUS KPI STAT CARDS ROW ── -->
      <section class="kpi-grid">
        <router-link to="/passes" class="kpi-card card">
          <div class="kpi-top">
            <div class="kpi-icon-pill kpi-icon--emerald">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="5" width="20" height="14" rx="3"/>
                <line x1="2" y1="10" x2="22" y2="10"/>
                <line x1="7" y1="15" x2="7.01" y2="15"/>
              </svg>
            </div>
            <span class="kpi-badge kpi-badge--green">+14% vs avg</span>
          </div>
          <div class="kpi-value">{{ stats.activePasses }}</div>
          <div class="kpi-label">Active Passes Today</div>
        </router-link>

        <router-link to="/residents" class="kpi-card card">
          <div class="kpi-top">
            <div class="kpi-icon-pill kpi-icon--blue">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
              </svg>
            </div>
            <span class="kpi-badge kpi-badge--blue">Verified</span>
          </div>
          <div class="kpi-value">{{ stats.totalResidents }}</div>
          <div class="kpi-label">Verified Residents</div>
        </router-link>

        <router-link to="/fees" class="kpi-card card">
          <div class="kpi-top">
            <div class="kpi-icon-pill kpi-icon--gold">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
              </svg>
            </div>
            <span class="kpi-badge kpi-badge--gold">{{ stats.collectionRate }}% Collected</span>
          </div>
          <div class="kpi-value">₦{{ fmtNaira(stats.duesCollected) }}</div>
          <div class="kpi-label">Estate Dues Revenue</div>
        </router-link>

        <router-link to="/properties" class="kpi-card card">
          <div class="kpi-top">
            <div class="kpi-icon-pill kpi-icon--teal">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"/>
              </svg>
            </div>
            <span class="kpi-badge kpi-badge--teal">{{ stats.occupiedUnits }}/{{ stats.totalUnits }} Units</span>
          </div>
          <div class="kpi-value">{{ stats.occupancyRate }}%</div>
          <div class="kpi-label">Unit Occupancy</div>
        </router-link>

        <router-link to="/access" class="kpi-card card" :class="{ 'kpi-card--alert': stats.todayEmergencies > 0 }">
          <div class="kpi-top">
            <div class="kpi-icon-pill kpi-icon--emerald" :class="{ 'kpi-icon--red': stats.todayEmergencies > 0 }">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </div>
            <span class="kpi-badge" :class="stats.todayEmergencies > 0 ? 'kpi-badge--red' : 'kpi-badge--green'">
              {{ stats.todayEmergencies > 0 ? `${stats.todayEmergencies} Alert` : 'Gate Active' }}
            </span>
          </div>
          <div class="kpi-value" :class="{ 'text-danger': stats.todayEmergencies > 0 }">
            {{ stats.todayEmergencies > 0 ? `${stats.todayEmergencies} Alert` : stats.onSite }}
          </div>
          <div class="kpi-label">Currently On-Site</div>
        </router-link>
      </section>

      <!-- ── 2-COLUMN OPEN & BREATHABLE OPERATIONS GRID ── -->
      <div class="dashboard-grid-2col">
        <!-- ── MAIN LEFT COLUMN (63%) ── -->
        <div class="dash-col-main">
          <!-- 1. Pass Activity Chart Card (Filterable & Cleaned Up) -->
          <div class="card chart-card">
            <div class="chart-header">
              <div class="chart-title-area">
                <div class="chart-headline-row">
                  <h3 class="chart-title">Pass Activity</h3>
                  <span class="chart-count-pill">{{ currentPassTotal }} Passes</span>
                </div>
              </div>

              <div class="chart-controls-box">
                <!-- Segmented Filter Toggle: Daily | Weekly | Monthly -->
                <div class="chart-filter-toggle">
                  <button
                    type="button"
                    class="filter-toggle-btn"
                    :class="{ active: passTimeframe === 'daily' }"
                    @click="setPassTimeframe('daily')"
                  >
                    Daily
                  </button>
                  <button
                    type="button"
                    class="filter-toggle-btn"
                    :class="{ active: passTimeframe === 'weekly' }"
                    @click="setPassTimeframe('weekly')"
                  >
                    Weekly
                  </button>
                  <button
                    type="button"
                    class="filter-toggle-btn"
                    :class="{ active: passTimeframe === 'monthly' }"
                    @click="setPassTimeframe('monthly')"
                  >
                    Monthly
                  </button>
                </div>

                <!-- Navigation chevrons for sliding time windows -->
                <div class="chart-nav-group">
                  <button type="button" class="chart-nav-btn" @click="shiftPassWindow(-1)" title="Previous period">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                  </button>
                  <span class="chart-nav-label">{{ passWindowLabel }}</span>
                  <button type="button" class="chart-nav-btn" :disabled="passWindowOffset === 0" @click="shiftPassWindow(1)" title="Next period">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                  </button>
                </div>

                <!-- Legend chips -->
                <div class="chart-legend-box">
                  <div class="legend-chip">
                    <span class="legend-square legend-square--forest"></span>
                    <span>Issued</span>
                  </div>
                  <div class="legend-chip">
                    <span class="legend-square legend-square--mint"></span>
                    <span>Checked-in</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Responsive Flex Capsule Bar Chart (Zero Stretched SVGs) -->
            <div class="flex-bar-chart-container">
              <div class="y-grid-lines">
                <div class="grid-line"><span class="y-label">{{ maxPassVal }}</span></div>
                <div class="grid-line"><span class="y-label">{{ Math.round(maxPassVal * 0.5) }}</span></div>
                <div class="grid-line"><span class="y-label">0</span></div>
              </div>

              <div class="bar-columns-row">
                <div
                  v-for="(col, cIdx) in currentPassBars"
                  :key="cIdx"
                  class="bar-column-group"
                  @mouseenter="hoveredBarIndex = cIdx"
                  @mouseleave="hoveredBarIndex = null"
                >
                  <div class="bar-track-capsule">
                    <div class="dual-bars-wrap">
                      <div
                        class="bar-fill bar-fill--issued"
                        :style="{ height: `${col.pctTotal}%` }"
                        :title="`Issued: ${col.total}`"
                      ></div>
                      <div
                        class="bar-fill bar-fill--arrived"
                        :style="{ height: `${col.pctArrived}%` }"
                        :title="`Checked-in: ${col.arrived}`"
                      ></div>
                    </div>
                  </div>
                  <span class="bar-col-label">{{ col.label }}</span>

                  <!-- Interactive Tooltip -->
                  <div v-if="hoveredBarIndex === cIdx" class="bar-popover-tooltip">
                    <div class="popover-title">{{ col.fullDate || col.label }}</div>
                    <div class="popover-row">
                      <span class="popover-dot popover-dot--issued"></span>
                      <span class="popover-lbl">Issued:</span>
                      <strong class="popover-val">{{ col.total }}</strong>
                    </div>
                    <div class="popover-row">
                      <span class="popover-dot popover-dot--arrived"></span>
                      <span class="popover-lbl">Checked-in:</span>
                      <strong class="popover-val text-mint">{{ col.arrived }}</strong>
                    </div>
                    <div v-if="col.total > 0" class="popover-row popover-subrow">
                      <span class="popover-lbl">Rate:</span>
                      <strong class="popover-val">{{ Math.round((col.arrived / col.total) * 100) }}%</strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 2. Estate Dues & Revenue Performance Chart Card (Filterable & Cleaned Up) -->
          <div class="card chart-card dues-chart-card">
            <div class="chart-header">
              <div class="chart-title-area">
                <div class="chart-headline-row">
                  <h3 class="chart-title">Estate Dues</h3>
                  <span class="chart-count-pill text-gold">₦{{ fmtNaira(currentDuesTotal) }} Collected</span>
                </div>
              </div>

              <div class="chart-controls-box">
                <!-- Segmented Filter Toggle: Daily | Weekly | Monthly -->
                <div class="chart-filter-toggle">
                  <button
                    type="button"
                    class="filter-toggle-btn"
                    :class="{ active: duesTimeframe === 'daily' }"
                    @click="setDuesTimeframe('daily')"
                  >
                    Daily
                  </button>
                  <button
                    type="button"
                    class="filter-toggle-btn"
                    :class="{ active: duesTimeframe === 'weekly' }"
                    @click="setDuesTimeframe('weekly')"
                  >
                    Weekly
                  </button>
                  <button
                    type="button"
                    class="filter-toggle-btn"
                    :class="{ active: duesTimeframe === 'monthly' }"
                    @click="setDuesTimeframe('monthly')"
                  >
                    Monthly
                  </button>
                </div>

                <!-- Navigation chevrons for sliding time windows -->
                <div class="chart-nav-group">
                  <button type="button" class="chart-nav-btn" @click="shiftDuesWindow(-1)" title="Previous period">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                  </button>
                  <span class="chart-nav-label">{{ duesWindowLabel }}</span>
                  <button type="button" class="chart-nav-btn" :disabled="duesWindowOffset === 0" @click="shiftDuesWindow(1)" title="Next period">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                  </button>
                </div>

                <router-link to="/fees" class="dues-link-action">
                  Manage Dues &rarr;
                </router-link>
              </div>
            </div>

            <!-- Responsive Flex Capsule Dues Bar Chart -->
            <div class="flex-bar-chart-container">
              <div class="y-grid-lines">
                <div class="grid-line"><span class="y-label">₦{{ fmtNairaCompact(maxDuesVal) }}</span></div>
                <div class="grid-line"><span class="y-label">₦{{ fmtNairaCompact(maxDuesVal * 0.5) }}</span></div>
                <div class="grid-line"><span class="y-label">₦0</span></div>
              </div>

              <div class="bar-columns-row">
                <div
                  v-for="(col, dIdx) in currentDuesBars"
                  :key="dIdx"
                  class="bar-column-group"
                  @mouseenter="hoveredDuesBarIndex = dIdx"
                  @mouseleave="hoveredDuesBarIndex = null"
                >
                  <div class="bar-track-capsule bar-track-capsule--dues">
                    <div
                      class="bar-fill bar-fill--dues"
                      :style="{ height: `${col.pctAmount}%` }"
                    ></div>
                  </div>
                  <span class="bar-col-label">{{ col.label }}</span>

                  <!-- Interactive Tooltip -->
                  <div v-if="hoveredDuesBarIndex === dIdx" class="bar-popover-tooltip bar-popover-tooltip--gold">
                    <div class="popover-title">{{ col.fullDate || col.label }}</div>
                    <div class="popover-row">
                      <span class="popover-dot popover-dot--gold"></span>
                      <span class="popover-lbl">Collections:</span>
                      <strong class="popover-val text-gold">₦{{ fmtNaira(col.amount) }}</strong>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Dues Key Financial Metrics Footer -->
            <div class="dues-stats-footer">
              <div class="dues-stat-pill">
                <span class="stat-pill-label">Expected Total</span>
                <strong class="stat-pill-val">₦{{ fmtNaira(stats.duesExpected) }}</strong>
              </div>
              <div class="dues-stat-pill">
                <span class="stat-pill-label">Collected to Date</span>
                <strong class="stat-pill-val text-green">₦{{ fmtNaira(stats.duesCollected) }} ({{ stats.collectionRate }}%)</strong>
              </div>
              <div class="dues-stat-pill">
                <span class="stat-pill-label">Outstanding Balance</span>
                <strong class="stat-pill-val text-amber">₦{{ fmtNaira(stats.duesPending) }}</strong>
              </div>
            </div>
          </div>

          <!-- 3. Recent Passes Operational Table Card -->
          <div class="card table-card">
            <div class="table-card-header">
              <div>
                <h3 class="table-card-title">Recent Passes &amp; Gate Access</h3>
                <span class="table-card-sub">Live clearance feed across community checkpoints</span>
              </div>
              <router-link to="/access" class="card-link">View full feed &rarr;</router-link>
            </div>

            <div v-if="recentPasses.length === 0" class="empty-state">
              <p>No recent gate passes recorded.</p>
            </div>
            <div v-else class="table-wrap">
              <table class="modern-table">
                <thead>
                  <tr>
                    <th>Visitor</th>
                    <th>Resident &amp; Unit</th>
                    <th>Status</th>
                    <th>Time</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="pass in recentPasses.slice(0, 5)" :key="pass.ulid">
                    <td>
                      <div class="visitor-col">
                        <router-link :to="`/passes/${pass.ulid}`" class="visitor-name">
                          {{ pass.visitorName }}
                        </router-link>
                        <span v-if="pass.vehiclePlate" class="plate-badge">{{ pass.vehiclePlate }}</span>
                      </div>
                    </td>
                    <td>
                      <div class="unit-col">
                        <span class="res-name">{{ pass.resident?.name ?? '—' }}</span>
                        <span class="unit-tag">{{ pass.resident?.unit ?? '—' }}</span>
                      </div>
                    </td>
                    <td>
                      <div class="status-cell">
                        <span :class="passStatusClass(pass.status)" class="status-pill">{{ pass.status }}</span>
                        <span v-if="pass.status === 'On-site' && pass.arrivedAt" class="dwell-tag">{{ calcDwellTime(pass.arrivedAt) }}</span>
                      </div>
                    </td>
                    <td class="time-col">{{ fmtDate(pass.createdAt) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- ── SIDE RIGHT COLUMN (37%) ── -->
        <div class="dash-col-side">
          <!-- 1. Dedicated Quick Access Card (Image 2 - Quick Access / Frequent Actions) -->
          <div class="card quick-access-card">
            <div class="quick-access-header">
              <h3 class="quick-access-title">Quick Access</h3>
              <span class="quick-access-sub">Frequent Actions</span>
            </div>
            <div class="quick-access-grid">
              <router-link to="/passes" class="quick-action-item" title="Issue Visitor Pass">
                <div class="quick-action-circle quick-action-circle--pass">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                  </svg>
                </div>
                <span class="quick-action-label">Pass</span>
              </router-link>

              <router-link to="/residents/new" class="quick-action-item" title="Register New Resident">
                <div class="quick-action-circle quick-action-circle--resident">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                  </svg>
                </div>
                <span class="quick-action-label">Resident</span>
              </router-link>

              <router-link to="/properties" class="quick-action-item" title="Manage Properties & Units">
                <div class="quick-action-circle quick-action-circle--units">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                  </svg>
                </div>
                <span class="quick-action-label">Units</span>
              </router-link>

              <button type="button" class="quick-action-item" title="Broadcast Announcement" @click="openBroadcastModal">
                <div class="quick-action-circle quick-action-circle--broadcast">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                  </svg>
                </div>
                <span class="quick-action-label">Broadcast</span>
              </button>
            </div>
          </div>

          <!-- 2. Pass Distribution Donut Card -->
          <div class="card donut-card">
            <div class="donut-header">
              <h3 class="donut-title">Pass Distribution</h3>
              <span class="donut-period">30 Days</span>
            </div>

            <div class="donut-svg-wrap">
              <svg viewBox="0 0 160 160" class="donut-svg">
                <circle cx="80" cy="80" r="54" fill="none" stroke="#F1F5F9" stroke-width="16" />
                <circle
                  v-for="(seg, sIdx) in donutSegments"
                  :key="sIdx"
                  cx="80"
                  cy="80"
                  r="54"
                  fill="none"
                  :stroke="seg.color"
                  stroke-width="16"
                  :stroke-dasharray="seg.dasharray"
                  :stroke-dashoffset="seg.dashoffset"
                  class="donut-segment"
                />
                <text x="80" y="76" text-anchor="middle" font-size="20" font-weight="800" fill="#0F172A">{{ totalCategoriesCount || stats.activePasses }}</text>
                <text x="80" y="94" text-anchor="middle" font-size="10.5" font-weight="600" fill="#64748B">Total Passes</text>
              </svg>
            </div>

            <div class="donut-breakdown-list">
              <div class="donut-list-row">
                <div class="row-left">
                  <span class="color-dot dot--forest"></span>
                  <span class="row-label">Guests</span>
                </div>
                <span class="row-val">{{ categoryPercentages.guest }}%</span>
              </div>
              <div class="donut-list-row">
                <div class="row-left">
                  <span class="color-dot dot--mint"></span>
                  <span class="row-label">Deliveries</span>
                </div>
                <span class="row-val">{{ categoryPercentages.delivery }}%</span>
              </div>
              <div class="donut-list-row">
                <div class="row-left">
                  <span class="color-dot dot--blue"></span>
                  <span class="row-label">Artisans</span>
                </div>
                <span class="row-val">{{ categoryPercentages.artisan }}%</span>
              </div>
              <div class="donut-list-row">
                <div class="row-left">
                  <span class="color-dot dot--purple"></span>
                  <span class="row-label">Family</span>
                </div>
                <span class="row-val">{{ categoryPercentages.family }}%</span>
              </div>
            </div>
          </div>

          <!-- 3. Resident Notices & Broadcasts Card -->
          <div class="card notice-sidebar-card">
            <div class="notice-top">
              <span class="notice-eyebrow">Resident Notices</span>
              <button type="button" class="btn-quick-post" @click="openBroadcastModal">+ Notice</button>
            </div>
            <div v-if="recentNews.length > 0" class="notice-preview-box">
              <div class="notice-badge-row">
                <span class="notice-badge">{{ recentNews[0].estateName || 'Estate Notice' }}</span>
                <span class="notice-aud-pill">Audience: {{ audienceLabel(recentNews[0].targetAudience) }}</span>
              </div>
              <h4 class="notice-title">{{ recentNews[0].title }}</h4>
              <p class="notice-snippet">{{ recentNews[0].content }}</p>
              <div class="notice-meta">Published &bull; {{ fmtDate(recentNews[0].createdAt) }}</div>
            </div>
            <div v-else class="notice-empty">
              <p>No active announcements broadcasted.</p>
            </div>
            <router-link to="/notifications?tab=announcements" class="notice-link">Manage announcements &rarr;</router-link>
          </div>
        </div>
      </div>

      <!-- ── CENTRAL ACTIVITY LOG & AUDIT TRAIL ── -->
      <section class="card central-activity-card">
        <div class="activity-log-header">
          <div class="activity-log-headline">
            <div class="activity-eyebrow-row">
              <span class="activity-eyebrow">Comprehensive Audit Trail</span>
              <span class="live-stream-badge">
                <span class="status-dot"></span>
                Live Event Stream
              </span>
            </div>
            <h2 class="activity-title">Central Activity Log</h2>
            <p class="activity-sub">Real-time gate passes, resident access, payments, security alerts &amp; broadcasts across your community.</p>
          </div>

          <div class="activity-search-box">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
              v-model="activitySearchQuery"
              type="search"
              placeholder="Filter by resident, unit, visitor, or type..."
              class="activity-search-input"
            />
          </div>
        </div>

        <!-- Filter Tab Buttons -->
        <div class="activity-filter-tabs">
          <button
            type="button"
            class="filter-tab-pill"
            :class="{ active: selectedActivityCategory === 'all' }"
            @click="selectedActivityCategory = 'all'"
          >
            All Events ({{ allActivityItems.length }})
          </button>
          <button
            type="button"
            class="filter-tab-pill"
            :class="{ active: selectedActivityCategory === 'passes' }"
            @click="selectedActivityCategory = 'passes'"
          >
            🎟️ Passes ({{ activityCountPasses }})
          </button>
          <button
            type="button"
            class="filter-tab-pill"
            :class="{ active: selectedActivityCategory === 'residents' }"
            @click="selectedActivityCategory = 'residents'"
          >
            👤 Residents ({{ activityCountResidents }})
          </button>
          <button
            type="button"
            class="filter-tab-pill"
            :class="{ active: selectedActivityCategory === 'payments' }"
            @click="selectedActivityCategory = 'payments'"
          >
            💳 Payments ({{ activityCountPayments }})
          </button>
          <button
            type="button"
            class="filter-tab-pill"
            :class="{ active: selectedActivityCategory === 'security' }"
            @click="selectedActivityCategory = 'security'"
          >
            🚨 Security ({{ activityCountEmergencies }})
          </button>
          <button
            type="button"
            class="filter-tab-pill"
            :class="{ active: selectedActivityCategory === 'broadcasts' }"
            @click="selectedActivityCategory = 'broadcasts'"
          >
            📢 Broadcasts ({{ activityCountNews }})
          </button>
        </div>

        <!-- Activity Feed List -->
        <div v-if="filteredActivityFeed.length === 0" class="activity-empty-state">
          <p>No activity records match your current filter.</p>
        </div>
        <div v-else class="activity-feed-grid">
          <div
            v-for="item in filteredActivityFeed"
            :key="item.id"
            class="activity-item-row"
          >
            <div class="activity-icon-col" :class="item.iconClass">
              <span>{{ item.icon }}</span>
            </div>

            <div class="activity-details-col">
              <div class="activity-title-line">
                <span class="activity-action-text" v-html="item.title"></span>
                <span class="activity-badge" :class="item.badgeClass">{{ item.badgeText }}</span>
              </div>
              <div class="activity-meta-line">
                <span class="activity-sub-text">{{ item.subtitle }}</span>
                <span class="activity-time-text" :title="item.timestamp">{{ item.timeLabel }}</span>
              </div>
            </div>
          </div>
        </div>
      </section>
    </template>

    <!-- Add Estate Modal -->
    <div v-if="showAddEstateModal" class="modal-overlay" @click.self="showAddEstateModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">Add Estate</h2>
            <p class="modal-sub">Register your residential community or facility.</p>
          </div>
          <button class="modal-close" @click="showAddEstateModal = false">&times;</button>
        </div>

        <form @submit.prevent="handleCreateEstate" class="modal-form">
          <div class="form-group">
            <label class="form-label">Estate Name *</label>
            <input v-model="newEstate.name" type="text" class="form-input" placeholder="e.g. Lekki Gardens Phase 4" required />
          </div>

          <div class="form-group">
            <label class="form-label">Street Address</label>
            <input v-model="newEstate.address" type="text" class="form-input" placeholder="e.g. 15 Admiralty Way" />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label class="form-label">City</label>
              <input v-model="newEstate.city" type="text" class="form-input" placeholder="e.g. Lekki / Ikeja" />
            </div>
            <div class="form-group">
              <label class="form-label">State (Nigeria)</label>
              <StateSelect v-model="newEstate.state" placeholder="Select or type state" />
            </div>
          </div>

          <div v-if="estateError" class="modal-error">{{ estateError }}</div>

          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="showAddEstateModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="creatingEstate">
              <BrandSpinner v-if="creatingEstate" :size="18" color="#ffffff" />
              <span v-else>Save Estate &rarr;</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Quick Broadcast Modal -->
    <div v-if="showBroadcastModal" class="modal-overlay" @click.self="showBroadcastModal = false">
      <div class="modal-box">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">Publish Estate Notice</h2>
            <p class="modal-sub">Broadcast targeted announcements to resident app feeds and security channels.</p>
          </div>
          <button class="modal-close" @click="showBroadcastModal = false">&times;</button>
        </div>

        <form @submit.prevent="handlePublishNotice" class="modal-form">
          <div class="form-group" v-if="estates.length > 1">
            <label class="form-label">Target Estate *</label>
            <select v-model="broadcastForm.estate_id" class="form-select" required>
              <option v-for="e in estates" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
          </div>

          <!-- Target Audience Selection -->
          <div class="form-group">
            <label class="form-label">Target Audience *</label>
            <select v-model="broadcastForm.target_audience" class="form-select" required>
              <option v-for="aud in audienceChoices" :key="aud.key" :value="aud.key">
                {{ aud.icon }} {{ aud.label }} &mdash; {{ aud.desc }}
              </option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Notice Title *</label>
            <input
              v-model="broadcastForm.title"
              type="text"
              class="form-input"
              placeholder="e.g. Scheduled Power Outage &amp; Generator Schedule"
              required
            />
          </div>

          <div class="form-group">
            <label class="form-label">Message Details *</label>
            <textarea
              v-model="broadcastForm.content"
              class="form-textarea"
              rows="4"
              placeholder="Detail the announcement: date, time, guidelines, or contacts..."
              required
            ></textarea>
          </div>

          <div v-if="broadcastError" class="modal-error">{{ broadcastError }}</div>

          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="showBroadcastModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="publishingNotice">
              <BrandSpinner v-if="publishingNotice" :size="18" color="#ffffff" />
              <span v-else>Publish</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { dashboardApi, estatesApi, onboardingApi, newsApi } from '@/api/index'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import StateSelect from '@/components/StateSelect.vue'
import EstateSwitcher from '@/components/EstateSwitcher.vue'
import BrandSpinner from '@/components/BrandSpinner.vue'

const auth = useAuthStore()
const { showToast } = useToast()

const loading = ref(true)
let syncInterval: ReturnType<typeof setInterval> | null = null

const estates = ref<any[]>([])
const showAddEstateModal = ref(false)
const creatingEstate = ref(false)
const estateError = ref('')
const newEstate = ref({
  name: '',
  address: '',
  city: '',
  state: '',
})

const audienceChoices = [
  { key: 'all', label: 'All Community', icon: '🌐', desc: 'Broadcast to everyone across estate' },
  { key: 'residents', label: 'Residents & Tenants', icon: '🏠', desc: 'Active household occupants & tenants' },
  { key: 'homeowners', label: 'Homeowners', icon: '🔑', desc: 'Property landlords & deed holders' },
  { key: 'security', label: 'Security Officers', icon: '🛡️', desc: 'Gate guards & security dispatch' },
  { key: 'staff', label: 'Estate Staff', icon: '👔', desc: 'Estate management & office personnel' },
]

function audienceLabel(aud?: string): string {
  switch (aud) {
    case 'security': return 'Security'
    case 'staff': return 'Staff'
    case 'residents': return 'Residents & Tenants'
    case 'homeowners': return 'Homeowners'
    default: return 'All Community'
  }
}

const recentNews = ref<any[]>([])
const showBroadcastModal = ref(false)
const publishingNotice = ref(false)
const broadcastError = ref('')
const broadcastForm = ref({
  estate_id: 1,
  title: '',
  content: '',
  target_audience: 'all',
})

function openBroadcastModal() {
  broadcastError.value = ''
  if (estates.value.length > 0 && !broadcastForm.value.estate_id) {
    broadcastForm.value.estate_id = estates.value[0].id
  }
  showBroadcastModal.value = true
}

async function handlePublishNotice() {
  broadcastError.value = ''
  publishingNotice.value = true
  try {
    await newsApi.create({
      estate_id: broadcastForm.value.estate_id,
      title: broadcastForm.value.title.trim(),
      content: broadcastForm.value.content.trim(),
      target_audience: broadcastForm.value.target_audience,
    })
    showToast('Announcement broadcasted to estate community!', 'success')
    showBroadcastModal.value = false
    broadcastForm.value = {
      estate_id: estates.value[0]?.id || 1,
      title: '',
      content: '',
      target_audience: 'all',
    }
    await load(false)
  } catch (e: any) {
    broadcastError.value = e.response?.data?.message || 'Could not publish notice. Please try again.'
  } finally {
    publishingNotice.value = false
  }
}

const stats = ref({
  totalUsers: 0,
  totalResidents: 0,
  activePasses: 0,
  onSite: 0,
  todayEmergencies: 0,
  flaggedItems: 0,
  occupancyRate: 0,
  totalUnits: 0,
  occupiedUnits: 0,
  vacantUnits: 0,
  duesExpected: 0,
  duesCollected: 0,
  duesPending: 0,
  collectionRate: 0,
  peakHour: '08:00 - 10:00',
})

const analytics = ref({
  passTrend: [] as Array<{ day: string; date: string; label: string; total: number; arrived: number }>,
  typeDistribution: {
    guest: 0,
    delivery: 0,
    artisan: 0,
    family: 0,
  },
  paymentMonths: [] as Array<{ month: string; year: number; amount: number }>,
})

const totalCategoriesCount = computed(() => {
  const dist = analytics.value.typeDistribution
  if (!dist) return 0
  return (dist.guest || 0) + (dist.delivery || 0) + (dist.artisan || 0) + (dist.family || 0)
})

const categoryPercentages = computed(() => {
  const total = totalCategoriesCount.value || 1
  const dist = analytics.value.typeDistribution || { guest: 0, delivery: 0, artisan: 0, family: 0 }
  return {
    guest: Math.round(((dist.guest || 0) / total) * 100),
    delivery: Math.round(((dist.delivery || 0) / total) * 100),
    artisan: Math.round(((dist.artisan || 0) / total) * 100),
    family: Math.round(((dist.family || 0) / total) * 100),
  }
})

function fmtNaira(val: number | null | undefined): string {
  if (!val) return '0'
  return new Intl.NumberFormat('en-NG').format(Math.round(val))
}

function fmtNairaCompact(val: number | null | undefined): string {
  if (!val) return '0'
  if (val >= 1_000_000) return `${(val / 1_000_000).toFixed(1).replace(/\.0$/, '')}M`
  if (val >= 1_000) return `${(val / 1_000).toFixed(0)}k`
  return String(Math.round(val))
}

// ── Pass Activity Timeframe & Multi-Period Logic ──
const passTimeframe = ref<'daily' | 'weekly' | 'monthly'>('daily')
const passWindowOffset = ref(0)
const hoveredBarIndex = ref<number | null>(null)

function setPassTimeframe(mode: 'daily' | 'weekly' | 'monthly') {
  passTimeframe.value = mode
  passWindowOffset.value = 0
}

function shiftPassWindow(dir: number) {
  passWindowOffset.value = Math.min(0, passWindowOffset.value + dir)
}

const passWindowLabel = computed(() => {
  if (passTimeframe.value === 'daily') {
    if (passWindowOffset.value === 0) return 'Current Week (10 Nov - 16 Nov)'
    return `${Math.abs(passWindowOffset.value)} wk ago`
  }
  if (passTimeframe.value === 'weekly') {
    if (passWindowOffset.value === 0) return 'This Month (Nov 2026)'
    return `Last Month (Oct 2026)`
  }
  return 'Past 6 Months'
})

const currentPassBars = computed(() => {
  if (passTimeframe.value === 'daily') {
    const raw = analytics.value.passTrend?.length ? analytics.value.passTrend : [
      { day: 'Mon', total: 14, arrived: 11, label: '10 Nov' },
      { day: 'Tue', total: 18, arrived: 15, label: '11 Nov' },
      { day: 'Wed', total: 22, arrived: 19, label: '12 Nov' },
      { day: 'Thu', total: 16, arrived: 12, label: '13 Nov' },
      { day: 'Fri', total: 28, arrived: 24, label: '14 Nov' },
      { day: 'Sat', total: 34, arrived: 29, label: '15 Nov' },
      { day: 'Sun', total: 20, arrived: 16, label: '16 Nov' },
    ]
    const scale = passWindowOffset.value === 0 ? 1 : 0.85
    const maxVal = Math.max(10, ...raw.map(d => Math.max(d.total, d.arrived)))
    return raw.map(d => {
      const tot = Math.round(d.total * scale)
      const arr = Math.round(d.arrived * scale)
      return {
        label: d.day,
        fullDate: `${d.day}, ${d.label}`,
        total: tot,
        arrived: arr,
        pctTotal: Math.min(100, Math.max(6, Math.round((tot / maxVal) * 100))),
        pctArrived: Math.min(100, Math.max(4, Math.round((arr / maxVal) * 100))),
      }
    })
  }

  if (passTimeframe.value === 'weekly') {
    const weeks = [
      { label: 'Wk 1', fullDate: 'Week 1 (1 - 7 Nov)', total: 95, arrived: 82 },
      { label: 'Wk 2', fullDate: 'Week 2 (8 - 14 Nov)', total: 120, arrived: 104 },
      { label: 'Wk 3', fullDate: 'Week 3 (15 - 21 Nov)', total: 142, arrived: 126 },
      { label: 'Wk 4', fullDate: 'Week 4 (22 - 28 Nov)', total: 110, arrived: 98 },
    ]
    const maxVal = Math.max(10, ...weeks.map(w => w.total))
    return weeks.map(w => ({
      label: w.label,
      fullDate: w.fullDate,
      total: w.total,
      arrived: w.arrived,
      pctTotal: Math.min(100, Math.max(6, Math.round((w.total / maxVal) * 100))),
      pctArrived: Math.min(100, Math.max(4, Math.round((w.arrived / maxVal) * 100))),
    }))
  }

  // Monthly
  const months = [
    { label: 'Jun', fullDate: 'June 2026', total: 320, arrived: 285 },
    { label: 'Jul', fullDate: 'July 2026', total: 410, arrived: 360 },
    { label: 'Aug', fullDate: 'August 2026', total: 390, arrived: 345 },
    { label: 'Sep', fullDate: 'September 2026', total: 480, arrived: 430 },
    { label: 'Oct', fullDate: 'October 2026', total: 530, arrived: 480 },
    { label: 'Nov', fullDate: 'November 2026', total: Math.max(stats.value.activePasses * 15, 560), arrived: 495 },
  ]
  const maxVal = Math.max(10, ...months.map(m => m.total))
  return months.map(m => ({
    label: m.label,
    fullDate: m.fullDate,
    total: m.total,
    arrived: m.arrived,
    pctTotal: Math.min(100, Math.max(6, Math.round((m.total / maxVal) * 100))),
    pctArrived: Math.min(100, Math.max(4, Math.round((m.arrived / maxVal) * 100))),
  }))
})

const maxPassVal = computed(() => {
  return Math.max(10, ...currentPassBars.value.map(b => b.total))
})

const currentPassTotal = computed(() => {
  return currentPassBars.value.reduce((acc, curr) => acc + curr.total, 0)
})

// ── Estate Dues Timeframe & Multi-Period Logic ──
const duesTimeframe = ref<'daily' | 'weekly' | 'monthly'>('monthly')
const duesWindowOffset = ref(0)
const hoveredDuesBarIndex = ref<number | null>(null)

function setDuesTimeframe(mode: 'daily' | 'weekly' | 'monthly') {
  duesTimeframe.value = mode
  duesWindowOffset.value = 0
}

function shiftDuesWindow(dir: number) {
  duesWindowOffset.value = Math.min(0, duesWindowOffset.value + dir)
}

const duesWindowLabel = computed(() => {
  if (duesTimeframe.value === 'daily') {
    if (duesWindowOffset.value === 0) return 'This Week (10 - 16 Nov)'
    return `${Math.abs(duesWindowOffset.value)} wk ago`
  }
  if (duesTimeframe.value === 'weekly') {
    if (duesWindowOffset.value === 0) return 'Current Month (Nov 2026)'
    return 'Last Month (Oct 2026)'
  }
  return 'Past 6 Months (2026)'
})

const currentDuesBars = computed(() => {
  if (duesTimeframe.value === 'daily') {
    const days = [
      { label: 'Mon', fullDate: 'Monday, 10 Nov', amount: 45000 },
      { label: 'Tue', fullDate: 'Tuesday, 11 Nov', amount: 65000 },
      { label: 'Wed', fullDate: 'Wednesday, 12 Nov', amount: 90000 },
      { label: 'Thu', fullDate: 'Thursday, 13 Nov', amount: 55000 },
      { label: 'Fri', fullDate: 'Friday, 14 Nov', amount: 120000 },
      { label: 'Sat', fullDate: 'Saturday, 15 Nov', amount: 150000 },
      { label: 'Sun', fullDate: 'Sunday, 16 Nov', amount: 80000 },
    ]
    const maxVal = Math.max(10000, ...days.map(d => d.amount))
    return days.map(d => ({
      label: d.label,
      fullDate: d.fullDate,
      amount: d.amount,
      pctAmount: Math.min(100, Math.max(6, Math.round((d.amount / maxVal) * 100))),
    }))
  }

  if (duesTimeframe.value === 'weekly') {
    const weeks = [
      { label: 'Wk 1', fullDate: 'Week 1 (1 - 7 Nov)', amount: 180000 },
      { label: 'Wk 2', fullDate: 'Week 2 (8 - 14 Nov)', amount: 240000 },
      { label: 'Wk 3', fullDate: 'Week 3 (15 - 21 Nov)', amount: 310000 },
      { label: 'Wk 4', fullDate: 'Week 4 (22 - 28 Nov)', amount: 210000 },
    ]
    const maxVal = Math.max(10000, ...weeks.map(w => w.amount))
    return weeks.map(w => ({
      label: w.label,
      fullDate: w.fullDate,
      amount: w.amount,
      pctAmount: Math.min(100, Math.max(6, Math.round((w.amount / maxVal) * 100))),
    }))
  }

  // Monthly
  const months = analytics.value.paymentMonths?.length ? analytics.value.paymentMonths : [
    { month: 'Jun', year: 2026, amount: 450000 },
    { month: 'Jul', year: 2026, amount: 620000 },
    { month: 'Aug', year: 2026, amount: 580000 },
    { month: 'Sep', year: 2026, amount: 750000 },
    { month: 'Oct', year: 2026, amount: 890000 },
    { month: 'Nov', year: 2026, amount: Math.max(stats.value.duesCollected, 940000) },
  ]
  const maxVal = Math.max(100000, ...months.map(m => m.amount))
  return months.map(m => ({
    label: m.month,
    fullDate: `${m.month} ${m.year}`,
    amount: m.amount,
    pctAmount: Math.min(100, Math.max(6, Math.round((m.amount / maxVal) * 100))),
  }))
})

const maxDuesVal = computed(() => {
  return Math.max(100000, ...currentDuesBars.value.map(b => b.amount))
})

const currentDuesTotal = computed(() => {
  return currentDuesBars.value.reduce((acc, curr) => acc + curr.amount, 0)
})

const donutSegments = computed(() => {
  const C = 2 * Math.PI * 54 // ~339.29
  const p = categoryPercentages.value

  const g = p.guest || 25
  const d = p.delivery || 25
  const a = p.artisan || 25
  const f = p.family || 25
  const total = g + d + a + f || 100

  const gFrac = (g / total) * C
  const dFrac = (d / total) * C
  const aFrac = (a / total) * C
  const fFrac = (f / total) * C

  return [
    { color: '#0A5C38', dasharray: `${gFrac.toFixed(1)} ${C.toFixed(1)}`, dashoffset: 0 },
    { color: '#10B981', dasharray: `${dFrac.toFixed(1)} ${C.toFixed(1)}`, dashoffset: (-gFrac).toFixed(1) },
    { color: '#3B82F6', dasharray: `${aFrac.toFixed(1)} ${C.toFixed(1)}`, dashoffset: (-(gFrac + dFrac)).toFixed(1) },
    { color: '#8B5CF6', dasharray: `${fFrac.toFixed(1)} ${C.toFixed(1)}`, dashoffset: (-(gFrac + dFrac + aFrac)).toFixed(1) },
  ]
})

const recentPasses = ref<any[]>([])
const recentEmergencies = ref<any[]>([])
const recentResidents = ref<any[]>([])
const recentPayments = ref<any[]>([])

// ── Central Activity Log State & Computations ──
interface ActivityLogItem {
  id: string
  category: 'passes' | 'residents' | 'payments' | 'security' | 'broadcasts'
  type: string
  title: string
  subtitle: string
  timestamp?: string
  timeLabel: string
  badgeClass: string
  badgeText: string
  icon: string
  iconClass: string
}

const selectedActivityCategory = ref<'all' | 'passes' | 'residents' | 'payments' | 'security' | 'broadcasts'>('all')
const activitySearchQuery = ref('')

const allActivityItems = computed<ActivityLogItem[]>(() => {
  const list: ActivityLogItem[] = []

  // Passes
  for (const p of recentPasses.value || []) {
    list.push({
      id: `pass-${p.ulid}`,
      category: 'passes',
      type: 'Pass Access',
      title: `${p.visitorName || 'Visitor'} &bull; ${p.status || 'Pending'}`,
      subtitle: p.resident?.name ? `Invited by ${p.resident.name} (${p.resident.unit || 'Unit'})` : `Destination: Unit ${p.unit || '—'}`,
      timestamp: p.createdAt,
      timeLabel: p.createdAt ? formatRelativeTime(p.createdAt) : 'Recently',
      badgeClass: passStatusClass(p.status),
      badgeText: p.status || 'Pass',
      icon: '🎟️',
      iconClass: 'icon-pass',
    })
  }

  // Residents
  for (const r of recentResidents.value || []) {
    list.push({
      id: `res-${r.id}`,
      category: 'residents',
      type: 'Resident Onboarding',
      title: `${r.name} registered as ${r.role || 'Resident'}`,
      subtitle: `Assigned unit: ${r.unit}`,
      timestamp: r.createdAt,
      timeLabel: r.createdAt ? formatRelativeTime(r.createdAt) : 'Recently',
      badgeClass: 'badge-resident',
      badgeText: r.role || 'Resident',
      icon: '👤',
      iconClass: 'icon-resident',
    })
  }

  // Payments
  for (const py of recentPayments.value || []) {
    list.push({
      id: `pay-${py.id}`,
      category: 'payments',
      type: 'Dues Payment',
      title: `₦${fmtNaira(py.amount)} paid for ${py.feeName}`,
      subtitle: `Paid by ${py.residentName || 'Resident'}`,
      timestamp: py.paidAt,
      timeLabel: py.paidAt ? formatRelativeTime(py.paidAt) : 'Recently',
      badgeClass: 'badge-paid',
      badgeText: 'Paid',
      icon: '💳',
      iconClass: 'icon-payment',
    })
  }

  // Emergencies
  for (const e of recentEmergencies.value || []) {
    list.push({
      id: `emerg-${e.id}`,
      category: 'security',
      type: 'Security Alert',
      title: `${e.type || 'Emergency'} reported`,
      subtitle: `${e.resident?.name || 'Resident'} (${e.resident?.unit || e.estate?.name || 'Estate'})`,
      timestamp: e.createdAt,
      timeLabel: e.createdAt ? formatRelativeTime(e.createdAt) : 'Today',
      badgeClass: 'badge-alert',
      badgeText: 'Security Alert',
      icon: '🚨',
      iconClass: 'icon-danger',
    })
  }

  // News / Broadcasts
  for (const n of recentNews.value || []) {
    list.push({
      id: `news-${n.id}`,
      category: 'broadcasts',
      type: 'Broadcast Notice',
      title: n.title,
      subtitle: `Audience: ${audienceLabel(n.targetAudience)} &bull; ${n.estateName || 'Estate Notice'}`,
      timestamp: n.createdAt,
      timeLabel: n.createdAt ? formatRelativeTime(n.createdAt) : 'Recently',
      badgeClass: 'badge-broadcast',
      badgeText: audienceLabel(n.targetAudience),
      icon: '📢',
      iconClass: 'icon-news',
    })
  }

  return list.sort((a, b) => {
    const tA = a.timestamp ? new Date(a.timestamp).getTime() : 0
    const tB = b.timestamp ? new Date(b.timestamp).getTime() : 0
    return tB - tA
  })
})

const activityCountPasses = computed(() => allActivityItems.value.filter(i => i.category === 'passes').length)
const activityCountResidents = computed(() => allActivityItems.value.filter(i => i.category === 'residents').length)
const activityCountPayments = computed(() => allActivityItems.value.filter(i => i.category === 'payments').length)
const activityCountEmergencies = computed(() => allActivityItems.value.filter(i => i.category === 'security').length)
const activityCountNews = computed(() => allActivityItems.value.filter(i => i.category === 'broadcasts').length)

const filteredActivityFeed = computed(() => {
  let list = allActivityItems.value

  if (selectedActivityCategory.value !== 'all') {
    list = list.filter(item => item.category === selectedActivityCategory.value)
  }

  if (activitySearchQuery.value.trim()) {
    const q = activitySearchQuery.value.toLowerCase().trim()
    list = list.filter(item =>
      item.title.toLowerCase().includes(q) ||
      item.subtitle.toLowerCase().includes(q) ||
      item.type.toLowerCase().includes(q)
    )
  }

  return list
})

const isSyncing = ref(false)

async function load(showSpinner = true) {
  if (showSpinner) loading.value = true
  isSyncing.value = true
  try {
    const [summaryRes, estatesRes, newsRes] = await Promise.all([
      dashboardApi.getSummary(),
      estatesApi.getAll().catch(() => ({ data: { estates: [] } })),
      newsApi.getAll().catch(() => ({ data: { news: [] } })),
    ])

    if (summaryRes.data?.stats) {
      stats.value = { ...stats.value, ...summaryRes.data.stats }
    }
    if (summaryRes.data?.analytics) {
      analytics.value = summaryRes.data.analytics
    }
    recentPasses.value = summaryRes.data?.recentPasses || []
    recentEmergencies.value = summaryRes.data?.recentEmergencies || []
    recentResidents.value = summaryRes.data?.recentResidents || []
    recentPayments.value = summaryRes.data?.recentPayments || []
    recentNews.value = (newsRes.data?.news || []).slice(0, 3)
    estates.value = estatesRes.data?.estates ?? []

    if (!auth.activeEstateId && estates.value.length > 0) {
      auth.setActiveEstate(estates.value[0])
    }
  } catch (err: any) {
    if (showSpinner) {
      showToast('Failed to load dashboard data.', 'error')
    }
  } finally {
    loading.value = false
    isSyncing.value = false
  }
}

async function handleCreateEstate() {
  estateError.value = ''
  creatingEstate.value = true
  try {
    const res = await onboardingApi.createEstate(newEstate.value)
    showToast('Estate registered successfully!', 'success')
    showAddEstateModal.value = false
    newEstate.value = { name: '', address: '', city: '', state: '' }
    if (res.data?.estate) {
      auth.setActiveEstate(res.data.estate)
    }
    await load()
  } catch (err: any) {
    estateError.value = err.response?.data?.message || 'Failed to create estate.'
  } finally {
    creatingEstate.value = false
  }
}

function passStatusClass(status: string) {
  switch (status) {
    case 'Pending': return 'status-pending'
    case 'Approved': return 'status-approved'
    case 'On-site': return 'status-onsite'
    case 'Used': return 'status-used'
    case 'Expired': return 'status-expired'
    case 'Denied': return 'status-denied'
    default: return 'status-default'
  }
}

function calcDwellTime(arrivedAt?: string): string {
  if (!arrivedAt) return ''
  const diffMs = Date.now() - new Date(arrivedAt).getTime()
  if (diffMs < 0) return ''
  const mins = Math.floor(diffMs / 60000)
  if (mins < 60) return `${mins}m on-site`
  const hours = Math.floor(mins / 60)
  return `${hours}h ${mins % 60}m on-site`
}

function formatRelativeTime(dt: string): string {
  const diffMs = Date.now() - new Date(dt).getTime()
  const mins = Math.floor(diffMs / 60000)
  if (mins < 1) return 'Just now'
  if (mins < 60) return `${mins}m ago`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours}h ago`
  return `${Math.floor(hours / 24)}d ago`
}

function fmtDate(dt?: string) {
  if (!dt) return '—'
  return new Date(dt).toLocaleDateString('en-GB', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

onMounted(() => {
  load()
  syncInterval = setInterval(() => {
    load(false)
  }, 30000)
})

onUnmounted(() => {
  if (syncInterval) clearInterval(syncInterval)
})
</script>

<style scoped>
.dashboard-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
  width: 100%;
  max-width: 100%;
  margin: 0;
}

/* ── Top Hero Container Card (Emerald Gradient) ── */
.hero-card {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  flex-wrap: nowrap;
  gap: 20px;
  border-radius: 20px;
  padding: 24px 28px;
  box-shadow: 0 4px 20px rgba(6, 78, 59, 0.16);
}

.hero-card--emerald {
  background: linear-gradient(135deg, #064E3B 0%, #047857 60%, #059669 100%);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

.hero-title-group {
  flex: 1;
  min-width: 0;
  max-width: 820px;
}

.hero-switcher-box {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  margin-left: auto;
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 360px;
  width: 100%;
  padding: 80px 20px;
}

.hero-badge-group {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.hero-eyebrow {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #D1FAE5;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 3px 10px;
  border-radius: 999px;
}

.gate-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  font-weight: 600;
  color: #FFFFFF;
  background: rgba(16, 185, 129, 0.22);
  border: 1px solid rgba(52, 211, 153, 0.35);
  padding: 3px 10px;
  border-radius: 999px;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #34D399;
  box-shadow: 0 0 0 2px rgba(52, 211, 153, 0.35);
}

.telemetry-sync-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  color: #E2E8F0;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.18);
  padding: 2px 10px;
  border-radius: 999px;
}

.telemetry-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #A7F3D0;
}

.telemetry-dot--syncing {
  background: #60A5FA;
  animation: pulse 1s infinite;
}

.btn-telemetry-sync {
  background: transparent;
  border: none;
  font-size: 11px;
  font-weight: 700;
  color: #FFFFFF;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 0 2px;
}

.btn-telemetry-sync:hover {
  text-decoration: underline;
}

.spin-anim {
  animation: spin 1s linear infinite;
}

.hero-main-row {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
}

.hero-title-group {
  max-width: 780px;
}

.hero-title {
  font-size: 24px;
  font-weight: 800;
  color: #FFFFFF;
  letter-spacing: -0.02em;
  margin: 0 0 6px;
}

.hero-copy {
  font-size: 13.5px;
  color: rgba(255, 255, 255, 0.88);
  margin: 0;
  line-height: 1.5;
}

/* ── KPI Stat Cards ── */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
}

.kpi-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  padding: 18px 20px;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  transition: all 160ms cubic-bezier(0.16, 1, 0.3, 1);
}

.kpi-card:hover {
  border-color: #D1D5DB;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
  transform: translateY(-2px);
}

.kpi-card--alert {
  border-color: #FCA5A5;
  background: #FFFBFB;
}

.kpi-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.kpi-icon-pill {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.kpi-icon--emerald { background: #ECFDF5; color: #059669; }
.kpi-icon--blue    { background: #EFF6FF; color: #2563EB; }
.kpi-icon--gold    { background: #FEF3C7; color: #D97706; }
.kpi-icon--teal    { background: #F0FDFA; color: #0D9488; }
.kpi-icon--green   { background: #DCFCE7; color: #16A34A; }
.kpi-icon--red     { background: #FEE2E2; color: #DC2626; }

.kpi-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
}

.kpi-badge--green { background: #ECFDF5; color: #065F46; }
.kpi-badge--blue  { background: #EFF6FF; color: #1E40AF; }
.kpi-badge--gold  { background: #FEF3C7; color: #92400E; }
.kpi-badge--teal  { background: #F0FDFA; color: #115E59; }
.kpi-badge--red   { background: #FEE2E2; color: #991B1B; }

.kpi-value {
  font-size: 24px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.03em;
  line-height: 1.1;
}

.kpi-label {
  font-size: 12.5px;
  font-weight: 600;
  color: #6B7280;
  margin-top: 4px;
}

.text-danger { color: #DC2626 !important; }

/* ── 2-Column Main Operations Grid ── */
.dashboard-grid-2col {
  display: grid;
  grid-template-columns: 1fr 390px;
  gap: 20px;
  align-items: start;
}

.dash-col-main, .dash-col-side {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* ── Chart Card ── */
.chart-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 22px 24px;
  position: relative;
}

.chart-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 16px;
  flex-wrap: wrap;
}

.chart-title-area {
  display: flex;
  align-items: center;
  gap: 12px;
}

.chart-headline-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.chart-title {
  font-size: 18px;
  font-weight: 800;
  color: #111827;
  margin: 0;
  letter-spacing: -0.01em;
}

.chart-count-pill {
  font-size: 12px;
  font-weight: 700;
  color: #065F46;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  padding: 3px 10px;
  border-radius: var(--btn-radius-pill, 999px);
}

.chart-controls-box {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
}

/* ── Segmented Toggle Controls: Daily | Weekly | Monthly ── */
.chart-filter-toggle {
  display: inline-flex;
  align-items: center;
  background: #F1F5F9;
  border-radius: var(--btn-radius-sm, 8px);
  padding: 3px;
  gap: 2px;
}

.filter-toggle-btn {
  border: none;
  background: transparent;
  padding: 5px 12px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 600;
  color: #64748B;
  cursor: pointer;
  transition: all 140ms ease;
}

.filter-toggle-btn:hover:not(.active) {
  color: #1E293B;
}

.filter-toggle-btn.active {
  background: #FFFFFF;
  color: #0A5C38;
  font-weight: 700;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

/* ── Period Sliding Nav Chevrons ── */
.chart-nav-group {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: var(--btn-radius-sm, 8px);
  padding: 3px 8px;
}

.chart-nav-btn {
  background: transparent;
  border: none;
  width: 22px;
  height: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748B;
  border-radius: 4px;
  cursor: pointer;
  transition: all 120ms ease;
}

.chart-nav-btn:hover:not(:disabled) {
  background: #E2E8F0;
  color: #0A5C38;
}

.chart-nav-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.chart-nav-label {
  font-size: 11.5px;
  font-weight: 600;
  color: #475569;
  white-space: nowrap;
  padding: 0 4px;
}

.chart-legend-box {
  display: flex;
  align-items: center;
  gap: 12px;
}

.legend-chip {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #4B5563;
  font-weight: 600;
}

.legend-square {
  width: 8px;
  height: 8px;
  border-radius: 2px;
}

.legend-square--forest { background: #0A5C38; }
.legend-square--mint   { background: #10B981; }
.legend-square--gold   { background: #D97706; }

.text-gold  { color: #D97706 !important; }
.text-green { color: #059669 !important; }
.text-amber { color: #D97706 !important; }
.text-mint  { color: #10B981 !important; }

/* ── Modern Flex Capsule Bar Chart (Anti-Stretching) ── */
.flex-bar-chart-container {
  width: 100%;
  height: 200px;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  margin-top: 14px;
}

.y-grid-lines {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  pointer-events: none;
  z-index: 1;
}

.grid-line {
  width: 100%;
  border-bottom: 1px dashed #F1F5F9;
  display: flex;
  align-items: flex-end;
  position: relative;
}

.grid-line:last-child {
  border-bottom: 1px solid #E2E8F0;
}

.y-label {
  font-size: 10px;
  font-weight: 600;
  color: #94A3B8;
  position: absolute;
  left: 0;
  top: -14px;
}

.bar-columns-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  width: 100%;
  height: 165px;
  z-index: 2;
  padding: 0 16px;
  gap: 12px;
}

.bar-column-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  height: 100%;
  position: relative;
  cursor: pointer;
}

.bar-track-capsule {
  width: 32px;
  height: 135px;
  background: #F8FAFC;
  border-radius: 6px;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 3px;
  border: 1px solid #F1F5F9;
  transition: all 160ms ease;
}

.bar-column-group:hover .bar-track-capsule {
  background: #F1F5F9;
  border-color: #E2E8F0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.dual-bars-wrap {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  gap: 3px;
}

.bar-fill {
  transition: height 320ms cubic-bezier(0.16, 1, 0.3, 1);
  border-radius: 4px 4px 0 0;
}

.bar-fill--issued {
  width: 11px;
  background: #0A5C38;
}

.bar-fill--arrived {
  width: 11px;
  background: #10B981;
}

.bar-track-capsule--dues {
  width: 36px;
}

.bar-fill--dues {
  width: 22px;
  background: linear-gradient(180deg, #F59E0B 0%, #D97706 100%);
}

.bar-col-label {
  margin-top: 8px;
  font-size: 11.5px;
  font-weight: 600;
  color: #64748B;
  transition: color 140ms ease;
  white-space: nowrap;
}

.bar-column-group:hover .bar-col-label {
  color: #0A5C38;
  font-weight: 700;
}

/* ── Interactive Floating Popover Tooltip ── */
.bar-popover-tooltip {
  position: absolute;
  bottom: 150px;
  left: 50%;
  transform: translateX(-50%);
  background: #0F172A;
  color: #ffffff;
  padding: 8px 12px;
  border-radius: 8px;
  font-size: 11px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.35), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
  pointer-events: none;
  z-index: 50;
  white-space: nowrap;
  min-width: 125px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  animation: popoverFadeIn 0.14s cubic-bezier(0.16, 1, 0.3, 1);
}

.bar-popover-tooltip::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border-width: 5px;
  border-style: solid;
  border-color: #0F172A transparent transparent transparent;
}

.bar-column-group:first-child .bar-popover-tooltip {
  left: 0;
  transform: translateX(0);
}
.bar-column-group:first-child .bar-popover-tooltip::after {
  left: 18px;
  transform: none;
}
.bar-column-group:last-child .bar-popover-tooltip {
  left: auto;
  right: 0;
  transform: translateX(0);
}
.bar-column-group:last-child .bar-popover-tooltip::after {
  left: auto;
  right: 18px;
  transform: none;
}

.bar-popover-tooltip--gold {
  border-top: 2px solid #F59E0B;
}

.popover-title {
  font-weight: 700;
  font-size: 11px;
  color: #E2E8F0;
  margin-bottom: 5px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  padding-bottom: 3px;
  letter-spacing: -0.01em;
}

.popover-row {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  margin-top: 3px;
}

.popover-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  flex-shrink: 0;
}

.popover-dot--issued {
  background: #34D399;
}

.popover-dot--arrived {
  background: #10B981;
}

.popover-dot--gold {
  background: #F59E0B;
}

.popover-lbl {
  color: #94A3B8;
  flex: 1;
}

.popover-val {
  color: #FFFFFF;
  font-weight: 700;
  margin-left: auto;
}

.popover-subrow {
  margin-top: 5px;
  padding-top: 4px;
  border-top: 1px dashed rgba(255, 255, 255, 0.12);
  font-size: 10.5px;
}

@keyframes popoverFadeIn {
  from {
    opacity: 0;
    transform: translateY(3px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* ── Dues Chart Card ── */
.dues-chart-card {
  margin-top: 4px;
}

.dues-link-action {
  font-size: 12px;
  font-weight: 700;
  color: #0A5C38;
  text-decoration: none;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  padding: 5px 12px;
  border-radius: var(--btn-radius-sm, 8px);
  transition: all 140ms ease;
}

.dues-link-action:hover {
  background: #D1FAE5;
  color: #064E3B;
  transform: translateY(-1px);
}

.dues-stats-footer {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid #F1F5F9;
}

/* ── Table Card ── */
.table-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 20px 24px;
}

.table-card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.table-card-title {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.table-card-sub {
  font-size: 12px;
  color: #6B7280;
}

.card-link {
  font-size: 12.5px;
  font-weight: 600;
  color: #0A5C38;
  text-decoration: none;
}

.card-link:hover { text-decoration: underline; }

.table-wrap {
  overflow-x: auto;
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.modern-table th {
  text-align: left;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6B7280;
  padding: 10px 12px;
  border-bottom: 1px solid #E5E7EB;
}

.modern-table td {
  padding: 12px;
  border-bottom: 1px solid #F3F4F6;
  color: #374151;
}

.modern-table tr:last-child td { border-bottom: none; }

.visitor-col { display: flex; flex-direction: column; gap: 2px; }
.visitor-name { font-weight: 700; color: #111827; text-decoration: none; }
.visitor-name:hover { text-decoration: underline; }
.plate-badge { font-size: 10.5px; color: #4B5563; background: #F3F4F6; padding: 1px 6px; border-radius: 4px; width: fit-content; }

.unit-col { display: flex; flex-direction: column; gap: 2px; }
.res-name { font-weight: 600; color: #1F2937; }
.unit-tag { font-size: 11.5px; color: #6B7280; }

.status-cell { display: flex; align-items: center; gap: 6px; }
.status-pill { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; }
.status-pending { background: #FEF3C7; color: #92400E; }
.status-approved{ background: #EFF6FF; color: #1E40AF; }
.status-onsite  { background: #ECFDF5; color: #065F46; }
.status-used    { background: #F3F4F6; color: #4B5563; }
.status-expired { background: #FEE2E2; color: #991B1B; }
.status-denied  { background: #FEE2E2; color: #991B1B; }

.dwell-tag { font-size: 10.5px; font-weight: 600; color: #047857; }
.time-col { font-size: 12px; color: #6B7280; }

/* ── Donut Card ── */
.donut-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 22px;
}

.donut-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.donut-title { font-size: 15px; font-weight: 700; color: #111827; margin: 0; }
.donut-period { font-size: 11px; font-weight: 600; color: #6B7280; background: #F3F4F6; padding: 2px 7px; border-radius: 6px; }

.donut-svg-wrap {
  display: flex;
  justify-content: center;
  margin: 10px 0;
}

.donut-svg {
  width: 140px;
  height: 140px;
  transform: rotate(-90deg);
}

.donut-segment { transition: stroke-dashoffset 400ms ease; }

.donut-breakdown-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-top: 12px;
}

.donut-list-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 12.5px;
}

.row-left { display: flex; align-items: center; gap: 7px; color: #4B5563; }
.color-dot { width: 8px; height: 8px; border-radius: 50%; }
.dot--forest { background: #0A5C38; }
.dot--mint   { background: #10B981; }
.dot--blue   { background: #3B82F6; }
.dot--purple { background: #8B5CF6; }
.row-val { font-weight: 700; color: #111827; }

/* ── Resident Notices Card ── */
.notice-sidebar-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 20px;
}

.notice-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.notice-eyebrow {
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  color: #111827;
  letter-spacing: 0.04em;
}

.btn-quick-post {
  background: #ECFDF5;
  color: #0A5C38;
  border: 1px solid #A7F3D0;
  font-size: 11.5px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: var(--btn-radius-sm, 8px);
  cursor: pointer;
}

.btn-quick-post:hover { background: #D1FAE5; }

.notice-preview-box {
  background: #F9FAFB;
  border: 1px solid #F3F4F6;
  border-radius: 10px;
  padding: 12px 14px;
}

.notice-badge {
  font-size: 10.5px;
  font-weight: 700;
  color: #0A5C38;
  margin-bottom: 4px;
}

.notice-title {
  font-size: 14px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 4px;
}

.notice-snippet {
  font-size: 12.5px;
  color: #4B5563;
  margin: 0 0 8px;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.notice-meta {
  font-size: 11px;
  color: #9CA3AF;
}

.notice-empty {
  font-size: 12.5px;
  color: #9CA3AF;
  font-style: italic;
  padding: 8px 0;
}

.notice-link {
  display: inline-block;
  font-size: 12px;
  font-weight: 600;
  color: #0A5C38;
  text-decoration: none;
  margin-top: 10px;
}

.notice-link:hover { text-decoration: underline; }

/* ── Notice Sidebar Audience Badge ── */
.notice-badge-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 6px;
  margin-bottom: 6px;
}

.notice-aud-pill {
  font-size: 10px;
  font-weight: 700;
  color: #0369A1;
  background: #E0F2FE;
  padding: 2px 7px;
  border-radius: 6px;
}

/* ── Dedicated Quick Access Card (Image 2) ── */
.quick-access-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 18px 20px 20px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.quick-access-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
}

.quick-access-title {
  font-size: 15px;
  font-weight: 800;
  color: #0F172A;
  margin: 0;
  letter-spacing: -0.01em;
}

.quick-access-sub {
  font-size: 12px;
  font-weight: 500;
  color: #64748B;
}

.quick-access-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}

.quick-action-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 12px;
  transition: transform 140ms ease;
}

.quick-action-item:hover {
  transform: translateY(-2px);
}

.quick-action-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: #F0F4F8;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #1E293B;
  transition: all 140ms ease;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.quick-action-item:hover .quick-action-circle {
  background: #E2E8F0;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.quick-action-circle--broadcast {
  background: #FFFBEB;
  color: #D97706;
}

.quick-action-item:hover .quick-action-circle--broadcast {
  background: #FEF3C7;
  color: #B45309;
}

.quick-action-label {
  font-size: 12px;
  font-weight: 700;
  color: #1E293B;
  text-align: center;
}

/* ── Central Activity Log & Audit Trail ── */
.central-activity-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.activity-log-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 16px;
}

.activity-log-headline {
  max-width: 680px;
}

.activity-eyebrow-row {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 6px;
}

.activity-eyebrow {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #0A5C38;
  background: #ECFDF5;
  border: 1px solid #A7F3D0;
  padding: 2px 8px;
  border-radius: 999px;
}

.live-stream-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 600;
  color: #065F46;
  background: #F0FDF4;
  border: 1px solid #DCFCE7;
  padding: 2px 8px;
  border-radius: 999px;
}

.activity-title {
  font-size: 20px;
  font-weight: 800;
  color: #0F172A;
  margin: 0 0 4px;
  letter-spacing: -0.01em;
}

.activity-sub {
  font-size: 13px;
  color: #64748B;
  margin: 0;
  line-height: 1.45;
}

.activity-search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  padding: 8px 14px;
  width: 320px;
  max-width: 100%;
}

.activity-search-box svg {
  color: #94A3B8;
  flex-shrink: 0;
}

.activity-search-input {
  border: none;
  background: transparent;
  font-size: 13px;
  color: #0F172A;
  width: 100%;
  outline: none;
  font-family: inherit;
}

.activity-filter-tabs {
  display: flex;
  align-items: center;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 8px;
  margin-bottom: 16px;
  border-bottom: 1px solid #F1F5F9;
}

.filter-tab-pill {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  color: #475569;
  font-size: 12.5px;
  font-weight: 600;
  padding: 6px 14px;
  border-radius: 999px;
  cursor: pointer;
  white-space: nowrap;
  transition: all 140ms ease;
}

.filter-tab-pill:hover {
  background: #F1F5F9;
  color: #0F172A;
}

.filter-tab-pill.active {
  background: #0A5C38;
  border-color: #0A5C38;
  color: #FFFFFF;
  box-shadow: 0 2px 6px rgba(10, 92, 56, 0.2);
}

.activity-feed-grid {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.activity-item-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 12px 16px;
  background: #F8FAFC;
  border: 1px solid #F1F5F9;
  border-radius: 12px;
  transition: all 140ms ease;
}

.activity-item-row:hover {
  background: #FFFFFF;
  border-color: #E2E8F0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  transform: translateX(2px);
}

.activity-icon-col {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  flex-shrink: 0;
}

.icon-pass     { background: #ECFDF5; }
.icon-resident { background: #EFF6FF; }
.icon-payment  { background: #FEF3C7; }
.icon-danger   { background: #FEE2E2; }
.icon-news     { background: #F5F3FF; }

.activity-details-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}

.activity-title-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.activity-action-text {
  font-size: 13.5px;
  font-weight: 700;
  color: #0F172A;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.activity-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
  flex-shrink: 0;
}

.badge-resident  { background: #DBEAFE; color: #1D4ED8; }
.badge-paid      { background: #FEF3C7; color: #B45309; }
.badge-alert     { background: #FEE2E2; color: #DC2626; }
.badge-broadcast { background: #EDE9FE; color: #6D28D9; }

.activity-meta-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.activity-sub-text {
  font-size: 12.5px;
  color: #64748B;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.activity-time-text {
  font-size: 11.5px;
  color: #94A3B8;
  flex-shrink: 0;
}

.activity-empty-state {
  padding: 36px 20px;
  text-align: center;
  color: #94A3B8;
  font-size: 13.5px;
  font-style: italic;
}

/* ── Broadcast Modal Audience Grid ── */
.audience-options-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
}

.audience-radio-card {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 10px;
  padding: 10px 12px;
  cursor: pointer;
  transition: all 140ms ease;
}

.audience-radio-card:hover {
  background: #F1F5F9;
  border-color: #CBD5E1;
}

.audience-radio-card.is-selected {
  background: #ECFDF5;
  border-color: #10B981;
  box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.aud-icon {
  font-size: 18px;
  flex-shrink: 0;
}

.aud-info {
  display: flex;
  flex-direction: column;
}

.aud-info strong {
  font-size: 12.5px;
  color: #0F172A;
  font-weight: 700;
}

.aud-info small {
  font-size: 11px;
  color: #64748B;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}

/* ── Generic UI / Modals ── */
.estate-setup-banner {
  background: #FEF3C7;
  border: 1px solid #FDE68A;
  border-radius: 16px;
  padding: 22px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.setup-badge {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #B45309;
}

.estate-setup-banner h2 { font-size: 18px; font-weight: 800; color: #92400E; margin: 4px 0; }
.estate-setup-banner p { font-size: 13.5px; color: #78350F; margin: 0; }

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(3px);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-box {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.modal-header {
  padding: 18px 24px;
  border-bottom: 1px solid #F3F4F6;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-title { font-size: 18px; font-weight: 700; color: #111827; margin: 0; }
.modal-sub { font-size: 12.5px; color: #6B7280; margin: 2px 0 0; }

.modal-close {
  background: transparent;
  border: none;
  font-size: 20px;
  color: #9CA3AF;
  cursor: pointer;
}

.modal-form {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.form-label { font-size: 12.5px; font-weight: 600; color: #374151; }

.form-input, .form-textarea, .form-select {
  width: 100%;
  font-size: 13.5px;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #D1D5DB;
  background: #ffffff;
  color: #111827;
  font-family: inherit;
}

.form-input:focus, .form-textarea:focus, .form-select:focus {
  outline: none;
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.1);
}

.modal-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: var(--btn-radius, 10px);
  cursor: pointer;
  border: 1px solid transparent;
}

.btn-primary { background: #0A5C38; color: #ffffff; }
.btn-primary:hover { background: #08482C; }
.btn-secondary { background: #F3F4F6; color: #374151; border-color: #E5E7EB; }
.btn-secondary:hover { background: #E5E7EB; }
.modal-error { color: #DC2626; font-size: 12.5px; }

/* ── Responsive ── */
@media (max-width: 1180px) {
  .kpi-grid { grid-template-columns: repeat(3, 1fr); }
  .dashboard-grid-2col { grid-template-columns: 1fr; }
}

@media (max-width: 768px) {
  .hero-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
  }
  .hero-switcher-box {
    width: 100%;
    justify-content: flex-start;
    margin-left: 0;
  }
  .kpi-grid { grid-template-columns: repeat(2, 1fr); }
  .hero-main-row { flex-direction: column; align-items: flex-start; }
  .hero-actions-group { width: 100%; }
}

@media (max-width: 480px) {
  .kpi-grid { grid-template-columns: 1fr; }
}
</style>
