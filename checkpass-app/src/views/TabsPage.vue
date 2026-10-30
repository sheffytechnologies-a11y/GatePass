<template>
  <ion-page>
    <ion-tabs>
      <ion-router-outlet />
      <ion-tab-bar slot="bottom" class="custom-tab-bar" :selected-tab="activeTab">

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
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { IonPage, IonTabs, IonTabBar, IonTabButton, IonIcon, IonLabel, IonRouterOutlet } from '@ionic/vue'
import { homeOutline, addOutline, cardOutline, warningOutline, personOutline } from 'ionicons/icons'

const route = useRoute()

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
  width: 44px;
  height: 44px;
  min-width: 44px;
  min-height: 44px;
  max-width: 44px;
  max-height: 44px;
  aspect-ratio: 1 / 1;
  border-radius: 50%;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 14px rgba(10, 92, 56, 0.38);
  transition: transform 0.15s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.15s ease;
  margin: 0 auto;
}

.create-fab:active {
  transform: scale(0.92);
}

.create-fab ion-icon {
  font-size: 26px !important;
  width: 26px !important;
  height: 26px !important;
  color: #FFFFFF !important;
  display: block;
  margin: 0 !important;
}
</style>
