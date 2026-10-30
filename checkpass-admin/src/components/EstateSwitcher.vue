<template>
  <div class="estate-switcher" ref="dropdownRef">
    <!-- Trigger Pill Button -->
    <button
      type="button"
      class="switcher-trigger"
      :class="{ 'is-open': isOpen }"
      @click="toggleDropdown"
      aria-haspopup="true"
      :aria-expanded="isOpen"
    >
      <div class="estate-icon-badge">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="16" height="20" x="4" y="2" rx="2" ry="2"/>
          <path d="M9 22v-4h6v4"/>
          <path d="M8 6h.01M16 6h.01M8 10h.01M16 10h.01M8 14h.01M16 14h.01"/>
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
          <router-link to="/register/estate" class="add-estate-btn" @click="isOpen = false">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14M12 5v14"/>
            </svg>
            Add / Register New Estate
          </router-link>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore, type EstateWorkspace } from '@/stores/auth'
import { estatesApi } from '@/api'

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
</style>
