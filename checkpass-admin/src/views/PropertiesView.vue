<template>
  <div class="properties-page">
    <!-- Hero Header -->
    <section class="hero-card hero-card--emerald">
      <div class="hero-copy">
        <h1 class="hero-title">Configure estate structure, generate units in bulk, and track occupancy.</h1>
      </div>
      <div class="hero-actions">
        <button class="btn btn-primary hero-btn-white" @click="openCreateModal('setup')">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Add Property
        </button>
      </div>
    </section>

    <!-- Telemetry Cards -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-meta">
          <span class="stat-label">Total Properties</span>
          <span class="stat-icon-wrap bg-green-soft">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#047857" stroke-width="2" stroke-linecap="round"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"/></svg>
          </span>
        </div>
        <div class="stat-val">{{ stats.totalProperties }}</div>
        <div class="stat-hint">Registered across active estate</div>
      </div>

      <div class="stat-card">
        <div class="stat-meta">
          <span class="stat-label">Occupied</span>
          <span class="stat-icon-wrap bg-emerald-soft">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="2" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </span>
        </div>
        <div class="stat-val text-emerald">{{ stats.occupiedProperties }}</div>
        <div class="stat-hint">{{ stats.occupancyRate }}% occupancy rate</div>
      </div>

      <div class="stat-card">
        <div class="stat-meta">
          <span class="stat-label">Vacant Units</span>
          <span class="stat-icon-wrap bg-amber-soft">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </span>
        </div>
        <div class="stat-val text-amber">{{ stats.vacantProperties }}</div>
        <div class="stat-hint">Ready for new tenant onboarding</div>
      </div>

      <div class="stat-card">
        <div class="stat-meta">
          <span class="stat-label">Hierarchy Structure</span>
          <span class="stat-icon-wrap bg-blue-soft">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="5" r="3"/><circle cx="6" cy="19" r="3"/><circle cx="18" cy="19" r="3"/><path d="M12 8v4M6 16l6-4 6 4"/></svg>
          </span>
        </div>
        <div class="stat-val text-blue">{{ currentPresetLabel }}</div>
        <div class="stat-hint">{{ structureLevels.map(l => l.name).join(' → ') || 'Standard' }}</div>
      </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="toolbar-card">
      <div class="search-box">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input
          v-model="search"
          type="search"
          placeholder="Search by address, lane, house, or resident name..."
          class="search-input"
          @input="debounceLoad"
        />
      </div>

      <div class="filter-group">
        <select
          v-if="auth.estates && auth.estates.length > 1"
          :value="activeEstateId"
          class="form-select toolbar-select"
          @change="onSwitchEstate(Number(($event.target as HTMLSelectElement).value))"
        >
          <option v-for="est in auth.estates" :key="est.id" :value="est.id">
            🏡 {{ est.name }}
          </option>
        </select>
        <select v-model="occupancyFilter" class="form-select toolbar-select" @change="loadProperties">
          <option value="all">All Occupancy</option>
          <option value="occupied">Occupied Only</option>
          <option value="vacant">Vacant Only</option>
        </select>

        <select v-model="unitTypeFilter" class="form-select toolbar-select" @change="loadProperties">
          <option value="all">All Property Types</option>
          <option value="Apartment">Apartment</option>
          <option value="Duplex">Duplex</option>
          <option value="Terrace">Terrace</option>
          <option value="Penthouse">Penthouse</option>
          <option value="Bungalow">Bungalow</option>
          <option value="Plot">Plot</option>
          <option value="Commercial">Commercial / Shop</option>
        </select>
      </div>
    </div>

    <!-- Properties Inventory Grid -->
    <div v-if="loading" class="empty-state">
      <div class="spinner" />
      <p>Loading properties inventory...</p>
    </div>

    <div v-else-if="properties.length === 0" class="empty-state empty-card">
      <div class="empty-icon-wrap">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="1.8" stroke-linecap="round">
          <path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M9 7h1M14 7h1M9 11h1M14 11h1M9 15h1M14 15h1"/>
        </svg>
      </div>
      <h3>No properties configured yet</h3>
      <p>Use the Batch Generator to quickly create lanes, buildings, and flats in seconds, or add a single property.</p>
      <div class="empty-actions">
        <button class="btn btn-primary" @click="openCreateModal('batch')">Batch Upload</button>
        <button class="btn btn-outline" @click="openCreateModal('setup')">+ Add Property</button>
      </div>
    </div>

    <div v-else class="properties-grid">
      <div
        v-for="prop in properties"
        :key="prop.id"
        class="property-card"
        :class="{ 'property-card--vacant': !prop.isOccupied }"
      >
        <div class="property-header">
          <div class="property-title-wrap">
            <span class="type-badge">{{ prop.unitType || 'Apartment' }}</span>
            <h3 class="property-address">{{ prop.flatAddress }}</h3>
          </div>
          <span
            class="occupancy-badge"
            :class="prop.isOccupied ? 'badge-occupied' : 'badge-vacant'"
          >
            <span class="pulse-dot" />
            {{ prop.isOccupied ? 'Occupied' : 'Vacant' }}
          </span>
        </div>

        <!-- Hierarchy Breakdown Tags -->
        <div v-if="prop.hierarchyData" class="hierarchy-tags">
          <span v-for="(val, key) in prop.hierarchyData" :key="key" class="h-tag">
            <strong>{{ key }}:</strong> {{ val }}
          </span>
        </div>
        <div v-else class="hierarchy-tags">
          <span v-if="prop.lane" class="h-tag"><strong>Lane:</strong> {{ prop.lane }}</span>
          <span v-if="prop.house" class="h-tag"><strong>House:</strong> {{ prop.house }}</span>
          <span v-if="prop.flat" class="h-tag"><strong>Flat:</strong> {{ prop.flat }}</span>
        </div>

        <!-- Occupants Section -->
        <div class="occupants-summary">
          <div class="summary-label">
            Occupants ({{ prop.residentsCount || 0 }})
          </div>
          <div v-if="prop.residents && prop.residents.length > 0" class="residents-chips">
            <div
              v-for="res in prop.residents"
              :key="res.id"
              class="res-chip"
              :title="`${res.name} (${res.phone || 'No phone'}) - ${res.role}`"
            >
              <div class="avatar-mini">{{ res.name.charAt(0).toUpperCase() }}</div>
              <span class="res-chip-name">{{ res.name }}</span>
              <span class="role-pill">{{ res.role }}</span>
            </div>
          </div>
          <div v-else class="vacant-note">
            No residents assigned. Ready for assignment.
          </div>
        </div>

        <!-- Card Footer -->
        <div class="property-footer">
          <span class="prop-id">ID: #{{ prop.id }}</span>
          <div class="card-action-btns">
            <button class="btn-icon" title="Edit Property" @click="openEditModal(prop)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button
              class="btn-icon btn-icon--danger"
              title="Delete Property"
              :disabled="prop.isOccupied"
              @click="confirmDelete(prop)"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL 1: Topology Structure Setup -->
    <div v-if="structureModalOpen" class="modal-backdrop" @click.self="structureModalOpen = false">
      <div class="modal-card modal-lg">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">Configure Estate Property Topology</h2>
            <p class="modal-subtitle">Choose a naming layout that matches how your estate is physically organized.</p>
          </div>
          <button class="close-btn" @click="structureModalOpen = false">✕</button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Topology Preset</label>
            <div class="preset-grid">
              <div
                v-for="p in PRESETS"
                :key="p.id"
                class="preset-card"
                :class="{ 'preset-card--active': structurePreset === p.id }"
                @click="applyPreset(p)"
              >
                <div class="preset-title">{{ p.label }}</div>
                <div class="preset-desc">{{ p.description }}</div>
                <div class="preset-sample">{{ p.levels.map(l => l.name).join(' → ') }}</div>
              </div>
            </div>
          </div>

          <div class="form-group mt-4">
            <label class="form-label">Hierarchy Levels (Tiers)</label>
            <div class="tiers-editor">
              <div v-for="(lvl, idx) in structureLevels" :key="idx" class="tier-row">
                <span class="tier-num">Level {{ lvl.level }}</span>
                <input
                  v-model="lvl.name"
                  class="form-input flex-1"
                  placeholder="e.g. Lane, Block, House, Suite"
                />
                <button
                  v-if="structureLevels.length > 1"
                  class="btn btn-sm btn-outline-danger"
                  @click="removeTier(idx)"
                >
                  Remove
                </button>
              </div>
              <button class="btn btn-sm btn-outline mt-2" @click="addTier">
                + Add Another Tier
              </button>
            </div>
          </div>

          <div class="form-group mt-3 relabel-box">
            <label class="toggle-control">
              <input type="checkbox" v-model="relabelExistingUnits" />
              <span>
                <strong>Apply to existing properties in this estate</strong>
                <small class="d-block text-muted">Safely re-labels existing units with the new tier names while preserving resident assignments and gate pass history.</small>
              </span>
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline" @click="structureModalOpen = false">Cancel</button>
          <button class="btn btn-primary" :disabled="savingStructure" @click="saveStructure">
            {{ savingStructure ? 'Saving...' : 'Save Topology' }}
          </button>
        </div>
      </div>
    </div>

    <!-- UNIFIED MODAL: Add / Edit Property (Setup vs Batch Upload) -->
    <div v-if="singleModalOpen" class="modal-backdrop" @click.self="singleModalOpen = false">
      <div class="modal-card" :class="{ 'modal-lg': addPropertyTab === 'batch' && !editingProperty }">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">
              {{ editingProperty ? 'Edit Property' : (addPropertyTab === 'setup' ? 'Add Property · Setup' : 'Add Properties · Batch Upload') }}
            </h2>
            <p class="modal-subtitle">
              {{ editingProperty ? 'Update unit identifiers and address.' : (addPropertyTab === 'setup' ? 'Add an individual property unit with automatic address compilation.' : 'Generate or import property units in bulk for your estate.') }}
            </p>
          </div>
          <button class="close-btn" @click="singleModalOpen = false">✕</button>
        </div>

        <!-- Mode Toggle Segmented Control (Only shown when adding new, hidden when editing) -->
        <div v-if="!editingProperty" class="modal-tab-toggle-wrap">
          <div class="modal-tab-toggle">
            <button
              type="button"
              class="tab-toggle-btn"
              :class="{ 'is-active': addPropertyTab === 'setup' }"
              @click="addPropertyTab = 'setup'"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              Setup
            </button>
            <button
              type="button"
              class="tab-toggle-btn"
              :class="{ 'is-active': addPropertyTab === 'batch' }"
              @click="addPropertyTab = 'batch'"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
              </svg>
              Batch Upload
            </button>
          </div>
        </div>

        <!-- TAB 1: SETUP (Single Property Addition / Edit) -->
        <div v-if="addPropertyTab === 'setup' || editingProperty" class="modal-body">
          <!-- Fallback input only if editing legacy property without tiers -->
          <div v-if="editingProperty && !computedAddress" class="form-group">
            <label class="form-label">Full Address / Code <span class="req">*</span></label>
            <input
              v-model="singleForm.flat_address"
              class="form-input"
              placeholder="e.g. Flat 3B, House 12, Lane 2"
            />
          </div>

          <div class="form-group">
            <label class="form-label">Unit Type</label>
            <select v-model="singleForm.unit_type" class="form-select">
              <option value="Apartment">Apartment</option>
              <option value="Duplex">Duplex</option>
              <option value="Terrace">Terrace</option>
              <option value="Penthouse">Penthouse</option>
              <option value="Bungalow">Bungalow</option>
              <option value="Plot">Plot</option>
              <option value="Commercial">Commercial / Shop</option>
            </select>
          </div>

          <div class="address-grid-3">
            <div class="form-group">
              <label class="form-label">{{ tier1Label }}</label>
              <input v-model="singleForm.lane" class="form-input" :placeholder="tier1Placeholder" />
            </div>
            <div class="form-group">
              <label class="form-label">{{ tier2Label }}</label>
              <input v-model="singleForm.house" class="form-input" :placeholder="tier2Placeholder" />
            </div>
            <div class="form-group">
              <label class="form-label">{{ tier3Label }}</label>
              <input v-model="singleForm.flat" class="form-input" :placeholder="tier3Placeholder" />
            </div>
          </div>

          <!-- Live Generated Address Preview Badge -->
          <div class="address-preview-box">
            <span class="preview-tag">Generated Address</span>
            <div class="preview-address-display">
              <span class="preview-icon">📍</span>
              <span v-if="effectiveAddress" class="preview-val">{{ effectiveAddress }}</span>
              <span v-else class="preview-hint">Enter unit tiers above to automatically generate address</span>
            </div>
          </div>

          <div class="form-group">
            <div class="form-label-row">
              <label class="form-label">Notes (Optional)</label>
              <button type="button" class="btn-link-topology" @click="openStructureModal">⚙️ Configure Estate Tiers</button>
            </div>
            <textarea
              v-model="singleForm.notes"
              class="form-input"
              rows="2"
              placeholder="e.g. Corner unit, ground floor, fiber line installed"
            />
          </div>

          <p v-if="singleError" class="form-error">{{ singleError }}</p>
        </div>

        <!-- TAB 2: BATCH UPLOAD (Batch Generator & CSV Import) -->
        <div v-else class="modal-body">
          <div class="batch-subnav">
            <button
              type="button"
              class="subnav-pill"
              :class="{ 'is-active': batchMethod === 'generator' }"
              @click="batchMethod = 'generator'"
            >
              ⚡ Rule-based Generator
            </button>
            <button
              type="button"
              class="subnav-pill"
              :class="{ 'is-active': batchMethod === 'csv' }"
              @click="batchMethod = 'csv'"
            >
              📄 CSV Spreadsheet Upload
            </button>
          </div>

          <!-- SUB-MODE 1: Rule-based Generator -->
          <div v-if="batchMethod === 'generator'">
            <div class="form-group mb-3">
              <label class="form-label">Property Unit Type</label>
              <select v-model="batchUnitType" class="form-select">
                <option value="Apartment">Apartment</option>
                <option value="Duplex">Duplex</option>
                <option value="Terrace">Terrace</option>
                <option value="Penthouse">Penthouse</option>
                <option value="Bungalow">Bungalow</option>
                <option value="Plot">Plot</option>
                <option value="Commercial">Commercial / Shop</option>
              </select>
            </div>

            <!-- Configuration for each level -->
            <div class="batch-levels-box">
              <div
                v-for="(lvl, idx) in batchConfigLevels"
                :key="idx"
                class="batch-level-card"
              >
                <div class="batch-level-head">
                  <span class="level-indicator">Level {{ idx + 1 }}</span>
                  <strong class="level-name">{{ lvl.name }}</strong>
                  <div class="mode-toggles">
                    <button
                      type="button"
                      class="mode-btn"
                      :class="{ 'mode-btn--active': lvl.mode === 'range' }"
                      @click="lvl.mode = 'range'"
                    >
                      Range (1..N)
                    </button>
                    <button
                      type="button"
                      class="mode-btn"
                      :class="{ 'mode-btn--active': lvl.mode === 'custom' }"
                      @click="lvl.mode = 'custom'"
                    >
                      Custom List
                    </button>
                  </div>
                </div>

                <!-- Range Mode -->
                <div v-if="lvl.mode === 'range'" class="range-inputs">
                  <div class="form-group">
                    <label class="form-label-xs">Prefix</label>
                    <input v-model="lvl.prefix" class="form-input" placeholder="e.g. Lane " />
                  </div>
                  <div class="form-group">
                    <label class="form-label-xs">Start #</label>
                    <input v-model.number="lvl.start" type="number" min="1" class="form-input" />
                  </div>
                  <div class="form-group">
                    <label class="form-label-xs">End #</label>
                    <input v-model.number="lvl.end" type="number" min="1" class="form-input" />
                  </div>
                </div>

                <!-- Custom List Mode -->
                <div v-else class="custom-inputs">
                  <label class="form-label-xs">Comma-separated entries</label>
                  <input
                    v-model="lvl.customList"
                    class="form-input"
                    placeholder="e.g. Flat A, Flat B, Flat C, Penthouse"
                  />
                </div>

                <div class="level-preview-count">
                  {{ getTierValues(lvl).length }} items ({{ getTierValues(lvl).slice(0, 4).join(', ') }}{{ getTierValues(lvl).length > 4 ? '...' : '' }})
                </div>
              </div>
            </div>

            <!-- Live Calculator Summary Banner -->
            <div class="calculator-banner">
              <div class="calc-math">
                <span class="calc-label">Calculated Combinations:</span>
                <span class="calc-formula">{{ batchFormulaString }}</span>
                <span class="calc-total">= {{ calculatedBatchCount }} Properties</span>
              </div>
              <div v-if="calculatedBatchCount > 0" class="calc-preview">
                <span class="preview-title">Sample Addresses:</span>
                <div class="sample-pills">
                  <span v-for="(sample, sIdx) in sampleBatchPreview" :key="sIdx" class="sample-pill">
                    {{ sample }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- SUB-MODE 2: CSV Upload -->
          <div v-else class="csv-upload-box">
            <div class="csv-template-notice">
              <div>
                <strong>Import properties from CSV</strong>
                <p class="text-hint">Columns: {{ structureLevels.map(l => l.name).join(', ') }}, Unit Type, Notes</p>
              </div>
              <button type="button" class="btn btn-sm btn-outline" @click="downloadSampleCsv">
                📥 Download Template
              </button>
            </div>

            <div class="csv-dropzone" @click="triggerCsvFileInput">
              <input
                ref="csvFileInputRef"
                type="file"
                accept=".csv,text/csv"
                class="sr-only"
                @change="handleCsvFileSelect"
              />
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#047857" stroke-width="1.8" stroke-linecap="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/></svg>
              <div v-if="csvFileName" class="csv-chosen-name">
                <strong>{{ csvFileName }}</strong>
                <span class="text-emerald">({{ csvProperties.length }} properties detected)</span>
              </div>
              <div v-else class="csv-prompt">
                <span class="drop-title">Click to choose or drop a CSV file</span>
                <span class="drop-sub">Formatted with {{ structureLevels[0]?.name || 'Lane' }}, {{ structureLevels[1]?.name || 'House' }}, {{ structureLevels[2]?.name || 'Flat' }} columns</span>
              </div>
            </div>

            <div v-if="csvProperties.length > 0" class="csv-preview-list">
              <span class="preview-title">Detected Properties (First 4):</span>
              <div class="sample-pills">
                <span v-for="(p, pIdx) in csvProperties.slice(0, 4)" :key="pIdx" class="sample-pill">
                  {{ p.flat_address }} ({{ p.unit_type }})
                </span>
                <span v-if="csvProperties.length > 4" class="sample-pill">
                  + {{ csvProperties.length - 4 }} more
                </span>
              </div>
            </div>

            <p v-if="csvError" class="form-error mt-2">{{ csvError }}</p>
          </div>

          <p v-if="batchError" class="form-error mt-2">{{ batchError }}</p>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline" @click="singleModalOpen = false">Cancel</button>

          <!-- Setup Mode Save Button -->
          <button
            v-if="addPropertyTab === 'setup' || editingProperty"
            class="btn btn-primary"
            :disabled="savingSingle"
            @click="saveSingleProperty"
          >
            {{ savingSingle ? 'Saving...' : (editingProperty ? 'Update Property' : 'Save Property') }}
          </button>

          <!-- Batch Mode Action Buttons -->
          <template v-else>
            <button
              v-if="batchMethod === 'generator'"
              class="btn btn-primary"
              :disabled="generatingBatch || calculatedBatchCount === 0"
              @click="executeBatchGeneration"
            >
              {{ generatingBatch ? 'Generating Properties...' : `Generate ${calculatedBatchCount} Properties` }}
            </button>
            <button
              v-else
              class="btn btn-primary"
              :disabled="generatingBatch || csvProperties.length === 0"
              @click="uploadCsvBatch"
            >
              {{ generatingBatch ? 'Importing Properties...' : `Import ${csvProperties.length} Properties` }}
            </button>
          </template>
        </div>
      </div>
    </div>

    <!-- MODAL 4: Delete Confirmation -->
    <div v-if="deleteTarget" class="modal-backdrop" @click.self="deleteTarget = null">
      <div class="modal-card modal-sm">
        <div class="modal-header">
          <h2 class="modal-title text-danger">Delete Property</h2>
          <button class="close-btn" @click="deleteTarget = null">✕</button>
        </div>
        <div class="modal-body">
          <p>
            Are you sure you want to remove <strong>{{ deleteTarget.flatAddress }}</strong> from the estate inventory?
          </p>
          <p class="text-hint mt-2">
            This action cannot be undone. Units with active residents cannot be deleted.
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline" @click="deleteTarget = null">Cancel</button>
          <button class="btn btn-danger" :disabled="deleting" @click="executeDelete">
            {{ deleting ? 'Deleting...' : 'Delete Property' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { propertiesApi } from '@/api/index'
import { useToast } from '@/composables/useToast'

const auth = useAuthStore()
const { showToast } = useToast()

const activeEstateId = computed(() => Number(auth.activeEstateId) || 1)

// Telemetry & State
const loading = ref(false)
const properties = ref<any[]>([])
const stats = ref({
  totalProperties: 0,
  occupiedProperties: 0,
  vacantProperties: 0,
  occupancyRate: 0,
  unitTypes: {} as Record<string, number>,
})

// Filters
const search = ref('')
const occupancyFilter = ref('all')
const unitTypeFilter = ref('all')
let debounceTimer: any = null

function debounceLoad() {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    loadProperties()
  }, 300)
}

// Presets
const PRESETS = [
  {
    id: 'lane_house_flat',
    label: 'Gated Community',
    description: 'Classic residential layout with lanes, buildings, and flats.',
    levels: [
      { level: 1, name: 'Lane', slug: 'lane', required: true },
      { level: 2, name: 'House', slug: 'house', required: true },
      { level: 3, name: 'Flat', slug: 'flat', required: false },
    ],
  },
  {
    id: 'block_floor_suite',
    label: 'High-Rise / Towers',
    description: 'Multi-story apartment blocks or modern towers.',
    levels: [
      { level: 1, name: 'Block / Tower', slug: 'block', required: true },
      { level: 2, name: 'Floor', slug: 'floor', required: true },
      { level: 3, name: 'Suite / Apt', slug: 'suite', required: true },
    ],
  },
  {
    id: 'street_house',
    label: 'Street / Crescent',
    description: 'Simple two-tier naming with street and house number.',
    levels: [
      { level: 1, name: 'Street / Crescent', slug: 'street', required: true },
      { level: 2, name: 'House No.', slug: 'house', required: true },
    ],
  },
  {
    id: 'custom',
    label: 'Custom Hierarchy',
    description: 'User-defined custom hierarchy naming and depth.',
    levels: [
      { level: 1, name: 'Zone', slug: 'zone', required: true },
      { level: 2, name: 'Plot', slug: 'plot', required: true },
    ],
  },
]

// Topology Structure
const relabelExistingUnits = ref(true)
const structureModalOpen = ref(false)
const structurePreset = ref('lane_house_flat')
const structureLevels = ref<any[]>([
  { level: 1, name: 'Lane', slug: 'lane', required: true },
  { level: 2, name: 'House', slug: 'house', required: true },
  { level: 3, name: 'Flat', slug: 'flat', required: false },
])
const savingStructure = ref(false)

const currentPresetLabel = computed(() => {
  const match = PRESETS.find(p => p.id === structurePreset.value)
  return match?.label || 'Custom Hierarchy'
})

function applyPreset(p: any) {
  structurePreset.value = p.id
  structureLevels.value = JSON.parse(JSON.stringify(p.levels))
}

function addTier() {
  const nextLvl = structureLevels.value.length + 1
  structureLevels.value.push({
    level: nextLvl,
    name: `Tier ${nextLvl}`,
    slug: `tier_${nextLvl}`,
    required: false,
  })
}

function removeTier(index: number) {
  structureLevels.value.splice(index, 1)
  structureLevels.value.forEach((l, idx) => {
    l.level = idx + 1
  })
}

async function loadStructure() {
  if (!activeEstateId.value) return
  try {
    const res = await propertiesApi.getStructure(activeEstateId.value)
    if (res.data?.structure) {
      structurePreset.value = res.data.structure.preset || 'lane_house_flat'
      structureLevels.value = res.data.structure.levels || structureLevels.value
    }
  } catch (err) {
    // fallback to default
  }
}

async function saveStructure() {
  if (!activeEstateId.value) return
  savingStructure.value = true
  try {
    const res = await propertiesApi.updateStructure(activeEstateId.value, {
      preset: structurePreset.value,
      levels: structureLevels.value,
      relabel_existing_units: relabelExistingUnits.value,
    })
    if (res.data?.relabelledCount > 0) {
      showToast(`Topology updated and ${res.data.relabelledCount} properties re-labeled.`, 'success')
    } else {
      showToast('Topology structure updated successfully.', 'success')
    }
    await loadProperties()
    showToast('Topology structure updated successfully.', 'success')
    structureModalOpen.value = false
  } catch (err: any) {
    showToast(err.response?.data?.message || 'Failed to update structure', 'error')
  } finally {
    savingStructure.value = false
  }
}

function openStructureModal() {
  structureModalOpen.value = true
}

// Batch Generator & Unified Modal State
const addPropertyTab = ref<'setup' | 'batch'>('setup')
const batchMethod = ref<'generator' | 'csv'>('generator')
const batchUnitType = ref('Apartment')
const batchConfigLevels = ref<any[]>([])
const generatingBatch = ref(false)
const batchError = ref('')

// CSV Upload State
const csvFileInputRef = ref<HTMLInputElement | null>(null)
const csvFileName = ref('')
const csvProperties = ref<any[]>([])
const csvParsing = ref(false)
const csvError = ref('')

function initBatchConfig() {
  batchConfigLevels.value = structureLevels.value.map(lvl => ({
    name: lvl.name,
    mode: 'range',
    prefix: lvl.name.includes('Lane') || lvl.name.includes('Street') ? 'Lane ' : (lvl.name.includes('House') ? 'House ' : 'Flat '),
    start: 1,
    end: lvl.name.includes('Flat') ? 4 : (lvl.name.includes('House') ? 5 : 2),
    customList: '',
  }))
}


function triggerCsvFileInput() {
  csvFileInputRef.value?.click()
}

function handleCsvFileSelect(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  csvFileName.value = file.name
  csvError.value = ''
  csvParsing.value = true

  const reader = new FileReader()
  reader.onload = (e) => {
    try {
      const text = (e.target?.result as string) || ''
      const lines = text.split(/\r?\n/).map(l => l.trim()).filter(Boolean)
      if (lines.length < 2) {
        csvError.value = 'CSV file must have a header row and at least 1 data row.'
        csvProperties.value = []
        return
      }

      const rows: any[] = []
      // Parse data rows
      for (let i = 1; i < lines.length; i++) {
        const cols = lines[i].split(',').map(c => c.trim().replace(/^["']|["']$/g, ''))
        const lane = cols[0] || ''
        const house = cols[1] || ''
        const flat = cols[2] || ''
        const unit_type = cols[3] || batchUnitType.value || 'Apartment'
        const notes = cols[4] || ''
        const flat_address = [flat, house, lane].filter(Boolean).join(', ')
        if (flat_address) {
          rows.push({ lane, house, flat, unit_type, notes, flat_address })
        }
      }
      csvProperties.value = rows
      if (rows.length === 0) {
        csvError.value = 'No valid property addresses found in CSV.'
      }
    } catch (err: any) {
      csvError.value = 'Failed to parse CSV: ' + err.message
    } finally {
      csvParsing.value = false
    }
  }
  reader.onerror = () => {
    csvError.value = 'Error reading CSV file.'
    csvParsing.value = false
  }
  reader.readAsText(file)
}

function downloadSampleCsv() {
  const l1 = structureLevels.value[0]?.name || 'Lane'
  const l2 = structureLevels.value[1]?.name || 'House'
  const l3 = structureLevels.value[2]?.name || 'Flat'
  const header = `${l1},${l2},${l3},Unit Type,Notes\r\n`
  const row1 = `${l1} 1,${l2} 1,${l3} 101,Apartment,Block A ground floor\r\n`
  const row2 = `${l1} 1,${l2} 1,${l3} 102,Apartment,Block A first floor\r\n`
  const row3 = `${l1} 2,${l2} 4,,Duplex,Corner unit\r\n`
  const blob = new Blob([header + row1 + row2 + row3], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `checkpass_${auth.activeEstateName || 'estate'}_properties_template.csv`
  a.click()
  URL.revokeObjectURL(url)
}

async function uploadCsvBatch() {
  if (!activeEstateId.value || csvProperties.value.length === 0) return
  generatingBatch.value = true
  batchError.value = ''
  try {
    for (const prop of csvProperties.value) {
      await propertiesApi.create({
        estate_id: activeEstateId.value,
        flat_address: prop.flat_address,
        unit_type: prop.unit_type,
        lane: prop.lane,
        house: prop.house,
        flat: prop.flat,
        notes: prop.notes,
      })
    }
    showToast(`Successfully uploaded ${csvProperties.value.length} properties!`, 'success')
    singleModalOpen.value = false
    await loadProperties()
    await loadStats()
  } catch (err: any) {
    batchError.value = err.response?.data?.message || 'Failed to import CSV properties.'
  } finally {
    generatingBatch.value = false
  }
}

function getTierValues(tier: any): string[] {
  if (tier.mode === 'range') {
    const s = Math.max(1, tier.start || 1)
    const e = Math.max(s, tier.end || 1)
    const out: string[] = []
    const prefix = tier.prefix || ''
    for (let i = s; i <= e; i++) {
      out.push(`${prefix}${i}`.trim())
    }
    return out
  }
  return tier.customList
    ? tier.customList.split(',').map((s: string) => s.trim()).filter(Boolean)
    : []
}

const batchFormulaString = computed(() => {
  return batchConfigLevels.value
    .map(lvl => `${getTierValues(lvl).length} ${lvl.name}`)
    .join(' × ')
})

const calculatedBatchCount = computed(() => {
  if (batchConfigLevels.value.length === 0) return 0
  return batchConfigLevels.value.reduce((acc, lvl) => {
    const len = getTierValues(lvl).length
    return acc * (len > 0 ? len : 1)
  }, 1)
})

const sampleBatchPreview = computed(() => {
  if (calculatedBatchCount.value === 0) return []
  const tierVals = batchConfigLevels.value.map(lvl => getTierValues(lvl))
  const samples: string[] = []
  
  // Create first 3 combinations
  const l1 = tierVals[0] || []
  const l2 = tierVals[1] || []
  const l3 = tierVals[2] || []

  for (const v1 of l1) {
    if (l2.length > 0) {
      for (const v2 of l2) {
        if (l3.length > 0) {
          for (const v3 of l3) {
            samples.push(`${v3}, ${v2}, ${v1}`)
            if (samples.length >= 4) return samples
          }
        } else {
          samples.push(`${v2}, ${v1}`)
          if (samples.length >= 4) return samples
        }
      }
    } else {
      samples.push(v1)
      if (samples.length >= 4) return samples
    }
  }
  return samples
})

async function executeBatchGeneration() {
  if (!activeEstateId.value) return
  batchError.value = ''
  generatingBatch.value = true

  const levelsPayload = batchConfigLevels.value.map(lvl => ({
    name: lvl.name,
    values: getTierValues(lvl),
  }))

  try {
    const res = await propertiesApi.batchCreate({
      estate_id: activeEstateId.value,
      unit_type: batchUnitType.value,
      levels: levelsPayload,
    })
    showToast(`Successfully created ${res.data?.createdCount || calculatedBatchCount.value} properties!`, 'success')
    singleModalOpen.value = false
    await loadProperties()
    await loadStats()
  } catch (err: any) {
    batchError.value = err.response?.data?.message || 'Failed to generate batch properties.'
  } finally {
    generatingBatch.value = false
  }
}

// Single / Setup Property Modal State
const singleModalOpen = ref(false)
const editingProperty = ref<any>(null)
const savingSingle = ref(false)
const singleError = ref('')
const singleForm = ref({
  flat_address: '',
  unit_type: 'Apartment',
  lane: '',
  house: '',
  flat: '',
  notes: '',
})

// Dynamic tier labels and placeholders based on configured estate structure
const tier1Label = computed(() => structureLevels.value[0]?.name ? `Tier 1 (${structureLevels.value[0].name})` : 'Tier 1 (Lane/Block)')
const tier2Label = computed(() => structureLevels.value[1]?.name ? `Tier 2 (${structureLevels.value[1].name})` : 'Tier 2 (House/Floor)')
const tier3Label = computed(() => structureLevels.value[2]?.name ? `Tier 3 (${structureLevels.value[2].name})` : 'Tier 3 (Flat/Unit)')

// Clean placeholders: 1, A, 4 etc. instead of repeating tier words (system auto-prefixes the tier name)
const tier1Placeholder = computed(() => 'e.g. 1, A, 4')
const tier2Placeholder = computed(() => 'e.g. 4, 12, B')
const tier3Placeholder = computed(() => 'e.g. B, 1, 2')

// Helper to auto-prefix tier label if not already present (e.g. '1' -> 'Lane 1', '4' -> 'House 4', 'B' -> 'Flat B')
function formatTierDisplay(val: string, tierName?: string): string {
  const cleanVal = (val || '').trim()
  if (!cleanVal) return ''
  const cleanTier = (tierName || '').trim()
  if (!cleanTier) return cleanVal

  const lowerVal = cleanVal.toLowerCase()
  const lowerTier = cleanTier.toLowerCase()

  // If the user already typed the tier name (e.g. "Lane 1" or "Flat B"), don't duplicate it
  if (lowerVal.startsWith(lowerTier)) {
    return cleanVal
  }

  return `${cleanTier} ${cleanVal}`
}

// Computed reverse-hierarchical address from tiers: Flat -> House -> Lane (auto-prefixed with tier names)
const computedAddress = computed(() => {
  const t3Name = structureLevels.value[2]?.name || 'Flat'
  const t2Name = structureLevels.value[1]?.name || 'House'
  const t1Name = structureLevels.value[0]?.name || 'Lane'

  const t3Formatted = formatTierDisplay(singleForm.value.flat, t3Name)
  const t2Formatted = formatTierDisplay(singleForm.value.house, t2Name)
  const t1Formatted = formatTierDisplay(singleForm.value.lane, t1Name)

  const parts = [t3Formatted, t2Formatted, t1Formatted].filter(Boolean)
  return parts.join(', ')
})

const effectiveAddress = computed(() => {
  return computedAddress.value || singleForm.value.flat_address.trim()
})

function openCreateModal(tab?: 'setup' | 'batch' | Event) {
  const resolvedTab = (typeof tab === 'string' && (tab === 'setup' || tab === 'batch')) ? tab : 'setup'
  editingProperty.value = null
  addPropertyTab.value = resolvedTab
  singleForm.value = {
    flat_address: '',
    unit_type: 'Apartment',
    lane: '',
    house: '',
    flat: '',
    notes: '',
  }
  singleError.value = ''
  batchError.value = ''
  csvError.value = ''
  csvFileName.value = ''
  csvProperties.value = []
  initBatchConfig()
  singleModalOpen.value = true
}

function openEditModal(prop: any) {
  editingProperty.value = prop
  addPropertyTab.value = 'setup'
  singleForm.value = {
    flat_address: prop.flatAddress || '',
    unit_type: prop.unitType || 'Apartment',
    lane: prop.lane || '',
    house: prop.house || '',
    flat: prop.flat || '',
    notes: prop.notes || '',
  }
  singleError.value = ''
  singleModalOpen.value = true
}

async function saveSingleProperty() {
  if (!activeEstateId.value) return
  const addressToSave = effectiveAddress.value
  if (!addressToSave) {
    singleError.value = 'Please provide at least one address tier (e.g. 1, 4, B).'
    return
  }
  singleError.value = ''
  savingSingle.value = true

  const hierarchyData: Record<string, string> = {}
  if (structureLevels.value[0]?.name && singleForm.value.lane) {
    hierarchyData[structureLevels.value[0].name] = singleForm.value.lane.trim()
  }
  if (structureLevels.value[1]?.name && singleForm.value.house) {
    hierarchyData[structureLevels.value[1].name] = singleForm.value.house.trim()
  }
  if (structureLevels.value[2]?.name && singleForm.value.flat) {
    hierarchyData[structureLevels.value[2].name] = singleForm.value.flat.trim()
  }

  try {
    if (editingProperty.value) {
      await propertiesApi.update(editingProperty.value.id, {
        flat_address: addressToSave,
        unit_type: singleForm.value.unit_type,
        lane: singleForm.value.lane.trim(),
        house: singleForm.value.house.trim(),
        flat: singleForm.value.flat.trim(),
        hierarchy_data: Object.keys(hierarchyData).length > 0 ? hierarchyData : undefined,
        notes: singleForm.value.notes,
      })
      showToast('Property updated successfully.', 'success')
    } else {
      await propertiesApi.create({
        estate_id: activeEstateId.value,
        flat_address: addressToSave,
        unit_type: singleForm.value.unit_type,
        lane: singleForm.value.lane.trim(),
        house: singleForm.value.house.trim(),
        flat: singleForm.value.flat.trim(),
        hierarchy_data: Object.keys(hierarchyData).length > 0 ? hierarchyData : undefined,
        notes: singleForm.value.notes,
      })
      showToast('Property created successfully.', 'success')
    }
    singleModalOpen.value = false
    await loadProperties()
    await loadStats()
  } catch (err: any) {
    singleError.value = err.response?.data?.message || 'Failed to save property.'
  } finally {
    savingSingle.value = false
  }
}

// Delete Property
const deleteTarget = ref<any>(null)
const deleting = ref(false)

function confirmDelete(prop: any) {
  deleteTarget.value = prop
}

async function executeDelete() {
  if (!deleteTarget.value) return
  deleting.value = true
  try {
    await propertiesApi.delete(deleteTarget.value.id)
    showToast('Property deleted.', 'success')
    deleteTarget.value = null
    await loadProperties()
    await loadStats()
  } catch (err: any) {
    showToast(err.response?.data?.message || 'Failed to delete property.', 'error')
  } finally {
    deleting.value = false
  }
}

// Fetchers
async function loadProperties() {
  if (!activeEstateId.value) return
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (search.value.trim()) params.search = search.value.trim()
    if (occupancyFilter.value !== 'all') params.occupancy = occupancyFilter.value
    if (unitTypeFilter.value !== 'all') params.unit_type = unitTypeFilter.value

    const res = await propertiesApi.getAll(activeEstateId.value, params)
    properties.value = res.data?.properties || []
  } catch (err) {
    properties.value = []
  } finally {
    loading.value = false
  }
}

async function loadStats() {
  if (!activeEstateId.value) return
  try {
    const res = await propertiesApi.getStats(activeEstateId.value)
    if (res.data?.stats) {
      stats.value = res.data.stats
    }
  } catch (err) {
    // ignore
  }
}

function onSwitchEstate(newId: number) {
  const match = auth.estates.find((e: any) => e.id === newId)
  if (match) {
    auth.setActiveEstate(match)
  }
}

watch(activeEstateId, () => {
  loadStructure()
  loadProperties()
  loadStats()
})

onMounted(() => {
  loadStructure()
  loadProperties()
  loadStats()
})
</script>

<style scoped>
.properties-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Hero Section */
.hero-card--emerald {
  background: linear-gradient(135deg, #064E3B 0%, #047857 60%, #059669 100%);
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 16px;
  padding: 24px 28px;
  border-radius: 24px;
  color: white;
  box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.25);
}

.hero-eyebrow {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  opacity: 0.85;
  font-weight: 700;
  margin-bottom: 6px;
}

.hero-title {
  font-size: 20px;
  font-weight: 700;
  max-width: 620px;
  line-height: 1.35;
  margin: 0;
}

.hero-actions {
  display: flex;
  gap: 10px;
  flex-shrink: 0;
  flex-wrap: wrap;
}

.btn-outline-white {
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: white;
  padding: 8px 14px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  backdrop-filter: blur(8px);
  transition: all 0.2s ease;
}

.btn-outline-white:hover {
  background: rgba(255, 255, 255, 0.25);
}

.btn-emerald-batch {
  background: #022C22;
  color: #6EE7B7;
  border: 1px solid rgba(110, 231, 183, 0.3);
  padding: 8px 14px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 13px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
}

.btn-emerald-batch:hover {
  background: #064E3B;
  color: white;
}

.hero-btn-white {
  background: white;
  color: #064E3B;
  border: none;
  padding: 8px 16px;
  border-radius: 12px;
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transition: all 0.2s ease;
}

.hero-btn-white:hover {
  background: #f0fdf4;
  transform: translateY(-1px);
}

/* Stats Row */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 16px;
}

.stat-card {
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 18px;
  padding: 18px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
}

.stat-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 12px;
  font-weight: 600;
  color: #6B7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.stat-icon-wrap {
  width: 32px;
  height: 32px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-green-soft { background: #ECFDF5; }
.bg-emerald-soft { background: #D1FAE5; }
.bg-amber-soft { background: #FEF3C7; }
.bg-blue-soft { background: #EFF6FF; }

.stat-val {
  font-size: 26px;
  font-weight: 800;
  color: #111827;
  line-height: 1.1;
}

.text-emerald { color: #047857; }
.text-amber { color: #D97706; }
.text-blue { color: #2563EB; }

.stat-hint {
  font-size: 12px;
  color: #9CA3AF;
  margin-top: 6px;
}

/* Toolbar */
.toolbar-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 12px 16px;
  flex-wrap: wrap;
}

.search-box {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  padding: 8px 14px;
  flex: 1;
  min-width: 260px;
}

.search-input {
  border: none;
  background: transparent;
  outline: none;
  width: 100%;
  font-size: 13.5px;
  color: #111827;
}

.filter-group {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.toolbar-select {
  padding: 8px 12px;
  font-size: 13px;
  border-radius: 10px;
  border: 1px solid #E5E7EB;
  background-color: #F9FAFB;
  color: #374151;
  outline: none;
  font-weight: 500;
}

/* Properties Grid */
.properties-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 16px;
}

.property-card {
  background: white;
  border: 1px solid #E5E7EB;
  border-radius: 18px;
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  transition: all 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

.property-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 18px -4px rgba(0, 0, 0, 0.06);
  border-color: #D1D5DB;
}

.property-card--vacant {
  border-left: 4px solid #10B981;
}

.property-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
}

.type-badge {
  display: inline-block;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  color: #065F46;
  background: #ECFDF5;
  padding: 2px 8px;
  border-radius: 6px;
  margin-bottom: 4px;
}

.property-address {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin: 0;
  line-height: 1.25;
}

.occupancy-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 700;
  padding: 3px 10px;
  border-radius: 20px;
  flex-shrink: 0;
}

.badge-occupied {
  background: #064E3B;
  color: #6EE7B7;
}

.badge-vacant {
  background: #ECFDF5;
  color: #047857;
  border: 1px solid rgba(4, 120, 87, 0.2);
}

.pulse-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
}

.hierarchy-tags {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}

.h-tag {
  font-size: 11.5px;
  background: #F3F4F6;
  color: #4B5563;
  padding: 2px 8px;
  border-radius: 6px;
}

.occupants-summary {
  background: #F9FAFB;
  border-radius: 12px;
  padding: 10px 12px;
  flex: 1;
}

.summary-label {
  font-size: 11px;
  font-weight: 700;
  color: #6B7280;
  text-transform: uppercase;
  margin-bottom: 6px;
}

.residents-chips {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.res-chip {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
}

.avatar-mini {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #047857;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
}

.res-chip-name {
  font-weight: 600;
  color: #1F2937;
}

.role-pill {
  font-size: 10px;
  text-transform: uppercase;
  background: #E5E7EB;
  color: #4B5563;
  padding: 1px 6px;
  border-radius: 4px;
}

.vacant-note {
  font-size: 12px;
  color: #059669;
  font-style: italic;
}

.property-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 8px;
  border-top: 1px solid #F3F4F6;
}

.prop-id {
  font-size: 11px;
  color: #9CA3AF;
  font-weight: 500;
}

.card-action-btns {
  display: flex;
  gap: 6px;
}

.btn-icon {
  background: #F3F4F6;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #4B5563;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-icon:hover {
  background: #E5E7EB;
  color: #111827;
}

.btn-icon--danger:hover {
  background: #FEE2E2;
  color: #DC2626;
}

.btn-icon:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 40px 20px;
  background: white;
  border-radius: 18px;
  border: 1px dashed #D1D5DB;
}

.empty-icon-wrap {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  background: #ECFDF5;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 14px;
}

.empty-actions {
  display: flex;
  justify-content: center;
  gap: 12px;
  margin-top: 18px;
}

/* Modals */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 16px;
}

.modal-card {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 520px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

.modal-lg { max-width: 720px; }
.modal-sm { max-width: 400px; }

.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid #E5E7EB;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.modal-title {
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.modal-subtitle {
  font-size: 13px;
  color: #6B7280;
  margin: 4px 0 0;
}

.close-btn {
  background: #F3F4F6;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  cursor: pointer;
  color: #4B5563;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-body {
  padding: 20px 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #E5E7EB;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  background: #F9FAFB;
}

/* Preset Cards */
.preset-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 10px;
  margin-top: 8px;
}

.preset-card {
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 12px 14px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.preset-card:hover {
  border-color: #059669;
  background: #F0FDF4;
}

.preset-card--active {
  border-color: #047857;
  background: #ECFDF5;
  box-shadow: 0 0 0 1px #047857;
}

.preset-title {
  font-weight: 700;
  font-size: 14px;
  color: #111827;
}

.preset-desc {
  font-size: 11.5px;
  color: #6B7280;
  margin: 2px 0 6px;
}

.preset-sample {
  font-size: 11px;
  font-weight: 600;
  color: #047857;
}

/* Tiers Editor */
.tiers-editor {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.tier-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.tier-num {
  font-size: 12px;
  font-weight: 700;
  color: #4B5563;
  width: 70px;
}

/* Batch Modal Elements */
.batch-levels-box {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.batch-level-card {
  border: 1px solid #E5E7EB;
  border-radius: 14px;
  padding: 14px;
  background: #FAFAFA;
}

.batch-level-head {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
}

.level-indicator {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  background: #E5E7EB;
  color: #374151;
  padding: 2px 6px;
  border-radius: 4px;
}

.level-name {
  font-size: 14px;
  color: #111827;
}

.mode-toggles {
  margin-left: auto;
  display: flex;
  background: #E5E7EB;
  border-radius: 8px;
  padding: 2px;
}

.mode-btn {
  border: none;
  background: transparent;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 11.5px;
  font-weight: 600;
  color: #4B5563;
  cursor: pointer;
}

.mode-btn--active {
  background: white;
  color: #047857;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.range-inputs {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 8px;
}

.form-label-xs {
  font-size: 11px;
  font-weight: 600;
  color: #6B7280;
  margin-bottom: 4px;
  display: block;
}

.level-preview-count {
  font-size: 11.5px;
  color: #059669;
  font-weight: 500;
  margin-top: 6px;
}

.calculator-banner {
  background: #022C22;
  color: white;
  border-radius: 14px;
  padding: 14px 18px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.calc-math {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  font-size: 13.5px;
}

.calc-formula {
  color: #A7F3D0;
  font-family: monospace;
  font-size: 13px;
}

.calc-total {
  font-weight: 800;
  color: #34D399;
  font-size: 15px;
}

.sample-pills {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  margin-top: 4px;
}

.sample-pill {
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #F0FDF4;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 11.5px;
}

.preview-title {
  font-size: 11px;
  text-transform: uppercase;
  color: #9CA3AF;
  letter-spacing: 0.05em;
}

/* Generic Form Helpers */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.form-label {
  font-size: 12px;
  font-weight: 600;
  color: #374151;
}

.req { color: #DC2626; }

.form-input, .form-select {
  border: 1px solid #D1D5DB;
  border-radius: 10px;
  padding: 8px 12px;
  font-size: 13.5px;
  color: #111827;
  outline: none;
}

.form-input:focus, .form-select:focus {
  border-color: #047857;
  box-shadow: 0 0 0 2px rgba(4, 120, 87, 0.15);
}

.address-grid-3 {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 8px;
}

/* Modal Tab Toggle (Setup vs Batch Upload) */
.modal-tab-toggle-wrap {
  padding: 0 24px 14px;
  border-bottom: 1px solid #E5E7EB;
}

.modal-tab-toggle {
  display: flex;
  background: #F3F4F6;
  padding: 4px;
  border-radius: 12px;
  gap: 4px;
}

.tab-toggle-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 9px 16px;
  border: none;
  background: transparent;
  color: #4B5563;
  font-size: 13.5px;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-toggle-btn.is-active {
  background: #FFFFFF;
  color: #047857;
  font-weight: 700;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.tab-toggle-btn:hover:not(.is-active) {
  color: #111827;
}

/* Live Generated Address Preview Box */
.address-preview-box {
  background: #F0FDF4;
  border: 1.5px dashed #86EFAC;
  border-radius: 10px;
  padding: 10px 14px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.preview-tag {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #15803D;
}

.preview-address-display {
  display: flex;
  align-items: center;
  gap: 8px;
}

.preview-icon {
  font-size: 14px;
}

.preview-val {
  font-size: 14px;
  font-weight: 700;
  color: #065F46;
}

.preview-hint {
  font-size: 12.5px;
  color: #6B7280;
  font-style: italic;
}

/* Form label row with action */
.form-label-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-link-topology {
  background: none;
  border: none;
  color: #047857;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
}
.btn-link-topology:hover {
  text-decoration: underline;
}

/* Hierarchy Structure Card Clickable */
.stat-card--clickable {
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.stat-card--clickable:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
}
.topology-link {
  color: #1D4ED8;
  font-weight: 600;
  text-decoration: underline;
}

/* Batch Sub-Nav & CSV Upload */
.batch-subnav {
  display: flex;
  gap: 8px;
  margin-bottom: 14px;
  border-bottom: 1px solid #F3F4F6;
  padding-bottom: 10px;
}

.subnav-pill {
  padding: 6px 12px;
  border-radius: 8px;
  border: 1px solid #E5E7EB;
  background: white;
  color: #4B5563;
  font-size: 12.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.subnav-pill.is-active {
  background: #ECFDF5;
  color: #047857;
  border-color: #A7F3D0;
}

.csv-upload-box {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.csv-template-notice {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  padding: 10px 14px;
}

.csv-dropzone {
  border: 2px dashed #D1D5DB;
  border-radius: 12px;
  padding: 24px;
  text-align: center;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
  background: #FAFAFA;
}

.csv-dropzone:hover {
  border-color: #047857;
  background: #F0FDF4;
}

.drop-title {
  font-size: 13.5px;
  font-weight: 600;
  color: #111827;
  display: block;
}

.drop-sub {
  font-size: 12px;
  color: #6B7280;
  display: block;
}

.csv-chosen-name {
  font-size: 13.5px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.csv-preview-list {
  background: #F9FAFB;
  border-radius: 10px;
  padding: 10px 14px;
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

.form-error {
  color: #DC2626;
  font-size: 12.5px;
  font-weight: 500;
}

.text-danger { color: #DC2626; }
.text-hint { font-size: 12.5px; color: #6B7280; }
.flex-1 { flex: 1; }
.mt-2 { margin-top: 8px; }
.mt-4 { margin-top: 16px; }

/* Buttons */
.btn {
  padding: 8px 16px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s ease;
}

.btn-primary {
  background: #047857;
  color: white;
}

.btn-primary:hover {
  background: #065F46;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-outline {
  border-color: #D1D5DB;
  background: white;
  color: #374151;
}

.btn-outline:hover {
  background: #F3F4F6;
}

.btn-danger {
  background: #DC2626;
  color: white;
}

.btn-danger:hover {
  background: #B91C1C;
}

.btn-outline-danger {
  border-color: #FCA5A5;
  color: #DC2626;
  background: white;
}

.btn-outline-danger:hover {
  background: #FEE2E2;
}

.btn-sm {
  padding: 4px 10px;
  font-size: 12px;
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid #E5E7EB;
  border-top-color: #047857;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 10px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .hero-card--emerald {
    flex-direction: column;
    align-items: flex-start;
  }
  .hero-actions {
    width: 100%;
  }
  .toolbar-card {
    flex-direction: column;
    align-items: stretch;
  }
  .range-inputs {
    grid-template-columns: 1fr;
  }
}

.relabel-box {
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 12px;
  padding: 12px 14px;
}
.toggle-control {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  cursor: pointer;
  font-size: 13px;
  color: #111827;
}
.toggle-control input[type="checkbox"] {
  margin-top: 3px;
  accent-color: #047857;
  width: 16px;
  height: 16px;
}
.d-block { display: block; }
.text-muted { color: #6B7280; font-size: 11.5px; }
</style>

