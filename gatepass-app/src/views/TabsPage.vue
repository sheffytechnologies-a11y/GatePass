<template>
  <ion-page>
    <ion-tabs>
      <ion-router-outlet />
      <ion-tab-bar slot="bottom" style="width: 100%; max-width: 100%">

        <ion-tab-button tab="home" href="/tabs/home">
          <ion-icon :icon="homeOutline" />
          <ion-label>Home</ion-label>
        </ion-tab-button>

      

        <ion-tab-button tab="fees" href="/tabs/fees">
          <ion-icon :icon="cardOutline" />
          <ion-label>Fees</ion-label>
        </ion-tab-button>

          <!-- Centre CREATE button - elevated -->
        <ion-tab-button tab="create" href="/tabs/create" class="tab-create">
          <div class="create-fab">
            <ion-icon :icon="addOutline" />
          </div>
          <ion-label>Create Pass</ion-label>
        </ion-tab-button>

        <ion-tab-button tab="emergency" href="/tabs/emergency">
          <ion-icon :icon="warningOutline" />
          <ion-label>SOS</ion-label>
        </ion-tab-button>

        <ion-tab-button tab="profile" href="/tabs/profile">
          <ion-icon :icon="personOutline" />
          <ion-label>Profile</ion-label>
        </ion-tab-button>

      </ion-tab-bar>
    </ion-tabs>
  </ion-page>
</template>

<script setup lang="ts">
import { IonPage, IonTabs, IonTabBar, IonTabButton, IonIcon, IonLabel, IonRouterOutlet } from '@ionic/vue'
import { homeOutline, addOutline, cardOutline, warningOutline, personOutline } from 'ionicons/icons'
</script>

<style scoped>
.tab-create {
  --color: var(--w-primary);
}
.create-fab {
  background: var(--w-primary);
  color: white;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 4px;
  box-shadow: 0 4px 12px rgba(10, 92, 56, 0.4);
}
.create-fab ion-icon {
  font-size: 22px;
  color: white;
}
</style>
