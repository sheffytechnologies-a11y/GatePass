<template>
  <div class="user-detail-page">
    <!-- Top Bar with Back Link & Action CTAs -->
    <div class="top-nav-bar">
      <router-link to="/users" class="btn-back">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"/>
          <polyline points="12 19 5 12 12 5"/>
        </svg>
        <span>Back to Staff & Security Directory</span>
      </router-link>

      <div v-if="user" class="top-bar-right">
        <button type="button" class="btn btn-outline" @click="openEditModal">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
          <span>Edit Profile</span>
        </button>

        <button
          type="button"
          class="btn"
          :class="user.isActive ? 'btn-disable' : 'btn-enable'"
          :disabled="togglingStatus"
          @click="toggleUserStatus"
        >
          <BrandSpinner v-if="togglingStatus" :size="14" color="currentColor" />
          <template v-else>
            <svg v-if="user.isActive" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
            <svg v-else width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </template>
          <span>{{ user.isActive ? 'Disable Account' : 'Activate Account' }}</span>
        </button>

        <button type="button" class="btn btn-danger-ghost" @click="confirmDeleteModal">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
          </svg>
          <span>Remove</span>
        </button>
      </div>
    </div>

    <!-- LOADING STATE -->
    <div v-if="loading" class="loading-state card">
      <BrandSpinner :size="42" color="#0A5C38" />
      <p class="loading-label">Loading staff profile and access clearance history...</p>
    </div>

    <!-- ERROR STATE -->
    <div v-else-if="error" class="card error-card">
      <div class="empty-icon-wrap">⚠️</div>
      <h2>Staff Record Not Found</h2>
      <p>{{ error }}</p>
      <router-link to="/users" class="btn btn-primary">Return to Staff Directory</router-link>
    </div>

    <!-- MAIN CONTENT -->
    <div v-else-if="user" class="user-content">
      <!-- ── HERO PROFILE BANNER ── -->
      <section class="hero-banner card">
        <div class="hero-main-row">
          <div class="hero-profile-group">
            <div class="avatar-hero-wrap">
              <div v-if="user.avatar" class="avatar-photo-hero" :style="{ backgroundImage: `url(${user.avatar})` }" />
              <div v-else class="avatar-badge-hero" :style="{ background: avatarBg(user.id) }">
                {{ initials(user.name) }}
              </div>
              <span
                class="avatar-status-indicator"
                :class="user.isActive ? 'status-indicator--active' : 'status-indicator--disabled'"
                :title="user.isActive ? 'Active Duty Account' : 'Disabled Account'"
              />
            </div>

            <div class="hero-identity">
              <div class="hero-name-row">
                <h1 class="hero-name">{{ user.name }}</h1>
                <div class="hero-pills-group">
                  <span class="role-pill" :class="'role-pill--' + (user.type || 'security')">
                    <svg v-if="user.type === 'admin'" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <svg v-else width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                      <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>{{ formatRoleTitle(user.type) }}</span>
                  </span>

                  <span
                    class="status-pill"
                    :class="user.isActive ? 'status-pill--active' : 'status-pill--disabled'"
                  >
                    <span class="status-dot"></span>
                    {{ user.isActive ? 'Active Account' : 'Disabled Account' }}
                  </span>
                </div>
              </div>

              <!-- Contact & Station Bar -->
              <div class="hero-meta-strip">
                <span v-if="user.phone" class="meta-item meta-item--interactive" @click="copyText(user.phone, 'Phone number')">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                  </svg>
                  <a :href="'tel:' + user.phone" class="meta-link" @click.stop>{{ user.phone }}</a>
                  <button type="button" class="btn-copy-mini" title="Copy Phone Number">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                  </button>
                </span>

                <span v-if="user.phone && user.email" class="meta-dot">&bull;</span>

                <span v-if="user.email" class="meta-item meta-item--interactive" @click="copyText(user.email, 'Email address')">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                  </svg>
                  <a :href="'mailto:' + user.email" class="meta-link" @click.stop>{{ user.email }}</a>
                  <button type="button" class="btn-copy-mini" title="Copy Email">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                    </svg>
                  </button>
                </span>

                <span class="meta-dot">&bull;</span>

                <span class="meta-item">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                  </svg>
                  <span>{{ user.estate?.name || 'Sheffy Estate' }}</span>
                </span>

                <span class="meta-dot">&bull;</span>

                <span class="meta-item">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                  </svg>
                  <span>{{ user.assignedStation || 'Main Gate' }}</span>
                </span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ── KPI METRICS STRIP (4 METRIC TILES) ── -->
      <section class="kpi-strip">
        <div class="kpi-card card">
          <div class="kpi-icon-wrap kpi-icon--emerald">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              <polyline points="9 12 11 14 15 10"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-value">{{ stats.totalGranted || grantedAccess.length || 0 }}</span>
            <span class="kpi-label">Total Clearances Granted</span>
            <span class="kpi-sub">Lifetime visitor entries verified</span>
          </div>
        </div>

        <div class="kpi-card card">
          <div class="kpi-icon-wrap kpi-icon--amber">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-value">{{ stats.todayGranted || 0 }}</span>
            <span class="kpi-label">Granted Today</span>
            <span class="kpi-sub">Admitted during current shift</span>
          </div>
        </div>

        <div class="kpi-card card">
          <div class="kpi-icon-wrap kpi-icon--blue">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-value">{{ stats.onsiteNow || 0 }}</span>
            <span class="kpi-label">Currently On-Site</span>
            <span class="kpi-sub">Visitors inside perimeter</span>
          </div>
        </div>

        <div class="kpi-card card">
          <div class="kpi-icon-wrap" :class="user.isActive ? 'kpi-icon--emerald' : 'kpi-icon--red'">
            <svg v-if="user.isActive" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M12 8v4l3 3"/>
            </svg>
            <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="15" y1="9" x2="9" y2="15"/>
              <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
          </div>
          <div class="kpi-info">
            <span class="kpi-value kpi-status-text" :class="user.isActive ? 'text-emerald' : 'text-red'">
              {{ user.isActive ? 'On Duty' : 'Off Duty' }}
            </span>
            <span class="kpi-label">Gate Duty Status</span>
            <span class="kpi-sub">{{ user.shift || 'Day / Active Patrol Shift' }}</span>
          </div>
        </div>
      </section>

      <!-- ── 2-COLUMN MAIN LAYOUT ── -->
      <div class="detail-grid">
        <!-- ── LEFT COLUMN: CREDENTIALS & CLEARANCE CHECKLIST ── -->
        <aside class="col-left">
          <!-- Card 1: Gate Security Credentials Checklist -->
          <div class="card panel-card">
            <div class="panel-head">
              <div class="panel-icon-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0A5C38" stroke-width="2.2">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <h3>Gate Clearance Credentials</h3>
              </div>
              <span class="badge-status-ok">Authorized</span>
            </div>

            <div class="credentials-checklist">
              <div class="cred-item">
                <div class="cred-check-icon">✓</div>
                <div class="cred-text">
                  <span class="cred-title">Gate Terminal Station</span>
                  <span class="cred-desc">{{ user.assignedStation || 'Main Gate' }}</span>
                </div>
              </div>

              <div class="cred-item">
                <div class="cred-check-icon">✓</div>
                <div class="cred-text">
                  <span class="cred-title">QR Scanner Access</span>
                  <span class="cred-desc">Hardware & Web scanner terminal active</span>
                </div>
              </div>

              <div class="cred-item">
                <div class="cred-check-icon">✓</div>
                <div class="cred-text">
                  <span class="cred-title">Emergency Alert Broadcast</span>
                  <span class="cred-desc">Authorized to trigger perimeter lockdown</span>
                </div>
              </div>

              <div class="cred-item">
                <div class="cred-check-icon">✓</div>
                <div class="cred-text">
                  <span class="cred-title">Verified Phone Authentication</span>
                  <span class="cred-desc">{{ user.phone || 'SMS authentication active' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Card 2: Officer Information Summary -->
          <div class="card panel-card">
            <div class="panel-head">
              <div class="panel-icon-title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0A5C38" stroke-width="2.2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                  <circle cx="12" cy="7" r="4"/>
                </svg>
                <h3>Staff Account Details</h3>
              </div>
            </div>

            <div class="officer-specs-list">
              <div class="spec-row">
                <span class="spec-label">Assigned Estate</span>
                <span class="spec-val font-semibold">{{ user.estate?.name || 'Sheffy Estate' }}</span>
              </div>
              <div class="spec-row">
                <span class="spec-label">Staff Role</span>
                <span class="spec-val">{{ formatRoleTitle(user.type) }}</span>
              </div>
              <div class="spec-row">
                <span class="spec-label">Access Level</span>
                <span class="spec-val">Gate Verification & Clearance</span>
              </div>
              <div class="spec-row">
                <span class="spec-label">Member Since</span>
                <span class="spec-val">{{ fmtDate(user.createdAt) }}</span>
              </div>
              <div class="spec-row">
                <span class="spec-label">Account Status</span>
                <span class="spec-val" :class="user.isActive ? 'text-emerald font-semibold' : 'text-red font-semibold'">
                  {{ user.isActive ? 'Active & Cleared' : 'Suspended' }}
                </span>
              </div>
            </div>
          </div>
        </aside>

        <!-- ── RIGHT COLUMN: PEOPLE GRANTED ACCESS (ACCESS CLEARANCE LOG) ── -->
        <main class="col-right">
          <div class="card access-log-card">
            <header class="log-header">
              <div class="log-title-group">
                <div class="log-icon-wrap">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0A5C38" stroke-width="2.2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <polyline points="16 11 18 13 22 9"/>
                  </svg>
                </div>
                <div>
                  <h2 class="log-heading">People Granted Access</h2>
                  <p class="log-subtitle">
                    Audit log of visitors, couriers, and guests verified and admitted into the estate by {{ user.name }}.
                  </p>
                </div>
              </div>

              <div class="log-count-pill">
                {{ filteredAccessList.length }} {{ filteredAccessList.length === 1 ? 'clearance' : 'clearances' }}
              </div>
            </header>

            <!-- Search Bar & Status Filter Tabs -->
            <div class="filter-toolbar">
              <div class="search-input-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2">
                  <circle cx="11" cy="11" r="8"/>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Search by visitor name, vehicle plate, phone, resident, or purpose..."
                  class="search-input"
                />
                <button
                  v-if="searchQuery"
                  type="button"
                  class="search-clear-btn"
                  @click="searchQuery = ''"
                  title="Clear search"
                >
                  ✕
                </button>
              </div>

              <div class="status-tabs-group">
                <button
                  type="button"
                  class="status-tab-btn"
                  :class="{ 'is-active': activeTab === 'all' }"
                  @click="activeTab = 'all'"
                >
                  All Clearances
                  <span class="tab-count-badge">{{ grantedAccess.length }}</span>
                </button>

                <button
                  type="button"
                  class="status-tab-btn"
                  :class="{ 'is-active': activeTab === 'onsite' }"
                  @click="activeTab = 'onsite'"
                >
                  Currently On-Site
                  <span class="tab-count-badge tab-count--green">{{ onsiteCount }}</span>
                </button>

                <button
                  type="button"
                  class="status-tab-btn"
                  :class="{ 'is-active': activeTab === 'exited' }"
                  @click="activeTab = 'exited'"
                >
                  Exited
                  <span class="tab-count-badge">{{ exitedCount }}</span>
                </button>
              </div>
            </div>

            <!-- TABLE VIEW FOR DESKTOP -->
            <div v-if="filteredAccessList.length > 0" class="table-responsive">
              <table class="access-table">
                <thead>
                  <tr>
                    <th>Visitor Details</th>
                    <th>Visit Purpose</th>
                    <th>Vehicle / Transport</th>
                    <th>Host Resident & Unit</th>
                    <th>Entry Cleared</th>
                    <th>Exit / Departure</th>
                    <th class="th-status">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in filteredAccessList" :key="item.id" class="access-tr">
                    <!-- Visitor Details -->
                    <td class="td-visitor">
                      <div class="visitor-flex">
                        <div class="visitor-avatar" :style="{ background: avatarBg(item.id || item.visitorName) }">
                          {{ initials(item.visitorName) }}
                        </div>
                        <div class="visitor-text">
                          <span class="visitor-name">{{ item.visitorName }}</span>
                          <span v-if="item.visitorPhone" class="visitor-phone">
                            <a :href="'tel:' + item.visitorPhone" class="phone-link">{{ item.visitorPhone }}</a>
                          </span>
                        </div>
                      </div>
                    </td>

                    <!-- Visit Purpose -->
                    <td class="td-purpose">
                      <div class="purpose-cell">
                        <span class="purpose-title">{{ item.purpose || 'General Visit' }}</span>
                        <span class="purpose-badge">{{ item.passType || 'One-time' }}</span>
                      </div>
                    </td>

                    <!-- Vehicle / Transport -->
                    <td class="td-vehicle">
                      <div class="vehicle-cell">
                        <span
                          class="plate-pill"
                          :class="item.vehiclePlate && item.vehiclePlate.toLowerCase() !== 'pedestrian' ? 'plate-pill--vehicle' : 'plate-pill--walk'"
                        >
                          <svg v-if="item.vehiclePlate && item.vehiclePlate.toLowerCase() !== 'pedestrian'" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <rect x="1" y="3" width="15" height="13"/>
                            <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                            <circle cx="5.5" cy="18.5" r="2.5"/>
                            <circle cx="18.5" cy="18.5" r="2.5"/>
                          </svg>
                          <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <circle cx="12" cy="5" r="3"/>
                            <line x1="12" y1="8" x2="12" y2="14"/>
                            <line x1="9" y1="11" x2="15" y2="11"/>
                            <line x1="12" y1="14" x2="9" y2="20"/>
                            <line x1="12" y1="14" x2="15" y2="20"/>
                          </svg>
                          <span>{{ item.vehiclePlate || 'Pedestrian' }}</span>
                        </span>
                      </div>
                    </td>

                    <!-- Host Resident & Unit -->
                    <td class="td-host">
                      <div class="host-cell">
                        <span class="host-name">{{ item.residentName || 'Estate Resident' }}</span>
                        <span class="host-unit">{{ item.unitAddress || 'Residential Unit' }}</span>
                      </div>
                    </td>

                    <!-- Entry Cleared Time -->
                    <td class="td-time">
                      <div class="time-cell">
                        <span class="time-main">{{ item.arrivedFormatted || 'Just now' }}</span>
                        <span class="time-gate">{{ item.gatePoint || 'Main Gate' }}</span>
                      </div>
                    </td>

                    <!-- Exit / Departure -->
                    <td class="td-exit">
                      <div v-if="item.exitedFormatted" class="time-cell">
                        <span class="time-main text-muted">{{ item.exitedFormatted }}</span>
                        <span class="time-gate">Departure logged</span>
                      </div>
                      <div v-else class="onsite-pulse-wrap">
                        <span class="pulse-dot"></span>
                        <span class="pulse-text">Inside Perimeter</span>
                      </div>
                    </td>

                    <!-- Status -->
                    <td class="td-status">
                      <span
                        class="access-status-pill"
                        :class="item.status === 'On-site' ? 'status--onsite' : 'status--exited'"
                      >
                        <span class="status-mini-dot"></span>
                        {{ item.status === 'On-site' ? 'On-site' : 'Exited' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- EMPTY LOG STATE -->
            <div v-else class="empty-log-state">
              <div class="empty-log-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                  <circle cx="9" cy="7" r="4"/>
                  <line x1="18" y1="8" x2="23" y2="13"/>
                  <line x1="23" y1="8" x2="18" y2="13"/>
                </svg>
              </div>
              <h3 class="empty-log-title">No Access Clearances Found</h3>
              <p class="empty-log-desc">
                {{ searchQuery ? 'No entry records matched your search query.' : 'This security officer has not yet verified any visitor gate passes.' }}
              </p>
              <button v-if="searchQuery" type="button" class="btn btn-outline btn-sm" @click="searchQuery = ''">
                Clear Filters
              </button>
            </div>
          </div>
        </main>
      </div>
    </div>

    <!-- ── EDIT STAFF MODAL ── -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="showEditModal = false">
      <div class="modal-card">
        <header class="modal-header">
          <div>
            <div class="modal-eyebrow">{{ formatRoleTitle(user?.type).toUpperCase() }}</div>
            <h3 class="modal-title">Edit {{ user?.name }}</h3>
          </div>
          <button type="button" class="btn-close" @click="showEditModal = false">✕</button>
        </header>

        <form @submit.prevent="saveProfileChanges" class="modal-body">
          <div class="form-group">
            <label class="form-label">Full Name <span class="required">*</span></label>
            <input
              v-model="editForm.name"
              type="text"
              required
              class="form-input"
              placeholder="e.g. Chuku Ebonyi"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Phone Number <span class="required">*</span></label>
            <input
              v-model="editForm.phone"
              type="tel"
              required
              class="form-input"
              placeholder="e.g. 08143948473"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input
              v-model="editForm.email"
              type="email"
              class="form-input"
              placeholder="e.g. Chuk@gmail.com"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Account Access Status</label>
            <div class="status-choice-grid">
              <label class="choice-tile" :class="{ 'is-selected': editForm.is_active }">
                <input type="radio" :value="true" v-model="editForm.is_active" class="sr-only" />
                <span class="choice-dot dot-active"></span>
                <div class="choice-text">
                  <span class="choice-title">Active Account</span>
                  <span class="choice-sub">Allowed to log in and perform gate duties</span>
                </div>
              </label>

              <label class="choice-tile" :class="{ 'is-selected': !editForm.is_active }">
                <input type="radio" :value="false" v-model="editForm.is_active" class="sr-only" />
                <span class="choice-dot dot-disabled"></span>
                <div class="choice-text">
                  <span class="choice-title">Disabled Account</span>
                  <span class="choice-sub">Access suspended from estate operations</span>
                </div>
              </label>
            </div>
          </div>

          <p v-if="formError" class="modal-error">{{ formError }}</p>

          <footer class="modal-footer">
            <button type="button" class="btn btn-outline" @click="showEditModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">
              <BrandSpinner v-if="saving" :size="16" color="#ffffff" />
              <span>{{ saving ? 'Saving Changes...' : 'Save Changes' }}</span>
            </button>
          </footer>
        </form>
      </div>
    </div>

    <!-- ── DELETE CONFIRMATION MODAL ── -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-card modal-card-sm">
        <header class="modal-header">
          <div>
            <div class="modal-eyebrow text-red">REMOVE STAFF</div>
            <h3 class="modal-title">Remove {{ user?.name }}?</h3>
          </div>
          <button type="button" class="btn-close" @click="showDeleteModal = false">✕</button>
        </header>

        <div class="modal-body">
          <p class="confirm-copy">
            This staff member's credentials and estate portal access will be permanently revoked. Historical clearance logs will remain preserved for audit integrity.
          </p>

          <footer class="modal-footer">
            <button type="button" class="btn btn-outline" @click="showDeleteModal = false">Cancel</button>
            <button type="button" class="btn btn-danger" :disabled="deleting" @click="doDeleteUser">
              <BrandSpinner v-if="deleting" :size="16" color="#ffffff" />
              <span>{{ deleting ? 'Removing...' : 'Remove Staff Member' }}</span>
            </button>
          </footer>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usersApi } from '@/api/index'
import { useToast } from '@/composables/useToast'
import BrandSpinner from '@/components/BrandSpinner.vue'

const route = useRoute()
const router = useRouter()
const { showToast } = useToast()

const userId = computed(() => route.params.id as string)

// ── State ────────────────────────────────────────────────────────────
const loading = ref(true)
const error = ref('')
const user = ref<any>(null)
const stats = ref<any>({
  totalGranted: 0,
  todayGranted: 0,
  onsiteNow: 0,
  dutyStatus: 'On Duty'
})
const grantedAccess = ref<any[]>([])

// Filters & Tabs
const searchQuery = ref('')
const activeTab = ref<'all' | 'onsite' | 'exited'>('all')

// Action States
const togglingStatus = ref(false)
const showEditModal = ref(false)
const saving = ref(false)
const formError = ref('')
const editForm = ref({
  name: '',
  phone: '',
  email: '',
  is_active: true
})

const showDeleteModal = ref(false)
const deleting = ref(false)

// ── Computed Filtered List ───────────────────────────────────────────
const onsiteCount = computed(() => {
  return grantedAccess.value.filter(i => i.status === 'On-site').length
})

const exitedCount = computed(() => {
  return grantedAccess.value.filter(i => i.status === 'Exited').length
})

const filteredAccessList = computed(() => {
  let list = grantedAccess.value

  // Tab filter
  if (activeTab.value === 'onsite') {
    list = list.filter(i => i.status === 'On-site')
  } else if (activeTab.value === 'exited') {
    list = list.filter(i => i.status === 'Exited')
  }

  // Search filter
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return list

  return list.filter(i => {
    return (
      (i.visitorName && i.visitorName.toLowerCase().includes(q)) ||
      (i.visitorPhone && i.visitorPhone.includes(q)) ||
      (i.vehiclePlate && i.vehiclePlate.toLowerCase().includes(q)) ||
      (i.purpose && i.purpose.toLowerCase().includes(q)) ||
      (i.residentName && i.residentName.toLowerCase().includes(q)) ||
      (i.unitAddress && i.unitAddress.toLowerCase().includes(q)) ||
      (i.badgeCode && i.badgeCode.toLowerCase().includes(q))
    )
  })
})

// ── Helpers ──────────────────────────────────────────────────────────
const COLORS = [
  '#0A5C38', '#1D4ED8', '#B45309', '#7C3AED',
  '#BE185D', '#047857', '#C2410C', '#4338CA'
]

function avatarBg(id: any): string {
  const num = typeof id === 'number' ? id : String(id || '').charCodeAt(String(id || '').length - 1 || 0)
  return COLORS[num % COLORS.length]
}

function initials(name?: string): string {
  if (!name) return '?'
  const parts = name.trim().split(/\s+/)
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

function formatRoleTitle(type?: string): string {
  const t = (type || '').toLowerCase()
  if (t === 'admin') return 'Estate Administrator'
  return 'Gate Security Personnel'
}

function fmtDate(iso: string | null): string {
  if (!iso) return 'Recent'
  return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}

function copyText(val: string, label: string) {
  navigator.clipboard.writeText(val)
  showToast(`${label} copied to clipboard!`, 'success')
}

// ── Load Data ─────────────────────────────────────────────────────────
async function loadUser() {
  loading.value = true
  error.value = ''
  try {
    const res = await usersApi.getOne(userId.value)
    user.value = res.data.user
    stats.value = res.data.stats || {
      totalGranted: 0,
      todayGranted: 0,
      onsiteNow: 0,
      dutyStatus: user.value?.isActive ? 'On Duty' : 'Off Duty'
    }
    grantedAccess.value = res.data.grantedAccess || []
  } catch (err: any) {
    error.value = err?.response?.data?.message || 'Failed to load staff member details.'
  } finally {
    loading.value = false
  }
}

// ── Edit & Delete Actions ─────────────────────────────────────────────
function openEditModal() {
  if (!user.value) return
  editForm.value = {
    name: user.value.name,
    phone: user.value.phone || '',
    email: user.value.email || '',
    is_active: !!user.value.isActive
  }
  formError.value = ''
  showEditModal.value = true
}

async function saveProfileChanges() {
  if (!user.value) return
  formError.value = ''
  saving.value = true
  try {
    const payload: Record<string, any> = {
      name: editForm.value.name.trim(),
      email: editForm.value.email.trim() || null,
      is_active: editForm.value.is_active
    }
    if (editForm.value.phone.trim()) {
      payload.phone = editForm.value.phone.trim()
    }

    const res = await usersApi.update(user.value.id, payload)
    if (res.data?.user) {
      user.value = { ...user.value, ...res.data.user }
    } else {
      user.value.name = editForm.value.name.trim()
      user.value.phone = editForm.value.phone.trim()
      user.value.email = editForm.value.email.trim()
      user.value.isActive = editForm.value.is_active
    }

    showToast('Staff profile updated successfully.', 'success')
    showEditModal.value = false
  } catch (err: any) {
    formError.value = err?.response?.data?.message || 'Failed to update staff record.'
  } finally {
    saving.value = false
  }
}

async function toggleUserStatus() {
  if (!user.value) return
  togglingStatus.value = true
  const next = !user.value.isActive
  try {
    await usersApi.update(user.value.id, { is_active: next })
    user.value.isActive = next
    showToast(
      `${user.value.name} is now ${next ? 'active' : 'disabled'}.`,
      next ? 'success' : 'info'
    )
  } catch (err: any) {
    showToast(err?.response?.data?.message || 'Failed to toggle account status.', 'error')
  } finally {
    togglingStatus.value = false
  }
}

function confirmDeleteModal() {
  showDeleteModal.value = true
}

async function doDeleteUser() {
  if (!user.value) return
  deleting.value = true
  try {
    await usersApi.delete(user.value.id)
    showToast(`${user.value.name} has been removed.`, 'info')
    showDeleteModal.value = false
    router.push('/users')
  } catch (err: any) {
    showToast(err?.response?.data?.message || 'Failed to delete staff member.', 'error')
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  loadUser()
})
</script>

<style scoped>
.user-detail-page {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
  max-width: 100%;
  margin: 0;
  padding-bottom: 40px;
}

.user-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
}

/* ── Top Bar ── */
.top-nav-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 4px 0;
  flex-wrap: wrap;
  gap: 12px;
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
  gap: 10px;
}

.btn-disable {
  background: #ffffff;
  color: #DC2626;
  border: 1px solid #FECACA;
  display: inline-flex;
  align-items: center;
  gap: 7px;
}

.btn-disable:hover {
  background: #FEF2F2;
  border-color: #F87171;
}

.btn-enable {
  background: #0A5C38;
  color: #ffffff;
  border: 1px solid #0A5C38;
  display: inline-flex;
  align-items: center;
  gap: 7px;
}

.btn-enable:hover {
  background: #084d2f;
}

.btn-danger-ghost {
  background: #ffffff;
  color: #6B7280;
  border: 1px solid #E5E7EB;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 9px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  transition: all 150ms ease;
}

.btn-danger-ghost:hover {
  background: #FEF2F2;
  color: #DC2626;
  border-color: #FECACA;
}

/* ── Hero Profile Banner ── */
.hero-banner {
  padding: 24px 28px;
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.hero-main-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.hero-profile-group {
  display: flex;
  align-items: center;
  gap: 20px;
}

.avatar-hero-wrap {
  position: relative;
  flex-shrink: 0;
}

.avatar-badge-hero {
  width: 68px;
  height: 68px;
  border-radius: 999px;
  color: #ffffff;
  font-size: 24px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  letter-spacing: -0.5px;
  box-shadow: 0 4px 12px rgba(10, 92, 56, 0.2);
}

.avatar-photo-hero {
  width: 68px;
  height: 68px;
  border-radius: 999px;
  background-size: cover;
  background-position: center;
  border: 2px solid #E5E7EB;
}

.avatar-status-indicator {
  position: absolute;
  bottom: 2px;
  right: 2px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid #ffffff;
}

.status-indicator--active {
  background: #10B981;
}

.status-indicator--disabled {
  background: #DC2626;
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
  letter-spacing: -0.4px;
  margin: 0;
  text-transform: capitalize;
}

.hero-pills-group {
  display: flex;
  align-items: center;
  gap: 8px;
}

.role-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 999px;
  letter-spacing: 0.2px;
  text-transform: uppercase;
}

.role-pill--security {
  background: #ECFDF5;
  color: #0A5C38;
  border: 1px solid #A7F3D0;
}

.role-pill--admin {
  background: #EEF2FF;
  color: #4F46E5;
  border: 1px solid #C7D2FE;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: 999px;
}

.status-pill--active {
  background: #F0FDF4;
  color: #15803D;
  border: 1px solid #DCFCE7;
}

.status-pill--disabled {
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

.hero-meta-strip {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  color: #4B5563;
  flex-wrap: wrap;
  margin-top: 2px;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.meta-item--interactive {
  cursor: pointer;
  padding: 2px 6px;
  border-radius: 6px;
  transition: background 140ms ease;
}

.meta-item--interactive:hover {
  background: #F3F4F6;
}

.meta-link {
  color: #111827;
  text-decoration: none;
  font-weight: 600;
}

.meta-link:hover {
  color: #0A5C38;
  text-decoration: underline;
}

.btn-copy-mini {
  background: transparent;
  border: none;
  color: #9CA3AF;
  padding: 2px;
  display: inline-flex;
  cursor: pointer;
  transition: color 140ms ease;
}

.btn-copy-mini:hover {
  color: #0A5C38;
}

.meta-dot {
  color: #D1D5DB;
}

/* ── KPI Strip ── */
.kpi-strip {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.kpi-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 18px 20px;
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.kpi-icon-wrap {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.kpi-icon--emerald {
  background: #ECFDF5;
  color: #0A5C38;
}

.kpi-icon--amber {
  background: #FFFBEB;
  color: #D97706;
}

.kpi-icon--blue {
  background: #EFF6FF;
  color: #2563EB;
}

.kpi-icon--red {
  background: #FEF2F2;
  color: #DC2626;
}

.kpi-info {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.kpi-value {
  font-size: 22px;
  font-weight: 800;
  color: #111827;
  line-height: 1.1;
  letter-spacing: -0.5px;
}

.kpi-status-text {
  font-size: 18px;
  font-weight: 800;
  letter-spacing: -0.3px;
}

.kpi-label {
  font-size: 13px;
  font-weight: 700;
  color: #374151;
}

.kpi-sub {
  font-size: 11px;
  color: #6B7280;
}

/* ── 2-Column Detail Grid ── */
.detail-grid {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 16px;
  align-items: start;
}

.col-left, .col-right {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* ── Panel Cards (Left Column) ── */
.panel-card {
  padding: 20px;
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.panel-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #F3F4F6;
}

.panel-icon-title {
  display: flex;
  align-items: center;
  gap: 8px;
}

.panel-icon-title h3 {
  font-size: 15px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.badge-status-ok {
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 999px;
  background: #ECFDF5;
  color: #0A5C38;
  border: 1px solid #A7F3D0;
}

.credentials-checklist {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.cred-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.cred-check-icon {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #ECFDF5;
  color: #0A5C38;
  font-size: 13px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 1px;
}

.cred-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.cred-title {
  font-size: 13px;
  font-weight: 700;
  color: #1F2937;
}

.cred-desc {
  font-size: 12px;
  color: #6B7280;
}

.officer-specs-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.spec-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 13px;
  padding-bottom: 10px;
  border-bottom: 1px solid #F9FAFB;
}

.spec-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.spec-label {
  color: #6B7280;
  font-weight: 500;
}

.spec-val {
  color: #111827;
}

/* ── Access Log Main Card (Right Column) ── */
.access-log-card {
  padding: 24px;
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.log-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.log-title-group {
  display: flex;
  align-items: center;
  gap: 14px;
}

.log-icon-wrap {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: #ECFDF5;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.log-heading {
  font-size: 18px;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.3px;
  margin: 0;
}

.log-subtitle {
  font-size: 13px;
  color: #6B7280;
  margin: 2px 0 0;
}

.log-count-pill {
  font-size: 12px;
  font-weight: 700;
  padding: 4px 12px;
  background: #F3F4F6;
  color: #374151;
  border-radius: 999px;
}

/* Filter Toolbar */
.filter-toolbar {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 18px;
}

.search-input-box {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}

.search-input-box svg {
  position: absolute;
  left: 14px;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 10px 38px 10px 40px;
  font-size: 13px;
  border: 1px solid #E5E7EB;
  border-radius: 9px;
  background: #F9FAFB;
  outline: none;
  transition: all 160ms ease;
}

.search-input:focus {
  background: #ffffff;
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.1);
}

.search-clear-btn {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #9CA3AF;
  font-size: 12px;
  cursor: pointer;
  padding: 4px;
}

.status-tabs-group {
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid #E5E7EB;
  padding-bottom: 2px;
}

.status-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  font-size: 13px;
  font-weight: 600;
  color: #6B7280;
  background: transparent;
  border: none;
  border-bottom: 2px solid transparent;
  cursor: pointer;
  margin-bottom: -2px;
  transition: all 150ms ease;
}

.status-tab-btn:hover {
  color: #111827;
}

.status-tab-btn.is-active {
  color: #0A5C38;
  font-weight: 700;
  border-bottom-color: #0A5C38;
}

.tab-count-badge {
  font-size: 11px;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 999px;
  background: #E5E7EB;
  color: #374151;
}

.tab-count--green {
  background: #DCFCE7;
  color: #15803D;
}

/* Access Table */
.table-responsive {
  width: 100%;
  overflow-x: auto;
}

.access-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 13px;
}

.access-table th {
  text-align: left;
  padding: 10px 14px;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  color: #6B7280;
  letter-spacing: 0.5px;
  border-bottom: 1px solid #E5E7EB;
  background: #F9FAFB;
}

.th-status {
  text-align: right;
}

.access-tr {
  transition: background 140ms ease;
}

.access-tr:hover {
  background: #F9FAFB;
}

.access-table td {
  padding: 14px;
  border-bottom: 1px solid #F3F4F6;
  vertical-align: middle;
}

/* Cells */
.visitor-flex {
  display: flex;
  align-items: center;
  gap: 12px;
}

.visitor-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  color: #ffffff;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.visitor-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.visitor-name {
  font-size: 13px;
  font-weight: 700;
  color: #111827;
}

.visitor-phone {
  font-size: 12px;
}

.phone-link {
  color: #6B7280;
  text-decoration: none;
}

.phone-link:hover {
  color: #0A5C38;
  text-decoration: underline;
}

.purpose-cell {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.purpose-title {
  font-weight: 600;
  color: #1F2937;
}

.purpose-badge {
  font-size: 11px;
  color: #6B7280;
}

.vehicle-cell {
  display: flex;
  align-items: center;
}

.plate-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  padding: 3px 8px;
  border-radius: 6px;
  letter-spacing: 0.3px;
}

.plate-pill--vehicle {
  background: #F3F4F6;
  color: #1F2937;
  border: 1px solid #D1D5DB;
  font-family: ui-monospace, monospace;
}

.plate-pill--walk {
  background: #F9FAFB;
  color: #6B7280;
  border: 1px solid #E5E7EB;
}

.host-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.host-name {
  font-weight: 600;
  color: #111827;
}

.host-unit {
  font-size: 12px;
  color: #6B7280;
}

.time-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.time-main {
  font-weight: 600;
  color: #1F2937;
  font-size: 12.5px;
}

.time-gate {
  font-size: 11px;
  color: #9CA3AF;
}

.onsite-pulse-wrap {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #ECFDF5;
  color: #0A5C38;
  padding: 3px 8px;
  border-radius: 6px;
  border: 1px solid #A7F3D0;
}

.pulse-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10B981;
  box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
  animation: pulseGreen 1.8s infinite;
}

@keyframes pulseGreen {
  0% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
  }
  70% {
    box-shadow: 0 0 0 5px rgba(16, 185, 129, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
  }
}

.pulse-text {
  font-size: 11px;
  font-weight: 700;
}

.td-status {
  text-align: right;
}

.access-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 9px;
  border-radius: 999px;
}

.status--onsite {
  background: #ECFDF5;
  color: #0A5C38;
  border: 1px solid #A7F3D0;
}

.status--exited {
  background: #F3F4F6;
  color: #6B7280;
  border: 1px solid #E5E7EB;
}

.status-mini-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: currentColor;
}

/* Empty State */
.empty-log-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 24px;
  text-align: center;
  gap: 10px;
}

.empty-log-icon {
  margin-bottom: 4px;
}

.empty-log-title {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.empty-log-desc {
  font-size: 13px;
  color: #6B7280;
  max-width: 380px;
  margin: 0 0 10px;
}

/* ── Generic State Cards ── */
.loading-state,
.error-card {
  padding: 50px 24px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  background: #ffffff;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
}

.loading-label {
  font-size: 14px;
  color: #6B7280;
  font-weight: 500;
}

.empty-icon-wrap {
  font-size: 40px;
}

/* ── Modals ── */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-card {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
  overflow: hidden;
  animation: modalIn 180ms cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-card-sm {
  max-width: 440px;
}

@keyframes modalIn {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(4px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  padding: 20px 24px 16px;
  border-bottom: 1px solid #F3F4F6;
}

.modal-eyebrow {
  font-size: 11px;
  font-weight: 700;
  color: #0A5C38;
  letter-spacing: 0.5px;
  margin-bottom: 2px;
}

.text-red {
  color: #DC2626 !important;
}

.modal-title {
  font-size: 18px;
  font-weight: 800;
  color: #111827;
  margin: 0;
  text-transform: capitalize;
}

.btn-close {
  background: none;
  border: none;
  font-size: 16px;
  color: #9CA3AF;
  cursor: pointer;
  padding: 4px;
}

.btn-close:hover {
  color: #111827;
}

.modal-body {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 13px;
  font-weight: 700;
  color: #374151;
}

.required {
  color: #DC2626;
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  font-size: 14px;
  border: 1px solid #D1D5DB;
  border-radius: 9px;
  background: #ffffff;
  outline: none;
  transition: all 150ms ease;
}

.form-input:focus {
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.1);
}

.status-choice-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

.choice-tile {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 12px;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  cursor: pointer;
  background: #ffffff;
  transition: all 140ms ease;
}

.choice-tile:hover {
  background: #F9FAFB;
}

.choice-tile.is-selected {
  border-color: #0A5C38;
  background: #F0FDF4;
}

.choice-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-top: 5px;
  flex-shrink: 0;
}

.dot-active {
  background: #10B981;
}

.dot-disabled {
  background: #DC2626;
}

.choice-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.choice-title {
  font-size: 13px;
  font-weight: 700;
  color: #111827;
}

.choice-sub {
  font-size: 11px;
  color: #6B7280;
  line-height: 1.3;
}

.confirm-copy {
  font-size: 14px;
  color: #4B5563;
  line-height: 1.5;
  margin: 0;
}

.modal-error {
  font-size: 13px;
  color: #DC2626;
  background: #FEF2F2;
  padding: 10px 14px;
  border-radius: 8px;
  border: 1px solid #FEE2E2;
  margin: 0;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 6px;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
}

.text-emerald {
  color: #10B981 !important;
}

.text-red {
  color: #DC2626 !important;
}

.font-semibold {
  font-weight: 600;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
  .kpi-strip {
    grid-template-columns: repeat(2, 1fr);
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .hero-main-row {
    flex-direction: column;
    align-items: flex-start;
  }

  .kpi-strip {
    grid-template-columns: 1fr;
  }

  .status-choice-grid {
    grid-template-columns: 1fr;
  }

  .top-nav-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .top-bar-right {
    justify-content: flex-end;
  }
}
</style>
