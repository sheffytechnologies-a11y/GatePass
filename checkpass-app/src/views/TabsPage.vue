<template>
  <ion-page>
    <ion-tabs>
      <ion-router-outlet />
      <ion-tab-bar
        v-if="showTabBar"
        slot="bottom"
        class="custom-tab-bar"
        :selected-tab="activeTab"
      >

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

      </ion-tab-bar>
    </ion-tabs>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { IonPage, IonTabs, IonTabBar, IonTabButton, IonRouterOutlet } from '@ionic/vue'
import { PhHouse, PhCreditCard, PhPlus, PhWarningCircle, PhUser } from '@phosphor-icons/vue'

const route = useRoute()
const isModalOpen = ref(false)

onMounted(() => {
  window.addEventListener('ionModalDidPresent', () => { isModalOpen.value = true })
  window.addEventListener('ionModalDidDismiss', () => { isModalOpen.value = false })
  window.addEventListener('ionAlertDidPresent', () => { isModalOpen.value = true })
  window.addEventListener('ionAlertDidDismiss', () => { isModalOpen.value = false })
  window.addEventListener('ionActionSheetDidPresent', () => { isModalOpen.value = true })
  window.addEventListener('ionActionSheetDidDismiss', () => { isModalOpen.value = false })
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
  height: 50px;
  width: 100%;
  max-width: 66px;
  padding: 4px 2px;
  border-radius: 18px;
  color: #7B8B82;
  background: transparent;
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Active Pill Wrapper: Elevated white capsule with green filled icon & label */
.tab-pill.pill-active {
  background: #FFFFFF;
  color: var(--w-primary, #0A5C38);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 1px 2px rgba(0, 0, 0, 0.04);
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
