<template>
  <ion-page>
    <ion-tabs>
      <ion-router-outlet />
      <ion-tab-bar
        v-if="showTabBar"
        slot="bottom"
        class="custom-tab-bar"
        :class="{ 'security-bar': auth.isSecurityUser }"
        :selected-tab="activeTab"
      >

        <!-- SECURITY TABS: Home, Scan (Checkpass Logo FAB), Profile -->
        <template v-if="auth.isSecurityUser">
          <!-- 1. Home -->
          <ion-tab-button
            tab="home"
            href="/tabs/home"
            :class="{ 'is-active': activeTab === 'home' }"
          >
            <div class="tab-pill" :class="{ 'pill-active': activeTab === 'home' }">
              <PhHouse :size="20" :weight="activeTab === 'home' ? 'fill' : 'regular'" />
              <span class="tab-label">Home</span>
            </div>
          </ion-tab-button>

          <!-- 2. Centre SCAN (Checkpass Logo) FAB Button -->
          <ion-tab-button
            tab="scan"
            class="tab-create tab-scan"
            aria-label="Scan Visitor QR Pass"
            @click.stop.prevent="triggerSecurityScan"
          >
            <div class="create-fab scan-fab" title="Scan Visitor QR Pass">
              <svg width="24" height="24" viewBox="0 0 119 119" fill="none" xmlns="http://www.w3.org/2000/svg" class="checkpass-fab-logo">
                <path d="M21.6807 96.6416H55.5674V118.174H42.3701C30.6745 118.174 21.1566 108.849 20.8457 97.2285C9.34033 96.8386 0.108192 87.4159 0.0576172 75.8047L0 62.6084L21.5322 62.5146L21.6807 96.6416ZM118.079 62.6084L118.021 75.8047C117.971 87.4163 108.738 96.8391 97.2324 97.2285C96.9215 108.849 87.4046 118.174 75.709 118.174H62.5127V96.6416H96.3984L96.5469 62.5146L118.079 62.6084ZM55.5674 21.5322H21.6807L21.5322 55.6592L0 55.5654L0.0576172 42.3691C0.108192 30.7579 9.34032 21.3343 20.8457 20.9443C21.1571 9.32425 30.6748 0 42.3701 0H55.5674V21.5322ZM75.71 0C87.405 0.000315153 96.923 9.32446 97.2344 20.9443C108.739 21.3347 117.971 30.7582 118.021 42.3691L118.079 55.5654L96.5479 55.6592L96.3994 21.5322H62.5127V0H75.71Z" fill="#FFFFFF"/>
              </svg>
            </div>
          </ion-tab-button>

          <!-- 3. Profile -->
          <ion-tab-button
            tab="profile"
            href="/tabs/profile"
            :class="{ 'is-active': activeTab === 'profile' }"
          >
            <div class="tab-pill" :class="{ 'pill-active': activeTab === 'profile' }">
              <PhUser :size="20" :weight="activeTab === 'profile' ? 'fill' : 'regular'" />
              <span class="tab-label">Profile</span>
            </div>
          </ion-tab-button>
        </template>

        <!-- RESIDENT TABS: Home, Dues, Create (+), Emergency, Profile -->
        <template v-else>
          <!-- 1. Home -->
          <ion-tab-button
            tab="home"
            href="/tabs/home"
            :class="{ 'is-active': activeTab === 'home' }"
          >
            <div class="tab-pill" :class="{ 'pill-active': activeTab === 'home' }">
              <PhHouse :size="20" :weight="activeTab === 'home' ? 'fill' : 'regular'" />
              <span class="tab-label">Home</span>
            </div>
          </ion-tab-button>

          <!-- 2. Fees / Dues -->
          <ion-tab-button
            tab="fees"
            href="/tabs/fees"
            :class="{ 'is-active': activeTab === 'fees' }"
          >
            <div class="tab-pill" :class="{ 'pill-active': activeTab === 'fees' }">
              <PhCreditCard :size="20" :weight="activeTab === 'fees' ? 'fill' : 'regular'" />
              <span class="tab-label">Dues</span>
            </div>
          </ion-tab-button>

          <!-- 3. Centre CREATE (+) FAB Button -->
          <ion-tab-button
            tab="create"
            href="/tabs/create"
            class="tab-create"
            :class="{ 'is-active': activeTab === 'create' }"
            aria-label="Create Pass"
          >
            <div class="create-fab">
              <PhPlus :size="24" weight="bold" />
            </div>
          </ion-tab-button>

          <!-- 4. SOS / Emergency -->
          <ion-tab-button
            tab="emergency"
            href="/tabs/emergency"
            :class="{ 'is-active': activeTab === 'emergency' }"
          >
            <div class="tab-pill" :class="{ 'pill-active': activeTab === 'emergency' }">
              <PhWarningCircle :size="20" :weight="activeTab === 'emergency' ? 'fill' : 'regular'" />
              <span class="tab-label">Emergency</span>
            </div>
          </ion-tab-button>

          <!-- 5. Profile -->
          <ion-tab-button
            tab="profile"
            href="/tabs/profile"
            :class="{ 'is-active': activeTab === 'profile' }"
          >
            <div class="tab-pill" :class="{ 'pill-active': activeTab === 'profile' }">
              <PhUser :size="20" :weight="activeTab === 'profile' ? 'fill' : 'regular'" />
              <span class="tab-label">Profile</span>
            </div>
          </ion-tab-button>
        </template>

      </ion-tab-bar>
    </ion-tabs>

    <!-- Global QR Scanner Modal for Security -->
    <QRScannerModal
      v-if="securityScannerOpen"
      :is-open="true"
      @close="securityScannerOpen = false"
      @scanned="onSecurityScanned"
    />
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { IonPage, IonTabs, IonTabBar, IonTabButton, IonRouterOutlet } from '@ionic/vue'
import { PhHouse, PhCreditCard, PhPlus, PhWarningCircle, PhUser } from '@phosphor-icons/vue'
import { useAuthStore } from '@/stores/auth'
import QRScannerModal from '@/components/QRScannerModal.vue'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()
const { showToast } = useToast()

const isModalOpen = ref(false)
const securityScannerOpen = ref(false)

function triggerSecurityScan() {
  securityScannerOpen.value = true
}

function onSecurityScanned(value: string) {
  securityScannerOpen.value = false
  if (!value) return

  // Value can be a ULID or a full URL like https://.../pass/01M...
  let passId = value.trim()
  if (passId.includes('/pass/')) {
    passId = passId.split('/pass/')[1].split('?')[0].split('/')[0]
  }

  showToast('Pass scanned — opening verification...', 'info')
  router.push(`/pass/${passId}`)
}

function handleOpenScannerEvent() {
  securityScannerOpen.value = true
}

onMounted(() => {
  window.addEventListener('ionModalDidPresent', () => { isModalOpen.value = true })
  window.addEventListener('ionModalDidDismiss', () => { isModalOpen.value = false })
  window.addEventListener('ionAlertDidPresent', () => { isModalOpen.value = true })
  window.addEventListener('ionAlertDidDismiss', () => { isModalOpen.value = false })
  window.addEventListener('ionActionSheetDidPresent', () => { isModalOpen.value = true })
  window.addEventListener('ionActionSheetDidDismiss', () => { isModalOpen.value = false })
  window.addEventListener('open-gate-scanner', handleOpenScannerEvent)
})

onUnmounted(() => {
  window.removeEventListener('open-gate-scanner', handleOpenScannerEvent)
})

const showTabBar = computed(() => {
  if (isModalOpen.value) return false
  if (route.meta?.hideTabBar) return false
  const p = route.path || ''
  if (
    p.includes('/create') ||
    p.includes('/pass/') ||
    p.includes('/passes') ||
    p.includes('/notifications') ||
    p.includes('/fees/payment')
  ) {
    return false
  }
  return true
})

const activeTab = computed(() => {
  const p = route.path || ''
  if (p.includes('/tabs/fees')) return 'fees'
  if (p.includes('/tabs/create')) return 'create'
  if (p.includes('/tabs/emergency')) return 'emergency'
  if (p.includes('/tabs/profile')) return 'profile'
  if (p.includes('/tabs/home')) return 'home'
  return 'home'
})
</script>

<style scoped>
.custom-tab-bar {
  --background: transparent;
  --border-color: transparent;
  position: fixed;
  bottom: calc(var(--ion-safe-area-bottom, 0px) + 12px);
  left: 16px;
  right: 16px;
  width: calc(100% - 32px) !important;
  max-width: 420px !important;
  margin: 0 auto;
  height: 64px;
  background: rgba(238, 241, 239, 0.96) !important;
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.9) !important;
  border-radius: 9999px !important;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.04) !important;
  display: flex;
  align-items: center;
  justify-content: space-evenly;
  padding: 0 4px;
  contain: none !important;
  overflow: visible !important;
  z-index: 999;
}

/* 3-tab security bar: slightly narrower, ultra-balanced */
.custom-tab-bar.security-bar {
  max-width: 310px !important;
  padding: 0 10px;
}

.scan-fab {
  background: var(--w-primary, #0A5C38) !important;
  box-shadow: 0 4px 16px rgba(10, 92, 56, 0.42) !important;
}

.checkpass-fab-logo {
  display: block;
}

ion-tab-button {
  --background: transparent;
  --color: #7B8B82;
  --color-selected: var(--w-primary, #0A5C38);
  --padding-top: 0px;
  --padding-bottom: 0px;
  --padding-start: 0px;
  --padding-end: 0px;
  --ripple-color: transparent;
  background: transparent;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 1;
  contain: none !important;
  overflow: visible !important;
}

/* Inactive / Base Pill Wrapper */
.tab-pill {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  height: 52px;
  width: 100%;
  max-width: 64px;
  padding: 4px 6px;
  border-radius: 9999px !important;
  color: #7B8B82;
  background: transparent;
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Active Pill Wrapper: Elevated white capsule with green filled icon & label */
.tab-pill.pill-active {
  background: #FFFFFF;
  color: var(--w-primary, #0A5C38);
  border-radius: 9999px !important;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.04);
}

.tab-pill:active {
  transform: scale(0.92);
}

.tab-label {
  font-size: 10px;
  font-weight: 500;
  line-height: 1.15;
  letter-spacing: -0.015em;
  color: inherit;
  white-space: nowrap;
  font-family: 'Satoshi', -apple-system, BlinkMacSystemFont, 'SF Pro Text', Roboto, sans-serif;
  transition: font-weight 0.18s ease, color 0.18s ease;
}

.tab-pill.pill-active .tab-label {
  font-weight: 700;
  color: var(--w-primary, #0A5C38);
}

/* Center FAB button */
.tab-create {
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: visible;
  flex: 1;
}

.create-fab {
  background: var(--w-primary, #0A5C38);
  color: #FFFFFF;
  width: 46px;
  height: 46px;
  min-width: 46px;
  min-height: 46px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 14px rgba(10, 92, 56, 0.38);
  transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.18s ease;
}

.tab-create:active .create-fab {
  transform: scale(0.92);
}

.tab-create.is-active .create-fab {
  background: var(--w-primary-dark, #053B23);
  box-shadow: 0 2px 8px rgba(10, 92, 56, 0.45);
}

body.modal-open ion-tab-bar,
.modal-is-open ion-tab-bar,
ion-modal.show-modal ~ ion-tab-bar {
  display: none !important;
}
</style>
