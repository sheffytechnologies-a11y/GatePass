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
        <h1 class="setup-hero-title">Property Layout & Structure Setup</h1>
        <p class="setup-hero-subtitle">
          Define how physical properties and addresses are structured across your estate. Customize template tiers or create new custom templates.
        </p>
      </div>
      <div class="setup-hero-actions">
        <button type="button" class="btn btn-white-hero" @click="openCreateTemplateModal">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Add Property Template
        </button>
      </div>
    </section>

    <!-- Template Cards Grid -->
    <div class="templates-section">
      <div class="section-heading-row">
        <div>
          <h2 class="section-title">Available Layout Templates</h2>
          <p class="section-subtitle">Choose or customize property templates to structure addresses and units across your estate.</p>
        </div>
        <span class="templates-count">{{ allTemplates.length }} templates available</span>
      </div>

      <div class="templates-grid">
        <div
          v-for="tpl in allTemplates"
          :key="tpl.id"
          class="template-card"
        >
          <div class="tpl-header">
            <div class="tpl-title-group">
              <h3 class="tpl-title">{{ tpl.label }}</h3>
              <span v-if="tpl.isCustom" class="tpl-custom-badge">Custom</span>
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
                type="button"
                class="btn btn-sm btn-outline-edit"
                @click="openEditTemplateModal(tpl)"
                title="Edit this template"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Edit
              </button>
              <button
                v-if="tpl.isCustom"
                type="button"
                class="btn btn-sm btn-outline-danger"
                @click="deleteCustomTemplate(tpl.id)"
                title="Delete custom template"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>
                </svg>
                Delete
              </button>
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
            <h2 class="modal-title">{{ isEditing ? 'Edit Property Template' : 'Add Property Template' }}</h2>
            <p class="modal-subtitle">Specify tier levels and naming for this property layout.</p>
          </div>
          <button type="button" class="close-btn" @click="modalOpen = false">✕</button>
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
                  type="button"
                  class="btn-remove-tier"
                  @click="removeTierFromEditing(idx)"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline" @click="modalOpen = false">Cancel</button>
          <button type="button" class="btn btn-primary" :disabled="savingModal" @click="saveTemplate">
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
const defaultPresets = ref<TemplateItem[]>([
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
])

const customTemplates = ref<TemplateItem[]>([])

// Modal State
const modalOpen = ref(false)
const isEditing = ref(false)
const savingModal = ref(false)
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
  return [...defaultPresets.value, ...customTemplates.value]
})

// Load custom templates
async function loadStructure() {
  if (!activeEstateId.value) return

  // Load local customized defaults if any
  const storedDefaults = localStorage.getItem(`estate_default_presets_${activeEstateId.value}`)
  if (storedDefaults) {
    try {
      defaultPresets.value = JSON.parse(storedDefaults)
    } catch {}
  }

  try {
    const res = await propertiesApi.getStructure(activeEstateId.value)
    if (res.data?.structure?.custom_templates && Array.isArray(res.data.structure.custom_templates)) {
      customTemplates.value = res.data.structure.custom_templates
    } else {
      const stored = localStorage.getItem(`estate_custom_templates_${activeEstateId.value}`)
      if (stored) {
        try {
          customTemplates.value = JSON.parse(stored)
        } catch {}
      }
    }
  } catch (err) {
    const stored = localStorage.getItem(`estate_custom_templates_${activeEstateId.value}`)
    if (stored) {
      try {
        customTemplates.value = JSON.parse(stored)
      } catch {}
    }
  }
}

// Open modal to create a new template
function openCreateTemplateModal() {
  isEditing.value = false
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
  modalOpen.value = true
}

// Open modal to edit an existing template
function openEditTemplateModal(tpl: TemplateItem) {
  isEditing.value = true
  editingTemplate.value = {
    id: tpl.id,
    label: tpl.label,
    description: tpl.description || '',
    levels: JSON.parse(JSON.stringify(tpl.levels)),
    isCustom: tpl.isCustom ?? false,
  }
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

  for (const lvl of editingTemplate.value.levels) {
    if (!lvl.name.trim()) {
      showToast('All tier levels must have a name (e.g. Street, House).', 'error')
      return
    }
  }

  savingModal.value = true
  const tplId = editingTemplate.value.id
  const defIdx = defaultPresets.value.findIndex(p => p.id === tplId)

  if (defIdx !== -1) {
    defaultPresets.value[defIdx].label = editingTemplate.value.label
    defaultPresets.value[defIdx].description = editingTemplate.value.description
    defaultPresets.value[defIdx].levels = JSON.parse(JSON.stringify(editingTemplate.value.levels))
    localStorage.setItem(`estate_default_presets_${activeEstateId.value}`, JSON.stringify(defaultPresets.value))
  } else {
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

  // Sync custom templates to backend
  try {
    const payload = {
      preset: tplId,
      levels: editingTemplate.value.levels,
      custom_templates: customTemplates.value,
    }
    await propertiesApi.updateStructure(activeEstateId.value, payload)
  } catch {}

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

  try {
    const payload = {
      preset: 'lane_house_flat',
      levels: defaultPresets.value[0]?.levels || [{ level: 1, name: 'Lane' }, { level: 2, name: 'House' }],
      custom_templates: customTemplates.value,
    }
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
  border-color: #0A5C38;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
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
  padding-top: 14px;
  border-top: 1px solid #F3F4F6;
}

.tpl-footer-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-outline-edit {
  background: #ffffff;
  border: 1.5px solid #D1D5DB;
  color: #1F2937;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 10px;
  font-size: 13px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-outline-edit:hover {
  background: #F9FAFB;
  border-color: #0A5C38;
  color: #0A5C38;
}

.btn-outline-danger {
  background: #ffffff;
  border: 1.5px solid #FCA5A5;
  color: #DC2626;
  padding: 8px 14px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-outline-danger:hover {
  background: #FEF2F2;
  border-color: #EF4444;
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
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 540px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  animation: modalPop 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-lg {
  max-width: 620px;
}

@keyframes modalPop {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(8px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

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
  width: 30px;
  height: 30px;
  border-radius: 50%;
  cursor: pointer;
  color: #4B5563;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: all 0.15s ease;
}

.close-btn:hover {
  background: #E5E7EB;
  color: #111827;
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

/* Form Inputs inside Modal */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.form-input {
  width: 100%;
  border: 1px solid #D1D5DB;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 14px;
  color: #111827;
  outline: none;
  transition: all 0.15s ease;
  box-sizing: border-box;
}

.form-input:focus {
  border-color: #0A5C38;
  box-shadow: 0 0 0 3px rgba(10, 92, 56, 0.12);
}

.mt-3 {
  margin-top: 12px;
}

.flex-1 {
  flex: 1;
}

/* Buttons */
.btn {
  padding: 9px 18px;
  border-radius: 10px;
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s ease;
}

.btn-primary {
  background: #0A5C38;
  color: white;
}

.btn-primary:hover {
  background: #064E3B;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-outline {
  border-color: #D1D5DB;
  background: white;
  color: #374151;
}

.btn-outline:hover {
  background: #F3F4F6;
  border-color: #9CA3AF;
}

/* Tiers Editor */
.tiers-section {
  margin-top: 16px;
}

.tiers-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.tiers-label {
  margin: 0;
  font-size: 13.5px;
  font-weight: 600;
  color: #374151;
}

.btn-add-tier-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
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
  gap: 10px;
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

.btn-remove-tier {
  height: 40px;
  padding: 0 14px;
  border-radius: 8px;
  font-size: 12.5px;
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

.btn-remove-tier:hover {
  background: #FEF2F2;
  border-color: #EF4444;
}

@media (max-width: 768px) {
  .setup-hero-card {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
