<template>
  <ion-app>
    <ion-router-outlet />
    <AppToast />
  </ion-app>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { IonApp, IonRouterOutlet } from '@ionic/vue'
import { pushNotifications } from '@/services/pushNotifications'
import AppToast from '@/components/AppToast.vue'

onMounted(() => {
  pushNotifications.initialize()
})
</script>
