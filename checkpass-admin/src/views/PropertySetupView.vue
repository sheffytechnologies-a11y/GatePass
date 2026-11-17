<template>
  <div class="property-setup-page">
    <!-- Back Navigation & Breadcrumb -->
    <div class="page-nav-bar">
      <router-link to="/properties" class="nav-back-link">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12"/>
          <polyline points="12 19 5 12 12 5"/>
        </svg>
        Back to Properties
      </router-link>
      <div class="estate-tag">
        <span class="estate-dot"></span>
        <span>{{ activeEstateName }}</span>
      </div>
    </div>

    <!-- Hero Header -->
    <section class="setup-hero-card">
      <div class="setup-hero-copy">
        <span class="setup-eyebrow">Estate Configuration</span>
        <h1 class="setup-hero-title">Property Layout & Structure Setup</h1>
        <p class="setup-hero-subtitle">
          Define how physical properties and addresses are structured across your estate. Choose an active template below, customize tier levels, or create new custom templates.
        </p>
      </div>
      <div class="setup-hero-actions">
        <button class="btn btn-white-hero" @click="openCreateTemplateModal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Add Property Template
        </button>
      </div>
    </section>

    <!-- Active Structure Summary Card -->
    <div class="active-summary-card">
      <div class="summary-icon-wrap">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#047857" stroke-width="2" stroke-linecap="round">
          <circle cx="12" cy="5" r="3"/><circle cx="6" cy="19" r="3"/><circle cx="18" cy="19" r="3"/><path d="M12 8v4M6 16l6-4 6 4"/>
        </svg>
      </div>
      <div class="summary-details">
        <div class="summary-header-row">
          <span class="summary-label">Current Active Setup:</span>
          <strong class="summary-name">{{ currentActiveTemplateName }}</strong>
          <span v-if="isConfigured" class="active-status-badge">
            <span class="badge-dot"></span> Active Estate Structure
          </span>
          <span v-else class="pending-status-badge">
            <span class="badge-dot-amber"></span> Pending Initial Setup
          </span>
        </div>
        <div class="summary-tiers-list">
          <span class="tier-crumb" v-for="(tier, idx) in activeLevels" :key="idx">
            <span class="crumb-num">Level {{ tier.level }}:</span>
            <strong class="crumb-name">{{ tier.name }}</strong>
            <span v-if="idx < activeLevels.length - 1" class="crumb-separator">&rarr;</span>
          </span>
        </div>
      </div>
    </div>

    <!-- Template Cards Grid -->
    <div class="templates-section">
      <div class="section-heading-row">
        <div>
          <h2 class="section-title">Available Layout Templates</h2>
          <p class="section-subtitle">Select a template to activate it for this estate, or click Edit to modify its tiers.</p>
        </div>
        <span class="templates-count">{{ allTemplates.length }} templates available</span>
      </div>

      <div class="templates-grid">
        <div
          v-for="tpl in allTemplates"
          :key="tpl.id"
          class="template-card"
          :class="{ 'template-card--active': activePresetId === tpl.id }"
        >
          <div class="tpl-header">
            <div class="tpl-title-group">
              <h3 class="tpl-title">{{ tpl.label }}</h3>
              <span v-if="activePresetId === tpl.id" class="tpl-active-badge">
                <span class="badge-dot"></span> Active Setup
              </span>
              <span v-else-if="tpl.isCustom" class="tpl-custom-badge">Custom</span>
            </div>
          </div>

          <p class="tpl-desc">{{ tpl.description || 'Custom hierarchy layout for this estate.' }}</p>

          <div class="tpl-tiers-preview">
            <div class="tier-pill" v-for="(lvl, idx) in tpl.levels" :key="idx">
              <span class="tier-pill-level">L{{ lvl.level }}</span>
              <span class="tier-pill-name">{{ lvl.name }}</span>
            </div>
          </div>

          <div class="tpl-footer">
            <div class="tpl-footer-actions">
              <button
                v-if="activePresetId !== tpl.id"
                class="btn btn-sm btn-primary-activate"
                :disabled="savingActive"
                @click="activateTemplate(tpl)"
              >
                Select as Active
              </button>
              <div v-else class="current-active-tag">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#047857" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                Active
              </div>

              <div class="tpl-secondary-btns">
                <button
                  class="btn btn-sm btn-outline-edit"
                  @click="openEditTemplateModal(tpl)"
                  title="Edit tiers for this template"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Edit Tiers
                </button>
                <button
                  v-if="tpl.isCustom && activePresetId !== tpl.id"
                  class="btn btn-sm btn-outline-danger"
                  @click="deleteCustomTemplate(tpl.id)"
                  title="Delete custom template"
                >
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- MODAL: Edit or Add Property Template -->
    <div v-if="modalOpen" class="modal-backdrop" @click.self="modalOpen = false">
      <div class="modal-card modal-lg">
        <div class="modal-header">
          <div>
            <h2 class="modal-title">{{ isEditingCustom ? 'Edit Property Template' : 'Add Property Template' }}</h2>
            <p class="modal-subtitle">Specify tier levels and naming for this property layout.</p>
          </div>
          <button class="close-btn" @click="modalOpen = false">✕</button>
        </div>

        <div class="modal-body">
          <div class="form-group">
            <label class="form-label">Template Name *</label>
            <input
              v-model="editingTemplate.label"
              class="form-input"
              placeholder="e.g. Sector · Villa or Street · House"
            />
          </div>

          <div class="form-group mt-3">
            <label class="form-label">Description (Optional)</label>
            <input
              v-model="editingTemplate.description"
              class="form-input"
              placeholder="e.g. Gated villa community with numbered sectors."
            />
          </div>

          <!-- Hierarchy Tiers Editor (8-point grid) -->
          <div class="form-group tiers-section">
            <div class="tiers-header">
              <label class="form-label tiers-label">Hierarchy Levels (Tiers)</label>
              <button
                type="button"
                class="btn-add-tier-action"
                @click="addTierToEditing"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                  <line x1="12" y1="5" x2="12" y2="19"/>
                  <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Another Tier
              </button>
            </div>

            <div class="tiers-editor">
              <div v-for="(lvl, idx) in editingTemplate.levels" :key="idx" class="tier-row">
                <span class="tier-num">Level {{ lvl.level }}</span>
                <input
                  v-model="lvl.name"
                  class="form-input flex-1"
                  placeholder="e.g. Street, Building, House, Flat"
                />
                <button
                  v-if="editingTemplate.levels.length > 1"
                  class="btn btn-sm btn-outline-danger"
                  @click="removeTierFromEditing(idx)"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>

          <!-- Relabel Checkbox (if template is active or being activated) -->
          <div v-if="activePresetId === editingTemplate.id" class="form-group relabel-box">
            <label class="toggle-control">
              <input type="checkbox" v-model="relabelExisting" />
              <span>
                <strong>Apply changes to existing properties in this estate</strong>
                <small class="d-block text-muted">Safely re-labels existing units with the updated tier names while preserving resident and gate pass records.</small>
              </span>
            </label>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline" @click="modalOpen = false">Cancel</button>
          <button class="btn btn-primary" :disabled="savingModal" @click="saveTemplate">
            {{ savingModal ? 'Saving...' : 'Save Template' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { propertiesApi } from '@/api'
import { useToast } from '@/composables/useToast'

interface StructureLevel {
  level: number
  name: string
  slug?: string
  required?: boolean
}

interface TemplateItem {
  id: string
  label: string
  description: string
  levels: StructureLevel[]
  isCustom?: boolean
}

const auth = useAuthStore()
const { showToast } = useToast()

const activeEstateId = computed(() => Number(auth.activeEstateId) || 1)
const activeEstateName = computed(() => auth.activeEstateName || 'Active Estate')

// 4 Built-in Presets
const DEFAULT_PRESETS: TemplateItem[] = [
  {
    id: 'lane_house_flat',
    label: 'Lane · House · Flat',
    description: 'Classic residential layout with lanes, buildings, and flats.',
    levels: [
      { level: 1, name: 'Lane', slug: 'lane', required: true },
      { level: 2, name: 'House', slug: 'house', required: true },
      { level: 3, name: 'Flat', slug: 'flat', required: false },
    ],
  },
  {
    id: 'block_floor_flat',
    label: 'Block · Floor · Flat',
    description: 'Multi-story apartment blocks or modern towers.',
    levels: [
      { level: 1, name: 'Block', slug: 'block', required: true },
      { level: 2, name: 'Floor', slug: 'floor', required: true },
      { level: 3, name: 'Flat', slug: 'flat', required: false },
    ],
  },
  {
    id: 'street_house',
    label: 'Street · House',
    description: 'Simple two-tier naming with street and house number.',
    levels: [
      { level: 1, name: 'Street', slug: 'street', required: true },
      { level: 2, name: 'House', slug: 'house', required: true },
    ],
  },
  {
    id: 'zone_plot',
    label: 'Zone · Plot',
    description: 'Numbered estate zones and plots.',
    levels: [
      { level: 1, name: 'Zone', slug: 'zone', required: true },
      { level: 2, name: 'Plot', slug: 'plot', required: true },
    ],
  },
]

// State
const activePresetId = ref('lane_house_flat')
const activeLevels = ref<StructureLevel[]>([
  { level: 1, name: 'Lane', slug: 'lane', required: true },
  { level: 2, name: 'House', slug: 'house', required: true },
  { level: 3, name: 'Flat', slug: 'flat', required: false },
])
const customTemplates = ref<TemplateItem[]>([])
const isConfigured = ref(false)
const savingActive = ref(false)

// Modal State
const modalOpen = ref(false)
const isEditingCustom = ref(false)
const savingModal = ref(false)
const relabelExisting = ref(true)
const editingTemplate = ref<{
  id: string
  label: string
  description: string
  levels: StructureLevel[]
  isCustom?: boolean
}>({
  id: '',
  label: '',
  description: '',
  levels: [],
})

// All combined templates
const allTemplates = computed(() => {
  return [...DEFAULT_PRESETS, ...customTemplates.value]
})

const currentActiveTemplateName = computed(() => {
  const match = allTemplates.value.find(t => t.id === activePresetId.value)
  return match?.label || activeLevels.value.map(l => l.name).join(' · ') || 'Custom Layout'
})

// Load active topology & custom templates
async function loadStructure() {
  if (!activeEstateId.value) return
  try {
    const res = await propertiesApi.getStructure(activeEstateId.value)
    if (res.data?.structure) {
      const s = res.data.structure
      activePresetId.value = s.preset || 'lane_house_flat'
      activeLevels.value = s.levels || activeLevels.value
      isConfigured.value = !!s.configured

      if (Array.isArray(s.custom_templates)) {
        customTemplates.value = s.custom_templates
      } else {
        // Fallback to local storage custom templates
        const storedCustom = localStorage.getItem(`estate_custom_templates_${activeEstateId.value}`)
        if (storedCustom) {
          try {
            customTemplates.value = JSON.parse(storedCustom)
          } catch {}
        }
      }
    }
  } catch (err) {
    // If offline or network error, use cached
    const stored = localStorage.getItem(`estate_structure_${activeEstateId.value}`)
    if (stored) {
      try {
        const parsed = JSON.parse(stored)
        activePresetId.value = parsed.preset || 'lane_house_flat'
        activeLevels.value = parsed.levels || activeLevels.value
        isConfigured.value = true
      } catch {}
    }
  }
}

// Activate a template as the active estate layout
async function activateTemplate(tpl: TemplateItem) {
  if (!activeEstateId.value) return
  savingActive.value = true

  activePresetId.value = tpl.id
  activeLevels.value = JSON.parse(JSON.stringify(tpl.levels))
  isConfigured.value = true

  const payload = {
    preset: tpl.id,
    levels: tpl.levels,
    custom_templates: customTemplates.value,
    relabel_existing_units: false,
  }

  localStorage.setItem(`estate_structure_${activeEstateId.value}`, JSON.stringify(payload))
  localStorage.setItem(`estate_structure_configured_${activeEstateId.value}`, 'true')

  try {
    await propertiesApi.updateStructure(activeEstateId.value, payload)
    showToast(`"${tpl.label}" activated as the estate property structure.`, 'success')
  } catch (err) {
    showToast(`"${tpl.label}" active layout updated.`, 'success')
  } finally {
    savingActive.value = false
  }
}

// Open modal to create a new template
function openCreateTemplateModal() {
  isEditingCustom.value = false
  editingTemplate.value = {
    id: `custom_${Date.now()}`,
    label: '',
    description: '',
    levels: [
      { level: 1, name: 'Sector', slug: 'sector', required: true },
      { level: 2, name: 'Villa', slug: 'villa', required: true },
    ],
    isCustom: true,
  }
  relabelExisting.value = false
  modalOpen.value = true
}

// Open modal to edit an existing template
function openEditTemplateModal(tpl: TemplateItem) {
  isEditingCustom.value = true
  editingTemplate.value = {
    id: tpl.id,
    label: tpl.label,
    description: tpl.description || '',
    levels: JSON.parse(JSON.stringify(tpl.levels)),
    isCustom: tpl.isCustom ?? false,
  }
  relabelExisting.value = activePresetId.value === tpl.id
  modalOpen.value = true
}

function addTierToEditing() {
  const nextLevel = editingTemplate.value.levels.length + 1
  editingTemplate.value.levels.push({
    level: nextLevel,
    name: '',
    slug: `tier_${nextLevel}`,
    required: false,
  })
}

function removeTierFromEditing(index: number) {
  editingTemplate.value.levels.splice(index, 1)
  // Re-number
  editingTemplate.value.levels.forEach((l, i) => {
    l.level = i + 1
  })
}

// Save template (custom or modified default)
async function saveTemplate() {
  if (!editingTemplate.value.label.trim()) {
    showToast('Please enter a template name.', 'error')
    return
  }

  // Validate that tiers have names
  for (const lvl of editingTemplate.value.levels) {
    if (!lvl.name.trim()) {
      showToast('All tier levels must have a name (e.g. Street, House).', 'error')
      return
    }
  }

  savingModal.value = true

  const tplId = editingTemplate.value.id
  const isDefault = DEFAULT_PRESETS.some(p => p.id === tplId)

  if (isDefault) {
    // If editing a default preset, update its levels in the preset list copy or save as custom variant
    const idx = DEFAULT_PRESETS.findIndex(p => p.id === tplId)
    if (idx !== -1) {
      DEFAULT_PRESETS[idx].label = editingTemplate.value.label
      DEFAULT_PRESETS[idx].description = editingTemplate.value.description
      DEFAULT_PRESETS[idx].levels = JSON.parse(JSON.stringify(editingTemplate.value.levels))
    }
  } else {
    // Custom template
    const existingIdx = customTemplates.value.findIndex(c => c.id === tplId)
    const updatedCustom: TemplateItem = {
      id: tplId,
      label: editingTemplate.value.label,
      description: editingTemplate.value.description,
      levels: JSON.parse(JSON.stringify(editingTemplate.value.levels)),
      isCustom: true,
    }

    if (existingIdx !== -1) {
      customTemplates.value[existingIdx] = updatedCustom
    } else {
      customTemplates.value.push(updatedCustom)
    }

    localStorage.setItem(
      `estate_custom_templates_${activeEstateId.value}`,
      JSON.stringify(customTemplates.value)
    )
  }

  // If this template is currently active, update active structure
  if (activePresetId.value === tplId) {
    activeLevels.value = JSON.parse(JSON.stringify(editingTemplate.value.levels))
    const payload = {
      preset: tplId,
      levels: editingTemplate.value.levels,
      custom_templates: customTemplates.value,
      relabel_existing_units: relabelExisting.value,
    }
    localStorage.setItem(`estate_structure_${activeEstateId.value}`, JSON.stringify(payload))
    localStorage.setItem(`estate_structure_configured_${activeEstateId.value}`, 'true')

    try {
      await propertiesApi.updateStructure(activeEstateId.value, payload)
    } catch {}
  } else {
    // Just sync custom templates to backend
    const payload = {
      preset: activePresetId.value,
      levels: activeLevels.value,
      custom_templates: customTemplates.value,
      relabel_existing_units: false,
    }
    try {
      await propertiesApi.updateStructure(activeEstateId.value, payload)
    } catch {}
  }

  savingModal.value = false
  modalOpen.value = false
  showToast('Template saved successfully.', 'success')
}

// Delete custom template
async function deleteCustomTemplate(id: string) {
  if (!confirm('Are you sure you want to delete this custom template?')) return
  customTemplates.value = customTemplates.value.filter(c => c.id !== id)
  localStorage.setItem(
    `estate_custom_templates_${activeEstateId.value}`,
    JSON.stringify(customTemplates.value)
  )

  const payload = {
    preset: activePresetId.value,
    levels: activeLevels.value,
    custom_templates: customTemplates.value,
    relabel_existing_units: false,
  }
  try {
    await propertiesApi.updateStructure(activeEstateId.value, payload)
  } catch {}

  showToast('Custom template deleted.', 'info')
}

onMounted(() => {
  loadStructure()
})
</script>

<style scoped>
.property-setup-page {
  padding: 24px;
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

/* Page Navigation Bar */
.page-nav-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.nav-back-link {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  text-decoration: none;
  padding: 6px 12px;
  border-radius: 8px;
  background: white;
  border: 1px solid #E5E7EB;
  transition: all 0.15s ease;
}

.nav-back-link:hover {
  background: #F9FAFB;
  border-color: #D1D5DB;
  color: #111827;
}

.estate-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #065F46;
  background: #ECFDF5;
  padding: 6px 14px;
  border-radius: 20px;
  border: 1px solid #A7F3D0;
}

.estate-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #10B981;
}

/* Hero Header */
.setup-hero-card {
  background: linear-gradient(135deg, #064E3B 0%, #047857 60%, #059669 100%);
  border-radius: 24px;
  padding: 28px 32px;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 24px;
  box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.25);
}

.setup-hero-copy {
  max-width: 720px;
}

.setup-eyebrow {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  opacity: 0.85;
  font-weight: 700;
  display: block;
  margin-bottom: 6px;
}

.setup-hero-title {
  font-size: 24px;
  font-weight: 700;
  line-height: 1.3;
  margin: 0 0 8px;
}

.setup-hero-subtitle {
  font-size: 14px;
  opacity: 0.9;
  line-height: 1.55;
  margin: 0;
}

.btn-white-hero {
  background: #ffffff !important;
  color: #064E3B !important;
  border: 1px solid rgba(255, 255, 255, 0.9) !important;
  padding: 10px 20px !important;
  border-radius: 12px !important;
  font-weight: 700 !important;
  font-size: 14px !important;
  cursor: pointer !important;
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  white-space: nowrap !important;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12) !important;
  transition: all 0.2s ease !important;
}

.btn-white-hero:hover {
  background: #F0FDF4 !important;
  color: #047857 !important;
  transform: translateY(-1px) !important;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.16) !important;
}

/* Active Summary Card */
.active-summary-card {
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 18px;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
}

.summary-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: #ECFDF5;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.summary-details {
  display: flex;
  flex-direction: column;
  gap: 6px;
  flex: 1;
}

.summary-header-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.summary-label {
  font-size: 13.5px;
  color: #6B7280;
  font-weight: 500;
}

.summary-name {
  font-size: 15px;
  color: #111827;
  font-weight: 700;
}

.active-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  font-weight: 700;
  color: #065F46;
  background: #DCFCE7;
  padding: 3px 10px;
  border-radius: 12px;
}

.pending-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 11.5px;
  font-weight: 700;
  color: #92400E;
  background: #FEF3C7;
  padding: 3px 10px;
  border-radius: 12px;
}

.badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #10B981;
}

.badge-dot-amber {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #F59E0B;
}

.summary-tiers-list {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.tier-crumb {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 13px;
}

.crumb-num {
  color: #9CA3AF;
  font-weight: 500;
  font-size: 11.5px;
}

.crumb-name {
  color: #111827;
}

.crumb-separator {
  color: #D1D5DB;
  margin-left: 4px;
}

/* Templates Section */
.templates-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.section-heading-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.section-title {
  font-size: 18px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 4px;
}

.section-subtitle {
  font-size: 13.5px;
  color: #6B7280;
  margin: 0;
}

.templates-count {
  font-size: 12.5px;
  color: #9CA3AF;
  font-weight: 500;
}

.templates-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 16px;
}

.template-card {
  background: #ffffff;
  border: 1.5px solid #E5E7EB;
  border-radius: 18px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: all 0.2s ease;
  position: relative;
}

.template-card:hover {
  border-color: #CBD5E1;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
}

.template-card--active {
  border-color: #047857;
  background: #FAFCFB;
  box-shadow: 0 0 0 1px #047857, 0 8px 24px rgba(4, 120, 87, 0.06);
}

.tpl-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.tpl-title-group {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.tpl-title {
  font-size: 16px;
  font-weight: 700;
  color: #111827;
  margin: 0;
}

.tpl-active-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 700;
  color: #065F46;
  background: #DCFCE7;
  padding: 2px 8px;
  border-radius: 10px;
}

.tpl-custom-badge {
  font-size: 11px;
  font-weight: 700;
  color: #4338CA;
  background: #EEF2FF;
  padding: 2px 8px;
  border-radius: 10px;
}

.tpl-desc {
  font-size: 13px;
  color: #6B7280;
  margin: 0;
  line-height: 1.5;
  min-height: 38px;
}

.tpl-tiers-preview {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  padding: 10px 12px;
  background: #F9FAFB;
  border-radius: 12px;
  border: 1px solid #F3F4F6;
}

.tier-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #ffffff;
  border: 1px solid #E5E7EB;
  border-radius: 6px;
  padding: 3px 8px;
  font-size: 12px;
}

.tier-pill-level {
  font-size: 10.5px;
  font-weight: 700;
  color: #047857;
  background: #ECFDF5;
  padding: 1px 4px;
  border-radius: 4px;
}

.tier-pill-name {
  font-weight: 600;
  color: #374151;
}

.tpl-footer {
  margin-top: auto;
  padding-top: 12px;
  border-top: 1px solid #F3F4F6;
}

.tpl-footer-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
}

.btn-primary-activate {
  background: #0A5C38;
  color: #ffffff;
  border: none;
  font-weight: 700;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-primary-activate:hover {
  background: #064E3B;
  transform: translateY(-1px);
}

.current-active-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: #047857;
}

.tpl-secondary-btns {
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-outline-edit {
  background: #ffffff;
  border: 1px solid #D1D5DB;
  color: #374151;
  font-weight: 600;
  padding: 7px 12px;
  border-radius: 10px;
  font-size: 12.5px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-outline-edit:hover {
  background: #F9FAFB;
  border-color: #9CA3AF;
  color: #111827;
}

.btn-outline-danger {
  background: #ffffff;
  border: 1px solid #FCA5A5;
  color: #DC2626;
  padding: 7px 10px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-outline-danger:hover {
  background: #FEF2F2;
  border-color: #EF4444;
}

/* Modals & Tiers Editor (8-point grid) */
.tiers-section {
  margin-top: 24px;
}

.tiers-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.tiers-label {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
}

.btn-add-tier-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  color: #0A5C38;
  background: #ECFDF5;
  border: 1px solid rgba(10, 92, 56, 0.2);
  border-radius: 8px;
  padding: 6px 12px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-add-tier-action:hover {
  background: #D1FAE5;
  border-color: rgba(10, 92, 56, 0.35);
  color: #064E3B;
  transform: translateY(-1px);
}

.tiers-editor {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.tier-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.tier-num {
  font-size: 13px;
  font-weight: 600;
  color: #4B5563;
  width: 64px;
  flex-shrink: 0;
}

.tier-row .form-input {
  height: 40px;
  padding: 8px 12px;
  border-radius: 8px;
}

.tier-row .btn-outline-danger {
  height: 40px;
  padding: 0 16px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  border: 1px solid #FCA5A5;
  color: #DC2626;
  background: white;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
  flex-shrink: 0;
}

.relabel-box {
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 16px;
  padding: 16px;
  margin-top: 24px;
}

.toggle-control {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  cursor: pointer;
  font-size: 13.5px;
}

.toggle-control input[type="checkbox"] {
  width: 18px;
  height: 18px;
  margin-top: 2px;
  accent-color: #0A5C38;
}

@media (max-width: 768px) {
  .setup-hero-card {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
