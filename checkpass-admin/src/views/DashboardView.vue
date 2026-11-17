<template>
  <div class="dashboard-page">
    <section class="hero-card">
      <div class="hero-top-row">
        <div class="hero-title-group">
          <h1 class="hero-title">Estate Command &amp; Security Overview</h1>
          <p class="hero-copy">Monitor real-time visitor gate access, resident activity, and emergency dispatch across your community.</p>
        </div>

        <!-- Quick Access Section -->
        <div class="hero-quick-access">
          <div class="quick-access-header">
            <span class="quick-access-dot"></span>
            <span class="quick-access-title">Quick Access</span>
          </div>
          <div class="quick-access-actions">
            <router-link to="/passes" class="btn btn-hero-action">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="16" rx="2"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="12" y1="11" x2="12" y2="15"/>
                <line x1="10" y1="13" x2="14" y2="13"/>
              </svg>
              <span>Issue Pass</span>
            </router-link>
            <router-link to="/residents" class="btn btn-hero-action">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              <span>Residents</span>
            </router-link>
            <router-link to="/properties" class="btn btn-hero-action">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              <span>Properties</span>
            </router-link>
            <button type="button" class="btn btn-hero-action btn-hero-action--subtle" @click="openBroadcastModal">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
              </svg>
              <span>Broadcast</span>
            </button>
          </div>
        </div>
      </div>
      <div class="hero-switcher-row">
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
      <!-- Operational KPI Stat Cards with SVG Icons -->
      <div class="stats-grid">
        <router-link to="/users" class="stat-card">
          <div class="stat-icon stat-icon--blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.totalUsers }}</div>
            <div class="stat-label">Total Users</div>
          </div>
        </router-link>

        <router-link to="/residents" class="stat-card">
          <div class="stat-icon stat-icon--green">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.totalResidents }}</div>
            <div class="stat-label">Residents</div>
          </div>
        </router-link>

        <router-link to="/passes?status=Pending" class="stat-card">
          <div class="stat-icon stat-icon--teal">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="5" width="20" height="14" rx="3"/>
              <line x1="2" y1="10" x2="22" y2="10"/>
              <line x1="7" y1="15" x2="7.01" y2="15"/>
              <line x1="11" y1="15" x2="13" y2="15"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.activePasses }}</div>
            <div class="stat-label">Active Passes</div>
          </div>
        </router-link>

        <router-link to="/access?status=On-site" class="stat-card">
          <div class="stat-icon stat-icon--teal">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.onSite }}</div>
            <div class="stat-label">On-Site Now</div>
          </div>
        </router-link>

        <router-link to="/emergencies" class="stat-card" :class="{ 'stat-card--alert': stats.todayEmergencies > 0 }">
          <div class="stat-icon stat-icon--red">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value" :class="{ 'text-danger': stats.todayEmergencies > 0 }">{{ stats.todayEmergencies }}</div>
            <div class="stat-label">Emergencies Today</div>
          </div>
        </router-link>

        <router-link to="/access?flagged=1" class="stat-card" :class="{ 'stat-card--warning': stats.flaggedItems > 0 }">
          <div class="stat-icon stat-icon--yellow">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
              <line x1="4" y1="22" x2="4" y2="15"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ stats.flaggedItems }}</div>
            <div class="stat-label">Flagged Item Passes</div>
          </div>
        </router-link>
      </div>

      <!-- Executive Analytics & Visual Telemetry Grid -->
      <div class="analytics-row">
        <!-- Passes & Gate Traffic Influx Trend Card -->
        <div class="card analytics-card">
          <div class="card-header analytics-header">
            <div>
              <div class="header-sub">Gate Telemetry</div>
              <h2 class="card-title">Visitor Passes & Gate Influx</h2>
            </div>
            <div class="tab-pill-group">
              <button 
                type="button" 
                class="tab-pill" 
                :class="{ active: passChartTab === 'trend' }"
                @click="passChartTab = 'trend'"
              >
                7-Day Influx
              </button>
              <button 
                type="button" 
                class="tab-pill" 
                :class="{ active: passChartTab === 'breakdown' }"
                @click="passChartTab = 'breakdown'"
              >
                Categories
              </button>
            </div>
          </div>

          <div class="card-body">
            <!-- 7-Day Influx Area Chart -->
            <div v-if="passChartTab === 'trend'" class="chart-container">
              <div class="chart-legend-row">
                <div class="legend-item">
                  <span class="legend-dot legend-dot--total"></span>
                  <span class="legend-text">Passes Issued ({{ totalWeekPasses }})</span>
                </div>
                <div class="legend-item">
                  <span class="legend-dot legend-dot--arrived"></span>
                  <span class="legend-text">Checked-in At Gate ({{ totalWeekArrived }})</span>
                </div>
                <div class="chart-stat-chip">
                  <span>Avg: {{ avgDailyPasses }}/day</span>
                </div>
              </div>

              <!-- SVG Chart -->
              <div class="svg-chart-wrapper">
                <svg viewBox="0 0 500 150" class="svg-chart" preserveAspectRatio="none">
                  <defs>
                    <linearGradient id="passAreaGradient" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="#10B981" stop-opacity="0.25" />
                      <stop offset="100%" stop-color="#10B981" stop-opacity="0.0" />
                    </linearGradient>
                  </defs>

                  <!-- Horizontal grid lines -->
                  <line x1="30" y1="20" x2="485" y2="20" stroke="#F1F5F9" stroke-width="1" />
                  <line x1="30" y1="65" x2="485" y2="65" stroke="#F1F5F9" stroke-width="1" />
                  <line x1="30" y1="110" x2="485" y2="110" stroke="#F1F5F9" stroke-width="1" />
                  <line x1="30" y1="125" x2="485" y2="125" stroke="#E2E8F0" stroke-width="1.2" />

                  <!-- Filled Area for Checked-in -->
                  <path v-if="passTrendCoords.areaPath" :d="passTrendCoords.areaPath" fill="url(#passAreaGradient)" />

                  <!-- Total line (dashed soft blue) -->
                  <path v-if="passTrendCoords.totalPath" :d="passTrendCoords.totalPath" fill="none" stroke="#60A5FA" stroke-width="2" stroke-dasharray="4 3" />

                  <!-- Arrived line (solid emerald) -->
                  <path v-if="passTrendCoords.arrivedPath" :d="passTrendCoords.arrivedPath" fill="none" stroke="#059669" stroke-width="2.5" />

                  <!-- Interactive Points -->
                  <g v-for="(pt, idx) in passTrendCoords.points" :key="idx">
                    <!-- Day label -->
                    <text :x="pt.x" y="142" text-anchor="middle" font-size="11" font-weight="600" fill="#64748B">{{ pt.day }}</text>
                    
                    <!-- Total circle -->
                    <circle :cx="pt.x" :cy="pt.yTotal" r="3.5" fill="#ffffff" stroke="#3B82F6" stroke-width="2" />

                    <!-- Arrived circle -->
                    <circle 
                      :cx="pt.x" 
                      :cy="pt.yArrived" 
                      r="4.5" 
                      fill="#059669" 
                      stroke="#ffffff" 
                      stroke-width="2" 
                      class="chart-interactive-point"
                      @mouseenter="hoveredPass = pt"
                      @mouseleave="hoveredPass = null"
                    />
                  </g>
                </svg>

                <!-- Tooltip if hovered -->
                <div v-if="hoveredPass" class="chart-tooltip" :style="{ left: `${(hoveredPass.x / 500) * 100}%` }">
                  <div class="tooltip-header">{{ hoveredPass.day }} ({{ hoveredPass.label }})</div>
                  <div class="tooltip-row"><span>Passes Issued:</span> <strong>{{ hoveredPass.total }}</strong></div>
                  <div class="tooltip-row"><span>Checked-in:</span> <strong class="text-emerald">{{ hoveredPass.arrived }}</strong></div>
                </div>
              </div>
            </div>

            <!-- Category Breakdown Mode -->
            <div v-else class="category-breakdown-box">
              <div class="breakdown-headline">
                <span>30-Day Visitor Categorization</span>
                <span class="breakdown-total">{{ totalCategoriesCount }} Total Logs</span>
              </div>
              
              <!-- Multi-segment bar -->
              <div class="segmented-track">
                <div class="segment seg-guest" :style="{ width: `${categoryPercentages.guest}%` }" :title="`Guests: ${categoryPercentages.guest}%`"></div>
                <div class="segment seg-delivery" :style="{ width: `${categoryPercentages.delivery}%` }" :title="`Deliveries: ${categoryPercentages.delivery}%`"></div>
                <div class="segment seg-artisan" :style="{ width: `${categoryPercentages.artisan}%` }" :title="`Artisans: ${categoryPercentages.artisan}%`"></div>
                <div class="segment seg-family" :style="{ width: `${categoryPercentages.family}%` }" :title="`Family: ${categoryPercentages.family}%`"></div>
              </div>

              <!-- Legend grid -->
              <div class="categories-detail-grid">
                <div class="category-metric-card">
                  <div class="cat-pill cat-pill--green">👤 Guests</div>
                  <div class="cat-val">{{ analytics.typeDistribution?.guest || 0 }}</div>
                  <div class="cat-sub">{{ categoryPercentages.guest }}% of traffic</div>
                </div>
                <div class="category-metric-card">
                  <div class="cat-pill cat-pill--amber">📦 Delivery / Courier</div>
                  <div class="cat-val">{{ analytics.typeDistribution?.delivery || 0 }}</div>
                  <div class="cat-sub">{{ categoryPercentages.delivery }}% of traffic</div>
                </div>
                <div class="category-metric-card">
                  <div class="cat-pill cat-pill--blue">🔧 Artisans & Service</div>
                  <div class="cat-val">{{ analytics.typeDistribution?.artisan || 0 }}</div>
                  <div class="cat-sub">{{ categoryPercentages.artisan }}% of traffic</div>
                </div>
                <div class="category-metric-card">
                  <div class="cat-pill cat-pill--purple">👨‍👩‍👧 Family & Residents</div>
                  <div class="cat-val">{{ analytics.typeDistribution?.family || 0 }}</div>
                  <div class="cat-sub">{{ categoryPercentages.family }}% of traffic</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estate Financials & Dues Revenue Card -->
        <div class="card analytics-card">
          <div class="card-header analytics-header">
            <div>
              <div class="header-sub">Financial Health</div>
              <h2 class="card-title">Estate Dues & Revenue Performance</h2>
            </div>
            <router-link to="/fees" class="card-link">Manage dues &rarr;</router-link>
          </div>

          <div class="card-body">
            <!-- Dues Collection Rate Progress Tracker -->
            <div class="dues-progress-box">
              <div class="dues-summary-row">
                <div>
                  <span class="dues-label">Total Dues Collected</span>
                  <div class="dues-main-val">₦{{ fmtNaira(stats.duesCollected) }}</div>
                </div>
                <div class="dues-rate-badge" :class="stats.collectionRate >= 75 ? 'rate-high' : 'rate-mid'">
                  {{ stats.collectionRate }}% Collected
                </div>
              </div>

              <!-- Animated Progress Track -->
              <div class="progress-track-bg">
                <div class="progress-track-fill" :style="{ width: `${Math.min(stats.collectionRate, 100)}%` }"></div>
              </div>

              <div class="dues-submetrics">
                <span>Expected: <strong>₦{{ fmtNaira(stats.duesExpected) }}</strong></span>
                <span class="pending-text">Outstanding: <strong>₦{{ fmtNaira(stats.duesPending) }}</strong></span>
              </div>
            </div>

            <!-- 6-Month Revenue Bar Chart -->
            <div class="monthly-revenue-section">
              <div class="revenue-chart-title">6-Month Revenue Trend (₦)</div>
              
              <div class="svg-chart-wrapper">
                <svg viewBox="0 0 500 120" class="svg-chart" preserveAspectRatio="none">
                  <!-- Base line -->
                  <line x1="15" y1="95" x2="485" y2="95" stroke="#E2E8F0" stroke-width="1" />

                  <!-- Monthly Bars -->
                  <g v-for="(bar, idx) in revenueBarCoords.bars" :key="idx">
                    <!-- Bar rect -->
                    <rect 
                      :x="bar.x" 
                      :y="bar.y" 
                      :width="bar.width" 
                      :height="bar.height" 
                      rx="6" 
                      fill="url(#revenueBarGrad)"
                      class="revenue-bar"
                      @mouseenter="hoveredMonth = bar"
                      @mouseleave="hoveredMonth = null"
                    />
                    
                    <!-- Month label -->
                    <text :x="bar.labelX" :y="bar.labelY" text-anchor="middle" font-size="11" font-weight="600" fill="#64748B">
                      {{ bar.month }}
                    </text>
                  </g>

                  <defs>
                    <linearGradient id="revenueBarGrad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="#059669" />
                      <stop offset="100%" stop-color="#10B981" />
                    </linearGradient>
                  </defs>
                </svg>

                <!-- Tooltip for hovered month -->
                <div v-if="hoveredMonth" class="chart-tooltip" :style="{ left: `${(hoveredMonth.labelX / 500) * 100}%` }">
                  <div class="tooltip-header">{{ hoveredMonth.month }} {{ hoveredMonth.year }}</div>
                  <div class="tooltip-row">
                    <span>Revenue:</span> 
                    <strong class="text-emerald">₦{{ fmtNaira(hoveredMonth.amount) }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Occupancy & Command Dispatch Strip -->
      <div class="command-strip-grid">
        <!-- Unit Occupancy Status -->
        <div class="command-widget-card">
          <div class="widget-top">
            <span class="widget-tag">Topology Status</span>
            <router-link to="/properties" class="widget-action-link">View units &rarr;</router-link>
          </div>
          <div class="widget-title-row">
            <h3 class="widget-title">Unit Occupancy</h3>
            <span class="occupancy-pill">{{ stats.occupancyRate }}% Occupied</span>
          </div>
          <div class="occupancy-progress-bar">
            <div class="occupancy-fill" :style="{ width: `${Math.min(stats.occupancyRate, 100)}%` }"></div>
          </div>
          <div class="occupancy-breakdown">
            <span><strong>{{ stats.occupiedUnits }}</strong> Occupied</span>
            <span>&bull;</span>
            <span><strong>{{ stats.vacantUnits }}</strong> Vacant</span>
            <span>&bull;</span>
            <span><strong>{{ stats.totalUnits }}</strong> Total Units</span>
          </div>
        </div>

        <!-- Gate Shift & Peak Traffic Influx -->
        <div class="command-widget-card">
          <div class="widget-top">
            <span class="widget-tag">Security Intelligence</span>
            <router-link to="/access" class="widget-action-link">Audit feed &rarr;</router-link>
          </div>
          <div class="widget-title-row">
            <h3 class="widget-title">Peak Influx Window</h3>
            <span class="peak-pill">⚡ Active Window</span>
          </div>
          <div class="peak-window-display">
            <span class="peak-time">{{ stats.peakHour }}</span>
            <span class="peak-desc">Peak visitor arrival window based on gate logging patterns.</span>
          </div>
          <div class="gate-status-footer">
            <span class="status-live-dot"></span>
            <span>All checkpoints synced &bull; Shift running smoothly</span>
          </div>
        </div>
      </div>

      <!-- Real-time Operational Panels (Recent Passes & Emergencies) directly visible without redundant quick-grid -->
      <div class="dashboard-panels">
        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Recent Passes</h2>
            <router-link to="/access" class="card-link">Open access →</router-link>
          </div>
          <div v-if="recentPasses.length === 0" class="empty-state">
            <p>No passes yet.</p>
          </div>
          <div v-else class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Visitor</th>
                  <th>Resident</th>
                  <th>Unit</th>
                  <th>Status</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="pass in recentPasses" :key="pass.ulid">
                  <td>
                    <div class="visitor-meta">
                      <router-link :to="`/passes/${pass.ulid}`" class="link visitor-name">
                        {{ pass.visitorName }}
                      </router-link>
                      <!-- Subordinate vehicle & category line: never prioritized over visitor identity -->
                      <div v-if="pass.vehiclePlate || (pass.type && pass.type.toLowerCase() !== 'standard' && pass.type.toLowerCase() !== 'one-time')" class="visitor-subline">
                        <span v-if="pass.vehiclePlate" class="vehicle-subtext" title="Vehicle plate number">
                          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                            <line x1="2" y1="10" x2="22" y2="10"/>
                          </svg>
                          {{ pass.vehiclePlate }}
                        </span>
                        <span v-if="pass.type && pass.type.toLowerCase() !== 'standard' && pass.type.toLowerCase() !== 'one-time'" class="visitor-type-tag">
                          {{ pass.type }}
                        </span>
                      </div>
                    </div>
                  </td>
                  <td>{{ pass.resident?.name ?? '—' }}</td>
                  <td>{{ pass.resident?.unit ?? '—' }}</td>
                  <td>
                    <div class="status-cell">
                      <span :class="passStatusClass(pass.status)" class="badge">{{ pass.status }}</span>
                      <span v-if="pass.status === 'On-site' && pass.arrivedAt" class="dwell-time-hint" title="Time spent on estate">
                        {{ calcDwellTime(pass.arrivedAt) }}
                      </span>
                    </div>
                  </td>
                  <td>{{ fmtDate(pass.createdAt) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h2 class="card-title">Recent Emergencies</h2>
            <router-link to="/emergencies" class="card-link">View all →</router-link>
          </div>
          <div v-if="recentEmergencies.length === 0" class="empty-state">
            <p>No emergencies recorded.</p>
          </div>
          <div v-else class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Resident</th>
                  <th>Estate</th>
                  <th>Status</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="e in recentEmergencies" :key="e.id">
                  <td>{{ e.type }}</td>
                  <td>{{ e.resident?.name ?? '—' }}</td>
                  <td>{{ e.estate?.name ?? '—' }}</td>
                  <td>
                    <span :class="emergencyStatusClass(e.status)" class="badge">{{ e.status }}</span>
                  </td>
                  <td>{{ fmtDate(e.createdAt) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Estate Announcements & Resident Broadcasts Section -->
      <div class="card broadcasts-dashboard-card">
        <div class="card-header">
          <div class="header-with-badge">
            <span class="header-eyebrow-chip">Resident App Feed</span>
            <h2 class="card-title">Estate Broadcasts &amp; Community Notices</h2>
          </div>
          <div class="card-header-actions">
            <button type="button" class="btn btn-sm btn-primary btn-post-notice" @click="openBroadcastModal">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
              <span>Publish Notice</span>
            </button>
            <router-link to="/news" class="card-link">Manage full stream &rarr;</router-link>
          </div>
        </div>

        <div v-if="recentNews.length === 0" class="empty-state broadcast-empty-state">
          <div class="broadcast-empty-icon">📢</div>
          <h3>No Active Broadcasts</h3>
          <p>Publish estate announcements, security advisories, or facility maintenance alerts directly to resident mobile feeds.</p>
          <button type="button" class="btn btn-primary btn-sm" @click="openBroadcastModal">
            + Publish First Announcement
          </button>
        </div>

        <div v-else class="broadcast-feed-grid">
          <article v-for="notice in recentNews" :key="notice.id" class="broadcast-feed-card">
            <div class="feed-top">
              <span class="feed-badge">{{ notice.estateName || 'Estate Notice' }}</span>
              <span class="feed-date">{{ fmtDate(notice.createdAt) }}</span>
            </div>
            <h4 class="feed-title">{{ notice.title }}</h4>
            <p class="feed-snippet">{{ notice.content }}</p>
            <div class="feed-footer">
              <span class="feed-author">Posted by {{ notice.adminName || 'Estate Admin' }}</span>
              <span class="feed-live-status">✓ Live in Resident App</span>
            </div>
          </article>
        </div>
      </div>
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
            <p class="modal-sub">Broadcast announcement directly to all resident mobile app feeds.</p>
          </div>
          <button class="modal-close" @click="showBroadcastModal = false">&times;</button>
        </div>

        <form @submit.prevent="handlePublishNotice" class="modal-form">
          <div class="form-group" v-if="estates.length > 1">
            <label class="form-label">Target Estate *</label>
            <select v-model="broadcastForm.estate_id" class="form-input" required>
              <option v-for="e in estates" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Announcement Title *</label>
            <input
              v-model="broadcastForm.title"
              type="text"
              class="form-input"
              placeholder="e.g. Scheduled Transformer Maintenance This Saturday"
              required
            />
          </div>

          <div class="form-group">
            <label class="form-label">Announcement Message *</label>
            <textarea
              v-model="broadcastForm.content"
              class="form-input form-textarea"
              rows="5"
              placeholder="Detail the announcement for residents: date, time, affected areas, guidelines..."
              required
            ></textarea>
          </div>

          <div v-if="broadcastError" class="modal-error">{{ broadcastError }}</div>

          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="showBroadcastModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="publishingNotice">
              <BrandSpinner v-if="publishingNotice" :size="18" color="#ffffff" />
              <span v-else>Broadcast Now &rarr;</span>
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
import { useToast } from '@/composables/useToast'
import StateSelect from '@/components/StateSelect.vue'
import EstateSwitcher from '@/components/EstateSwitcher.vue'

const { showToast } = useToast()

const loading = ref(true)
const isSyncing = ref(false)
const secondsSinceSync = ref(0)
let syncInterval: ReturnType<typeof setInterval> | null = null
let tickerInterval: ReturnType<typeof setInterval> | null = null

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

const recentNews = ref<any[]>([])
const showBroadcastModal = ref(false)
const publishingNotice = ref(false)
const broadcastError = ref('')
const broadcastForm = ref({
  estate_id: 1,
  title: '',
  content: '',
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
    })
    showToast('Announcement broadcasted to residents!', 'success')
    showBroadcastModal.value = false
    broadcastForm.value = { estate_id: estates.value[0]?.id || 1, title: '', content: '' }
    await load(false)
  } catch (e: any) {
    broadcastError.value = e.response?.data?.message || 'Could not publish notice. Please try again.'
  } finally {
    publishingNotice.value = false
  }
}

const passChartTab = ref<'trend' | 'breakdown'>('trend')
const hoveredPass = ref<any>(null)
const hoveredMonth = ref<any>(null)

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

const totalWeekPasses = computed(() => {
  return (analytics.value.passTrend || []).reduce((acc, curr) => acc + (curr.total || 0), 0)
})

const totalWeekArrived = computed(() => {
  return (analytics.value.passTrend || []).reduce((acc, curr) => acc + (curr.arrived || 0), 0)
})

const avgDailyPasses = computed(() => {
  const len = (analytics.value.passTrend || []).length
  if (!len) return 0
  return Math.round(totalWeekPasses.value / len)
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

const passTrendCoords = computed(() => {
  const trend = analytics.value.passTrend || []
  if (trend.length === 0) return { totalPath: '', arrivedPath: '', areaPath: '', points: [] }
  const width = 500
  const height = 150
  const padTop = 20
  const padBottom = 25
  const padLeft = 30
  const padRight = 20

  const maxVal = Math.max(...trend.map(t => Math.max(t.total, t.arrived)), 5)
  const chartW = width - padLeft - padRight
  const chartH = height - padTop - padBottom

  const points = trend.map((t, idx) => {
    const x = padLeft + (idx / Math.max(trend.length - 1, 1)) * chartW
    const yTotal = padTop + chartH - (t.total / maxVal) * chartH
    const yArrived = padTop + chartH - (t.arrived / maxVal) * chartH
    return { ...t, x, yTotal, yArrived }
  })

  const totalD = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.yTotal.toFixed(1)}`).join(' ')
  const arrivedD = points.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.yArrived.toFixed(1)}`).join(' ')
  
  const first = points[0]
  const last = points[points.length - 1]
  const areaD = `${arrivedD} L ${last.x.toFixed(1)} ${(height - padBottom).toFixed(1)} L ${first.x.toFixed(1)} ${(height - padBottom).toFixed(1)} Z`

  return { totalPath: totalD, arrivedPath: arrivedD, areaPath: areaD, points, maxVal, height, width }
})

const revenueBarCoords = computed(() => {
  const months = analytics.value.paymentMonths || []
  if (months.length === 0) return { bars: [] }
  const width = 500
  const height = 120
  const padTop = 15
  const padBottom = 25
  const padLeft = 20
  const padRight = 20

  const maxVal = Math.max(...months.map(m => m.amount), 50000)
  const chartW = width - padLeft - padRight
  const chartH = height - padTop - padBottom
  const barWidth = 36
  const gap = (chartW - (months.length * barWidth)) / Math.max(months.length - 1, 1)

  const bars = months.map((m, idx) => {
    const x = padLeft + idx * (barWidth + gap)
    const barH = Math.max(6, (m.amount / maxVal) * chartH)
    const y = padTop + chartH - barH
    return {
      ...m,
      x,
      y,
      width: barWidth,
      height: barH,
      labelX: x + barWidth / 2,
      labelY: height - 8,
    }
  })

  return { bars, maxVal }
})

const recentPasses = ref<any[]>([])
const recentEmergencies = ref<any[]>([])

async function load(isBackground = false) {
  if (isBackground) {
    isSyncing.value = true
  } else {
    loading.value = true
  }
  try {
    const [summaryRes, estatesRes, newsRes] = await Promise.all([
      dashboardApi.getSummary(),
      estatesApi.getAll().catch(() => ({ data: { estates: [] } })),
      newsApi.getAll().catch(() => ({ data: { news: [] } })),
    ])
    stats.value = { ...stats.value, ...summaryRes.data.stats }
    if (summaryRes.data.analytics) {
      analytics.value = summaryRes.data.analytics
    }
    recentPasses.value = summaryRes.data.recentPasses
    recentEmergencies.value = summaryRes.data.recentEmergencies
    recentNews.value = (newsRes.data?.news || []).slice(0, 3)
    estates.value = estatesRes.data?.estates ?? []
    secondsSinceSync.value = 0
  } catch {
    if (!isBackground) {
      showToast('Failed to load dashboard data.', 'error')
    }
  } finally {
    loading.value = false
    isSyncing.value = false
  }
}

function handleVisibilityChange() {
  if (!document.hidden && secondsSinceSync.value >= 30) {
    load(true)
  }
}

function calcDwellTime(iso: string): string {
  const diffMs = Date.now() - new Date(iso).getTime()
  if (diffMs < 0) return 'Just entered'
  const mins = Math.floor(diffMs / 60000)
  if (mins < 1) return '< 1m'
  if (mins < 60) return `${mins}m`
  const hrs = Math.floor(mins / 60)
  const remMins = mins % 60
  return `${hrs}h ${remMins}m`
}

async function handleCreateEstate() {
  estateError.value = ''
  creatingEstate.value = true
  try {
    await onboardingApi.createEstate({
      name: newEstate.value.name,
      address: newEstate.value.address || undefined,
      city: newEstate.value.city || undefined,
      state: newEstate.value.state || undefined,
    })
    showToast(`Estate "${newEstate.value.name}" added successfully!`, 'success')
    showAddEstateModal.value = false
    newEstate.value = { name: '', address: '', city: '', state: '' }
    await load(false)
  } catch (e: unknown) {
    const err = e as { response?: { data?: { message?: string } } }
    estateError.value = err.response?.data?.message ?? 'Could not create estate. Please try again.'
  } finally {
    creatingEstate.value = false
  }
}

function passStatusClass(status: string) {
  return {
    'badge-green':  status === 'On-site',
    'badge-blue':   status === 'Pending',
    'badge-gray':   status === 'Expired' || status === 'Exited',
    'badge-red':    status === 'Revoked',
    'badge-yellow': status === 'Item Flagged',
  }
}

function emergencyStatusClass(status: string) {
  return {
    'badge-red':    status === 'sent',
    'badge-yellow': status === 'acknowledged',
    'badge-green':  status === 'resolved',
  }
}

function fmtDate(iso: string | null): string {
  if (!iso) return '—'
  return new Date(iso).toLocaleString('en-NG', { dateStyle: 'medium', timeStyle: 'short' })
}

onMounted(() => {
  load(false)
  window.addEventListener('checkpass:estate-switched', () => load(false))
  document.addEventListener('visibilitychange', handleVisibilityChange)

  // 1-second ticker for relative time counter
  tickerInterval = setInterval(() => {
    secondsSinceSync.value++
  }, 1000)

  // 30-second background auto-refresh
  syncInterval = setInterval(() => {
    if (!document.hidden) {
      load(true)
    }
  }, 30000)
})

onUnmounted(() => {
  window.removeEventListener('checkpass:estate-switched', () => load(false))
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  if (tickerInterval) clearInterval(tickerInterval)
  if (syncInterval) clearInterval(syncInterval)
})
</script>

<style scoped>
.dashboard-page { display: flex; flex-direction: column; gap: 18px; }
.hero-card {
  background: radial-gradient(circle at top left, rgba(255,255,255,0.18), transparent 38%), linear-gradient(150deg, #102a21 0%, #0a5c38 60%, #78cca2 100%);
  color: white;
  border-radius: 28px;
  padding: 26px 28px;
  box-shadow: var(--shadow-md);
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-top-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 24px;
  flex-wrap: wrap;
}

.hero-title-group {
  flex: 1;
  min-width: 280px;
}

.hero-badge-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 8px;
}

.hero-eyebrow {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: rgba(255, 255, 255, 0.85);
  font-weight: 700;
}

.gate-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(8px);
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  color: #ffffff;
}

.status-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #34d399;
  box-shadow: 0 0 8px #34d399;
  display: inline-block;
}

.telemetry-sync-pill {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: rgba(255, 255, 255, 0.16);
  border: 1px solid rgba(255, 255, 255, 0.28);
  backdrop-filter: blur(8px);
  padding: 3px 6px 3px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  color: #ffffff;
}

.telemetry-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #34d399;
  box-shadow: 0 0 6px #34d399;
  display: inline-block;
  transition: background-color 200ms ease, box-shadow 200ms ease;
}

.telemetry-dot--syncing {
  background: #60a5fa;
  box-shadow: 0 0 6px #60a5fa;
  animation: pulse-dot 1s infinite alternate;
}

@keyframes pulse-dot {
  from { opacity: 0.4; }
  to { opacity: 1; }
}

.telemetry-label {
  letter-spacing: 0.02em;
}

.btn-telemetry-sync {
  background: rgba(255, 255, 255, 0.22);
  border: 1px solid rgba(255, 255, 255, 0.4);
  color: #ffffff;
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: background-color 150ms ease, transform 150ms ease;
}

.btn-telemetry-sync:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.35);
  transform: translateY(-0.5px);
}

.btn-telemetry-sync:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.spin-anim {
  animation: spin 900ms linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.hero-title {
  font-size: 26px;
  line-height: 1.15;
  font-family: var(--font-display);
  font-weight: 800;
  color: #ffffff;
}

.hero-copy {
  margin-top: 6px;
  color: rgba(255, 255, 255, 0.88);
  font-size: 14px;
  line-height: 1.5;
  max-width: 620px;
}

/* Quick Access Section in Hero */
.hero-quick-access {
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.22);
  backdrop-filter: blur(12px);
  border-radius: 18px;
  padding: 12px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.quick-access-header {
  display: flex;
  align-items: center;
  gap: 7px;
}

.quick-access-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #34d399;
  box-shadow: 0 0 6px #34d399;
}

.quick-access-title {
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: rgba(255, 255, 255, 0.9);
}

.quick-access-actions {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.btn-hero-action {
  background: rgba(255, 255, 255, 0.18);
  color: #fff;
  border: 1px solid rgba(255, 255, 255, 0.32);
  backdrop-filter: blur(8px);
  padding: 8px 14px;
  border-radius: 11px;
  font-size: 12.5px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  cursor: pointer;
  text-decoration: none;
  transition: transform 180ms ease, background 180ms ease, color 180ms ease, box-shadow 180ms ease;
}

.btn-hero-action:hover {
  background: #ffffff;
  color: #0a5c38;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.btn-hero-action:active {
  transform: translateY(0);
}

.btn-hero-action--subtle {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(255, 255, 255, 0.22);
}

.hero-switcher-row {
  display: flex;
  align-items: center;
  padding-top: 4px;
  border-top: 1px solid rgba(255, 255, 255, 0.14);
}

.loading-state {
  display: flex;
  justify-content: center;
  padding: 80px 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 12px;
}

.stat-card {
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: 20px;
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 14px;
  text-decoration: none;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  transition: transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
  cursor: pointer;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
  border-color: #CBD5E1;
}

.stat-card--alert {
  border-color: rgba(239, 68, 68, 0.3);
  background: linear-gradient(180deg, #FFFFFF 0%, #FEF2F2 100%);
}

.stat-card--warning {
  border-color: rgba(245, 158, 11, 0.3);
}

.text-danger {
  color: #DC2626 !important;
}

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 200ms ease;
}

.stat-card:hover .stat-icon {
  transform: scale(1.04);
}

.stat-icon--blue   { background: #EFF6FF; color: #2563EB; }
.stat-icon--green  { background: #ECFDF5; color: #059669; }
.stat-icon--teal   { background: #F0FDF4; color: #0A5C38; }
.stat-icon--red    { background: #FEF2F2; color: #DC2626; }
.stat-icon--yellow { background: #FFFBEB; color: #D97706; }

.stat-value {
  font-size: 26px;
  font-weight: 800;
  line-height: 1;
  color: var(--c-text);
  font-family: var(--font-display, inherit);
}

.stat-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--c-muted);
  margin-top: 4px;
}

.dashboard-panels {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(420px, 1fr));
  gap: 16px;
}

.card {
  background: var(--c-surface);
  border: 1px solid var(--c-border);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow);
  overflow: hidden;
}
.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--c-border);
}
.card-title {
  font-size: 15px;
  font-weight: 600;
}
.card-link {
  font-size: 13px;
  color: var(--c-primary);
  font-weight: 500;
}
.card-link:hover { text-decoration: underline; }

.link { color: var(--c-primary); font-weight: 500; }
.link:hover { text-decoration: underline; }

.visitor-meta {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.visitor-name {
  font-weight: 600;
  color: var(--c-text);
  text-decoration: none;
}

.visitor-name:hover {
  color: var(--c-primary);
}

.visitor-subline {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.vehicle-subtext {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  color: #64748B;
  background: #F1F5F9;
  border: 1px solid #E2E8F0;
  padding: 1px 6px;
  border-radius: 4px;
  letter-spacing: 0.03em;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
}

.visitor-type-tag {
  font-size: 10.5px;
  font-weight: 600;
  color: #065F46;
  background: #ECFDF5;
  padding: 1px 6px;
  border-radius: 4px;
  text-transform: capitalize;
}

.status-cell {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 3px;
}

.dwell-time-hint {
  font-size: 10.5px;
  color: #059669;
  font-weight: 600;
}

@media (max-width: 500px) {
  .stats-grid { grid-template-columns: 1fr; }
}

.estate-setup-banner {
  background: linear-gradient(135deg, rgba(10, 92, 56, 0.08) 0%, rgba(0, 201, 122, 0.08) 100%);
  border: 1.5px dashed var(--c-primary, #0a5c38);
  border-radius: 20px;
  padding: 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  flex-wrap: wrap;
}

.setup-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--c-primary, #0a5c38);
  background: rgba(10, 92, 56, 0.12);
  padding: 4px 10px;
  border-radius: 8px;
  margin-bottom: 8px;
}

.setup-banner-left h2 {
  font-size: 20px;
  font-weight: 800;
  color: var(--c-text);
  margin-bottom: 4px;
  font-family: var(--font-display);
}

.setup-banner-left p {
  font-size: 14px;
  color: var(--c-muted);
  max-width: 600px;
}

.btn-add-estate {
  padding: 12px 22px;
  font-size: 14px;
  font-weight: 700;
  border-radius: 12px;
  white-space: nowrap;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-box {
  background: var(--c-surface, #fff);
  border-radius: 24px;
  padding: 28px 24px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.2);
  animation: modalIn 0.25s ease-out;
}

@keyframes modalIn {
  from { opacity: 0; transform: scale(0.96) translateY(10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 20px;
}

.modal-title {
  font-size: 22px;
  font-weight: 800;
  font-family: var(--font-display);
}

.modal-sub {
  font-size: 13px;
  color: var(--c-muted);
  margin-top: 4px;
}

.modal-close {
  background: none;
  border: none;
  font-size: 26px;
  line-height: 1;
  color: var(--c-muted);
  cursor: pointer;
}

.modal-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.modal-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.modal-error {
  background: #fee2e2;
  color: #991b1b;
  font-size: 13px;
  padding: 10px 14px;
  border-radius: 10px;
}

/* ── Executive Analytics & Visual Telemetry ── */
.analytics-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(440px, 1fr));
  gap: 16px;
}

.analytics-card {
  display: flex;
  flex-direction: column;
}

.analytics-header {
  padding: 16px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid var(--c-border);
}

.header-sub {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #059669;
  margin-bottom: 2px;
}

.tab-pill-group {
  display: flex;
  align-items: center;
  gap: 4px;
  background: #F1F5F9;
  padding: 3px;
  border-radius: 999px;
  border: 1px solid #E2E8F0;
}

.tab-pill {
  border: none;
  background: transparent;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 11.5px;
  font-weight: 600;
  color: #64748B;
  cursor: pointer;
  transition: all 150ms ease;
}

.tab-pill.active {
  background: #ffffff;
  color: #0A5C38;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.card-body {
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.chart-legend-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
  font-size: 12px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
}

.legend-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}
.legend-dot--total {
  background: #3B82F6;
  border: 1px dashed #2563EB;
}
.legend-dot--arrived {
  background: #059669;
}

.legend-text {
  font-weight: 600;
  color: #374151;
}

.chart-stat-chip {
  background: #F0FDF4;
  border: 1px solid #BBF7D0;
  color: #166534;
  padding: 2px 8px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 11px;
}

.svg-chart-wrapper {
  position: relative;
  width: 100%;
  height: 150px;
}

.svg-chart {
  width: 100%;
  height: 100%;
  overflow: visible;
}

.chart-interactive-point {
  cursor: pointer;
  transition: transform 150ms ease;
}
.chart-interactive-point:hover {
  transform: scale(1.4);
}

.chart-tooltip {
  position: absolute;
  top: 10px;
  transform: translateX(-50%);
  background: #0F172A;
  color: #ffffff;
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 11px;
  pointer-events: none;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
  z-index: 10;
  white-space: nowrap;
}

.tooltip-header {
  font-weight: 700;
  border-bottom: 1px solid #334155;
  padding-bottom: 2px;
  margin-bottom: 4px;
}

.tooltip-row {
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.text-emerald {
  color: #34D399 !important;
}

/* Category Breakdown Mode */
.category-breakdown-box {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.breakdown-headline {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 12.5px;
  font-weight: 600;
  color: #374151;
}

.breakdown-total {
  font-size: 11px;
  font-weight: 700;
  background: #F1F5F9;
  padding: 2px 8px;
  border-radius: 999px;
  color: #475569;
}

.segmented-track {
  display: flex;
  height: 12px;
  border-radius: 6px;
  overflow: hidden;
  background: #F1F5F9;
}

.segment {
  height: 100%;
  transition: width 300ms ease;
}
.seg-guest    { background: #10B981; }
.seg-delivery { background: #F59E0B; }
.seg-artisan  { background: #3B82F6; }
.seg-family   { background: #8B5CF6; }

.categories-detail-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.category-metric-card {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 12px;
  padding: 10px 12px;
}

.cat-pill {
  font-size: 11px;
  font-weight: 700;
  display: inline-block;
  margin-bottom: 4px;
}
.cat-pill--green  { color: #059669; }
.cat-pill--amber  { color: #D97706; }
.cat-pill--blue   { color: #2563EB; }
.cat-pill--purple { color: #7C3AED; }

.cat-val {
  font-size: 18px;
  font-weight: 800;
  color: #0F172A;
  font-family: var(--font-display);
}

.cat-sub {
  font-size: 10.5px;
  color: #64748B;
  margin-top: 2px;
}

/* ── Dues & Financial Section ── */
.dues-progress-box {
  background: #F0FDF4;
  border: 1px solid #BBF7D0;
  border-radius: 16px;
  padding: 14px 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.dues-summary-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.dues-label {
  font-size: 11px;
  font-weight: 600;
  color: #166534;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.dues-main-val {
  font-size: 24px;
  font-weight: 800;
  color: #064E3B;
  font-family: var(--font-display);
  margin-top: 2px;
}

.dues-rate-badge {
  font-size: 11.5px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 999px;
}
.rate-high {
  background: #059669;
  color: #ffffff;
}
.rate-mid {
  background: #D97706;
  color: #ffffff;
}

.progress-track-bg {
  width: 100%;
  height: 10px;
  background: rgba(16, 185, 129, 0.16);
  border-radius: 999px;
  overflow: hidden;
}

.progress-track-fill {
  height: 100%;
  background: linear-gradient(90deg, #10B981, #059669);
  border-radius: 999px;
  transition: width 400ms ease-out;
}

.dues-submetrics {
  display: flex;
  justify-content: space-between;
  font-size: 11.5px;
  color: #374151;
}

.pending-text {
  color: #B45309;
}

.monthly-revenue-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.revenue-chart-title {
  font-size: 12px;
  font-weight: 700;
  color: #374151;
}

.revenue-bar {
  cursor: pointer;
  transition: opacity 150ms ease, transform 150ms ease;
}
.revenue-bar:hover {
  opacity: 0.85;
}

/* ── Occupancy & Security Command Strip ── */
.command-strip-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
  gap: 16px;
}

.command-widget-card {
  background: #ffffff;
  border: 1px solid var(--c-border);
  border-radius: var(--radius-lg);
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: var(--shadow);
}

.widget-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.widget-tag {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #64748B;
}

.widget-action-link {
  font-size: 12px;
  font-weight: 600;
  color: var(--c-primary);
  text-decoration: none;
}
.widget-action-link:hover {
  text-decoration: underline;
}

.widget-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.widget-title {
  font-size: 16px;
  font-weight: 800;
  color: var(--c-text);
  margin: 0;
}

.occupancy-pill {
  font-size: 11.5px;
  font-weight: 700;
  color: #065F46;
  background: #ECFDF5;
  padding: 2px 8px;
  border-radius: 999px;
  border: 1px solid #A7F3D0;
}

.occupancy-progress-bar {
  width: 100%;
  height: 8px;
  background: #F1F5F9;
  border-radius: 999px;
  overflow: hidden;
}

.occupancy-fill {
  height: 100%;
  background: linear-gradient(90deg, #10B981, #0A5C38);
  border-radius: 999px;
  transition: width 400ms ease;
}

.occupancy-breakdown {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #64748B;
}

.peak-pill {
  font-size: 11px;
  font-weight: 700;
  color: #92400E;
  background: #FEF3C7;
  padding: 2px 8px;
  border-radius: 999px;
  border: 1px solid #FDE68A;
}

.peak-window-display {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.peak-time {
  font-size: 22px;
  font-weight: 800;
  color: #0F172A;
  font-family: var(--font-display);
}

.peak-desc {
  font-size: 12px;
  color: #64748B;
}

.gate-status-footer {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  color: #059669;
  font-weight: 600;
  margin-top: 2px;
}

.status-live-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10B981;
  box-shadow: 0 0 6px #10B981;
}

/* ── Estate Broadcasts & Resident Notices Section ── */
.broadcasts-dashboard-card {
  display: flex;
  flex-direction: column;
}

.header-with-badge {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.header-eyebrow-chip {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #059669;
}

.card-header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-post-notice {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  font-size: 12px;
  font-weight: 700;
  border-radius: 8px;
}

.broadcast-empty-state {
  padding: 32px 20px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.broadcast-empty-icon {
  font-size: 32px;
}

.broadcast-empty-state h3 {
  font-size: 16px;
  font-weight: 800;
  margin: 0;
  color: var(--c-text);
}

.broadcast-empty-state p {
  font-size: 13px;
  color: var(--c-muted);
  max-width: 440px;
  margin: 0;
}

.broadcast-feed-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 14px;
  padding: 18px 20px;
}

.broadcast-feed-card {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 14px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  transition: transform 150ms ease, box-shadow 150ms ease;
}

.broadcast-feed-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  border-color: #CBD5E1;
}

.feed-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 11px;
}

.feed-badge {
  background: #E0F2FE;
  color: #0369A1;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
}

.feed-date {
  color: #64748B;
  font-weight: 500;
}

.feed-title {
  font-size: 14px;
  font-weight: 700;
  color: #0F172A;
  margin: 0;
}

.feed-snippet {
  font-size: 12.5px;
  color: #475569;
  line-height: 1.45;
  margin: 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.feed-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 11px;
  border-top: 1px solid #EEF2F6;
  padding-top: 8px;
  margin-top: 4px;
}

.feed-author {
  color: #64748B;
  font-weight: 600;
}

.feed-live-status {
  color: #059669;
  font-weight: 700;
}
</style>
