<template>
  <div class="estate-switcher" :class="{ 'estate-switcher--hero': props.variant === 'hero' }" ref="dropdownRef">
    <!-- Trigger Pill Button -->
    <button
      type="button"
      class="switcher-trigger"
      :class="{ 'is-open': isOpen, 'switcher-trigger--hero': props.variant === 'hero' }"
      @click="toggleDropdown"
      aria-haspopup="true"
      :aria-expanded="isOpen"
    >
      <div class="estate-icon-badge">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/>
          <path d="M9 21V12h6v9"/>
        </svg>
      </div>

      <div class="estate-info-text">
        <span class="estate-name-display">{{ currentEstateName }}</span>
      </div>

      <svg class="switcher-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="m6 9 6 6 6-6"/>
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <transition name="dropdown-fade">
      <div v-if="isOpen" class="switcher-dropdown" role="menu">
        <div class="dropdown-header">
          <span class="dh-title">ESTATE PORTFOLIO</span>
          <span class="dh-count">{{ displayEstates.length }} Communities</span>
        </div>

        <div class="estates-list">
          <button
            v-for="estate in displayEstates"
            :key="estate.id"
            type="button"
            class="estate-item-row"
            :class="{ 'is-active': String(estate.id) === String(auth.activeEstateId) }"
            @click="selectEstate(estate)"
          >
            <div class="item-main">
              <div class="item-name">{{ estate.name }}</div>
              <div v-if="estate.address" class="item-sub">{{ estate.address }}</div>
            </div>

            <div class="item-status">
              <span v-if="String(estate.id) === String(auth.activeEstateId)" class="active-check">✓</span>
              <span class="item-role-chip">{{ estate.role || 'Admin' }}</span>
            </div>
          </button>
        </div>

        <div class="dropdown-footer">
          <button type="button" class="add-estate-btn" @click="openCreateModal">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14M12 5v14"/>
            </svg>
            Add / Register New Estate
          </button>
        </div>
      </div>
    </transition>

    <!-- Modal: Register New Estate -->
    <Teleport to="body">
      <div v-if="createModalOpen" class="modal-backdrop" @click.self="createModalOpen = false">
        <div class="modal-card">
          <div class="modal-header">
            <div>
              <h2 class="modal-title">Add / Register New Estate</h2>
              <p class="modal-subtitle">Register a new gated community or facility to your management portfolio.</p>
            </div>
            <button type="button" class="close-btn" @click="createModalOpen = false">✕</button>
          </div>

          <form @submit.prevent="submitCreateEstate">
            <div class="modal-body">
              <div v-if="createError" class="modal-alert-danger">
                {{ createError }}
              </div>

              <div class="modal-field">
                <label class="modal-label">Estate Name <span class="req">*</span></label>
                <input
                  v-model="createForm.name"
                  type="text"
                  class="modal-input"
                  placeholder="e.g. Sheffy Estate, Lekki Horizon"
                  required
                />
              </div>

              <div class="modal-field">
                <label class="modal-label">Street Address</label>
                <input
                  v-model="createForm.address"
                  type="text"
                  class="modal-input"
                  placeholder="e.g. Plot 14, Admiralty Way, Lekki Phase 1"
                />
              </div>

              <div class="modal-field">
                <label class="modal-label">State</label>
                <StateSelect v-model="createForm.state" placeholder="Select Nigerian State" />
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn-cancel" @click="createModalOpen = false" :disabled="creating">
                Cancel
              </button>
              <button type="submit" class="btn-submit" :disabled="creating || !createForm.name.trim()">
                <span v-if="creating">Registering...</span>
                <span v-else>Register Estate</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore, type EstateWorkspace } from '@/stores/auth'
import { estatesApi } from '@/api'
import StateSelect from '@/components/StateSelect.vue'
import { useToast } from '@/composables/useToast'

const props = withDefaults(defineProps<{
  variant?: 'default' | 'hero'
}>(), {
  variant: 'default'
})

const auth = useAuthStore()
const isOpen = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

const currentEstateName = computed(() => {
  return auth.activeEstateName || 'Primary Estate'
})

// Fallback list if user has only 1 estate so far
const displayEstates = computed<EstateWorkspace[]>(() => {
  if (auth.estates && auth.estates.length > 0) {
    return auth.estates
  }
  return [
    {
      id: auth.activeEstateId || 1,
      name: auth.activeEstateName || 'Victoria Gardens Estate',
      role: auth.role === 'super_admin' ? 'Super Admin' : 'Estate Admin',
      address: 'Lekki Phase 1, Lagos',
    },
  ]
})

function toggleDropdown() {
  isOpen.value = !isOpen.value
}

function selectEstate(estate: EstateWorkspace) {
  auth.setActiveEstate(estate)
  isOpen.value = false
  // Reload current route data seamlessly
  window.dispatchEvent(new CustomEvent('checkpass:estate-switched', { detail: estate }))
}

async function loadEstates() {
  if (!auth.isAuthenticated) return
  try {
    const res = await estatesApi.getAll()
    const rawList = res.data?.estates || res.data
    if (Array.isArray(rawList) && rawList.length > 0) {
      const formatted: EstateWorkspace[] = rawList.map((e: any) => ({
        id: e.id,
        name: e.name,
        role: e.role || (auth.isSuperAdmin ? 'Super Admin' : 'Estate Admin'),
        address: e.address || e.city || '',
      }))
      auth.setEstatesList(formatted)
    }
  } catch (err) {
    // Graceful fallback to cached estate
  }
}

const { showToast } = useToast()

const createModalOpen = ref(false)
const creating = ref(false)
const createError = ref('')
const createForm = ref({
  name: '',
  address: '',
  state: 'Lagos',
})

function openCreateModal() {
  isOpen.value = false
  createError.value = ''
  createForm.value = {
    name: '',
    address: '',
    state: 'Lagos',
  }
  createModalOpen.value = true
}

async function submitCreateEstate() {
  if (!createForm.value.name.trim()) {
    createError.value = 'Estate name is required.'
    return
  }
  createError.value = ''
  creating.value = true

  try {
    const res = await estatesApi.create({
      name: createForm.value.name.trim(),
      address: createForm.value.address.trim() || undefined,
      state: createForm.value.state || undefined,
    })

    const newEstateRaw = res.data?.estate || res.data
    const newEstate: EstateWorkspace = {
      id: newEstateRaw.id,
      name: newEstateRaw.name,
      role: newEstateRaw.role || 'Estate Admin',
      address: newEstateRaw.address || newEstateRaw.state || '',
    }

    // Update estates list in auth store
    const existing = auth.estates || []
    const updatedList = [...existing.filter(e => e.id !== newEstate.id), newEstate]
    auth.setEstatesList(updatedList)

    // Switch to newly created estate
    auth.setActiveEstate(newEstate)

    // Dispatch global event so other dashboard views update
    window.dispatchEvent(new CustomEvent('checkpass:estate-switched', { detail: newEstate }))

    showToast(`Estate "${newEstate.name}" registered successfully!`, 'success')
    createModalOpen.value = false
  } catch (err: any) {
    createError.value = err.response?.data?.message || 'Failed to register estate.'
  } finally {
    creating.value = false
  }
}

function handleClickOutside(e: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target as Node)) {
    isOpen.value = false
  }
}

onMounted(() => {
  loadEstates()
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>

.estate-switcher {
  position: relative;
  display: inline-block;
}

/* Trigger Button */
.switcher-trigger {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 6px 13px 6px 9px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 99px;
  cursor: pointer;
  transition: all 0.15s ease;
  user-select: none;
}

.switcher-trigger:hover,
.switcher-trigger.is-open {
  background: #ffffff;
  border-color: #0a5c38;
  box-shadow: 0 2px 8px rgba(10, 92, 56, 0.12);
}

.estate-icon-badge {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: #ecfdf5;
  color: #0a5c38;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.estate-info-text {
  display: flex;
  align-items: center;
}

.estate-name-display {
  font-size: 13.5px;
  font-weight: 700;
  color: #0f172a;
  max-width: 170px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.switcher-chevron {
  color: #64748b;
  transition: transform 0.2s ease;
}

.switcher-trigger.is-open .switcher-chevron {
  transform: rotate(180deg);
  color: #0a5c38;
}

/* Dropdown Card */
.switcher-dropdown {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 290px;
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 16px 36px -8px rgba(15, 23, 42, 0.18);
  z-index: 1000;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.dropdown-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 16px;
  border-bottom: 1px solid #f1f5f9;
  background: #f8fafc;
}

.dh-title {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: #64748b;
}

.dh-count {
  font-size: 11px;
  font-weight: 700;
  color: #0a5c38;
}

.estates-list {
  max-height: 220px;
  overflow-y: auto;
  padding: 6px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.estate-item-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 9px 12px;
  border-radius: 10px;
  border: none;
  background: transparent;
  cursor: pointer;
  text-align: left;
  transition: background 0.12s ease;
  width: 100%;
}

.estate-item-row:hover {
  background: #f1f5f9;
}

.estate-item-row.is-active {
  background: #ecfdf5;
}

.item-main {
  flex: 1;
  min-width: 0;
  padding-right: 8px;
}

.item-name {
  font-size: 13px;
  font-weight: 700;
  color: #0f172a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-sub {
  font-size: 11px;
  color: #64748b;
  margin-top: 1px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.item-status {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-shrink: 0;
}

.active-check {
  font-size: 13px;
  font-weight: 900;
  color: #0a5c38;
}

.item-role-chip {
  font-size: 9.5px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 6px;
  background: #f1f5f9;
  color: #475569;
}

.estate-item-row.is-active .item-role-chip {
  background: #d1fae5;
  color: #065f46;
}

.dropdown-footer {
  padding: 10px 12px;
  border-top: 1px solid #f1f5f9;
  background: #f8fafc;
}

.add-estate-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  width: 100%;
  padding: 8px 12px;
  border-radius: 9px;
  background: #ffffff;
  border: 1px dashed #cbd5e1;
  color: #0a5c38;
  font-size: 12.5px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.15s ease;
}

.add-estate-btn:hover {
  border-color: #0a5c38;
  background: #ecfdf5;
}

/* Animations */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}

/* ── Hero High-Contrast White Pill Variant ── */
.estate-switcher--hero .switcher-dropdown {
  left: 0;
  right: auto;
}

.switcher-trigger.switcher-trigger--hero {
  background: #ffffff !important;
  border: 1.5px solid rgba(255, 255, 255, 0.95) !important;
  color: #0a5c38 !important;
  padding: 8px 16px 8px 10px;
  border-radius: 99px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.14);
}

.switcher-trigger.switcher-trigger--hero:hover,
.switcher-trigger.switcher-trigger--hero.is-open {
  background: #f0fdf4 !important;
  border-color: #0a5c38 !important;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
}

.switcher-trigger.switcher-trigger--hero .estate-icon-badge {
  background: #ecfdf5 !important;
  color: #0a5c38 !important;
}

.switcher-trigger.switcher-trigger--hero .estate-name-display {
  color: #0a5c38 !important;
  font-weight: 800 !important;
  font-size: 13.5px !important;
}

.switcher-trigger.switcher-trigger--hero .switcher-chevron {
  color: #0a5c38 !important;
}

/* ── In-App Register Estate Modal ── */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.45);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 16px;
}

.modal-card {
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 500px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.22);
  overflow: hidden;
  display: flex;
  flex-direction: column;
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
  width: 28px;
  height: 28px;
  border-radius: 50%;
  cursor: pointer;
  color: #4B5563;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  transition: background 0.15s ease;
}

.close-btn:hover {
  background: #E5E7EB;
}

.modal-body {
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.modal-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.modal-label {
  font-size: 13px;
  font-weight: 600;
  color: #374151;
}

.modal-label .req {
  color: #DC2626;
}

.modal-input {
  width: 100%;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1.5px solid #E5E7EB;
  font-size: 14px;
  color: #111827;
  outline: none;
  background: #F9FAFB;
  box-sizing: border-box;
  transition: border-color 0.15s ease, background-color 0.15s ease;
}

.modal-input:focus {
  border-color: #0A5C38;
  background-color: #FFFFFF;
}

.modal-alert-danger {
  background: #FEF2F2;
  border: 1px solid #FCA5A5;
  color: #B91C1C;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 13px;
  font-weight: 500;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #E5E7EB;
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  background: #F9FAFB;
}

.btn-cancel {
  padding: 9px 18px;
  border-radius: 10px;
  border: 1px solid #D1D5DB;
  background: #FFFFFF;
  color: #374151;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-cancel:hover {
  background: #F3F4F6;
}

.btn-submit {
  padding: 9px 20px;
  border-radius: 10px;
  border: none;
  background: #0A5C38;
  color: #FFFFFF;
  font-size: 13.5px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(10, 92, 56, 0.25);
  transition: all 0.15s ease;
}

.btn-submit:hover:not(:disabled) {
  background: #064E3B;
}

.btn-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

</style>

