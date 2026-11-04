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

        <ion-tab-button
          tab="home"
          href="/tabs/home"
          :class="{ 'is-active': activeTab === 'home' }"
        >
          <ion-icon :icon="homeOutline" />
          <ion-label>Home</ion-label>
        </ion-tab-button>

        <ion-tab-button
          tab="fees"
          href="/tabs/fees"
          :class="{ 'is-active': activeTab === 'fees' }"
        >
          <ion-icon :icon="cardOutline" />
          <ion-label>Fees</ion-label>
        </ion-tab-button>

        <!-- Centre CREATE button - perfect circle elevated FAB without text label -->
        <ion-tab-button
          tab="create"
          href="/tabs/create"
          class="tab-create"
          :class="{ 'is-active': activeTab === 'create' }"
        >
          <div class="create-fab">
            <ion-icon :icon="addOutline" />
          </div>
        </ion-tab-button>

        <ion-tab-button
          tab="emergency"
          href="/tabs/emergency"
          :class="{ 'is-active': activeTab === 'emergency' }"
        >
          <ion-icon :icon="warningOutline" />
          <ion-label>SOS</ion-label>
        </ion-tab-button>

        <ion-tab-button
          tab="profile"
          href="/tabs/profile"
          :class="{ 'is-active': activeTab === 'profile' }"
        >
          <ion-icon :icon="personOutline" />
          <ion-label>Profile</ion-label>
        </ion-tab-button>

      </ion-tab-bar>
    </ion-tabs>
  </ion-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { IonPage, IonTabs, IonTabBar, IonTabButton, IonIcon, IonLabel, IonRouterOutlet } from '@ionic/vue'
import { homeOutline, addOutline, cardOutline, warningOutline, personOutline } from 'ionicons/icons'

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
  --background: #FFFFFF;
  --border-color: #EAEFEA;
  --color: #8C9991;
  --color-selected: var(--w-primary, #0A5C38);
  height: 52px;
  width: 100% !important;
  max-width: 100% !important;
  left: 0 !important;
  right: 0 !important;
  border-top: 1px solid #EAEFEA;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.03);
  contain: none;
}

ion-tab-button {
  --color: #8C9991;
  --color-selected: var(--w-primary, #0A5C38);
  --padding-top: 3px;
  --padding-bottom: 3px;
  --padding-start: 0px;
  --padding-end: 0px;
  color: #8C9991;
  transition: color 0.15s ease;
}

ion-tab-button ion-icon {
  font-size: 24px;
  width: 24px;
  height: 24px;
  margin-top: 2px;
  margin-bottom: 2px;
  transition: color 0.15s ease;
}

ion-tab-button ion-label {
  font-size: 11.5px;
  font-weight: 500;
  line-height: 1.15;
  letter-spacing: -0.01em;
  margin-top: 0;
  margin-bottom: 2px;
  transition: color 0.15s ease, font-weight 0.15s ease;
}

/* Active tab state */
ion-tab-button.is-active,
ion-tab-button.tab-selected {
  --color: var(--w-primary, #0A5C38) !important;
  color: var(--w-primary, #0A5C38) !important;
}

ion-tab-button.is-active ion-icon,
ion-tab-button.tab-selected ion-icon {
  color: var(--w-primary, #0A5C38) !important;
}

ion-tab-button.is-active ion-label,
ion-tab-button.tab-selected ion-label {
  color: var(--w-primary, #0A5C38) !important;
  font-weight: 700 !important;
}

/* Center Create Pass FAB Button */
.tab-create {
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: visible;
}

.create-fab {
  background: var(--w-primary, #0A5C38);
  color: #FFFFFF;
  width: 48px;
  height: 48px;
  min-width: 48px;
  min-height: 48px;
  max-width: 48px;
  max-height: 48px;
  aspect-ratio: 1 / 1;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: -22px;
  position: relative;
  z-index: 25;
  box-shadow: 0 6px 18px rgba(10, 92, 56, 0.42);
  transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.18s ease;
}

.create-fab ion-icon {
  font-size: 26px;
  width: 26px;
  height: 26px;
  color: #FFFFFF !important;
  margin: 0;
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
